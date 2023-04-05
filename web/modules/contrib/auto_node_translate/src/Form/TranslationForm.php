<?php

namespace Drupal\auto_node_translate\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\node\Entity\Node;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * The Translation Form.
 */
class TranslationForm extends FormBase {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The config service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * The route service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $route;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * The module_handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\auto_node_translate\Form\TranslationForm object.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config service.
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   The route service.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The Current User service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module_handler service.
   */
  public function __construct(
    LanguageManagerInterface $language_manager,
    ConfigFactoryInterface $config,
    CurrentRouteMatch $route_match,
    TimeInterface $time,
    AccountProxyInterface $current_user,
    ModuleHandlerInterface $module_handler
  ) {
    $this->languageManager = $language_manager;
    $this->config = $config;
    $this->route = $route_match;
    $this->time = $time;
    $this->currentUser = $current_user;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('language_manager'),
      $container->get('config.factory'),
      $container->get('current_route_match'),
      $container->get('datetime.time'),
      $container->get('current_user'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'auto_node_translate_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $node = NULL) {
    $languages = $this->languageManager->getLanguages();
    $form['translate'] = [
      '#type' => 'fieldgroup',
      '#title' => $this->t('Languages to Translate'),
      '#closed' => FALSE,
      '#tree' => TRUE,
    ];

    foreach ($languages as $language) {
      $languageId = $language->getId();
      if ($languageId !== $node->langcode->value) {
        $label = ($node->hasTranslation($languageId)) ? $this->t('overwrite translation') : $this->t('new translation');
        $form['translate'][$languageId] = [
          '#type' => 'checkbox',
          '#title' => $this->t('@lang (@label)', [
            '@lang' => $language->getName(),
            '@label' => $label,
          ]),
        ];
      }
    }
    $form['save'] = [
      '#type' => 'submit',
      '#value' => $this->t('Translate'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config->get('auto_node_translate.config');
    if (empty($config->get('default_api'))) {
      $form_state->setError($form['translate'], $this->t('Error, translation API is not configured!'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = $this->route->getParameter('node');
    $translations = $form_state->getValues()['translate'];
    foreach ($translations as $lid => $value) {
      if ($value) {
        $this->autoNodeTranslateNode($node, $lid);
      }
    }
    $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);
  }

  /**
   * Translates node.
   *
   * @param \Drupal\node\Entity\Node $node
   *   The node to translate.
   * @param mixed $languageId
   *   The language id.
   */
  public function autoNodeTranslateNode(Node $node, $languageId) {
    $languageFrom = $node->langcode->value;
    $fields = $node->getFields();
    $node_trans = $this->getTransledNode($node, $languageId);
    $excludeFields = $this->getExcludeFields();
    $translatedTypes = $this->getTextFields();
    $config = $this->config->get('auto_node_translate.config');
    $apiType = "Drupal\auto_node_translate\\" . $config->get('default_api');
    $api = new $apiType();

    foreach ($fields as $field) {
      $fieldType = $field->getFieldDefinition()->getType();
      $fieldName = $field->getName();

      if (in_array($fieldType, $translatedTypes) && !in_array($fieldName, $excludeFields)) {
        $translatedValue = $this->translateTextField($field, $fieldType, $api, $languageFrom, $languageId);
        $node_trans->set($fieldName, $translatedValue);
      }
      elseif ($fieldType == 'link') {
        $values = $this->translateLinkField($field, $api, $languageFrom, $languageId);
        $node_trans->set($fieldName, $values);
      }
      elseif ($fieldType == 'entity_reference_revisions') {
        $this->translateParagraphField($field, $api, $languageFrom, $languageId);
      }
      elseif (!in_array($fieldName, $excludeFields)) {
        $node_trans->set($fieldName, $node->get($fieldName)->getValue());
      }
    }
    $node->setNewRevision(TRUE);
    $node->revision_log = $this->t('Automatica translation using @api', ['@api' => $config->get('default_api')]);
    $node->setRevisionCreationTime($this->time->getRequestTime());
    $node->setRevisionUserId($this->currentUser->id());
    $node->save();
  }

  /**
   * Translates paragraph.
   *
   * @param mixed $paragraph
   *   The paragraph to translate.
   * @param mixed $api
   *   The api to use.
   * @param mixed $languageFrom
   *   The language from.
   * @param mixed $languageId
   *   The language to.
   */
  public function translateParagraph($paragraph, $api, $languageFrom, $languageId) {
    $excludeFields = $this->getExcludeFields();
    $translatedTypes = $this->getTextFields();
    $translated_fields = [];
    $fields = $paragraph->getFields();

    foreach ($fields as $field) {
      $fieldType = $field->getFieldDefinition()->getType();
      $fieldName = $field->getName();

      if (in_array($fieldType, $translatedTypes) && !in_array($fieldName, $excludeFields)) {
        $translatedValue = $this->translateTextField($field, $fieldType, $api, $languageFrom, $languageId);
        $translated_fields[$fieldName] = $translatedValue;
      }
      elseif ($fieldType == 'link') {
        $values = $this->translateLinkField($field, $api, $languageFrom, $languageId);
        $translated_fields[$fieldName] = $values;
      }
      elseif ($fieldType == 'entity_reference_revisions') {
        $this->translateParagraphField($field, $api, $languageFrom, $languageId);
      }
      elseif (!in_array($fieldName, $excludeFields)) {
        $values = $field->getValue();
        $translated_fields[$fieldName] = $values;
      }
    }

    if ($paragraph->hasTranslation($languageId)) {
      $translated_paragraph = $paragraph->getTranslation($languageId);
      foreach ($translated_fields as $key => $value) {
        $translated_paragraph->set($key, $value);
      }
      $translated_paragraph->save();
    }
    else {
      $paragraph->addTranslation($languageId, $translated_fields)->save();
    }
  }

  /**
   * Gets or adds translated node.
   *
   * @param mixed $node
   *   The node.
   * @param mixed $languageId
   *   The language id.
   *
   * @return mixed
   *   the translated node.
   */
  public function getTransledNode(&$node, $languageId) {
    return $node->hasTranslation($languageId) ? $node->getTranslation($languageId) : $node->addTranslation($languageId);
  }

  /**
   * Translates text field.
   *
   * @param mixed $field
   *   The field to translate.
   * @param string $fieldType
   *   The field type.
   * @param mixed $api
   *   The api to use.
   * @param mixed $languageFrom
   *   The language from.
   * @param mixed $languageId
   *   The language id.
   * @param array $customTranslations
   *   Empty array to be changed by the hook with custom translations.
   */
  public function translateTextField($field, $fieldType, $api, $languageFrom, $languageId, array $customTranslations = []) {
    $translatedValue = [];
    $values = $field->getValue();
    foreach ($values as $key => $text) {
      if (!empty($text['value'])) {
        $info = [
          "field" => $field,
          "from" => $languageFrom,
          "to" => $languageId,
        ];
        $textToTranslate = $text['value'];
        $this->moduleHandler->invokeAll('auto_node_translate_translation_alter',
          [
            &$textToTranslate,
            &$customTranslations,
            &$info,
          ]
        );
        $translatedText = $api->translate($textToTranslate, $languageFrom, $languageId, $customTranslations);
        if (in_array($fieldType, ['string', 'text']) && (strlen($translatedText) > 255)) {
          $translatedText = mb_substr($translatedText, 0, 255);
        }
        $translatedValue[$key]['value'] = $translatedText;
        if (isset($text['format'])) {
          $translatedValue[$key]['format'] = $text['format'];
        }
      }
      else {
        $translatedValue[$key] = [];
      }
    }

    return $translatedValue;
  }

  /**
   * Translates link field.
   *
   * @param mixed $field
   *   The field to translate.
   * @param mixed $api
   *   The api to use.
   * @param mixed $languageFrom
   *   The language from.
   * @param mixed $languageId
   *   The language id.
   * @param array $customTranslations
   *   The Custom translations array.
   */
  public function translateLinkField($field, $api, $languageFrom, $languageId, array $customTranslations = []) {
    $values = $field->getValue();
    foreach ($values as $key => $link) {
      if (!empty($link['title'])) {
        $info = [
          "field" => $field,
          "from" => $languageFrom,
          "to" => $languageId,
        ];
        $textToTranslate = $link['title'];
        $this->moduleHandler->invokeAll('auto_node_translate_translation_alter',
        [
          &$textToTranslate,
          &$customTranslations,
          &$info,
        ]
        );
        $translatedText = $api->translate($textToTranslate, $languageFrom, $languageId, $customTranslations);
        $values[$key]['title'] = $translatedText;
      }
    }

    return $values;
  }

  /**
   * Translates paragraph field.
   *
   * @param mixed $field
   *   The field to translate.
   * @param mixed $api
   *   The api to use.
   * @param mixed $languageFrom
   *   The language from.
   * @param mixed $languageId
   *   The language id.
   */
  public function translateParagraphField($field, $api, $languageFrom, $languageId) {
    $targetParagraphs = $field->getValue();
    foreach ($targetParagraphs as $target) {
      $paragraph = Paragraph::load($target['target_id'], $target['target_revision_id']);
      $this->translateParagraph($paragraph, $api, $languageFrom, $languageId);
    }
  }

  /**
   * Returns excluded fields.
   */
  public function getExcludeFields() {
    return [
      'langcode',
      'parent_id',
      'parent_type',
      'parent_field_name',
      'default_langcode',
      'id',
      'uuid',
      'revision_id',
      'type',
      'status',
      'created',
      'behavior_settings',
      'revision_default',
      'revision_translation_affected',
      'content_translation_source',
      'content_translation_outdated',
      'content_translation_changed',
    ];
  }

  /**
   * Returns text fields.
   */
  public function getTextFields() {
    return [
      'string',
      'string_long',
      'text',
      'text_long',
      'text_with_summary',
    ];
  }

}

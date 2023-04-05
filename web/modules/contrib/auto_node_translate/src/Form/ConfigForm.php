<?php

namespace Drupal\auto_node_translate\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The Configuration form.
 */
class ConfigForm extends ConfigFormBase {

  /**
   * The storage handler class for files.
   *
   * @var \Drupal\file\FileStorage
   */
  protected $fileStorage;

  /**
   * Constructs a \Drupal\auto_node_translate\Form\ConfigForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity
   *   The Entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity) {
    $this->fileStorage = $entity->getStorage('file');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'auto_node_translate.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'auto_node_translate_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('auto_node_translate.config');

    $form['default_api'] = [
      '#type' => 'select',
      '#title' => $this->t('Translation API'),
      '#options' => [
        'AmazonTranslateApi' => $this->t('Amazon Translate'),
        'IbmWatsonTranslationApi' => $this->t('IBM Watson'),
        'MyMemoryTranslationApi' => $this->t('My Memory'),
        'GoogleTranslationApi' => $this->t('Google Cloud Translate V3'),
        'GoogleTranslationApiV2' => $this->t('Google Cloud Translate V2'),
      ],
      '#default_value' => $config->get('default_api'),
    ];

    $form['amazon_translate_api'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Amazon Translate API Configuration'),
      '#states' => [
        'visible' => [
          ':input[name="default_api"]' => ['value' => 'AmazonTranslateApi'],
        ],
      ],
    ];

    $form['amazon_translate_api']['amazon_translate_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api Key'),
      '#description' => $this->t('the Amazon Translate api key'),
      '#maxlength' => 256,
      '#size' => 128,
      '#default_value' => $config->get('amazon_translate_key'),
      '#states' => [
        'required' => [
          ':input[name="default_api"]' => ['value' => 'AmazonTranslateApi'],
        ],
      ],
    ];

    $form['amazon_translate_api']['amazon_translate_secret'] = [
      '#type' => 'password',
      '#title' => $this->t('Secret'),
      '#description' => $this->t('the Amazon Translate secret'),
      '#maxlength' => 256,
      '#size' => 128,
      '#default_value' => $config->get('amazon_translate_secret'),
      '#states' => [
        'required' => [
          ':input[name="default_api"]' => ['value' => 'AmazonTranslateApi'],
        ],
      ],
    ];

    $form['amazon_translate_api']['amazon_translate_region'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Region'),
      '#description' => $this->t('the Amazon Translate region'),
      '#maxlength' => 256,
      '#size' => 128,
      '#default_value' => $config->get('amazon_translate_region'),
      '#states' => [
        'required' => [
          ':input[name="default_api"]' => ['value' => 'AmazonTranslateApi'],
        ],
      ],
    ];

    $form['watson_api'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('IBM Watson API Configuration'),
      '#states' => [
        'visible' => [
          ':input[name="default_api"]' => ['value' => 'IbmWatsonTranslationApi'],
        ],
      ],
    ];

    $form['watson_api']['ibm_watson_apikey'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Api Key'),
      '#description' => $this->t('the IBM watson api key'),
      '#maxlength' => 256,
      '#size' => 128,
      '#default_value' => $config->get('ibm_watson_apikey'),
      '#states' => [
        'required' => [
          ':input[name="default_api"]' => ['value' => 'IbmWatsonTranslationApi'],
        ],
      ],
    ];

    $form['watson_api']['ibm_watson_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#description' => $this->t('the ibm watson url'),
      '#maxlength' => 256,
      '#size' => 128,
      '#default_value' => $config->get('ibm_watson_url'),
      '#states' => [
        'required' => [
          ':input[name="default_api"]' => ['value' => 'IbmWatsonTranslationApi'],
        ],
      ],
    ];

    $form['watson_api']['ibm_watson_version'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Version'),
      '#description' => $this->t('The Api Version in the format YYYY-MM-DD defaults to 2018-05-01'),
      '#maxlength' => 10,
      '#size' => 10,
      '#default_value' => empty($config->get('ibm_watson_version')) ? '2018-05-01' : $config->get('ibm_watson_version'),
      '#states' => [
        'required' => [
          ':input[name="default_api"]' => ['value' => 'IbmWatsonTranslationApi'],
        ],
      ],
    ];

    $form['google_api'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Google API V3 Configuration'),
      '#states' => [
        'visible' => [
          ':input[name="default_api"]' => ['value' => 'GoogleTranslationApi'],
        ],
      ],
    ];

    $form['google_api']['google_location'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Location'),
      '#description' => $this->t('Default: global'),
      '#maxlength' => 60,
      '#size' => 60,
      '#default_value' => $config->get('google_location'),
      '#states' => [
        'required' => [
          ':input[name="default_api"]' => ['value' => 'GoogleTranslationApi'],
        ],
      ],
    ];

    $form['google_api']['google_api_project'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Project ID'),
      '#description' => $this->t('Your Google Cloud Platform project ID'),
      '#maxlength' => 256,
      '#size' => 128,
      '#default_value' => $config->get('google_api_project'),
      '#states' => [
        'required' => [
          ':input[name="default_api"]' => ['value' => 'GoogleTranslationApi'],
        ],
      ],
    ];

    $form['google_api']['google_credentials'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Google API Credentials'),
      '#description' => $this->t('https://cloud.google.com/translate/docs/setup#php'),
      '#default_value' => $config->get('google_credentials'),
      '#upload_location' => 'private://',
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#states' => [
        'required' => [
          ':input[name="default_api"]' => ['value' => 'GoogleTranslationApi'],
        ],
      ],
    ];

    $form['google_api_v2'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Google API V2 Configuration'),
      '#states' => [
        'visible' => [
          ':input[name="default_api"]' => ['value' => 'GoogleTranslationApiV2'],
        ],
      ],
    ];

    $form['google_api_v2']['google_api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('google_api_key'),
      '#states' => [
        'required' => [
          ':input[name="default_api"]' => ['value' => 'GoogleTranslationApiV2'],
        ],
      ],
    ];

    $form['google_api_v2']['google_referer'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Referer'),
      '#description' => $this->t('Optional for domain validation in the API'),
      '#default_value' => $config->get('google_referer'),
    ];

    $form['mymemory'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('MyMemory API Configuration'),
      '#states' => [
        'visible' => [
          ':input[name="default_api"]' => ['value' => 'MyMemoryTranslationApi'],
        ],
      ],
    ];

    $form['mymemory']['mm_email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email'),
      '#default_value' => $config->get('mm_email'),
      '#description' => $this->t('If you provide an email the limit will be increased from 1000 to 10000 words'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $credentials = $form_state->getValues()['google_credentials'];
    if (!empty($credentials)) {
      $file = $this->fileStorage->load($credentials[0]);
      if ($file) {
        $file->setPermanent();
        $file->save();
      }
    }
    $this->config('auto_node_translate.config')
      ->set('amazon_translate_key', $form_state->getValue('amazon_translate_key'))
      ->set('amazon_translate_secret', $form_state->getValue('amazon_translate_secret'))
      ->set('amazon_translate_region', $form_state->getValue('amazon_translate_region'))
      ->set('ibm_watson_apikey', $form_state->getValue('ibm_watson_apikey'))
      ->set('ibm_watson_url', $form_state->getValue('ibm_watson_url'))
      ->set('ibm_watson_version', $form_state->getValue('ibm_watson_version'))
      ->set('google_api_project', $form_state->getValue('google_api_project'))
      ->set('google_location', $form_state->getValue('google_location'))
      ->set('google_credentials', $credentials)
      ->set('google_api_key', $form_state->getValue('google_api_key'))
      ->set('google_referer', $form_state->getValue('google_referer'))
      ->set('mm_email', $form_state->getValue('mm_email'))
      ->set('default_api', $form_state->getValue('default_api'))

      ->save();
  }

}

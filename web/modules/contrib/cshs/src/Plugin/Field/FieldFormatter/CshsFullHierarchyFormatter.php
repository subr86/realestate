<?php

namespace Drupal\cshs\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Defines the field formatter.
 *
 * @FieldFormatter(
 *   id = "cshs_full_hierarchy",
 *   label = @Translation("Full hierarchy"),
 *   description = @Translation("Display the full hierarchy of the taxonomy term."),
 *   field_types = {
 *     "entity_reference",
 *   },
 * )
 */
class CshsFullHierarchyFormatter extends CshsFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings(): array {
    $settings = parent::defaultSettings();
    $settings['separator'] = ' » ';

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state): array {
    $element = parent::settingsForm($form, $form_state);
    $element['separator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Separator'),
      '#description' => $this->t('Specify a separator which will be placed in between the different hierarchy levels.'),
      '#default_value' => $this->getSetting('separator'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary(): array {
    $summary = parent::settingsSummary();
    $separator = $this->getSetting('separator');

    $summary[] = $this->t('Separator: @separator', [
      '@separator' => empty($separator) ? $this->t('None') : $separator,
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode): array {
    $elements = [];
    $linked = $this->getSetting('linked');
    $reverse = $this->getSetting('reverse');
    $separator = $this->getSetting('separator') ?: ' ';

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $term) {
      $elements[$delta]['#markup'] = \implode($separator, $this->getTermsLabels($this->getTermParents($term, !$reverse), $linked));
    }

    return $elements;
  }

}

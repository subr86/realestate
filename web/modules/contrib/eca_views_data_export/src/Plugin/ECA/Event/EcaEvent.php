<?php

namespace Drupal\eca_views_data_export\Plugin\ECA\Event;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Entity\Objects\EcaEvent as BaseEcaEvent;
use Drupal\eca\Plugin\ECA\Event\EventBase;
use Drupal\eca_views_data_export\EcaEvents;
use Drupal\eca_views_data_export\Event\AlterRow;

/**
 * Plugin implementation of the ECA Events for eca_views_data_export.
 *
 * @EcaEvent(
 *   id = "eca_views_data_export",
 *   deriver = "Drupal\eca_views_data_export\Plugin\ECA\Event\EcaEventDeriver"
 * )
 */
class EcaEvent extends EventBase {

  /**
   * {@inheritdoc}
   */
  public static function definitions(): array {
    $definitions = [];
    $definitions['alter_row'] = [
      'label' => 'Alter a row',
      'event_name' => EcaEvents::ALTER_ROW,
      'event_class' => AlterRow::class,
    ];
    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    if ($this->eventClass() === AlterRow::class) {
      $form['view_id'] = [
        '#type' => 'textfield',
        '#title' => $this->t('View ID'),
        '#default_value' => $this->configuration['view_id'] ?? '',
      ];
      $form['display_id'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Display ID'),
        '#default_value' => $this->configuration['display_id'] ?? '',
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function lazyLoadingWildcard(string $eca_config_id, BaseEcaEvent $ecaEvent): string {
    switch ($this->getDerivativeId()) {

      case 'alter_row':
        $configuration = $ecaEvent->getConfiguration();
        return (isset($configuration['view_id']) ? trim($configuration['view_id']) : '') . '::' . (isset($configuration['display_id']) ? trim($configuration['display_id']) : '');

      default:
        return parent::lazyLoadingWildcard($eca_config_id, $ecaEvent);

    }
  }

}

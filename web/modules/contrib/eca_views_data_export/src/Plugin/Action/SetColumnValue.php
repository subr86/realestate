<?php

namespace Drupal\eca_views_data_export\Plugin\Action;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\eca\Plugin\Action\ConfigurableActionBase;
use Drupal\eca_views_data_export\Event\AlterRow;

/**
 * Describes the eca_views_data_export set_column_value action.
 *
 * @Action(
 *   id = "eca_views_data_export_set_column_value",
 *   label = @Translation("Set column value")
 * )
 */
class SetColumnValue extends ConfigurableActionBase {

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    $access_result = AccessResult::allowed();
    return $return_as_object ? $access_result : $access_result->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute(): void {
    $event = $this->getEvent();
    if ($event instanceof AlterRow) {
      $row = &$event->getRow();
      $column = $this->tokenServices->replaceClear($this->configuration['column']);
      $value = $this->tokenServices->replaceClear($this->configuration['value']);
      if (isset($row[$column])) {
        $row[$column] = $value;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'column' => '',
      'value' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form['column'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Column'),
      '#default_value' => $this->configuration['column'],
    ];
    $form['value'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Value'),
      '#default_value' => $this->configuration['value'],
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    $this->configuration['column'] = $form_state->getValue('column');
    $this->configuration['value'] = $form_state->getValue('value');
    parent::submitConfigurationForm($form, $form_state);
  }

}

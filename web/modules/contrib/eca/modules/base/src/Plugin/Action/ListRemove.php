<?php

namespace Drupal\eca_base\Plugin\Action;

use Drupal\Core\Form\FormStateInterface;
use Drupal\eca\Plugin\Action\ListRemoveBase;

/**
 * Action to remove an item from a list.
 *
 * @Action(
 *   id = "eca_list_remove",
 *   label = @Translation("List: remove item"),
 *   description = @Translation("Remove an item from a list and optionally store the item as a token.")
 * )
 */
class ListRemove extends ListRemoveBase {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $token_name = trim((string) $this->configuration['token_name']);
    $item = $this->removeItem();
    if ($token_name !== '') {
      $this->tokenServices->addTokenData($token_name, $item);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getValueToRemove() {
    return $this->tokenServices->getOrReplace($this->configuration['value']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'value' => '',
      'token_name' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildConfigurationForm($form, $form_state);
    $form['value'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Value to remove'),
      '#description' => $this->t('When <em>Drop by specified value</em> is selected above, then a value must be specified here. This field supports tokens.'),
      '#default_value' => $this->configuration['value'],
      '#weight' => 20,
    ];
    $form['token_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Name of token'),
      '#description' => $this->t('Provide the name of a token that holds the removed item.'),
      '#default_value' => $this->configuration['token_name'],
      '#weight' => 30,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state): void {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['value'] = $form_state->getValue('value');
    $this->configuration['token_name'] = $form_state->getValue('token_name');
  }

}

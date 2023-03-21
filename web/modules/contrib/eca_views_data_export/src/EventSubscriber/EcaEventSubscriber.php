<?php

namespace Drupal\eca_views_data_export\EventSubscriber;

use Drupal\eca\EventSubscriber\EcaBase;
use Drupal\eca_views_data_export\Plugin\ECA\Event\EcaEvent;

/**
 * Adds current views row events into a publicly available stack.
 */
class EcaEventSubscriber extends EcaBase {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    foreach (EcaEvent::definitions() as $definition) {
      $events[$definition['event_name']][] = ['onEvent'];
    }
    return $events;
  }

}

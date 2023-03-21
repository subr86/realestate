<?php

namespace Drupal\eca_views_data_export\Plugin\ECA\Event;

use Drupal\eca\Plugin\ECA\Event\EventDeriverBase;

/**
 * Deriver for eca_views_data_export event plugins.
 */
class EcaEventDeriver extends EventDeriverBase {

  /**
   * {@inheritdoc}
   */
  protected function definitions(): array {
    return EcaEvent::definitions();
  }

}

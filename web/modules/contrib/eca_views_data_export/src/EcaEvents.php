<?php

namespace Drupal\eca_views_data_export;

/**
 * Defines events provided by the eca_views_data_export module.
 */
final class EcaEvents {

  /**
   * Triggers when a views row can be altered.
   *
   * @Event
   *
   * @var string
   */
  public const ALTER_ROW = 'eca_views_data_export.alter_row';

}

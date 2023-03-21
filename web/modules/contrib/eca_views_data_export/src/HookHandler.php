<?php

namespace Drupal\eca_views_data_export;

use Drupal\eca\Event\BaseHookHandler;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * The handler for hooks within the eca_views_data_export.module file.
 *
 * @internal
 *   This class is not meant to be used as a public API. It is subject for name
 *   change or may be removed completely, also on minor version updates.
 */
class HookHandler extends BaseHookHandler {

  /**
   * Dispatches event alter row.
   *
   * @param array $row
   *   The row to be altered.
   * @param \Drupal\views\ResultRow $result
   *   The views result for this row.
   * @param \Drupal\views\ViewExecutable $view
   *   The views object.
   */
  public function alterRow(array &$row, ResultRow $result, ViewExecutable $view): void {
    $this->triggerEvent->dispatchFromPlugin('eca_views_data_export:alter_row', $row, $result, $view);
  }

}

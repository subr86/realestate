<?php

namespace Drupal\eca_views_data_export\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\eca\Event\ConditionalApplianceInterface;
use Drupal\views\ResultRow;
use Drupal\views\ViewExecutable;

/**
 * Provides an event for eca_views_data_export.
 *
 * @package Drupal\eca_views_data_export\Event
 */
class AlterRow extends Event implements ConditionalApplianceInterface {

  /**
   * The row to be altered.
   *
   * @var array
   */
  protected array $row;

  /**
   * The views result for this row as an array.
   *
   * @var array
   */
  protected array $result;

  /**
   * The views object.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected ViewExecutable $view;

  /**
   * Constructs an alter row event.
   *
   * @param array $row
   *   The row to be altered.
   * @param \Drupal\views\ResultRow $result
   *   The views result for this row.
   * @param \Drupal\views\ViewExecutable $view
   *   The views object.
   */
  public function __construct(array &$row, ResultRow $result, ViewExecutable $view) {
    $this->row = &$row;
    $this->result = [];
    // @phpstan-ignore-next-line
    foreach ($result as $key => $value) {
      $this->result[$key] = $value;
    }
    $this->view = $view;
  }

  /**
   * {@inheritdoc}
   */
  public function appliesForLazyLoadingWildcard(string $wildcard): bool {
    [$view_id, $display_id] = explode('::', $wildcard);
    return ($view_id === '' || $this->view->id() === $view_id) && ($display_id === '' || $this->view->current_display === $display_id);
  }

  /**
   * {@inheritdoc}
   */
  public function applies(string $id, array $arguments): bool {
    $view_id = isset($arguments['view_id']) ? trim($arguments['view_id']) : '';
    $display_id = isset($arguments['display_id']) ? trim($arguments['display_id']) : '';
    $condition1 = ($view_id === '') || ($this->view->id() === $view_id);
    $condition2 = ($display_id === '') || ($this->view->current_display === $display_id);
    return $condition1 && $condition2;
  }

  /**
   * Gets the row to be altered.
   *
   * @return array
   *   The row to be altered.
   */
  public function &getRow(): array {
    $row = &$this->row;
    return $row;
  }

  /**
   * Gets the views result as an array.
   *
   * @return array
   *   The views result.
   */
  public function getResult(): array {
    return $this->result;
  }

}

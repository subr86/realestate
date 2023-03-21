<?php

namespace Drupal\eca_views_data_export\EventSubscriber;

use Drupal\eca\EcaEvents;
use Drupal\eca\Event\AfterInitialExecutionEvent;
use Drupal\eca\Event\BeforeInitialExecutionEvent;
use Drupal\eca_base\EventSubscriber\EcaBase;
use Drupal\eca_views_data_export\Event\AlterRow;

/**
 * Adds current views row events into a publicly available stack.
 */
class EcaExecutionRowSubscriber extends EcaBase {

  /**
   * A stack of row events, which the subscriber involved for execution.
   *
   * @var \Drupal\eca_views_data_export\Event\AlterRow[]
   */
  protected array $eventStack = [];

  /**
   * Subscriber method before initial execution.
   *
   * Adds the event to the stack, and the row and result to the Token service.
   *
   * @param \Drupal\eca\Event\BeforeInitialExecutionEvent $before_event
   *   The according event.
   */
  public function onBeforeInitialExecution(BeforeInitialExecutionEvent $before_event): void {
    $event = $before_event->getEvent();
    if ($event instanceof AlterRow) {
      array_unshift($this->eventStack, $event);
      $this->tokenService->addTokenData('current-result', $event->getResult());
      $this->tokenService->addTokenData('current-row', $event->getRow());
    }
  }

  /**
   * Subscriber method after initial execution.
   *
   * Removes the row data provider from the Token service.
   *
   * @param \Drupal\eca\Event\AfterInitialExecutionEvent $after_event
   *   The according event.
   */
  public function onAfterInitialExecution(AfterInitialExecutionEvent $after_event): void {
    $event = $after_event->getEvent();
    if ($event instanceof AlterRow) {
      array_shift($this->eventStack);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events = [];
    $events[EcaEvents::BEFORE_INITIAL_EXECUTION][] = [
      'onBeforeInitialExecution',
      -100,
    ];
    $events[EcaEvents::AFTER_INITIAL_EXECUTION][] = [
      'onAfterInitialExecution',
      100,
    ];
    return $events;
  }

  /**
   * Get the stack of row events, which the subscriber involved for execution.
   *
   * @return \Drupal\eca_views_data_export\Event\AlterRow[]
   *   The stack of involved row events, which is an array ordered by the most
   *   recent events at the beginning and the first added events at the end.
   */
  public function getStackedRowEvents(): array {
    return $this->eventStack;
  }

}

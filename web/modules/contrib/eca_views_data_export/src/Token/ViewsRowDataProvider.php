<?php

namespace Drupal\eca_views_data_export\Token;

use Drupal\eca\Plugin\DataType\DataTransferObject;
use Drupal\eca\Token\DataProviderInterface;
use Drupal\eca_views_data_export\EventSubscriber\EcaExecutionRowSubscriber;

/**
 * Provides data of the current form.
 */
class ViewsRowDataProvider implements DataProviderInterface {

  /**
   * The ECA row event subscriber.
   *
   * @var \Drupal\eca_views_data_export\EventSubscriber\EcaExecutionRowSubscriber
   */
  protected EcaExecutionRowSubscriber $subscriber;

  /**
   * In-memory cache of instantiated data.
   *
   * @var array
   */
  protected static array $cached = [];

  /**
   * Constructs a new FormDataProvider object.
   *
   * @param \Drupal\eca_views_data_export\EventSubscriber\EcaExecutionRowSubscriber $subscriber
   *   The ECA row event subscriber.
   */
  public function __construct(EcaExecutionRowSubscriber $subscriber) {
    $this->subscriber = $subscriber;
  }

  /**
   * {@inheritdoc}
   */
  public function getData(string $key) {
    if (!($events = $this->subscriber->getStackedRowEvents())) {
      return NULL;
    }

    $event = reset($events);

    switch ($key) {

      case 'current-result':
        return DataTransferObject::create($event->getResult());

      case 'current-row':
        return DataTransferObject::create($event->getRow());

      default:
        return NULL;

    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasData(string $key): bool {
    return $this->getData($key) !== NULL;
  }

}

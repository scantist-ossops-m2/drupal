<?php

declare(strict_types=1);

namespace Drupal\performance_test\Cache;

use Drupal\Core\Cache\CacheTagsChecksumInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\performance_test\PerformanceDataCollector;

/**
 * Wraps an existing cache tags checksum invalidator to track calls separately.
 */
class CacheTagsChecksumDecorator implements CacheTagsChecksumInterface, CacheTagsInvalidatorInterface {

  public function __construct(protected readonly CacheTagsChecksumInterface $checksumInvalidator, protected readonly PerformanceDataCollector $performanceDataCollector) {}

  /**
   * {@inheritdoc}
   */
  public function getCurrentChecksum(array $tags) {
    $start = microtime(TRUE);
    $return = $this->checksumInvalidator->getCurrentChecksum($tags);
    $stop = microtime(TRUE);
    $this->logCacheTagOperation($tags, $start, $stop, CacheTagOperation::getCurrentChecksum);
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function isValid($checksum, array $tags) {
    $start = microtime(TRUE);
    $return = $this->checksumInvalidator->isValid($checksum, $tags);
    $stop = microtime(TRUE);
    $this->logCacheTagOperation($tags, $start, $stop, CacheTagOperation::isValid);
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags) {
    $start = microtime(TRUE);
    $return = $this->checksumInvalidator->invalidateTags($tags);
    $stop = microtime(TRUE);
    $this->logCacheTagOperation($tags, $start, $stop, CacheTagOperation::invalidateTags);
    return $return;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->checksumInvalidator->reset();
  }

  /**
   * Logs a cache tag operation.
   *
   * @param string[] $tags
   *   The cache tags.
   * @param float $start
   *   The start microtime.
   * @param float $stop
   *   The stop microtime.
   * @param \Drupal\performance_test\Cache\CacheTagOperation $operation
   *   The type of operation being logged.
   *
   * @return void
   */
  protected function logCacheTagOperation(array $tags, float $start, float $stop, CacheTagOperation $operation): void {
    $this->performanceDataCollector->addCacheTagOperation([
      'operation' => $operation,
      'tags' => implode(', ', $tags),
      'start' => $start,
      'stop' => $stop,
    ]);
  }

}

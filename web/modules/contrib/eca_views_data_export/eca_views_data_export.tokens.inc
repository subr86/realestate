<?php

/**
 * @file
 * Tokens provided by ECA Views Data Export module.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function eca_views_data_export_token_info(): array {
  $info = [];
  $info['types']['current-result'] = [
    'name' => t('Current views result'),
    'needs-data' => 'current-result',
  ];
  $info['types']['current-row'] = [
    'name' => t('Current views row'),
    'needs-data' => 'current-row',
  ];
  $info['tokens']['current-result'] = [];
  $info['tokens']['current-row'] = [];
  return $info;
}

/**
 * Implements hook_tokens().
 */
function eca_views_data_export_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata): array {
  if ($type === 'current-result' && !empty($data['current-result'])) {
    $data['dto'] = $data['current-result'];
    unset($data['current-result']);
    return eca_tokens('dto', $tokens, $data, $options, $bubbleable_metadata);
  }
  if ($type === 'current-row' && !empty($data['current-row'])) {
    $data['dto'] = $data['current-row'];
    unset($data['current-row']);
    return eca_tokens('dto', $tokens, $data, $options, $bubbleable_metadata);
  }
  return [];
}

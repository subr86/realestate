<?php

namespace Drupal\auto_node_translate;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use GuzzleHttp\Exception\BadResponseException;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Description of MyMemoryTranslationApi.
 *
 * Implementation of TranslationApiInterface for MyMemory API.
 */
class MyMemoryTranslationApi implements TranslationApiInterface {
  use StringTranslationTrait;

  /**
   * Constructs the object.
   */
  public function __construct() {

  }

  /**
   * {@inheritdoc}
   */
  public function translate($text, $languageFrom, $languageTo, $customTranslations = []) {
    $from = explode('-', $languageFrom)[0];
    $to = explode('-', $languageTo)[0];
    $text = str_replace('&nbsp;', '%20', $text);
    $config = \Drupal::config('auto_node_translate.config');
    $emailQuery = '';
    if (!empty($config->get('mm_email'))) {
      $emailQuery = '&de=' . $config->get('mm_email');
    }
    // Recursivity due to MymMemory limitation to 500 bytes length in query.
    if (strlen($text) > 400) {
      return $this->translate(substr($text, 0, 400), $languageFrom, $languageTo) . $this->translate(substr($text, 400), $languageFrom, $languageTo);
    }
    else {
      $url = 'https://api.mymemory.translated.net/get?q=' . $text . '&langpair=' . $from . '|' . $to . $emailQuery;
      try {
        $response = \Drupal::httpClient()->get($url);
      }
      catch (BadResponseException $e) {
        $link = Url::fromRoute('auto_node_translate.config_form', [], ['absolute' => TRUE])->toString();
        \Drupal::messenger()->addStatus($this->t('The translation cota or maximum requests/day have been exceeded for MyMemory. Try changing the default translation Api in <a href="@link">@link</a>', ['@link' => $link]));
        return $text;
      }
      $data = (string) $response->getBody();
      $translation = Json::decode($data);
      if (!$translation['quotaFinished']) {
        $translatedText = html_entity_decode($translation['responseData']['translatedText']);
      }
      else {
        $translatedText = $text;
        $link = Url::fromRoute('auto_node_translate.config_form', [], ['absolute' => TRUE])->toString();
        \Drupal::messenger()->addStatus($this->t('The translation cota has been exceeded for MyMemory try changing the default Api in <a href=@link>@link</a>'), ['@link' => $link]);
      }
      return $translatedText;
    }
  }

}

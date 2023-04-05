<?php

namespace Drupal\auto_node_translate;

use Google\Cloud\Translate\V2\TranslateClient;

/**
 * Description of GoogleTranslationAPIV2.
 *
 * Implementation of TranslationApiInterface for Google Cloud Translation V2.
 */
class GoogleTranslationApiV2 implements TranslationApiInterface {

  /**
   * Google\Cloud\Translate\V3\TranslationServiceClient definition.
   *
   * @var Google\Cloud\Translate\V3\TranslationServiceClient
   */
  private $translateClient;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $config = \Drupal::config('auto_node_translate.config');
    $key = $config->get('google_api_key');
    $referer = $config->get('google_referer');
    $options = [
      'key' => $key,
    ];
    if (!empty($referer)) {
      $options['restOptions']['headers']['referer'] = $referer;
    }
    $this->translateClient = new TranslateClient($options);
  }

  /**
   * {@inheritdoc}
   */
  public function translate($text, $languageFrom, $languageTo, $customTranslations = []) {
    $text = $this->removeUntranslatable($text, $customTranslations);
    $text = $this->removeCustomTranslations($text, $customTranslations, $languageTo);
    $options = [
      'target' => $languageTo,
      'source' => $languageFrom,
    ];
    $contents = [$text];
    try {
      $response = $this->translateClient->translate($contents, $options);
      $translation = $response['text'];

      $translation = $this->addUntranslatable($translation, $customTranslations);
      $translation = $this->addCustomTranslations($translation, $customTranslations, $languageTo);
    }
    catch (\Throwable $th) {
      $error = json_decode($th->getMessage());
      \Drupal::messenger()->addError($this->t('Error @code: @message', [
        '@code' => $error->error->code,
        '@message' => $error->error->message,
      ]));
    }

    return (empty($translation)) ? $text : $translation;
  }

  /**
   * Removes Untranslatable.
   *
   * @param mixed $text
   *   The text.
   * @param mixed $customTranslations
   *   The custom translation.
   *
   * @return mixed
   *   The returned text.
   */
  public function removeUntranslatable($text, $customTranslations) {
    if (isset($customTranslations['untrans'])) {
      foreach ($customTranslations['untrans'] as $key => $value) {
        $text = str_replace($key, $value, $text);
      }
    }
    return $text;
  }

  /**
   * Adds Untranslatable.
   *
   * @param mixed $translatedText
   *   The translated text.
   * @param mixed $customTranslations
   *   The custom translation.
   *
   * @return mixed
   *   The returned translated text.
   */
  public function addUntranslatable($translatedText, $customTranslations) {
    if (!empty($customTranslations['untrans'])) {
      foreach ($customTranslations['untrans'] as $key => $value) {
        $translatedText = str_replace($value, $key, $translatedText);
      }
    }
    return $translatedText;
  }

  /**
   * Removes Custom Translations.
   *
   * @param mixed $text
   *   The text.
   * @param mixed $customTranslations
   *   The custom translation.
   * @param mixed $languageTo
   *   The language to translate to.
   *
   * @return mixed
   *   The returned translated text.
   */
  public function removeCustomTranslations($text, $customTranslations, $languageTo) {
    if (isset($customTranslations[$languageTo])) {
      foreach ($customTranslations[$languageTo] as $key => $value) {
        $text = str_replace($key, $value['untrans'], $text);
      }
    }
    return $text;
  }

  /**
   * Adds Custom Translations.
   *
   * @param mixed $translatedText
   *   The text.
   * @param mixed $customTranslations
   *   The custom translation.
   * @param mixed $languageTo
   *   The language to translate to.
   *
   * @return mixed
   *   The returned translated text.
   */
  public function addCustomTranslations($translatedText, $customTranslations, $languageTo) {
    if (isset($customTranslations[$languageTo])) {
      foreach ($customTranslations[$languageTo] as $value) {
        $translatedText = str_replace($value['untrans'], $value['to'], $translatedText);
      }
    }
    return $translatedText;
  }

}

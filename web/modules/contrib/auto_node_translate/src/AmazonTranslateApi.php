<?php

namespace Drupal\auto_node_translate;

use Aws\Translate\TranslateClient;
use Aws\Exception\AwsException;

/**
 * Description of AmazonTranslateApi.
 *
 * Implementation of TranslationApiInterface for Amazon Translate.
 */
class AmazonTranslateApi implements TranslationApiInterface {

  /**
   * Aws\Translate\TranslateClient definition.
   *
   * @var Aws\Translate\TranslateClient
   */
  private $translateClient;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $config = \Drupal::config('auto_node_translate.config');
    $key = $config->get('amazon_translate_key');
    $secret = $config->get('amazon_translate_secret');
    $region = $config->get('amazon_translate_region');
    $options = [
      'version' => 'latest',
      'region'  => $region,
      'credentials' => [
        'key' => $key,
        'secret' => $secret,
      ],
    ];
    $this->translateClient = new TranslateClient($options);
  }

  /**
   * {@inheritdoc}
   */
  public function translate($text, $languageFrom, $languageTo, $customTranslations = []) {

    $text = $this->removeUntranslatable($text, $customTranslations);
    $text = $this->removeCustomTranslations($text, $customTranslations, $languageTo);

    $options = [
      'SourceLanguageCode' => $languageFrom,
      'TargetLanguageCode' => $languageTo,
      'Text' => $text,
    ];

    try {
      $result = $this->translateClient->translateText($options);
      $translation = $result['TranslatedText'];

      $translation = $this->addUntranslatable($translation, $customTranslations);
      $translation = $this->addCustomTranslations($translation, $customTranslations, $languageTo);
    }
    catch (AwsException $e) {
      $error = $e->getMessage();
      \Drupal::messenger()->addError($this->t('Error @code: @message', [
        '@code' => $error->error->code,
        '@message' => $error->error->message,
      ]));
    }

    // Pause between translation requests to prevent
    // API flood protection.
    usleep(750);

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

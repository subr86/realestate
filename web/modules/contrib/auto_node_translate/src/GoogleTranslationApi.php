<?php

namespace Drupal\auto_node_translate;

use Google\Cloud\Translate\V3\TranslationServiceClient;
use Drupal\file\Entity\File;

/**
 * Description of GoogleTranslationAPI.
 *
 * Implementation of TranslationApiInterface for Google Cloud Translation V3.
 */
class GoogleTranslationApi implements TranslationApiInterface {

  /**
   * Google\Cloud\Translate\V3\TranslationServiceClient definition.
   *
   * @var Google\Cloud\Translate\V3\TranslationServiceClient
   */
  private $translationServiceClient;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $config = \Drupal::config('auto_node_translate.config');
    $credentials = $config->get('google_credentials');
    $credentialsFile = File::load($credentials[0]);
    $absolute_path = \Drupal::service('file_system')->realpath($credentialsFile->getFileUri());
    $options = [
      'credentials' => $absolute_path,
    ];
    $this->translationServiceClient = new TranslationServiceClient($options);
  }

  /**
   * {@inheritdoc}
   */
  public function translate($text, $languageFrom, $languageTo, $customTranslations = []) {
    $text = $this->removeUntranslatable($text, $customTranslations);
    $text = $this->removeCustomTranslations($text, $customTranslations, $languageTo);
    $config = \Drupal::config('auto_node_translate.config');
    $projectId = $config->get('google_api_project');
    $location = $config->get('google_location');
    if (strlen($text) > 20000) {
      return $this->translate(substr($text, 0, 20000), $languageFrom, $languageTo, $customTranslations) . $this->translate(substr($text, 20000), $languageFrom, $languageTo, $customTranslations);
    }
    else {
      try {
        $contents = [$text];
        $targetLanguageCode = $languageTo;
        $formattedParent = $this->translationServiceClient->locationName($projectId, $location);
        $options = [
          'sourceLanguageCode' => 'en',
        ];
        $response = $this->translationServiceClient->translateText($contents, $targetLanguageCode, $formattedParent, $options);
        $translation = $response->getTranslations()->offsetGet(0)->getTranslatedText();

        $translation = $this->addUntranslatable($translation, $customTranslations);
        $translation = $this->addCustomTranslations($translation, $customTranslations, $languageTo);
      }
      catch (\Throwable $th) {
        $error = json_decode($th->getMessage());
        \Drupal::messenger()->addError($this->t('Error @code: @message', [
          '@code' => $error->code,
          '@message' => $error->message,
        ]));
      }
      finally {
        $this->translationServiceClient->close();
      }
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

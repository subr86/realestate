<?php

namespace Drupal\auto_node_translate;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Url;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Description of IbmWatsonTranslationApi.
 *
 * Implementation of TranslationApiInterface for IBM Watson.
 */
class IbmWatsonTranslationApi implements TranslationApiInterface {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function translate($text, $languageFrom, $languageTo, $customTranslations = []) {
    // Elements to untranslate.
    $text = $this->removeUntranslatable($text, $customTranslations);
    // Custom translation.
    $text = $this->removeCustomTranslations($text, $customTranslations, $languageTo);
    $config = \Drupal::config('auto_node_translate.config');
    $from = explode('-', $languageFrom)[0];
    $to = explode('-', $languageTo)[0];
    $apikey = $config->get('ibm_watson_apikey');
    $url = $config->get('ibm_watson_url');
    if ($this->modelIsAvailable($from, $to)) {
      $client = \Drupal::httpClient();
      try {
        $response = $client->post($url . '/v3/translate?version=' . $config->get('ibm_watson_version'), [
          'auth' => ['apikey', $apikey],
          'json' => [
            'text' => [$text],
            'model_id' => $from . '-' . $to,
          ],
          'timeout' => 600,
          'headers' => [
            'Content-type' => 'application/json',
          ],
        ]);
      }
      catch (BadResponseException $e) {
        $link = Url::fromRoute('auto_node_translate.config_form', [], ['absolute' => TRUE])->toString();
        \Drupal::messenger()->addStatus($this->t('Watson translator error @error .Try another Api in <a href="@link">@link</a>', [
          '@link' => $link,
          '@error' => $e->getMessage(),
        ]));
        return $text;
      }
      $formatedData = Json::decode($response->getBody()->getContents());
      if (isset($formatedData['translations'])) {
        $translatedText = $formatedData['translations'][0]['translation'];
      }
      else {
        $translatedText = $text;
        \Drupal::messenger()->addStatus($this->t('The translation failed for @text', ['@text' => $text]));
      }
    }
    else {
      $link = Url::fromRoute('auto_node_translate.config_form', [], ['absolute' => TRUE])->toString();
      $placeholders = [
        '@link' => $link,
        '@from' => $from,
        '@to' => $to,
      ];
      \Drupal::messenger()->addStatus($this->t("The model @from-@to isn't available in Watson. Try a different api in <a href=@link>@link</a> or try to translate form english as original language", $placeholders));
      $translatedText = $text;
    }
    \Drupal::messenger()->addStatus($this->t('Translation Completed'));
    $translatedText = $this->addUntranslatable($translatedText, $customTranslations);
    $translatedText = $this->addCustomTranslations($translatedText, $customTranslations, $languageTo);
    return $translatedText;
  }

  /**
   * {@inheritdoc}
   */
  public function modelIsAvailable($languageFrom, $languageTo) {
    $config = \Drupal::config('auto_node_translate.config');
    $from = explode('-', $languageFrom)[0];
    $to = explode('-', $languageTo)[0];
    $client = \Drupal::httpClient();
    $response = $client->get($config->get('ibm_watson_url') . '/v3/models?version=' . $config->get('ibm_watson_version'), [
      'auth' => ['apikey', $config->get('ibm_watson_apikey')],
      'headers' => [
        'Content-type' => 'application/json',
      ],
      'verify' => FALSE,
    ]);
    $formatedData = Json::decode($response->getBody()->getContents());
    $models = $formatedData['models'];
    $modelExists = FALSE;
    foreach ($models as $model) {
      if ($model['model_id'] == $from . '-' . $to) {
        $modelExists = TRUE;
      }
    }
    return $modelExists;
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

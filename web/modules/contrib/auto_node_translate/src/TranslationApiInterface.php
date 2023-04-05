<?php

namespace Drupal\auto_node_translate;

/**
 * TranslationApi Interface.
 */
interface TranslationApiInterface {

  /**
   * Translates Text.
   *
   * @param string $text
   *   - The text to translate.
   * @param string $from
   *   - Langcode of original language.
   * @param string $to
   *   - Langcode of destination language.
   * @param mixed $customTranslations
   *   - Custom Translations array.
   *
   * @return string
   *   - the translated string
   */
  public function translate($text, $from, $to, $customTranslations = []);

}

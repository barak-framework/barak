<?php

class ApplicationI18n {

  private $_locale;
  private $_words;

  private function __construct($default) {
    $this->_locale  = null;
    $this->_words   = ApplicationConfig::i18n($default);
    return $this;
  }

  public static function init($default = "tr") {
    if (isset($_SESSION[self::storage_key()])) {
      if ($_SESSION[self::storage_key()]->_locale)
        return;
    }
    $_SESSION[self::storage_key()] = new ApplicationI18n($default);
  }

  public static function locale($locale = "tr") {
    $_SESSION[self::storage_key()]->_locale = $locale;
    $_SESSION[self::storage_key()]->_words = ApplicationConfig::i18n($locale);
  }

  public static function translate($_word) {
    $words = explode(".", $_word);
    $current_words = [];
    foreach ($words as $word)
      $current_words = ($current_words == []) ? self::get_first_word($word) : $current_words[$word];
    return $current_words;
  }

  private static function get_first_word($word) {
    $words = $_SESSION[self::storage_key()]->_words;

    if (!isset($words[$word]))
      throw new I18nNotFoundException("Yerel ayar dosyasında böyle bir kelime mevcut değil", $word);
    return $words[$word];
  }

  private static function storage_key() {
    return '_i18n';
  }
}
?>
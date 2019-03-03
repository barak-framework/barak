<?php

class ApplicationI18n {

  private $_locale;
  private $_words;

  private function __construct($locale) {
    $this->_locale = $locale;
    $this->_words = ApplicationConfig::i18n($locale);
    return $this;
  }

  public static function init($locale) {
    if (isset($_SESSION[self::storage_key()])) {
      if ($_SESSION[self::storage_key()]->_locale)
        return;
    }
    $_SESSION[self::storage_key()] = new ApplicationI18n($locale);
  }

  public static function locale($locale) {
    $_SESSION[self::storage_key()]->_locale = $locale;
    $_SESSION[self::storage_key()]->_words = ApplicationConfig::i18n($locale);
  }

  public static function get_locale() {
    return $_SESSION[self::storage_key()]->_locale;
  }

  public static function translate($words) {
    $array_words = explode(".", $words);
    $reply_words = [];
    foreach ($array_words as $word)
      $reply_words = ($reply_words == []) ? self::get_first_word($word) : $reply_words[$word];
    return $reply_words;
  }

  private static function get_first_word($word) {
    $words = $_SESSION[self::storage_key()]->_words;

    if (!isset($words[$word]))
      throw new Exception("Yerel ayar dosyasında böyle bir kelime mevcut değil → " . $word);
    return $words[$word];
  }

  private static function storage_key() {
    return '_i18n';
  }

}
?>

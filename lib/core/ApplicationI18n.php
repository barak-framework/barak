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
    if (isset($_SESSION[self::_storage_key()])) {
      if ($_SESSION[self::_storage_key()]->_locale)
        return;
    }
    $_SESSION[self::_storage_key()] = new ApplicationI18n($locale);
  }

  public static function locale($locale) {
    $_SESSION[self::_storage_key()]->_locale = $locale;
    $_SESSION[self::_storage_key()]->_words = ApplicationConfig::i18n($locale);
  }

  public static function get_locale() {
    return $_SESSION[self::_storage_key()]->_locale;
  }

  public static function translate($words, $locals = null) {
    if ($words[0] == ".") { // lazy! tembel!
      $request_template = str_replace("/", ".", ApplicationView::$main_template);
      $request_template_title = $request_template . $words;
      return self::translate($request_template_title, $locals);
    } else {
      $array_words = explode(".", $words);
      $reply_words = [];
      foreach ($array_words as $word)
        $reply_words = ($reply_words == []) ? self::_first_word($word) : $reply_words[$word];

      if ($locals) {
        foreach ($locals as $key => $value)
          $reply_words = str_replace("{{$key}}", $value, $reply_words);
      }
      return $reply_words;
    }
  }

  private static function _first_word($word) {
    $words = $_SESSION[self::_storage_key()]->_words;

    if (!isset($words[$word]))
      throw new Exception("Yerel dil ayar dosyasında böyle bir kelime mevcut değil → " . $word);
    return $words[$word];
  }

  private static function _storage_key() {
    return '_session_i18n';
  }

}
?>

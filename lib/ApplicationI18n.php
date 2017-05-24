<?php

class ApplicationI18n {

  const LOCALESDIR = "config/locales/";

  public $_words;
  public $default_locale;
  public $locale;

  public function __construct($_locale) {

    if (!file_exists(self::LOCALESDIR))
      throw new FileNotFoundException("Yerel ayar dizini mevcut değil", self::LOCALESDIR);

    $this->locale = null;
    $this->default_locale = $_locale;
  }

  public function __get($word) {
    if (!isset($this->_words[$word]))
      throw new I18nNotFoundException("Yerel ayar dosyasında böyle bir kelime mevcut değil", $word);

    return $this->_words[$word];
  }

  public function run() {

    $localefile = self::LOCALESDIR . (($this->locale) ? $this->locale : $this->default_locale) . ".php";

    if (!file_exists($localefile))
      throw new I18nNotFoundException("Yerel ayar dosyası mevcut değil", $localefile);

    $this->_words = include $localefile;
  }
}
?>
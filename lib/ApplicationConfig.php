<?php

class ApplicationConfig {

  const APPFILE         = "config/application.ini";
  const DATABASEFILE    = "config/database.ini";
  const ROUTESFILE      = "config/routes.php";
  const LOCALESDIR      = "config/locales/";

  // yapılandırma ayarlarını set et
  public static function sets() {

    if (!file_exists(self::APPFILE))
      throw new FileNotFoundException("Yapılandırma ayar dosyası mevcut değil", self::APPFILE);

    // for $_SESSION hash kick!
    if (!strlen(session_id())) session_start();

    // default setting
    ApplicationI18n::default();
    ApplicationLogger::size();

    // configuration setting
    $app = parse_ini_file(self::APPFILE);
    foreach ($app as $key => $value) {
      switch ($key) {
        case "timezone":  date_default_timezone_set($value); break;
        case "errors":    ini_set("display_errors", $value); break;
        case "locale":    ApplicationI18n::default($value);   break;
        case "logsize":   ApplicationLogger::size($value);      break;
        default:
        throw new ConfigurationException("Uygulamanın yapılandırma dosyasında bilinmeyen parametre", $key);
      }
    }
  }

  // veritabanı ayar dosyasını oku
  public static function database() {

    if (!file_exists(self::DATABASEFILE))
      throw new FileNotFoundException("Veritabanı ayar dosyası mevcut değil", self::DATABASEFILE);

    return parse_ini_file(self::DATABASEFILE);
  }

  // router dosyasını oku
  public static function route() {

    if (!file_exists(self::ROUTESFILE))
      throw new FileNotFoundException("Yönlendirme ayar dosyası mevcut değil", self::ROUTESFILE);

    // configuration routes load and route action dispatch
    include self::ROUTESFILE;
  }

  // yerel ayar dosyasını oku
  public static function i18n($locale) {

    $localefile = self::LOCALESDIR . $locale . ".php";
    if (!file_exists($localefile))
      throw new FileNotFoundException("Yerel ayar dosyası mevcut değil", $localefile);

    $words = include $localefile;
    return $words;
  }
}

?>
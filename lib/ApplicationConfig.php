<?php

class ApplicationConfig {

  const APPFILE      = "config/application.ini";
  const DATABASEFILE = "config/database.ini";
  const ROUTEFILE    = "config/routes.php";
  const LOCALEDIR    = "config/locales/";

  // public function __construct() {}

  public static function run() {

    if (!file_exists(self::APPFILE))
      throw new FileNotFoundException("Yapılandırma ayar dosyası mevcut değil", self::APPFILE);

    $app = parse_ini_file(self::APPFILE);

    foreach ($app as $key => $value) {
      switch ($key) {
        case "time_zone":      date_default_timezone_set($value); break;
        case "display_errors": ini_set("display_errors", $value); break;
        default:
        throw new ConfigurationException("Uygulamanın yapılandırma dosyasında bilinmeyen parametre", $key);
      }
    }

    // for $_SESSION hash
    if (!strlen(session_id())) session_start();
  }

  public static function database() {

    if (!file_exists(self::DATABASEFILE))
      throw new FileNotFoundException("Yapılandırma ayar dosyası mevcut değil", self::DATABASEFILE);

    return parse_ini_file(self::DATABASEFILE);
  }

  public static function route() {

    if (!file_exists(self::ROUTEFILE))
      throw new FileNotFoundException("Yönlendirme ayar dosyası mevcut değil", self::ROUTEFILE);

    // configuration routes load and route action dispatch
    include self::ROUTEFILE;
  }
}

?>
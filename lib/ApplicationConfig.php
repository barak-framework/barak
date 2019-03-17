<?php

class ApplicationConfig {

  const APPFILE      = "config/application.ini";
  const DATABASEFILE = "config/database.ini";
  const LOGGERFILE   = "config/logger.ini";
  const MAILERFILE   = "config/mailer.ini";
  const ROUTESFILE   = "config/routes.php";
  const LOCALESDIR   = "config/locales/";

  // genel yapılandırma ayarlarını set et
  public static function sets() {

    if (!file_exists(self::APPFILE))
      throw new Exception("Uygulama yapılandırma ayar dosyası mevcut değil → " . self::APPFILE);

    // for $_SESSION hash kick!
    if (!strlen(session_id())) session_start();

    // DEFAULT SETTINGS - start

    // DEBUG: public/500.html veya hata kodlarını gösterme ayarı, hata kodlarını gösterme
    ApplicationDebug::init(false);

    // COOKIE: httponly ile JS'in ilgili cookie'yi okuması engelleme ayarı, JS'yi engelle
    ini_set('session.cookie_httponly', 1);

    // DEFAULT SETTINGS - end

    // configuration setting
    $app_configuration = parse_ini_file(self::APPFILE);
    foreach ($app_configuration as $key => $value) {
      switch ($key) {
        case "timezone":               date_default_timezone_set($value);          break;
        case "debug":                  ApplicationDebug::init($value);             break;
        case "locale":                 ApplicationI18n::init($value);              break;
        case "cacheexpiration":        ApplicationCache::expiration($value);       break;
        case "session.cookiehttponly": ini_set("session.cookie_httponly", $value); break;
        default:
        throw new Exception("Uygulama yapılandırma dosyasında bilinmeyen parametre → " . $key);
      }
    }

    // I18n: varsayılan olan config/locales/tr.php dosyasını oku
    ApplicationI18n::init("tr");
  }

  // veritabanı ayar dosyasını oku
  public static function database() {

    if (!file_exists(self::DATABASEFILE))
      throw new Exception("Veritabanı ayar dosyası mevcut değil → " . self::DATABASEFILE);

    return parse_ini_file(self::DATABASEFILE);
  }

  // log ayar dosyasını oku
  public static function logger() {

    if (!file_exists(self::LOGGERFILE))
      throw new Exception("Logger ayar dosyası mevcut değil → " . self::LOGGERFILE);

    return parse_ini_file(self::LOGGERFILE);
  }

  // mail ayar dosyasını oku
  public static function mailer() {

    if (!file_exists(self::MAILERFILE))
      throw new Exception("Mailer ayar dosyası mevcut değil → " . self::MAILERFILE);

    return parse_ini_file(self::MAILERFILE);
  }

  // router dosyasını oku
  public static function route() {

    if (!file_exists(self::ROUTESFILE))
      throw new Exception("Yönlendirme ayar dosyası mevcut değil → " . self::ROUTESFILE);

    // configuration routes load in this file
    include self::ROUTESFILE;
  }

  // yerel ayar dosyasını oku
  public static function i18n($locale) {

    $localefile = self::LOCALESDIR . $locale . ".php";
    if (!file_exists($localefile))
      throw new Exception("Yerel ayar dosyası mevcut değil → " . $localefile);

    $words = include $localefile;
    return $words;
  }

}
?>

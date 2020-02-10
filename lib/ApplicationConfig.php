<?php

class ApplicationConfig {

  // kernel configuration files
  const APPFILE      = "config/application.php";
  const ROUTESFILE   = "config/routes.php";
  const LOCALESPATH  = "config/locales/";

  // modules configuration files
  const DATABASEFILE = "config/database.ini";
  const CACHERFILE   = "config/cacher.ini";
  const MAILERFILE   = "config/mailer.ini";

  // genel yapılandırma ayarlarını dahil et
  public static function application() {

    if (!file_exists(self::APPFILE))
      exit("Uygulama yapılandırma ayar dosyası mevcut değil → " . self::APPFILE);

    // configuration application load in this file
    include self::APPFILE;
  }

  // routes yapılandırma ayarlarını dahil et
  public static function routes() {

    if (!file_exists(self::ROUTESFILE))
      throw new Exception("Yönlendirme ayar dosyası mevcut değil → " . self::ROUTESFILE);

    // configuration routes load in this file
    include self::ROUTESFILE;
  }

  // yerel dil yapılandırma ayarlarını oku ve dön
  public static function i18n($locale) {

    $locale_file_path = self::LOCALESPATH . $locale . ".php";
    if (!file_exists($locale_file_path))
      throw new Exception("Yerel dil ayar dosyası mevcut değil → " . $locale_file_path);

    $words = include $locale_file_path;
    return $words;
  }

  // veritabanı ayar dosyasını oku ve dön
  public static function database() {

    if (!file_exists(self::DATABASEFILE))
      throw new Exception("Veritabanı ayar dosyası mevcut değil → " . self::DATABASEFILE);

    return parse_ini_file(self::DATABASEFILE);
  }

  // cacher ayar dosyasını oku ve dön
  public static function cacher() {

    if (!file_exists(self::CACHERFILE))
      throw new Exception("Cacher ayar dosyası mevcut değil → " . self::CACHERFILE);

    return parse_ini_file(self::CACHERFILE);
  }

  // mailer ayar dosyasını oku ve dön
  public static function mailer() {

    if (!file_exists(self::MAILERFILE))
      throw new Exception("Mailer ayar dosyası mevcut değil → " . self::MAILERFILE);

    return parse_ini_file(self::MAILERFILE);
  }

}
?>

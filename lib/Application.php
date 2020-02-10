<?php

class Application {

  private static $_configutaion = NULL;

  public static $options = [

  // application kernel options overwrite
  // Çekirdek uygulama ayarları zaten varsayılan olarak değerleri yüklüdür.
  // Eğer üzerine yazmak isteniyorsa config/application.php içerisinde set fonksiyonunda belirtilmelidir.
  "timezone" => "Europe/Istanbul",
  "locale" => "tr",
  "debug" => true,
  "logger" => ["file" => "production", "level" => "info", "driver" => "weekly", "rotate" => 4, "size" => 5242880],

  // application modules default status = true/false
  // Uygulama modül ayarlarında aşağıdaki tüm modüller, yüklenmeyecek şekilde gelecektir.
  // Eğer gelmesi isteniyorsa config/application.php içerisinde modules fonksiyonunda belirtilmelidir.

  "cacher" => false,
  "mailer" => false,
  "model" => false,
  "http" => false
  ];

  public static function set($key, $value) {

    if (!array_key_exists($key, self::$options))
      throw new Exception("Application yapılandırma dosyasında bilinmeyen parametre → " . $key);

    self::$options[$key] = $value;
  }

  public static function config(callable $_functions) {
    // yapılandırma dosyasını bu fonkiyon ne kadar çağrılırsa çağrılsın sadece bir defa oku!
    if (self::$_configutaion == NULL) {

      // config processing
      $_functions();
      // config processed

      // bir daha ::config fonksiyonu çağrılmaması için
      self::$_configutaion = TRUE;
    }
  }

}
?>

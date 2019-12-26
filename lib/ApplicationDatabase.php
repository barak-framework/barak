<?php

class ApplicationDatabase {

  const SEEDSFILE  = "db/seeds.php";

  private static $_configuration = NULL;

  public static function connect() {

    // yapılandırma dosyasını bu fonkiyon ne kadar çağrılırsa çağrılsın sadece bir defa oku!
    if (self::$_configuration == NULL) {

      // load database.ini with check ApplicationConfig
      extract(ApplicationConfig::database());

      if ($adapter) {
        try {
          self::$_configuration = new PDO("{$adapter}:host={$hostname};dbname={$database}", $username, $password);

          // configuration database
          self::$_configuration->query("set names 'utf8'");
          self::$_configuration->query("set character set 'utf8'");
          self::$_configuration->query("set collation_configuration = 'utf8_general_ci'");
          self::$_configuration->query("set collation-server = 'utf8_general_ci'");
          self::$_configuration->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch (PDOException $e) {
          throw new Exception("Veritabanı bağlantısı başarılı değil! → " . $e->getMessage());
        }
      }
    }

    return self::$_configuration;
  }

  public static function close() {
    if (isset(self::$_configuration))
      self::$_configuration = null;
  }

  public static function seed() {
    if (file_exists(self::SEEDSFILE))
      include self::SEEDSFILE;
  }

}
?>

<?php

class ApplicationDatabase {

  const SEEDSFILE  = "db/seeds.php";

  private static $_connection = NULL;

  public static function connect() {

    // load database.ini with check ApplicationConfig
    extract(ApplicationConfig::database());

    try {
      if (!isset(self::$_connection)) {
        self::$_connection = new PDO("mysql:host={$host};dbname={$name}", $user, $pass);

        // configuration database
        self::$_connection->query('set names "utf8"');
        self::$_connection->query('set character set "utf8"');
        self::$_connection->query('set collation_connection = "utf8_general_ci"');
        self::$_connection->query('set collation-server = "utf8_general_ci"');
        self::$_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      }
    } catch (PDOException $e) {
      throw new DatabaseException("Veritabanına bağlantısı başarılı değil!", $e->getMessage());
    }
    return self::$_connection;
  }

  public static function close() {
    if (isset(self::$_connection))
      self::$_connection = null;
  }

  public static function seed() {
    if (file_exists(self::SEEDSFILE))
      include self::SEEDSFILE;
  }
}
?>
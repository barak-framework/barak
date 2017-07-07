<?php

class ApplicationDatabase extends PDO {

  const SEEDSFILE  = "db/seeds.php";

  public function __construct() {

    // load database.ini with check ApplicationConfig
    extract(ApplicationConfig::database());

    try {
      parent::__construct("mysql:host={$host};dbname={$name}", $user, $pass);
    } catch (PDOException $e) {
      throw new DatabaseException("Veritabanına bağlantısı başarılı değil!", $e->getMessage());
    }

    parent::query('set names "utf8"');
    parent::query('set character set "utf8"');
    parent::query('set collation_connection = "utf8_general_ci"');
    parent::query('set collation-server = "utf8_general_ci"');

    $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $this;
  }

  public static function seed() {
    if (file_exists(self::SEEDSFILE))
      include self::SEEDSFILE;
  }
}

?>
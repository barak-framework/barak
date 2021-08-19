<?php

class ApplicationLogger {

  const LOGGERPATH = "tmp/log/";

  const LEVELNAMES = ["info" => 1, "warning" => 2, "error" => 3, "fatal" => 4, "debug" => 5];
  const DRIVERNAMES = ["daily" => 1, "weekly" => 7, "montly" => 30, "yearly" => 365];

  private static $_configuration = NULL;

  // configuration variables
  private static $_level = 1;           // 1 = info     // info level
  private static $_driver = 7;          // 7 = weekly   // weekly log
  private static $_file = "production"; // "production" // log file name
  private static $_size = 5242880;      // 5242880      // 5 MB = 5 * 1024 * 1024
  private static $_rotate = 4;          // 4            // 4 backup file

  // main log path, created_at
  private static $_file_path = null;
  private static $_file_created_at = null;

  private static function _loggerpath() {
    return $_SERVER["DOCUMENT_ROOT"] . "/" . self::LOGGERPATH;
  }

  public static function init($options) {
    // yapılandırma dosyasını bu fonkiyon ne kadar çağrılırsa çağrılsın sadece bir defa oku!
    if (self::$_configuration == NULL) {

      foreach ($options as $key => $value) {
        switch ($key) {
          case "file": self::$_file = $value; break;
          case "level":
          if (!array_key_exists($value, self::LEVELNAMES))
            throw new Exception("Logger kullanımı için bilinmeyen level → " . $value);
          self::$_level = self::LEVELNAMES[$value];
          break;
          case "driver":
          if (!array_key_exists($value, self::DRIVERNAMES))
            throw new Exception("Logger kullanımı için bilinmeyen sürücü → " . $value);
          self::$_driver = self::DRIVERNAMES[$value];
          break;
          case "rotate": self::$_rotate = intval($value); break;
          case "size": self::$_size = intval($value); break;
          default:
          throw new Exception("Logger kullanımı için bilinmeyen parametre → " . $key);
        }
      }

      // self::$_file isminde log dosyası
      // eğer yok ise : yeni log dosyası oluştur ve self::$_file_path, self::$_file_created_at değişkenlerini ata
      // eğer var ise : self::$_file_path, self::$_file_created_at değişkenlerini ata
      self::_create();

      // bir daha ::init fonksiyonu çağrılmaması için
      self::$_configuration = TRUE;
    }
  }

  public static function __callStatic($level, $messages) {

    if (!array_key_exists($level, self::LEVELNAMES))
      throw new Exception("Logger kullanımı için bilinmeyen method → " . $level);

    // level yazmaya uygun mu bak
    if (self::$_level <= self::LEVELNAMES[$level]) {

      if (self::$_driver <= self::_expire()) {

        // sürücü süresi dolmuşsa log dosyasını döndür
        self::_rotate();

      } else if (self::$_size < filesize(self::$_file_path)) {

        // boyut aşılmışsa da log dosyasını döndür
        self::_rotate();

      }

      if (!($fh = fopen(self::$_file_path, 'a')))
        throw new Exception("Log dosyası ekleme yapılmak için açılamadı → " . self::$_file_path);

      $message = strval($messages[0]);

      fwrite($fh, $message . "\n");
      fclose($fh);
    }
  }

  private static function _create() {

    if (!(list($_file_path, $_file_created_at) = self::_exists(self::$_file))) {

      // yeni ana log için oluşturma tarihi ve path ata
      // self::$_file_created_at → oluşturma tarihi
      self::$_file_created_at = date("Y-m-d");
      // self::$_file_path → path, open_basedir sorunu yüzünden $_SERVER["DOCUMENT_ROOT"] yazılmak zorunda
      self::$_file_path = self::_loggerpath() . self::$_file . "_" . self::$_file_created_at . ".log";

      if (!($fh = fopen(self::$_file_path, 'w')))
        throw new Exception("Log dosyası oluşturulmak için açılamadı → " . self::$_file_path);

      fwrite($fh, ""); // boş yaz yani sadece dokun, geç.
      fclose($fh);
    } else {
    	self::$_file_created_at = $_file_created_at;
    	self::$_file_path = $_file_path;
    }
  }

  // self::$_file = FILE
  // $_files =
  // FILE_YYYY-MM-DD.log
  // FILE@1_YYYY-MM-DD.log
  // FILE@2_YYYY-MM-DD.log
  // returned
  // ["/tmp/var/log/FILE_YYYY-MM-DD.log", "YYYY-MM-DD"];
  // ["/tmp/var/log/FILE@1_YYYY-MM-DD.log", "YYYY-MM-DD"];
  // ["/tmp/var/log/FILE@2_YYYY-MM-DD.log", "YYYY-MM-DD"];

  private static function _exists($file) {

    $_files = scandir(self::_loggerpath());

    $_matchs = [];
    foreach ($_files as $_file) {

      if (preg_match("/^(.*?)_([0-9]{4}-[0-9]{2}-[0-9]{2}).log$/si", $_file, $_match)) {
        if ($_match[1] == $file) {
          $_matchs[] = [self::_loggerpath() . $_match[0], $_match[2]];
        }
      }
    }
    $_matchs_count = count($_matchs);
    if ($_matchs_count > 1)
      throw new Exception("Log dosyasının benzerleri mevcut → " . $file);
    else if ($_matchs_count == 1)
      return $_matchs[0];
    else
      return false;
  }

  // self::$_file = FILE
  // returned
  // [
  //   "1" => "/tmp/var/log/FILE@1_YYYY-MM-DD.log",
  //   "2" => "/tmp/var/log/FILE@2_YYYY-MM-DD.log"
  //   "3" => "/tmp/var/log/FILE@3_YYYY-MM-DD.log"
  // ]

  private static function _backups() {

    $_files = scandir(self::_loggerpath());

    $_file_path_backups = [];
    foreach ($_files as $_file) {

      if (preg_match("/^" . self::$_file . "@" . "(.*?)". "_([0-9]{4}-[0-9]{2}-[0-9]{2}).log$/si", $_file, $_match)) {
        if (array_key_exists($_match[1], $_file_path_backups))
          throw new Exception("Yedek Log dosyasının benzerleri mevcut → " . $file);
        else
          $_file_path_backups[$_match[1]] = self::_loggerpath() . $_match[0];
      }
    }

    return $_file_path_backups;
  }

  private static function _expire() {

    // now date and file _created_at time diff
    $_diff_sec = strtotime(date("Y-m-d")) - strtotime(self::$_file_created_at);

    // 1 day = 24 hours
    // 1 day = 24 * 60 * 60 = 86400 seconds
    $_diff_day = abs(round($_diff_sec / 86400));

    // şuan ile ana log dosyanın oluşturma zamanının farkı gün sayısını dön
    return $_diff_day;
  }

  private static function _rotate() {

    // en son yedek varsa sil
    $_file_backup_end = self::$_file . "@" . self::$_rotate;
    if ((list($_file_path_backup_end, $_file_created_at_backup_end) = self::_exists($_file_backup_end))) {
      unlink($_file_path_backup_end);
    }

    // yedekleri al
    $_file_path_backups = self::_backups();

    // yedek dosya numarasına göre ters sırala
    // son yedekten(keyden) başlamak üzere taşımaya başla ki birbirinin üzerine yazma olmasın
    // [
    //   "3" => "/tmp/var/log/FILE@3_YYYY-MM-DD.log",
    //   "2" => "/tmp/var/log/FILE@2_YYYY-MM-DD.log",
    //   "1" => "/tmp/var/log/FILE@1_YYYY-MM-DD.log"
    // ]

    krsort($_file_path_backups);
    foreach ($_file_path_backups as $_file_index => $_file_path_backup) {

      $_file_path_backup_before = $_file_path_backup;
      if (file_exists($_file_path_backup_before)) {
        $_file_path_backup_after = str_replace("@{$_file_index}_", "@" . ($_file_index + 1) . "_", $_file_path_backup_before);
        rename($_file_path_backup_before, $_file_path_backup_after);
      }
    }

    // ana log dosyası yazmak için bir kontrol et, yerinde mi beyfendi
    if (!self::_exists(self::$_file))
      throw new Exception("Ana Log dosyası mevcut değil → " . self::$_file);

    // ana log dosyayı(şu an log yazılan dosyayı), 1 nolu yedek dosya olarak taşı
    rename(self::$_file_path, self::_loggerpath() . self::$_file . "@1_" . self::$_file_created_at . ".log");

    // yeni log dosyası oluştur ve self::$_file_path, self::$_file_created_at değişkenlerini ata
    self::_create();
  }
}
?>

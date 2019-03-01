<?php

class ApplicationLogger {

  const LOGDIR = "tmp/log/";

  const LEVELNAMES = ["info", "warning", "error", "fatal", "debug"];

  private static $_configuration = NULL;
  private static $_size = 5242880;
  private static $_level = 0;

  public static function __callStatic($levelname, $messages) {

    // yapılandırma dosyasını bu fonkiyon ne kadar çağrılırsa çağrılsın sadece bir defa oku!
    if (self::$_configuration == NULL) {
       foreach (ApplicationConfig::logger() as $key => $value) { // optional
        switch ($key) {
          case "size":  self::$_size  = $value; break;
          case "level": self::$_level = intval($value); break;
          default:
          throw new Exception("Logger yapılandırma dosyasında bilinmeyen parametre → " . $key);
        }
       }
       self::$_configuration = TRUE;
     }

     if (!in_array($levelname, self::LEVELNAMES))
      throw new Exception("Logger kullanımı için bilinmeyen method → " . $levelname);

     // find key of array
     $levels = array_flip(self::LEVELNAMES);
     $level = $levels[$levelname];

     if (self::$_level <= $level) {

      $message = strval($messages[0]);
      $filename = self::LOGDIR . date("Y-m-d") . ".log";
      if (!($fh = fopen($filename, 'a')))
        throw new Exception("Log dosyası açılamadı → " . $filename);

      $filesize = filesize($filename);
      $logsize = self::$_size;
      if ($logsize >= $filesize)
        fwrite($fh, $message . "\n");

      fclose($fh);
     }
   }

 }
 ?>

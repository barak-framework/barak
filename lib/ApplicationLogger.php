<?php

class ApplicationLogger {

  const LOGDIR = "log/";

  private static $_levels = ["info", "warning", "error", "fatal", "debug"];

  public static function size($size = 5242880) {
    $GLOBALS[self::storage_key()] = intval($size);
  }

  public static function __callStatic($level, $messages) {

    if (!in_array($level, self::$_levels))
      throw new MethodNotFoundException("Bilinmeyen fonksiyon!", $level);

    $message = date("Y-m-d H:i:s") . " → $level : " . implode(",", $messages);

    $filename = self::LOGDIR . date("Y-m-d") . ".log";
    if (!($fh = fopen($filename, 'a')))
      throw new FileNotFoundException("Log dosyası açılamadı", $filename);

    $filesize = filesize($filename);
    $logsize = $GLOBALS[self::storage_key()];
    if ($logsize >= $filesize)
      fwrite($fh, $message . "\n");

    fclose($fh);
  }

  private static function storage_key() {
    return '_log';
  }
}
?>
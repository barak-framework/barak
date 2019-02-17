<?php

class ApplicationLogger {

  const LOGDIR = "tmp/log/";

  private static $_levels = ["info", "warning", "error", "fatal", "debug"];

  private static $_size = 5242880;

  public static function size($byte) {
    self::$_size = intval($byte);
  }

  public static function __callStatic($level, $messages) {

    if (!in_array($level, self::$_levels))
      throw new Exception("Bilinmeyen fonksiyon! → " . $level);

    $message = "[" . date("Y-m-d H:i:s") . "] $level : " . implode(",", $messages);

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
?>

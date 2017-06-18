<?php
class ApplicationLog {

  const LOGDIR = "log/";

  private static $_levels = ["debug", "info", "warning", "error", "fatal"];

  //
  // ApplicationLog::debug("sorunu buldum");
  // ApplicationLog::info("bilmek iyidir");
  // ApplicationLog::warning("olabilir?");
  // ApplicationLog::error("dikkat et");
  // ApplicationLog::fatal("cevap vermiyor");
  //

  public function __callStatic($level, $messages) {
    if (!file_exists(self::LOGDIR))
      throw new FileNotFoundException("Yerel ayar dizini mevcut değil", self::LOGDIR);

    if (in_array($level, self::$_levels)) {
      $message = date("Y-m-d H:i:s") . " → $level : " . implode(",", $messages);

      $filename = self::LOGDIR . date("Y-m-d") . ".txt";
      if (!($fh = fopen($filename, 'a')))
        throw new FileNotFoundException("Log dosyası açılamadı", $filename);

      fwrite($fh, $message . "\n");
      fclose($fh);
    } else {
      throw new ApplicationException("Bilinmeyen fonksiyon!", $level);
    }
  }
}
?>

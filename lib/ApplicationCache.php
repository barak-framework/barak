<?php

class ApplicationCache {

  const CACHEDIR = "tmp/cache/";

  private static $_expiration = 600000;

  public static function expiration($millisecond) {
    self::$_expiration = intval($millisecond);
  }

  public static function write($key, $value) {

    // struct of key
    $data = [ 'time' => time(), 'expire' => self::$_expiration, 'value' => serialize($value) ];

    $filename = self::filename_format($key);

    if (!($fh = fopen($filename, 'w')))
      throw new Exception("Cache bellek açılamadı → " . $filename);

    fwrite($fh, json_encode($data));

    fclose($fh);
  }

  public static function read($key) {

    // get filename
    $filename = self::filename_format($key);
    if (file_exists($filename)) {

      // check expire time ? get or delete
      $data = json_decode(file_get_contents($filename), true);
      if ($data["expire"] > (time() - $data["time"]))
        return unserialize($data["value"]);
      else
        self::delete($key);
    }
    return null;
  }

  public static function delete($key) {
    $filename = self::filename_format($key);
    if (file_exists($filename))
      unlink($filename);
  }

  public static function exists($key) {
    return (file_exists(self::filename_format($key))) ? true : false;
  }

  public static function reset() {
    foreach (glob(self::CACHEDIR . "*") as $filename)
      unlink($filename);
  }

  private static function filename_format($key) {
    $requesturi = preg_replace('/[^0-9a-z\.\_\-]/i', '', strtolower($_SERVER["REQUEST_URI"]));
    return self::CACHEDIR . md5($requesturi . $key);
  }
}
?>

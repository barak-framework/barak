<?php

class ApplicationCacher {

  const CACHEPATH = "tmp/cache/";

  const clear_expire = "._clear_expire_";

  private static $_configuration = NULL;

  public static function init() {
    // yapılandırma dosyasını bu fonkiyon ne kadar çağrılırsa çağrılsın sadece bir defa oku!
    if (self::$_configuration == NULL) {

      self::$_configuration = new stdClass();
      foreach (ApplicationConfig::cacher() as $key => $value) {
        switch ($key) {
          case "datas": self::$_configuration->datas = $value; break;
          case "clear": self::$_configuration->clear = $value; break;
          default:
          throw new Exception("Cacher yapılandırma dosyasında bilinmeyen parametre → " . $key);
        }
      }

      if (!isset(self::$_configuration->datas)) self::$_configuration->datas = 60;  // 1 dakika dosya süresi
      if (!isset(self::$_configuration->clear)) self::$_configuration->clear = 120; // 2 dakika genel süre

      self::_clear_write();
    }
  }

  public static function close() {
    self::_clear_read();
  }

  public static function expire($second) {
    self::$_configuration->datas = intval($second);
  }

  public static function write($key, $value) {
    $filename = self::_filename_md5($key);
    self::_write($filename, $value, self::$_configuration->datas);
  }

  public static function read($key) {
    $filename = self::_filename_md5($key);
    return self::_read($filename);
  }

  public static function delete($key) {
    $filename = self::_filename_md5($key);
    if (file_exists($filename))
      unlink($filename);
  }

  public static function exists($key) {
    return (file_exists(self::_filename_md5($key))) ? true : false;
  }

  public static function clear() {
    foreach (glob(self::CACHEPATH . "*") as $filename)
      unlink($filename);
  }

  private static function _clear_write() {
    $value = "Bu dosya " . date("Y-m-d | h m' s''") . " tarihinde oluşturuldu." .
             "Bu dosyanın yaşam süresi " .  ApplicationConfig::CACHERFILE . " dosyasında clear değişkenine saniye olarak atandı." .
             "Bu dosya yaşam süresini tamamladığında, " . self::CACHEPATH . " altındaki tüm cache dosyaları silinecek.";
    self::_write(self::CACHEPATH . self::clear_expire, $value, self::$_configuration->clear);
  }

  private static function _clear_read() {
    if (!self::_read(self::CACHEPATH . self::clear_expire))
      self::clear();
  }

  private static function _write($filename, $value, $expire) {
    if (!file_exists($filename)) {

      if (!($fh = fopen($filename, 'w')))
        throw new Exception("Cache bellek açılamadı → " . $filename);

      fwrite($fh, self::_data_json_encode($value, $expire));
      fclose($fh);
    }
  }

  private static function _read($filename) {
    if (file_exists($filename)) {

      $data = self::_data_json_decode($filename);
      if (self::_expire($data))
        unlink($filename);
      else
        return unserialize($data["value"]);
    }
    return null;
  }

  private static function _data_json_encode($value, $expire) {
    $data = ['time' => time(), 'expire' => $expire, 'value' => serialize($value)];
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  }

  private static function _data_json_decode($filename) {
    return json_decode(file_get_contents($filename), true);
  }

  private static function _expire($data) { // süre sona ermiş mi ?
    return ($data["expire"] > (time() - $data["time"])) ? false : true;
  }

  private static function _filename_md5($key) {
    return self::CACHEPATH . md5($key);
  }

}
?>

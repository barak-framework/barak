<?php

class ApplicationCacher {

  const CACHEPATH = "tmp/cache/";

  const clear_expire = "._clear_expire_";

  private static $_configuration = NULL;

  private static function _cacher_path() {
    return $_SERVER["DOCUMENT_ROOT"] . "/" . self::CACHEPATH;
  }

  private static function _file_path($key) {
    return self::_cacher_path() . md5($key) . ".cache";
  }

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
    $file_path = self::_file_path($key);
    self::_write($file_path, $value, self::$_configuration->datas);
  }

  public static function read($key) {
    $file_path = self::_file_path($key);
    return self::_read($file_path);
  }

  public static function delete($key) {
    $file_path = self::_file_path($key);
    if (file_exists($file_path))
      unlink($file_path);
  }

  public static function exists($key) {
    return (file_exists(self::_file_path($key))) ? true : false;
  }

  public static function clear() {
    foreach (glob(self::CACHEPATH . "*") as $file_name)
      unlink($file_name);
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

  private static function _write($file_path, $value, $expire) {
    if (!file_exists($file_path)) {

      if (!($fh = fopen($file_path, 'w')))
        throw new Exception("Cache dosyası açılamadı → " . $file_path);

      fwrite($fh, self::_data_json_encode($value, $expire));
      fclose($fh);
    }
  }

  private static function _read($file_path) {
    if (file_exists($file_path)) {

      $data = self::_data_json_decode($file_path);
      if (self::_expire($data))
        unlink($file_path);
      else
        return unserialize($data["value"]);
    }
    return null;
  }

  private static function _data_json_encode($value, $expire) {
    $data = ['time' => time(), 'expire' => $expire, 'value' => serialize($value)];
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  }

  private static function _data_json_decode($file_path) {
    return json_decode(file_get_contents($file_path), true);
  }

  private static function _expire($data) { // süre sona ermiş mi ?
    return ($data["expire"] > (time() - $data["time"])) ? false : true;
  }

}
?>

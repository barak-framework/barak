<?php

class ApplicationFlash {

  const FLASHKEYS = ["success", "info", "warning", "danger"];
  const FLASHFLAG = ".flag";

  public static function init() {
    if (isset($_SESSION[self::_storage_key()]))
      $_SESSION[self::_storage_key()][self::FLASHFLAG] = TRUE;
  }

  // in ApplicationController
  // $this->flash["info"] = "gökhan";
  // ApplicationFlash::sets($this->flash);

  public static function sets($options) {
    if ($options) {
    	ApplicationLogger::info(serialize($options));
      foreach ($options as $key => $value) {
        if (!in_array($key, self::FLASHKEYS))
          throw new Exception("Flash kullanımı için bilinmeyen anahtar → " . $key);

        $_SESSION[self::_storage_key()][$key] = $value;
      }
    }
  }

  // alias in ApplicationView
  // $flash = ApplicaionFlash::gets();
  // $flash variable access in templates
  // $flash["info"];

  public static function gets() {
    if (isset($_SESSION[self::_storage_key()]))
      return $_SESSION[self::_storage_key()];
    return null;
  }

  public static function close() {
    if (isset($_SESSION[self::_storage_key()][self::FLASHFLAG]))
      unset($_SESSION[self::_storage_key()]);
  }

  private static function _storage_key() {
    return '_session_flash';
  }

}
?>

<?php

class ApplicationCache {
  
  public static function set($key, $value) {}

  public static function get($key) {}

  public static function del($key) {}

  public static function has($key) {}

  public static function reset() {}

  private static function storage_key() {
    return '_cache';
  }
}
?>

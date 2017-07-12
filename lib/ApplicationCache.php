<?php

class ApplicationCache {
  public static function set() {}
  public static function get() {}
  public static function del() {}
  public static function has() {}
  public static function reset() {}
  private static function storage_key() {
    return '_cache';
  }
}
?>

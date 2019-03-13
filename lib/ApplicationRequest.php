<?php

class ApplicationRequest {

  public $ip; // requester ip
  public $rule; // request rule
  public $method; // request method
  public $datetime; // request datetime

  final public function __construct() { // genişletilemez fonksiyon
    $this->ip = self::_ip();
    $this->rule = self::_rule();
    $this->method = self::_method();
    $this->datetime = self::_datetime();
  }

  private static function _ip() {
    return $_SERVER['REMOTE_ADDR'];
  }

  private static function _rule() {
    return $_SERVER['REQUEST_URI'];
  }

  private static function _method() {
    return $_SERVER['REQUEST_METHOD'];
  }

  private static function _datetime() {
    return date("Y-m-d H:i:s");
  }

}
?>

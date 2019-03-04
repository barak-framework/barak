<?php

class ApplicationRequest {

  public $ip; // requester ip
  public $rule; // request rule
  public $method; // request method

  final public function __construct() { // genişletilemez fonksiyon
  	$this->ip = self::_ip();
  	$this->rule = self::_rule();
  	$this->method = self::_method();
  }

  private function _ip() {
  	return $_SERVER['REMOTE_ADDR'];
  }

  private function _rule() {
  	return $_SERVER['REQUEST_URI'];
  }

  private function _method() {
  	return $_SERVER['REQUEST_METHOD'];
  }

}
?>

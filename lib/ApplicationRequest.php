<?php

class ApplicationRequest {

  public $ip; // requester ip
  public $rule; // request rule
  public $method; // request method

  final public function __construct() { // genişletilemez fonksiyon
  	$this->ip = self::ip();
  	$this->rule = self::rule();
  	$this->method = self::method();
  }

  private function ip() {
  	return $_SERVER['REMOTE_ADDR'];
  }

  private function rule() {
  	return $_SERVER['REQUEST_URI'];
  }

  private function method() {
  	return $_SERVER['REQUEST_METHOD'];
  }

}
?>
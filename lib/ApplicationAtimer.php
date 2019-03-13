<?php

class ApplicationAtimer {

  private $_start = NULL;

  // alt sınıfların erişmesi için
  protected $_timer_message = "Finished";

  final public function __construct() {
    $this->_start = microtime(true);
  }

  final public function __destruct() {
    ApplicationLogger::info("$this->_timer_message" . sprintf ("(%.2f ms)", (microtime(true) - $this->_start) * 1000));
  }

}
?>

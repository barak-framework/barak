<?php

class ApplicationResponse {

  CONST STATUS =
  [
  200 => "OK",
  302 => "Found",
  404 => "Not Found",
  500 => "Internal Server Error",
  NULL => ""
  ];

  const ERRORPAGE = "public/404.html";
  const DEBUGPAGE = "public/500.html";

  private $_statuscode;
  private $_status; // status code and status text
  private $_body;

  final public function __construct($statuscode = 200, $body = NULL) { // genişletilemez fonksiyon
    $this->_statuscode = $statuscode;
    $this->_status = $statuscode . " " . self::STATUS[$statuscode];
    $this->_body = $body;
  }

  final public function send() { // genişletilemez fonksiyon

    switch ($this->_statuscode) {
      case 200:  $this->_write();     break;
      case 302:  $this->_redirect();  break; // exit
      case 404:  $this->_write_404(); break;
      case 500:  $this->_write_500(); break;
      case NULL: $this->_send_data(); break;
      default:
      throw new Exception("Yanıt vermek için bilinmeyen durum kodu → " . $this->_statuscode);
    }
  }

  final public function status() {
    return $this->_status;
  }


  private function _redirect() {
    exit(header("Location: http://" . $_SERVER['SERVER_NAME'] . "/" . trim($this->_body, "/"), FALSE, 302));
  }

  private function _write() {
    header("Content-Type: text/html; charset=utf-8");
    header("HTTP/1.1 {$this->_status}");
    echo $this->_body;
  }

  private function _write_404() {
    $v = new ApplicationView();
    ($this->_body) ? $v->set(["text" => $this->_body]) : $v->set(["file" => self::ERRORPAGE]);
    $this->_body = $v->run();
    $this->_write();
  }

  private function _write_500() {
    $v = new ApplicationView();
    ($this->_body) ? $v->set(["text" => $this->_body]) : $v->set(["file" => self::DEBUGPAGE]);
    $this->_body = $v->run();
    $this->_write();
  }

  private function _send_data() {
    list($body, $filename) = $this->_body;
    header("Content-Disposition: attachment; filename='{$filename}'", FALSE, 200);
    echo $body;
  }

}
?>
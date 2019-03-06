<?php

class ApplicationResponse {

  CONST STATUS =
  [
  200 => "OK", // doğru görüntülendi : HTML
  302 => "Found", // yönlendirme yapıldı : NULL
  404 => "Not Found", // sayfa bulunamadı : HTML
  500 => "Internal Server Error", // uygulama hata verdi : HTML
  NULL => "" // dosya indirme isteği geldi : MIX
  ];

  const ERRORPAGE = "public/404.html";
  const DEBUGPAGE = "public/500.html";

  private $_statuscode;
  private $_status; // status code and status text
  private $_header;
  private $_body;

  final public function __construct($statuscode = 200, $body = NULL) { // genişletilemez fonksiyon
    $this->_statuscode = $statuscode;
    $this->_status = $statuscode . " " . self::STATUS[$statuscode];
    $this->_body = $body;

    // default security headers:
    // source : https://guides.rubyonrails.org/security.html#default-headers
    $this->_header = [
      'X-Frame-Options' => 'SAMEORIGIN',
      'X-XSS-Protection' => '1; mode=block',
      'X-Content-Type-Options' => 'nosniff',
      'X-Download-Options' => 'noopen',
      'X-Permitted-Cross-Domain-Policies' => 'none',
      'Referrer-Policy' => 'strict-origin-when-cross-origin'
    ];
  }

  final public function status() {
    return $this->_status;
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

  private function _write() {
    header("Content-Type: text/html; charset=utf-8");
    header("HTTP/1.1 {$this->_status}");

    foreach ($this->_header as $name => $value) {
      header($name . ': ' . $value);
    }

    echo $this->_body;
  }

  private function _redirect() {
    exit(header("Location: http://" . $_SERVER['SERVER_NAME'] . "/{$this->_body}", FALSE, 302));
  }

  private function _send_data() {
    list($body, $filename, $type) = $this->_body;
    $type = ($type) ?: "application/octet-stream";
    
    foreach ($this->_header as $name => $value) {
      header($name . ': ' . $value);
    }

    header("Content-Type: $type; charset=utf-8");
    header("Content-Disposition: attachment; filename='{$filename}'", FALSE, 200);
    echo $body;
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

}
?>

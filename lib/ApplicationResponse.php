<?php

class ApplicationResponse {

  // speacial status

  // 0 for attachment file
  // 302 for location url
  // 404 for error page
  // 500 for debug page

  const STATUS =
  [
  0   => "",
  100 => "Continue",
  101 => "Switching Protocols",
  102 => "Processing",
  200 => "OK",
  201 => "Created",
  202 => "Accepted",
  203 => "Non-Authoritative Information",
  204 => "No Content",
  205 => "Reset Content",
  206 => "Partial Content",
  207 => "Multi-Status",
  226 => "IM Used",
  300 => "Multiple Choices",
  301 => "Moved Permanently",
  302 => "Found",
  303 => "See Other",
  304 => "Not Modified",
  305 => "Use Proxy",
  307 => "Temporary Redirect",
  400 => "Bad Request",
  401 => "Unauthorized",
  402 => "Payment Required",
  403 => "Forbidden",
  404 => "Not Found",
  405 => "Method Not Allowed",
  406 => "Not Acceptable",
  407 => "Proxy Authentication Required",
  408 => "Request Timeout",
  409 => "Conflict",
  410 => "Gone",
  411 => "Length Required",
  412 => "Precondition Failed",
  413 => "Request Entity Too Large",
  414 => "Request-URI Too Long",
  415 => "Unsupported Media Type",
  416 => "Requested Range Not Satisfiable",
  417 => "Expectation Failed",
  422 => "Unprocessable Entity",
  423 => "Locked",
  424 => "Failed Dependency",
  426 => "Upgrade Required",
  500 => "Internal Server Error",
  501 => "Not Implemented",
  502 => "Bad Gateway",
  503 => "Service Unavailable",
  504 => "Gateway Timeout",
  505 => "HTTP Version Not Supported",
  507 => "Insufficient Storage",
  510 => "Not Extended"
  ];

  const ERRORPAGE = "public/404.html";
  const DEBUGPAGE = "public/500.html";

  // default security headers
  // source : https://guides.rubyonrails.org/security.html#default-headers
  const DEFAULTHEADERS = [
  'X-Frame-Options' => 'SAMEORIGIN',
  'X-XSS-Protection' => '1; mode=block',
  'X-Content-Type-Options' => 'nosniff',
  'X-Download-Options' => 'noopen',
  'X-Permitted-Cross-Domain-Policies' => 'none',
  'Referrer-Policy' => 'strict-origin-when-cross-origin'
  ];

  private $_status; // status code and status text

  public $headers = [];
  public $status_code = NULL;
  public $content_type = NULL;
  public $body = NULL;

  final public function __construct() { // genişletilemez method
  }

  final public function set($options) { // genişletilemez method
    if (is_array(($options))) {

      foreach ($options as $key => $value) {
        switch ($key) {
          case "headers":       $this->headers = $value;     break;
          case "status_code":  $this->_status_code($value);  break;
          case "content_type": $this->content_type = $value; break;
          default:
          throw new Exception("Response ayarlarında bilinmeyen parametre → " . $key);
        }
      }

    }
  }

  final public function status() { // genişletilemez method
    return $this->_status;
  }

  final public function send() { // genişletilemez method


    if (!is_array($this->headers))
      throw new Exception("Headers list olmalıdır → " . $this->headers);

    // set with default headers
    $this->headers = array_merge($this->headers, self::DEFAULTHEADERS);

    switch ($this->status_code) {
      case 0:   $this->_attachment(); break;
      case 302: $this->_location();   break; // exit
      case 404: $this->_write_404();  break;
      case 500: $this->_write_500();  break;
      default:

      if (!$this->status_code) $this->status_code(200);

      if (!in_array($this->status_code, self::STATUS))
        throw new Exception("Yanıt vermek için bilinmeyen durum kodu → " . $this->status_code);

      if (!$this->content_type) $this->content_type = "text/html";

      // write status code not including: 0, 302, 404, 500
      $this->_write();
    }
  }

  private function _status_code($status_code) {
    $this->status_code = $status_code;
    $this->_status = $status_code . " " . self::STATUS[$status_code];
  }

  private function _attachment() {
    list($body, $filename) = $this->body;
    $content_type = ($this->content_type) ?: "application/octet-stream";

    header("Content-Type: {$content_type}; charset=utf-8");
    header("Content-Disposition: attachment; filename='{$filename}'", FALSE, 200);
    echo $body;
  }

  private function _location() {
    exit(header("Location: http://" . $_SERVER['SERVER_NAME'] . "/{$this->body}", FALSE, 302));
  }

  private function _write() {
    header("Content-Type: {$this->content_type}; charset=utf-8");
    header("HTTP/1.1 {$this->_status}");

    foreach ($this->headers as $name => $value)
      header("{$name}: {$value};");

    echo $this->body;
  }

  private function _write_404() {
    $v = new ApplicationView();
    if ($this->body) $v->text = $this->body; else $v->file = self::ERRORPAGE;
    $this->body = $v->run();
    $this->_write();
  }

  private function _write_500() {
    $v = new ApplicationView();
    if ($this->body) $v->text = $this->body; else $v->file = self::DEBUGPAGE;
    $this->body = $v->run();
    $this->_write();
  }

}
?>

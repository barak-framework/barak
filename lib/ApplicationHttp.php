<?php

class ApplicationHttp {

  private $_ch = NULL;

  public $headers = [];
  public $options = [];

  const DEFAULTHEADERS = [
    'Content-Type' => 'application/x-www-form-urlencoded'
  ];

  const DEFAULTOPTIONS = [
    'CURLOPT_HEADER' => true,
    'CURLOPT_RETURNTRANSFER' => 1
  ];

  final public function __construct() {
    $this->_ch = curl_init();
  }

  final public function head($url) {
    $this->_set('head', $url);
    return $this->_execute();
  }

  final public function get($url) {
    $this->_set('get', $url);
    return $this->_execute();
  }

  final public function post($url, $data = []) {
    $content_length = $this->_set_data($data);
    $this->_set('post', $url, $content_length);
    return $this->_execute();
  }

  private function _execute() {
    $curl_response = curl_exec($this->_ch);

    $info = curl_getinfo($this->_ch);
    if (!curl_errno($this->_ch)) {

      $response = new ApplicationResponse();
      $response->status_code = $info["http_code"];
      $response->content_type = $info["content_type"];
      $response->headers = self::_parser_curl_headers(substr($curl_response, 0, $info["header_size"]));
      $response->body = substr($curl_response, $info["header_size"]);

    } else {

      $response = new ApplicationResponse();
      $response->status_code = 500;
      $response->body = curl_errno($this->_ch) . "#" . curl_error($this->_ch);

    }

    $status = $info["http_code"] . " " . ApplicationResponse::STATUS[$info["http_code"]];
    ApplicationLogger::info("Completed $status Api → " . $info['url'] . " in " . sprintf("(%.2f ms)", $info['total_time'] * 1000));

    curl_close($this->_ch);
    return $response;
  }

  private function _set($method, $url, $content_length = null) {
    $this->_set_headers($content_length);
    $this->_set_options();
    $this->_set_method($method);
    $this->_set_url($url);
  }

  private static function _parser_curl_headers($curl_headers) {
    preg_match_all('/(.*?): (.*)\r\n/', $curl_headers, $matches);
    $keys = $matches[1];
    $values = $matches[2];
    return array_combine($keys, $values);
  }

  private function _set_headers($content_length) {

    // check headers
    if (!is_array($this->headers))
      throw new Exception("Headers list olmalıdır → " . $this->headers);

    // for post data
    if ($content_length)
      $this->headers['Content-Length'] = $content_length;

    $headers = array_merge($this->headers, self::DEFAULTHEADERS);

    $_headers = [];
    foreach ($headers as $key => $value)
      $_headers[] = "{$key}: {$value}";

    curl_setopt($this->_ch, CURLOPT_HTTPHEADER, $_headers);
  }

  private function _set_options() {

    // check options
    if (!is_array($this->options))
      throw new Exception("Options list olmalıdır → " . $this->options);

    $options = array_merge($this->options, self::DEFAULTOPTIONS);

    foreach ($options as $option => $value) {
      curl_setopt($this->_ch, constant('CURLOPT_'.str_replace('CURLOPT_', '', strtoupper($option))), $value);
    }
  }

  private function _set_method($method) {
    switch ($method) {
      case 'head': curl_setopt($this->_ch, CURLOPT_NOBODY, true);  break;
      case 'get':  curl_setopt($this->_ch, CURLOPT_HTTPGET, true); break;
      case 'post': curl_setopt($this->_ch, CURLOPT_POST, true);    break;
      default:     curl_setopt($this->_ch, CURLOPT_CUSTOMREQUEST, $method);
    }
  }

  private function _set_url($url) {
    curl_setopt($this->_ch, CURLOPT_URL, trim($url));
  }

  private function _set_data($params) {

    // check params
    if (!is_array($params))
      throw new Exception("Params list olmalıdır → " . $params);

    $query_params = http_build_query($params);

    if (!is_array($this->headers))
      throw new Exception("Headers list olmalıdır → " . $this->headers);

    curl_setopt($this->_ch, CURLOPT_POSTFIELDS, $query_params);
    return strlen($query_params);
  }

}
?>

<?php

class ApplicationMailer {

  const MAILERPATH = "app/mailers/";

  private $_mailer;

  private $_locals = [];
  private $_mail;

  private $_view;
  private $_action;
  private $_args;

  final public function __get($local) { // genişletilemez fonksyion
    return $this->_locals[$local];
  }

  final public function __set($local, $value) { // genişletilemez fonksyion
    $this->_locals[$local] = $value;
  }

  final public function __isset($local) { // genişletilemez fonksyion
    return isset($this->_locals[$local]);
  }

  final public function __unset($local) { // genişletilemez fonksyion
    unset($this->_locals[$local]);
  }

  final public function mail($options) { // genişletilemez fonksyion
    $this->_mail = $options;
  }

  private static function _mailer() {
    $mailer = new PHPMailer();

    // Enables SMTP debug information (for testing)
    //    0 = diasabled
    //    1 = errors and messages
    //    2 = messages only
    //
    $mailer->SMTPDebug = 0;

    // Setting SMTP Protocol
    // telling the class to use SMTP
    $mailer->isSMTP();

    // Default Charset
    $mailer->CharSet  = 'UTF-8';
    $mailer->Encoding = "base64";

    // Default SMTP Auth
    // Enable SMTP authentication
    $mailer->SMTPAuth = true;

    // Default HTML Format
    $mailer->isHTML(true);

    $mailer_configuration = ApplicationConfig::mailer();
    foreach ($mailer_configuration as $key => $value) {
      switch ($key) {
        // set host like 'mail.website.com'
        case "address":  $mailer->Host = $value; break;
        // Setting Default Port
        // set the SMTP port for the outMail server
        //    use either 25, 587, 2525 or 8025
        case "port":     $mailer->Port = $value; break;
        // nerden ?
        // set username like mail@gdemir.me
        case "username": $mailer->Username = $value; $mailer->SetFrom($value, 'Admin'); break;
        case "password": $mailer->Password = $value; break;
        default:
        throw new Exception("Uygulamanın yapılandırma dosyasında bilinmeyen parametre → " . $key);
      }
    }

    return $mailer;
  }

  private function _filter($action, $filter_actions) {

    foreach ($filter_actions as $filter_action) {

      if (array_key_exists(0, $filter_action)) {
        $filter_action_name = $filter_action[0];
        if (method_exists($this, $filter_action_name)) {
          if (array_key_exists("only", $filter_action)) {
            if (in_array($action, $filter_action["only"]))
              $this->{$filter_action_name}();
          } elseif (array_key_exists("except", $filter_action)) {
            if (!in_array($action, $filter_action["except"]))
              $this->{$filter_action_name}();
          } elseif (!array_key_exists("only", $filter_action) and !array_key_exists("except", $filter_action)) {
            $this->{$filter_action_name}();
          }
        }
        if ($this->_mail) self::_mail($filter_action_name);
      }

    }
  }

  private function _helpers() {
    ApplicationHelper::load($this->helpers);
  }

  private function _mail($action) {
    $main_mailer = $this->_mailer;

    $mailer = clone $main_mailer;

    if (!isset($this->_mail["to"]))
      throw new Exception("Fonksiyounda bir alıcı belirtilmelidir → " . $action);

    foreach ($this->_mail["to"] as $email => $name) {
      $mailer->AddAddress($email, $name);
    }

    if (isset($this->_mail["subject"]))
      $mailer->Subject = $this->_mail["subject"];

    $v = new ApplicationView();

    $v->set(["layout" => "mailer", "view" => "/mail/" . $this->_view, "action" => $action]);

    // mailerin localsları
    if ($this->_locals)
      $v->set(["locals" => $this->_locals]);

    $mailer->Body = $v->run();

    if ($mailer->send())
      ApplicationLogger::info("Mail gönderildi");
    else
      ApplicationLogger::error("Mail gönderiminde sorun oluştu");
  }

  private function _run() {

    if (isset($this->helpers)) $this->_helpers();

    if (isset($this->before_actions)) $this->_filter($this->_action, $this->before_actions);

    if (method_exists($this, $this->_action)) call_user_func_array(array($this, $this->_action), $this->_args);

    if ($this->_mail) self::_mail($this->_action);

    if (isset($this->after_actions)) $this->_filter($this->_action, $this->after_actions);

  }

  // UserMailer::delivery("password_reset");
  // UserMailer::delivery("password_reset", [$code]);
  // UserMailer::delivery("password_reset", [$code, $site_url]);

  final public static function delivery($action = null, $args = []) { // genişletilemez fonksyion
    $mailer_class = strtolower(get_called_class());
    list($view) = explode("mailer", $mailer_class);

    if (!$action)
      throw new Exception("Mailler sınıfında ilgili fonksiyon belirtilmelidir → " . $mailer_class);

    $m = new $mailer_class();
    $m->_mailer = self::_mailer();
    $m->_view = $view;
    $m->_action = $action;
    $m->_args = $args;
    $m->_run();

  }
}
?>

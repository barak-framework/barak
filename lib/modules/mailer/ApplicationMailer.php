<?php

class ApplicationMailer {

  const MAILERPATH = "app/mailers/";

  private static $_configuration = NULL; // for PHPMailer

  private $_locals = [];
  private $_errors = [];

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

  // UserMailer::delivery("password_reset");
  // UserMailer::delivery("password_reset", [$code]);
  // UserMailer::delivery("password_reset", [$code, $site_url]);

  final public static function delivery($action = null, $args = []) { // genişletilemez fonksyion
    $mailer_class = strtolower(get_called_class());
    if (!$action)
      throw new Exception("Mailler sınıfında ilgili method belirtilmelidir → " . $mailer_class);

    list($view) = explode("mailer", $mailer_class);

    $m = new $mailer_class();
    $m->_view = $view;
    $m->_action = $action;
    $m->_args = $args;
    return $m->_run();
  }

  public static function init() {
    // yapılandırma dosyasını bu fonkiyon ne kadar çağrılırsa çağrılsın sadece bir defa oku!
    if (self::$_configuration == NULL) {

      self::$_configuration = new PHPMailer();

      // Enables SMTP debug information (for testing)
      // Debugs levels
      //    0 = diasabled
      //    1 = client
      //    2 = client, server
      //    3 = client, server, connection
      //    4 = low-info
      self::$_configuration->SMTPDebug = 0;

      // Setting SMTP Protocol
      // telling the class to use SMTP
      self::$_configuration->isSMTP();

      // Default Charset
      self::$_configuration->CharSet  = 'UTF-8';
      self::$_configuration->Encoding = "base64";

      // Default SMTP Auth
      // Enable SMTP authentication
      self::$_configuration->SMTPAuth = true;

      // Default HTML Format
      self::$_configuration->isHTML(true);

      foreach (ApplicationConfig::mailer() as $key => $value) {
        switch ($key) {
          // set host like 'mail.website.com'
          case "address":  self::$_configuration->Host = $value; break;
          // Setting Default Port
          // set the SMTP port for the outMail server
          //    use either 25, 587, 2525 or 8025
          case "port":     self::$_configuration->Port = $value; break;
          // nerden ?
          // set username like mail@gdemir.me
          case "username": self::$_configuration->Username = $value; self::$_configuration->SetFrom($value, 'Admin'); break;
          case "password": self::$_configuration->Password = $value; break;
          default:
          throw new Exception("Mailer yapılandırma dosyasında bilinmeyen parametre → " . $key);
        }
      }
    }
  }

  private function _filter($action, $filter_actions) {

    foreach ($filter_actions as $filter_action) {

      if (array_key_exists(0, $filter_action)) {

        $filter_action_name = $filter_action[0];
        if (method_exists($this, $filter_action_name)) {

          // her action öncesi locals yükünü boşalt
          $this->_locals = [];

          if (array_key_exists("only", $filter_action)) {

            if (in_array($action, $filter_action["only"])) $this->{$filter_action_name}();

          } elseif (array_key_exists("except", $filter_action)) {

            if (!in_array($action, $filter_action["except"])) $this->{$filter_action_name}();

          } elseif (!array_key_exists("only", $filter_action) and !array_key_exists("except", $filter_action)) {
            $this->{$filter_action_name}();
          }
        }
        if ($this->_mail) self::_mail($filter_action_name);
      }

    }
  }

  private function _mail($action) {
    $main_mailer = self::$_configuration;

    $mailer = clone $main_mailer;

    if (!isset($this->_mail["to"]))
      throw new Exception("Fonksiyonunda bir alıcı belirtilmelidir → " . $action);

    foreach ($this->_mail["to"] as $email => $name) {
      $mailer->AddAddress($email, $name);
    }

    if (isset($this->_mail["subject"]))
      $mailer->Subject = $this->_mail["subject"];

    $v = new ApplicationView();

    $v->set(["layout" => "mailer", "view" => "mail/" . $this->_view, "action" => $action]);

    // mailerin localsları
    if ($this->_locals)
      $v->set(["locals" => $this->_locals]);

    $mailer->Body = $v->run(true);

    if ($mailer->send())
      ApplicationLogger::info("  Mail Sended");
    else {
      $this->_errors[$action] = "Mail Failed: " . $mailer->ErrorInfo;
      ApplicationLogger::error("  Mail Failed → " . $mailer->ErrorInfo);
    }
  }

  private function _run() {

    if (isset($this->helpers)) ApplicationHelper::load($this->helpers);

    // before actions
    if (isset($this->before_actions)) $this->_filter($this->_action, $this->before_actions);

    // kick main action!
    if (method_exists($this, $this->_action)) call_user_func_array(array($this, $this->_action), $this->_args);

    // main action and view go!
    if ($this->_mail) self::_mail($this->_action);

    // after actions
    if (isset($this->after_actions)) $this->_filter($this->_action, $this->after_actions);

    return $this->_errors;
  }

}
?>

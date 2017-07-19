
<?php

class ApplicationMailer {

  const MAILERPATH = "app/mailers/";

  const HELPERPATH = "app/helpers/";

  private $_locals = [];
  private $_mail;
  private $_mailer;

  private $_view;
  private $_action;

  final public function __construct() {
    $this->_mailer = new PHPMailer();

    // Enables SMTP debug information (for testing)
    //    1 = errors and messages
    //    2 = messages only
    $this->_mailer->SMTPDebug = 1;

    // Setting SMTP Protocol
    // telling the class to use SMTP
    $this->_mailer->isSMTP();

    // Default Charset
    $this->_mailer->CharSet  = 'UTF-8';
    $this->_mailer->Encoding = "base64";

    // Default SMTP Auth
    // Enable SMTP authentication
    $this->_mailer->SMTPAuth = true;

    // Default HTML Format
    $this->_mailer->isHTML(true);

    $mailer_configuration = ApplicationConfig::mailer();
    foreach ($mailer_configuration as $key => $value) {
      switch ($key) {
        // set host like 'mail.website.com'
        case "address":  $this->_mailer->Host = $value; break;
        // Setting Default Port
        // set the SMTP port for the outMail server
        //    use either 25, 587, 2525 or 8025
        case "port":     $this->_mailer->Port = $value; break;
        // nerden ?
        // set username like mail@gdemir.me
        case "username": $this->_mailer->Username = $value; $this->_mailer->SetFrom($value, 'Admin'); break;
        case "password": $this->_mailer->Password = $value; break;
        default:
        throw new ConfigurationException("Uygulamanın yapılandırma dosyasında bilinmeyen parametre", $key);
      }
    }
  }

  final public function __get($local) { // genişletilemez fonksyion
    return $this->_locals[$local];
  }

  final public function __set($local, $value) { // genişletilemez fonksyion
    $this->_locals[$local] = $value;
  }

  final public function mail($options) { // genişletilemez fonksyion
    $this->_mail[] = $options;
  }

  // UserMailer::delivery("test");

  final public static function delivery() {
    $mailer_class = strtolower(get_called_class());
    list($view) = explode("mailer", $mailer_class);

    $args = func_get_args();

    $action = $args[0];
    $args = array_slice($args, 1);

    $m = new $mailer_class();
    $m->_view = $view;
    $m->_action = $action;
    $m->run();
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

  private function _helper($helper) {
    if (is_array($this->helpers)) {
      foreach ($this->helpers as $helper) {
        $helper_path = self::HELPERPATH . $helper . "Helper.php";
        if (!file_exists($helper_path))
          throw new FileNotFoundException("Helper dosyası mevcut değil", $helper_path);
        require_once $helper_path;
      }
    } elseif ($this->helpers == "all") {
      foreach (glob($self::HELPERPATH . "*.php") as $class) {
        require_once $class;
      }
    } else {
      throw new Exception("Helper methodunda bilinmeyen parametre", $this->helper);
    }
  }

  private function _mail($action) {

    foreach ($this->_mail as $option) {

      if (isset($options["to"])) {
        list($email, $name) = $options["to"];
        $this->_mailer->AddAddress($email, $name);
      }

      if (isset($options["subject"]))
        $this->_mailer->Subject = $options["subject"];
    }

    $v = new ApplicationView();

    $v->set(["layout" => "mailer", "view" => "/mail/" . $this->_view, "action" => $action]);

    // mailerin localsları
    if ($this->_locals)
      $v->set(["locals" => $this->_locals]);

    $this->_mailer->Body = $v->run();

    return ($this->_mailer->Send()) ? true : false;
  }

  final public function run() {

    if (isset($this->helpers)) $this->_helper($this->helpers);

    if (isset($this->before_actions)) $this->_filter($this->_action, $this->before_actions);

    if (method_exists($this, $this->_action)) $this->{$this->_action}();

    if ($this->_mail) self::_mail($this->_action);

    if (isset($this->after_actions)) $this->_filter($this->_action, $this->after_actions);

  }
}
?>
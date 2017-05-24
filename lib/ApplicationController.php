<?php
class ApplicationController {

  public $_locals = [];
  public $_render;

  public function _filter($action, $filter_actions) {

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
      }

    }
  }

  public function run($action) {
    if (isset($this->before_actions)) $this->_filter($action, $this->before_actions);

    if (method_exists($this, $action)) $this->$action();

    if (isset($this->after_actions)) $this->_filter($action, $this->after_actions);
  }

  public function render($options) {
    $this->_render = $options;
  }

  public function redirect_to($url) {
    exit(header("Location: http://" . $_SERVER['SERVER_NAME'] . "/" . trim($url, "/"), false, 303));
  }

  public function __get($local) {
    return $this->_locals[$local];
  }

  public function __set($local, $value) {
    $this->_locals[$local] = $value;
  }
}
?>

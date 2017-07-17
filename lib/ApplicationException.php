<?php

class ApplicationException extends Exception {

  public function __construct($message, $request) {
    echo(sprintf(
      "
      <p style = '
      text-align: center;
      padding: 3px;
      color: #E0EBD6;
      padding: 8px;
      border: 6px solid #ddd;
      border-radius: 5px;
      box-shadow: 0px 0px 5px #ddd;
      background: #30D5C8;
      '>

      <b style = 'color:#31708f;'> %s </b> → %s [<i style = 'color:#31708f;'> %s </i>]

      </p>
      ",
      get_called_class(), $message, $request
      ));

    parent::__construct("$request : $message");
  }

};

// extends ApplicationException class define

class BelongNotFoundException extends ApplicationException {};
class ConfigurationException extends ApplicationException {};
class CRUDException extends ApplicationException {};
class DatabaseException extends ApplicationException {};
class FileNotFoundException extends ApplicationException {};
class FieldNotFoundException extends ApplicationException {};
class I18nNotFoundException extends ApplicationException {};
class MethodNotFoundException extends ApplicationException {};
class TableNotFoundException extends ApplicationException {};
class ViewNotFoundException extends ApplicationException {};

?>
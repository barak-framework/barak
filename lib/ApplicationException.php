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
      background: #F07746;
      '>

      <b style = 'color:rgba(201, 2, 92, 0.5);'> %s </b> → %s [<i style = 'color:rgba(201, 2, 92, 0.5);'> %s </i>]

      </p>
      ", get_class(), $message, $request
      ));

    parent::__construct("$request : $message");
  }

};

// extends ApplicationException class define

class FileNotFoundException extends ApplicationException {};
class ConfigurationException extends ApplicationException {};
class FieldNotFoundException extends ApplicationException {};
class TableNotFoundException extends ApplicationException {};
class BelongNotFoundException extends ApplicationException {};
class SQLException extends ApplicationException {};
class ViewNotFoundException extends ApplicationException {};
class I18nNotFoundException extends ApplicationException {};
class DatabaseException extends ApplicationException {};

?>

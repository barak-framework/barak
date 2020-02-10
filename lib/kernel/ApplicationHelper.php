<?php

class ApplicationHelper {

  const HELPERPATH = "app/helpers/";

  const HELPERALLFILE = "all";
  const HELPERSUBNAME = "Helper";

  public static function load($helpers) {
    if (is_array($helpers)) {
      foreach ($helpers as $helper) {
        $helper_file_path = self::HELPERPATH . $helper . self::HELPERSUBNAME . ".php";
        if (!file_exists($helper_file_path))
          throw new Exception("Helper dosyası mevcut değil → " . $helper_file_path);
        require_once $helper_file_path;
      }
    } elseif ($helpers == self::HELPERALLFILE) {
      foreach (glob($self::HELPERPATH . "*" . self::HELPERSUBNAME . ".php") as $class) {
        require_once $class;
      }
    } else {
      throw new Exception("Helper methodunda bilinmeyen parametre → " . $helper);
    }
  }

}
?>

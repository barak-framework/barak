<?php
// TODO must be test!
class ApplicationHelper {

  const HELPERPATH = "app/helpers/";

  public static function load($helpers) {
    if (is_array($helpers)) {
      foreach ($helpers as $helper) {
        $helper_path = self::HELPERPATH . $helper . "Helper.php";
        if (!file_exists($helper_path))
          throw new Exception("Helper dosyası mevcut değil → " . $helper_path);
        require_once $helper_path;
      }
    } elseif ($helpers == "all") {
      foreach (glob($self::HELPERPATH . "*.php") as $class) {
        require_once $class;
      }
    } else {
      throw new Exception("Helper methodunda bilinmeyen parametre → " . $helper);
    }
  }
}
?>

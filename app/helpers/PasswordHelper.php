<?php

class PasswordHelper {

  public static function generate($length) {
    // rastgele bir parolar belirle
    $alphabet = "abcdefghijklmnopqrstuwxyzABC0123456789";
    for ($i = 0; $i < $length; $i++) {
      $random_password[$i] = $alphabet[rand(0, strlen($alphabet) - 1)];
    }
    return implode("", $random_password);
  }

}
?>

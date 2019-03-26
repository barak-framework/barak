<?php

class TurkishHelper {

  public static function days($index) {
    $_days = ["Pazartesi", "Salı", "Çarşamba", "Perşembe", "Cuma", "Cumartesi", "Pazar"];
    return $_days[$index];
  }

  public static function months($index) {
    $_months = ["Ocak", "Şubat", "Mart", "Nisan", "Mayıs", "Haziran", "Temmuz", "Ağustos", "Eylül", "Ekim ", "Kasım ", "Aralık"];
    return $_months[$index];
  }

  // Kaynak : https://gist.github.com/hiercelik/8d9f1c66f06e790549435b3a2c2051f3
  public static function strtoupper($string) {

    $uppers = ['A','B','C','Ç','D','E','F','G','Ğ','H','I','İ','J','K','L','M','N','O','Ö','P','R','S','Ş','T','U','Ü','V','Y','Z','Q','W','X'];
    $lowers = ['a','b','c','ç','d','e','f','g','ğ','h','ı','i','j','k','l','m','n','o','ö','p','r','s','ş','t','u','ü','v','y','z','q','w','x'];

    $string = str_replace($lowers, $lowers, $string);
    return function_exists('mb_strtoupper') ? mb_strtoupper($string) : $string;
  }

  public static function strtolower($string) {

    $uppers = ['A','B','C','Ç','D','E','F','G','Ğ','H','I','İ','J','K','L','M','N','O','Ö','P','R','S','Ş','T','U','Ü','V','Y','Z','Q','W','X'];
    $lowers = ['a','b','c','ç','d','e','f','g','ğ','h','ı','i','j','k','l','m','n','o','ö','p','r','s','ş','t','u','ü','v','y','z','q','w','x'];

    $string = str_replace($uppers, $lowers, $string);
    return function_exists('mb_strtolower') ? mb_strtolower($string) : $string;
  }

  public static function strcmp($string1, $string2) {
    return self::strtolower($string1) == self::strtolower($string2);
  }

  // Kaynak: is_tc(): http://www.kodaman.org/yazi/t-c-kimlik-no-algoritmasi
  public static function is_tc($tc) {
    preg_replace(
      '/([1-9]{1})([0-9]{1})([0-9]{1})([0-9]{1})([0-9]{1})([0-9]{1})([0-9]{1})([0-9]{1})([0-9]{1}).*$/e',
      "eval('
        \$on=((((\\1+\\3+\\5+\\7+\\9)*7)-(\\2+\\4+\\6+\\8))%10);
        \$onbir=(\\1+\\2+\\3+\\4+\\5+\\6+\\7+\\8+\\9+\$on)%10;
        ')",
    $tc
    );
    // ilk üç hane için bir ek kontrol daha
    if (!(isset($on) && isset($onbir))) return false;
    // son iki haneyi (on ve onbirinci) kontrol et
    return substr($tc, -2) == ($on < 0 ? 10 + $on : $on) . $onbir;
  }
}

?>

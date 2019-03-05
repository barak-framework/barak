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

  public static function strtoupper($string) {
    $_upper = [
      'i' => 'İ',
      'ı' => 'I',
      'ğ' => 'Ğ',
      'ü' => 'Ü',
      'ş' => 'Ş',
      'ö' => 'Ö',
      'ç' => 'Ç'
    ];
    return strtoupper(strtr($string, $_upper));
  }
  
  public static function strtolower($string) {
    $_lower = [
      'İ' => 'i',
      'I' => 'ı',
      'Ğ' => 'ğ',
      'Ü' => 'ü',
      'Ş' => 'ş',
      'Ö' => 'ö',
      'Ç' => 'ç'
    ];
    return strtolower(strtr($string, $_lower));
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

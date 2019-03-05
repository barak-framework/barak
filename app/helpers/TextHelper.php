<?php

class TextHelper {

  /*
  $text = "Oğuz Kağandan Günümüze Barak Türkmenleri Tarihi"; // len 53
  */

  /*
  echo TextHelper::truncate($text);
  echo TextHelper::truncate($text, 10);
  echo TextHelper::truncate($text, 53);
  echo TextHelper::truncate($text, 1000);

  Oğuz Kağandan Günümüze Bara...
  Oğuz Kağan...
  Oğuz Kağandan Günümüze Barak Türkmenleri Tarihi
  Oğuz Kağandan Günümüze Barak Türkmenleri Tarihi
  */

  public static function truncate($text, $length = 27, $escape = "...") {
    return (strlen($text) <= $length) ? $text : mb_substr($text, 0, $length, "utf-8") . $escape;
  }

  /*
  echo TextHelper::truncate_word($text);
  echo TextHelper::truncate_word($text, 10);
  echo TextHelper::truncate_word($text, 10, "... Daha Fazla");
  echo TextHelper::truncate_word($text, 1000, "... Daha Fazla");

  Oğuz Kağandan Günümüze Barak...
  Oğuz Kağandan...
  Oğuz Kağandan... Daha Fazla
  Oğuz Kağandan Günümüze Barak Türkmenleri Tarihi
  */

  public static function truncate_word($text, $length = 27, $escape = "...") {
    if (strlen($text) <= $length) return $text;
    $index = mb_strpos($text, ' ', $length, "utf-8");
    return ($index === false) ? $text : mb_substr($text, 0, $index, "utf-8") . $escape;
  }

}
?>

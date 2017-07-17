<?php

class ApplicationUtil {

  /* source: http://stackoverflow.com/questions/7128856/strip-out-html-and-special-characters */
  public static function html_escape($content = "") {
    // Strip HTML Tags
    $clear = strip_tags($content);

    // Clean up things like &amp;
    $clear = html_entity_decode($clear);

    // Strip out any url-encoded stuff
    $clear = urldecode($clear);

    // Replace non-AlNum characters with space
    // $clear = preg_replace('/[^A-Za-z0-9]/', ' ', $clear);

    // Replace Multiple spaces with single space
    $clear = preg_replace('/ +/', ' ', $clear);

    // Trim the string of leading/trailing space
    $clear = trim($clear);

    return $clear;
  }

  public static function url_encode($content = "") {
    return urlencode($content);
  }

  public static function url_decode($content = "") {
    return urldecode($content);
  }
}
?>
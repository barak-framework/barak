<?php
// #TODO must be test!!!
class ApplicationCache {

  const CACHEDIR = "cache/";

  public static function set($key, $value, $expiration = 604800) {

    // struct of key
    $data = [
			'time'	 => time(),
			'expire' => $expiration,
			'value'	 => serialize($value)
		];

    $filename = self::filename_format($key);

    // save
   
    file_put_contents($filename, json_encode($data));
    // or 
    if (!($fh = fopen($filename, 'a')))
      throw new FileNotFoundException("Cache dosyası açılamadı", $filename);

    fwrite($fh, json_encode($data));

    fclose($fh);
  }

  public static function get($key) {

    // get filename
    $filename = self::filename_format($key);
    if (file_exists($filename)) {

      // check expire time ? get or del
      $data = json_decode(include $filename);       
      if ($data["expire"] > (time() - $data["time"]))
        return unserialize($data["value"]);
      else
        self::del($key);
    }
    return null;
  }

  public static function del($key) {
    
    // get filename
    $filename = self::filename_format($key);
    if (!file_exists($filename))
      new throw Exception("cache bellekte anahtar bulunamadı", $key);

    unlink($filename);
  }

  public static function has($key) {
    return (file_exists(self::filename_format($key))) ? true : false; 
  }
  
  public static function reset() {
    foreach(glob(self::CACHEDIR . "*.php") as $cachename)
      unlink($cachename);
  }

  private static function filename_format($key) {
    $scriptname = preg_replace('/[^0-9a-z\.\_\-]/i', '', strtolower($_SERVER["SCRIPT_FILENAME"]));
    return self::CACHEDIR . md5($scriptname . $key);
  }
}
?>

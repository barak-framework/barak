<?php

class FileHelper {

  // NOTE

  // $_SERVER["DOCUMENT_ROOT"] owner should be www-data
  // sudo chown www-data:www-data /var/www/
  // sudo chown www-data:www-data /var/www/PROJECT

  public static function path() {
    return $_SERVER["DOCUMENT_ROOT"];
  }

  // Example MOVE with $_FILE

  //
  // HTML
  //<form method="post" enctype="multipart/form-data">
  //  <input type="file" id="file" name="file" multiple class="form-control"/>
  //  <button type="submit" class="btn btn-primary">Send</button>
  //</form>
  //
  // PHP
  // $file = $_FILES['file'];
  //
  // FileHelper::move_f($file, "/upload", "logo");
  // return "/upload/logo.png";
  // FileHelper::move_f($file, "/upload", "file");
  // return "/upload/file.txt";
  // FileHelper::move_f($file, "/upload/users", "logo");
  // return /upload/users/logo.png";
  // FileHelper::move_f($file, "/upload/texts", "file");
  // return "/upload/texts/file.txt";

  public static function move_f($file, $destination_directory, $destination_file) {

    $temp_file = $file["tmp_name"];
    $path_parts = pathinfo($file["name"]);
    $destination_file = $destination_file . "." . strtolower($path_parts["extension"]);

    return FileHelper::move($temp_file, $destination_directory, $destination_file, null);
  }

  // Example MOVE

  // FileHelper::move("ali.png", "/upload/texts", "file.png");
  // return "/upload/texts/file.txt";

  public static function move($source_file, $destination_directory, $destination_file, $path = true) {
    $destination_directory_path = FileHelper::make_directories($destination_directory);

    $destination_file_path = $destination_directory_path . "/" . $destination_file;
    $source_file_path = (($path) ? FileHelper::path() : "") . $source_file;

    move_uploaded_file($source_file_path, $destination_file_path);
    return $destination_directory . "/" . $destination_file;
  }

  // Example COPY

  // FileHelper::copy("/app/assets/img/default.png", "/upload/agendas", "$agenda_id.png");
  // return "/upload/agendas/$agenda_id.png";

  public static function copy($source_file, $destination_directory, $destination_file, $path = true) {
    $destination_directory_path = FileHelper::make_directories($destination_directory);

    $destination_file_path = $destination_directory_path . "/" . $destination_file;
    $source_file_path = (($path) ? FileHelper::path() : "") . $source_file;

    copy($source_file_path, $destination_file_path);
    return $destination_directory . "/" . $destination_file;
  }

  // Example REMOVE

  // FileHelper::remove("/upload/logo.png");
  // FileHelper::remove("/upload/users/logo.png");

  public static function remove($file) {
    $file_path = FileHelper::path() . "/$file";
    if (is_file($file_path))
      unlink($file_path);
  }

  // Example DIRECTORIES

  // FileHelper::make_directories("/upload/agendas");
  // return "/var/ww/html/upload/agendas";
  // FileHelper::make_directories("/upload/agendas/pdf");
  // return "/var/ww/html/upload/agendas/pdf";

  public static function make_directories($directory) {
    $directory_path = FileHelper::path() . $directory;
    if (!is_dir($directory_path))
      mkdir($directory_path, 0777, true);

    return $directory_path;
  }

}
?>

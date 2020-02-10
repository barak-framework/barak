<?php
Application::config(function() {
  set("timezone", "Europe/Istanbul");
  set("debug", true);
  set("locale", "tr");
  set("logger", [
    "file" => "production",
    "level" => "info",
    "driver" => "weekly",
    "rotate" => 4,
    "size" => 5288000
  ]);
  //  set("logger", "production.log");
  // modules(["cacher", "mailer"]);
  modules(["cacher", "mailer", "model"]);
  // set("cacher", false);
  // set("model", false);
  // set("mailer", false);
});
?>

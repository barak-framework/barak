<?php

class ApplicationAlias {

  public static function extract() {

    // LOCALES

    function t($words, $locals = NULL) {
      return ApplicationI18n::translate($words, $locals);
    }

    // LAYOUT and TEMPLATE
    // for app/views/VIEW/ACTION.php and app/views/layouts/VIEW.php

    function render($view_options = NULL) {
      if ($view_options) {
        $v = new ApplicationView();
        $v->set($view_options);
        return $v->run();
      }
    }

  }

}
?>

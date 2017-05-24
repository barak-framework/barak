<?php

// CRUD
// DRAFT #TODO or builder : https://github.com/ryangurn/PHP-MVC/blob/master/libraries/activerecord/lib/SQLBuilder.php

// SQL injection protection http://stackoverflow.com/questions/60174/how-can-i-prevent-sql-injection-in-php

class ApplicationSql {

  public static $order_sort_type = ["ASC", "DESC"];
  public static $where_logics = ["AND", "OR"];
  public static $where_in_marks = ["IN", "NOT IN"];
  public static $where_between_marks = ["BETWEEN", "NOT BETWEEN"];
  public static $where_like_marks = ["LIKE", "NOT LIKE"];
  public static $where_null_marks = ["IS NULL", "IS NOT NULL"];
  public static $where_other_marks = ["=", "!=", ">", "<", ">=", "<="];

  // ["first_name" => "Gökhan", "last_name" => "Demir"]
  private static function where_to_command_symbol_symbolvalue($_list) {

    if (!empty($_list)) {
      $symbols = "";
      $symbol_and_values = [];  // [":first_name" => "Gökhan", ":last_name" => "Demir"]

      foreach ($_list as $index => $hash) {

        $unique_symbol_prefix = "WHERE_" . $index;
        if ($index == 0) {

          if (in_array($hash["mark"], static::$where_null_marks)) {

            $symbols .= $hash["field"] . " " . $hash["mark"];

          } elseif (in_array($hash["mark"], static::$where_in_marks)) {

            list($in_command, $in_symbols, $in_symbolvalues) = static::list_to_command_symbol_symbolvalue($hash["value"], $unique_symbol_prefix);
            $symbols .= $hash["field"] . " " . $hash["mark"] . " " . "(" . $in_symbols . ")";
            $symbol_and_values = array_merge($symbol_and_values, $in_symbolvalues);

          } elseif (in_array($hash["mark"], static::$where_between_marks)) {

            list($between_command, $between_symbols, $between_symbolvalues) = static::list_to_command_symbol_symbolvalue($hash["value"], $unique_symbol_prefix, "AND");
            $symbols .= $hash["field"] . " " . $hash["mark"] . " " . $between_symbols;
            $symbol_and_values = array_merge($symbol_and_values, $between_symbolvalues);

          } else {

            list($field_command, $field_symbol, $field_symbolvalue) = static::list_to_command_symbol_symbolvalue([$hash["value"]], $unique_symbol_prefix);
            $symbols .= $hash["field"] . " " . $hash["mark"] . " " . $field_symbol;
            $symbol_and_values = array_merge($symbol_and_values, $field_symbolvalue);

          }

        } else {

          if (in_array($hash["mark"], static::$where_null_marks)) {

            $symbols .= " " . $hash["logic"] . " " . $hash["field"] . " " . $hash["mark"];

          } elseif (in_array($hash["mark"], static::$where_in_marks)) {

            list($in_command, $in_symbols, $in_symbolvalues) = static::list_to_command_symbol_symbolvalue($hash["value"], $unique_symbol_prefix);
            $symbols .= " " . $hash["logic"] . " " . $hash["field"] . " " . $hash["mark"] . " " . "(" . $in_symbols . ")";
            $symbol_and_values = array_merge($symbol_and_values, $in_symbolvalues);

          } elseif (in_array($hash["mark"], static::$where_between_marks)) {

            list($between_command, $between_symbols, $between_symbolvalues) = static::list_to_command_symbol_symbolvalue($hash["value"], $unique_symbol_prefix, "AND");
            $symbols .= " " . $hash["logic"] . " " . $hash["field"] . " " . $hash["mark"] . " " . $between_symbols;
            $symbol_and_values = array_merge($symbol_and_values, $between_symbolvalues);

          } else {

            list($field_command, $field_symbol, $field_symbolvalue) = static::list_to_command_symbol_symbolvalue([$hash["value"]], $unique_symbol_prefix);
            $symbols .= " " . $hash["logic"] . " " . $hash["field"] . " " . $hash["mark"] . " " . $field_symbol;
            $symbol_and_values = array_merge($symbol_and_values, $field_symbolvalue);

          }

        }

      }

      return array("WHERE $symbols", $symbols, $symbol_and_values);
    } else {
      return array("", "", []);
    }

  }

  // ["first_name" => "Gökhan", "last_name" => "Demir"]
  private static function hash_to_key_symbol_symbolvalue($_hash, $_command = "", $_delimiter = ",") {

    $symbols = "";                             // ["first_name" => ":first_name", "last_name" => ":last_name"]
    $symbol_and_values = [];                   // [":first_name" => "Gökhan", ":last_name" => "Demir"]
    $keys = "";

    foreach ($_hash as $key => $value) {

      $keys .= ($keys ? " $_delimiter " : "") . $key;
      $key_symbol = ":$_command" . "_" . str_replace(".", "_", $key);
      $symbols .= ($symbols ? " $_delimiter " : "") . $key_symbol;
      $symbol_and_values[$key_symbol] = $value;

    }

    /*
    ["first_name", "last_name"],
    [":first_name", ":last_name"],
    [":first_name" => "Gökhan", ":last_name" => "Demir"]
    */
    return array($keys, $symbols, $symbol_and_values);
  }

  // ["first_name" => "Gökhan", "last_name" => "Demir"]
  private static function hash_to_keysymbol_symbolvalue($_hash, $delimiter = ",", $command = "") {

    $key_and_symbols = "";                     // ["first_name" => ":first_name", "last_name" => ":last_name"]
    $symbol_and_values = [];                   // [":first_name" => "Gökhan", ":last_name" => "Demir"]
    foreach ($_hash as $key => $value) {
      $key_symbol = ":$command" . "_" . str_replace(".", "_", $key);
      $key_and_symbols .= ($symbol_and_values ? " $delimiter " : "") . "$key=$key_symbol";
      $symbol_and_values[$key_symbol] =  $value;
    }
    return array($key_and_symbols ? "$command $key_and_symbols" : "", $symbol_and_values);
  }

  // ["first_name", "last_name"]
  private static function list_to_command_symbol_symbolvalue($_list, $_command = "", $_delimiter = ",") {

    if (!empty($_list)) {

      $symbols = "";                              // ":first_name , :last_name"
      $symbol_and_values = [];                    // [":first_name" => "first_name", ":last_name" => "last_name"]
      $command = str_replace(" ", "", $_command); // ORDER BY => ORDERBY, GROUP BY => GROUPBY

      foreach ($_list as $index => $field) {

        //$key_symbol = ":$command" . "_" . str_replace(".", "_", str_replace(" ", "_", $field));
        $key_symbol = ":$command" . "_" . $index;
        $symbols .= ($symbols ? " $_delimiter " : "") . $key_symbol;
        $symbol_and_values[$key_symbol] = $field;

      }

      // array(
      // "ORDER BY :first_name, :last_name",
      // ":first_name, :last_name",
      // "[':first_name' => 'gökhan', ':last_name' => 'demir']"
      // )

      return array("$_command $symbols", $symbols, $symbol_and_values);
    } else {
      return array("", "", []);
    }

  }

  private static function var_to_command_symbol_value($_value, $_command = "") {

    if (isset($_value)) {
      $symbol = ":" . str_replace(" ", "", $_command); // ORDER BY => ORDERBY, GROUP BY => GROUPBY
      return array("$_command $symbol", $symbol, [$symbol => $_value]);
    } else {
      return array("", "", []);
    }

  }

  public static function create($_table, $_fields) {

    if (array_key_exists("id", $_fields)) unset($_fields["id"]);

    foreach ($_fields as $field => $value) if ($value == null) unset($_fields[$field]);

    list($field_keys, $field_symbols, $field_symbolvalues) = static::hash_to_key_symbol_symbolvalue($_fields);

    $query = $GLOBALS['db']->prepare("INSERT INTO `$_table` ( $field_keys ) VALUES ( $field_symbols )");

    $symbolvalues = array_merge(
      $field_symbolvalues
      );

    foreach ($symbolvalues as $symbol => $value) {
      $query->bindValue($symbol, $value, ApplicationSql::bindtype($value));
    }

    if (!$query->execute())
      throw new SQLException("Tabloya kayıt yazmada sorun oluştu", $_table);

    return intval($GLOBALS["db"]->lastInsertId());
  }

  public static function read($_table, $_select, $_where) {

    $_select_fields = (!empty($_select)) ? implode(",", $_select) : "*";

    list($where_commands, $where_symbols, $where_symbolvalues) = static::where_to_command_symbol_symbolvalue($_where);

    $query = $GLOBALS['db']->prepare("SELECT $_select_fields FROM `$_table` $where_commands");

    $symbolvalues = array_merge(
      $where_symbolvalues
      );

    foreach ($symbolvalues as $symbol => $value) {
      $query->bindValue($symbol, $value, ApplicationSql::bindtype($value));
    }

    if (!$query->execute())
      throw new SQLException("Tablodan veri okumasında sorun oluştu", $_table);

    return $query->fetch(PDO::FETCH_ASSOC);
  }

  public static function update($_table, $_sets, $_where) {

    list($set_keysymbols, $set_symbolvalues) = static::hash_to_keysymbol_symbolvalue($_sets);
    list($where_commands, $where_symbols, $where_symbolvalues) = static::where_to_command_symbol_symbolvalue($_where);

    $query = $GLOBALS['db']->prepare("UPDATE `$_table` SET $set_keysymbols $where_commands");

    $symbolvalues = array_merge(
      $where_symbolvalues,
      $set_symbolvalues
      );

    foreach ($symbolvalues as $symbol => $value) {
      $query->bindValue($symbol, $value, ApplicationSql::bindtype($value));
    }

    if (!$query->execute())
      throw new SQLException("Tabloda kayıt güncellemesinde sorun oluştu", $_table);
  }

  public static function delete($_table, $_where, $_limit) {

    list($where_commands, $where_symbols, $where_symbolvalues) = static::where_to_command_symbol_symbolvalue($_where);
    list($limit_command, $limit_symbol, $limit_symbolvalue)  = static::var_to_command_symbol_value($_limit, "LIMIT");

    $query = $GLOBALS['db']->prepare("DELETE FROM `$_table` $where_commands $limit_command");

    $symbolvalues = array_merge(
      $where_symbolvalues,
      $limit_symbolvalue
      );

    foreach ($symbolvalues as $symbol => $value) {
      $query->bindValue($symbol, $value, ApplicationSql::bindtype($value));
    }

    if (!$query->execute())
      throw new SQLException("Tablodan veri silmesinde sorun oluştu", $_table);
  }

  public static function query($_select, $_table, $_join, $_where, $_order, $_group, $_limit, $_offset) {

    $_select_fields = (!empty($_select)) ? implode(",", $_select) : "*";
    $_order_fields  = (!empty($_order))  ? "ORDER BY " . implode(",", $_order) : "";
    $_group_fields  = (!empty($_group))  ? "GROUP BY " . implode(",", $_group) : "";

    if ($_join) {
      $_join_fields = "";
      foreach ($_join as $table => $condition) {
        $_join_fields .= ($_join_fields ? " " : "") . "INNER JOIN $table ON $condition";;
      }
    } else {
      $_join_fields = "";
    }

    list($where_commands, $where_symbols, $where_symbolvalues) = static::where_to_command_symbol_symbolvalue($_where);
    list($limit_command,  $limit_symbol,  $limit_symbolvalue)  = static::var_to_command_symbol_value($_limit,  "LIMIT");
    list($offset_command, $offset_symbol, $offset_symbolvalue) = static::var_to_command_symbol_value($_offset, "OFFSET");

    $sql = "
    SELECT $_select_fields
    FROM $_table
    $_join_fields
    $where_commands
    $_order_fields
    $_group_fields
    $limit_command
    $offset_command
    ";

    $query = $GLOBALS['db']->prepare($sql);

    $symbolvalues = array_merge(
      $where_symbolvalues,
      $limit_symbolvalue,
      $offset_symbolvalue
      );

    foreach ($symbolvalues as $symbol => $value) {
      // $query->bindParam($symbol, $value);
      $query->bindValue($symbol, $value, ApplicationSql::bindtype($value));
    }

    if (!$query->execute())
      throw new SQLException("Tablodan kayıtların okunmasında sorun oluştu", $_table);

    return $query->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function bindtype($value) {
    if     (is_int($value))  return PDO::PARAM_INT;
    elseif (is_bool($value)) return PDO::PARAM_BOOL;
    elseif (is_null($value)) return PDO::PARAM_NULL;
    else                     return PDO::PARAM_STR;
  }

  public static function tablenames() {
    $name = $GLOBALS['db']->query("select database()")->fetchColumn();
    $result = $GLOBALS['db']->query("show tables");
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) $tablenames[] = $row["Tables_in_" . $name];
    return $tablenames;
  }

  public static function fieldnames($table) {
    return $GLOBALS['db']->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN);
  }

  public static function primary_keyname($table) {
    return $GLOBALS['db']->query("SHOW INDEX FROM $table WHERE Key_name = 'PRIMARY'")->fetch(PDO::FETCH_ASSOC)["Column_name"];
  }
}

?>

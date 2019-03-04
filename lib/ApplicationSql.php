<?php

// SQL injection protection http://stackoverflow.com/questions/60174/how-can-i-prevent-sql-injection-in-php

class ApplicationSql {

  public static $order_sort_type = ["ASC", "DESC"];
  public static $where_logics = ["AND", "OR"];
  public static $where_marks_in = ["IN", "NOT IN"];
  public static $where_marks_between = ["BETWEEN", "NOT BETWEEN"];
  public static $where_marks_like = ["LIKE", "NOT LIKE"];
  public static $where_marks_null = ["IS NULL", "IS NOT NULL"];
  public static $where_marks_other = ["=", "!=", ">", "<", ">=", "<="];

  //////////////////////////////////////////////////
  // Public Functions
  //////////////////////////////////////////////////

  public static function create($_table = "", $_fields = []) {

    if (array_key_exists("id", $_fields)) unset($_fields["id"]);

    foreach ($_fields as $field => $value) if ($value == null) unset($_fields[$field]);

    list($field_keys, $field_symbols, $field_symbolvalues) = static::_hash_to_key_symbol_symbolvalue($_fields);

    $_query = "INSERT INTO `$_table` ( $field_keys ) VALUES ( $field_symbols )";

    $_symbolvalues = array_merge(
      $field_symbolvalues
      );

    $query = self::_query_execute($_query, $_symbolvalues);

    $connection = ApplicationDatabase::connect();
    return intval($connection->lastInsertId());
  }

  public static function read($_select = [], $_table = "", $_where = []) {
    $_select_fields = (!empty($_select)) ? implode(",", $_select) : "*";
    list($where_commands, $where_symbols, $where_symbolvalues) = static::_where_to_command_symbol_symbolvalue($_where);

    $_symbolvalues = array_merge(
      $where_symbolvalues
      );

    $_query = "SELECT $_select_fields FROM `$_table` $where_commands LIMIT 1";

    $query = self::_query_execute($_query, $_symbolvalues);

    return $query->fetch(PDO::FETCH_ASSOC);
  }

  public static function read_all($_select = [], $_table = "", $_join = [], $_where = [], $_order = [], $_group = [], $_limit = null, $_offset = null) {

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

    list($where_commands, $where_symbols, $where_symbolvalues) = static::_where_to_command_symbol_symbolvalue($_where);
    list($limit_command,  $limit_symbol,  $limit_symbolvalue)  = static::_var_to_command_symbol_value($_limit,  "LIMIT");
    list($offset_command, $offset_symbol, $offset_symbolvalue) = static::_var_to_command_symbol_value($_offset, "OFFSET");

    $_query = "
    SELECT $_select_fields
    FROM $_table
    $_join_fields
    $where_commands
    $_group_fields
    $_order_fields
    $limit_command
    $offset_command";

    $_symbolvalues = array_merge(
      $where_symbolvalues,
      $limit_symbolvalue,
      $offset_symbolvalue
      );

    $query = self::_query_execute($_query, $_symbolvalues);

    return $query->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function update($_table = "", $_sets = [], $_where = [], $_limit = null) {

    list($set_keysymbols, $set_symbolvalues) = static::_hash_to_keysymbol_symbolvalue($_sets);
    list($where_commands, $where_symbols, $where_symbolvalues) = static::_where_to_command_symbol_symbolvalue($_where);
    list($limit_command, $limit_symbol, $limit_symbolvalue)  = static::_var_to_command_symbol_value($_limit, "LIMIT");

    $_query = "UPDATE `$_table` SET $set_keysymbols $where_commands $limit_command";

    $_symbolvalues = array_merge(
      $set_symbolvalues,
      $where_symbolvalues,
      $limit_symbolvalue
      );

    $query = self::_query_execute($_query, $_symbolvalues);
  }

  public static function delete($_table = "", $_where = [], $_limit = null) {

    list($where_commands, $where_symbols, $where_symbolvalues) = static::_where_to_command_symbol_symbolvalue($_where);
    list($limit_command, $limit_symbol, $limit_symbolvalue)  = static::_var_to_command_symbol_value($_limit, "LIMIT");

    $_query = "DELETE FROM `$_table` $where_commands $limit_command";

    $_symbolvalues = array_merge(
      $where_symbolvalues,
      $limit_symbolvalue
      );

    $query = self::_query_execute($_query, $_symbolvalues);
  }

  public static function tablenames() {
    $connection = ApplicationDatabase::connect();
    $name = $connection->query("select database()")->fetchColumn();
    $result = $connection->query("show tables");
    $tablenames = [];
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) $tablenames[] = $row["Tables_in_" . $name];
    return $tablenames;
  }

  public static function fieldnames($table) {
    $connection = ApplicationDatabase::connect();
    return $connection->query("DESCRIBE $table")->fetchAll(PDO::FETCH_COLUMN);
  }

  //////////////////////////////////////////////////
  // Private Functions
  //////////////////////////////////////////////////

  private static function _query_execute($_query, $_symbolvalues) {
    try {
      $connection = ApplicationDatabase::connect();
      $query = $connection->prepare($_query);

      foreach ($_symbolvalues as $symbol => $value)
        $query->bindValue($symbol, $value, self::_bindtype($value));

      // kick query
      $query->execute();

    } catch(PDOException $e) {
      ApplicationLogger::debug($_query);
      throw new Exception($e->getMessage());
    }

    return $query;
  }

  private static function _bindtype($value) {
    if     (is_int($value))  return PDO::PARAM_INT;
    elseif (is_bool($value)) return PDO::PARAM_BOOL;
    elseif (is_null($value)) return PDO::PARAM_NULL;
    else                     return PDO::PARAM_STR;
  }

  // [
  //   ["field" => "first_name", value => "Gökhan", "mark" => "=", "logic" => "AND"]
  // ]
  // >>>
  // array(
  //   "WHERE first_name=:WHERE_0_0",
  //   "first_name=:WHERE_0_0"
  //   array(
  //     ":WHERE_0_0" => "Gökhan"
  //   )
  // )

  // [
  //   ["field" => "first_name", value => "Gökhan", "mark" => "=", "logic" => "AND"],
  //   ["field" => "last_name", value => "Demir", "mark" => "=", "logic" => "AND"]
  // ]
  // >>>
  // array(
  //   "WHERE first_name=:WHERE_0_0 AND last_name=:WHERE_1_0",
  //   "first_name=:WHERE_0_0 AND last_name=:WHERE_1_0"
  //   array(
  //     ":WHERE_0_0" => "Gökhan",
  //     ":WHERE_1_0" => "Demir"
  //   )
  // )

  // [
  //   ["field" => "id", value => [1, 2, 3], "mark" => "IN", "logic" => "AND"]
  // ]
  // >>>
  // array(
  //   "WHERE id IN (:WHERE_0_0, :WHERE_0_1, :WHERE_0_2)",
  //   "id IN (:WHERE_0_0, :WHERE_0_1, :WHERE_0_2)"
  //   array(
  //     ":WHERE_0_0" => 1,
  //     ":WHERE_0_1" => 2,
  //     ":WHERE_0_2" => 3
  //   )
  // )

  // [
  //   ["field" => "id", value => [1, 12], "mark" => "BETWEEN", "logic" => "AND"]
  // ]
  // >>>
  // array(
  //   "WHERE id BETWEEN :WHERE_0_0 AND :WHERE_0_1",
  //   "id BETWEEN :WHERE_0_0 AND :WHERE_0_1"
  //   array(
  //     ":WHERE_0_0" => 1,
  //     ":WHERE_0_1" => 2
  //   )
  // )

  private static function _where_to_command_symbol_symbolvalue($_list) {

    if (!empty($_list)) {

      $symbols = "";

      // [":first_name" => "Gökhan", ":last_name" => "Demir"]
      $symbol_and_values = [];

      foreach ($_list as $index => $hash) {

        $unique_symbol_prefix = "WHERE_" . $index;
        $logic = ($index == 0) ? "" : " " . $hash["logic"] . " ";

        if (in_array($hash["mark"], static::$where_marks_null)) {

          $symbols .= $logic . $hash["field"] . " " . $hash["mark"];

        } elseif (in_array($hash["mark"], static::$where_marks_in)) {

          list($in_command, $in_symbols, $in_symbolvalues) = static::_list_to_command_symbol_symbolvalue($hash["value"], $unique_symbol_prefix);
          $symbols .= $logic . $hash["field"] . " " . $hash["mark"] . " " . "(" . $in_symbols . ")";
          $symbol_and_values = array_merge($symbol_and_values, $in_symbolvalues);

        } elseif (in_array($hash["mark"], static::$where_marks_between)) {

          list($between_command, $between_symbols, $between_symbolvalues) = static::_list_to_command_symbol_symbolvalue($hash["value"], $unique_symbol_prefix, "AND");
          $symbols .= $logic . $hash["field"] . " " . $hash["mark"] . " " . $between_symbols;
          $symbol_and_values = array_merge($symbol_and_values, $between_symbolvalues);

        } else {

          list($field_command, $field_symbol, $field_symbolvalue) = static::_list_to_command_symbol_symbolvalue([$hash["value"]], $unique_symbol_prefix);
          $symbols .= $logic . $hash["field"] . " " . $hash["mark"] . " " . $field_symbol;
          $symbol_and_values = array_merge($symbol_and_values, $field_symbolvalue);

        }

      }

      return array("WHERE $symbols", $symbols, $symbol_and_values);
    } else {
      return array("", "", []);
    }

  }

  // ["first_name" => "Gökhan", "last_name" => "Demir"]
  // >>>
  // array(
  //   "first_name DELIMITER last_name",
  //   "COMMAND :COMMAND_first_name DELIMITER :COMMAND_last_name",
  //    array(
  //      ":COMMAND_first_name" => "Gökhan",
  //      ":COMMAND_last_name" => "Demir"
  //    )
  // )

  private static function _hash_to_key_symbol_symbolvalue($_hash, $_command = "", $_delimiter = ",") {

    if (!empty($_hash)) {

    // ["first_name" => ":first_name", "last_name" => ":last_name"]
      $symbols = "";

    // [":first_name" => "Gökhan", ":last_name" => "Demir"]
      $symbol_and_values = [];
      $keys = "";

      foreach ($_hash as $key => $value) {

        $keys .= ($keys ? " $_delimiter " : "") . $key;
        $key_symbol = ":$_command" . "_" . str_replace(".", "_", $key);
        $symbols .= ($symbols ? " $_delimiter " : "") . $key_symbol;
        $symbol_and_values[$key_symbol] = $value;

      }
    }

    return array($keys, $symbols, $symbol_and_values);
  }

  // ["first_name" => "Gökhan", "last_name" => "Demir"]
  // >>>
  // array(
  //   "COMMAND first_name=:COMMAND_first_name DELIMITER last_name=:COMMAND_last_name",
  //   array(
  //     ":COMMAND_first_name" => "Gökhan",
  //     ":COMMAND_last_name" => "Demir"
  //   )
  // )

  private static function _hash_to_keysymbol_symbolvalue($_hash, $_command = "", $delimiter = ",") {

    if (!empty($_hash)) {

      // ["first_name" => ":first_name", "last_name" => ":last_name"]
      $key_and_symbols = "";

      // [":first_name" => "Gökhan", ":last_name" => "Demir"]
      $symbol_and_values = [];

      foreach ($_hash as $key => $value) {
        $key_symbol = ":$_command" . "_" . str_replace(".", "_", $key);
        $key_and_symbols .= ($symbol_and_values ? " $delimiter " : "") . "$key=$key_symbol";
        $symbol_and_values[$key_symbol] =  $value;
      }
    }

    return array($key_and_symbols ? "$_command $key_and_symbols" : "", $symbol_and_values);
  }

  // ["first_name", "last_name"]
  // >>>
  // array(
  //   "COMMAND :COMMAND_0 DELIMITER :COMMAND_1",
  //   ":COMMAND_0 DELIMITER :COMMAND_1",
  //   array(
  //     ":COMMAND_0" => "first_name",
  //     ":COMMAND_1" => "last_name"
  //   )
  // )

  private static function _list_to_command_symbol_symbolvalue($_list, $_command = "", $_delimiter = ",") {

    if (!empty($_list)) {

      // ":first_name, :last_name"
      $symbols = "";

      // [":first_name" => "first_name", ":last_name" => "last_name"]
      $symbol_and_values = [];

      // ORDER BY => ORDERBY, GROUP BY => GROUPBY
      $command = str_replace(" ", "", $_command);

      foreach ($_list as $index => $field) {

        $key_symbol = ":$command" . "_" . $index;
        $symbols .= ($symbols ? " $_delimiter " : "") . $key_symbol;
        $symbol_and_values[$key_symbol] = $field;

      }

      return array("$_command $symbols", $symbols, $symbol_and_values);
    } else {
      return array("", "", []);
    }

  }

  // 12
  // >>>
  // array(
  //    "COMMAND :COMMAND_12",
  //    ":COMMAND_12",
  //    array(
  //      ":COMMAND_12" => 12
  //    )
  // )

  private static function _var_to_command_symbol_value($_value, $_command = "") {

    if (isset($_value)) {
      $symbol = ":" . str_replace(" ", "", $_command); // ORDER BY => ORDERBY, GROUP BY => GROUPBY
      return array("$_command $symbol", $symbol, [$symbol => $_value]);
    } else {
      return array("", "", []);
    }
  }

}
?>

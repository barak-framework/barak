<?php

class ApplicationQuery {

  private $_select = [];   // list
  private $_table  = "";   // string
  private $_where  = [];   // hash
  private $_join   = [];   // hash
  private $_order  = [];   // list
  private $_group  = [];   // list
  private $_limit  = null; // int
  private $_offset = null; // int

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // |Magic Methods| : __construct, _get, _set, __call, __callStatic
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  public function __construct($tablename) {
    $this->_table = $tablename;
  }

  public function __get($field) {
    throw new Exception("Query içerisinde böyle bir anahtar mevcut değil → " . $field);
  }

  public function __set($field, $value) {
      throw new Exception("Query içerisinde böyle bir anahtar mevcut değil → " . $field);
  }

  public function __call($method, $args) {
    throw new Exception("Query içerisinde böyle bir method bulunamadı → " . $method);
  }

  public static function __callStatic($method, $args) {
    throw new Exception("Query içerisinde böyle bir statik method bulunamadı → " . $method);
  }

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // |Query Methods| : select, where, or_where, joins, order, group, limit, offset
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  public function select() {

    $fields = func_get_args();

    // merge field with table
    $fields = $this->_merge_fields_with_table($fields);

    // varsayılan olarak ekle, objeler yüklenirken her zaman id olmalıdır.
    $fields[] = $this->_table . ".id";
    $this->_select = $fields;

    return $this;
  }

  public function where($field = null, $value = null, $mark = "=", $logic = "AND") {
    $field = $this->_merge_field_with_table($field);

    // mark control
    $mark = strtoupper(trim($mark));
    if (is_null($value)) {
      $mark = ApplicationSql::$where_marks_null[0]; // "IS NULL";
      $value = NULL;
    } elseif (in_array($value, ApplicationSql::$where_marks_null, true)) {
      $mark = $value;
      $value = NULL;
    } elseif (in_array($mark, ApplicationSql::$where_marks_in)) {
      if (!is_array($value))
        throw new Exception(sprintf("WHERE %s için değer list olmalıdır → ", implode(',', ApplicationSql::$where_marks_in)) . $value);
    } elseif (in_array($mark, ApplicationSql::$where_marks_between)) {
      if (!is_array($value) or (is_array($value) and count($value) != 2))
        throw new Exception(sprintf("WHERE %s için değer list ve 2 değerli olmalıdır → ", implode(',', ApplicationSql::$where_marks_in)) . $value);
    } elseif (!in_array($mark, array_merge(ApplicationSql::$where_marks_other, ApplicationSql::$where_marks_like))) {
      throw new Exception("WHERE için tanımlı böyle bir işaretçi bulunamadı → " . $mark);
    }

    // logic control
    $logic = strtoupper(trim($logic));
    if (!in_array($logic, ApplicationSql::$where_logics))
      throw new Exception("WHERE de tanımlı böyle bir bağlayıcı bulunamadı → " . $logic);

    $this->_where[] = self::_set_to_where($field, $value, $mark, $logic);

    return $this;
  }

  public function or_where($field, $value = null, $mark = "=") {
    return $this->where($field, $value, $mark, "OR");
  }

  // #TODO INNER OR LEFT OUTER
  public function joins($belong_tables, $table = null) {

    // like for single variable : Category::load()->joins("article")->get_all();
    if (!is_array($belong_tables)) $belong_tables = [$belong_tables];

    ($table) ? ApplicationSql::check_table($table) : ($table = $this->_table);

    foreach ($belong_tables as $key => $value) {

      // find belong table
      list($belong_table, $belong_tables) = (is_array($value)) ? [$key, $value] : [$value, null];

      ApplicationSql::check_table($belong_table);
      $belong_table_fieldnames = ApplicationSql::fieldnames($belong_table);
      $foreign_key = strtolower($table) . "_id";

      // join işlemi için user.id = comment.user_id gibi where'ye eklemeler yap
      $this->_join[$belong_table] = $belong_table . "." . $foreign_key . "=" . str_replace("_", ".", $foreign_key);

      // have a more belong tables ?
      if ($belong_tables)
        $this->joins($belong_tables, $belong_table);

      // ilk tablo sütunları hariç join işleminde select çakışmasını önle. (Ör.: user.first_name as user_first_name gibi)
      // select kullanılmamışsa
      foreach ($belong_table_fieldnames as $field)
        $this->_select[] = $belong_table . "." . "$field as $belong_table" . "_" . $field;
        // $this->_select[] = $belong_table . "." . $field;
    }

    // ilk tablonun kendi select'i için eklemeler yap. (Ör.: user.first_name gibi)
    // select kullanılmamışsa
    foreach (ApplicationSql::fieldnames($this->_table) as $field)
      $this->_select[] = $this->_table . "." . $field;

    return $this;
  }

  public function order($field, $sort_type = "ASC") {
    $field = $this->_merge_field_with_table($field);

    // sort_type control
    $sort_type = strtoupper(trim($sort_type));
    if (!in_array($sort_type, ApplicationSql::$order_sort_type))
      throw new Exception("Order sorgusunda bilinmeyen parametre → " . $sort_type);

    $this->_order[] = "$field $sort_type";
    return $this;
  }

  public function group() {

    $fields = func_get_args();

    // merge field with table
    $fields = $this->_merge_fields_with_table($fields);

    $this->_group = $fields;
    return $this;
  }

  public function limit($limit = 1) {
    $limit = intval($limit);

    // limit control
    if ($limit < 0)
      throw new Exception("LİMİT'de değer, sıfır veya üstü olmalıdır → " . $limit);

    $this->_limit = $limit;
    return $this;
  }

  public function offset($offset = null) {
    $this->_offset = intval($offset);
    return $this;
  }

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // | Public Execute Methods | : get, get_all, pluck, count, update_all, delete_all, first, last
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  public function get() {
    if ($record = ApplicationSql::read($this->_select, $this->_table, $this->_where)) {
      return ApplicationModel::instance_old($this->_table, $record);
    }
    return null;
  }

  public function get_all() {
    $records = $this->_read_all();

    if ($records) {
      $objects = [];
      foreach ($records as $record)
        $objects[] = ApplicationModel::instance_old($this->_table, $record);
      return $objects;
    } else {
      return null;
    }
  }

  public function pluck($field) {
    $this->_select = [$this->_merge_field_with_table($field)];
    $records = $this->_read_all();

    if ($records) {
      foreach ($records as $record)
        $values[] = $record[$field];
      return $values;
    }
    return null;
  }

  public function count() {
    $field = "count(*)";
    if (empty($this->_group)) {
      $record = ApplicationSql::read([$field], $this->_table, $this->_where);
      return $record[$field] ?: null;
    } else {
      $this->_select = array_merge([$field], $this->_group);
      $records = $this->_read_all();
      return $records ?: null;
    }
  }

  public function update_all($sets) {
    // check sets
    foreach ($sets as $field => $value)
      ApplicationSql::check_field($field, $this->_table);

    ApplicationSql::update($this->_table, $sets, $this->_where, $this->_limit);
  }

  public function delete_all() {
    ApplicationSql::delete($this->_table, $this->_where, $this->_limit);
  }

  public function first($limit = 1) {
    $this->order("id", "ASC");
    $this->limit($limit);
    if ($this->_limit != 1)
      return self::get_all();
    return self::get();
  }

  public function last($limit = 1) {
    $this->order("id", "DESC");
    $this->limit($limit);
    if ($this->_limit != 1)
      return self::get_all();
    return self::get();
  }

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Private Helper Methods |Main| : _read_all, _merge_field_with_table, _merge_fields_with_table
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  private function _read_all($all = false) {
    return ApplicationSql::read_all($this->_select, $this->_table, $this->_join, $this->_where, $this->_order, $this->_group, $this->_limit, $this->_offset);
  }

  private function _merge_field_with_table($field) {

    if (strpos($field, '.') !== false) {
      list($table, $field) = array_map('trim', explode('.', $field));
      ApplicationSql::check_table($table);
      ApplicationSql::check_field($field, $table);
    } else {
      $table = $this->_table;
      ApplicationSql::check_field($field, $table);
    }
    return strtolower("$table.$field");
  }

  private function _merge_fields_with_table($fields) {
    foreach ($fields as $index => $field)
      $fields[$index] = $this->_merge_field_with_table($field);
    return $fields;
  }

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Private Static Methods |Helper| : _set_to_where
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  private static function _set_to_where($field = null, $value = null, $mark = "=", $logic = "AND") {
    return compact("field", "value", "mark", "logic");
  }

}
?>

<?php

class ApplicationModel {

  // Query

  private $_select = [];   // list
  private $_table  = "";   // string
  private $_where  = [];   // hash
  private $_join   = [];   // hash
  private $_order  = [];   // list
  private $_group  = [];   // list
  private $_limit  = null; // int
  private $_offset = null; // int

  // One Record

  private $_fields = [];      // for only one record CRUD
  private $_new_record_state; // for only one record state new/old ?

  ////////////////////////////////////////////////////////////////////////////////////////////////////////
  // |Magic Methods| : __construct, _get, _set, __call, __callStatic
  ////////////////////////////////////////////////////////////////////////////////////////////////////////

  private function __construct($tablename) {
    $this->_table = $tablename;
  }

  public function __get($field) {

    if (array_key_exists($field, $this->_fields)) {
      return $this->_fields[$field];
    } else if (in_array($field, ApplicationMySQL::tablenames())) {

      $belong_table = $field; // user
      $foreign_key = $field . "_id"; // user_id

      if (!in_array($foreign_key, ApplicationMySQL::fieldnames($this->_table)))
        throw new Exception("Modele ait olan böyle foreign key mevcut değil → " . $foreign_key);

      return $belong_table::find($this->_fields[$foreign_key]);
    } else {
      preg_match_all("/all_of_.*/", $field, $matches);
      $matches = $matches[0];

      if ($matches) {
        $field = substr($field, 7);
        if (in_array($field, ApplicationMySQL::tablenames())) {

          $owner_table = ucfirst($field); // model name
          $owner_key = strtolower($this->_table) . "_id";

          return $owner_table::load()->where($owner_key, $this->_fields["id"])->get_all();
        }
      }
    }

    throw new Exception("Modele ait böyle bir anahtar mevcut değil → " . $field);
  }

  public function __set($field, $value) {
    if (array_key_exists($field, $this->_fields))
      $this->_fields[$field] = $value;
    else
      throw new Exception("Tabloda yüklenecek böyle bir anahtar mevcut değil → " . $field);
  }

  public function __call($method, $args) {
    throw new Exception("Modelde böyle bir method bulunamadı → " . $method);
  }

  public static function __callStatic($method, $args) {
    throw new Exception("Modelde böyle bir static method bulunamadı → " . $method);
  }

  ////////////////////////////////////////////////////////////////////////////////////////////////////////
  // |One Record Methods| : draft, create, save, destroy
  ////////////////////////////////////////////////////////////////////////////////////////////////////////

  public static function draft($sets = null) {

    // check sets
    $table = self::tablename();
    $object = self::instance_model_new($table);

    if ($sets) {
      foreach ($sets as $field => $value) {
        self::check_field($field, $object->_table);
        $object->$field = $value;
      }
    }

    return $object;
  }

  public static function create($fields) {
    return self::draft($fields)->save();
  }

  public function save() {

    if (!$this->_new_record_state) {

      ApplicationMySQL::update($this->_table, $this->_fields, [self::set_to_where("id", intval($this->_fields["id"]))], 1);

    } else {

      // kayıt tamamlandıktan sonra en son id döndür
      $id = ApplicationMySQL::create($this->_table, $this->_fields);

      // artık yeni kayıt değil
      $this->_new_record_state = false;

      // id'si olan kaydın alan değerlerini yeni kayıtta güncelle, (ör.: id otomatik olarak alması için)
      $table = $this->_table;
      $this->_fields = $table::load()->where("id", intval($id))->get()->_fields;
      return $this;
    }
  }

  public function destroy() {
    ApplicationMySQL::delete($this->_table, [self::set_to_where("id", intval($this->_fields["id"]))], 1);
  }

  ////////////////////////////////////////////////////////////////////////////////////////////////////////
  // |Query Methods| : load, select, where, or_where, joins, order, group, limit, offset
  ////////////////////////////////////////////////////////////////////////////////////////////////////////

  public static function load() {
    $table = self::tablename();
    return self::instance_query($table);
  }

  public function select() {

    $fields = func_get_args();

    // merge field with table
    $fields = $this->_merge_fields_with_table($fields);

    // varsayılan olarak ekle, objeler yüklenirken her zaman id olmalıdır.
    $this->_select = array_merge($fields, [$this->_table . ".id"]);

    return $this;
  }

  public function where($field = null, $value = null, $mark = "=", $logic = "AND") {
    $field = $this->_merge_field_with_table($field);

    // mark control
    $mark = strtoupper(trim($mark));
    if (is_null($value)) {
      $mark = "IS NULL";
      $value = NULL;
    } elseif (in_array($value, ApplicationMySQL::$where_marks_null, true)) {
      $mark = $value;
      $value = NULL;
    } elseif (in_array($mark, ApplicationMySQL::$where_marks_in)) {
      if (!is_array($value))
        throw new Exception(sprintf("WHERE %s için değer list olmalıdır → ", implode(',', ApplicationMySQL::$where_marks_in)) . $value);
    } elseif (in_array($mark, ApplicationMySQL::$where_marks_between)) {
      if (!is_array($value) or (is_array($value) and count($value) != 2))
        throw new Exception(sprintf("WHERE %s için değer list ve 2 değerli olmalıdır → ", implode(',', ApplicationMySQL::$where_marks_in)) . $value);
    } elseif (!in_array($mark, array_merge(ApplicationMySQL::$where_marks_other, ApplicationMySQL::$where_marks_like))) {
      throw new Exception("WHERE için tanımlı böyle bir işaretçi bulunamadı → " . $mark);
    }

    // logic control
    $logic = strtoupper(trim($logic));
    if (!in_array($logic, ApplicationMySQL::$where_logics))
      throw new Exception("WHERE de tanımlı böyle bir bağlayıcı bulunamadı → " . $logic);

    $this->_where[] = self::set_to_where($field, $value, $mark, $logic);

    return $this;
  }

  public function or_where($field, $value = null, $mark = "=") {
    return $this->where($field, $value, $mark, "OR");
  }

  // #TODO INNER OR LEFT OUTER
  public function joins($belong_tables, $table = null) {

    // like for single variable : Category::load()->joins("article")->get_all();
    if (!is_array($belong_tables)) $belong_tables = [$belong_tables];

    ($table) ? self::check_table($table) : ($table = $this->_table);

    foreach ($belong_tables as $key => $value) {

      // find belong table
      list($belong_table, $belong_tables) = (is_array($value)) ? [$key, $value] : [$value, null];

      self::check_table($belong_table);
      $belong_table_fieldnames = ApplicationMySQL::fieldnames($belong_table);
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
    foreach (ApplicationMySQL::fieldnames($this->_table) as $fieldname)
      $this->_select[] = $this->_table . "." . $fieldname;

    return $this;
  }

  public function order($field, $sort_type = "ASC") {
    $field = $this->_merge_field_with_table($field);

    // sort_type control
    $sort_type = strtoupper(trim($sort_type));
    if (!in_array($sort_type, ApplicationMySQL::$order_sort_type))
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

  ////////////////////////////////////////////////////////////////////////////////////////////////////////
  // | Public Execute Methods | : get, get_all, pluck, count, update_all, delete_all, first, last
  ////////////////////////////////////////////////////////////////////////////////////////////////////////

  public function get() {
    if ($record = ApplicationMySQL::read($this->_select, $this->_table, $this->_where)) {
      return self::instance_model_old($this->_table, $record);
    }
    return null;
  }

  public function get_all() {
    $records = $this->_read_all();

    if ($records) {
      $objects = [];
      foreach ($records as $record)
        $objects[] = self::instance_model_old($this->_table, $record);
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
      $record = ApplicationMySQL::read([$field], $this->_table, $this->_where);
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
      self::check_field($field, $this->_table);

    ApplicationMySQL::update($this->_table, $sets, $this->_where, $this->_limit);
  }

  public function delete_all() {
    ApplicationMySQL::delete($this->_table, $this->_where, $this->_limit);
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

  ////////////////////////////////////////////////////////////////////////////////////////////////////////
  // |Query Helper Methods| : all, create, unique, find, find_all, exists, update, delete
  ////////////////////////////////////////////////////////////////////////////////////////////////////////

  public static function all() {
    return self::load()->get_all();
  }

  public static function unique($sets = null) {
    $record = self::load();

    // check and where sets
    foreach ($sets as $field => $value)
      $record = $record->where($field, $value);

    return $record->get();
  }

  public static function find($id) {
    return self::load()->where("id", intval($id))->get();
  }

  public static function find_all($ids = null) {

    // array control
    if (!is_array($ids))
      throw new Exception("find_all sorgusunda değer list olmalıdır → " . $sort_type);

    // int check
    foreach ($ids as $index => $id)
      $ids[$index] = intval($id);

    return self::load()->where("id", $ids, "IN")->get_all();
  }

  public static function exists($id) {
    return self::load()->where("id", intval($id))->get() ? true : false;
  }

  public static function update($id, $sets) {

    // check sets
    $object = self::load();
    foreach ($sets as $field => $value)
      self::check_field($field, $object->_table);

    // find record and set fields
    if ($record = $object->where("id", intval($id))->get()) {
      foreach ($sets as $key => $value)
        $record->$key = $value;
      $record->save();
    }

    return $record;
  }

  public static function delete($id) {
    // find record and destroy
    if ($record = self::load()->where("id", intval($id))->get())
      $record->destroy();
  }

  ////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Private Helper Methods |Main| : _read_all, _merge_field_with_table, _merge_fields_with_table
  ////////////////////////////////////////////////////////////////////////////////////////////////////////

  private function _read_all($all = false) {
    return ApplicationMySQL::read_all($this->_select, $this->_table, $this->_join, $this->_where, $this->_order, $this->_group, $this->_limit, $this->_offset);
  }

  private function _merge_field_with_table($field) {

    if (strpos($field, '.') !== false) {
      list($table, $field) = array_map('trim', explode('.', $field));
      self::check_table($table);
      self::check_field($field, $table);
    } else {
      $table = $this->_table;
      self::check_field($field, $table);
    }
    return strtolower("$table.$field");
  }

  private function _merge_fields_with_table($fields) {
    foreach ($fields as $index => $field)
      $fields[$index] = $this->_merge_field_with_table($field);
    return $fields;
  }

  ////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Private Static Methods |Main| : tablename, instance_query, instance_model_new, instance_model_old
  ////////////////////////////////////////////////////////////////////////////////////////////////////////

  private static function tablename() {
    $table = strtolower(get_called_class());
    self::check_table($table);
    return $table;
  }

  private static function instance_query($modelname) {
    $object = new $modelname($modelname);
    return $object;
  }

  private static function instance_model_new($modelname) {
    $object = new $modelname($modelname);
    $object->_new_record_state = true;
    foreach (ApplicationMySQL::fieldnames($modelname) as $fieldname)
      $object->_fields[$fieldname] = null;
    return $object;
  }

  private static function instance_model_old($modelname, $fields) {
    $object = new $modelname($modelname);
    $object->_new_record_state = false;
    $object->_fields = $fields;
    return $object;
  }

  ////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Private Static Methods |Helper| : set_to_where, check_table, check_field
  ////////////////////////////////////////////////////////////////////////////////////////////////////////

  private static function set_to_where($field = null, $value = null, $mark = "=", $logic = "AND") {
    return compact("field", "value", "mark", "logic");
  }

  private static function check_table($table) { // $table = $this->_table or static table
    if (!in_array($table, ApplicationMySQL::tablenames()))
      throw new Exception("Veritabanında böyle bir tablo mevcut değil → " . $table);
  }

  private static function check_field($field, $table) {
    $fields = ApplicationMySQL::fieldnames($table);
    if (!in_array($field, $fields))
      throw new Exception("| $table | tablosunda böyle bir anahtar mevcut değil → " . $field);
  }

}
?>

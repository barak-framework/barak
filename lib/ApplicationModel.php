<?php

class ApplicationModel {

  // One Record

  private $_table = "";
  private $_fields = [];      // for only one record CRUD
  private $_new_record_state; // for only one record state new/old ?

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // |Magic Methods| : __construct, __debugInfo, __get, __set, __call, __callStatic
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  private function __construct($table, $fields = NULL) {
    $this->_table = $table;
  }

  final public function __debugInfo() {
    return $this->_fields;
  }

  final public function __get($field) {

    if (array_key_exists($field, $this->_fields)) {
      return $this->_fields[$field];
    } else if (in_array($field, ApplicationSql::tablenames())) {

      $belong_table = $field; // user
      $foreign_key = $field . "_id"; // user_id

      if (!in_array($foreign_key, ApplicationSql::fieldnames($this->_table)))
        throw new Exception("Modele ait olan böyle foreign key mevcut değil → " . $foreign_key);

      return $belong_table::find($this->_fields[$foreign_key]);
    } else {
      preg_match_all("/all_of_.*/", $field, $matches);
      $matches = $matches[0];

      if ($matches) {
        $field = substr($field, 7);
        if (in_array($field, ApplicationSql::tablenames())) {

          $owner_table = ucfirst($field); // model name
          $owner_key = strtolower($this->_table) . "_id";

          return $owner_table::load()->where($owner_key, $this->_fields["id"])->get_all();
        }
      }
    }

    throw new Exception("Modele ait böyle bir anahtar mevcut değil → " . $field);
  }

  final public function __set($field, $value) {
    if (array_key_exists($field, $this->_fields))
      $this->_fields[$field] = $value;
    else
      throw new Exception("Tabloda yüklenecek böyle bir anahtar mevcut değil → " . $field);
  }

  final public function __call($method, $args) {
    throw new Exception("Modelde böyle bir method bulunamadı → " . $method);
  }

  final public static function __callStatic($method, $args) {
    throw new Exception("Modelde böyle bir static method bulunamadı → " . $method);
  }

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // |Model Record Methods| : draft, create, save, destroy
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  final public static function draft($fields = null) {

    // check sets
    $table = self::_tablename();
    $object = ApplicationModel::instance_new($table);

    if ($fields) {
      foreach ($fields as $field => $value) {
        ApplicationSql::check_field($field, $object->_table);
        $object->$field = $value;
      }
    }

    return $object;
  }

  final public static function create($fields) {
    return self::draft($fields)->save();
  }

  final public function save() {

    if (!$this->_new_record_state) {

      ApplicationSql::update($this->_table, $this->_fields, [self::_set_to_where("id", intval($this->_fields["id"]))], 1);

    } else {

      // kayıt tamamlandıktan sonra en son id döndür
      $id = ApplicationSql::create($this->_table, $this->_fields);

      // artık yeni kayıt değil
      $this->_new_record_state = false;

      // id'si olan kaydın alan değerlerini yeni kayıtta güncelle, (ör.: id otomatik olarak alması için)
      $table = $this->_table;
      $this->_fields = $table::load()->where("id", intval($id))->get()->_fields;
      return $this;
    }
  }

  final public function destroy() {
    ApplicationSql::delete($this->_table, [self::_set_to_where("id", intval($this->_fields["id"]))], 1);
  }

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // |ModelQuery Kick Method| : load
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  final public static function load() {
    $table = self::_tablename();
    $object = new ApplicationQuery($table);
    return $object;
  }

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Public Static |Alias Methods of ModelQuery| : all, create, unique, find, find_all, exists, update, delete
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  final public static function all() {
    return self::load()->get_all();
  }

  final public static function unique($sets = null) {
    $record = self::load();

    // check and where sets
    foreach ($sets as $field => $value)
      $record = $record->where($field, $value);

    return $record->get();
  }

  final public static function find($id) {
    return self::load()->where("id", intval($id))->get();
  }

  final public static function find_all($ids = null) {

    // array control
    if (!is_array($ids))
      throw new Exception("find_all sorgusunda değer list olmalıdır → " . $sort_type);

    // int check
    foreach ($ids as $index => $id)
      $ids[$index] = intval($id);

    return self::load()->where("id", $ids, "IN")->get_all();
  }

  final public static function exists($id) {
    return self::load()->where("id", intval($id))->get() ? true : false;
  }

  final public static function update($id, $sets) {

    // check sets
    $object = self::load();
    foreach ($sets as $field => $value)
      ApplicationSql::check_field($field, $object->_table);

    // find record and set fields
    if ($record = $object->where("id", intval($id))->get()) {
      foreach ($sets as $key => $value)
        $record->$key = $value;
      $record->save();
    }

    return $record;
  }

  final public static function delete($id) {
    // find record and destroy
    if ($record = self::load()->where("id", intval($id))->get())
      $record->destroy();
  }

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Public Static |Instance Methods| : instance_model_new, instance_model_old
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  final public static function instance_new($modelname) {
    $object = new $modelname($modelname);
    $object->_new_record_state = TRUE;
    foreach (ApplicationSql::fieldnames($modelname) as $field)
      $object->_fields[$field] = NULL;
    return $object;
  }

  /*
  ApplicationModelQuery verdiği verilerde
  tablo ve alan bilgileri kontrol edildiği için
  direk yüklenmesini sağlayan fonksyion
  */
  final public static function instance_old($modelname, $fields) {
    $object = new $modelname($modelname);
    $object->_new_record_state = FALSE;
    $object->_fields = $fields;
    return $object;
  }

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Private Static |Main Methods| : _tablename
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  private static function _tablename() {
    $table = strtolower(get_called_class());
    ApplicationSql::check_table($table);
    return $table;
  }

  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // Private Static |Helper Methods| : _set_to_where
  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////

  private static function _set_to_where($field = null, $value = null, $mark = "=", $logic = "AND") {
    return compact("field", "value", "mark", "logic");
  }

}
?>

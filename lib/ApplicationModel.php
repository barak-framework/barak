<?php

class ApplicationModel {

  private $_select = [];     // list
  private $_table  = "";     // string
  private $_where  = [];     // hash
  private $_join   = [];     // hash
  private $_order  = [];     // list
  private $_group  = [];     // list
  private $_limit  = null;   // int
  private $_offset = null;   // int

  private $_fields;
  private $_new_record_state;

  //////////////////////////////////////////////////
  // System Main Functions
  //////////////////////////////////////////////////

  // Ekleme ör.: 1

  // $user = User::new();
  // $user->first_name ="Gökhan";
  // $user->last_name ="Demir";
  // $user->save(); // yeni kayıt eklendi

  // Ekleme ör.: 2

  // $user = User::new(["last_name" => "Demir"]);
  // $user->first_name ="Gökhan";
  // $user->save(); // yeni kayıt eklendi

  // Ekleme ör.: 3

  // $user = User::new(["last_name" => "Demir"])->save();

  // Günceleme ör.: 1

  // $this->users = User::all(); // tüm users içinde id olduğu için kayıtları güncelleme özelliği oluyor.
  // foreach ($this->users as $user) {
  //   $user->first_name = "Gökhan";
  //   $user->save();
  // }

  // ok v3
  private function __construct() {
    $this->_new_record_state = true;

    foreach (ApplicationSql::fieldnames(self::tablename()) as $fieldname)
      $this->_fields[$fieldname] = null;
  }

  // Alma 1:

  // $user = User::find(1);
  // echo $user->_first_name;

  // Alma 2:

  // $comment = Comment::find(1); // ["id", "name", "content", "user_id"]
  // echo $comment->user->first_name;

  // ok v3
  public function __get($field) {

    if (array_key_exists($field, $this->_fields)) {
      return $this->_fields[$field];
    } else if (in_array($field, ApplicationSql::tablenames())) { // Büyükten -> Küçüğe

      $belong_table = $field; // user
      $foreign_key = $field . "_id"; //user_id

      if (!in_array($foreign_key, ApplicationSql::fieldnames($this->_table)))
        throw new BelongNotFoundException("Tabloya ait olan böyle foreign key yok", $foreign_key);

      return $belong_table::find($this->_fields[$foreign_key]);
    } else {
      preg_match_all("/all_of_.*/", $field, $matches);
      $matches = $matches[0];

      if ($matches) {
        $field = substr($field, 7);
        if (in_array($field, ApplicationSql::tablenames())) {

          $owner_table = ucfirst($field); // model name
          $owner_key = strtolower($this->_table) . "_id";

          return $owner_table::load()->where($owner_key, $this->_fields["id"])->take();
        }
      }
    }

    throw new FieldNotFoundException("Tabloda getirilecek böyle bir anahtar mevcut değil", $field);
  }

  // ok v3
  public function __set($field, $value) {
    if (array_key_exists($field, $this->_fields))
      $this->_fields[$field] = $value;
    else
      throw new FieldNotFoundException("Tabloda yüklenecek böyle bir anahtar mevcut değil", $field);
  }

  // ok v3
  public function __call($method, $args) {
    throw new MethodNotFoundException("Modelde böyle bir method bulunamadı", $method);
  }

  //////////////////////////////////////////////////
  // Public Functions
  //////////////////////////////////////////////////

  // FETCH Functions Start

  // ok v3
  public function take() {

    $records = $this->_read();

    if ($records) {
      $objects = [];
      foreach ($records as $record) {
        $object = self::clone($this->_table);
        $object->_fields = $record;
        $object->_new_record_state = false;

        $objects[] = $object;
      }
      return (count($records) == 1) ? $objects[0] : $objects;
    } else {
      return null;
    }
  }

  // ok v3
  public function pluck($field) {

    $this->_select = [$this->_merge_field_with_table($field)];
    $records = $this->_read();

    if ($records) {
      foreach ($records as $record)
        $values[] = $record[$field];

      return $values;
    }
    return null;
  }

  // ok v3
  public function count() {
    $field = "count(*)";
    $this->_select = [$field];
    $this->_limit = 1;

    $record = $this->_read();

    return $record[$field] ?: null;
  }

  // ok v3
  public function update_all($sets) {
    foreach ($sets as $field => $value)
      self::check_field($field, $this->_table);
    ApplicationSql::update($this->_table, $sets, $this->_where);
  }

  // ok v3
  public function delete_all() {
    ApplicationSql::delete($this->_table, $this->_where, $this->_limit);
  }

  // FETCH Functions End

  // $user = new User();
  // $user->first_name ="Gökhan";
  // $user->save(); // kayıt ettikten sonra otomatik id değeri alır.

  // print_r($user);
  // [_fields:ApplicationModel:private] => Array ( [first_name] => Gökhan [last_name] => [username] => [password] => [content] => [id] => 368 )

  // ok v3
  public function save() {

    if (!$this->_new_record_state) {

      ApplicationSql::update($this->_table, $this->_fields, [self::set_to_where("id", intval($this->_fields["id"]))], null);

    } else {

      // kayıt tamamlandıktan sonra en son id döndür
      $id = ApplicationSql::create($this->_table, $this->_fields);

      // artık yeni kayıt değil
      $this->_new_record_state = false;

      // id'si olan kaydın alan değerlerini yeni kayıtta güncelle, (ör.: id otomatik olarak alması için)
      $table = $this->_table;
      $this->_fields = $table::find($id)->_fields;
    }
  }

  // ok v3
  public function destroy() {
    ApplicationSql::delete($this->_table, [self::set_to_where("id", intval($this->_fields["id"]))], null);
  }

  // $users = User::load()->select("first_name, last_name")->get();
  // ["user.firs_name, user.last_name"]

  // ok v3
  public function select($fields = ['*']) {

    $fields = is_array($fields) ? $fields : func_get_args();

    // merge field with table
    $fields = $this->_merge_fields_with_table($fields);

    // varsayılan olarak ekle, objeler yüklenirken her zaman id olmalıdır.
    $fields = array_merge($fields, [$this->_table . ".id"]);

    $this->_select = $fields; // ["user.first_name", "user.last_name", "comment.name"]

    return $this;
  }

/*

  MARK

  field1 =  value1
  field1 <> value1
  field1 >  value1
  field1 <  value1
  field1 >= value1
  field1 <= value1

  field1 BETWEEN value1 and value2
  field1 NOT BEETWEEN value1 and value2
  fiedl1 LIKE value
  field1 NOT_LIKE value
  field1 IN (value1, value2)
  fiedl1 NOT IN (value1, value2)

*/

  // ok v3
  public function where($field = null, $value = null, $mark = "=", $logic = "AND") {
    $field = $this->_merge_field_with_table($field);

    // mark control
    $mark = strtoupper(trim($mark));
    if (is_null($value)) {
      $mark = "IS NULL";
      $value = NULL;
    } elseif (in_array($value, ApplicationSql::$where_marks_null)) {
      $mark = $value;
      $value = NULL;
    } elseif (in_array($mark, ApplicationSql::$where_marks_in)) {
      if (!is_array($value))
        throw new BelongNotFoundException(sprintf("WHERE %s için değer list olmalıdır", implode(',', ApplicationSql::$where_marks_in)), $value);
    } elseif (in_array($mark, ApplicationSql::$where_marks_between)) {
      if (!is_array($value) or (is_array($value) and count($value) != 2))
        throw new BelongNotFoundException(sprintf("WHERE %s için değer list ve 2 değerli olmalıdır", implode(',', ApplicationSql::$where_marks_in)), $value);
    } elseif (!in_array($mark, array_merge(ApplicationSql::$where_marks_other, ApplicationSql::$where_marks_like))) {
      throw new BelongNotFoundException("WHERE için tanımlı böyle bir işaretçi bulunamadı", $mark);
    }

    // logic control
    $logic = strtoupper(trim($logic));
    if (!in_array($logic, ApplicationSql::$where_logics))
      throw new BelongNotFoundException("WHERE de tanımlı böyle bir bağlayıcı bulunamadı", $logic);

    $this->_where[] = self::set_to_where($field, $value, $mark, $logic);

    return $this;
  }

  // ok v3
  public function or_where($field, $value = null, $mark = "=") {
    return $this->where($field, $value, $mark, "OR");
  }

  // $categories = Category::load()->joins("article")->take();
  // $categories = Category::load()->joins(["article" => "comment"])->take();
  // $categories = Category::load()->joins(["article" => ["comment" => ["tag"]]])->take();
  // $categories = Category::load()->joins(["article" => ["comment" => ["tag"], "like"]])->take();
  // $categories = Category::load()->joins(["article" => ["comment" => ["tag"], "like"], "document"])->take();
  // $categories = Category::load()->joins(["article", "document"])->take();

  // #TODO INNER OR LEFT OUTER
  // ok v3
  public function joins($belong_tables, $table = null) {

    // like for single variable : Category::load()->joins("article")->take();
    if (!is_array($belong_tables)) $belong_tables = [$belong_tables];

    ($table) ? self::check_table($table) : ($table = $this->_table);

    foreach ($belong_tables as $key => $value) {

      // find belong table
      list($belong_table, $belong_tables) = (is_array($value)) ? [$key, $value] : [$value, null];

      self::check_table($belong_table);
      $belong_table_fieldnames = ApplicationSql::fieldnames($belong_table);
      $foreign_key = strtolower($table) . "_id";

      // join işlemi için user.id = comment.user_id gibi where'ye eklemeler yap
      $this->_join[$belong_table] = $belong_table . "." . $foreign_key . "=" . str_replace("_", ".", $foreign_key);

      // join işleminde select çakışması önlenmesi için User.first_name, User.last_name gibi ekleme yap
      foreach ($belong_table_fieldnames as $field)
        $this->_select[] = $belong_table . "." . $field;

      // have a more belong tables ?
      if ($belong_tables)
        $this->joins($belong_tables, $belong_table);

    }

    // tablonun kendi select için eklemeler yap
    foreach (ApplicationSql::fieldnames($this->_table) as $fieldname)
      $this->_select[] = $this->_table . "." . $fieldname;

    return $this;
  }

  // ok v3
  public function order($field, $sort_type = "ASC") {
    $field = $this->_merge_field_with_table($field);

    // sort_type control
    $sort_type = strtoupper(trim($sort_type));
    if (!in_array($sort_type, ApplicationSql::$order_sort_type))
      throw new FieldNotFoundException("Order sorgusunda bilinmeyen parametre", $sort_type);

    $this->_order[] = "$field $sort_type";
    return $this;
  }

  // ok v3
  public function group($field) {
    $field = $this->_merge_field_with_table($field);
    $this->_group[] = $field;
    return $this;
  }

  // ok v3
  public function limit($limit = null) {
    $this->_limit = intval($limit);
    return $this;
  }

  // ok v3
  public function offset($offset = null) {
    $this->_offset = intval($offset);
    return $this;
  }

  //////////////////////////////////////////////////
  // Public Static Functions
  //////////////////////////////////////////////////

  // $user = User::unique(["username" => "gdemir", "password" => "123456"]);
  // echo $user->first_name;

  // ok v3
  public static function unique($sets = null) {
    $record = self::load();

    // where sets
    foreach ($sets as $field => $value)
      $record = $record->where($field, $value);

    return $record->limit(1)->take();
  }

  // Ör. 1:

  // $user = User::draft();
  // $user->first_name = "Gökhan";
  // $user->save();
  // print_r($user); // otomatik id alır

  // Ör. 2:

  // $user = User::draft(["first_name" => "Gökhan"])->save();

  // ok v3
  public static function draft($sets = null) {

    // check $sets
    $object = self::load();
    foreach ($sets as $field => $value)
      self::check_field($field, $object->_table);

    $object->_fields = $sets;
    return $object;
  }

  // no check tablename and clone

  // ok v3
  public static function load($sets = null) {
    $table = self::tablename();
    self::check_table($table);
    return self::clone($table, $sets);
  }

  // $users = User::all(); // return User objects array
  //
  // foreach ($users as $user) {
  //   $user->first_name = "Gökhan";
  //   $user->save();
  // }

  // ok v3
  public static function all() {
    return self::load()->take();
  }

  // User::create(["first_name" => "Gökhan"]);

  // ok v3
  public static function create($fields) {
    self::draft($fields)->save();
  }

  // User::first();

  // ok v3
  public static function first($limit = 1) {
    return self::load()->order("id", "asc")->limit($limit)->take();
  }

  // User::last();

  // ok v3
  public static function last($limit = 1) {
    return self::load()->order("id", "desc")->limit($limit)->take();
  }

  // $user = User::find(1); // return User objects
  // $user->first_name = 'foo';
  // $user->save();

  // ok v3
  public static function find($id) {
    return self::unique(["id" => intval($id)]); // convert to where struct and pass
  }

  // $users = User::find_all([1, 2, 3]); // return User objects array
  //
  // foreach ($users as $user) {
  //   $user->first_name = "Gökhan";
  //   $user->save();
  // }

  // ok v3
  public static function find_all($ids = null) {

    // array control
    if (!is_array($ids))
      throw new FieldNotFoundException("find_all sorgusunda değer list olmalıdır", $sort_type);

    // int check
    foreach ($ids as $index => $id)
      $ids[$index] = intval($id);

    return self::load()->where("id", $ids, "IN")->take();
  }

  // ok v3
  public static function exists($id) {
    return self::find($id) ? true : false;
  }

  // ok v3
  public static function update($id, $sets) {

    // check sets
    $object = self::load();
    foreach ($sets as $field => $value)
      self::check_field($field, $object->_table);

    // find record and set fields
    if ($record = $object->where("id", intval($id))->take()) {
      $record->_fields = $sets;
      $record->save();
    }

    return $record;
  }

  // ok v3
  public static function delete($id) {

    // find record and destroy
    if ($record = self::find($id))
      $record->destroy();
    return $record;
  }

  //////////////////////////////////////////////////
  // Private Functions
  //////////////////////////////////////////////////

  // ok v3
  private function _read() {
    return ApplicationSql::read($this->_select, $this->_table, $this->_join, $this->_where, $this->_order, $this->_group, $this->_limit, $this->_offset);
  }

  // ok v3
  private function _merge_fields_with_table($fields) {
    foreach ($fields as $index => $field)
      $fields[$index] = $this->_merge_field_with_table($field);
    return $fields;
  }

  // ok v3
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

  //////////////////////////////////////////////////
  // Private Static Functions
  //////////////////////////////////////////////////

  // ok v3
  private static function tablename() {
    return strtolower(get_called_class());
  }

  // ok v3
  private static function clone($modelname, $sets = null) {
    $object = new $modelname($sets);
    $object->_table = $modelname;
    return $object;
  }

  // ok v3
  private static function set_to_where($field = null, $value = null, $mark = "=", $logic = "AND") {
    return compact("field", "value", "mark", "logic");
  }

  // ok v3
  private static function check_table($table) { // $table = $this->_table or static table
    if (!in_array($table, ApplicationSql::tablenames()))
      throw new TableNotFoundException("Veritabanında böyle bir tablo mevcut değil", $table);
  }

  // ok v3
  private static function check_field($field, $table) {
    $fields = ApplicationSql::fieldnames($table);
    if (!in_array($field, $fields))
      throw new FieldNotFoundException("Tabloda böyle bir anahtar mevcut değil", $table . "." . $field);
  }
}

?>

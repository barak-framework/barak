<?php

class ApplicationModel {

  private $_select = [];  // list
  private $_table  = "";  // string
  private $_where  = [];  // hash
  private $_join   = [];  // hash
  private $_order  = [];  // list
  private $_group  = [];  // list
  private $_limit;
  private $_offset;

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

  private function __construct($fields = null) {
    $this->_new_record_state = true;

    foreach (ApplicationSql::fieldnames(self::tablename()) as $fieldname) {
      if ($fields) {

        // simple load for create
        $this->_fields[$fieldname] = in_array($fieldname, array_keys($fields)) ? $fields[$fieldname] : null;

      } else {

        // create draft fieldnames
        $this->_fields[$fieldname] = null;

      }
    }
  }

  // Alma 1:

  // $user = User::find(1);
  // echo $user->_first_name;

  // Alma 2:

  // $comment = Comment::find(1); // ["id", "name", "content", "user_id"]
  // echo $comment->user->first_name;

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

  // ok
  public function __set($field, $value) {
    if (array_key_exists($field, $this->_fields))
      $this->_fields[$field] = $value;
    else
      throw new FieldNotFoundException("Tabloda yüklenecek böyle bir anahtar mevcut değil", $field);
  }

  //////////////////////////////////////////////////
  // Public Functions
  //////////////////////////////////////////////////

  // FETCH Functions Start

  // ok
  public function take() {

    $records = self::query();

    if ($records) {
      $tablename = $this->_table;

      foreach ($records as $record) {
        $object = $tablename::find((int)$record["id"]);
        $object->_fields = $record;
        $objects[] = $object;
      }
      return $objects;
    }
    return null;
  }

  // ok
  public function pluck($field) {

    self::check_fieldname($field);

    $this->_select = [$field];
    $records = self::query();

    if ($records) {
      foreach ($records as $record)
        $values[] = $record[$field];

      return $values;
    }
    return null;
  }

  // ok
  public function count() {
    $this->_select = ["count(*)"];

    $record = ApplicationSql::read($this->_table, $this->_select, $this->_where);

    return $record["count(*)"] ?: null;
  }

  // FETCH Functions End

  // $user = new User();
  // $user->first_name ="Gökhan";
  // $user->save(); // kayıt ettikten sonra otomatik id değeri alır.

  // print_r($user);
  // [_fields:ApplicationModel:private] => Array ( [first_name] => Gökhan [last_name] => [username] => [password] => [content] => [id] => 368 )

  // ok
  public function save() {

    if (!$this->_new_record_state) {

      ApplicationSql::update($this->_table, $this->_fields, self::field_to_where(["id" => intval($this->_fields["id"])]));

    } else {

      // kayıt tamamlandıktan sonra en son id döndür
      $primary_key = ApplicationSql::create($this->_table, $this->_fields);

      // artık yeni kayıt değil
      $this->_new_record_state = false;

      // id'si olan kaydın alan değerlerini yeni kayıtta güncelle, (ör.: id otomatik olarak alması için)
      $tablename = $this->_table;
      $this->_fields = $tablename::find($primary_key)->_fields;
    }
  }

  // ok
  public function destroy() {
    ApplicationSql::delete($this->_table, self::field_to_where(["id" => intval($this->_fields["id"])]), null);
  }

  // $users = User::load()->select("first_name, last_name")->get();
  // ["User.firs_name, User.last_name"]

  // ok
  public function select($fields) {

    $fields = self::check_fields_of_table_list(array_map('trim', explode(',', $fields)));

    // varsayılan olarak ekle, objeler yüklenirken her zaman id olmalıdır.
    $table_and_primary_key = $this->_table . ".id";
    if (!in_array($table_and_primary_key, $fields))
      array_push($fields, $table_and_primary_key);

    $this->_select = $fields; // ["User.first_name", "User.last_name", "Comment.name"]
    return $this;
  }

  // // ok
  // public function where($fields = null) {
  //   $fields = self::check_fields_of_table_hash($fields);

  //   $this->_where = ($this->_where) ? array_merge($this->_where, $fields) : $fields;
  //   return $this;
  // }

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

  // ok
  public function where($field, $value = null, $mark = "=", $logic = "AND") {
    self::check_fieldname($field);

    // mark control
    $mark = strtoupper(trim($mark));
    if (is_null($value)) {
      $mark = "IS NULL";
      $value = NULL;
    } elseif (in_array($value, ApplicationSql::$where_null_marks)) {
      $mark = $value;
      $value = NULL;
    } elseif (in_array($mark, ApplicationSql::$where_in_marks)) {
      if (!is_array($value))
        throw new BelongNotFoundException(sprintf("WHERE %s için değer list olmalıdır", implode(',', ApplicationSql::$where_in_marks)), $value);
    } elseif (in_array($mark, ApplicationSql::$where_between_marks)) {
      if (!is_array($value) or (is_array($value) and count($value) != 2))
        throw new BelongNotFoundException(sprintf("WHERE %s için değer list ve 2 değerli olmalıdır", implode(',', ApplicationSql::$where_in_marks)), $value);
    } elseif (!in_array($mark, array_merge(ApplicationSql::$where_other_marks, ApplicationSql::$where_like_marks))) {
      throw new BelongNotFoundException("WHERE için tanımlı böyle bir işaretçi bulunamadı", $mark);
    }

    // logic control
    $logic = strtoupper(trim($logic));
    if (!in_array($logic, ApplicationSql::$where_logics))
      throw new BelongNotFoundException("WHERE de tanımlı böyle bir bağlayıcı bulunamadı", $logic);

    $this->_where[] = [
    "field" => $field,
    "mark"  => $mark,
    "value" => $value,
    "logic" => $logic
    ];

    return $this;
  }

  // ok
  public function or_where($field, $value = null, $mark = "=") {
    return $this->where($field, $value, $mark, "OR");
  }

  // $categories = Category::load()->joins("article")->take();
  // $categories = Category::load()->joins(["article" => "comment"])->take();
  // $categories = Category::load()->joins(["article" => ["comment" => ["tag"]]])->take();
  // $categories = Category::load()->joins(["article" => ["comment" => ["tag"], "like"]])->take();
  // $categories = Category::load()->joins(["article" => ["comment" => ["tag"], "like"], "document"])->take();
  // $categories = Category::load()->joins(["article", "document"])->take();

  // ok
  public function joins($belong_tables, $table = null) {

    // like for single variable : Category::load()->joins("article")->take();
    if (!is_array($belong_tables)) $belong_tables = [$belong_tables];

    ($table) ? self::check_tablename($table) : ($table = $this->_table);

    foreach ($belong_tables as $key => $value) {

      // find belong table
      list($belong_table, $belong_tables) = (is_array($value)) ? [$key, $value] : [$value, null];

      self::check_tablename($belong_table);
      $belong_table_fieldnames = ApplicationSql::fieldnames($belong_table);
      $foreign_key = strtolower($table) . "_id";

      if (!in_array($foreign_key, $belong_table_fieldnames))
        throw new BelongNotFoundException("Sorgulama işleminde böyle bir tablo mevcut değil", $foreign_key);

      // join işlemi için user.id = comment.user_id gibi where'ye eklemeler yap
      $this->_join[$belong_table] = $belong_table . "." . $foreign_key . "=" . str_replace("_", ".", $foreign_key);

      // join işleminde select çakışması önlenmesi için User.first_name, User.last_name gibi ekleme yap
      foreach ($belong_table_fieldnames as $fieldname)
        $this->_select[] = $belong_table . "." . $fieldname;

      // have a more belong tables ?
      if ($belong_tables)
        $this->joins($belong_tables, $belong_table);

    }

    // tablonun kendi select için eklemeler yap
    foreach (ApplicationSql::fieldnames($this->_table) as $fieldname)
      $this->_select[] = $this->_table . "." . $fieldname;

    return $this;
  }

  // ok
  public function order($field, $sort_type = "ASC") {
    self::check_fieldname($field);

    // sort_type control
    $sort_type = strtoupper(trim($sort_type));
    if (!in_array($sort_type, ApplicationSql::$order_sort_type))
      throw new FieldNotFoundException("Order sorgusunda bilinmeyen parametre", $sort_type);

    $this->_order[] = "$field $sort_type";
    return $this;
  }

  // ok
  public function group($field) {
    self::check_fieldname($field);

    $this->_group[] = $field;
    return $this;
  }

  // ok
  public function limit($limit = null) {
    $this->_limit = intval($limit);
    return $this;
  }

  // ok
  public function offset($offset = null) {
    $this->_offset = intval($offset);
    return $this;
  }

  // ok
  public function delete_all() {
    ApplicationSql::delete($this->_table, $this->_where, null);
  }

  //////////////////////////////////////////////////
  // Public Static Functions
  //////////////////////////////////////////////////

  // ok
  public static function tablename() {
    $tablename = strtolower(get_called_class());
    self::check_tablename($tablename);

    return $tablename;
  }

  // $user = User::unique(["username" => "gdemir", "password" => "123456"]);
  // echo $user->first_name;

  // ok
  public static function unique($fields = null) {
    $tablename = self::tablename();

    if ($record = ApplicationSql::read($tablename, null, self::field_to_where($fields))) {

      $object = $tablename::load();
      foreach ($record as $field => $value)
        $object->$field = $value;

      $object->_new_record_state = false;
      return $object;
    }
    return null;
  }

  // echo User::primary_keyname();

  // ok
  public static function primary_keyname() {
    return ApplicationSql::primary_keyname(self::tablename());
  }

  // Ör. 1:

  // $user = User::draft();
  // $user->first_name = "Gökhan";
  // $user->save();
  // print_r($user); // otomatik id alır

  // Ör. 2:

  // $user = User::draft(["first_name" => "Gökhan"])->save();

  // ok
  public static function draft($fields = null) {
    $model_name = self::tablename();
    $object = new $model_name($fields);
    $object->_table = $model_name;
    return $object;
  }

  // ok
  public static function load() {
    return self::draft(null);
  }

  // User::create(["first_name" => "Gökhan"]);

  // ok
  public static function create($fields) {
    $tablename = self::tablename();
    $tablename::draft($fields)->save();
  }

  // User::first();

  // ok
  public static function first($limit = 1) {
    $tablename = self::tablename();
    $records = $tablename::load()->order("id", "asc")->limit($limit)->take();
    return ($limit == 1) ? $records[0] : $records;
  }

  // User::last();

  // ok
  public static function last($limit = 1) {
    $tablename = self::tablename();
    $records = $tablename::load()->order("id", "desc")->limit($limit)->take();
    return ($limit == 1) ? $records[0] : $records;
  }

  // $user = User::find(1); // return User objects
  // $user->first_name = 'foo';
  // $user->save();

  // ok
  public static function find($primary_key) {
    $tablename = self::tablename();
    return $tablename::unique(["id" => intval($primary_key)]); // convert to where struct and pass
  }

  // $users = User::find_all([1, 2, 3]); // return User objects array
  //
  // foreach ($users as $user) {
  //   $user->first_name = "Gökhan";
  //   $user->save();
  // }

  // ok
  public static function find_all($primary_keys) {
    $tablename = self::tablename();
    foreach ($primary_keys as $primary_key)
      $objects[] = $tablename::find($primary_key);

    return isset($objects) ? $objects : null;
  }

  // $users = User::all(); // return User objects array
  //
  // foreach ($users as $user) {
  //   $user->first_name = "Gökhan";
  //   $user->save();
  // }

  // ok
  public static function all() {
    $tablename = self::tablename();
    return $tablename::load()->take();
  }

  // ok
  public static function exists($primary_key) {
    $tablename = self::tablename();
    return $tablename::find($primary_key) ? true : false;
  }

  // ok
  public static function update($primary_key, $fields) {
    self::check_fieldnames(array_keys($fields));

    ApplicationSql::update(self::tablename(), $fields, self::field_to_where(["id" => intval($primary_key)]));
  }

  // ok
  public static function delete($primary_key) {
    ApplicationSql::delete(self::tablename(), self::field_to_where(["id" => intval($primary_key)]), null);
  }

  // public function field_exists($field) {
  //   return in_array($field, ApplicationSql::fieldnames(self::tablename())) ? true : false;
  // }

  //////////////////////////////////////////////////
  // Private Functions
  //////////////////////////////////////////////////

  // ok
  private function query() {
    return ApplicationSql::query($this->_select, $this->_table, $this->_join, $this->_where, $this->_order, $this->_group, $this->_limit, $this->_offset);
  }

  // ok
  private static function field_to_where($fields = null, $mark = "=", $logic = "AND") {
    if ($fields) {
      $_where = [];
      foreach ($fields as $field => $value)
        $_where[] = ["field" => $field, "mark"  => $mark, "value" => $value, "logic" => $logic];
    } else {
      $_where = null;
    }
    return $_where;
  }

  // name check_join_table_and_field
  // select, where, group, order by

  // ok
  private static function check_fields_of_table_list($fields) {
    $tablename = self::tablename();
    foreach ($fields as $index => $field) {
      if (strpos($field, '.') !== false) { // found TABLE
        list($request_table, $request_field) = array_map('trim', explode('.', $field));

        if ($request_table != $this->_table and ($this->_join and !array_key_exists($request_table, $this->_join)))
          throw new TableNotFoundException("Sorgulama işleminde böyle bir tablo mevcut değil", $request_table);

        self::check_fieldname($request_field, $request_table);

      } else {
        $fields[$index] = $tablename . '.' .  $field;
      }
    }
    return $fields;
  }

  // ok
  private static function check_tablename($tablename) {
    if (!in_array($tablename, ApplicationSql::tablenames()))
      throw new TableNotFoundException("Veritabanında böyle bir tablo mevcut değil", $tablename);
  }

  // ok
  private static function check_fieldname($field, $table = null) {
    $table = ($table) ? $table : self::tablename();
    if (!in_array($field, ApplicationSql::fieldnames($table)))
      throw new FieldNotFoundException("Tabloda böyle bir anahtar mevcut değil", $table . "." . $field);
  }

  // ok
  private static function check_fieldnames($fields, $table = null) {
    $table = ($table) ? $table : self::tablename();
    $table_fields = ApplicationSql::fieldnames($table);
    foreach ($fields as $field) {
      if (!in_array($field, $table_fields))
        throw new FieldNotFoundException("Tabloda böyle bir anahtar mevcut değil", $table . "." . $field);
    }
  }
}

?>

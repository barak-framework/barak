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

    foreach (ApplicationSql::fieldnames(self::table_name()) as $fieldname) {
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

          // return $owner_table::load()->where([$owner_key => $this->_fields["id"]])->take();
          return $owner_table::load()->where($owner_key, $this->_fields["id"])->take();
        }
      }
    }

    throw new FieldNotFoundException("Tabloda getirilecek böyle bir anahtar mevcut değil", $field);
  }

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
      $table_name = $this->_table;

      foreach ($records as $record) {
        $object = $table_name::find((int)$record["id"]);
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

      ApplicationSql::update($this->_table, $this->_fields, static::field_to_where(["id" => intval($this->_fields["id"])]));

    } else {

      // kayıt tamamlandıktan sonra en son id döndür
      $primary_key = ApplicationSql::create($this->_table, $this->_fields);

      // artık yeni kayıt değil
      $this->_new_record_state = false;

      // id'si olan kaydın alan değerlerini yeni kayıtta güncelle, (ör.: id otomatik olarak alması için)
      $table_name = $this->_table;
      $this->_fields = $table_name::find($primary_key)->_fields;
    }
  }

  // ok
  public function destroy() {
    ApplicationSql::delete($this->_table, static::field_to_where(["id" => intval($this->_fields["id"])]), null);
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
  
  // ok
  public function where($field, $value = null, $mark = "=", $logic = "AND") {

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

    // if (is_null($value))
    //   $mark = $value;

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
        throw new BelongNotFoundException("WHERE IN, NOT IN için değer list olmalıdır", $value);
    } elseif (in_array($mark, ApplicationSql::$where_between_marks)) {
      if (!is_array($value)) {
        throw new BelongNotFoundException("WHERE BETWEEN, NOT BETWEEN için değer list olmalıdır", $value);
      } elseif (is_array($value)) {
        if (count($value) != 2)
          throw new BelongNotFoundException("WHERE BETWEEN, NOT BETWEEN için değer list ve 2 değerli olmalıdır", $value);
      }
    } elseif (!in_array($mark, array_merge(ApplicationSql::$where_other_marks, ApplicationSql::$where_like_marks))) {
      throw new BelongNotFoundException("WHERE de tanımlı böyle bir işaretçi bulunamadı", $mark);
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

  public function or_where($field, $value = null, $mark = "=") {
    return $this->where($field, $value, $mark, "OR");
  }

  // ok
  public function joins($belong_tables) {

    $table = $this->_table;
    foreach ($belong_tables as $index => $belong_table) {

      $belong_table_fieldnames = ApplicationSql::fieldnames($belong_table);
      $foreign_key = strtolower($table) . "_id";

      if (!in_array($foreign_key, $belong_table_fieldnames))
        throw new BelongNotFoundException("Sorgulama işleminde böyle bir tablo mevcut değil", $foreign_key);

      // join işlemi için User.id = Comment.user_id gibi where'ye eklemeler yap
      $this->_join[$belong_table] = $belong_table . "." . $foreign_key . "=" . ucfirst(str_replace("_", ".", $foreign_key));

      // // join işleminde select çakışması önlenmesi için User.first_name, User.last_name gibi ekleme yap
      foreach ($belong_table_fieldnames as $fieldname)
        $this->_select[] = $belong_table . "." . $fieldname;

      $table = $belong_table;
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

  public static function table_name() {
    $table_name = strtolower(get_called_class());

    if (!in_array($table_name, ApplicationSql::tablenames()))
      throw new TableNotFoundException("Veritabanında böyle bir tablo mevcut değil", $table_name);

    return $table_name;
  }

  // $user = User::unique(["username" => "gdemir", "password" => "123456"]);
  // echo $user->first_name;
  // ok

  public static function unique($fields = null) {
    $table_name = self::table_name();

    if ($record = ApplicationSql::read($table_name, null, static::field_to_where($fields))) {

      $object = $table_name::load();
      foreach ($record as $fieldname => $value)
        $object->$fieldname = $value;

      $object->_new_record_state = false;
      return $object;
    }
    return null;
  }

  // echo User::primary_keyname();

  // ok
  public static function primary_keyname() {
    return ApplicationSql::primary_keyname(self::table_name());
  }

  // Ör. 1:

  // $user = User::draft();
  // $user->first_name = "Gökhan";
  // $user->save();
  // print_r($user); // otomatik id alır

  // Ör. 2:

  // $user = User::draft(["first_name" => "Gökhan"])->save();

  public static function draft($fields = null) {
    $model_name = self::table_name();
    $object = new $model_name($fields);
    $object->_table = $model_name;
    return $object;
  }

  public static function load() {
    return self::draft(null);
  }

  // User::create(["first_name" => "Gökhan"]);

  // ok
  public static function create($fields) {
    $table_name = self::table_name();
    $table_name::draft($fields)->save();
  }

  // User::first();

  // ok
  public static function first($limit = 1) {
    $table_name = self::table_name();
    $records = $table_name::load()->order("id", "asc")->limit($limit)->take();
    return ($limit == 1) ? $records[0] : $records;
  }

  // User::last();

  // ok
  public static function last($limit = 1) {
    $table_name = self::table_name();
    $records = $table_name::load()->order("id", "desc")->limit($limit)->take();
    return ($limit == 1) ? $records[0] : $records;
  }

  // $user = User::find(1); // return User objects
  // $user->first_name = 'foo';
  // $user->save();

  // ok
  public static function find($primary_key) {
    $table_name = self::table_name();
    return $table_name::unique(["id" => intval($primary_key)]); // convert to where struct and pass
  }

  // $users = User::find_all([1, 2, 3]); // return User objects array
  //
  // foreach ($users as $user) {
  //   $user->first_name = "Gökhan";
  //   $user->save();
  // }

  public static function find_all($primary_keys) {
    $table_name = self::table_name();
    foreach ($primary_keys as $primary_key)
      $objects[] = $table_name::find($primary_key);

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
    $table_name = self::table_name();
    return $table_name::load()->take();
  }

  // ok
  public static function exists($primary_key) {
    $table_name = self::table_name();
    return $table_name::find($primary_key) ? true : false;
  }

  // ok
  public static function update($primary_key, $fields) {
    self::check_fieldnames(array_keys($fields));

    ApplicationSql::update(self::table_name(), $fields, static::field_to_where(["id" => intval($primary_key)]));
  }

  // ok
  public static function delete($primary_key) {
    ApplicationSql::delete(self::table_name(), static::field_to_where(["id" => intval($primary_key)]), null);
  }
  
  // ?
  // public function field_exists($field) {
  //   return in_array($field, ApplicationSql::fieldnames(self::table_name())) ? true : false;
  // }

  //////////////////////////////////////////////////
  // Private Functions
  //////////////////////////////////////////////////

  private function query() {
    return ApplicationSql::query($this->_select, $this->_table, $this->_join, $this->_where, $this->_order, $this->_group, $this->_limit, $this->_offset);
  }

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

  public static function check_fields_of_table_list($fields) {
    $table_name = self::table_name();
    foreach ($fields as $index => $field) {
      if (strpos($field, '.') !== false) { // found TABLE
        list($request_table, $request_field) = array_map('trim', explode('.', $field));

        if ($request_table != $this->_table and ($this->_join and !array_key_exists($request_table, $this->_join)))
          throw new TableNotFoundException("Sorgulama işleminde böyle bir tablo mevcut değil", $request_table);

        self::check_fieldname($request_field, $request_table);

      } else {
        $fields[$index] = $table_name . '.' .  $field;
      }
    }
    return $fields;
  }

  public static function check_fields_of_table_hash($fields) {
    $table_name = self::table_name();
    foreach ($fields as $field => $value) {
      if (strpos($field, '.') !== false) { // found TABLE
        list($request_table, $request_field) = array_map('trim', explode('.', $field));

        if ($request_table != $this->_table and ($this->_join and !array_key_exists($request_table, $this->_join)))
          throw new TableNotFoundException("WHERE işleminde böyle bir tablo mevcut değil", $request_table);

        self::check_fieldname($request_field, $request_table);

      } else {
        $fields[$table_name . '.' .  $field] = $value;
        unset($fields[$field]);
      }
    }
    return $fields;
  }

  public static function check_fieldname($field, $table = null) {
    $table = ($table) ? $table : self::table_name();
    if (!in_array($field, ApplicationSql::fieldnames($table)))
      throw new FieldNotFoundException("Tabloda böyle bir anahtar mevcut değil", $table . "." . $field);
  }

  public static function check_fieldnames($fields, $table = null) {
    $table = ($table) ? $table : self::table_name();
    $table_fields = ApplicationSql::fieldnames($table);
    foreach ($fields as $field) {
      if (!in_array($field, $table_fields))
        throw new FieldNotFoundException("Tabloda böyle bir anahtar mevcut değil", $table . "." . $field);
    }
  }
}

?>

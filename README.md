# Barak

[![Latest Stable Version](https://poser.pugx.org/gdemir/barak/v/stable)](https://packagist.org/packages/gdemir/barak)
[![Total Downloads](https://poser.pugx.org/gdemir/barak/downloads)](https://packagist.org/packages/gdemir/barak)
[![Latest Unstable Version](https://poser.pugx.org/gdemir/barak/v/unstable)](https://packagist.org/packages/gdemir/barak)
[![License](https://poser.pugx.org/gdemir/barak/license)](https://packagist.org/packages/gdemir/barak)

##  Requirements Packages and Versions

- MySQL

- Web server: [apache2-settings](https://github.com/gdemir/barak/blob/master/.htaccess) or [nginx-settings](https://github.com/gdemir/barak/blob/master/nginx-settings) or [iss-settings](https://github.com/gdemir/barak/blob/master/web.config)

- Php Version : 7.0, - Php Database Access : [PDO](http://php.net/manual/tr/book.pdo.php)

- Install : [LAMP](http://gdemir.me/categories/linux/lamp/) or [LEMP](http://gdemir.me/categories/linux/lemp/)

- [Composer](http://gdemir.me/categories/php/composer/)


## Install

- Composer

[Composer](http://gdemir.me/categories/php/composer/)

- Barak

```sh
composer create-project gdemir/barak project_name
```

## Configuration Database File (`config/database.ini`)

```ini
[database_configuration]
host  = localhost
user  = root
pass  = barak
name  = BARAK
```

## Run

    cd project_name
    php -S localhost:9090

and check homepage : [http://localhost:9090](http://localhost:9090) and thats all!

## Releases

- [https://github.com/gdemir/barak/releases](https://github.com/gdemir/barak/releases)

---

## Guides

### Simple Usage
---

> request url : `/`


> `config/routes.php`

```php
ApplicationRoutes::draw(
  get("/", "home#index")
);
```

> `app/controller/HomeController.php`

```php
class HomeController extends ApplicationController {

  public function index() {
    $this->message = "Hello World";
  }

}
```

> `app/views/home/index.php`

```php
<h1> Home#Index </h1>;
<?= $message; ?>
```

> `app/views/layouts/home.php`

```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="tr" lang="tr">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title></title>
</head>
<body>
  <?= $yield; ?>
</body>
</html>
```

### Router (`config/routes.php`)
---

#### GET

- Simple

> `config/routes.php`

```php
ApplicationRoutes::draw(
  get("/home/index")
);
```

- Dynamical Segment

Dinamik route tanımlamalarında "home#index" gibi hedef belirtilmek zorundadır:

> `config/routes.php`

```php
ApplicationRoutes::draw(
  get("/home/index/:id", "home#index")
);
```

Dinamik route tanımlamalarında ki "id" gibi parçalara erişim:

> `app/controllers/HomeController.php`

```php
class HomeController extends ApplicationController {

  public function index() {
    echo $this->id;
  }

}
```

> `app/views/home/index.php`

```php
<h1> Home#Index </h1>;
<?= "id" . $id; ?>
```

#### POST

- Simple

> `config/routes.php`

```php
ApplicationRoutes::draw(
  post("/admin/login")
);
```

> `app/controllers/AdminController.php`

```php
class AdminController extends ApplicationController {

  protected $before_actions = [
  ["require_login", "except" => ["login", "logout"]]
  ];

  public function login() {

    if (isset($_SESSION["admin"]))
      return $this->redirect_to("/admin/index");

    if (isset($_POST["username"]) and isset($_POST["password"])) {

      if ($user = User::unique(["username" => $_POST["username"], "password" => md5($_POST["password"])])) {

        $_SESSION["success"] = "Admin sayfasına hoş geldiniz";
        $_SESSION["full_name"] = "$user->first_name $user->last_name";
        $_SESSION["admin"] = $user->id;
        return $this->render("/admin/index");

      } else {

        $_SESSION["danger"] = "Oops! İsminiz veya şifreniz hatalı, belki de bunlardan sadece biri hatalıdır?";

      }
    }

    return $this->render(["layout" => "default"]);
  }

  // public function index() {} // OPTIONAL

  public function logout() {
    if (isset($_SESSION["admin"]))
      session_destroy();
    return $this->redirect_to("/admin/login");
  }

  public function require_login() {
    if (!isset($_SESSION["admin"])) {
      $_SESSION["warning"] = "Lütfen hesabınıza giriş yapın!";
      return $this->redirect_to("/admin/login");
    }
  }
}
```

> `app/views/admin/index.php`

```php
<h1> Admin#Index </h1>;
<?= $_SESSION["full_name"]; ?>
```

#### RESOURCE

> `config/routes.php`

```php
ApplicationRoutes::draw(
  resource("/users")
);
```

> *Aşağıdaki routes kümesini üretir:*

```php
ApplicationRoutes::draw(
  get("/users/", "users#index"), // all record
  get("/users/create"),          // new record form
  post("users/save"),            // new record save
  get("/users/show"),            // display record
  get("/users/edit"),            // edit record
  post("/users/update"),         // update record
  post("/users/destroy")         // destroy record
);
```

#### RESOURCES

> `config/routes.php`

```php
ApplicationRoutes::draw(
  resources("/users")
);
```

> *Aşağıdaki routes kümesini üretir:*

```php
ApplicationRoutes::draw(
  get("/users", "users#index"),         // all record
  get("/users/create"),                 // new record form
  post("/users/save"),                  // new record save
  get("/users/show/:id", "users#show"), // display record
  get("/users/edit/:id", "users#edit"), // edit record
  post("/users/update"),                // update record
  post("/users/destroy")                // destroy record
);
```

#### SCOPE

Kodları daha derli toplu kullanmak için Route'in Gruplama özelliğidir. Bir PATH altında CONTROLLER ve VIEW dizininin çalışma imkanı sağlar. 

> controller: `app/controllers/PATH/CONTROLLER.php`

> view : `app/views/VIEW/PATH/CONTROLLER/ACTION.php`

- Simple

> `config/routes.php`

> view : `app/views/admin/categories/ACTION.php`
> controller : `app/controllers/admin/CategoriesController.php`

```php
ApplicationRoutes::draw(
 scope("/admin",
    resources("/categories")
 )
);
```
> *Aşağıdaki routes kümesini üretir:*

```php
ApplicationRoutes::draw(
  get("/admin/categories",          "categories#index", "/admin"),  // all record
  get("/admin/categories/create",   false,              "/admin"),  // new record form
  post("/admin/categories/save",    false,              "/admin"),  // new record save
  get("/admin/categories/show/:id", "categories#show",  "/admin"),  // display record
  get("/admin/categories/edit/:id", "categories#edit",  "/admin"),  // edit record
  post("/admin/categories/update",  false,              "/admin"),  // update record
  post("/admin/categories/destroy", false,              "/admin"),  // destroy record
);
```

- Mix

> `config/routes.php`

```php
ApplicationRoutes::draw(
  get("/admin/login"),
  scope("/admin",
    [
    get("/users", "users#index"),
    get("/users/show/:id")
    ],
    resources("/categories"),
    resource("/products")
  );

);
```

> *Aşağıdaki routes kümesini üretir:*

```php
ApplicationRoutes::draw(
  get("/admin/login"),

  get("/admin/users",               "users#index",      "/admin"),  // all record
  get("/admin/users/show/:id",      false,              "/admin"),  // display record

  get("/admin/categories",          "categories#index", "/admin"),  // all record
  get("/admin/categories/create",   false,              "/admin"),  // new record form
  post("/admin/categories/save",    false,              "/admin"),  // new record save
  get("/admin/categories/show/:id", "categories#show",  "/admin"),  // display record
  get("/admin/categories/edit/:id", "categories#edit",  "/admin"),  // edit record
  post("/admin/categories/update",  false,              "/admin"),  // update record
  post("/admin/categories/destroy", false,              "/admin"),  // destroy record

  get("/admin/products",           "products#index",    "/admin"),  // all record
  get("/admin/products/create",     false,              "/admin"),  // new record form
  post("/admin/products/save",      false,              "/admin"),  // new record save
  get("/admin/products/show",       false,              "/admin"),  // display record
  get("/admin/products/edit",       false,              "/admin"),  // edit record
  post("/admin/products/update",    false,              "/admin"),  // update record
  post("/admin/products/destroy",   false,              "/admin")   // destroy record
);
```

#### ROOT

> `config/routes.php`

```php
ApplicationRoutes::draw(
  root("home#index")
);
```

> *Aşağıdaki routes kümesini üretir:*

```php
ApplicationRoutes::draw(
  get("/", "home#index"),
);
```

### Controller (`app/controller/*.php`)
---

Her `config/routes.php` içerisinde tanımlanan `get` işlemi için `app/controller/*.php` dosyası içerisinde fonksiyon tanımlamak zorunlu değildir, tanımlanırsa bir değişken yükü/yükleri controller içinde `$this->KEY` şeklinde tanımlanırsa ilgili yönlenen sayfada `$KEY` şeklinde veriye erişebilir. Her `config/routes.php` içerisinde tanımlanan `post` için ilgili `app/controller/*.php` dosyası içerisinde fonksiyon tanımlamak zorunludur.

- Render

> layout : `app/views/layouts/VIEW.php`

> view : `app/views/VIEW/ACTION.php`

Example

```php
class HomeController extends ApplicationController {

  public function index() {
    echo "HomeIndex sayfası öncesi çalışan fonksiyon";

    // DEFAULT LAYOUT: home, VIEW: home, ACTION: index
    $this->render("/home/index"); // like $this->render(["template" => "/home/index"]);

    // DEFAULT LAYOUT: home, VIEW: home, ACTION: show
    $this->render("/home/show"); // like $this->render(["template" => "/home/show"]);

    // DEFAULT LAYOUT: home, VIEW: admin, ACTION: show
    $this->render("/admin/show"); // like $this->render(["template" => "/admin/show"]);

    // Default LAYOUT: home, VIEW: home, ACTION: index
    $this->render(["layout" => "home", "view" => "home", "action" => "index"]); // default render

    // LAYOUT: false, VIEW: home, ACTION: index
    $this->render(["layout" => false]);

    // LAYOUT: home, VIEW: admin, ACTION: index
    $this->render(["view" => "admin", "action" => "index"]);

    // LAYOUT: home, VIEW: admin, ACTION: index
    $this->render(["view" => "admin", "action" => "index"]);

    // LAYOUT: admin, VIEW: home, ACTION: show
    $this->render(["layout" => "admin", "view" => "home", "action" => "show"]);

    // LAYOUT: admin, VIEW: home, ACTION: index
    $this->render(["layout" => "admin", "template" => "home/index"]);

    // LAYOUT: admin, VIEW: home, ACTION: show
    $this->render(["layout" => "admin", "template" => "home/show"]);

    // LAYOUT: false, VIEW: false, ACTION: false
    // only load controller params and get this file
    $this->render(["file" => "/app/views/admin/login.php"]);

    // TODO partial, ayrıca sayfa üzerinde
    // $this->render(["partial" => "home/navbar"]);
  }

}
```

- Redirect

> request url [`/` or `/home`] redirect to `/home/index`

> `config/routes.php`

```php
ApplicationRoutes::draw(
  get("/", "home#home"), // or root("home#home"),
  get("/home", "home#home"),
  get("/home/index"),
);
```

> `app/controllers/HomeController.php`

```php
class HomeController extends ApplicationController {
  public function home() {
    return $this->redirect_to("/home/index");
  }
  public function index() {}
}
```

> `app/views/home/index.php`

```php
<h1>Home#Index</h1>
```

- Before Action

Before Action (`protected $before_actions`) özelliği, `app/controller/*.php` dosyası içerisinde her çalışacak get/post fonksiyonları için önceden çalışacak fonksiyonları belirtmeye yarayan özelliktir. Özelliğin etkisini ayarlamak için aşağıdaki 3 şekilde kullanılabilir:

1. `except` anahtarı ile nerelerde çalışmayacağını

2. `only` anahtarı ile nerelerde çalışacağını

3. Anahtar yok ise her yerde çalışacağını

```php
class HomeController extends ApplicationController {

  protected $before_actions = [
    ["login", "except" => ["login", "index"]],
    ["notice_clear", "only" => ["index"]],
    ["every_time"]
  ];

  public function index() {
    echo "HomeIndex : Anasayfa (bu işlem için login fonksiyonu çalışmaz, notice_clear ve every_time çalışır)";
  }

  public function login() {
    echo "Home#Login : Her işlem öncesi login oluyoruz. (get/post için /home/login, /home/index hariç)";
  }

  public function notice_clear() {
    echo "Home#NoticeClear : Duyular silindi. (get/post için sadece /home/index'de çalışır)";
  }

  public function every_time() {
    echo "Home#EveryTime : Her zaman get/post öncesi çalışırım.";
  }
```

- After Action

After Action (`protected $after_actions`) özelliği, `app/controller/*.php` dosyası içerisinde her çalışacak get/post fonksiyonları için sonradan çalışacak fonksiyonları belirtmeye yarayan özelliktir. Özelliğin etkisini ayarlamak için aşağıdaki 3 şekilde kullanılabilir:

1. `except` anahtarı ile nerelerde çalışmayacağını

2. `only` anahtarı ile nerelerde çalışacağını

3. Anahtar yok ise her yerde çalışacağını

`#TODO`

- Simple

> `config/routes.php`

```php
ApplicationRoutes::draw(
  get("/admin/home"),
  get("/admin/login"),
  post("/admin/login")
);
```

> `app/controller/AdminController.php`

```php
class AdminController extends ApplicationController {

  protected $before_actions = [
  ["require_login", "except" => ["login", "logout"]]
  ];

  public function login() {

    if (isset($_SESSION["admin"]))
      return $this->redirect_to("/admin/index");

    if (isset($_POST["username"]) and isset($_POST["password"])) {

      if ($user = User::unique(["username" => $_POST["username"], "password" => md5($_POST["password"])])) {

        $_SESSION["success"] = "Admin sayfasına hoş geldiniz";
        $_SESSION["full_name"] = "$user->first_name $user->last_name";
        $_SESSION["admin"] = $user->id;
        return $this->render("/admin/index");

      } else {

        $_SESSION["danger"] = "Oops! İsminiz veya şifreniz hatalı, belki de bunlardan sadece biri hatalıdır?";

      }
    }

    return $this->render(["layout" => "default"]);
  }

  // public function index() {} // OPTIONAL

  public function logout() {
    if (isset($_SESSION["admin"]))
      session_destroy();
    return $this->redirect_to("/admin/login");
  }

  public function require_login() {
    if (!isset($_SESSION["admin"])) {
      $_SESSION["warning"] = "Lütfen hesabınıza giriş yapın!";
      return $this->redirect_to("/admin/login");
    }
  }
}
```

> `app/views/admin/login.php`

```php
<div class="row">
  <div class="col-xs-3">
    <img src="/app/assets/img/default.png" class="img-thumbnail" />
  </div>
  <div class="col-xs-9">
    <form class="login-form" action="/admin/login" accept-charset="UTF-8" method="post">
      <input type="text" placeholder="Kullanıcı Adı" class="form-control" size="50" name="username" id="username" />
      <input type="password" placeholder="Parola" class="form-control" size="50" name="password" id="password" />
      <button type="submit" class="btn btn-primary" style="width:100%">SİSTEME GİRİŞ</button>
    </form>
  </div>
</div>
```

> `app/views/admin/home.php`

```php
<h1> Admin#Home </h1>
```

> `app/views/layouts/admin.php`

```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="tr" lang="tr">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title></title>
</head>
<body>
  <?= $yield; ?>
</body>
</html>
```

### Views (`app/views/DIRECTORY/*.php`)
---

Her `get` işlemi için `config/routes.php` de yönlendirilen `controller` ve `action` adlarını alarak, `app/views/CONTROLLER/ACTION.php` html sayfası `app/views/layouts/CONTROLLER.php` içerisine `<?= $yield; ?>` değişken kısmına gömülür ve görüntülenir.

> `app/views/DIRECTORY/*.php`

```html
<h1> Hello World </h1>
```

> `app/views/layouts/DIRECTORY.php`

```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="tr" lang="tr">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title></title>
</head>
<body>
  <?= $yield; ?>
</body>
</html>
```

### Model (`app/models/TABLE.php`)
---

> `app/models/TABLE.php`
`example: app/models/User.php`

```php
class User extends ApplicationModel {

  public function full_name() {
    return $this->first_name . " " . $this->last_name;
  }

}
```

```php
$user = User::find(1);
echo $user->full_name();
```

- Public Access Functions

> `save`, `destroy`, `delete_all`, `select`, `where`, `or_where`, `joins`, `order`, `group`, `limit`, `take`, `pluck`, `count`

- Static Access Functions

> `draft`, `load`, `create`, `unique`, `find`, `find_all`, `all`, `first`, `last`, `exists`, `delete`, `update`

#### CREATE ( `new`, `create` )


> `new`

```php
// Ör. 1:

$user = User::draft();
$user->first_name = "Gökhan";
$user->save();
print_r($user); // otomatik id alır

// Ör. 2:

$user = User::draft(["first_name" => "Gökhan"])->save();
print_r($user); // otomatik id alır
```

> `create`

``` php
$user = User::create(["first_name" => "Gökhan"]);
print_r($user);
```

#### READ ( `load`, `select`, `where`, `or_where`, `order`, `group`, `limit`, `take`, `pluck`, `count`, `joins`, `find`, `find_all`, `all`, `first`, `last` )

> `load`


```php
$users = User::load()->take();
foreach ($users as $user)
  echo $user->first_name;
```

> `where`


operators: `=`, `!=`, `>`, `<`, `>=`, `<=`


```php
$users = User::load()->where("first_name", "Gökhan")->take();
$users = User::load()->where("first_name", "Gökhan", "=")->take();
// SELECT * FROM user WHERE first_name = 'Gökhan';

$users = User::load()->where("age", 25, "<>")->take();
// SELECT * FROM user WHERE age <> 25;

$users = User::load()->where("age", 25, ">")->take();
// SELECT * FROM user WHERE age > 25;

$users = User::load()->where("age", 25, "<")->take();
// SELECT * FROM user WHERE age < 25;

$users = User::load()->where("age", 25, ">=")->take();
// SELECT * FROM user WHERE age >= 25;

$users = User::load()->where("age", 25, "<=")->take();
// SELECT * FROM user WHERE age <= 25;
```

operators: `IS NULL`, `IS NOT NULL`

```php
$users = User::load()->where("email", NULL)->take();
$users = User::load()->where("email", "IS NULL")->take();
// SELECT * FROM user WHERE email IS NULL;
$users = User::load()->where("email", "IS NOT NULL")->take();
// SELECT * FROM user WHERE email IS NOT NULL;
```

operators: `LIKE`, `NOT LIKE`

```php
$users = User::load()->where("email", "%.com.tr", "LIKE")->take();
// SELECT * FROM user WHERE email LIKE '%.com.tr';
$users = User::load()->where("email", "%.com.tr", "NOT LIKE")->take();
// SELECT * FROM user WHERE email NOT LIKE '%.com.tr';
```

operators: `IN`, `NOT IN`

```php
$users = User::load()->where("id", [1, 2, 3], "IN")->take();
// SELECT * FROM user WHERE id IN (1, 2, 3);
$users = User::load()->where("id", [1, 2, 3], "NOT IN")->take();
// SELECT * FROM user WHERE id NOT IN (1, 2, 3)
```

operators: `BETWEEN`, `NOT BETWEEN`

```php
$users = User::load()->where("created_at", ["2016-12-01", "2016-13-01"], "BETWEEN")->take();
// SELECT * FROM user WHERE created_at BETWEEN "2016-12-01" AND "2016-13-01";
$users = User::load()->where("created_at", ["2016-12-01", "2016-13-01"], "NOT BETWEEN")->take();
// SELECT * FROM user WHERE created_at NOT BETWEEN "2016-12-01" AND "2016-13-01";
```

> `or_where`

only logic key: `OR`

```php
// where($field, $value, $mark, "OR")
$users = User::load()->where("first_name", "Gökhan")->or_where("last_name", "Demir")->take();
$users = User::load()->where("first_name", "Gökhan", "=", "AND")->where("last_name", "Demir", "=", "OR")->take();
// SELECT * FROM user WHERE first_name = 'Gökhan' OR last_name = 'Demir';
```

> `select`, `where`, `order`, `group`, `limit`, `take`

```php
$users = User::load()
           ->where("first_name", "Gökhan")
           ->select("first_name")
           ->order("id")
           ->limit(10)
           ->take();

foreach ($users as $user)
  echo $user->first_name;
```

> `pluck`

```php
// Ör. 1:
$user_ids = User::load()->pluck("id");
print_r($user_ids);
// [1, 2, 3, 4, 66, 677, 678]


// Ör. 2:

$user_firstnames = User::load()->pluck("first_name");
print_r($user_firstnames);
// ["Gökhan", "Göktuğ", "Gökçe", "Gökay", "Atilla", "Altay", "Tarkan", "Başbuğ", "Ülkü"]
```

> `count`

```php
// Ör. 1:
echo User::load()->count();
// 12


// Ör. 2:

echo User::load()->where("first_name", "Gökhan")->count();
// 5
```

> `joins`

```php
// Department ["id", "name"], User ["id", "department_id", "first_name"], "Address" ["id", "user_id", "content"]

$department = Department::load()
                ->joins(["User", "Address"])
                ->where("User.id", 1)
                ->select("User.first_name, Department.name, Address.content")
                ->limit(1)
                ->take();
print_r($department);
```

> `unique`

```php
$user = User::unique(["username" => "gdemir", "password" => "123456"]);
echo $user->first_name;
```

> `find`

```php
$user = User::find(1);
echo $user->first_name;
```

> `find_all`

```php
$users = User::find_all([1, 2, 3]);
foreach ($users as $user)
  echo $user->first_name;
```

> `all`

```php
$users = User::all();
foreach ($users as $user)
  echo $user->first_name;
```

> `first`

```php
// Ör. 1:

$user = User::first();
echo $user->first_name;

// Ör. 2:
$users = User::first(10);
foreach ($users as $user)
  echo $user->first_name;
```

> `last`

```php
// Ör. 1:

$user = User::last();
  echo $user->first_name;

// Ör. 2:

$users = User::last(10);
foreach ($users as $user)
  echo $user->first_name;
```

> `exists`

```php
echo User::exists(1) ? "kayit var" : "kayit yok";
```

#### UPDATE ( `save`, `update` )

> `save`

```php
// Ör. 1:

$user = User::unique(["username" => "gdemir", "password" => "123456"]);
$user = User::find(1);
$user = User::first();
$user = User::last();
$user->first_name = "Gökhan";
$user->save()
print_r($user);

// Ör. 2:

$users = User::find_all([1, 2, 3]);
$users = User::load()->take();
$users = User::all();
$users = User::load()
           ->where("first_name", "Gökhan")
           ->select("first_name")
           ->order("id")
           ->limit(10)
           ->take();
$users = User::first(10);
foreach ($users as $user) {
  $user->first_name = "Göktuğ";
  $user->save();
}
```

> `update`

```php
// Ör. 1:

User::update(1, ["first_name" => "Gökhan", "last_name" => "Demir"]);

// Ör. 2:

$users = User::find_all([1, 2, 3]);
$users = User::load()->take();
$users = User::all();
$users = User::load()
           ->where("first_name", "Gökhan")
           ->select("first_name")
           ->order("id")
           ->limit(10)
           ->take();
foreach ($users as $user)
  User::update($user->id, ["first_name" => "Göktuğ", "last_name" => "Demir"]);
```

#### DELETE ( `destroy`, `delete`, `delete_all` )

> `destroy`

```php

$user = User::unique(["username" => "gdemir", "password" => "123456"]);
$user = User::find(1);
$user = User::first();
$user = User::last();
$user->destroy();

```

> `delete`

```php
User::delete(1);
```

> `delete_all`

```php
User::load()->delete_all();
User::load()->where("first_name", "Gökhan")->delete_all();
User::load()->where("first_name", "Gökhan")->limit(10)->delete_all();
User::load()->limit(10)->delete_all();
```

#### Dependencies (`$BELONG_TABLE->OWNER_TABLE`, `$OWNER_TABLE->all_of_BELONG_TABLE`)

> `$BELONG_TABLE->OWNER_TABLE`

```php
// Department ["id", "name"]
// User ["id", "department_id", "first_name", "last_name"]
// Book ["id", "user_id", "name"]

// Department
// [1, "Bilgisayar Mühendisliği"]
// [2, "Makine Mühendisliği"]

// User
// [1, 1, "Gökhan", "Demir"]
// [2, 1, "Göktuğ", "Demir"]
// [3, 2, "Göksen", "Demir"]

// Book
// [1, 1, "Barak Türkmenlerinin Tarihi"]
// [2, 1, "Oğuz Boyu"]
// [3, 3, "Almila"]

// Ör. 1:

$book = Book::find(1);
// [1, 1, "Barak Türkmenlerinin Tarihi"]

print_r($book->user);
// [1, 1, "Gökhan", "Demir"]

print_r($book->user->department);
// [1, "Bilgisayar Mühendisliği"]

echo "$book->user->department->name $book->user->first_name  $book->name";
// "Bilgisayar Mühendisliği Gökhan Barak Türkmenlerinin Tarihi"

```

> `$OWNER_TABLE->all_of_BELONG_TABLE`

```php
// User ["id", "department_id", "first_name", "last_name"]
// Book ["id", "user_id", "name"]

// User
// [1, 1, "Gökhan", "Demir"]
// [2, 1, "Göktuğ", "Demir"]
// [3, 2, "Göksen", "Demir"]

// Book
// [1, 1, "Barak Türkmenlerinin Tarihi"]
// [2, 1, "Oğuz Boyu"]
// [3, 2, "Kımız"]
// [4, 3, "Almila"]

$user = User::find(1);
$books = $user->all_of_book;
foreach ($books as $book)
  echo $book->name;

```

### Configurations (`config/database.ini`, `config/application.ini`)
---

> `config/database.ini` (database configuration file)

```ini
[database_configuration]
host  = localhost
user  = root
pass  = barak
name  = BARAK
```

> `config/application.ini` (application configuration file)

```ini
[app_configuration]
display_errors = true
time_zone      = Europe/Istanbul
```

### Seeds (`db/seeds.php`)

Proje başlamadan önce ilk çalıştırılacak dosyadır.

> `db/seeds.php` (database seeds file)

```php
if (User::load()->count() == 0) {
User::create(["first_name" => "Gökhan", "last_name" => "Demir", "username" => "gdemir",  "password" => "123456"]);
User::create(["first_name" => "Gökçe",  "last_name" => "Demir", "username" => "gcdemir", "password" => "123456"]);
User::create(["first_name" => "Göktuğ", "last_name" => "Demir", "username" => "gtdemir", "password" => "123456"]);
User::create(["first_name" => "Atilla", "last_name" => "Demir", "username" => "ademir",  "password" => "123456"]);
}
```

## Trailer

[![BarakTurkmens](https://img.youtube.com/vi/cYNnHN5w1ok/2.jpg)](https://www.youtube.com/watch?v=cYNnHN5w1ok)
[![BarakTurkmens#İskan](https://img.youtube.com/vi/haNqSJKs_j4/2.jpg)](https://www.youtube.com/watch?v=haNqSJKs_j4)
[![BarakTurkmens#YanıkKerem](https://img.youtube.com/vi/m21oNITMdyI/2.jpg)](https://www.youtube.com/watch?v=m21oNITMdyI)
[![BarakTurkmens#MürselBey](https://img.youtube.com/vi/uSoz28QpHRI/2.jpg)](https://www.youtube.com/watch?v=uSoz28QpHRI)
[![BarakTurkmens#VeledBey](https://img.youtube.com/vi/3RBtPGWRnsI/2.jpg)](https://www.youtube.com/watch?v=3RBtPGWRnsI)
[![BarakTurkmens#VeledBey2](https://img.youtube.com/vi/CiThgSNoSr0/2.jpg)](https://www.youtube.com/watch?v=CiThgSNoSr0)

## Sources

- [https://tr.wikipedia.org/wiki/Barak_T%C3%BCrkmenleri](https://tr.wikipedia.org/wiki/Barak_T%C3%BCrkmenleri)

## License

Barak is released under the [MIT License](http://www.opensource.org/licenses/MIT).

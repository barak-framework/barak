# BARAK FRAMEWORK

## Introduction

Barak Framework PHP diliyle yazılmış, açık kaynak kodlu bir web uygulama geliştirme çatısıdır. Web uygulamaları için ihtiyaç duyulabilecek bütün bileşenleri barındıran Barak; MVC (model-view-controller), DRY (don't repeat yourself), CoC (convention over configuration) yaklaşımlarını temel alır. Barak ile aktif hızlı ve kolay RESTful web uygulamaları yapabilirsiniz.

### Requirements

- Packages

> Programming Language : `Php`

> Database Access : `MySQL`

> Web Server : `Apache`, `Nginx`, `ISS`

> Package Manager : `Composer`

- Installation

> Linux, Apache, MySQL, Php Installation : [LAMP](http://gdemir.github.io/categories/linux/lamp/)

> Linux, Nginx, MySQL, Php Installation : [LEMP](http://gdemir.github.io/categories/linux/lemp/)

> Web Server Settings : [apache2-settings](https://github.com/barak-framework/barak/blob/master/.htaccess.sample) or [nginx-settings](https://github.com/barak-framework/barak/blob/master/nginx.config.sample) or [iss-settings](https://github.com/barak-framework/barak/blob/master/web.config.sample)

> Package Mananger Installation : [composer-installation](http://gdemir.github.io/categories/php/composer/)

### Installing Barak

```sh
composer create-project barak-framework/barak project_name
```

### Run

    cd project_name
    php -S localhost:9090

and check homepage : [http://localhost:9090](http://localhost:9090) and thats all!

### Releases

- [https://github.com/barak-framework/barak/releases](https://github.com/barak-framework/barak/releases)

### License

Barak is released under the [MIT License](http://www.opensource.org/licenses/MIT).

---

## Guides

### Simple Usage

---

> request url : `/`


> `config/routes.php`

```php
ApplicationRoutes::draw(function() {
  get("/", "home#index");
});
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

Herhangi bir İstek URL çalışabilmesi için yönlendirilme dosyasında (`config/routes.php`) ne tür bir istek olduğu tanımlanmalıdır. Eğer istek URL bulunmuyorsa  `public/404.html` sayfası gösterilir.

- Kick Function (static)

> `draw`

- Route Functions (global)

> `get`, `post`, `resource`, `resources`, `scope`, `root`

#### `draw` (`function() { /* ROUTE_FUNCTIONS */ }`)

Tanımlaması yapılan yönlendirmelerin okunması ve çalışması için tetikleyici fonksiyondur. Bu fonksiyon ikinci kez kullanıldığında işleme almamaktadır.

```php
ApplicationRoutes::draw(function() {
  /* ROUTE_FUNCTIONS */
});
```

#### `get` ($rule, $target = false, $path = null)

- Simple

> `config/routes.php`

```php
ApplicationRoutes::draw(function() {
  get("/home/index");
});
```

- Dynamical Segment

Dinamik route tanımlamalarında "home#index" gibi hedef belirtilmelidir.

> `config/routes.php`

```php
ApplicationRoutes::draw(function() {
  get("/home/index/:id", "home#index");
});
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
<?= "id: $id"; ?>
```

#### `post` ($rule, $target = false, $path = null)

- Simple

> `config/routes.php`

```php
ApplicationRoutes::draw(function() {
  post("/admin/login");
});
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
        $_SESSION["admininfo"] = $user;
        $_SESSION["admin"] = true;

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

#### `resource` ($rule, $path = null)

> `config/routes.php`

```php
ApplicationRoutes::draw(function() {
  resource("/users");
});
```

> *Aşağıdaki routes kümesini üretir.*

```php
ApplicationRoutes::draw(function() {
  get("/users/", "users#index"); // all record
  get("/users/create");          // new record form
  post("users/save");            // new record save
  get("/users/show");            // display record
  get("/users/edit");            // edit record
  post("/users/update");         // update record
  post("/users/destroy");        // destroy record
});
```

#### `resources` ($rule, $path = null)

> `config/routes.php`

```php
ApplicationRoutes::draw(function() {
  resources("/users");
});
```

> *Aşağıdaki routes kümesini üretir.*

```php
ApplicationRoutes::draw(function() {
  get("/users", "users#index");         // all record
  get("/users/create");                 // new record form
  post("/users/save");                  // new record save
  get("/users/show/:id", "users#show"); // display record
  get("/users/edit/:id", "users#edit"); // edit record
  post("/users/update");                // update record
  post("/users/destroy");               // destroy record
});
```

#### `scope` ($path, callable $routes)

Kodları daha derli toplu kullanmak için Route'in gruplama özelliğidir. Bir `PATH` altında `CONTROLLER` ve `VIEW` dizininin çalışma imkanı sağlar.

> controller: `app/controllers/PATH/CONTROLLER.php`

> view : `app/views/VIEW/PATH/CONTROLLER/ACTION.php`

- Simple

> `config/routes.php`

> view : `app/views/admin/categories/ACTION.php`
> controller : `app/controllers/admin/CategoriesController.php`

```php
ApplicationRoutes::draw(function() {
 scope("/admin", function() {
    resources("/categories");
 });
});
```
> *Aşağıdaki routes kümesini üretir.*

```php
ApplicationRoutes::draw(function() {
  get("/admin/categories",          "categories#index", "/admin");  // all record
  get("/admin/categories/create",   false,              "/admin");  // new record form
  post("/admin/categories/save",    false,              "/admin");  // new record save
  get("/admin/categories/show/:id", "categories#show",  "/admin");  // display record
  get("/admin/categories/edit/:id", "categories#edit",  "/admin");  // edit record
  post("/admin/categories/update",  false,              "/admin");  // update record
  post("/admin/categories/destroy", false,              "/admin");  // destroy record
});
```

- Multiple

```php
ApplicationRoutes::draw(function() {
  scope("/admin", function() {
    scope("/dashboard", function() {
      resources("/categories");
    });
  });
});
```

> *Aşağıdaki routes kümesini üretir.*

```php
ApplicationRoutes::draw(function() {
  get("/admin/dashboard/categories",          "categories#index", "/admin/dashboard");  // all record
  get("/admin/dashboard/categories/create",   false,              "/admin/dashboard");  // new record form
  post("/admin/dashboard/categories/save",    false,              "/admin/dashboard");  // new record save
  get("/admin/dashboard/categories/show/:id", "categories#show",  "/admin/dashboard");  // display record
  get("/admin/dashboard/categories/edit/:id", "categories#edit",  "/admin/dashboard");  // edit record
  post("/admin/dashboard/categories/update",  false,              "/admin/dashboard");  // update record
  post("/admin/dashboard/categories/destroy", false,              "/admin/dashboard");  // destroy record
});
```

- Mix

> `config/routes.php`

```php
ApplicationRoutes::draw(function() {
  get("/admin/login");
  scope("/admin", function() {
    get("/users", "users#index");
    get("/users/show/:id");
    resources("/categories");
    resource("/products");
  });
});
```

> *Aşağıdaki routes kümesini üretir.*

```php
ApplicationRoutes::draw(function() {
  get("/admin/login");

  get("/admin/users",               "users#index",      "/admin");  // all record
  get("/admin/users/show/:id",      false,              "/admin");  // display record

  get("/admin/categories",          "categories#index", "/admin");  // all record
  get("/admin/categories/create",   false,              "/admin");  // new record form
  post("/admin/categories/save",    false,              "/admin");  // new record save
  get("/admin/categories/show/:id", "categories#show",  "/admin");  // display record
  get("/admin/categories/edit/:id", "categories#edit",  "/admin");  // edit record
  post("/admin/categories/update",  false,              "/admin");  // update record
  post("/admin/categories/destroy", false,              "/admin");  // destroy record

  get("/admin/products",           "products#index",    "/admin");  // all record
  get("/admin/products/create",     false,              "/admin");  // new record form
  post("/admin/products/save",      false,              "/admin");  // new record save
  get("/admin/products/show",       false,              "/admin");  // display record
  get("/admin/products/edit",       false,              "/admin");  // edit record
  post("/admin/products/update",    false,              "/admin");  // update record
  post("/admin/products/destroy",   false,              "/admin");  // destroy record
});
```

#### `root` ($target = false, $path = null)

> `config/routes.php`

```php
ApplicationRoutes::draw(function() {
  root("home#index");
});
```

> *Aşağıdaki routes kümesini üretir.*

```php
ApplicationRoutes::draw(function() {
  get("/", "home#index");
});
```

### Controllers (`app/controllers/*.php`)

---

Her controller dosyası ile sınıfının ismi aynı olmalıdır ve sistemin olan `ApplicationController` sınıfından miras alır.

```php
// dosya : `app/controllers/HomeController.php`
class HomeController extends ApplicationController {
}
```

Her `config/routes.php` içerisinde tanımlanan

1. `get` yönlendirmesi için  `app/controller/CONTROLLER.php` sınıfı içerisinde fonksiyon tanımlamak zorunlu değildir. Eğer fonksiyon tanımlanırsa ve değişken yükü/yükleri controller içinde `$this->KEY` şeklinde atandığında ilgili yönlenen sayfada (`app/views/CONTROLLER/ACTION.php`) bu veriye `$KEY` şeklinde erişme imkanı verir.
2. `post` yönlendirmesi için `app/controller/CONTROLLER.php` sınıfı içerisinde fonksiyon tanımlamak zorunludur.

- Functions

> `render`, `redirect_to`

- Options

> `helpers`, `before_actions`, `after_actions`

#### `render`
##### (["view" => $view, "action" => $action, "template" => $template, "layout" => $layout, "locals" => $locals, "file" => $file, "partial" => $partial, "text" => $text])
or
##### ($template)

> options : `layout`, `view`, `action`, `template`, `file`, `text`, `partial`, `locals`

```php
class HomeController extends ApplicationController {

  public function index() {
    echo "HomeIndex sayfası öncesi çalışan fonksiyon";

    /////////////////////////////////////////////////////////////////////////////////
    // default render for this functions examples : /home/index
    /////////////////////////////////////////////////////////////////////////////////

    // LAYOUT: home, VIEW: home, ACTION: index, LOCALS: null
    $this->render("/home/index");

    $this->render(["template" => "/home/index"]);
    $this->render(["template" => "/home/index", "locals" => null]);
    $this->render(["template" => "/home/index", "layout" => "home"]);
    $this->render(["template" => "/home/index", "layout" => "home", "locals" => null]);

    $this->render(["view" => "home"]);
    $this->render(["action" => "index"]);
    $this->render(["layout" => "home"]);

    $this->render(["view" => "home", "action" => "index"]);
    $this->render(["view" => "home", "action" => "index", "locals" => null]);
    $this->render(["view" => "home", "action" => "index", "layout" => "home"]);
    $this->render(["view" => "home", "action" => "index", "layout" => "home", "locals" = null]);

    /////////////////////////////////////////////////////////////////////////////////
    // no options
    /////////////////////////////////////////////////////////////////////////////////

    // DEFAULT LAYOUT: home, VIEW: home, ACTION: index, DEFAULT LOCALS: null
    $this->render("/home/index"); // like $this->render(["template" => "/home/index"]);

    // DEFAULT LAYOUT: home, VIEW: home, ACTION: show, DEFAULT LOCALS: null
    $this->render("/home/show");  // like $this->render(["template" => "/home/show"]);

    // DEFAULT LAYOUT: home, VIEW: admin, ACTION: show, DEFAULT LOCALS: null
    $this->render("/admin/show"); // like $this->render(["template" => "/admin/show"]);

    /////////////////////////////////////////////////////////////////////////////////
    // option : layout, view, action, template
    /////////////////////////////////////////////////////////////////////////////////

    // LAYOUT: false, DEFAULT VIEW: home, DEFAULT ACTION: index, DEFAULT LOCALS: null
    $this->render(["layout" => false]);

    // DEFAULT LAYOUT: false, DEFAULT VIEW: home, ACTION: index, DEFAULT LOCALS: null
    $this->render(["action" => "index"]);

    // DEFAULT LAYOUT: false, VIEW: home, DEFAULT ACTION: index, DEFAULT LOCALS: null
    $this->render(["view" => "home"]);

    // DEFAULT LAYOUT: home, VIEW: home, ACTION: index, DEFAULT LOCALS: null
    $this->render(["template" => "/home/index"]);

    // DEFAULT LAYOUT: home, VIEW: admin, ACTION: index, DEFAULT LOCALS: null
    $this->render(["view" => "admin", "action" => "index"]);

    // LAYOUT: admin, VIEW: home, ACTION: show, DEFAULT LOCALS: null
    $this->render(["layout" => "admin", "view" => "home", "action" => "show"]);

    // LAYOUT: admin, VIEW: home, ACTION: index, DEFAULT LOCALS: null
    $this->render(["layout" => "admin", "template" => "home/index"]);

    // LAYOUT: admin, VIEW: home, ACTION: show, DEFAULT LOCALS: null
    $this->render(["layout" => "admin", "template" => "home/show"]);

    /////////////////////////////////////////////////////////////////////////////////
    // option : file ( LAYOUT : pass, VIEW : pass, ACTION : pass )
    /////////////////////////////////////////////////////////////////////////////////
    // include locals and this file
    // example file path = "/app/views/home/users/show.php"

    // DEFAULT LOCALS: null
    $this->render(["file" => "/app/views/home/users/show.php"]);

    // LOCALS: ( $fist_name : "Gökhan", $last_name : "Demir" )
    $this->render(["file" => "/app/views/home/users/show.php", "locals" => ["fist_name" => "Gökhan", "last_name" => "Demir"]);

    /////////////////////////////////////////////////////////////////////////////////
    // option : partial ( LAYOUT : pass, VIEW : pass, ACTION : pass )
    /////////////////////////////////////////////////////////////////////////////////
    // include locals and this file "_show.php" on VIEW path
    // example file : /app/views/home/users/_show.php

    // DEFAULT LOCALS: null
    $this->render(["partial" => "home/users/show"]);

    // LOCALS: ( $fist_name : "Gökhan", $last_name : "Demir" )
    $this->render(["partial" => "home/users/show", "locals" => "locals" => ["fist_name" => "Gökhan", "last_name" => "Demir"]]);

    /////////////////////////////////////////////////////////////////////////////////
    // option : text ( LAYOUT : pass, VIEW : pass, ACTION : pass, LOCALS : pass )
    /////////////////////////////////////////////////////////////////////////////////
    // this option, available in Ajax functions

    $this->render(["text" => "Hello World"]);
  }

}
```

#### `redirect_to` ($url)

> request url [`/` or `/home`] redirect to `/home/index`

> `config/routes.php`

```php
ApplicationRoutes::draw(function() {
  get("/", "home#home"); // or root("home#home"),
  get("/home", "home#home");
  get("/home/index");
});
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
<h1> Home#Index </h1>
```

#### `helpers`

Helpers `app/helpers/*Helper.php` şeklinde tanımlanan her controller ya da proje için gerekli görüldüğü yerlerde çağrılması gereken sınıfları projeye dahil eder.

> keys : `$FILE`, `all`

> `$FILE`

İstenilen helper sınıflarını projeye dahil eder.

```php
class HomeController extends ApplicationController {

  protected $helpers = ["Password"];

  public function index() {
    echo "random password : " . PasswordHelper::generate(10);
  }
}
```

> `all`

`app/helpers/*` altındaki tüm helper sınıflarını projeye dahil eder. #TODO anahtar ismi daha özel olabilir ?

```php
class HomeController extends ApplicationController {

  protected $helpers = ["all"];

  public function index() {
    echo "random string   : " . StringHelper::generate(10);
    echo "random password : " . PasswordHelper::generate(10);
  }
}
```

#### `before_actions`

Before Action (`protected $before_actions`) özelliği, `app/controller/*.php` dosyası içerisinde her çalışacak get/post fonksiyonları için önceden çalışacak fonksiyonları belirtmeye yarayan özelliktir. Özelliğin etkisini ayarlamak için aşağıdaki 3 şekilde kullanılabilir:

> options : `except`, `only`

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

#### `after_actions`

After Action (`protected $after_actions`) özelliği, `app/controller/*.php` dosyası içerisinde her çalışacak get/post fonksiyonları için sonradan çalışacak fonksiyonları belirtmeye yarayan özelliktir. Özelliğin etkisini ayarlamak için aşağıdaki 3 şekilde kullanılabilir:

> options : `except`, `only`

1. `except` anahtarı ile nerelerde çalışmayacağını

2. `only` anahtarı ile nerelerde çalışacağını

3. Anahtar yok ise her yerde çalışacağını

> `config/routes.php`

```php
ApplicationRoutes::draw(function() {
  get("/admin/home");
  get("/admin/login");
  post("/admin/login");
});
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
        $_SESSION["admininfo"] = $user;
        $_SESSION["admin"] = true;

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

```html
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

Yönlendirme dosyasında tanımlı olan (`config/routes.php`) her `get` veya `post` yönlendirmeleri için de yönlendirilen `controller` ve `action` adlarını alarak,
view dosyasını (`app/views/CONTROLLER/ACTION.php`) layout dosyası (`app/views/layouts/CONTROLLER.php`) içerisine `<?= $yield; ?>` değişken kısmına gömülür ve görüntülenir.

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

- Functions

> `render`

#### `render`

Fonksiyonu Controller'daki gibi tüm özellikleri ile kullanılabilir. Yalnızca `*.php` dosyalarının içersinde kullanılırken `<?php render(); ?>` şeklinde kullanılmalıdır. Daha ayrıntılı bilgi için [Controller#render](https://github.com/barak-framework/barak/blob/master/README.md#render)

### Model (`app/models/TABLE.php`)

---

Her hazırlanan `Tablo` kullanılırken,

1. Her tablo isminin harfleri küçük **olmalıdır**. (Ör.: user, agenda, page, product)
2. Her tablo `id` değerine sahip olmalı ve `auto_increment` **olmalıdır**.
3. Her tablo sütunlarının(`id` hariç) varsayılan değeri `NULL` **olmalıdır**.

Her hazırlanan `Model` kullanılırken,

1. Her tablonun bir modeli olmak **zorundadır**.
2. Her model adının ilk harfi büyük olmak **zorundadır**.  (Ör.: tablo: `user` ise `User` olmalıdır.)

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

- Query Kick Function (static)

> `load`

- Query Load Functions (public)

> `select`, `where`, `or_where`, `joins`, `order`, `group`, `limit`, `offset`

- Query Fetch Functions (public)

> `get`, `get_all`, `pluck`, `count`, `update_all`, `delete_all`, `first`, `last`

- Query Function Helpers (static) [alias]

> `all`, `unique`, `find`, `find_all`, `exists`, `update`, `delete`

- Model Functions

> `draft`, `create`, `save`, `destroy`

#### CREATE

- Functions

>  `draft`, `create`

##### `draft` ([$field1 => $value1, ...])

```php
// Ör. 1:

$user = User::draft();
$user->first_name = "Gökhan";
$user->save();
print_r($user); // otomatik id alır
```

```php
// Ör. 2:

$user = User::draft(["first_name" => "Gökhan"])->save();
print_r($user); // otomatik id alır
```

##### `create` ([$field1 => $value1, ...])

``` php
$user = User::create(["first_name" => "Gökhan"]);
print_r($user);
```

#### READ

- Functions

> `load`, `select`, `where`, `or_where`, `order`, `group`, `limit`, `get`, `get_all`, `pluck`, `count`, `joins`, `find`, `find_all`, `all`, `first`, `last`

##### `load` ()

- With `get`

```php
// Ör. 1:

$user = User::load()->get();
// SELECT * FROM user LIMIT 1

echo $user->first_name;
```

- With `get_all`

```php
// Ör. 2:

$users = User::load()->get_all();
// SELECT * FROM user

foreach ($users as $user)
  echo $user->first_name;
```

##### `select` ("tablename.field1", ...)

 Tablodan her kayıt bir sınıfa yüklenirken sütun ismi olarak `id` otomatik olarak eklenmektedir.

- Simple

```php
// Ör. 1:

// User ["id", "first_name", "last_name"]
// 1, Gökhan, Demir
// 2, Gökhan, Arıoğlu

$users = User::load()
           ->select("last_name") // or ->select("user.id", "user.last_name")
           ->get_all();

foreach ($users as $user)
  echo "$user->id, $user->last_name";

// 1, Demir
// 2, Arıoğlu
```

- With `joins`

`joins` kullanılıyor ise `select` işleminde ilişki kurulan tabloya erişimde tablo ismi yazılmalıdır. (Ör.: address.content)
Ayrıca  `joins` işleminde tüm sütunlar otomatik gelmektedir, bundan dolayı istediğiniz bir alan var ise `select` işlemini `joins`den sonra kullanılmalıdır.

```php
// Ör. 2:

// user ["id", "first_name", "last_name"]
// address ["id", "phone" "user_id"]

$users = User::load()
           ->joins("address")
           ->select("user.first_name", "user.last_name", "address.phone")
           ->get_all();

foreach ($users as $user)
  echo "$user->first_name, $user->phone";
```

##### `where`
##### ($key, $value, $mark={"=", "LIKE", "NOT LIKE", "IN", "NOT IN", "BETWEEN", "NOT BETWEEN"}, $logic={"AND", "OR"})
or
##### ($key, $mark={"NULL", "IS NULL", "IS NOT NULL"}, $logic={"AND", "OR"})

defaults: mark="=", logic="AND"


operators: `=`, `!=`, `>`, `<`, `>=`, `<=`

```php
$users = User::load()->where("first_name", "Gökhan")->get_all();
$users = User::load()->where("first_name", "Gökhan", "=")->get_all();
// SELECT * FROM user WHERE first_name = 'Gökhan';

$users = User::load()->where("age", 25, "<>")->get_all();
// SELECT * FROM user WHERE age <> 25;

$users = User::load()->where("age", 25, ">")->get_all();
// SELECT * FROM user WHERE age > 25;

$users = User::load()->where("age", 25, "<")->get_all();
// SELECT * FROM user WHERE age < 25;

$users = User::load()->where("age", 25, ">=")->get_all();
// SELECT * FROM user WHERE age >= 25;

$users = User::load()->where("age", 25, "<=")->get_all();
// SELECT * FROM user WHERE age <= 25;
```

operators: `IS NULL`, `IS NOT NULL`

```php
$users = User::load()->where("email", NULL)->get_all();
$users = User::load()->where("email", "IS NULL")->get_all();
// SELECT * FROM user WHERE email IS NULL;
$users = User::load()->where("email", "IS NOT NULL")->get_all();
// SELECT * FROM user WHERE email IS NOT NULL;
```

operators: `LIKE`, `NOT LIKE`

```php
$users = User::load()->where("email", "%.com.tr", "LIKE")->get_all();
// SELECT * FROM user WHERE email LIKE '%.com.tr';
$users = User::load()->where("email", "%.com.tr", "NOT LIKE")->get_all();
// SELECT * FROM user WHERE email NOT LIKE '%.com.tr';
```

operators: `IN`, `NOT IN`

```php
$users = User::load()->where("id", [1, 2, 3], "IN")->get_all();
// SELECT * FROM user WHERE id IN (1, 2, 3);
$users = User::load()->where("id", [1, 2, 3], "NOT IN")->get_all();
// SELECT * FROM user WHERE id NOT IN (1, 2, 3)
```

operators: `BETWEEN`, `NOT BETWEEN`

```php
$users = User::load()->where("created_at", ["2016-12-01", "2016-13-01"], "BETWEEN")->get_all();
// SELECT * FROM user WHERE created_at BETWEEN "2016-12-01" AND "2016-13-01";
$users = User::load()->where("created_at", ["2016-12-01", "2016-13-01"], "NOT BETWEEN")->get_all();
// SELECT * FROM user WHERE created_at NOT BETWEEN "2016-12-01" AND "2016-13-01";
```

##### `or_where` ($field, $value, $mark="=")

defaults: mark="="

only logic key: `OR`

```php
// where($field, $value, $mark, "OR")
$users = User::load()->where("first_name", "Gökhan")->or_where("last_name", "Demir")->get_all();
$users = User::load()->where("first_name", "Gökhan", "=", "AND")->where("last_name", "Demir", "=", "OR")->get_all();
// SELECT * FROM user WHERE first_name = 'Gökhan' OR last_name = 'Demir';
```

##### `order` ($field, $sort_type={"DESC", "ASC"})

- defaults: sort_type="ASC"


- Simple

```php

$users = User::load()
           ->order("first_name") // or ->order("first_name", "ASC")
           ->get_all();

foreach ($users as $user)
  echo $user->first_name;
```

- Multiple

```php
$users = User::load()
           ->order("first_name")
           ->order("last_name", "DESC")
           ->get_all();

foreach ($users as $user)
  echo $user->first_name;
```

##### `group` ("tablename.field1", ...)

- Simple

Bir sütun seçilip ve seçilmeyen sütunlar gösterilmeye çalışılınırsa ilk bulduğu kaydı getirdiği için ilk kaydın da  sütunlarını getirmektedir. Bu `GROUP BY`ın  olağan sonucudur.

```php
// Ör.: 1

// user ["id", "first_name", "last_name"]
// 1, Gökhan, Demir
// 2, Gökhan, Demir
// 3, Gökhan, Arıoğlu
// 4, Gökhan, Seven
// 5, Gökçe, Demir
// 6, Gökçe, Arıoğlu

$users = User::load()
           ->group("first_name") // or ->group("user.first_name")
           ->get_all();

foreach ($users as $user)
  echo "$user->id, $user->first_name, $user->last_name";

// 1, Gökhan, Demir
// 4, Gökçe, Demir
```

- Simple With `count`

```php
// Ör.: 2

// user ["id", "first_name", "last_name"]
// 1, Gökhan, Demir
// 2, Gökhan, Demir
// 3, Gökhan, Arıoğlu
// 4, Gökhan, Seven
// 5, Gökçe, Demir
// 6, Gökçe, Arıoğlu

$user_count = User::load()
                ->group("first_name") // or ->group("user.first_name")
                ->count();

// [4 => ["First_name" => "Gökhan"], 2 => ["first_name" => "Gökçe"]]

foreach ($user_count as $count => $user_fields)
  echo $user_count . " : " . implode(",", $user_fields));

// 4 : Gökhan
// 2 : Gökçe
```

- Multiple
```php
// Ör.: 2

// user ["id", "first_name", "last_name"]
// 1, Gökhan, Demir
// 2, Gökhan, Demir
// 3, Gökhan, Arıoğlu
// 4, Gökhan, Seven
// 5, Gökçe, Demir
// 6, Gökçe, Arıoğlu

$users = User::load()
           ->where("first_name", "Gökhan")
           ->group("first_name", "last_name")
           ->get_all();

foreach ($users as $user)
  echo "$user->id, $user->first_name, $user->last_name";

// 1, Gökhan, Demir
// 3, Gökhan, Arıoğlu
// 4, Gökhan, Seven
```

- Multiple With `count`

```php
// Ör.: 1

// user ["id", "first_name", "last_name"]
// 1, Gökhan, Demir
// 2, Gökhan, Demir
// 3, Gökhan, Arıoğlu
// 4, Gökhan, Seven
// 5, Gökçe, Demir
// 6, Gökçe, Arıoğlu

$user_counts = User::load()
                 ->where("user.first_name", "Gökhan")
                 ->group("user.first_name", "user.last_name")
                 ->count();
/*
[
2 => ["First_name" => "Gökhan", "last_name" => "Demir"],
1 => ["First_name" => "Gökhan", "last_name" => "Arıoğlu"],
1 => ["First_name" => "Gökhan", "last_name" => "Seven"]
]
*/

foreach ($user_counts as $user_count => $user_fields)
  echo $user_count . " : " . implode(",", $user_fields));

// 2 : Gökhan, Demir
// 1 : Gökhan, Arıoğlu
// 1 : Gökhan, Seven
```

- Multiple With `joins`, `count`

```php
// Ör.: 3

// user ["id", "first_name", "last_name"]
// 1, Gökhan, Demir
// 2, Gökhan, Arıoğlu
// 3, Gökçe, Demir
// 4, Gökçe, Arıoğlu

// address ["id", "country_id", "user_id"]
// 1, 1, 1
// 2, 1, 2
// 3, 1, 3
// 4, 2, 4

// country ["id", "name"]
// 1, Mersin
// 2, Samsun

$user_count = User::load()
                ->joins("address")
                ->group("user.first_name", "address.country_id")
                ->count();
/*
[
2 => ["first_name" => Gökhan, "address_country_id" => 1],
1 => ["first_name" => Gökçe, "address_country_id" => 1],
1 => ["first_name" => Gökçe, "address_country_id" => 2]
]
*/

$user_count = User::load()
                ->joins(["address" => "country"])
                ->group("user.first_name", "country.name")
                ->count();

/*
[
2 => ["first_name" => Gökhan, "country_name" => Mersin],
1 => ["first_name" => Gökçe, "country_name" => Mersin],
1 => ["first_name" => Gökçe, "country_name" => Samsun]
]
*/
```

##### `limit` ($limit=1)

- defaults: limit=1

```php
$users = User::load()
           ->limit(10)
           ->get_all();

print_r($users);
```

##### `pluck` ($field)

```php
// Ör. 1:

$user_ids = User::load()->pluck("id");
print_r($user_ids);
// [1, 2, 3, 4, 66, 677, 678]
```

```php
// Ör. 2:

$user_firstnames = User::load()->where("last_name", "Demir")->pluck("first_name");
print_r($user_firstnames);
// ["Gökhan", "Göktuğ", "Gökçe", "Gökay", "Atilla", "Altay", "Tarkan", "Başbuğ", "Ülkü"]
```

##### `count` ()

Bu fonksiyon kullnılırken eğer `group` kullanılmamışsa direkt rakamsal sonuç döner, ancak `group` kullanılmışsa dizi döner.

```php
// Ör. 1:

echo User::load()->count();
// 12
```

```php
// Ör. 2:

echo User::load()->where("first_name", "Gökhan")->count();
// 5
```

##### `joins` ($table) or ([$table]) or ([$table1 => $table2]) or ([$table1 => [$table2 => $table3]) or ([$table1 => [$table2, $table3 => [$table4]]])

İlk tablo sütunları hariç join işleminde select çakışmasını önlemek için diğer tablo alan bilgileri `$TABLE_$field` şeklinde gelmektedir. (Ör.: `user.first_name as user_first_name` gibi)
Veriler alınırken eğer ilişki kurulan diğer tabloda ilişik-veri (yabancı anahtar bazlı bir satır) yok ise kayıt getirmeyecektir. Bu `INNER JOIN`in olağan sonucudur.

```php
// Ör. 1:

// category ["id", "name"]
// article ["id", "category_id"]
// like ["id", "article_id"]
// comment ["id", "article_id"]
// tag ["id", "comment_id"]
// document ["id", "category_id"]

$categories = Category::load()->joins("article")->get_all();
// SELECT category.id, category.name, article.id as article_id, article.category_id as article_category_id * FROM category INNER JOIN article ON article.category_id=category.id;

$categories = Category::load()->joins(["article"])->get_all();
$categories = Category::load()->joins(["article" => "comment"])->get_all();
$categories = Category::load()->joins(["article" => ["comment" => ["tag"]]])->get_all();
$categories = Category::load()->joins(["article" => ["like", "comment" => ["tag"]]])->get_all();
$categories = Category::load()->joins(["document", "article" => ["like", "comment" => ["tag"]]])->get_all();
$categories = Category::load()->joins(["article", "document"])->get_all();
```

```php
// Ör. 2:

// department ["id", "name"]
// user ["id", "department_id", "first_name"]
// address ["id", "user_id", "content"]

$department = Department::load()
                ->joins(["user", "address"])
                ->where("user.id", 1)
                ->select("user.first_name", "department.name", "address.content")
                ->limit(1)
                ->get_all();
print_r($department);
```

##### `unique` ([$field1 => $value1, ...])

```php
$user = User::unique(["username" => "gdemir", "password" => "123456"]);
echo $user->first_name;
```

##### `find` ($id)

```php
$user = User::find(1);
echo $user->first_name;
```

##### `find_all` ([$id1, ...])

```php
$users = User::find_all([1, 2, 3]);
foreach ($users as $user)
  echo $user->first_name;
```

##### `all` ()

```php
$users = User::all();
foreach ($users as $user)
  echo $user->first_name;
```

##### `first` ($count=1)

defaults: count=1

```php
// Ör. 1:

$user = User::first();
echo $user->first_name;
```

```php
// Ör. 2:

$users = User::first(10);
foreach ($users as $user)
  echo $user->first_name;
```

##### `last` ($count=1)

defaults: count=1

```php
// Ör. 1:

$user = User::last();
  echo $user->first_name;
```

```php
// Ör. 2:

$users = User::last(10);
foreach ($users as $user)
  echo $user->first_name;
```

##### `exists` ($id)

```php
echo User::exists(1) ? "kayit var" : "kayit yok";
```

#### UPDATE

- Functions

> `save`, `update`

##### `save` ()

```php
// Ör. 1:

$user = User::unique(["username" => "gdemir", "password" => "123456"]);
$user = User::find(1);
$user = User::load()->get();
$user = User::first();
$user = User::last();
$user->first_name = "Gökhan";
$user->save();

print_r($user);
```

```php
// Ör. 2:

$users = User::find_all([1, 2, 3]);
$users = User::load()->get_all();
$users = User::all();
$users = User::load()
           ->where("first_name", "Gökhan")
           ->select("first_name")
           ->order("id")
           ->limit(10)
           ->get_all();
$users = User::first(10);

foreach ($users as $user) {
  $user->first_name = "Göktuğ";
  $user->save();
}
```

##### `update` ($id, [$field1 => $value1, ...])

```php
// Ör. 1:

User::update(1, ["first_name" => "Gökhan", "last_name" => "Demir"]);
```

```php
// Ör. 2:

$users = User::find_all([1, 2, 3]);
$users = User::load()->get_all();
$users = User::all();
$users = User::load()
           ->where("first_name", "Gökhan")
           ->select("first_name")
           ->order("id")
           ->limit(10)
           ->get_all();
foreach ($users as $user)
  User::update($user->id, ["first_name" => "Göktuğ", "last_name" => "Demir"]);
```

#### DELETE

- Functions

> `destroy`, `delete`, `delete_all`

##### `destroy` ()

```php
$user = User::unique(["username" => "gdemir", "password" => "123456"]);
$user = User::find(1);
$user = User::load()->get();
$user = User::first();
$user = User::last();
$user->destroy();
```

##### `delete` ($id)

```php
User::delete(1);
```

##### `delete_all` ()

```php
User::load()->delete_all();
User::load()->where("first_name", "Gökhan")->delete_all();
User::load()->where("first_name", "Gökhan")->limit(10)->delete_all();
User::load()->limit(10)->delete_all();
```

#### DEPENDENCİES

> `$BELONG_TABLE->OWNER_TABLE`

```php
// department ["id", "name"]
// user ["id", "department_id", "first_name", "last_name"]
// book ["id", "user_id", "name"]

// department
// [1, "Bilgisayar Mühendisliği"]
// [2, "Makine Mühendisliği"]

// user
// [1, 1, "Gökhan", "Demir"]
// [2, 1, "Göktuğ", "Demir"]
// [3, 2, "Göksen", "Demir"]

// book
// [1, 1, "Barak Türkmenlerinin Tarihi"]
// [2, 1, "Oğuz Boyu"]
// [3, 3, "Almila"]

$book = Book::find(1);
// [1, 1, "Barak Türkmenlerinin Tarihi"]

print_r($book->user);
// [1, 1, "Gökhan", "Demir"]

print_r($book->user->department);
// [1, "Bilgisayar Mühendisliği"]

echo "$book->user->department->name $book->user->first_name $book->name";
// "Bilgisayar Mühendisliği Gökhan Barak Türkmenlerinin Tarihi"
```

> `$OWNER_TABLE->all_of_BELONG_TABLE`

```php
// user ["id", "department_id", "first_name", "last_name"]
// book ["id", "user_id", "name"]

// user
// [1, 1, "Gökhan", "Demir"]
// [2, 1, "Göktuğ", "Demir"]
// [3, 2, "Göksen", "Demir"]

// book
// [1, 1, "Barak Türkmenlerinin Tarihi"]
// [2, 1, "Oğuz Boyu"]
// [3, 2, "Kımız"]
// [4, 3, "Almila"]

$user = User::find(1);
$books = $user->all_of_book;
foreach ($books as $book)
  echo $book->name;
```

### Mailer (`app/mailers/*.php`)

---

Mailer sınıf olarak `PHPMailer`i kullanmaktadır ve yapı olarak Controller sınıfındaki gibi benzeri görev yapmaktadır. Ayarlama olarak `helper`, `before_actions`, `after_actions` yardımcı özelliklerini kullanabilme imkanı vermektedir.

Her hazırlanan Mailer sınıfı kullanırken,

1. Sınıf `app/mailers/*.php` isminde tanımlanmalıdır.
2. Sınıf içerisinde tanımlanan fonksiyonlarda `mail` fonksiyonu kullanılmak **zorunludur**.
3. Layout olarak **zorunlu** `app/views/layouts/mailer.php` dosyasını kullanmaktadır.
4. View olarak **zorunlu** `app/views/mail` dizinini kullanmaktadır. İstenilen actiona göre `app/views/mail/ACTION.php` dosyası tanımlanması gerekir.


- Mailer Kick Functions

> `delivery`

- Functions

> `mail`

- Options

> `helpers`, `before_actions`, `after_actions`

#### `delivery` ($action, [$param1, ...])

1. parametre olarak kullanılacak Mailer içersindeki method ismi yazılır.
2. parametre eğer method bir veri alacak şekilde tanımlandıysa bu veriler liste içersinde gönderilir.

> `app/controllers/HomeController.php`

```php
class HomeController extends ApplicationController {
  public function index() {
    PasswordMailer::delivery("reset");
    PasswordMailer::delivery("reset2", ["m930fj039fj039j", "gdemir.me", "mail@gdemir.me", "Gökhan Demir"]);
  }
}
```

> `app/mailers/PasswordMailer.php`

```php
class PasswordMailer extends ApplicationMailer {

  protected $after_actions = [["info"]];

  public function info() {
    if (isset($this->email) && isset($this->fullname)) {
      $this->mail([
        "to" => [$this->email => $this->fullname],
        "subject" => "Güçlü Şifre İçin Öneriler"
      ]);
    }
  }

  public function reset() {
    $this->code = "ab234c2589de345fgAASD6";
    $this->site_url = "gdemir.me";
    $this->mail([
      "to" => ["mail@gdemir.me" => "Gökhan Demir"],
      "subject" => "[Admin] Please reset your password"
      ]);
  }

  public function reset2($random_code, $site_url, $email, $fullname) {
    $this->code = $random_code;
    $this->site_url = $site_url;
    $this->mail([
      "to" => [$email => $fullname],
      "subject" => "[Admin] Please reset your password"
      ]);
  }
}
```

#### `mail` (["to" => [$email1 => $name1, ...], "subject" => $subject])

> options : `to`, `subject`

> `app/controllers/HomeController.php`

```php
class HomeController extends ApplicationController {
  public function index() {
    PasswordMailer::delivery("reset");
    PasswordMailer::delivery("reset2", ["m930fj039fj039j", "gdemir.me"]);
  }
}
```

> `app/mailers/UserMailer.php`

```php
class UserMailer extends ApplicationMailer {

  protected $after_actions = [["info"]];

  protected $before_actions = [["notice"]];

  public function notice() {
    $this->mail([
      "to" => ["gdemir@bil.omu.edu.tr" => "Gökhan Demir"],
      "subject" => "1 Kullanıcı Şifre Sıfırlama Talebinde Bulundu"
      ]);
  }

  public function info() {
    if (isset($this->email) && isset($this->fullname)) {
      $this->mail([
        "to" => [$this->email => $this->fullname],
        "subject" => "Güçlü Şifre İçin Öneriler"
      ]);
    }
  }

  public function reset() {
    $this->code = "ab234c2589de345fgAASD6";
    $this->site_url = "gdemir.me";
    $this->mail([
      "to" => ["mail@gdemir.me" => "Gökhan Demir"],
      "subject" => "[Admin] Please reset your password"
      ]);
  }

  public function reset2($random_code, $site_url, $email, $fullname) {
    $this->code = $random_code;
    $this->site_url = $site_url;
    $this->email = $email;
    $this->fullname = $fullname;
    $this->mail([
      "to" => [$this->email => $this->fullname],
      "subject" => "[Admin] Please reset your password"
      ]);
  }
}
```

// default layout TODO change?
> `app/views/layouts/mailer.php`

```html
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="tr" lang="tr">
<head>
  <meta http-equiv="content-type" content="text/html; charset=utf-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title></title>
  <link href="" rel="alternate" title="" type="application/atom+xml" />
  <link rel="shortcut icon" href="/favicon.ico">
  <link rel="stylesheet" href="/app/assets/css/syntax.css" type="text/css" />
  <link href='http://fonts.googleapis.com/css?family=Monda' rel='stylesheet' type='text/css'>

  <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="/app/assets/js/html5shiv.min.js"></script>
  <script src="/app/assets/js/respond.min.js"></script>
  <![endif]-->

  <script src="http://code.jquery.com/jquery.js"></script>
  <script src="/app/assets/js/bootstrap.min.js"></script>
</head>
<body>
  <div class="container" style="width:365px; min-height:200px; margin-top: 8%;">
    <?= $yield; ?>
  </div>
</body>
</html>
```

> `app/views/mail/password/reset.php`

```html
Sistem şifrenizi kaybettiğinizi duyduk. Üzgünüm!<br/><br/>
Endişelenme! Parolanızı sıfırlamak için 1 saat içinde aşağıdaki bağlantıyı kullanabilirsiniz:<br/><br/>
<a href='http://$_site_url/admin/password_reset/$code'>http://$_site_url/admin/password_reset/$code</a>
```

> `app/views/mail/password/reset2.php`

```html
Sistem şifrenizi kaybettiğinizi duyduk. Üzgünüm!<br/><br/>
Endişelenme! Parolanızı sıfırlamak için 1 saat içinde aşağıdaki bağlantıyı kullanabilirsiniz:<br/><br/>
<a href='http://$_site_url/admin/password_reset/$code'>http://$_site_url/admin/password_reset/$code</a>
```

> `app/views/mail/password/info.php`

```html
<code>UYARI</code>:
<i class="text-info">
  <ul class="col-sm-offset-1">
    <li>Şifreniz en az 8 karakterden oluşmalıdır</li>
    <li>Büyük, küçük harfler ve rakamların her biri en az 1 defa kullanılmalıdır</li>
    <li>"?, @, !, #, %, +, -, *, %" gibi özel karakterler en az 1 defa kullanılmalıdır</li>
  </ul>
</i>
```

> `app/views/mail/password/notice.php`

```html
<code>BİLDİRİM</code>:
<hr>
Web sayfasında 1 kişi şifre değişikliği talebinde bulundu. <br/>
<b>Tarih :</b> <?= date("Y-m-d H:i:s"); ?>
```

### Configurations (`config/*`)

---

> `config/application.ini` (application configuration file)

```ini
[application_configuration]
debug           = true
timezone        = Europe/Istanbul
locale          = tr
logsize         = 5242880
cacheexpiration = 604800
```

> `config/database.ini` (database configuration file)

```ini
[database_configuration]
host  = localhost
user  = root
pass  = barak
name  = BARAK
```

> `config/mailer.ini` (mailer configuration file)

- Default SMTP Configuration (Test Edildi)

```ini
[mailer_configuration]
port     = 25
address  = mail.gdemir.me
username = mail@gdemir.me
password = 123456
```

- Yandex SMTP Configuration (Test Edildi)

```ini
port     = 587
address  = smtp.yandex.com
username = mail@gdemir.me
password = 123456
```

- Gmail SMTP Configuration (Test Edilmedi, Gmail'in kendi problemi var)

```ini
port     = 465
address  = smtp.gmail.com
username = mail@gdemir.me
password = 123456
```

> `config/locales/LANGUAGE.php` (language configuration file)

Varsayılan dosyası `config/locales/tr.php` dosyasıdır, yeni bir dil eklenecekse aynı list kullanılıp değer kısımları değiştirilerek
kaydedilmelidir. Bu çeviri dosyalarının yönetimi erişimi ve yönetimi için I18n kısmında anlatılmaktadır.

`config/locales/tr.php`

```php
<?php
return [

"home" => [
  "link" => "Anasayfa",
  "about_us" => "Hakkımızda"
  ]

];
?>
```

`config/locales/en.php`

```php
<?php
return [

"home" => [
  "link" => "Homepage",
  "about_us" => "About Us"
  ]

];
?>
```

### Seeds (`db/seeds.php`)

---

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

### I18n (`config/locales/LOCALE.php`)

---

- Functions

> `locale`, `get_locale`, `translate`

#### `locale` ($locale)

Çeviri kelimeleri (`config/locales/tr.php` veya `config/locales/en.php` gibi dosyalar dizi olarak `$_SESSION["_i18n"]` üzerine yüklenir.) proje başlangıcında `config/application.ini` dosyası içerisinde `locale` değişkenine ile atanabilir veya projenin herhangi bir aşamasında aşağıdaki gibi atanabilir/değiştirilebilir. Varsayılan olarak `config/locales/tr.php` dosyası okunur.


```php
ApplicationI18n::locale("tr");
```

#### `get_locale` ()

O an seçili olan dilin hangisi olduğunu anlamak için bu fonksiyon kullanılır.

```php
// Ör. 1:

ApplicationI18n::get_locale();
// tr
```

```php
// Ör. 2:

ApplicationI18n::get_locale();
// en

```

#### `translate` ($path)

Çevirisi yapılacak bir kelime dizini o an hangi dil yüklü ise ona göre çeviri yapmak için aşağıdaki gibi kullanılır.

```php
ApplicationI18n::translate("home.about_us");
```

bu fonksiyonu daha kolay kullanmak için alias olarak tanımlı `t` fonksiyonu ile erişilebilir.

```php
t("home.about_us");
```

### Debug

---

Exception, Error, Shutdown(Fatal Error) akışlarını yakalayıp tek sayfada göstermeye yararayan sınıftır. Eğer hataların gösterilmesi istenmiyorsa `config/application.ini` dosyası içerisinde `debug = false` denilerek kullanıcı bazlı `public/500.html` sayfası gösterilir, ancak log kaydı her şekilde de tutulur.

- Functions

> `exception`, `error`, `shutdown`

#### `exception` (Exception $exception)

```php
throw new Exception("OMG!");
```

veya

```php
ApplicationDebug::exception(new Exception("OMG!"));
```

tarzındaki fonksiyonlar ile hataları bulunduğu sayfada yakalar ve istisnanın bulunduğu kod satırınının bir kısmını gösterir.

#### `error` ($errno, $error, $file, $line)

```php
ApplicationDebug::error(123123, "Undefined variable: a", "/var/www/html/app/controllers/DefaultController.php", 10);
```

veya

```php
echo $a;
```

gibi ifadelerle bir tanımlanmayan değişkenin kullanma hatasını adım adım framework'de hangi dosyalardan hangi satıra kadar olduğunun gösterilmesini sağlar.

#### `shutdown` ()

```
ApplicationDebug::shutdown();
```

veya

ölümcül başka türlü hatalarda (sistemin çalışmadığı durumlarda) sistemin ölümcül hata verdiği kısmı adım adım framework'de hangi dosyalardan hangi satıra kadar olduğunun gösterilmesini sağlar.

### Logger (`tmp/log/*`)

---

Günlük olarak dosyalar açarak verilen mesajları loglamaya yarayan sınıftır.

- Functions

> `size`, `info`, `warning`, `error`, `fatal`, `debug`

#### `size` ($byte = 5242880)

Log dosyasının maximum boyutunun ayarlar, varsayılan olarak boyut `5242880 byte (5 megabyte)` şeklindedir.

```php
ApplicationLogger::size(5000);
```

#### `info`, `warning`, `error`, `fatal`, `debug` ($message)

```php
ApplicationLogger::info("bilmek iyidir");
ApplicationLogger::warning("olabilir?");
ApplicationLogger::error("dikkat et");
ApplicationLogger::fatal("cevap vermiyor");
ApplicationLogger::debug("olaylar olaylar");

// log/2017-06-18.txt
// 2017-06-18 09:45:36 → info : bilmek iyidir
// 2017-06-18 09:45:36 → warning : olabilir?
// 2017-06-18 09:45:36 → error : dikkat et
// 2017-06-18 09:45:36 → fatal : cevap vermiyor
// 2017-06-18 09:45:36 → debug : olaylar olaylar
```

### Cache (`tmp/cache/*`)

---

Verilen anahtarlara göre  `request_url` + `key` (istek url ve verilen anahtar)'e göre md5 ile şifreleyip `tmp/cache/*` dizini üzerinde yazma, okuma, silme, var olduğunu bakma, tamamen silme gibi işlemleri yapan sınıftır.

- Functions

> `expiration`, `write`, `read`, `delete`, `exist`, `reset`

#### `expiration` ($millisecond = 600000)

Saklanacak verilerin genel olarak ne kadar süre ile tutulacağının ayarlar, veriler varsayılan olarak `600000 milisaniye (10 dakika)` süre ile saklanır.

```php
ApplicationLogger::expiration(600000);
```

#### `write` ($key, $value)

Saklanacak verilerin  `request_url` + `key` (istek url ve verilen anahtar)'e göre md5 ile şifreleyip belleğe yazar. Bu şekilde farklı bir sayfada kaydettiğiniz aynı anahtar isimli veriler, farklı dosyalar olarak kaydedilmektedir.

```php
$users = User::all();
ApplicationCache::write("users", $users);
```

#### `read` ($key)

Bellekteki veriyi `request_url` + `key` mantığı ile okur, eğer dosyanın süresi geçmişse otomatik olarak siler.

```php
$users = ApplicationCache::read("users");
```

#### `delete` ($key)

`request_url` + `key` mantığına göre bulunan ve var olan dosya süresine bakılmaksızın silinir.

```php
ApplicationCache::delete("users");
```

#### `exists` ($key)

`request_url` + `key` mantığına göre var olmasına bakar.

```php
echo (ApplicationCache::exists("users")) ? "bellekte var" : "bellekte yok";
```

#### `reset` ()

`tmp/cache/*` altındaki tüm saklanan verileri sürelerine bakılmaksızın siler.

```php
ApplicationCache::reset();
```

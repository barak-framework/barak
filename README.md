# BARAK FRAMEWORK

## What is Barak Framework ?

Barak Framework PHP diliyle yazılmış, açık kaynak kodlu bir web uygulama geliştirme çatısıdır. Web uygulamaları için ihtiyaç duyulabilecek bütün bileşenleri barındıran Barak; MVC (model-view-controller), DRY (don't repeat yourself), CoC (convention over configuration) yaklaşımlarını temel alır. Barak ile aktif hızlı ve kolay RESTful web uygulamaları yapabilirsiniz.

### Requirements

1. Programming Language : `Php >= 5.4`
2. Database Access : `MySQL`
3. Web Server : `Apache`, `Nginx`, `ISS`
4. Package Manager : `Composer`

Barak Framework için gerekli olan paketler yukarıda verilmiştir. Bu paketlerin nasıl kurulacağına ilişkin aşağıdaki bağlantıları inceleyebilirsiniz.

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

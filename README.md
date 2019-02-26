# BARAK FRAMEWORK

## Barak Framework Nedir ?

Barak Framework PHP diliyle yazılmış, açık kaynak kodlu bir web uygulama geliştirme çatısıdır. Web uygulamaları için ihtiyaç duyulabilecek bütün bileşenleri barındıran Barak; MVC (model-view-controller), DRY (don't repeat yourself), CoC (convention over configuration) yaklaşımlarını temel alır. Barak ile aktif hızlı ve kolay RESTful web uygulamaları yapabilirsiniz.

### Gereksinimler

1. Programlama Dili : `Php >= 5.4`
2. Veritabanı Sürücüsü : `PDO`
3. Web Sunucuları :
    1. `Apache`
    2. `Nginx`
    3. `ISS`
4. Paket Yöneticisi : `Composer`

Barak Framework için gerekli olan paketler yukarıda verilmiştir. Bu paketlerin nasıl kurulacağına ilişkin aşağıdaki bağlantıları inceleyebilirsiniz.

> Linux, Apache, MySQL, Php Kurulum : [LAMP](http://gdemir.github.io/categories/linux/lamp/)

> Linux, Nginx, MySQL, Php Kurulum : [LEMP](http://gdemir.github.io/categories/linux/lemp/)

> Web Sunucu Ayarları : [apache2-settings](https://github.com/barak-framework/barak/blob/master/.htaccess.sample) veya [nginx-settings](https://github.com/barak-framework/barak/blob/master/nginx.config.sample) veya [iss-settings](https://github.com/barak-framework/barak/blob/master/web.config.sample)

> Paket Yönetici Kurulumu : [composer-installation](http://gdemir.github.io/categories/php/composer/)

### Barak Kurulum

```sh
composer create-project barak-framework/barak project_name
```

### Çalıştır

    cd project_name
    php -S localhost:9090

and check homepage : [http://localhost:9090](http://localhost:9090) and thats all!

### Versiyonlar

- [https://github.com/barak-framework/barak/releases](https://github.com/barak-framework/barak/releases)

### Lisans

Barak is released under the [MIT License](http://www.opensource.org/licenses/MIT).

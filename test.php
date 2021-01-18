<?php
set_time_limit(0);
?>

composer

https://getcomposer.org/
https://packagist.org/packages/ 软件包列表

作用：主要管理PHP中的依赖关系（类似于Linux yum源）。会自动校验安装包的要求，比如php版本要求。
安装：安装非常简单，只需要选择一个PHP启动程序，直接下一步下一步就能完成。
监测是否安装成功：可以在windows程序与功能里面查看到composer程序。cmd命令行执行composer命令会显示详细信息。

使用：创建一个 composer.json 文件，编写要安装的软件包（包名和版本号）信息，进入 composer.json 文件所在目录下执行 composer install 命令开始安装。
会在 composer.json 所在的同级目录下创建 vendor 文件夹，该文件夹内含有所有的安装包信息。


修改镜像源：速度会很快
    1.composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/
    2.composer config -g repo.packagist composer https://packagist.phpcomposer.com


可以安装软件：
    CURL    # 主要用作微信开发
    Upload  # 文件操作
    excel   # 表格
    mail    # 邮件
    log     # 日志

HHVM：

Laravel Homestead 是一个官方预封装的 Vagrant box，提供给你一个完美的开发环境，你无需在本机电脑上安装 PHP、HHVM、Web 服务器或其它服务器软件。并且不用再担心系统被搞乱！Vagrant box 为你搞定一切。如果有什么地方出错了，你也可以在几分钟内快速的销毁并重建虚拟机！Homestead 可以在 Windows、Mac 或 Linux 系统上面运行，里面包含了 Nginx Web 服务器、PHP 7.0、MySQL、Postgres、Redis、Memcached、Node，以及所有你在使用 Laravel 开发时所需要用到的各种软件。

Vagrant 是一个虚拟机管理软件。提供简单、优雅的方式来管理与配置虚拟机，Homestead 构建于 Vagrant 之上。

Artisan：
    Artisan 是 Laravel 中自带的命令行工具的名称。它提供了一些对您的应用开发有帮助的命令。它是由强大的 Symfony Console 组件驱动的。
    Artisan 工具是放在 laravel 框架的根目录一个php文件,可以用来生成控制器、model、查看路由、创建中间件，网站下线设置等。


    网站下线：php artisan down/up 类似网站显示404错误页面，视图文件：laravel/yzmedu2/resources/views/errors/503.blade.php
    查看路由列表：php artisan route:list

CSRF 的作用：保护表单安全。
    Laravel 提供简单的方法保护你的应用不受到 跨站请求伪造（CSRF）的攻击。跨站请求伪造是一种恶意的攻击，它利用已通过身份验证的用户身份来运行未授权的命令。



Laravel

Laravel 使用 Composer 来管理代码依赖。所以，在使用 Laravel 之前，请先确认你的电脑上安装了 Composer。

系统要求为以下：
    PHP >= 5.6.4
    OpenSSL PHP Extension
    PDO PHP Extension
    Mbstring PHP Extension
    Tokenizer PHP Extension
    XML PHP Extension

安装 Laravel 项目：
1、下载 laravel 安装包：使用 composer 下载 Laravel 安装包：composer global require "laravel/installer"
2、创建项目开发文件夹
    创建最新版本项目（不用安装）：
        方法一：laravel 创建项目 laravel new your-project-name # 该命令可能会报错，提示 522 Origin Connection Time-out 原始链接超时，跨域问题导致
        方法二：composer 创建项目 composer create-project --prefer-dist laravel/laravel your-project-name
    创建指定版本的项目（v5.3）
        composer create-project laravel/laravel your-project-name --prefer-dist "5.3.*"

3、查看 laravel 的版本
    查看 Laravel Installer 版本：laravel -V   # laravel 命令已经加入全局变量path中，可以在任何地方执行
    查看 Laravel Framework 版本：php artisan -V   # 只能在对应的项目的根目录下执行

4、虚拟主机配置
    网站根目录：虚拟主机根目录指向项目下的 public 文件夹
    项目入口：Laravel 入口文件是 public/index.php

5、地址栏访问主机  显示 laravel 则配置成功。


框架文件夹结构：
    app  应用程序的核心代码
    bootstrap 框架启动或自动加载的文件，缓存文件，性能优化文件
    config  应用程序的配置文件

        配置文件：
            .env    是环境配置，通过 env("DB_HOST",'默认值') 来获取
            config/  是系统配置、框架配置，通过 Config("app.timezone") 来获取，可以通过修改配置 Config(['app.timezone' => 'UTC']);
    database 包含数据库迁移与数据填充文件
    public  index.php 程序入库，前端控制器和资源文件（CSS、JS、图片等等）
    resources  界面文件，包含了视图、原始的资源文件，以及语言包
    routes   应用的所有路由定义，默认包含 web.php、api.php、console.php三个文件。
    storage  缓存文件、临时文件。此文件夹分格成 app、framework，及 logs 目录。app 目录可用于存储应用程序使用的任何文件。
            framework 目录被用于保存框架生成的文件及缓存。最后，logs 目录包含了应用程序的日志文件。
    tests  包含自动化测试
    vendor  包含 Composer 的依赖包


项目开发
1、框架连接数据库 /laravel/yzmedu2/.env 文件
2、设置路由 /laravel/yzmedu2/routes/web.php

.env 文件解析：
APP_KEY：应用程序密钥，是设置一个随机字符串的密钥。使用密匙进行加密解密数据，防止别人破解代码。
重新生成密匙：php artisan key:generate
APP_DEBUG：打印错误追溯信息，true|false


路由：
1、直接输出内容
    Route::get('/user', function () {
        echo '123';
    });
2、加载页面（需手动创建 blade.php 视图文件）
    Route::get('home',function(){
        return view('home');
    });
3、加载控制器
    Route::get('user', 'IndexController@index');
    Route::post('check','LoginController@check');
    Route::put('putHandle','LoginController@putHandle');


















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


常用全局命令:
# comooser init           用于创建新项目的composer.json
# composer                打印所有命令
# composer -V             版本号
# composer --version      版本号
# composer self-update    更新composer版本为最新版
# composer show           显示本项目下已经安装的包
# composer info           显示本项目下已经安装的包
# composer clearcache     清理缓存



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
    任何指向 web 中 POST, PUT 或 DELETE 路由的 HTML 表单请求都应该包含一个 CSRF 令牌，否则，这个请求将会被拒绝。



Laravel
Laravel框架的依赖注入确实很强大，并且通过容器实现依赖注入可以有选择性的加载需要的服务，减少初始化框架的开销。

Laravel 使用 Composer 来管理代码依赖。所以，在使用 Laravel 之前，请先确认你的电脑上安装了 Composer。
Laravel 运行还必须要求服务器满足一些硬性配置，PHP拓展要开启。

Laravel 版本：
    最早一个版本是 2011年6月发布的，1.0版本；每年都会发布新版本，当前最新版本是2020年9月8号发布的 Laravel 8.x。
    当前最新稳定的 LTS 版本是 Laravel 6.x，要求 PHP ≥ 7.2.0。LTS版本：是Long Time Support，长期支持的意思。



系统要求为以下（5.3.31）：
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
位置：laravel\yzmedu2\routes
路由请求类型有：get、post、put、patch、delete、options、match、any
路由的作用：百度优先对短链接进行收录，及 SEO 优化

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
    Route::put('putHandle','LoginController@putHandle');// PUT请求

    控制器的书写方式：
        public function putHandle(Request $request){
            dd($request->input());
        }

请求实例：
    $request 通过 服务容器 自动诸如当前的请求实例。含有各种请求获取与判断的方法，比如是GET还是POST方式等。
    控制器或者路由闭包获取请求：要获取请求实例，应该在控制器方法或者路由闭包中使用 Illuminate\Http\Request 类型提示。
    https://learnku.com/docs/laravel/5.3/requests/1164#request-path-and-method



4、一个路由实现多个请求，实现一个接口响应 GET 和 POST 两种方式
    Route::match(['get','post'],'/indexMore','IndexController@index');
    Route::any('/indexAny','IndexController@index');// 响应任何方式的 indexAny 请求


5、资源路由
    利用 names 属性来修改，格式为 动作 => 名词。
    例如 'index' => 'photos.index', 为 PhotosController 里的方法 index ，对应路由命名 'photos.index'。
完整例子如下：
    Route::resource('photos', 'PhotosController', ['names' => [
        'index'   => 'photos.index',
        'create'  => 'photos.create',
        'store'   => 'photos.store',
        'show'    => 'photos.show',
        'edit'    => 'photos.edit',
        'update'  => 'photos.update',
        'destroy' => 'photos.destroy'
    ]]);

6、带参数的路由
    必选路由参数 Route::get('user/{id}', function ($id) {});
    定义多个参数 Route::get('user/{name}/{sex}', function ($name,$sex) {});
    定义多个参数 Route::get('posts/{post}/comments/{comment}', function ($postId, $commentId) {});
    可选参数    Route::get('user/{name?}', function ($name = null) {});# 相应的变量必须有默认值

7、命名路由
    含义：取别名：访问还是原来的名字访问，可以通过 route('别名') 获取原来路由的名字。
    作用：命名路由可以方便的生成 URLs 或者重定向。
    Route::get('abc','IndexController@abc')->name('one');

8、重定向
    通过路由 return redirect('abc');
    通过命名路由（别名）return redirect()->route('one');

9、路由组：路由组（如Home和Admin模块）允许共享路由属性，比如中间件和命名空间等，没必要为每个路由单独设置共有属性。
命名空间配置（Admin模块配置）：
    Route::group([],function(){
        Route::get('admin','Admin\IndexController@index');
        Route::get('admin/user','Admin\UserController@index');
        Route::get('admin/goods','Admin\GoodsController@index');
    });

    // 提取公共 命名空间+前缀
    Route::group(['namespace' => 'Admin','prefix' => 'admin'],function(){
        Route::get('/','IndexController@index');
        Route::get('user','UserController@index');
        Route::get('goods','GoodsController@index');
    });


中间件：过滤进入应用程序的 HTTP 请求
    HTTP 中间件提供了一个方便的机制来过滤进入应用程序的 HTTP 请求，例如，Auth 中间件验证用户的身份，如果用户未通过身份验证，中间件将会把用户导向登录页面，反之，当用户通过了身份验证，中间件将会通过此请求并接着往下执行。当然，除了身份验证之外，中间件也可以被用来运行各式各样的任务，如：CORS 中间件负责替所有即将离开程序的响应加入适当的标头；而日志中间件则可以记录所有传入应用程序的请求。Laravel 框架已经内置了一些中间件，包括维护、身份验证、CSRF 保护，等等。所有的中间件都放在 app/Http/Middleware 目录内。

    创建中间件 php artisan make:middleware adminLogin
    注册中间件 yzmedu2/app/Http/Kernel.php 文件的 $routeMiddleware 中添加配置





10、Blade 模板：
    Blade 模板是 Laravel 提供的一个既简单又强大的模板引擎，与其他流行的PHP模板引擎不一样，Blade 不限制在视图中使用原生 PHP 代码。
所有 Blade 视图文件都将被编译成原生的 PHP 代码并缓存起来，除非它被修改，否则不会重新编译，所以 Blade 基本不会给应用增加任何额外的负担。
Blade 视图文件使用 .blade.php 拓展名，一般存放在 resource/views 目录下。


模板布局：主要解决网站公共部分内容的修改。

创建模板布局文件，模板布局文件通过 @yield("main") 占位符进行占位。
普通页面通过 @extends("layouts.admin") 继承模板文件，通过 @section 命令将内容注入到视图中，@section 区块的内容将会替换 @yield 的区域。
    @yield 占位符：指令是用来显示指定区块的内容的，创建模板布局文件，模板布局文件通过 @yield("main") 占位符进行占位
    @section 替换占位符：命令正如其名字所暗示的一样是用来定义一个视图区块的
    @extends 继承布局模板：命令来为子页面指定其所 「继承」 的页面布局





闪存：Laravel 允许将本次请求的输入数据保留到下次请求提交前，数据保存到 SESSION 中，下次请求填写的数据可以根据 KEY 从 SESSION 获取数据，主要为了避免在表单验证错误后重新填写表单重复填写的麻烦。
$request->flash();# 将输入数据闪存至 Session
$username = $request->old('username');# 获取旧输入数据
old('username')；# Blade 模板中通过 old 全局辅助函数获取







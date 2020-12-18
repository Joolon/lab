<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';


/**
 * Http 服务器
 *
 * 支持路由
 * 相当于一个浏览器访问的 WEB 服务器了。
 *
 */
$http = new Swoole\Http\Server("0.0.0.0", 9503);

$_array = [];

/**
 *
 * $request 参数包含了请求信息，如 GET/POST 请求的数据
 *
 * $response 参数是对 $request 请求的响应
 *
 */
$http->on('request', function($request, $response){
    //var_dump($request->server['request_uri'], $request->get, $request->post);
    $response->header("Content-Type", "text/html; charset=utf-8");

    global $_array;
    //请求 /a（协程 1 ）
    if ($request->server['request_uri'] == '/a') {
        $_array['name'] = 'a';
        co::sleep(3.0);
        echo $_array['name'];
        $response->end($_array['name']);
    }
    //请求 /b（协程 2 ）
    else {
        $_array['name'] = 'b';
        $response->end();
    }

    //$response->end("<h1>Hello Swoole. #".rand(1000, 9999)."</h1>");

//    while(1){
//        file_put_contents(dirname(__FILE__).'/log.txt',date('Y-m-d H:i:s').PHP_EOL,FILE_APPEND);
//    }

    /*

    // 可以用来执行 执行的路由
    list($controller, $action) = explode('/', trim($request->server['request_uri'], '/'));
    //根据 $controller, $action 映射到不同的控制器类和方法
    (new $controller)->$action($request, $response);

    */
});

$http->start();


/*

客户端访问
可以打开浏览器，访问 http://127.0.0.1:9501   远程访问 http://47.107.183.46:9501

*/

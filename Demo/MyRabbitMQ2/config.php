<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/31 0031
 * Time: 11:44
 */

return [
    //配置
    'host'     => [
        'host'     => '127.0.0.1',
        'port'     => '5672',
        'user'     => 'admin',
        'password' => 'admin',
        'vhost'    => 'testhost',
    ],
    //交换机
    'exchange' => 'amq.direct',
    //路由
    'routes'   => [],
];
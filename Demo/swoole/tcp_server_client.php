<?php

include_once dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'Conf/constants.php';

$data = [
    'st_action' => 'get',
    'st_key' => 'aaa3',
    'fd' => '13',
    'reactor_id' => '2',
    'data' => '1212'
];

$client = new Swoole\Client(SWOOLE_SOCK_TCP);
if (!$client->connect('127.0.0.1', 9501, -1)) {
    exit("connect failed. Error: {$client->errCode}\n");
}
$client->send(json_encode($data));
var_dump($client->recv());
$client->close();

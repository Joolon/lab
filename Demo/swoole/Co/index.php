<?php

require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Demo01.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Demo02.php';

//$demo01 = new \Demo\swoole\Co\Demo01();
//$demo01->cookByCo();

$demo01 = new \Demo\swoole\Co\Demo02();
$demo01->cookByCo();

echo PHP_EOL . 'sss';exit;


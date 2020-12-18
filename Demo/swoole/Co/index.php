<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'Conf'.DIRECTORY_SEPARATOR.'constants.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Demo01.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Demo02.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Demo03_Http_Server.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Demo03_Redis_Pool.php';
require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Demo03_Timer.php';



//$demo01 = new \Demo\swoole\Co\Demo01();
//$demo01 = new \Demo\swoole\Co\Demo02();
//$demo01 = new \Demo\swoole\Co\Demo03_Http_Server();
//$demo01 = new \Demo\swoole\Co\Demo03_Redis_Pool();
$demo01 = new \Demo\swoole\Co\Demo03_Timer();
$demo01->cookByCo();


echo PHP_EOL . '程序结束' ."\n\n";

exit;


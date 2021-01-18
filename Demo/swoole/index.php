<?php
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'Conf' . DIRECTORY_SEPARATOR . 'constants.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Co/Demo01.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Co/Demo02.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Co/Demo04_Http_Server.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Co/Demo03_Redis_Pool.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Co/Demo05_Timer.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Co/Demo06_Swoole_Table.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Process/Demo01_Process.php';
require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Process/Demo01_Process_Co.php';



$demo01 = new \Demo\swoole\Co\Demo01();
//$demo01 = new \Demo\swoole\Co\Demo02();
//$demo01 = new \Demo\swoole\Co\Demo04_Http_Server();
//$demo01 = new \Demo\swoole\Co\Demo03_Redis_Pool();
//$demo01 = new \Demo\swoole\Co\Demo05_Timer();
//$demo01 = new \Demo\swoole\Co\Demo06_Swoole_Table();
//$demo01 = new \Demo\swoole\Process\Demo01_Process();
//$demo01 = new \Demo\swoole\Process\Demo01_Process_Co();


$demo01->cookByCo();


echo PHP_EOL . '程序结束' ."\n\n";

exit;


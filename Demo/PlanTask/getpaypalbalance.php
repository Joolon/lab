<?php

include 'taskbase.php';
require_once BASE_PATH."include/config.php";
require_once BASE_PATH.'include/paypalCallerService.php';
require_once BASE_PATH."include/functions.php";

$startime	= time();
$uptSystemTask = "update system_task set taskstatus=1,taskstarttime=".time().",RunTime=0 where ID=80 ";
$dbcon->execute($uptSystemTask);

getPaypalBalance('ALL','UPT');

$usetime	= (time()-$startime)/60;
$uptSystemTask = "update system_task set Runtime=".time().",UseTime=".$usetime.",taskstatus=0 where ID=80 ";
$dbcon->execute($uptSystemTask);
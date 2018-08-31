<?php
@session_start();
error_reporting(0);
set_time_limit(0);
$truename = $user = $_SESSION['user'] = $userFrode = 'otw';
include 'taskbase.php';
include_once BASE_PATH."include/config.php";
include_once BASE_PATH.'class/InventoryReportHelp.class.php';

$reportHelp = new InventoryReportHelp();
$reportHelp->createPeriodBeginStock();

echo 'Success';
exit;





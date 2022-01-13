<?php

include '../taskbase.php';
include_once BASE_PATH."include/config.php";
include_once BASE_PATH."include/tools/timefunction.php";
include_once BASE_PATH."include/tools/arrayfunction.php";
include_once BASE_PATH."Help/DB.class.php";

include_once BASE_PATH.'class/PurchaseOrder.class.php';
include_once BASE_PATH.'class/1688/AliDealHelp.class.php';
include_once BASE_PATH.'class/1688/AliAccount.class.php';
include_once BASE_PATH.'class/1688/AliOrderApi.class.php';
include_once BASE_PATH.'class/1688/AliGoodsApi.class.php';


AliAccount::setAccount('A1688-hohan2018');



// 同步ALI 1688 采购订单
include_once '1688syncOrder.php';

// 同步 ALI 1688 采购订单物流单号
include_once '1688syncOrderLogistics.php';
exit;

// 同步1688产品
include_once '1688syncProduct.php';














<?php

// 采购计划公共函数


// 特殊处理的SKU
// 1.根据关键字 匹配设定采购天数
$specialPurDaysKeys = array(
    'scrapbooking' => array('goodDay' => 15,'purDay' => 15),// 关键字 => 采购天数
);

/**
 * 根据SKU的最早销售日期和日均销量获得SKU的预警天数和采购天数
 * @param string $earlyDay		最早销售日期
 * @param string $qty0			日均销量
 * @return array
 */
function getGoodsAndPurDays($earlyDay,$qty0){
    if($earlyDay < 30){ //增加备货（最早销售日期小于30天）
        if($qty0 < 0.15){
            $goods_days		= 1;
            $purchasedays	= 3;
        }else if($qty0 > 0.35){
            $goods_days		= 3;
            $purchasedays	= 7;
        }else{
            $goods_days		= 2;
            $purchasedays	= 5;
        }
    }else{ //增加备货（最早销售大于30天的SKU）
        if($qty0 < 0.15){
            $goods_days		= 0;
            $purchasedays	= 3;
        }else if($qty0 >= 1){
            $goods_days		= 7;
            $purchasedays	= 20;
        }else if($qty0 > 0.35){
            $goods_days		= 5;
            $purchasedays	= 14;
        }else{
            $goods_days		= 3;
            $purchasedays	= 7;
        }
    }

    return array($goods_days,$purchasedays);
}

/**
 * 指定的特殊的SKU的预警天数和采购天数从库存资料中读取
 * @param $specialPurDaysKeys
 * @param $skuInfo
 * @return array
 */
function specialKeysFlag($specialPurDaysKeys,$skuInfo){
    $flag 	= false;
    $goodDay = $purDay = 0;

    foreach($specialPurDaysKeys as $keys => $value){
        // 判断 SKU信息是否在关键字中
        if(stristr($skuInfo['goods_sn'],$keys) OR stristr($skuInfo['goods_name'],$keys)
            OR stristr($skuInfo['goods_unit'],$keys) ){
            $flag 		= true;
            $goodDay 	= $value['goodDay'];
            $purDay 	= $value['purDay'];
            break;
        }
    }
    return array('flag' => $flag,'goodDay' => $goodDay,'purDay' => $purDay);
}

/**
 * 计算指定产品 指定仓库的最近7天、15天、30天的销售数量
 * @param $goods_sn
 * @param $storeid
 * @return array
 */
function calculateSaleNum($goods_sn,$storeid){

    $start1						= date('Y-m-d').'23:59:59';
    $start0						= date('Y-m-d',strtotime("$start1 -7 days")).' 00:00:00';
    $qty7						= getProductsqty($start0,$start1,$goods_sn,$storeid);			//7天销售数量
    $start1						= date('Y-m-d').'23:59:59';
    $start0						= date('Y-m-d',strtotime("$start1 -15 days")).' 00:00:00';
    $qty15						= getProductsqty($start0,$start1,$goods_sn,$storeid);
    $start1						= date('Y-m-d').'23:59:59';
    $start0						= date('Y-m-d',strtotime("$start1 -30 days")).' 00:00:00';
    $qty30						= getProductsqty($start0,$start1,$goods_sn,$storeid);

    return array(
        'qty7' 	=> $qty7,
        'qty15' => $qty15,
        'qty30' => $qty30
    );
}

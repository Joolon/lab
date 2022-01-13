<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Help/DB.class.php';

/**
 * Class GoodsPurchaseHelp
 * 产品 销售订单需求数、采购单在途数统计
 * @author:zwl
 */
class GoodsPurchaseHelp
{

    /**
     * 获取SKU的在途库存数
     * @param $goods_sn
     * @param int $warehouse_id
     * @return int
     */
    public static function getStockBookUsed($goods_sn,$warehouse_id = 0){

        $sqlCount = "SELECT goods_sn,sum(b.goods_count-b.goods_count0) as qty FROM ebay_iostore AS a 
                JOIN ebay_iostoredetail AS b ON a.io_ordersn=b.io_ordersn	 
                WHERE (a.io_status ='0' or a.io_status ='1' or a.io_status ='3' or a.io_status ='4' or a.io_status ='5') 
                and type ='2' and goods_sn='$goods_sn' ";

        if($warehouse_id){
            $sqlCount .= " AND a.io_warehouse='$warehouse_id' ";
        }

        $sqlCount = DB::QuerySQL($sqlCount);
        if($sqlCount){
            $count = $sqlCount[0]['qty'];
            return $count;
        }else{
            return 0;
        }
    }




}


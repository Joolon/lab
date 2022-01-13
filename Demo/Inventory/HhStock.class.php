<?php
include_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Help/DB.class.php';


include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'IOBase.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Goods.class.php';
include_once dirname(__FILE__).DIRECTORY_SEPARATOR.'StockLog.class.php';

/**
 * 仓库仓位库存数操作类
 * Class HhStock
 * @date 2017-12-19
 */

class HhStock extends IOBase
{

    /**
     * 入库
     *  （若未传入入库仓位，出库成功后可以调用 getSuccess()获取实际入库仓位）
     * @param int $warehouse 仓库
     * @param string $sku SKU
     * @param int $quantity 入库数量（大于0）
     * @param string $storage_sn 入库仓位
     * @return bool
     */
    public static function warehouseEntry($warehouse,$sku, $quantity,$storage_sn = '') {
        $quantity = intval($quantity);

        if(empty($warehouse) OR empty($sku) OR empty($quantity)){
            self::$error = '仓库|SKU|数量缺失';
            $notes = "更新库存失败:仓库[{$warehouse}]SKU[{$sku}]数量[{$quantity}]仓位[{$storage_sn}][ERROR:".self::$error."]";
            StockLog::addOperationLog(11,$sku,$notes);
            return false;
        }

        if(empty($storage_sn)){
            $localList = Goods::getLocationList($warehouse,$sku);
            if(empty($localList) OR empty($localList[0]['storage_sn'])){
                self::$error = '该仓库和SKU下未找到可用仓位';
                $notes = "更新库存失败:仓库[{$warehouse}].']SKU[{$sku}]数量[{$quantity}]仓位[{$storage_sn}][ERROR:".self::$error."]";
                StockLog::addOperationLog(11,$sku,$notes);
                return false;
            }
            $storage_sn = $localList[0]['storage_sn'];
            self::$success = $storage_sn;
        }

        $res = self::updateQuantity($warehouse,$sku, $quantity,$storage_sn);

        return $res;
    }


    /**
     * 出库
     * （若未传入出库仓位，出库成功后可以调用 getSuccess()获取实际出库仓位）
     * @param int $warehouse 仓库
     * @param string $sku SKU
     * @param int $quantity 出库数量（大于0）
     * @param string $storage_sn 出库仓位
     * @return bool
     */
    public static function warehouseOut($warehouse,$sku, $quantity,$storage_sn = '') {
        $quantity = intval($quantity);

        if(empty($warehouse) OR empty($sku) OR empty($quantity)){
            self::$error = '仓库|SKU|数量缺失';
            $notes = "更新库存失败:仓库[{$warehouse}]SKU[{$sku}]数量[{$quantity}]仓位[{$storage_sn}][ERROR:".self::$error."]";
            StockLog::addOperationLog(12,$sku,$notes);
            return false;
        }

        if(empty($storage_sn)){// 自动获取仓位
            $localList = Goods::getLocationList($warehouse,$sku);
            if(empty($localList)){
                self::$error = '该仓库和SKU下未找到可用仓位';
                $notes = "更新库存失败:仓库[{$warehouse}].']SKU[{$sku}]数量[{$quantity}]仓位[{$storage_sn}][ERROR:".self::$error."]";
                StockLog::addOperationLog(11,$sku,$notes);
                return false;
            }
            $storage_sn = $localList[0]['storage_sn'];
            self::$success = $storage_sn;
        }

//        print_r($storage_sn);exit;
        $res = self::updateQuantity($warehouse,$sku, - $quantity,$storage_sn);

        return $res;
    }


    /**
     * 更新库存数（实际数量）
     *
     * @param int       $warehouse  仓库
     * @param string    $sku   SKU
     * @param int       $quantity 实际库存
     * @param string    $storage_sn  仓位
     * @return bool
     */
    public static function updateQuantity($warehouse,$sku,$quantity,$storage_sn){
        $quantity = intval($quantity);

        $date = date('Y-m-d H:i:s');
        if($quantity >= 0){// 最后出入库时间
            $set_sub = " ,in_time='$date'";
        }else{
            $set_sub = " ,out_time='$date'";
        }

        $sqlUpdate = "UPDATE ebay_storage_sku SET amount=amount+{$quantity} {$set_sub} 
                    WHERE store_id='$warehouse' AND sku='$sku' AND storage_sn='$storage_sn' LIMIT 1";


        if(DB::QuerySQL($sqlUpdate)){
//            echo 1;exit;
            if(self::getTotalStock($warehouse,$sku) === false){// 不存在总库存记录则插入
                $add = array();
                $goodsInfo = Goods::getGoodsInfo($sku);
                $add['goods_id']    = $goodsInfo['goods_id'];
                $add['goods_sn']    = $goodsInfo['goods_sn'];
                $add['goods_name']  = $goodsInfo['goods_name'];
                $add['goods_count'] = $quantity;
                if($quantity > 0){// 出入库数量汇总
                    $add['all_in']      = $quantity;
                    $add['period_in']   = $quantity;
                }else{
                    $add['all_out']     = $quantity;
                    $add['period_out']  = $quantity;
                }
                $add['store_id']    = $warehouse;
                if( Goods::addSkuToOnhandle($add) ){
                    return true;
                }else{
                    StockLog::addOperationLog(13,$sku,'插入Onhandle失败'.json_encode($add));
                    self::$error = '库存总数插入失败(系统错误)';
                    return false;
                }
            }
            // 出入数量汇总
            if($quantity > 0){
                $sqlUpdateTotal = "UPDATE ebay_onhandle SET goods_count=goods_count+{$quantity},
                                        all_in=all_in+{$quantity},period_in=period_in+{$quantity}
                                    WHERE store_id='$warehouse' AND goods_sn='$sku' LIMIT 1 ";
            }else{
                $sqlUpdateTotal = "UPDATE ebay_onhandle SET goods_count=goods_count+{$quantity},
                                        all_out=all_out+{$quantity},period_out=period_out+{$quantity}
                                    WHERE store_id='$warehouse' AND goods_sn='$sku' LIMIT 1 ";
            }
            if(! DB::QuerySQL($sqlUpdateTotal)){// 更新失败
                StockLog::addOperationLog(13,$sku,$sqlUpdateTotal);
            }

            return true;
        }else{
            $type = ($quantity>=0)?11:12;// 操作类型 11.SKU入库,12.SKU出库
            StockLog::addOperationLog($type,$sku,$sqlUpdate);
            self::$error = '库存数更新失败[ERROR:系统错误]';
            return false;
        }

    }

    /**
     * 设置 仓库、SKU、仓位的 库存数
     *
     * @param int       $warehouse  仓库
     * @param string    $sku        SKU
     * @param int       $quantity   实际库存数
     * @param string    $storage_sn   仓位
     * @return bool
     */
    public function setWareStock($warehouse,$sku, $quantity,$storage_sn) {
        $quantity = intval($quantity);

        if(empty($warehouse) OR empty($sku) OR empty($storage_sn)){
            self::$error = '仓库|SKU|仓位缺失';
            return false;
        }

        $sqlUpdate = "UPDATE ebay_storage_sku SET amount={$quantity} 
              WHERE store_id='$warehouse' AND sku='$sku' AND storage_sn='$storage_sn' LIMIT 1";

        if(DB::QuerySQL($sqlUpdate)){
            $totalQuantity = self::calculTotalStock($warehouse,$sku);// 统计总库存
            self::setTotalStock($warehouse,$sku,$totalQuantity);// 设置总库存
            return true;
        }else{
            StockLog::addOperationLog(11,$sku,$sqlUpdate);
            self::$error = '仓位库存数更新失败(系统错误)';
            return false;
        }

    }


    /**
     * 设置 总库存数
     * @param int       $warehouse  仓库
     * @param string    $sku        SKU
     * @param int       $quantity   实际库存数
     * @return bool
     */
    public function setTotalStock($warehouse,$sku, $quantity) {
        $quantity = intval($quantity);

        if(empty($warehouse) OR empty($sku) OR empty($quantity) ){
            self::$error = '仓库|SKU|数量缺失';
            return false;
        }
        if(self::getTotalStock($warehouse,$sku) === false){// 不存在总库存记录则插入
            $add = array();
            $goodsInfo = Goods::getGoodsInfo($sku);
            $add['goods_id']    = $goodsInfo['gods_id'];
            $add['goods_sn']    = $goodsInfo['goods_sn'];
            $add['goods_name']  = $goodsInfo['goods_name'];
            $add['goods_count'] = $quantity;
            $add['store_id']    = $warehouse;

            if( Goods::addSkuToOnhandle($add) ){
                return true;
            }else{
                StockLog::addOperationLog(13,$sku,'插入Onhandle失败'.json_encode($add));
                self::$error = '库存总数插入失败(系统错误)';
                return false;
            }
        }

        $sqlUpdateTotal = "UPDATE ebay_onhandle SET goods_count={$quantity} WHERE store_id='$warehouse' AND sku='$sku' LIMIT 1 ";
        if(DB::QuerySQL($sqlUpdateTotal)){
            return true;
        }else{
            self::$error = '库存总数更新失败[ERROR:系统错误]';
            StockLog::addOperationLog(13,$sku,$sqlUpdateTotal);
            return false;
        }

    }

    /**
     * 获取 指定仓库、SKU、仓位 的库存数
     *
     * @param int       $warehouse  仓库
     * @param string    $sku        SKU
     * @param string    $storage_sn   仓位
     * @return bool
     */
    public function getWareStock($warehouse,$sku,$storage_sn) {
        $count = DB::Find('ebay_storage_sku'," store_id='$warehouse' AND sku='$sku' AND storage_sn='$storage_sn'",'amount');

        if(!$count['amount']) return 0;
        return intval($count['amount']);
    }

    /**
     * 获取 指定仓库下 总库存数
     * @param int $warehouse 仓库ID
     * @param string $sku 产品编码
     * @return int
     */
    public static function getTotalStock($warehouse,$sku){
        $count = DB::Find('ebay_onhandle'," store_id='$warehouse' AND goods_sn='$sku' ",'goods_count');

        if(!isset($count['goods_count'])) return false;

        if(!$count['goods_count']) return 0;
        return intval($count['goods_count']);

    }

    /**
     * 获取 所有仓库的 总库存数
     * @param string $sku 产品编码
     * @return int
     */
    public static function getAllStoreTotalStock($sku){
        $count = DB::Find('ebay_onhandle'," goods_sn='$sku' ",'sum(goods_count) as goods_count');

        if(!isset($count['goods_count'])) return false;

        if(!$count['goods_count']) return 0;
        return intval($count['goods_count']);

    }

    /**
     * 根据 仓位库存计算总库存数
     * @param int       $warehouse  仓库
     * @param string    $sku        SKU
     * @return int
     */
    public static function calculTotalStock($warehouse,$sku){
        $count = DB::Find('ebay_storage_sku'," store_id='$warehouse' AND sku='$sku' ",'SUM(amount) AS amount');
        $count = isset($count['amount'])?$count['amount']:0;
		return $count|0;
    }

    /**
     * 查询仓位下的SKU列表
     * @param array $condition
     *  $condition = array(
     * 'store_id' => '仓库ID'
     * 'storage_id' => '仓位ID',
     * 'storage_sn' => '仓位编码',
     * 'sku' => 'SKU',
     * )
     * @return bool|array
     */
    public static function getGoodsListByStorage($condition){
        $where = '1';
        if(isset($condition['store_id']))
            $where .= " AND store_id='".$condition['store_id']."'";
        if(isset($condition['storage_id']))
            $where .= " AND storage_id='".$condition['storage_id']."'";
        if(isset($condition['storage_sn']))
            $where .= " AND storage_sn='".$condition['storage_sn']."'";
        if(isset($condition['sku']))
            $where .= " AND sku='".$condition['sku']."'";

        if(!empty($where) AND $where != '1'){
            $goodsList = DB::Select('ebay_storage_sku',$where);
            return $goodsList;
        }else{
            self::$error = '缺少查询条件';
            return false;
        }

    }


}
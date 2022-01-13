<?php 
/*
*@name 库存分配类
*@add time 2018-04-26 tian
*@add user tian
*/
include_once dirname(dirname(dirname(__FILE__))). '/include/dbconnect.php';
if(empty($dbcon)){
	$dbcon = new DBClass();	
}
include_once dirname(dirname(dirname(__FILE__))).'/Help/DB.class.php';
include_once dirname(dirname(dirname(__FILE__))).'/class/PublicClass/LogOper.class.php';
class KcAllocation{
	//库存分配
	public static function kcHandle($ebay_id, $allot_type = 0){
		global $truename;
		$old_time = time()-(15*86400);
		$order = self::getOrderInfo($ebay_id);
		//确单时间超过15天未出库作废
		if(($order[0]['ebay_confirmtime']<$old_time) && in_array($order[0]['ebay_status'],array(230,232)) && $order[0]['ebay_ordertype']!='复制订单'){
			$note = '【库存分配new】：确单时间超过15天,订单作废';
			DB::Update("ebay_order", array('ebay_status' => 236), "ebay_id=$ebay_id");
			LogOper::updateOrderLogs($ebay_id,$note,27);
			return false;
		}
		if(!$order){
			$note = '库存分配失败：查询不到订单';
			LogOper::updateOrderLogs($ebay_id, "【库存分配new】:".$note,25);
			return false;
		}
		
		$ebay_status = $order[0]['ebay_status'];
		$usestore = $order[0]['usestore'];
		$ebay_warehouse = $order[0]['ebay_warehouse'];
		if (!in_array($ebay_status, array(230, 232))) {
            $note = "【库存分配new】：订单状态不是待打印，待扫描";
			LogOper::updateOrderLogs($ebay_id, "库存分配失败---".$note,25);
            return false;
        }
		if(in_array($usestore,array(1,11))){
			$note = '【库存分配new】：已经分配过库存';
			LogOper::updateOrderLogs($ebay_id, "库存分配失败---".$note,25);
			return false;
		}
		$fpRs = true;
		$cwInfos = array();//分配的结果数据（拣货单数据）
		$invalidArr = array();//被占用库存
		//---------------------------------分配库存 begin-----------------------------
		foreach($order AS $v){
			if(!$v['sku']){
				$note = '无子订单信息';
				$fpRs = false;
				break; 
			}
			//获取总库存信息
			$totalStockInfo = self::getTotalStock($v['sku'],$ebay_warehouse);
			if(!$totalStockInfo){
				$note = '该sku【'.$v['sku'].'】无仓位';
				$fpRs = false;
				break;
			}
			//需求数
			$ebay_amount = $v['ebay_amount'];
			//获取sku已占用库存
			$invalid_amount = self::getInvalidStock($v['sku'],$ebay_warehouse);
			if(isset($invalidArr[$v['sku']])){
				$invalid_amount += $invalidArr[$v['sku']];
			}else{
				$invalidArr[$v['sku']] = 0;
			}
			//总需
			$total_invalid_amount = $ebay_amount + $invalid_amount;
			if($total_invalid_amount>$totalStockInfo['total_amount']){
				$note = '该sku【'.$v['sku'].'】库存不足';
				$fpRs = false;
				break;
			}
			//开始分配仓位中的库存
			foreach($totalStockInfo AS $c){//如要很精准则需考虑在分配过程中,总库存被订单出库扣除的情况(并发)
				$cwInfo = array();
				$cwInfo['ebay_id']=$ebay_id;
				$cwInfo['sub_ebay_id']=$v['tid'];
				$cwInfo['sku']=$v['sku'];
				$cwInfo['allot_type']=$allot_type;
				$cwInfo['store_id']=$ebay_warehouse;
				$cwInfo['storage_sn']=$c['storage_sn'];
				$cwInfo['storage_number']=$c['amount'];
				if($c['amount']>=$ebay_amount){//本仓位库存足够
					$cwInfo['real_number']=$ebay_amount;
					$cwInfo['allot_number']=$ebay_amount;
					$cwInfos[]=$cwInfo;
					break;
				}else{
					$cwInfo['real_number']=$c['amount'];
					$cwInfo['allot_number']=$c['amount'];
					$ebay_amount = $ebay_amount - $c['amount'];
					$cwInfos[]=$cwInfo;
				}
				
			}
			//每次被分配后的需求数 
			$invalidArr[$v['sku']] +=  $v['ebay_amount'];
		}
		//---------------------------------分配库存 end-------------------------------
		if ($fpRs) {//所有子单都能分配到库存
			DB::Delete("order_allot_storage", "ebay_id=$ebay_id");//先删掉旧数据
			foreach($cwInfos AS $cw){
				self::AddAllotInfo($cw['ebay_id'], $cw['sub_ebay_id'], $cw['sku'], $cw['real_number'], $cw['allot_number'], $ebay_warehouse, $cw['storage_sn'], $cw['storage_number'], $allot_type);
			}
            $usestore = $usestore + 1;
            DB::Update("ebay_order", array('usestore' => $usestore), "ebay_id=$ebay_id");
            LogOper::updateOrderLogs($ebay_id, "【库存分配new】：分配成功",26);
            return true;
        } else {
			// if(in_array($order['ebay_ordertype'],array('EBAY订单-UK','EBAY订单-US','EBAY订单-AU')) && $ebay_warehouse==36){//EBAY海外仓产品库存不足的 与坂田仓互转
				// $ckRe = self::ckToCk($ebay_id,$ebay_warehouse);
				// self::$Error = '海外仓库存不足,订单转为'.$ckRe;
			// }
            LogOper::updateOrderLogs($ebay_id, "【库存分配new】：分配失败---".$note,25);
            return false;
        }
		
	}
	/**
     * 获取已经分配的总库存
     * @param type $sku
     * @param type $ebay_warehouse
     */
    private static function getInvalidStock($sku, $ebay_warehouse) {
        $sql = "select sum(real_number) as invalid_amount from order_allot_storage where store_id=$ebay_warehouse and sku='$sku' and ebay_id in (select ebay_id from ebay_order where ebay_status in (232,230) AND usestore IN(1,11))";
        $list = DB::QuerySQL($sql);
		if(!$list){
			return 0;
		}
		$invalid_amount = 0;
		foreach($list AS $v){
			$invalid_amount +=$v['invalid_amount'];
		}
        return $invalid_amount;
    }
	//订单信息
	private static function getOrderInfo($ebay_id){
		$where = ' a.ebay_id='.$ebay_id;
		$table = ' ebay_order AS a INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn = b.ebay_ordersn ';
		$fields = ' a.ebay_id,a.ebay_status,a.usestore,a.ebay_warehouse,a.ebay_confirmtime,a.ebay_ordertype,b.ebay_id AS tid,b.sku,b.ebay_amount ';
		return DB::Select($table,$where,$fields);
	}
	//获取总库存   ***特别注意，多仓的统计 总库存   
	public static function getTotalStock($sku,$warehouse){
        $count = DB::Select('ebay_storage_sku'," store_id='$warehouse' AND sku='$sku' GROUP BY storage_sn ORDER BY amount DESC ",'SUM(amount) AS amount,storage_sn');
		$total_amount = 0;
		foreach($count AS $v){
			$total_amount += $v['amount'];
		}
		$count['total_amount'] = $total_amount;
		return $count;
    }
	/**
     * 添加分配记录（拣货单数据）
     */
    private static function AddAllotInfo($ebay_id, $sub_ebay_id, $sku, $real_number, $allot_number, $store_id, $storage_sn, $storage_number, $allot_type) {
        $data = array(
            'ebay_id' => $ebay_id,
            'sub_ebay_id' => $sub_ebay_id,
            'sku' => $sku,
            'real_number' => $real_number,
            'allot_number' => $allot_number,
            'allot_type' => $allot_type,
            'store_id' => $store_id,
            'storage_sn' => $storage_sn,
            'storage_number' => $storage_number
        );
        DB::Add("order_allot_storage", $data);
    }
	//分配仓位库存
	private static function cwHandle($sku,$ebay_warehouse,$ebay_amount){
		$where = ' store_id='.$ebay_warehouse.' AND sku='.$sku;
		$res = DB::Select('ebay_storage_sku',$where,'amount');
		
	}
	//仓库互转
	private static function ckToCk($ebay_id,$ebay_warehouse){
		if($ebay_warehouse==32){//转海外仓
			$arr['ebay_warehouse'] = 36;
		}elseif($ebay_warehouse==36){//转坂田仓
			$arr['ebay_warehouse'] = 32;
		}
		$where = 'ebay_id='.$ebay_id;
		DB::Update('ebay_order',$arr,$where);
		if($ebay_warehouse==32){
			$re = '【坂田海外仓】';
		}elseif($ebay_warehouse==36){
			$re = '【坂田仓库】';
		}
		return $re;
	}
	
}
// $aa=KcAllocation::kcHandle(39278759);
// var_dump($aa);exit;
// exit;
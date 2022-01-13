<?php

/*
 * 2018-3-27 mxc wirte for hhcq v2
 */

/**
 * OrderInProcessForGroupOrders 描述信息:  用于分组订单， 根据传入订单的id 查询出订单，然后根据订单类型查询可用仓库，根据仓库和sku，查询出订单 可用库存列表，然后综合之后对其分组
 *
 * @author 梅小春 <476984957@qq.com>
 */
// error_reporting(E_ALL);
class OrderInProcessForGroupOrders {
	/*
	*@name 多品订单按仓库分组
	*@param  订单id
	*@return array()
	*@addtime 2018-04-02 tian
	*/
	public static function GroupOrders($ebay_id){//
		global $dbcon;
		$return=array('status'=>'success');
		$sql='SELECT b.ebay_id AS sid,a.ebay_ordertype,b.sku FROM ebay_order AS a INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn=b.ebay_ordersn WHERE a.ebay_id='.$ebay_id;
		$sql=$dbcon->query($sql);
		$order=$dbcon->getResultArray($sql);
		unset($sql);
		$orderGroup=array();//子订单按仓库分组
		$detailSkuErr=array();//产品资料不存在对应仓库
		//---------------子订单按仓库分组 begin--------------------
		foreach($order AS $detail){
			$sql='SELECT a.store_id,a.warehouse_en FROM order_belong_warehouse AS a LEFT JOIN ebay_storage_sku AS b ON a.store_id=b.store_id WHERE a.order_type="'.$detail['ebay_ordertype'].'" AND b.sku="'.$detail['sku'].'"';
			$store_list=DB::QuerySQL($sql);
			if(!$store_list){//正常逻辑 找不到产品资料 返回异常放到待处理
				//$detailSkuErr[]=$detail['sid'];
				//暂时修改成 找不到仓库 整个订单归属坂田仓
				$issetCK = 32;
				break;
			}
			foreach($store_list AS $store_id){
				$orderGroup[$store_id['store_id']][]=$detail['sid'];
			}
		}
		//---------------子订单按仓库分组 end--------------------
		if($detailSkuErr){//有子订单sku不存在对应仓库
			$return['status'] = 'error';
			$return['content'] = $detailSkuErr;
			return $return;
		}
		if(count($orderGroup)==1 || $issetCK==32){//不需要拆单
			$return['content']=$store_list[0]['store_id'];
			if($issetCK==32){
				$return['content']=$issetCK;
			}
			return $return;
		}		
		//---------------处理同仓库多地域的订单 begin--------------------
		arsort($orderGroup);
		$warehouseGroup=array();
		$tempFile = array();
		foreach($orderGroup AS $k=>$v){
			foreach($v AS $w){
				if(in_array($w,$tempFile)){
					continue;
				}else{
					$warehouseGroup[$k][] = $w;
					$tempFile[] = $w;
				}
			}
		}
		asort($warehouseGroup);
		if(count($warehouseGroup)>1){//需要拆单
			$return['content'] = $warehouseGroup;
		}else{
			$aa = array_keys($warehouseGroup);
			$aa = $aa[0];
			$return['content'] = $aa;
		}
		return $return;
	}
    //put your code here

    public static function GroupOrders1($ebay_id) {
        $order = DB::Find("ebay_order", "ebay_id=$ebay_id");
        $ordertype = $order['ebay_ordertype'];
        $settings = DB::QuerySQL("select * from order_belong_warehouse where order_type='$ordertype'");
        //按照区域分组
        $warehouse_en = array();
        foreach ($settings as $row) {
            $warehouse_en[$row['store_id']] = $row['warehouse_en'];
        }
        $orderdetails = DB::QuerySQL("select ebay_id,sku,ebay_amount from ebay_orderdetail where ebay_ordersn='{$order['ebay_ordersn']}'");

        $skuArr = array();
        foreach ($orderdetails as $row) {
            if (!in_array($row['sku'], $skuArr)) {
                $skuArr[] = $row['sku'];
            }
        }
        $arrList = array(); //根据仓库，查出每个sku所具有的 库存列表
        foreach ($settings as $row) {
            foreach ($orderdetails as $subOrder) {
                $sku = $subOrder['sku'];
                $store_id = $row['store_id'];
                $list = DB::QuerySQL("select * from ebay_storage_sku where sku='$sku' and store_id='$store_id'");
				foreach($list as $one){
					$arrList[] = $one;
				}
            }
        }
        $countArr = array();
        foreach ($arrList as $row) {
            if (!isset($countArr[$row['store_id']])) {
                $countArr[$row['store_id']][] = $row['sku'];
            }
            if (!in_array($row['sku'], $countArr[$row['store_id']])) { //
                $countArr[$row['store_id']][] = $row['sku'];
            }
        }
        $store_count_arr = array();
        $StoreArr = array();
        foreach ($countArr as $store_id => $arr) {
            $store_count_arr[$store_id] = count($arr); //统计
            if (count($arr) == count($skuArr)) {// 找到可以完全满足的情况
                $StoreArr[$store_id] = $store_id;
            }
        }
        if ($StoreArr) { //存在完全满足的情况
            $real_store_id = null;
            foreach ($StoreArr as $store_id) {
                if (strtoupper($warehouse_en[$store_id]) == 'SZ') {
                    $real_store_id = $store_id;
                    break;
                }
                $real_store_id = $store_id; //不是深圳
            }
            return array(
                $real_store_id => $orderdetails,
            );
        } else {
            $array_intersect = array(); //交集
            $temp = array();
            $array_diff = $skuArr; //差集
            arsort($store_count_arr);
            foreach ($store_count_arr as $store_id => $a) {
                //第一组存在的sku
                $tempSku = $countArr[$store_id];
                $temp = array_intersect($array_diff, $tempSku); // 交集 存在交集，则将此交集分配到当前仓库下
                $array_intersect[$store_id] = $temp;
                $array_diff = array_diff($array_diff, $temp); //求差集
                if (!$array_diff) {//如果不存在差集， 则表示分配完毕
                    break;
                }
            }
			print_r($array_intersect);
            //检测是否分配成功
            $num = 0;
            foreach ($array_intersect as $row) {
                $num +=count($row);
            }
            if ($num != count($skuArr)) { //sku个数不相等，则表示分配失败
                return array(); //分配失败返回空值
            } else {//分配成功则根据对应的sku，分别返回分组
                
                $return = array();
                $hasDispatch = array();
                foreach ($array_intersect as $store_id =>$rowArr) {
                    foreach ($orderdetails as $key => $sub){
                        if(in_array($key, $hasDispatch)){
                            continue;//已经分配过，直接跳过 也可以直接在此处返回错误
                        }
                        if(in_array($sub['sku'], $rowArr)){
                            $return[$store_id] = $sub;
                            $hasDispatch[] = $key;
                        }
                    }
                }
                return $return;
            }
        }
		print_r($return);exit;
    }

}

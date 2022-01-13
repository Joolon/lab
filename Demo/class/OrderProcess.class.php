<?php
include_once dirname(__FILE__)."/publicClass/LogOper.class.php"; 
/*
*@name 订单确定仓库，拆单，分配路由
*@add time 2018-03-22
*@add user tian
*/
class OrderProcess{
	public static $return=array(
			"state"=>"error",
			"msg"=>""
	);

    /**
     * 判断是否为组合订单
     * @param $ebay_id
     */
	public static function checkOrderdetailcount($ebay_id){
        global $dbcon;
        $detail_sql='SELECT a.ebay_total,b.ebay_itemprice,b.ebay_amount FROM ebay_order AS a INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn=b.ebay_ordersn WHERE a.ebay_id='.$ebay_id;
        $detail_sql=$dbcon->query($detail_sql);
        $detail_sql=$dbcon->getResultArray($detail_sql);
        if (count($detail_sql)>1){
            $res = true;
        }else{
            $res = false;
        }
        return $res;
    }
	/*
	*@name 查询需要走订单流程的订单
	*@param $time  订单导入时间节点
	*@return 订单列表
	*@add time 2018-03-22 tian
	*/
	public static function beginOrderProcess($time){
		global $dbcon;
		$mainsql = 'select a.ebay_id,a.ebay_ordersn,a.ebay_userid,a.ebay_usermail,a.ebay_username,a.ebay_street,a.ebay_street1,a.ebay_city,a.ebay_state,a.ebay_couny,a.ebay_countryname,a.ebay_postcode,a.ebay_phone,a.ebay_currency,a.ebay_total,a.ebay_shipfee,a.orderweight,a.ebay_carrier,a.ebay_account,a.ebay_ordertype,a.ebay_tracknumber,a.ebay_note,a.ebay_noteb,a.ordertype,b.ebay_id as ebayid,b.sku,b.ebay_itemprice,b.ebay_amount,b.shipingfee,b.notes,b.listingtype,c.goods_location,c.goods_weight,c.goods_name2,c.isuse from ebay_order as a inner join ebay_orderdetail as b on a.ebay_ordersn=b.ebay_ordersn left join ebay_goods as c on b.sku=c.goods_sn where a.ebay_status=274  and a.ebay_addtime<' . $time . ' and a.ebay_combine!="1" group by b.ebay_id AND a.ebay_id=26058594';
		$mainsql='SELECT * FROM ebay_order WHERE ebay_id=26058594';
		$sql=$dbcon->query($mainsql);
		$sql=$dbcon->getResultArray($sql);
		return $sql;
	}
	/*
	*@name 一些特殊的sku处理
	*@param sku
	*@return 处理后的sku
	*@add time 2018-03-22 tian
	*/
	public static function skuProcess($sku){
		$sku = str_replace("maza-", "", $sku);
		$sku = str_replace("yong-", "", $sku);
		if (strpos($sku, '@') !== false) {
			$sku = substr($sku, strpos($sku, '@') + 1);
		} else {
			$sku = $sku;
		}
		return $sku;
	}
	
	
	/*
	*@name 检查sku是否为别名
	*@param ebay_id  订单号
	*@return sku名称
	*@add time 2018-03-22 tian
	*/
	public static function getSkuName($sku){
		global $dbcon,$truename;
		$sql='SELECT goods_sn from ebay_goods WHERE goods_sn="'.$sku.'" ';
		$sql=$dbcon->query($sql);
		$sql=$dbcon->getResultArray($sql);
		if(count($sql)>0){//存在goods表 直接返回名称
			self::$return['msg']='new_sku';
			self::$return['content']=$sql[0]['goods_sn'];
			return self::$return;
		}
		unset($sql);
		//查询别名表
		$sql='SELECT goods_sn FROM ebay_goods_varsku WHERE varsku="'.$sku.'"';
		$sql=$dbcon->query($sql);
		$sql=$dbcon->getResultArray($sql);
		if(count($sql)>0){//如果是别名则返回sku真实名称
			self::$return['msg']='var_sku';
			self::$return['content']=$sql[0]['goods_sn'];
			return self::$return;
		}else{
			self::$return['msg']='old_sku';
			self::$return['content']=$sku;
			return self::$return;//不存在的sku返回原名,可能是业务胡乱取名
		}
	}
	/*
	*@name 检测是否混合仓订单,自动拆单(U仓与非U仓)
	*@param (多品订单)二维数组
	*@return 新订单集
	*@add time 2018-03-23 tian
	*/
	public static function autoSplitOrder($orderData){
		global $dbcon,$skuWarehouseGroup;
		if(count($orderData)<2){
			return;
		}
		//----------------检查是否有混合仓begin--------------
		$splitArray = array();
        $orderDataC = count($orderData);
		for ($j = 0; $j < $orderDataC; $j++) {//$orderDataC子订单数量
            $goods_location = trim($orderData[$j]["goods_location"]); //仓位
            $warehouseGroup = 'sz'; //初始一个值,用于拆单加标识
            if ($goods_location) {
                $goods_location = strtoupper($goods_location);
                $goods_location = $goods_location{0}; //取仓位首字母
                if (isset($skuWarehouseGroup[$goods_location]))
                    $warehouseGroup = $skuWarehouseGroup[$goods_location]['en']; //不是深圳则覆盖 $warehouseGroup比如=yw
            }
            if (!isset($splitArray[$warehouseGroup])) {
                $splitArray[$warehouseGroup] = array('total' => 0, 'ship' => 0, 'detailIds' => '','weight'=>''); 
            }
            //总金额
            $splitArray[$warehouseGroup]['total'] += $orderData[$j]['ebay_itemprice'] * $orderData[$j]['ebay_amount'] + $orderData[$j]['shipingfee'];
            //总运费
            $splitArray[$warehouseGroup]['ship'] += $orderData[$j]['shipingfee'];
            //子订单集
            $splitArray[$warehouseGroup]['detailIds'] .= ',' . $orderData[$j]["ebayid"];
			//总重量
			$splitArray[$warehouseGroup]['weight'] += $orderData[$j]['ebay_amount'] * $orderData[$j]['goods_weight'];
        }
		//----------------检查是否有混合仓end----------------
		
		//----------------------拆单begin--------------------
		if(count($splitArray)>1){
			ksort($splitArray);
			$j = 0;
            $ebay_order_list[] = $orderData[0]["ebay_id"]; //第一类仓库
			foreach($splitArray AS $warehouse=>$v){
				if($j == 0){//第一类仓库订单
					$dbcon->execute("update ebay_order set ebay_total=" . $y['total'] . ",ebay_shipfee=" . $y['ship'] . ",orderweight=".$y['weight']." where ebay_id=" . $orderData[0]["ebay_id"]);
				}else{
					$newordersn = $orderData[0]['ebay_ordersn'] . '-' . $warehouse;//新订单标识
					$addNewOrderSql = 'insert into ebay_order(ebay_ordersn,orderweight,ebay_total,ebay_shipfee,ebay_user,ebay_status,ebay_username,ebay_street,ebay_street1,ebay_city,ebay_state,ebay_couny,ebay_countryname,ebay_postcode,ebay_phone,ebay_usermail,ebay_userid,ebay_ptid,ebay_currency,ebay_paidtime,ebay_account,ebay_noteb,ebay_carrier,ebay_note,ebay_ordertype,ebay_warehouse,ebay_addtime,ebay_createdtime,ebay_markettime,ebay_paystatus,recordnumber,ebay_orderid) (select "' . $newordersn . '",' . $y['weight'] . ',' . $y['total'] . ',' . $y['ship'] . ',ebay_user,ebay_status,ebay_username,ebay_street,ebay_street1,ebay_city,ebay_state,ebay_couny,ebay_countryname,ebay_postcode,ebay_phone,ebay_usermail,ebay_userid,ebay_ptid,ebay_currency,ebay_paidtime,ebay_account,ebay_noteb,ebay_carrier,ebay_note,ebay_ordertype,ebay_warehouse,ebay_addtime,ebay_createdtime,ebay_markettime,ebay_paystatus,recordnumber,ebay_orderid from ebay_order where ebay_id=' . $orderData[0]['ebay_id'] . ')';
                    $dbcon->execute($addNewOrderSql);
					//反查复制是否成功
					$newOrderSql = $dbcon->query('select ebay_id from ebay_order where ebay_ordersn="' . $newordersn . '"');
                    $newOrderSql = $dbcon->getResultArray($newOrderSql);
					if (count($newOrderSql) == 1) {
                        $ebayPids = substr($y['detailIds'], 1); //拿到子单的ebay_id,并修改此ebay_ordersn
                        $dbcon->execute('update ebay_orderdetail set ebay_ordersn="' . $newordersn . '" where ebay_id in (' . $ebayPids . ')');
                        LogOper::addOrderLogs($newOrderSql[0]['ebay_id'], '此订单是从订单[' . $orderData[0]["ebay_id"] . ']拆出来,拆出子订单为[' . $ebayPids . '],操作人是:系统',17);
                        LogOper::addOrderLogs($orderData[0]["ebay_id"], '子订单[' . $ebayPids . ']被拆出到订单:[' . $newOrderSql[0]['ebay_id'] . '] 操作人是:系统',17);
                        $ebay_order_list[] = $newOrderSql[0]['ebay_id']; //新订单的主表ebay_id
                    }
				}
				$j++;
			}
		}
		//----------------------拆单end--------------------
		return $ebay_order_list;
	}

	/*
	*@name 分发路由(匹配物流渠道)
	*@param (多品订单)二维数组
	*@param1 订单id 
	*@return 新订单集
	*@add time 2018-03-23 tian
	*/
	public static function dispathCarrier($orderData=array(),$ebayId=''){
		global $dbcon, $user, $skuWarehouseGroup;
		if ($ebayId) {
			$mainDataArray = $dbcon->query('select a.ebay_id,a.ebay_ordersn,a.ebay_username,a.ebay_street,a.ebay_street1,a.ebay_city,a.ebay_state,a.ebay_couny,a.ebay_countryname,a.ebay_postcode,a.ebay_phone,a.ebay_currency,a.ebay_total,a.ebay_shipfee,a.orderweight,a.ebay_carrier,a.ebay_account,a.ebay_ordertype,a.ebay_tracknumber,a.ebay_note,a.ebay_noteb,b.ebay_id as ebayid,b.sku,b.ebay_itemprice,b.ebay_amount,b.shipingfee,b.notes,c.goods_location,c.goods_weight,c.goods_name2,c.isuse from ebay_order as a inner join ebay_orderdetail as b on a.ebay_ordersn=b.ebay_ordersn left join ebay_goods as c on b.sku=c.goods_sn where a.ebay_id=' . intval($ebayId) . ' group by b.ebay_id');
			$mainDataArray = $dbcon->getResultArray($mainDataArray);
			if ($mainDataArray) {
				$orderData = array();
				foreach ($mainDataArray as $y) {
					$orderData[] = $y;
				}
			}
			unset($mainDataArray);
		}
		
	}
	
	/*
	*@name 匹配仓库id
	*@param ebay_id 订单id
	*@param store_id 仓库id
	*@return 仓库id
	*@add time 2018-03-28 tian
	*/
	public static function dispathOrderWarehouse($ebay_id,$store_id){
		global $dbcon;
		$sql='UPDATE  ebay_order SET ebay_warehouse="'.$store_id.'" WHERE ebay_id='.$ebay_id;
		if($dbcon->execute($sql)){
			return $store_id;
		}else{
			return '';
		}
	}
	/*
	*@name 根据订单类型与sku锁定仓库id
	*@param sku
	*@parma1 订单类型
	*@return 仓库id
	*@add time 2018-03-26 tian
	*/
	public static function getStoreId($sku,$ebay_ordertype){
		global $dbcon;
		$sql='SELECT a.store_id,a.amount FROM ebay_storage_sku AS a LEFT JOIN order_belong_warehouse AS b on a.store_id=b.store_id  WHERE a.sku="'.$sku.'" AND b.order_type="'.$ebay_ordertype.'"';
		$sql=$dbcon->query($sql);
		$sql=$dbcon->getResultArray($sql);
		$store_list=array();
		if(count($sql)>0){
			foreach($sql AS $v){
				$store_list[$v['store_id']] = $v['amount'];
			}
		}else{//产品资料不存在
			return 0; 
		}
		//多地域仓库默认选择库存多的
		arsort($store_list);
		$store_id=array_keys($store_list);
		$store_id=$store_id[0];
		return $store_id;
	}
	/*
	*@name 获取仓库id与订单类型配置
	*@return 
	*@add time 2018-03-26 tian
	*/
	public static function ordertypeWarehouseGroup(){
		global $dbcon;
		$sql='SELECT store_id,order_type,warehouse_en AS en FROM order_belong_warehouse';
		$sql=$dbcon->query($sql);
		$sql=$dbcon->getResultArray($sql);
		$ordertypeWarehouseGroup=array();
		foreach($sql AS $v){
			$ordertypeWarehouseGroup[$v['order_type']][$v['store_id']]=$v['en'];
		}
		return $ordertypeWarehouseGroup;
	}
	/*
	*@name ALI组合订单有子单超过N
	*@param string $ebay_id 订单编号
	*@add date 2017-08-14 tian
	*/
	public static function checkAliOrderPrice($ebay_id,$n=5){
		global $dbcon;
		$returnStr=false;
		$sql='SELECT a.ebay_total,b.ebay_itemprice,b.ebay_amount FROM ebay_order AS a INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn=b.ebay_ordersn WHERE a.ebay_id='.$ebay_id;
		$sql=$dbcon->query($sql);
		$sql=$dbcon->getResultArray($sql);
		if(count($sql)>1){//组合订单
			$sonOrderPrice='';
			foreach($sql AS $v){
				$sonOrderPrice=$v['ebay_itemprice']*$v['ebay_amount'];
				if($sonOrderPrice>$n){//只要有子订单价格超过n
					$returnStr=true;
					break;
				}
			}
		}else{//非组合订单
			if($sql[0]['ebay_total']>$n){
				$returnStr=true;
			}
		}	
		return $returnStr;
	}
	/*确定产品属性*/
	public static function getgoodsattr($ebay_id) {
		global $dbcon;
		$csql = "SELECT goods_name2
				FROM `ebay_order` a
				LEFT JOIN `ebay_orderdetail` b
				ON a.ebay_ordersn = b.`ebay_ordersn`
				LEFT JOIN `ebay_goods` c
				ON b.sku = c.`goods_sn`
				WHERE 1=1
				AND a.ebay_id = '".$ebay_id."'
		";
		$cquery = $dbcon->execute($csql);
		$cres = $dbcon->getResultArray($cquery);
		$isbattry = '0';
		foreach($cres as $val) {
			if($val['goods_name2'] != '' && $val['goods_name2'] != '普通'&& $val['goods_name2'] != '普货') {
				$isbattry = '1';
				break;
			}
			
		}
		return $isbattry;
	}
	/*
	*@name 分配路由后处理UBI墨西哥专线对包裹限制问题
	*@param string $ebay_id 订单编号
	*@add date 2018-01-23 tian
	*/
	public static function checkUbiPack($ebay_id,$num=10){
		global $dbcon;
		$returnStr=false;//没超过包裹限制
		$sql='SELECT a.ebay_total,b.ebay_amount FROM ebay_order AS a INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn=b.ebay_ordersn WHERE a.ebay_id='.$ebay_id;
		$sql=$dbcon->query($sql);
		$sql=$dbcon->getResultArray($sql);
		foreach($sql AS $v){
			$amount+=$v['ebay_amount'];
		}
		if($amount>$num){
			$returnStr=true;
		}
		return $returnStr;
	}
	//黑名单
	public static function hackpeoples($ebayId) {
		global $dbcon, $user;
		$hp_sql = "select a.ebay_id,a.ebay_ordertype,a.ebay_userid,a.ebay_username,a.ebay_usermail,a.ebay_street,a.ebay_street1,a.ebay_city,
						a.ebay_state,a.ebay_countryname,a.ebay_postcode,a.ebay_phone,ebay_status
				from ebay_order as a 
				where a.ebay_id='$ebayId'
				LIMIT 1";
		$hp_sql = $dbcon->query($hp_sql);
		$orderData = $dbcon->getResultArray($hp_sql);
		if ($orderData && in_array($orderData[0]['ebay_ordertype'],array('EBAY订单','EBAY订单-US','EBAY订单-UK','EBAY订单-AU'))) {
			$sql = "SELECT * FROM ebay_hackpeoles 
					WHERE 1 ";

			$sql_tmp = '';
			if (!empty($orderData[0]['ebay_userid'])) {// 是否验证用户ID（不为空则验证）
				$sql_tmp .= " OR userid = '" . trim($orderData[0]['ebay_userid']) . "' ";
			}
			if (!empty($orderData[0]['ebay_usermail'])) {// 是否验证用户邮箱（不为空则验证）
				$sql_tmp .= " OR mail = '" . trim($orderData[0]['ebay_usermail']) . "' ";
			}
			if (false && !empty($orderData[0]['ebay_username'])) {// 是否验证用户名（不为空则验证）  加false取消该项验证
				$sql_tmp .= " OR ebay_username = '" . trim($orderData[0]['ebay_username']) . "' ";
			}
			if (!empty($orderData[0]['ebay_phone'])) {// 是否验证用户电话（不为空则验证）
				$sql_tmp .= " OR ebay_phone = '" . trim($orderData[0]['ebay_phone']) . "' ";
			}
			if (!empty($orderData[0]['ebay_street'])) {// 是否验证地址（不为空则验证）
				$sql_tmp .= " OR ebay_street='" . @mysql_escape_string(trim($orderData[0]['ebay_street'])) . "' ";
			}
			if (!empty($orderData[0]['ebay_postcode'])) {//
				$sql_tmp .= " OR ebay_postcode = '" . trim($orderData[0]['ebay_postcode']) . "' ";
			}
			if ($sql_tmp) {
				$sql_tmp = substr($sql_tmp, 3); // 去除最前面的 OR
				$sql .= " AND ($sql_tmp)";
			}

			$sql .= " AND status=0 LIMIT 1 ";
			echo '<br/>' . $sql . '<br/>';
			$sql = $dbcon->query($sql);
			$sql = $dbcon->getResultArray($sql);
			if ($sql) {
				$id = $sql[0]['id'];
				//保存匹配到黑名单的原因
				$saveMsg = '黑名单id:' . $id;
				if ($sql[0]['userid'] == trim($orderData[0]['ebay_userid'])) {
					$saveMsg.='>userid:' . $sql[0]['userid'];
				}
				if ($sql[0]['mail'] == trim($orderData[0]['ebay_usermail'])) {
					$saveMsg.='>ebay_usermail:' . $sql[0]['ebay_usermail'];
				}
				if ($sql[0]['ebay_username'] == trim($orderData[0]['ebay_username'])) {
					$saveMsg.='>ebay_username:' . $sql[0]['ebay_username'];
				}
				if ($sql[0]['ebay_phone'] == trim($orderData[0]['ebay_phone'])) {
					$saveMsg.='>ebay_phone:' . $sql[0]['ebay_phone'];
				}
				if ($sql[0]['ebay_street'] == trim($orderData[0]['ebay_street'])) {
					$saveMsg.='>ebay_street:' . $sql[0]['ebay_street'];
				}
				if ($sql[0]['ebay_postcode'] == trim($orderData[0]['ebay_postcode'])) {
					$saveMsg.='>ebay_postcode:' . $sql[0]['ebay_postcode'];
				}
				$notes = empty($sql[0]['notes']) ? $sql[0]['userid'] : $sql[0]['notes']; // 没有原因信息填入用户ID
				$porderstatus = self::GetOrderStatusV2($orderData[0]['ebay_id']);
				$dbcon->execute("UPDATE ebay_order SET ebay_status=261,ebay_noteb=concat(ebay_noteb,'，{$notes}') WHERE ebay_id=" . $orderData[0]['ebay_id'] . ' LIMIT 1'); // 标记为黑名单订单
				LogOper::addOrderLogs($orderData[0]['ebay_id'], '订单修改之前的状态是:[' . $porderstatus . '] 订单修改后的状态是: [黑名单] 修改人是:' . $user . "黑名单原因:$saveMsg");
			}
		}
	}
	/*
	*@name 对已经分配好的渠道做特殊处理
	*@param ebay_id 订单id
	*@param ebay_carrier 物流渠道
	*@return array();
	*@add time 2018-03-29 tian
	*/
	public static function specialOper($ebay_id,$ebay_carrier){
		$return['status']='error';
		//UBI墨西哥专线订单的产品总数量不得超过10个
		if($ebay_carrier=='UBI墨西哥专线'){
            $ubiRes=OrderProcess::checkUbiPack($ebay_id);
            if($ubiRes===true){
				$return['status']='success';
                $return['toStatus'] = 1;
                $return['toStatusName'] = 'UBI墨西哥专线,包裹产品数量超过10';
            }
        }
		return $return;
	}
	public static function GetOrderStatusV2($ebay_id) {
		global $dbcon;
		$vv = "select ebay_status from ebay_order where ebay_id ='$ebay_id' ";
		$vv = $dbcon->execute($vv);
		$vv = $dbcon->getResultArray($vv);
		$ebay_status = $vv[0]['ebay_status'];
		$returnstatus = '';
		if ($ebay_status == '0') {
			$returnstatus = '未付款订单';
		} else if ($ebay_status == '1') {
			$returnstatus = '待处理订单';
		} else if ($ebay_status == '2') {
			$returnstatus = '已经发货';
		} else {
			$rr = "select name from ebay_topmenu where id='$ebay_status' ";


			$rr = $dbcon->execute($rr);
			$rr = $dbcon->getResultArray($rr);
			$returnstatus = $rr[0]['name'];
		}
		return $returnstatus;
	}
}
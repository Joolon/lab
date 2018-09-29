<?php 
/*
*@name 订单操作类
*@add time 2018-03-23
*@add user tian
*/
include_once dirname(__FILE__)."/LogOper.class.php";
class OrderOper{
	/*
	*@name 拆单，拆出1个或多个
	*@param 主单id
	*@param1 子单集
	*@return 主单id与被拆出的新主单id
	*@add time 2018-03-23 tian
	*/
	public static function splitOrder($ebay_id,$ebay_tid,$city=null){
		global $dbcon,$truename;
		if(!isset($truename) || empty($truename)){
			$turename='系统';
		}
		
		$chaiDanSql = $dbcon->query('select a.ebay_ordersn,a.ebay_ordertype from ebay_order AS a INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn=b.ebay_ordersn WHERE a.ebay_id='.$ebay_id);
		$chaiDanSql	= $dbcon->getResultArray($chaiDanSql);
		$sqldid='select a.*,b.goods_weight from ebay_orderdetail AS a LEFT JOIN ebay_goods AS b ON a.sku=b.goods_sn where a.ebay_id IN('.$ebay_tid.') and a.ebay_ordersn="'.$chaiDanSql[0]['ebay_ordersn'].'"';
		$sqldid=$dbcon->execute($sqldid);
		$sqldid	= $dbcon->getResultArray($sqldid);//需要拆出来的子单
		$cChaidanSql=count($chaiDanSql);
		$cSqldid=count($sqldid);
		if($cSqldid==$cChaidanSql){
			return array(
				'status'=>'error',
				'content'=>'不能拆出所有子单'
			);
		}
		if($cSqldid > 0){
			$oldOrdersn=$sqldid[0]['ebay_ordersn'];
			$newOrderShipfee=0;//新订单总运费
			$newOrderTotal=0;//新订单的总价格
			$newWeight=0;//新订单重量
			if($city){
				$sqlNewOrdersn=$city;
			}else{
				$sqlNewOrdersn=substr($ebay_tid,0,8);//取第一个子单的ebay_id链接ordersn
			}
			
			$newOrdersn=$oldOrdersn.'-'.$sqlNewOrdersn;//新订单ordersn
			for($a=0;$a<$cSqldid;$a++){
				$newOrderTotal+=$sqldid[$a]['ebay_itemprice']*$sqldid[$a]['ebay_amount']+$sqldid[$a]['shipingfee'];
				$newOrderShipfee+=$sqldid[$a]['shipingfee'];
				$newWeight += $sqldid[$a]['ebay_amount'] * $sqldid[$a]['goods_weight'];
			}
			//添加新主订单
			$addmorder="insert into ebay_order (
			ebay_user,ebay_status,ebay_ordersn,ebay_username,ebay_street,ebay_street1,ebay_city,ebay_state,ebay_couny,ebay_countryname,
			ebay_postcode,ebay_phone,ebay_shipfee,ebay_usermail,ebay_userid,ebay_ptid,ebay_total,ebay_currency,ebay_paidtime,
			ebay_account,ebay_tracknumber,ebay_noteb,ebay_carrier,ebay_note,resendreason,refundreason,resendtime,refundtime,
			ebay_ordertype,packingtype,orderweight,ordershipfee,packinguser,ebay_warehouse,ebay_addtime,ebay_createdtime,ebay_markettime,ShippedTime,PayPalEmailAddress,recordnumber
			) select
			ebay_user,ebay_status,concat(ebay_ordersn,'-$sqlNewOrdersn'),ebay_username,ebay_street,ebay_street1,ebay_city,ebay_state,ebay_couny,ebay_countryname,
			ebay_postcode,ebay_phone,ebay_shipfee,ebay_usermail,ebay_userid,ebay_ptid,ebay_total,ebay_currency,ebay_paidtime,
			ebay_account,concat(ebay_tracknumber,null),ebay_noteb,ebay_carrier,ebay_note,resendreason,refundreason,resendtime,refundtime,
			ebay_ordertype,packingtype,orderweight,ordershipfee,packinguser,ebay_warehouse,ebay_addtime,ebay_createdtime,ebay_markettime,ShippedTime,PayPalEmailAddress,recordnumber
			from ebay_order where ebay_id='$ebay_id'";
			if($truename=='otw'){echo '<br>'.$addmorder;}
			$dbcon->execute($addmorder);
			//将拆除的子订单关联到新主单
			$uptdorder="update ebay_orderdetail set ebay_ordersn='$newOrdersn' where ebay_id IN($ebay_tid)";
			if($truename=='otw'){echo '<br>'.$uptdorder;}
			$dbcon->execute($uptdorder);
			//更新新订单的总价与运费
			$uptmordernew="update ebay_order set ebay_status=1,ebay_total=$newOrderTotal,ebay_shipfee=$newOrderShipfee,orderweight=$newWeight where ebay_ordersn='$newOrdersn'";
			if($truename=='otw'){echo '<br>'.$uptmordernew;}
			$dbcon->execute($uptmordernew);
			//更新旧主单的总价与运费
			$uptmorderold="update ebay_order set ebay_total=ebay_total-$newOrderTotal,ebay_shipfee=ebay_shipfee-$newOrderShipfee,orderweight=orderweight-$newWeight where ebay_ordersn='$oldOrdersn'";
			$dbcon->execute($uptmorderold);
			//新订单增加日志
			$sqlrd="select ebay_id from ebay_order where ebay_ordersn='$newOrdersn'";
			$sqlrd=$dbcon->execute($sqlrd);
			$sqlrd	= $dbcon->getResultArray($sqlrd);
			$newebay_id=$sqlrd[0]['ebay_id'];
			$notes= '此订单是从订单['.$ebay_id.']拆出来 操作人是:'.$truename;
			LogOper::addOrderLogs($newebay_id,$notes,17);
			//旧订单增加日志
			$notes= '子订单---['.$ebay_tid.']被拆出到订单：['.$newebay_id.'] 操作人是:'.$truename;
			LogOper::addOrderLogs($ebay_id,$notes,17);
			return array(
				'status'=>'success',
				'content'=>array(
					$ebay_id,
					$newebay_id
				)
			);
		}
	}
	/*
	*@name 无子订单则删除
	*@param 主单id
	*@return boolean
	*@add time 2018-03-24 tian
	*/
	public static function delErrOrder($ebay_id){
		global $dbcon;
		$sql='DELETE FROM ebay_order WHERE ebay_id='.$ebay_id;
		if($dbcon->execute($sql)){
			return true;
		}
		return false;
	}
	/*
	*@name 特殊订单转待处理
	*@param 主单id
	*@return boolean
	*@add time 2018-03-24 tian
	*/
	public static function toAbnormal($ebay_id){
		global $dbcon;
		if(!$ebay_id){
			return false;
		}
		$sql='UPDATE ebay_order SET ebay_status="1" WHERE ebay_id='.$ebay_id;
		if($dbcon->execute($sql)){
			return true;
		}
		return false;
	}
	/*
	*@name 特殊订单转ALI
	*@param 主单id
	*@return boolean
	*@add time 2018-03-29 tian
	*/
	public static function toAli($ebay_id){
		global $dbcon;
		if(!$ebay_id){
			return false;
		}
		$sql='UPDATE ebay_order SET ebay_status="254" WHERE ebay_id='.$ebay_id;
		if($dbcon->execute($sql)){
			return true;
		}
		return false;
	}
	/*
	*@name 修改订单仓库id
	*@param 主单id
	*@param2 子单id
	*@param1 仓库id 
	*@return boolean
	*@add time 2018-03-29 tian
	*/
	public static function modifyStoreId($ebay_id,$warehouse_id,$sid=null){
		global $dbcon;
		$return = array();
		if($sid){
			$sql="SELECT a.ebay_id,a.ebay_ordertype FROM ebay_order AS a RIGHT JOIN ebay_orderdetail AS b ON a.ebay_ordersn=b.ebay_ordersn WHERE b.ebay_id={$sid}";
		}else{
			$sql = 'SELECT ebay_id,ebay_ordertype FROM ebay_order WHERE ebay_id='.$ebay_id;
		}
		$sql=$dbcon->query($sql);
		$sql=$dbcon->getResultArray($sql);
		$ebay_id=$sql[0]['ebay_id'];
		$ebay_ordertype = $sql[0]['ebay_ordertype'];
		$sql='UPDATE ebay_order SET ebay_warehouse="'.$warehouse_id.'" WHERE ebay_id='.$ebay_id;
		if($dbcon->execute($sql)){
			$return['id'] = trim($ebay_id);
			$return['ordertype'] = trim($ebay_ordertype);
			$return['warehouse_id'] = trim($warehouse_id);
		}
		return $return;
	}
	/*
	*@name 计算订单重量
	*@param 主单id
	*@return void
	*@add time 2018-03-29 tian
	*/
	public static function orderWeight($ebay_id){
		global $dbcon;
		$sql='SELECT a.ebay_id,b.sku,b.ebay_amount,c.goods_weight FROM ebay_order AS a INNER JOIN ebay_orderdetail AS b ON a.ebay_ordersn=b.ebay_ordersn LEFT JOIN ebay_goods AS c ON b.sku=c.goods_sn WHERE a.ebay_id='.$ebay_id;
		$sql = $dbcon->query($sql);
		$sql = $dbcon->getResultArray($sql);
		$weight=0;
		foreach($sql AS $v){
			$weight += floatval($v['ebay_amount'] * $v['goods_weight']);
		}
		$dbcon->execute('UPDATE ebay_order SET orderweight='.$weight.' WHERE ebay_id='.$ebay_id);
                //** 添加关于此操作的记录 **//
                $json = json_encode($sql);
                $array = array(
                    'ebay_id'=>$ebay_id,
                    'json'=>$json
                );
                DB::Add('system_orderweight', $array);
	}
}
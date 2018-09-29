<?php 
/*
*@name 订单交运类
*@add time 2018-03-23
*@add user tian
*/
require_once dirname(__FILE__)."/LogOper.class.php";
class OrderConfirmShip{
	/*
	*@name 查询订单交运记录
	*@param 主单id
	*@return 交运的物流与追踪号
	*@add time 2018-03-23 tian
	*/
	public static function serachOrderShipMentRecord($ebay_id){
		global $dbcon;
		$sql='SELECT * FROM zfrode_order_markship WHERE ebay_id='.$ebay_id;
		$sql=$dbcon->query($sql);
		$sql=$dbcon->getResultArray($sql);
		return $sql;
	}
	/*
	*@name 删除订单交运记录
	*@param 订单id
	*@return boolean
	*@add time 2018-03-23 tian
	*/
	public static function delShipMentRecord($id){
		global $dbcon;
		$sql='DELETE FROM  zfrode_order_markship WHERE ebay_id='.$ebay_id;
		$sql=$dbcon->execute($sql);
		return $sql;
	}
	/*
	*@name 添加订单交运记录
	*@param 订单id
	*@param1 物流渠道
	*@param2 物流公司
	*@param3 追踪号
	*@param4 服务名称
	*@param4 订单类型
	*@return boolean
	*@add time 2018-03-23 tian
	*/
	public static function addOrderShipMentRecord($id,$carrier,$company,$track,$service,$ordertype){
		global $dbcon;
		$sql='INSERT zfrode_order_markship(ebay_id,sham_tracknumber,shippingcarrier_name,ebay_carrier,ebay_shiptype,ebay_markettime,ordertype) VALUES('.$id.',"'.$track.'","'.$company.'","'.$carrier.'","'.$service.'","'.time().'","'.$ordertype.'")';
		$res=$dbcon->execute($sql);
		return $res;
	}
	/*
	*@name 获取渠道交运service
	*@param 订单类型
	*@param1 物流公司id
	*@param2 物流渠道
	*@return service
	*@add time 2018-03-23 tian
	*/
	public static function getServiceName($order_type,$company_id='',$carrier=''){
		global $dbcon;
		$serviceName='';
		$order_type = strtolower($order_type);
		switch($order_type){
			case 'ali-express':
				$value="ali_name";
				break;
			case 'wish' :
			case 'wish-us' :
				$value="wish_name";
				break;
		}
		$sql='SELECT ebay_carrier,shippingcarrierid,'.$value.' FROM system_shippingqudao ';
		if($company_id && $carrier){
			$sql.='WHERE shippingcarrierid="'.$company_id.'" AND ebay_carrier="'.$carrier.'"';
		}
		$sql=$dbcon->query($sql);
		$sql=$dbcon->getResultArray($sql);
		if($company_id && $carrier){//指定
			$serviceName = $sql[0][$value];
		}else{
			foreach($sql AS $v){
				$serviceName[$v['shippingcarrierid']][$v['ebay_carrier']]=$v[$value];
			}
		}
		return $serviceName;
	}

}
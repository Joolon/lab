<?php 
/*
*@name 给面单添加额外属性
*@date 2017-8-09 tian
*/
class PubLabel{
	private $dbcon;
	function __construct(){
		global $dbcon;
		if(empty($dbcon)){
			include_once dirname(dirname(__FILE__)).'/dbconnect.php';
			$this->dbcon=new DBClass();
		}else{
			$this->dbcon=$dbcon;
		}
	}
	//获取订单类型
	function getOrderType($ebay_id){
		$ebay_ordertype='';
		$sql=$this->dbcon->query('SELECT ebay_ordertype FROM ebay_order WHERE ebay_id='.$ebay_id);
		$sql=$this->dbcon->getResultArray($sql);
		if(count($sql)>0){
			$ebay_ordertype=$sql[0]['ebay_ordertype'];
		}
		return $ebay_ordertype;
	}
	//设置平台简称
	function setShopCode($ebay_ordertype){
		$shop_code='';
		switch($ebay_ordertype){
			case 'ALI-EXPRESS' :
				$shop_code='AE';
				break;
			case 'AMAZON' :
				$shop_code='AM';
				break;
			case 'CD订单' :
				$shop_code='CD';
				break;
			case 'EBAY订单' :
				$shop_code='EB';
				break;
			case 'FNAC' :
				$shop_code='FN';
				break;
			case 'JOOM' :
				$shop_code='JO';
				break;
			case 'LAZADA' :
				$shop_code='LA';
				break;
			case 'MM订单' :
				$shop_code='MM';
				break;
			case 'NEWEGG' :
				$shop_code='NE';
				break;
			case 'Opensky' :
				$shop_code='OS';
				break;
			case 'PAYTM' :
				$shop_code='PA';
				break;
			case 'PM订单' :
				$shop_code='PM';
				break;
			case 'PP线下订单' :
				$shop_code='PP';
				break;
			case 'SHOPEE' :
				$shop_code='SH';
				break;
			case 'TANGA' :
				$shop_code='TA';
				break;
			case 'TOP' :
				$shop_code='TO';
				break;
			case 'WISH' :
				$shop_code='WI';
				break;
			case '复制订单' :
				$shop_code='CO';
				break;
		}
		return $shop_code;
	}

}
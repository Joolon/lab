<?php

/*
 * @name 获取跟踪号  打印面单
 * @date 2017-05-18 tian
 */
require_once dirname(__FILE__).'/logisticsPublicFunc.class.php';
class logistics extends logisticeFunc {
    private $returnMsg;
    function __construct() {
        parent::__construct();
    }
    //申请跟踪号
    public function getTrack($ebay_id) {
        $sql = 'SELECT b1.ebay_id,b1.recordnumber,b1.orderweight,b1.ebay_orderqk,b1.ebay_usermail,b1.ebay_couny,b1.ebay_countryname,b1.ebay_username,b1.ebay_street,b1.ebay_street1,b1.ebay_city,b1.ebay_state,b1.ebay_postcode,b1.ebay_phone,b1.location,b1.ebay_carrier,b1.ebay_total,b1.orderweight,b1.ebay_tracknumber,b2.ebay_itemprice,b2.ebay_amount,b2.sku,b2.ebay_itemtitle,b2.ebay_itemid,b2.ebay_tid,b3.goods_cost,b3.goods_ywsbmc,b3.goods_zysbmc,b3.goods_location,b3.goods_hgbm,b3.goods_sbjz,b3.goods_weight,b1.system_shippingcarriername,b1.ebay_ordertype,b1.ebay_account From ebay_order as b1 inner join ebay_orderdetail as b2 on b2.ebay_ordersn=b1.ebay_ordersn left join ebay_goods as b3 on b3.goods_sn=b2.sku Where 1=1 AND (b1.ebay_tracknumber IS NULL OR b1.ebay_tracknumber="") AND ebay_combine!="1" AND b1.ebay_id =(' . $ebay_id . ')';
        $orderArr = $this->dbcon->query($sql);
        $orderArr = $this->dbcon->getResultArray($orderArr);
        if (count($orderArr) < 1) {
            $this->returnMsg = '订单【' . $ebay_id . '】跟踪号已存在或为合并订单';
            $this->returnError();
        }
        if (count($orderArr) > 1) {//是否为混合仓
            $mixingWarehouse = $this->mixingWarehouse($ebay_id);
            if ($mixingWarehouse) {
                $this->returnMsg = '订单【' . $ebay_id . '】混合仓';
                $this->returnError();
            }
        }
        $logisticArr = $this->getShippingCompany($orderArr[0]['ebay_carrier']);
        $className = $logisticArr[0]['trackno_class'];
        $printName = $logisticArr[0]['label_method'];
        $getCodeName = $logisticArr[0]['trackno_method'];
        require_once dirname(__FILE__).'/'.$className . '.class.php'; //加载物流对应类
        $obj = new $className;
        $sortOrderArr=$this->sortData($orderArr);
        $response = $obj->$getCodeName($sortOrderArr);
        if($response['code']=='success'){
            $this->savaTracknumber($ebay_id,$response['msg'],$orderArr[0]['ebay_carrier']);
        }else{
            $this->returnMsg='订单号【'.$ebay_id.'】-'.$response['msg'].'<br/>';
            return $this->returnError();
        }
    }

    //打印面单
    public function printTemplate($ebay_id) {
        $sql = 'SELECT b3.goods_name,b1.ebay_ordertype,b1.ebay_id,b1.recordnumber,b1.ebay_orderqk,b1.ebay_usermail,b1.ebay_couny,b1.ebay_countryname,b1.ebay_username,b1.ebay_street,b1.ebay_street1,b1.ebay_city,b1.ebay_state,b1.ebay_postcode,b1.ebay_phone,b1.location,b1.ebay_carrier,b1.ebay_total,b1.orderweight,b1.ebay_tracknumber,b2.ebay_itemprice,b2.ebay_amount,b2.sku,b2.ebay_itemtitle,b2.ebay_itemid,b2.ebay_tid,b3.goods_cost,b3.goods_ywsbmc,b3.goods_zysbmc,b3.goods_location,b3.goods_hgbm,b3.goods_sbjz,b3.goods_weight,b1.system_shippingcarriername,b1.ebay_ordertype,b1.ebay_account From ebay_order as b1 inner join ebay_orderdetail as b2 on b2.ebay_ordersn=b1.ebay_ordersn left join ebay_goods as b3 on b3.goods_sn=b2.sku Where 1=1 AND  ebay_combine!="1" AND b1.ebay_id =(' . $ebay_id . ')  order by b3.goods_location asc';
        $orderArr = $this->dbcon->query($sql);
        $orderArr = $this->dbcon->getResultArray($orderArr);
        if(!$orderArr[0]['ebay_tracknumber']){
            $this->returnMsg='【'.$orderArr[0]['ebay_id'].'】-跟踪号不能为空';
            $this->returnError();
        }elseif(!$orderArr[0]['ebay_countryname']){
            $this->returnMsg='【'.$orderArr[0]['ebay_id'].'】-收件人国家为空';
            $this->returnError();
        }elseif(($orderArr[0]['ebay_street'].$orderArr[0]['ebay_street1'])==''){
            $this->returnMsg='【'.$orderArr[0]['ebay_id'].'】-收件人地址为空';
            $this->returnError();
        }
        $logisticArr = $this->getShippingCompany($orderArr[0]['ebay_carrier']);
//        $className = $logisticArr[0]['trackno_class'];
        $className = $logisticArr[0]['label_class'];
        $printName = $logisticArr[0]['label_method'];
        $getCodeName = $logisticArr[0]['trackno_method'];
        $sortOrderArr=$this->sortData($orderArr);//重构订单数据
//        echo dirname(__FILE__).'/'.$className . '.class.php';
        require_once dirname(__FILE__).'/'.$className . '.class.php'; //加载物流对应类
        $obj = new $className;
        if(count($sortOrderArr['product'])>1){
            return $obj->$printName($sortOrderArr).$this->appendTemplate($sortOrderArr);//额外打印拣货单
        }else{
            return $obj->$printName($sortOrderArr);
        }
    }
    //拣货单
    public function appendTemplate($dataArr){
        $orderDetail='';
        for($a=0;$a<count($dataArr['product']);$a++){
            $orderDetail.=$dataArr['product'][$a]['sku'].'*'.$dataArr['product'][$a]['ebay_amount'].'-【'.$dataArr['product'][$a]['goods_location'].'】<br/>';   
        }
        $appendLabel='<div style="width:98mm;height:98mm;padding:1mm;margin-top:5px;border:1px solid #333;"> 
                <h1 style="text-align:center;">'.$dataArr['ebay_id'].'拣货单</h1>
                <div style="line-height:1.1em;">
                   '.$orderDetail.'
                </div>
                </div>';
        return $appendLabel;
    }
    //数据整理
    public function sortData($orderArr){
        $dataArr=array();
		require_once dirname(__FILE__).'/PubLabel.class.php'; //订单类型简称
		$obj=new PubLabel();
		$dataArr['shop_code']=$obj->setShopCode($orderArr[0]['ebay_ordertype']);
        $dataArr['ebay_id']=$orderArr[0]['ebay_id'];
        $dataArr['ebay_orderqk']=$orderArr[0]['ebay_orderqk'];
        $dataArr['ebay_tracknumber']=$orderArr[0]['ebay_tracknumber'];
        $dataArr['ebay_username']=$orderArr[0]['ebay_username'];
        $dataArr['ebay_street']=$orderArr[0]['ebay_street'].' '.$orderArr[0]['ebay_street1']; 
        $dataArr['ebay_couny']=$orderArr[0]['ebay_couny'];
        $dataArr['ebay_postcode']=$orderArr[0]['ebay_postcode'];
        $dataArr['ebay_orderweight']=$orderArr[0]['orderweight'];
        $dataArr['ebay_countryname']=$orderArr[0]['ebay_countryname'];
        $dataArr['ebay_countryname1']=parent::getCNcountryname($orderArr[0]['ebay_couny']);//国家中文名
        $dataArr['ebay_phone']=$orderArr[0]['ebay_phone'];
        $dataArr['ebay_state']=$orderArr[0]['ebay_state'];
        $dataArr['ebay_city']=$orderArr[0]['ebay_city'];
        $dataArr['product'][0]['sku']=$orderArr[0]['sku'];
        $dataArr['product'][0]['ebay_amount']=$orderArr[0]['ebay_amount'];
        $dataArr['product'][0]['goods_name']=$orderArr[0]['goods_name'];
        $dataArr['product'][0]['goods_location']=$orderArr[0]['goods_location'];
        $dataArr['product'][0]['goods_weight']=$orderArr[0]['goods_weight'];
        $dataArr['product'][0]['goods_hgbm']=$orderArr[0]['goods_hgbm'] ? $orderArr[0]['goods_hgbm'] : '';
        $dataArr['product'][0]['goods_ywsbmc']=$orderArr[0]['goods_ywsbmc'];
        $dataArr['product'][0]['goods_zysbmc']=$orderArr[0]['goods_zysbmc'];
        $dataArr['product'][0]['goods_sbjz']=$orderArr[0]['goods_sbjz'];
        if(count($orderArr)>1){
            for($a=0;$a<count($orderArr);$a++){
                $dataArr['product'][$a]['sku']=$orderArr[$a]['sku'];
                $dataArr['product'][$a]['ebay_amount']=$orderArr[$a]['ebay_amount'];
                $dataArr['product'][$a]['goods_name']=$orderArr[$a]['goods_name'];
                $dataArr['product'][$a]['goods_location']=$orderArr[$a]['goods_location'];
                $dataArr['product'][$a]['goods_weight']=$orderArr[$a]['goods_weight'];
                $dataArr['product'][$a]['goods_hgbm']=$orderArr[$a]['goods_hgbm'];
                $dataArr['product'][$a]['goods_ywsbmc']=$orderArr[$a]['goods_ywsbmc'];
                $dataArr['product'][$a]['goods_zysbmc']=$orderArr[$a]['goods_zysmbc'];
                $dataArr['product'][$a]['goods_sbjz']=$orderArr[$a]['goods_sbjz'];
            }
        }
        return $dataArr;
    } 
    //根据订单物流公司id获取物流api名称，申请跟踪号方法，面单方法
    private function getShippingCompany($carrierName) {
        $res = $this->dbcon->query('SELECT label_class,trackno_class,trackno_method,label_method FROM flow_label_settings WHERE ebay_carrier="' . $carrierName . '"');
        $res = $this->dbcon->getResultArray($res);
        if($res){
            return $res;
        }else{
            $this->returnMsg='物流渠道为空或未设置';
            $this->returnError();
        }
    }
    //保存跟踪号信息
    function savaTracknumber($ebay_id,$tracknumber,$ebay_carrier,$ebay_orderqk=''){
        $sql='UPDATE ebay_order SET ebay_tracknumber="'.$tracknumber.'",ebay_orderqk="'.$ebay_orderqk.'" WHERE ebay_id='.$ebay_id;
        $res=$this->dbcon->query($sql);
        if($res){
            $notes='系统自动申请了'.$ebay_carrier.'跟踪号:'.$tracknumber;
            parent::addordernote($ebay_id,$notes);
			//同时保存跟踪号到备份表
			$addSql='INSERT ebay_backuptracknumber(ebay_id,ebay_tracknumber,gettime) VALUES("'.$ebay_id.'","'.$tracknumber.'",'.time().')';
			$this->dbcon->query($addSql);
            echo '【'.$ebay_id.'】-'.$notes;
        }else{
             $this->returnMsg='保存跟踪号失败';
             $this->returnError();
        }
    }
    //返回错误信息
    private function returnError() {
        return $this->returnMsg;
    }
}
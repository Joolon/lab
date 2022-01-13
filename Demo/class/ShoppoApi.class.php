<?php
/**
 * Class WadiApi
 * Wadi平台订单管理对接
 * @author:tl
 */
class ShoppoApi{

    private static $dbcon = '';
    private static $apiurl = 'https://api.shoppo.com';

    public function __construct()
    {
        global $dbcon;
        self::$dbcon = $dbcon;
    }

    /**
     * 获得订单
     * @param $apikey
     * @param $merchantid
     */
    public function getorder($apikey,$merchantid,$ebay_account){
        $api ='/api/merchant/orders/';
        $url = self::$apiurl.$api.'?limit=200&status_in=PAID';
        $order_result=self::do_request($apikey,$merchantid,$url);
        if($order_result['data']['total'] > 200){
            $page=$order_result['data']['total']/200;
            $order_bookmark = $order_result['data']['bookmark'];
            for($i = 0; $i <= ceil($page); $i++){
                $url = self::$apiurl.$api.'?limit=200&status_in=PAID'.'&bookmark='.$order_bookmark;
                $order=self::do_request($apikey,$merchantid,$url);
                $order_bookmark = $order['data']['bookmark'];
                $result=self::saveorder($order['data']['orders'],$ebay_account);
            }
        }else{
            $result = self::saveorder($order_result['data']['orders'],$ebay_account);
        }
    }

    /**
     * 保存订单
     * @param $order
     */
    public function saveorder($order,$ebay_account){
        foreach ($order as $key => $value){
            if(self::isshoppoorder($value['id'])){//判断订单是否存在若存在则跳过
                echo '<font color="#deb887">基础订单'.$value['id'].'已存在</font><br/>';
                continue;
            }
            $order_time = strtotime($value['order_time']);
            $time_created = strtotime($value['time_created']);
            $order_data = array(
                'buyer_id'=>$value['buyer_id'],
                'buyer_order_number'=>$value['buyer_order_number'],
                'order_id'=>$value['id'],
                'order_time'=>$order_time,
                'order_total'=>$value['order_total'],
                'shipping_address_recipient_name'=>$value['shipping_address']['recipient_name'],
                'shipping_address_address1'=>$value['shipping_address']['address1'],
                'shipping_address_address2'=>$value['shipping_address']['address2'],
                'shipping_address_city'=>$value['shipping_address']['city'],
                'shipping_address_country'=>$value['shipping_address']['country'],
                'shipping_address_phone_number'=>$value['shipping_address']['phone_number'],
                'shipping_address_state'=>$value['shipping_address']['state'],
                'shipping_address_zipcode'=>$value['shipping_address']['zipcode'],
                'status'=>$value['status'],
                'time_created'=>$time_created,
                'unique_id'=>$value['unique_id'],
                'account'=>$ebay_account,
                'add_time'=>time()
            );
            $order_result=DB::Add('shoppo_order',$order_data);
            if($order_result >0){
                echo '添加'.$value['id'].'基础订单<font color="blue">成功</font>';
                foreach ($value['order_items'] as $k => $v){
                    $detail_data = array(
                        'orderid'=>$value['id'],
                        'itemid'=>$v['id'],
                        'is_refunded'=>$v['is_refunded'],
                        'product_snapshot_identifier'=>$v['product_snapshot_identifier'],
                        'product_snapshot_name'=>$v['product_snapshot_name'],
                        'quantity'=>$v['quantity'],
                        'refund_reason_type'=>$v['refund_reason_type'],
                        'shipping_provider'=>$v['shipping_provider'],
                        'shipping_refunded'=>$v['shipping_refunded'],
                        'sku_snapshot_identifier'=>$v['sku_snapshot_identifier'],
                        'sku_snapshot_price'=>$v['sku_snapshot_price'],
                        'sku_snapshot_shipping_price'=>$v['sku_snapshot_shipping_price'],
                        'status'=>$v['status'],
                        'tracking_number'=>$v['tracking_number'],
                        'add_time'=>time()
                    );
                    $detail_result=DB::Add('shoppo_order_detail',$detail_data);
                    if ($detail_result >0){
                        echo '添加'.$v['id'].'<font color="blue">订单详情成功</font>';
                    }else{
                        echo '添加'.$v['id'].'<font color="red">订单详情失败</font>';
                    }
                }
                echo '<br/>';
            }else{
                echo '添加'.$value['id'].'<font color="red">基础订单失败</font>';
                echo '<br/>';
            }
        }
    }

    /**
     * 判断订单是否存在于基础表
     * @param $orderid
     * @return bool
     */
    function isshoppoorder($orderid){
        $sql = "select order_id from shoppo_order where order_id='{$orderid}'limit 1";
        $res = DB::QuerySQL($sql);
        if (count($res) >= 1) {
            return true;
        }
    }

    /**
     * 将基础表中的订单导入到系统表中
     * @param $order
     */
    function importorder($order){
        foreach ($order as $key => $value){
            if(self::judgeOrderExists($value['order_id'],$value['account'])){//判断订单是否存在若存在则跳过
                echo '<font color="#ff7f50">系统订单'.$value['order_id'].'已存在</font><br/>';
                continue;
            }
            $couny = self::getcountry($value['shipping_address_country']);
            if ($value['status'] == 'PAID'){
                $status = '274';
            }
            $order_array = array(
                'ebay_paystatus'=>'Complete','ebay_ordersn'=>$value['account'].'-'.$value['order_id'],
                'ebay_orderid'=>$value['order_id'],'ebay_createdtime'=>$value['time_created'],
                'ebay_paidtime'=>$value['order_time'],'ebay_userid'=>'',
                'ebay_username'=>$value['shipping_address_recipient_name'],'ebay_usermail'=>'',
                'ebay_street'=>$value['shipping_address_address1'],'ebay_street1'=>$value['shipping_address_address2'],
                'ebay_city'=>$value['shipping_address_city'],'ebay_state'=>$value['shipping_address_state'],
                'ebay_couny'=>$couny,'ebay_countryname'=>$value['shipping_address_country'],
                'ebay_postcode'=>$value['shipping_address_zipcode'],'ebay_phone'=>$value['shipping_address_phone_number'],
                'ebay_currency'=>'USD','ebay_total'=>$value['order_total'],
                'ebay_status'=>$status,'ebay_user'=>'otw',
                'ebay_shipfee'=>'0','ebay_account'=>$value['account'],
                'recordnumber'=>$value['order_id'],'ebay_addtime'=>time(),
                'eBayPaymentStatus'=>'','ebay_warehouse'=>32,//默认深圳仓
                'order_no'=>$value['order_id'],//使用id
                'ebay_ordertype'=>'SHOPPO',
                'ebay_ptid'=>$value['order_id'],
                'status'=>$value['status']
            );
            $order_result=DB::Add('ebay_order',$order_array);
            if ($order_result > 0){
                echo '系统订单'.$value['order_id'].'<font color="#4169e1">添加成功</font>';
                $order_detail=DB::Select('shoppo_order_detail',"orderid = '".$value['order_id']."'");
                foreach ($order_detail as $k => $v){
                    $detail_array = array(
                        'recordnumber'=>$v['orderid'],
                        'ebay_ordersn' => $value['account'].'-'.$v['orderid'],// V2系统订单编号（账户+订单ID）
                        'ebay_itemid'=>$v['itemid'],
                        'ebay_itemtitle'=>$v['product_snapshot_name'],
                        'ebay_itemprice'=>$v['sku_snapshot_price'],
                        'ebay_amount'=>$v['quantity'],
                        'ebay_createdtime'=>$value['time_created'],
                        'ebay_user'=>'otw',
                        'sku'=>$v['sku_snapshot_identifier'],
                        'shipingfee'=>$v['sku_snapshot_shipping_price'],
                        'ebay_account'=>$value['account'],
                        'addtime'=>time(),
                        'ebay_tid'=>$v['orderid'].'-'.$v['sku_snapshot_identifier'],
                        'OrderLineItemID'=>$v['itemid'].'-'.$v['sku_snapshot_identifier']
                    );
                    $detail_result=DB::Add('ebay_orderdetail',$detail_array);
                    if ($detail_result >0){
                        DB::Update('ebay_order',array('import_time'=>time()),"orderid='".$v['orderid']."'");
                        echo $v['itemid'].'订单详情添加<font color="#4169e1">成功</font>';
                    }else{
                        echo $v['itemid'].'订单详情添加<font color="red">失败</font>';
                    }
                }
                echo '<br/>';
            }else{
                echo '系统订单'.$value['order_id'].'<font color="red">添加失败</font>';
                echo '<br/>';
            }
        }
    }

    /**
     * 订单国家在国家地区列表里国家简码
     * function getcountry
     * @param $countryen
     * @return string
     */
    function getcountry($countryen){
        global $dbcon;
        $qsql="select countrysn from ebay_countrys where countryen='".trim($countryen)."' limit 1";
        //echo $qsql;
        $qsql = DB::QuerySQL($qsql);
        if(count($qsql) >=1 ){
            return $qsql[0]['countrysn'];
        }else{
            return '';
        }
    }

    /**
     * 判断订单是否存在
     * function judgeOrderExists
     * @param $order_id
     * @param $ebay_account
     * @return bool
     */
    function judgeOrderExists($order_id,$ebay_account)
    {
        $sql = "select ebay_id from ebay_order where recordnumber='{$order_id}' and ebay_account ='$ebay_account' limit 1";
        $res = DB::QuerySQL($sql);
        if (count($res) >= 1) {
            return true;
        }
        $sql = "select ebay_id from ebay_order_HistoryRcd where recordnumber='{$order_id}' and ebay_account ='$ebay_account' limit 1";
        $res = DB::QuerySQL($sql);
        if (count($res) >= 1) {
            return true;
        }
    }

    function getcarrierCode($apikey,$merchantid){
        $api = '/api/merchant/supported-couriers/';
        $url = self::$apiurl.$api;
        $result = self::do_request($apikey,$merchantid,$url);
        print_r($result);
        die;
    }

    /**
     * 订单交运
     * @param $apikey
     * @param $merchantid
     * @param $order
     */
    function shiporder($apikey,$merchantid,$order){
        $detail=DB::Select('ebay_orderdetail',"recordnumber = '".$order['recordnumber']."'");
        foreach ($detail as $key => $value){
            $api = '/api/merchant/order_item/'.$value['ebay_itemid'].'/';
            $url = self::$apiurl.$api;
            $carrierCode = self::getshipcode($order['ebay_carrier']);
            $data=array(
                'tracking_number'=>$order['ebay_tracknumber'],
                'shipping_provider'=>$carrierCode
            );
            $result = self::do_post_request($url,$apikey,$merchantid,$data);
            if(isset($result['error'])){
                if ($result['error'] == 'Tracking number can only be updated when the order item is in PAID or IN_FULFILLMENT status'){
                    DB::Update('ebay_order',array('ebay_markettime'=>time()),"ebay_id = '".$order['ebay_id']."'");
                    echo '<font color="#ff7f50">订单已交运,修改订单交运时间成功</font>';
                }
            }else{
                if($result['data']['status'] == 'IN_FULFILLMENT'){
                    $shipapi = '/api/merchant/order_items/ship/';
                    $shipurl = self::$apiurl.$shipapi;
                    $itemid = $value['ebay_itemid'];
                    $shipdata = array(
                        'order_item_ids'=>array($itemid)
                    );
                    $ship_result=self::do_post_request($shipurl,$apikey,$merchantid,$shipdata);
                    if ($ship_result['data']['order_items'][0]['status'] =='SHIPPED'){
                        DB::Update('ebay_order',array('ebay_markettime'=>time()),"ebay_id = '".$order['ebay_id']."'");
                        echo $itemid.'<font color="green">交运成功</font>';
                    }else{
                        echo $itemid.'<font color="red">交运失败</font>';
                    }
                }
            }
        }
    }

    /**
     * 获得渠道编码
     * @param $carrier
     * @return string
     */
    function getshipcode($carrier){
        $carrierCode='';
        switch($carrier){
            case 'CN小包':
                $carrierCode='china-post';
                break;
            case '顺友E邮宝':
                $carrierCode='china-ems';
                break;
            case '中美专线':
                $carrierCode='yunexpress';
                break;
            case '蓝思美国专线':
                $carrierCode='usps';
                break;
            case '顺丰美国小包':
                $carrierCode='sfb2c';
                break;
        }
        return $carrierCode;
    }

    function do_post_request($url,$apikey,$merchantid,$data){
        $headers = array(
            "content-type: application/json",
            'apikey:'.$apikey,
            'merchantid:'.$merchantid
        );
        $ch = curl_init();

        //根据您的系统，您可以添加其他选项或修改以下选项。.
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $response = json_decode(curl_exec($ch),true);
        $err = curl_error($ch);
        curl_close($ch);
        return $response;
    }


        /**
     * get转发url
     * @param $apikey
     * @param $merchantid
     * @param $url
     * @return mixed
     */
    public function do_request($apikey,$merchantid,$url){
        $headers = array(
            'apikey:'.$apikey,
            'merchantid:'.$merchantid
        );
        // 打开curl连接
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        // 将结果保存到$data
        $output = curl_exec($curl);
        // 关闭curl连接
        curl_close($curl);
        $result = json_decode($output,true);
        return $result;
    }
}
?>
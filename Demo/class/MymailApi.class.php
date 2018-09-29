<?php
/**
 * Created by PhpStorm.
 * User: tl
 * Date: 2017/11/16
 * Time: 9:12
 */
class MymailApi{


    /**
     * getorder
     * 获得所有订单
     */
    function getorders($account){
        $data = array(
            'grant_type'=>'password',
            'client_id'=>$account['client_id'],
            'client_secret'=>$account['client_secret'],
            'username'=>$account['username'],
            'password'=>$account['password']
        );
        $token=$this->get_token($data);
        $account_data = array(
            'mymail_refresh_token'=>$token['refresh_token'],
            'mymail_access_token'=>$token['access_token']
        );
        $result_account=DB::Update('ebay_account',$account_data,"id='".$account['id']."'");
        $pageurl ='https://mall.my.com/merchant/api/v1/purchase/order/_search?offset=0&limit=100';
        $page_data = array(
            "filter" => array(
                "fulfilled" => false
            )
        );
        $page_order=$this->do_post_request($pageurl,$page_data,$token['access_token']);
        if($page_order['data']['totalCount'] > 100){
            $Offset = $page_order['data']['totalCount']/100;
            $page =$Offset - 1;
            $new_order =array();
            $offset = 0;
            for($i = 0; $i <= ceil($page); $i++){
                $url = 'https://mall.my.com/merchant/api/v1/purchase/order/_search?offset='.$offset.'&limit=100';
                $order=$this->do_post_request($url,$page_data,$token['access_token']);
                $offset =$offset+100;
                $this->downorders($account['ebay_account'],$order['data']['orders']);
            }

        }else{
            $this->downorders($account['ebay_account'],$page_order['data']['orders']);
        }
    }

    /**
     * 下载订单
     * @param $account
     * @param $order
     */
    function downorders($account,$order){
        foreach ($order as $key => $value){
            if($this->is_mymail_order($account,$value['id'])){
                echo '订单号'.$value['id'].'<font color="#9acd32">已存在</font>';
                continue;
            }
            $order_array = array(
                'mymail_id'=>$value['id'],'purchase_id'=>$value['purchase']['id'],
                'quantity'=>$value['quantity'],'size'=>$value['size'],
                'color'=>$value['color'],'state'=>$value['state'],
                'updatedAt'=>strtotime($value['updatedAt']),'totalPrice_amount'=>$value['totalPrice']['amount'],
                'totalPrice_currency'=>$value['totalPrice']['currency'],'expectedFulfillDate'=>$value['expectedFulfillDate'],
                'price_amount'=>$value['price']['amount'],'price_currency'=>$value['price']['currency'],
                'shippingPrice_amount'=>$value['shippingPrice']['amount'],'shippingPrice_currency'=>$value['shippingPrice']['currency'],
                'productName'=>$value['productName'],'productImageUrl'=>$value['productImageUrl'],
                'shippingDetails_name'=>$value['shippingDetails']['name'],'shippingDetails_streetAddress1'=>$value['shippingDetails']['streetAddress1'],
                'shippingDetails_streetAddress2'=>$value['shippingDetails']['streetAddress2'],'shippingDetails_city'=>$value['shippingDetails']['city'],
                'shippingDetails_state'=>$value['shippingDetails']['state'],'shippingDetails_country'=>$value['shippingDetails']['country'],
                'shippingDetails_zipcode'=>$value['shippingDetails']['zipcode'],'shippingDetails_phoneNumber'=>$value['shippingDetails']['phoneNumber'],
                'sku'=>$value['sku'],'add_time'=>time(),'ebay_account'=>$account,'status'=>10
            );
            $result=DB::Add('mymail_order',$order_array);
            if ($result >1){
                echo '订单号'.$value['id'].'导入基础表<font color="green">成功</font>';
            }else{
                echo '订单号'.$value['id'].'导入基础表<font color="red">失败</font>';
            }
        }
    }

    function saveorder(){
        $mymail_order = DB::Select("mymail_order","status = 10");
        $result= array();
        foreach ($mymail_order as $k => $v) {
            $result[$v['purchase_id']][] = $v;
        }
        foreach ($result as $r_k =>$r_v){
            if (count($r_v) > 1){//多个
                $this->yes_order_save($r_v);
            }else{//单个
                $this->no_order_save($r_v[0]);
            }
        }
    }

    /**
     * 订单表中已有订单存在的处理方法
     * @param $order
     */
    function yes_order_save($order){
        if($this->isOrderimport($order[0]['purchase_id'])){
            foreach ($order as $key => $value){
                DB::Update('mymail_order',array('status'=>20),"id = '".$value['id']."'");
            }
            return;
        }
        $sku_array=array();
        $k=0;
        $arr_out=array();
        $amount_array = array();
        //去重
        foreach ($order as $key =>$value){
            $k++;
            $sku_array[$k]=$value['sku'];

            $key_out = $value['sku']; //提取内部一维数组的key(name age)作为外部数组的键
            if(array_key_exists($key_out,$arr_out)){
                continue;
            }
            else{
                $arr_out[$key_out] = $order[$key]; //以key_out作为外部数组的键
                $arr_wish[$key] = $order[$key];  //实现二维数组唯一性
            }
        }
        //获得每个item的sku数量
        $count_sku =array_count_values($sku_array);
        $total_price=array();
        foreach($arr_wish as $ke=>$val){
            $num=$count_sku[$val['sku']];
            $sku = str_replace('baba-','',$val['sku']);
            $sku = str_replace('yong2-','',$sku);
            $sku = str_replace('youy-','',$sku);
            $orderdetail = array(
                'recordnumber'=>$val['purchase_id'],
                'ebay_ordersn' => $val['ebay_account'].'-'.$val['purchase_id'],// V2系统订单编号（账户+订单ID）
                'ebay_itemid'=>$val['mymail_id'],
                'ebay_itemtitle'=>$val['productName'],
                'ebay_itemprice'=>$val['price_amount'],
                'ebay_amount'=>$val['quantity']*$num,
                'ebay_createdtime'=>$val['updatedAt'],
                'ebay_user'=>'otw',
                'sku'=>$sku,
                'shipingfee'=>$val['shippingPrice_amount']*$num,
                'ebay_account'=>$val['ebay_account'],
                'addtime'=>time(),
                'ebay_tid'=>$val['mymail_id'].'-'.str_replace('baba-','',$sku),
                'OrderLineItemID'=>$val['mymail_id'].'-'.str_replace('baba-','',$sku)
            );
            $orderdetail_result = DB::Add("ebay_orderdetail",$orderdetail);
            if($orderdetail_result !=0){
                echo $orderdetail['ebay_itemid'].'详情表添加成功';
            }else{
                echo '详情表添加失败';
            }
            $total_price['total']= $total_price['total']+$orderdetail['ebay_itemprice']*$orderdetail['ebay_amount'];
            $total_price['shipingfee']= $total_price['shipingfee']+$orderdetail['shipingfee']*$orderdetail['ebay_amount'];
        }
        if($order[0]['state']=='approved'){
            $ebay_paystatus = 'Complete';
            $ebay_status=274;
        }
        if($order[0]['shippingDetails_country'] == 'Rossiyskaya Federatsiya'||$order[0]['shippingDetails_country'] == 'Rassiya'||$order[0]['shippingDetails_country'] == 'ROSSIYa'){
            $countryname ='Russia';
        }else{
            $countryname = $order[0]['shippingDetails_country'];
        }
        $couny= $this->getcountry($countryname);
        $order_array = array(
            'ebay_paystatus'=>$ebay_paystatus,'ebay_ordersn'=>$order[0]['ebay_account'].'-'.$order[0]['purchase_id'],
            'ebay_orderid'=>$order[0]['purchase_id'],'ebay_createdtime'=>$order['updatedAt'],
            'ebay_paidtime'=>$order[0]['updatedAt'],'ebay_userid'=>'',
            'ebay_username'=>$order[0]['shippingDetails_name'],'ebay_usermail'=>'',
            'ebay_street'=>$order[0]['shippingDetails_streetAddress1'],'ebay_street1'=>'',
            'ebay_city'=>$order[0]['shippingDetails_city'],'ebay_state'=>'',
            'ebay_couny'=>$couny,'ebay_countryname'=>$countryname,
            'ebay_postcode'=>$order[0]['shippingDetails_zipcode'],'ebay_phone'=>$order[0]['shippingDetails_phoneNumber'],
            'ebay_currency'=>$order[0]['totalPrice_currency'],'ebay_total'=>$total_price['total'],
            'ebay_status'=>$ebay_status,'ebay_user'=>'otw',
            'ebay_shipfee'=>$total_price['shipingfee'],'ebay_account'=>$order[0]['ebay_account'],
            'recordnumber'=>$order[0]['purchase_id'],'ebay_addtime'=>time(),
            'eBayPaymentStatus'=>'','ebay_warehouse'=>32,//默认深圳仓
            'order_no'=>$order[0]['purchase_id'],//使用id
            'ebay_ordertype'=>'Mymail',
            'ebay_ptid'=>$order[0]['purchase_id'],
            'status'=>$order[0]['state']
        );
        $order_result = DB::Add('ebay_order',$order_array);
        if($order_result >=1){
            echo $order_array['recordnumber'].'订单表添加成功';
            foreach ($order as $m_k => $m_v){
                DB::Update('mymail_order',array('status'=>20),"id = '".$m_v['id']."'");
            }

        }else{
            echo '订单表添加失败';
        }
        echo '<br/>';
    }

    /**
     * 没有存在订单表中处理方法
     * @param $order
     */
    function no_order_save($order){
        if($this->isOrderimport($order['purchase_id'])){
            DB::Update('mymail_order',array('status'=>20),"id = '".$order['id']."'");
            return;
        }
        $sku = str_replace('baba-','',$order['sku']);
        $sku = str_replace('yong2-','',$sku);
        $sku = str_replace('youy-','',$sku);
        $orderdetail = array(
            'recordnumber'=>$order['purchase_id'],
            'ebay_ordersn' => $order['ebay_account'].'-'.$order['purchase_id'],// V2系统订单编号（账户+订单ID）
            'ebay_itemid'=>$order['mymail_id'],
            'ebay_itemtitle'=>$order['productName'],
            'ebay_itemprice'=>$order['price_amount'],
            'ebay_amount'=>$order['quantity'],
            'ebay_createdtime'=>$order['updatedAt'],
            'ebay_user'=>'otw',
            'sku'=>$sku,
            'shipingfee'=>$order['shippingPrice_amount'],
            'ebay_account'=>$order['ebay_account'],
            'addtime'=>time(),
            'ebay_tid'=>$order['mymail_id'].'-'.$sku,
            'OrderLineItemID'=>$order['mymail_id'].'-'.$sku
        );
        if($order['state']=='approved'){
            $ebay_paystatus = 'Complete';
            $ebay_status=274;
        }
        if($order['shippingDetails_country'] == 'Rossiyskaya Federatsiya'||$order['shippingDetails_country'] == 'Rassiya'||$order['shippingDetails_country'] == 'ROSSIYa'||$order['shippingDetails_country'] == 'Russian Federation'){
            $countryname ='Russia';
        }else{
            $countryname = $order['shippingDetails_country'];
        }
        $couny= $this->getcountry($countryname);
        $order = array(
            'ebay_paystatus'=>$ebay_paystatus,'ebay_ordersn'=>$order['ebay_account'].'-'.$order['purchase_id'],
            'ebay_orderid'=>$order['purchase_id'],'ebay_createdtime'=>$order['updatedAt'],
            'ebay_paidtime'=>$order['updatedAt'],'ebay_userid'=>'',
            'ebay_username'=>$order['shippingDetails_name'],'ebay_usermail'=>'',
            'ebay_street'=>$order['shippingDetails_streetAddress1'],'ebay_street1'=>'',
            'ebay_city'=>$order['shippingDetails_city'],'ebay_state'=>'',
            'ebay_couny'=>$couny,'ebay_countryname'=>$countryname,
            'ebay_postcode'=>$order['shippingDetails_zipcode'],'ebay_phone'=>$order['shippingDetails_phoneNumber'],
            'ebay_currency'=>$order['totalPrice_currency'],'ebay_total'=>$order['totalPrice_amount'],
            'ebay_status'=>$ebay_status,'ebay_user'=>'otw',
            'ebay_shipfee'=>$order['shippingPrice_amount'],'ebay_account'=>$order['ebay_account'],
            'recordnumber'=>$order['purchase_id'],'ebay_addtime'=>time(),
            'eBayPaymentStatus'=>'','ebay_warehouse'=>32,//默认深圳仓
            'order_no'=>$order['purchase_id'],//使用id
            'ebay_ordertype'=>'Mymail',
            'ebay_ptid'=>$order['purchase_id'],
            'status'=>$order['state']
        );
        $orderdetail_result = DB::Add("ebay_orderdetail",$orderdetail);
        if($orderdetail_result >0){
            $order_result = DB::Add('ebay_order',$order);
            if($order_result > 0){
                echo $order['recordnumber'].'订单表添加成功';
                DB::Update('mymail_order',array('status'=>20),"id = '".$order['id']."'");
            }else{
                echo '订单表添加失败';
            }
            echo $orderdetail['ebay_itemid'].'详情表添加成功';
        }else{
            echo '详情表添加失败';
        }
        echo '<br/>';
    }

    /**
     * 判断订单是否存在于基础表中
     * @param $account
     * @param $orderid
     * @return bool
     */
    function is_mymail_order($account,$mymail_id){
        $sql	= "select id from mymail_order where mymail_id='".$mymail_id."' limit 1";
        $res	= DB::QuerySQL($sql);
        if(count($res) >= 1){
            return true;
        }
        return false;
    }

    /**
     * 判断订单是否存在
     * @param $order_id
     * @return bool
     */
    function isOrderimport($order_id){
        $sql	= "select ebay_id from ebay_order where recordnumber='{$order_id}' limit 1";
        $res	= DB::QuerySQL($sql);
        if(count($res) >= 1){
            return true;
        }
        return false;
    }

    /**
     * delivery
     * 交运订单
     */
    function delivery($mymail,$account_data){
        $order = DB::Find("ebay_order","recordnumber='".$mymail['purchase_id']."'");
        $data = array(
            'grant_type'=>'password',
            'client_id'=>$account_data['client_id'],
            'client_secret'=>$account_data['client_secret'],
            'username'=>$account_data['username'],
            'password'=>$account_data['password']
        );
        $token=$this->get_token($data);
        $account_update = array(
            'mymail_refresh_token'=>$token['refresh_token'],
            'mymail_access_token'=>$token['access_token']
        );
        $result_account=DB::Update('ebay_account',$account_update,"id='".$account_data['id']."'");
        $account = DB::Find('ebay_account',"ebay_account='".$order['ebay_account']."' and ebay_type = 'mymail'","id,ebay_account,mymail_access_token,mymail_refresh_token");
        $fields = "trackingNumber=".$order['ebay_tracknumber'];
        $curl = curl_init();
        $url = "https://mall.my.com/merchant/api/v1/purchase/order/".$mymail['mymail_id']."/_fulfill";
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $fields,
            CURLOPT_SSL_VERIFYPEER=> false,
            CURLOPT_SSL_VERIFYHOST =>false,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".$account['mymail_access_token']
            ),
        ));
        $response = json_decode(curl_exec($curl),true);
        curl_close($curl);
        $data = array(
            'ebay_markettime'=>time(),
            'ShippedTime'=>time()
        );
        if ($response['meta']['code'] == 400){
            if($response['meta']['errorMessage'] == 'Purchase order could not transit to ship state from state `shipped`'){
                DB::Update('ebay_order',$data,"ebay_id='".$order['ebay_id']."'");
                DB::Update('mymail_order',array('shiptime'=>time()),"mymail_id='".$mymail['mymail_id']."'");
                $msg =$order['ebay_id'].'保存信息成功';
            }else{
                $msg =$order['ebay_id'].'失败';
            }
        }elseif ($response['meta']['code']==200){
            DB::Update('mymail_order',array('shiptime'=>time()),"mymail_id='".$mymail['mymail_id']."'");
            DB::Update('ebay_order',$data,"ebay_id='".$order['ebay_id']."'");
            $msg =$order['ebay_id'].'交运成功';
        }else{
            $msg =$order['ebay_id'].'交运失败';
        }
        return $msg;
    }


    /**
     * 判断订单详情是否存在
     * function judgeOrderExists
     * @param $order_id
     * @param $ebay_account
     * @return bool
     */
    function judgeOrderExists($order_id){
        $sql	= "select ebay_id,ebay_total from ebay_orderdetail where ebay_itemid='{$order_id}' limit 1";
        $res	= DB::QuerySQL($sql);
        if(count($res) >= 1){
            return true;
        }
        return false;
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
     * 获得token
     * @param $account
     * @return mixed
     */
    function get_token($account){
        $append = '';
        foreach ($account as $key => $value){
            $append.= $key."=".$value."&";
        }
        $files = rtrim($append, '&');
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://mall.my.com/oauth/v2/token",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $files,
            CURLOPT_SSL_VERIFYPEER=> false,
            CURLOPT_SSL_VERIFYHOST =>false,
            CURLOPT_HTTPHEADER => array(
                "content-type: application/x-www-form-urlencoded"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            print_r( "cURL Error #:" . $err);
        } else {
            return json_decode($response,true);
        }
    }


    /**
     * do_post_request
     * 是用curl进行post转发请求
     */
    function do_post_request($url,$data,$token){
        $curl = curl_init();
        $append = '';
        $files=json_encode($data);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $files,
            CURLOPT_SSL_VERIFYPEER=> false,
            CURLOPT_SSL_VERIFYHOST =>false,
            CURLOPT_HTTPHEADER => array(
                "authorization: Bearer ".$token,
                "content-type: application/json"
            ),
        ));

        $response = json_decode(curl_exec($curl),true);
        $err = curl_error($curl);

        curl_close($curl);
        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    /**
     * do_request
     * 使用curl进行get转发
     */
    function do_get_request($url,$token){
        $headers = array(
            'APIToken:'.$token
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
<?php
set_time_limit(0);

function getCurlData($curl, $Data, $method = 'post', $header = '',$type=false)
    {
        $ch = curl_init(); //初始化
        curl_setopt($ch, CURLOPT_URL, $curl); //设置访问的URL
        curl_setopt($ch, CURLOPT_HEADER, false); // false 设置不需要头信息 如果 true 连头部信息也输出
        curl_setopt($ch, CURLE_FTP_WEIRD_PASV_REPLY, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);//设置成秒
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if($type){
            curl_setopt($ch, CURLOPT_USERPWD, OA_ACCESS_TOKEN_USERPWD); //auth 验证  账号及密码
        }
        if ($header) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //只获取页面内容，但不输出
        if (strtolower($method) == 'post') {
            curl_setopt($ch, CURLOPT_POST, true); //设置请求是POST方式
            curl_setopt($ch, CURLOPT_POSTFIELDS, $Data); //设置POST请求的数据
        }
        $datas = curl_exec($ch); //执行访问，返回结果

        if(empty($datas) and $datas === false){
            $error = curl_error($ch);
            var_dump($error);exit;
        }

        curl_close($ch); //关闭curl，释放资源
        return $datas;
    }

$arr = [ 


"UPDATE pur_supplier_trading_detail SET trading_money='0.000',trading_origin_money='0.000',is_calculated=0 WHERE relative_trading_num='11372059' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='0.000',trading_origin_money='0.000',is_calculated=0 WHERE relative_trading_num='11379707' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='580.000',trading_origin_money='580.000',is_calculated=0 WHERE relative_trading_num='11433055' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='0.000',trading_origin_money='0.000',is_calculated=0 WHERE relative_trading_num='11742428' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='110.400',trading_origin_money='110.400',is_calculated=0 WHERE relative_trading_num='11747732' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='75.000',trading_origin_money='75.000',is_calculated=0 WHERE relative_trading_num='11748100' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='111.000',trading_origin_money='111.000',is_calculated=0 WHERE relative_trading_num='11745944' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='646.000',trading_origin_money='646.000',is_calculated=0 WHERE relative_trading_num='11750335' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='621.000',trading_origin_money='621.000',is_calculated=0 WHERE relative_trading_num='11753509' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='52.000',trading_origin_money='52.000',is_calculated=0 WHERE relative_trading_num='11747837' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='58.000',trading_origin_money='58.000',is_calculated=0 WHERE relative_trading_num='11751758' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='3034.000',trading_origin_money='3034.000',is_calculated=0 WHERE relative_trading_num='11769879' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='700.000',trading_origin_money='700.000',is_calculated=0 WHERE relative_trading_num='11769667' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='462.000',trading_origin_money='462.000',is_calculated=0 WHERE relative_trading_num='11768156' LIMIT 1; ",
"UPDATE pur_supplier_trading_detail SET trading_money='0.000',trading_origin_money='0.000',is_calculated=0 WHERE relative_trading_num='11777976' LIMIT 1; "
    
];

$header = array('Content-Type:application/json');

$url = 'http://pms.yibainetwork.com:81/Sync_warehouse_results_api/exec_sql';
foreach($arr as $value){
	
	$param = [
		'sql' => $value,
		'token' => "Justin666$$"
	];
	
    $result = getCurlData($url,json_encode($param),'post',$header);
	print_r($value);
	echo '<br/><br/>';
	// continue;
	// echo $i.'<br/>';
	//echo $res;
	echo $result,'<br/><br/>';
	
	//sleep(1);
	
	
}


echo 'sss';exit;








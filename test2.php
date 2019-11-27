<?php
set_time_limit(0);

$arr = [



];

$url = 'http://pms.yibainetwork.com:81/ufxfuiou_api/get_pay_ufxfuiou_voucher?pur_tran_num=';
foreach($arr as $value){
	print_r($url.$value);
	echo '<br/><br/>';
	// continue;
	// echo $i.'<br/>';
	echo file_get_contents($url.$value);
	//echo $res;
	echo '<br/><br/>';
	
	//sleep(1);
	
	
}


echo 'sss';exit;








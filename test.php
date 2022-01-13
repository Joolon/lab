<?php

echo base64_encode("1");
echo base64_decode(base64_encode("1"));
exit;

$age = '123456789000';

print_r(strspn($age,'123456789'));
echo "<br/>";
print_r(strlen($age));


exit;









echo '<a target="_blank" href="https://amos.alicdn.com/msg.aw?v=2&uid=cklf575&site=cntaobao&s=1&charset=utf-8" ><img border="0" src="http://amos.im.alisoft.com/online.aw?v=1&uid=cklf575&site=cntaobao" alt="点击这里给我发消息" /></a>';exit;
set_time_limit(0);


$count_list = ['A' => 3,'B' => 3,'C' => 3];
$price = 22.360;

$sum_count = array_sum($count_list);

$count_list_tmp = [];
foreach($count_list as $key => $value){
	
	$count_list_tmp[$key] = sprintf("%.3f", ($price / $sum_count )* $value);
	
}

// print_r($count_list_tmp);exit;

print_r(array_sum($count_list_tmp));exit;



$arr = [];

$stime = time();
for($i = 0; $i < 100000000;$i --){
	
	
}

echo time() - $stime;exit;

//print_r($arr );exit;

$url = 'http://pms.yibainetwork.com:81/supplier_api/get_old_purchase_cooperation_amount?date=';

foreach($arr as $value){
	
	echo $url.$value;
	echo '<br/><br/>';
	file_get_contents($url.$value);
	echo 'OK';
	echo '<br/><br/>';
}


echo 'sss';exit;








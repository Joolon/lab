<?php
set_time_limit(0);


$arr = [ 
'FBA10149357'
    
];

$url = 'http://pms.yibainetwork.com:81/charge_against_api/init_purchase_order_pay_type_price?purchase_number=';

foreach($arr as $value){
	
	echo $url.$value;
	echo '<br/><br/>';
	file_get_contents($url.$value);
	echo 'OK';
	echo '<br/><br/>';
}


echo 'sss';exit;








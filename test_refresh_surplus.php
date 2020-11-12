<?php
set_time_limit(0);


$arr = [



"PFB10095970",
"PFB10097789",
"FBA10147792",
"PFB10095879",
"PFB10094851",
"PFB10102571",
"PO10252552",
"PFB10097800",
"PFB10074970",
"PFB10100407",
"PFB10092781",
"PFB10103672",


];

$url = 'http://pms.yibainetwork.com:81/charge_against_api/recalculate_surplus?purchase_numbers=';

foreach($arr as $value){
	
	echo $url.$value;
	echo '<br/><br/>';
	file_get_contents($url.$value);
	echo 'OK';
	echo '<br/><br/>';
}


echo 'sss';exit;








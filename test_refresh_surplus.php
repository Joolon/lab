<?php
set_time_limit(0);


$arr = [
'ABD9646290',
'ABD9646296',
'ABD9646309',
'ABD9646316',
'ABD9646329',
'ABD9652576',
'ABD9652638',
'ABD9654635',
'ABD9658507',
'ABD9658506',
'ABD9658505',
'ABD9658282'


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








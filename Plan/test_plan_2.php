<?php

set_time_limit(0);

$url = 'http://pms.yibainetwork.com:81/ali_order_api/auto_update_order_tracking_number';
for($i = 0;$i < 20; $i ++){
	echo $i.'<br/>';
	file_get_contents($url);
	//echo $res;
	echo '<br/><br/>';
	
	//sleep(1);
	
	
}


echo 'sss';exit;
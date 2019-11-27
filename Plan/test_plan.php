<?php

set_time_limit(0);

$url = 'http://pms.yibainetwork.com:81/ali_order_api/receive_ali_order_message?debug=1&limit=20';
for($i = 0;$i < 1; $i ++){
	echo $i.'<br/>';
	file_get_contents($url);
	//echo $res;
	echo '<br/><br/>';
	
	//sleep(1);
	
	
}


echo 'sss';exit;




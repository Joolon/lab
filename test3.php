<?php

set_time_limit(0);
ob_start();
echo str_repeat('',4096);
ob_end_flush();
ob_flush();



$i = 1;
while(true){
	echo $i++;echo "<br/>";
	ob_flush();
	flush();
	sleep(1);
}




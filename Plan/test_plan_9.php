<?php

set_time_limit(0);

$url = "http://caigou.yibainetwork.com/v1/customer-service/update-product-img-download?type=1&page=81";

file_get_contents($url);

file_put_contents('d:/plan_log.txt',date('Y-m-d H:i:s').PHP_EOL, FILE_APPEND);
echo 'sss';
sleep(3);
exit;




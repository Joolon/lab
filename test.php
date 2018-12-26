<?php

$str = '你不好不奥啊啊(颜色革命)(红色)';
preg_match_all('/(\([^\(]+\))/',$str,$match);
print_r($match);
exit;


$arr = [
	[

	'id' => '72',
	'sku' => 'JM01859',

	'modify_user' => 'admin',
	'modify_time' => '2018-12-18 12:12:12',
	'processing_status' => '5',
	'refuse_reason' => ''
	]
];

echo json_encode($arr);
exit;



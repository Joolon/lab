<?php

$list = array(
array(
'id' => 18,
'result_status' => 1,
'modify_user' => 'admin',
'modify_time' => '201-01-17',
'refuse_reason' => '测试驳回',
),
array(
'id' => 19,
'result_status' => 2,
'modify_user' => 'admin',
'modify_time' => '201-01-17',
),
array(
'id' => 20,
'result_status' => 3,
'modify_user' => 'admin',
'modify_time' => '201-01-17',
'new_supplier_name' => '',
'new_supplier_price' => 1.23
),
);

echo json_encode($list);exit;

function format_price($price){
    return (float)sprintf("%.3f",$price);
}

var_dump(format_price(12.3565));exit;

$arr = ['JM08371'];

print_r(json_decode('[{"supplier_name":"\u6df1\u5733\u5e02\u4f18\u8010\u7f8e\u5149\u7535\u79d1\u6280\u6709\u9650\u516c\u53f8","supplier_code":"QS021405","supplier_type":"7","status":"4","reject_reason":"","status_label":"\u5f85\u5ba1"}]',true));exit;

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



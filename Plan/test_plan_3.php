<?php

set_time_limit(0);

$sql = "SELECT id,sku FROM pur_sku_update where ok=0 limit 100";

$conn = new mysqli('localhost', 'root', 'root', 'demo');
// Check connection
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

$url = "http://pms.yibainetwork.com:81/ali_product_api/verify_supplier_equal?sku=";
$result = $conn->query($sql);

$id_list = [];
$sku_list = [];
if ($result->num_rows > 0) {
    // 输出数据
    while($row = $result->fetch_assoc()) {
        $id_list[] = $row['id'];
        $sku_list[] = $row['sku'];
    }
}
$id_list = implode(',',$id_list);
if(empty($id_list)){echo 'end';exit;}
$conn->query("UPDATE pur_sku_update SET ok=500 where id IN($id_list)");

if ($sku_list) {
    // 输出数据
    foreach($sku_list as $sku) {

        $content = file_get_contents($url.$sku);
        if($content == 'Success'){
            $conn->query("UPDATE pur_sku_update SET ok=1 where sku='".$sku."'");
        }else{
            $conn->query("UPDATE pur_sku_update SET ok=2 where sku='".$sku."'");
        }
    }
}

echo 'sss';exit;




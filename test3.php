<?php

set_time_limit(0);
header("Content-type: text/html; charset=GB2312");
ini_set('memory_limit','1024M');


$replaceList = array(
    'publish' => 'subscribe',
    'publisher' => 'subscriber',
);

$fileContent = file_get_contents('D:/www/222.txt');

print_r($fileContent);

// 遍历数组替换所有关键字
foreach($replaceList as $key => $value){

    // 使用 反向肯定预查(?<=pattern) 和 正向肯定预查(?=pattern)
    $fileContent = preg_replace("/(?<=[^\w])(".$key.")(?=[^\w]+)/",$value,$fileContent);

    // 不区分大小写  /i
    //$fileContent = preg_replace("/(?<=[^\w])(".$key.")(?=[^\w]+)/i",$value,$fileContent);

}

echo "<br/>";
echo "<br/>";
print_r($fileContent);
echo 'sss';
exit;
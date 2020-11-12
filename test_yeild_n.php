<?php
set_time_limit(0);

function createRange($number){
    $data = [];
    for($i=0;$i<$number;$i++){
        $data[] = time();
    }
    return $data;
}

$data =createRange(10);
foreach($data as $value){
	print_r($data);
    sleep(1);//这里停顿1秒，我们后续有用
    echo $value."<br/>";
}

echo 'sss';exit;








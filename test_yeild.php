<?php
set_time_limit(0);

function createRange($number){
    for($i=0;$i<$number;$i++){
		$time = time();
		file_put_contents('d:/yeild.txt',$time.PHP_EOL,FILE_APPEND);
        yield time();
    }
}



$data =createRange(10);
foreach($data as $value){
	print_r($data);
    sleep(1);//这里停顿1秒，我们后续有用
    echo $value."<br/>";
}

echo 'sss';exit;








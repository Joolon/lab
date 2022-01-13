<?php



function bubbleSort(array $arr){

    $k1 = $k2 = 0;
    $max_i = count($arr) - 1;
    for($i = 0;$i < $max_i;$i ++){
        $k1 ++;
        for($j = 0 ;$j < $max_i- $i;$j ++){
            $k2 ++;
            if($arr[$j] > $arr[$j + 1]){
                $tmp = $arr[$j];
                $arr[$j] = $arr[$j+1];
                $arr[$j+1] = $tmp;
            }
        }
    }

//    var_dump($k1,$k2);exit;

    return $arr;

}

$arr = [9,2,4,5,6,7,8,1,3];
$arr = bubbleSort($arr);
print_r($arr);exit;
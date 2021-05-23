<?php
/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2016/10/14
 * Time: 11:05
 */


// 调试函数
function echo_br($times = 1)
{
    // 输出多个换行符
    for ($s = '', $i = 0; $i < $times; $i++) {
        $s .= <<< BR
  <br/>
BR;
    }
    echo $s;
}

// 调试函数
function debug_test($type,$message){
    if(is_string($message)){
        echo $type.':'.$message.'<br/>';
    }else{
        echo $type.':';
        print_r($message);
        echo "<br/>";
    }

}

// 调试函数
function print_pre($value){
    echo '<pre>';
    print_r($value);
    echo '</pre>';
}

// 调试函数
function var_dump_pre($value){
    echo '<pre>';
    var_dump($value);
    echo '</pre>';
}


// 金额转成中文
function num_to_cn($num)
{
    $word = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖');
    $unit = array('分', '角', '元', '拾', '佰', '仟', '万', '拾', '佰', '仟', '亿');

    $i = 0;
    $str = '';
    $number = round($num, 2) * 100;

    if ($number > 0) {
        do {
            switch ($number % 10) {
                case 0:
                    $str = $word[0] . $str;
                    break;
                case 1:
                    $str = $word[1] . $unit[$i] . $str;
                    break;
                case 2:
                    $str = $word[2] . $unit[$i] . $str;
                    break;
                case 3:
                    $str = $word[3] . $unit[$i] . $str;
                    break;
                case 4:
                    $str = $word[4] . $unit[$i] . $str;
                    break;
                case 5:
                    $str = $word[5] . $unit[$i] . $str;
                    break;
                case 6:
                    $str = $word[6] . $unit[$i] . $str;
                    break;
                case 7:
                    $str = $word[7] . $unit[$i] . $str;
                    break;
                case 8:
                    $str = $word[8] . $unit[$i] . $str;
                    break;
                case 9:
                    $str = $word[9] . $unit[$i] . $str;
                    break;
            }
            $i++;
            $number = intval($number / 10);
        } while ($number > 0);
    }
    return $str;
}



/**
 * 获取配置文件的方法
 * @param $name
 * @param null $default
 * @return null
 */
function C($name, $default = NULL)
{
    $config_arr = include(BASE_PATH . 'Conf/config.php');

    if (isset($config_arr[$name])) {
        return $config_arr[$name];
    } elseif (defined($name)) {
        return $name;
    } else {
        if ($default == NULL) {
            trigger_error('未定义的变量');
        } else {
            define($name, $default);
            return $default;
        }
    }
}
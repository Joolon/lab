<?php
/**
 * Created by PhpStorm.
 * User: JoLon
 * Date: 2016/10/14
 * Time: 11:05
 */

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


function httpCheck()
{
    // HTTP 认证机制（只有在PHP作为Apache模块时才有效，CGI模式无效）
    if (!isset($_SERVER['PHP_AUTH_USER'])) {
        // 向浏览器发送认证请求(实名认证)，输入的用户名、密码和认证类型保存在$_SERVER和$HTTP_SERVER_VARS中
        header('WWW-Authenticate:Basic realm="My Realm"');// Basic B要大写，否则可能浏览器不兼容
        header('HTTP/1.0 401 Unauthorized');
        echo 'Text to send if user hits Cancel button';
        exit;
    } else {
        echo "<p>HELLO {$_SERVER['PHP_AUTH_USER']}.</p>";
        echo "<p>YOU Entered {$_SERVER['PHP_AUTH_PW']} AS YOU  PASSWORD.</p>";

    }
}


/**
 * 获取配置文件的方法
 * @param $name
 * @param null $default
 * @return null
 */
function C($name, $default = NULL)
{
    $config_arr = include(BASE_PATH . DS . 'Conf\config.php');

    if (isset($config_arr[$name])) {
        return $config_arr[$name];
    } elseif (defined($name)) {
        return $name;
    } else {
        if ($default == NULL) {
            die('Not defined this variable!');
        } else {
            define($name, $default);
            return $default;
        }
    }
}
<?php

class  VHelper
{


    /**
     * 格式化打印出调试信息
     * @param $arr
     * @param bool $escape_html
     * @param string $bg_color
     * @param string $txt_color
     */
    public static function pr($arr, $escape_html = true, $bg_color = '#EEEEE0', $txt_color = '#000000'){
        echo sprintf('<pre style="background-color: %s; color: %s;">', $bg_color, $txt_color);
        $pr_location = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0];
        echo sprintf('print from %s 第%d行 <br/>', $pr_location['file'], $pr_location['line']);
        if ($arr) {
            if ($escape_html) {
                echo htmlspecialchars(print_r($arr, true));
            } else {
                print_r($arr);
            }

        } else {
            var_dump($arr);
        }
        echo '</pre>';
    }

    /**
     * 输出调试信息到浏览器控制台（debug查看，对IT调试屏蔽普通用户非常有用）
     * @param $data
     */
    public static function console_log($data){
        echo '<script>';
        echo 'console.log(' . json_encode($data) . ')';
        echo '</script>';
    }


}
<?php

namespace Libs;


/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2016/6/24
 * Time: 18:07
 */

/**
 * 正则表达式
 * 作用：快速简单查找、匹配、替换单词和句子，用最少的代码实现以上功能
 * 优点：方便地对文本格式进行验证、查找和替换，能够很好地弥补 php 内置函数的不足，
 * 缺点：难读，复杂的表达式会难以维护；性能问题、执行效率，回溯会使得引擎的效率极其低下（使用精确匹配、原子组等）。
 *
 *
 * 正则中“字符”的含义
 * ^ 匹配开始字符  ：^foo 表示匹配以foo开头的字符串
 * $ 匹配结束字符  ：foo$ 表示匹配以foo结尾的字符串
 *
 * 字符^和$同时使用时，表示精确匹配（字符串与模式一样）。例如：^foo$ 表示以foo开头和结尾的字符串，只能是foo
 * \ 把一个字符标记成特殊字符、原义字符、向后引用、八进制转移符 ：比如 \n \\ \( 等
 *
 *
 * 限制符 (*, +, ?, {n}, {n,}, {n,m})
 * ?         匹配子表达式 0||1次 ： （非贪婪模式：尽可能少的匹配）        例如，"do(es)?" 可以匹配 "do" 或 "does" 。? 等价于 {0,1}。
 * +         匹配子表达式 >= 1 次    （贪婪模式：尽可能多的匹配）        例如，'zo+' 能匹配 "zo" 以及 "zoo"，但不能匹配 "z"。
 * *         匹配子表达式 >= 0 次    （贪婪模式：尽可能多的匹配）
 * {n}       匹配子表达式 n 次        ：{2}匹配两次   （n>=0 等于0没有意义）
 * {n,}      匹配 >= n 次
 * {n,m}     至少匹配 n 次，至多匹配 m 次（n>=0,m>=n，m、n之间不能有空格）
 * 注意：    ？ 跟在限制符后面额匹配模式是非贪婪的，尽可能少匹配搜索的字符
 *
 *
 * 子表达式 ： ^、$、^$、[]、()、单字符
 *
 * .  匹配除 \n 之外的任何单个字符。要匹配包括 '\n' 在内的任何字符，请使用象 '[.\n]' 的模式。
 *
 *
 * 模式匹配
 *    (pattern)      匹配符合 pattern 的字符串
 *    (?:pattern)   匹配但不获取值（还不理解）
 *    (?=pattern)   正向肯定预查
 *    (?!pattern)      正向否定预查
 *    (?<=pattern)  反向肯定预查
 *    (?<!pattern)  反向否定预查
 * |  或匹配 ：如 f|foo 匹配 f 或 foo
 *
 *
 * 集合 []，每次匹配集合内的一个字符
 * [xyz]  字符集合：匹配一个集合中任何一个字符（匹配一个字符，匹配一次） ：[xyz] 匹配 x、y、z
 * [^xyz] 负值字符集合：匹配一个集合之外的任何字符 ：[^xyz] 匹配非x、y、z
 * [a-z]  匹配 a-z
 * [^a-z] 匹配非 a-z中的字符
 *
 * \b  匹配一个单词的边界    如 "/action\b/" 匹配以 action 结束的单词
 * \B  匹配一个非单词的边界
 * \d = [0-9] ：匹配一个数字字符
 * \D = [^0-9] : 匹配一个非数字字符
 * \f 匹配换页符
 * \n 换行符
 * \r 回车符
 * \t 制表符
 * \v 垂直制表符
 * \s = [ \f\n\r\t\v](空格也是其中之一) : 匹配任何空白字符
 * \S = [^ \f\n\r\t\v] ：匹配任何非空白字符
 * \w = '[A-Za-z0-9_]' ：匹配包括下划线的任何单词字符
 * \W = '[^A-Za-z0-9_]' : 匹配任何非单词字符
 *
 * \xn 匹配一个十六进制转义值（十六进制字为两个数字长 如 \x41 匹配 A ）
 * \n 标识一个八进制转义值或一个向后引用。
 */

/**
 * PHP 匹配函数
 * preg_match               — 匹配成功返回 true ，失败返回 false
 * preg_filter              — 执行一个正则表达式搜索和替换
 * preg_grep                — 返回匹配模式的数组条目
 * preg_last_error          — 返回最后一个PCRE正则执行产生的错误代码
 * preg_match_all           — 执行一个全局正则表达式匹配（获取所有子串）
 * preg_match               — 执行一个正则表达式匹配（获取第一个紫川）
 * preg_quote               — 转义正则表达式字符
 * preg_replace_callback    — 执行一个正则表达式搜索并且使用一个回调进行替换
 * preg_replace             — 执行一个正则表达式的搜索和替换
 * preg_split               — 通过一个正则表达式分隔字符串
 */

/**
 * 常用正则表达式
 * @link https://c.runoob.com/front-end/854
 * @link http://www.cnblogs.com/dengyang/p/3164402.html
 *
 *
 */


namespace Libs;

class PregMatch {

    /**
     * _check_email() 检查邮箱是否合法
     * @access public
     * @param string $_string 邮箱地址
     * @return boolean false|true
     */
    function _check_email($_string, $_min_num, $_max_num){
        if(preg_match('/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/', $_string)){
            if($_min_num < strlen($_string) || strlen($_string) < $_max_num){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    /**
     * 根据指定模式匹配一个整数
     *      指定模式：非负整数、非正整数、正整数
     * @param     $number
     * @param int $type
     * @return int
     */
    public static function matchInt($number, $type = 0){
        switch($type){
            case 1:// 非负整数 (0，1，2，...)
                $preg = '/^\d+$/';
                break;

            case 2:// 非正整数 (0，-1，-2，...)
                $preg = '/^((-\d+)|(0+))$/';// 用括号括起来，避免出现预想不到的结果
                break;

            default :// 正整数
                $preg = '/^[0-9]*[1-9][0-9]*$/';
        }

        return preg_match($preg, $number);
    }


    /**
     * 匹配 URL
     * @param $url
     * @return int
     */
    public function matchUrl($url){
        $url      = 'http://justcoding.iteye.com/na?cl=2&rn=20&tn=news&word=爱奇艺';
        $preg_url = '/^([a-zA-Z]+):\/\/(\w+)((.\w+)*)(\?\S+)?$/';

        return preg_match($preg_url, $url);
    }

    /**
     * 匹配一个字符串
     * @param $string
     * @param $des_string
     * @return int
     */
    public function matchString($string, $des_string){
        $preg_model = '/['.$string.']?/';

        return preg_match($preg_model, $des_string);
    }

    /**
     * 匹配HTML元素的内容
     * @param $html_string
     * @return mixed
     */
    public function matchHtml($html_string){
        // 以DIV为例
        $html_string = '<div id="biuuu">jb51.net</div><div id="biuuu_2">jb51.net2</div><div id="biuuu_3">jb51.net3</div>';
        preg_match_all('/<div\sid=\"([a-z0-9_]+)\">([^<>]+)<\/div>/', $html_string, $result);

        return $result;
    }


    /**
     * 替换：多个值同时匹配（批量处理）
     * @param $subject
     * @param $pattern
     * @param $replace
     * @return mixed
     */
    public function batchMatch($subject, $pattern, $replace){
        // preg_filter与preg_replace相同，差别仅在于返回的结果
        return preg_filter($pattern, $replace, $subject);

        // 数组例子
        $subject = array('1', 'a', '2', 'b', '3', 'A', 'B', '4');
        $pattern = array('/\d/', '/[a-z]/', '/[1a]/');
        $replace = array('A:$0', 'B:$0', 'C:$0');

        return preg_filter($pattern, $replace, $subject);

        // 字符串例子
        $string      = 'April 15, 2003';
        $pattern     = '/(\w+) (\d+), (\d+)/i';
        $replacement = '${1}1,$3';

        return preg_replace($pattern, $replacement, $string);
    }


    /**
     * 字符串的匹配与替换
     * @param $str
     */
    public function replace(&$str){
        preg_replace("/0/", "", $str);//去掉0字符，此时相当于 replace的功能,
        preg_replace("/0/", "A", $str); // 这样就是将0变成A的意思了
        preg_replace("/[0-9]/", "", $str);//去掉所有数字
        preg_replace("/[a-z]/", "", $str); //这样是去掉所有小写字母
        preg_replace("/[A-Z]/", "", $str); //这样是去掉所有大写字母
        preg_replace("/[a-z,A-Z]/", "", $str); //这样是去掉所有字母
        preg_replace("/[a-z,A-Z,0-9]/", "", $str); //去掉所有字母和数字
    }


    /**
     * 从一个字符串中提取第一个日期并验证日期的合法性
     * @param string $text 目标字符串
     * @return bool|string  string.匹配到的日期  false.没有日期或日期不合法
     */
    public function getDateFromStringCheck($text){
        $pattern = "/\d{1,4}((-|\.|\/)\d{1,2}){2}/";
        preg_match($pattern, $text, $match);

        if(empty($match))
            return false;

        $date = $match[0];
        if(strpos($date, '.') !== false){
            $date_break = explode('.', $date);
        }else{
            $date_break = explode('-', $date);
        }
        $result = checkdate($date_break[1], $date_break[2], $date_break[0]);// 验证日期是否合法
        if($result){
            return $date;
        }else{
            return false;
        }
    }


}

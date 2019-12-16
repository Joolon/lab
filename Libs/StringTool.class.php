<?php
namespace Libs;

/**
 * Created by JoLon.
 * 字符串处理的工具类
 * User：Jolon
 * Time: 2015-02-09 下午9:13
 */
class StringTool
{
    /**
     * 将一个字串中含有全角的数字字符、字母、空格或'%+-()'字符转换为相应半角字符
     * @param string $str 待转换字串
     * @return string 处理后字串
     */
    public static function convertStringSBCCase($str){
        $arr = array('０' => '0', '１' => '1', '２' => '2', '３' => '3', '４' => '4', '５' => '5', '６' => '6', '７' => '7', '８' => '8', '９' => '9', 'Ａ' => 'A', 'Ｂ' => 'B', 'Ｃ' => 'C', 'Ｄ' => 'D', 'Ｅ' => 'E', 'Ｆ' => 'F', 'Ｇ' => 'G', 'Ｈ' => 'H', 'Ｉ' => 'I', 'Ｊ' => 'J', 'Ｋ' => 'K', 'Ｌ' => 'L', 'Ｍ' => 'M', 'Ｎ' => 'N', 'Ｏ' => 'O', 'Ｐ' => 'P', 'Ｑ' => 'Q', 'Ｒ' => 'R', 'Ｓ' => 'S', 'Ｔ' => 'T', 'Ｕ' => 'U', 'Ｖ' => 'V', 'Ｗ' => 'W', 'Ｘ' => 'X', 'Ｙ' => 'Y', 'Ｚ' => 'Z', 'ａ' => 'a', 'ｂ' => 'b', 'ｃ' => 'c', 'ｄ' => 'd', 'ｅ' => 'e', 'ｆ' => 'f', 'ｇ' => 'g', 'ｈ' => 'h', 'ｉ' => 'i', 'ｊ' => 'j', 'ｋ' => 'k', 'ｌ' => 'l', 'ｍ' => 'm', 'ｎ' => 'n', 'ｏ' => 'o', 'ｐ' => 'p', 'ｑ' => 'q', 'ｒ' => 'r', 'ｓ' => 's', 'ｔ' => 't', 'ｕ' => 'u', 'ｖ' => 'v', 'ｗ' => 'w', 'ｘ' => 'x', 'ｙ' => 'y', 'ｚ' => 'z', '（' => '(', '）' => ')', '〔' => '[', '〕' => ']', '【' => '[', '】' => ']', '〖' => '[', '〗' => ']', '“' => '"', '”' => '"', '‘［' => '[', '］' => ']', '｛' => '{', '｝' => '}', '《' => '<', '》' => '>', '％' => '%', '＋' => '+', '—' => '-', '－' => '-', '～' => '-', '：' => ':', '。' => '.', '、' => ',', '，' => '.', '；' => ',', '？' => '?', '！' => '!', '…' => '-', '‖' => '|', '｀' => '`', '‘' => '`', '｜' => '|', '〃' => '"', '　' => ' ', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', ' ' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '' => '', '	' => '');

        return strtr($str, $arr);
    }

    /**
     * 去除字符串里面的中文空格，中文全角空格，换行符制表符回车符
     * @param $string
     * @return mixed
     */
    public static function customStrReplace($string){
        $search = array(" ","　"," ","\n","\r","\t");
        $replace = array("","","","","");

        $string = str_replace($search,$replace ,$string);
        return $string;
    }

    /**
     * 判断字符串是否 只含有字母、数字、下划线、短横线（验证文件名是否合乎要求）
     * @param $content
     * @return bool
     */
    public static function verifyStringIsBase($content){

        // 上传失败,文件名只能含有字母、数字、下划线、短横线
        if(!preg_match("/^[0-9a-zA-Z_\-]+$/", $content)){// 禁止含有中文
            return false;
        }else{
            return true;
        }
    }

    /**
     * 从一个字符串中提取第一个日期并验证日期的合法性
     * @param string $text  目标字符串
     * @return bool|string  string.匹配到的日期  false.没有日期或日期不合法
     */
    public function getDateFromStringCheck($text){
        $pattern = "/\d{4}((-|\.|\/)?\d{1,2}){2}/";
        preg_match($pattern,$text,$match);

        if(empty($match)) return false;

        $date       = $match[0];
        if(strpos($date,'.') !== false){
            $date_break = explode('.',$date);
        }elseif(strpos($date,'-') !== false){
            $date_break = explode('-',$date);
        }else{
            $date_break = array();
            $date_break[0] = substr($date,0,4);
            $date_break[1] = substr($date,4,2);
            $date_break[2] = substr($date,6,2);
        }
        $result     = checkdate($date_break[1],$date_break[2],$date_break[0]);// 验证日期是否合法
        if($result){
            return $date;
        }else{
            return false;
        }
    }

    /**
     * 判断字符串中是否含有中文
     * @param $content
     * @return bool
     */
    public static function verifyStringHasCNWord($content){

        $pattern = '/[^\x00-\x80]/';
        if(preg_match($pattern,$content)){
            return true;
        }else{
            return false;
        }
    }


    /**
     * 判断字符串是否 全部是中文
     * @param $content
     * @return bool
     */
    public static function verifyStringOnlyCNWord($content){
        $pattern = "[^\x80-\xff]";
        if(!eregi($pattern,$content)){
            return true;
        }else{
            return false;
        }
    }

    /**
     * GUID生成企业系统生成 36 位唯一序号
     * @return string
     */
    public static function getGuId()
    {
        // 判断是否有内置 GUID 生成函数
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000); // optional for php 4.2.0 and up.
            $charId = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45); // "-"
            $guId = substr($charId, 0, 8) . $hyphen
                . substr($charId, 8, 4) . $hyphen
                . substr($charId, 12, 4) . $hyphen
                . substr($charId, 16, 4) . $hyphen
                . substr($charId, 20, 12);

            return $guId;
        }
    }

    /**
     * 数字的金额转成中文字符串
     * @author Jolon
     * @param  float $num 金额 （只支持3位小数，最大 9999999.999）
     * @return string|bool
     */
    public function numberPriceToCname($num){
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "厘分角元拾佰仟万拾佰仟亿";

        $num = round($num * 1000, 0);// 将数字转化为整数，去掉小数点后面的数据
        if(strlen($num) > 10){
            return false;
        }

        $i = 0;
        $c = "";
        while(1){
            if($i == 0){
                // 获取最后一位数字
                $n = substr($num, strlen($num) - 1, 1);
            }else{
                $n = $num % 10;
            }
            // 每次将最后一位数字转化为中文
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))){
                $c = $p1.$p2.$c;
            }else{
                $c = $p1.$c;
            }
            $i = $i + 1;
            // 去掉数字最后一位了
            $num = $num / 10;
            $num = (int)$num;
            // 结束循环
            if($num == 0){
                break;
            }
        }
        $j    = 0;
        $slen = strlen($c);
        while($j < $slen){
            // utf8一个汉字相当3个字符
            $m = substr($c, $j, 6);
            // 处理数字中很多0的情况,每次循环去掉一个汉字“零”
            if($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零'){
                $left  = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c     = $left.$right;
                $j     = $j - 3;
                $slen  = $slen - 3;
            }
            $j = $j + 3;
        }
        // 这个是为了去掉类似23.0中最后一个“零”字
        if(substr($c, strlen($c) - 3, 3) == '零'){
            $c = substr($c, 0, strlen($c) - 3);
        }
        // 将处理的汉字加上“整”
        if(empty($c)){
            return "零元";
        }else{
            if(preg_match('/分|角|厘/', $c)){
                return $c;
            }else{
                return $c."整";
            }
        }
    }

    /**
     * 汉字转拼音
     * @param $_String
     * @param string $_Code
     * @return string
     */
    public function pinyin($_String, $_Code = 'gb2312')
    {

        $_DataKey = "a|ai|an|ang|ao|ba|bai|ban|bang|bao|bei|ben|beng|bi|bian|biao|bie|bin|bing|bo|bu|ca|cai|can|cang|cao|ce|ceng|cha" . "|chai|chan|chang|chao|che|chen|cheng|chi|chong|chou|chu|chuai|chuan|chuang|chui|chun|chuo|ci|cong|cou|cu|" . "cuan|cui|cun|cuo|da|dai|dan|dang|dao|de|deng|di|dian|diao|die|ding|diu|dong|dou|du|duan|dui|dun|duo|e|en|er" . "|fa|fan|fang|fei|fen|feng|fo|fou|fu|ga|gai|gan|gang|gao|ge|gei|gen|geng|gong|gou|gu|gua|guai|guan|guang|gui" . "|gun|guo|ha|hai|han|hang|hao|he|hei|hen|heng|hong|hou|hu|hua|huai|huan|huang|hui|hun|huo|ji|jia|jian|jiang" . "|jiao|jie|jin|jing|jiong|jiu|ju|juan|jue|jun|ka|kai|kan|kang|kao|ke|ken|keng|kong|kou|ku|kua|kuai|kuan|kuang" . "|kui|kun|kuo|la|lai|lan|lang|lao|le|lei|leng|li|lia|lian|liang|liao|lie|lin|ling|liu|long|lou|lu|lv|luan|lue" . "|lun|luo|ma|mai|man|mang|mao|me|mei|men|meng|mi|mian|miao|mie|min|ming|miu|mo|mou|mu|na|nai|nan|nang|nao|ne" . "|nei|nen|neng|ni|nian|niang|niao|nie|nin|ning|niu|nong|nu|nv|nuan|nue|nuo|o|ou|pa|pai|pan|pang|pao|pei|pen" . "|peng|pi|pian|piao|pie|pin|ping|po|pu|qi|qia|qian|qiang|qiao|qie|qin|qing|qiong|qiu|qu|quan|que|qun|ran|rang" . "|rao|re|ren|reng|ri|rong|rou|ru|ruan|rui|run|ruo|sa|sai|san|sang|sao|se|sen|seng|sha|shai|shan|shang|shao|" . "she|shen|sheng|shi|shou|shu|shua|shuai|shuan|shuang|shui|shun|shuo|si|song|sou|su|suan|sui|sun|suo|ta|tai|" . "tan|tang|tao|te|teng|ti|tian|tiao|tie|ting|tong|tou|tu|tuan|tui|tun|tuo|wa|wai|wan|wang|wei|wen|weng|wo|wu" . "|xi|xia|xian|xiang|xiao|xie|xin|xing|xiong|xiu|xu|xuan|xue|xun|ya|yan|yang|yao|ye|yi|yin|ying|yo|yong|you" . "|yu|yuan|yue|yun|za|zai|zan|zang|zao|ze|zei|zen|zeng|zha|zhai|zhan|zhang|zhao|zhe|zhen|zheng|zhi|zhong|" . "zhou|zhu|zhua|zhuai|zhuan|zhuang|zhui|zhun|zhuo|zi|zong|zou|zu|zuan|zui|zun|zuo";

        $_DataValue = "-20319|-20317|-20304|-20295|-20292|-20283|-20265|-20257|-20242|-20230|-20051|-20036|-20032|-20026|-20002|-19990" . "|-19986|-19982|-19976|-19805|-19784|-19775|-19774|-19763|-19756|-19751|-19746|-19741|-19739|-19728|-19725" . "|-19715|-19540|-19531|-19525|-19515|-19500|-19484|-19479|-19467|-19289|-19288|-19281|-19275|-19270|-19263" . "|-19261|-19249|-19243|-19242|-19238|-19235|-19227|-19224|-19218|-19212|-19038|-19023|-19018|-19006|-19003" . "|-18996|-18977|-18961|-18952|-18783|-18774|-18773|-18763|-18756|-18741|-18735|-18731|-18722|-18710|-18697" . "|-18696|-18526|-18518|-18501|-18490|-18478|-18463|-18448|-18447|-18446|-18239|-18237|-18231|-18220|-18211" . "|-18201|-18184|-18183|-18181|-18012|-17997|-17988|-17970|-17964|-17961|-17950|-17947|-17931|-17928|-17922" . "|-17759|-17752|-17733|-17730|-17721|-17703|-17701|-17697|-17692|-17683|-17676|-17496|-17487|-17482|-17468" . "|-17454|-17433|-17427|-17417|-17202|-17185|-16983|-16970|-16942|-16915|-16733|-16708|-16706|-16689|-16664" . "|-16657|-16647|-16474|-16470|-16465|-16459|-16452|-16448|-16433|-16429|-16427|-16423|-16419|-16412|-16407" . "|-16403|-16401|-16393|-16220|-16216|-16212|-16205|-16202|-16187|-16180|-16171|-16169|-16158|-16155|-15959" . "|-15958|-15944|-15933|-15920|-15915|-15903|-15889|-15878|-15707|-15701|-15681|-15667|-15661|-15659|-15652" . "|-15640|-15631|-15625|-15454|-15448|-15436|-15435|-15419|-15416|-15408|-15394|-15385|-15377|-15375|-15369" . "|-15363|-15362|-15183|-15180|-15165|-15158|-15153|-15150|-15149|-15144|-15143|-15141|-15140|-15139|-15128" . "|-15121|-15119|-15117|-15110|-15109|-14941|-14937|-14933|-14930|-14929|-14928|-14926|-14922|-14921|-14914" . "|-14908|-14902|-14894|-14889|-14882|-14873|-14871|-14857|-14678|-14674|-14670|-14668|-14663|-14654|-14645" . "|-14630|-14594|-14429|-14407|-14399|-14384|-14379|-14368|-14355|-14353|-14345|-14170|-14159|-14151|-14149" . "|-14145|-14140|-14137|-14135|-14125|-14123|-14122|-14112|-14109|-14099|-14097|-14094|-14092|-14090|-14087" . "|-14083|-13917|-13914|-13910|-13907|-13906|-13905|-13896|-13894|-13878|-13870|-13859|-13847|-13831|-13658" . "|-13611|-13601|-13406|-13404|-13400|-13398|-13395|-13391|-13387|-13383|-13367|-13359|-13356|-13343|-13340" . "|-13329|-13326|-13318|-13147|-13138|-13120|-13107|-13096|-13095|-13091|-13076|-13068|-13063|-13060|-12888" . "|-12875|-12871|-12860|-12858|-12852|-12849|-12838|-12831|-12829|-12812|-12802|-12607|-12597|-12594|-12585" . "|-12556|-12359|-12346|-12320|-12300|-12120|-12099|-12089|-12074|-12067|-12058|-12039|-11867|-11861|-11847" . "|-11831|-11798|-11781|-11604|-11589|-11536|-11358|-11340|-11339|-11324|-11303|-11097|-11077|-11067|-11055" . "|-11052|-11045|-11041|-11038|-11024|-11020|-11019|-11018|-11014|-10838|-10832|-10815|-10800|-10790|-10780" . "|-10764|-10587|-10544|-10533|-10519|-10331|-10329|-10328|-10322|-10315|-10309|-10307|-10296|-10281|-10274" . "|-10270|-10262|-10260|-10256|-10254";

        $_TDataKey = explode('|', $_DataKey);
        $_TDataValue = explode('|', $_DataValue);
        $_Data = (PHP_VERSION >= '5.0') ? array_combine($_TDataKey, $_TDataValue) : self::Arr_Combine($_TDataKey, $_TDataValue);
        arsort($_Data);
        reset($_Data);
        if ($_Code != 'gb2312') {
            $_String = $this->U2_Utf8_Gb($_String);
        }
        $_Res = '';
        for ($i = 0; $i < strlen($_String); $i++) {
            $_P = ord(substr($_String, $i, 1));
            if ($_P > 160) {
                $_Q = ord(substr($_String, ++$i, 1));
                $_P = $_P * 256 + $_Q - 65536;
            }
            $_Res .= self::Pinyins($_P, $_Data);
        }
        return $_Res;
    }

    private function Pinyins($_Num, $_Data)
    {
        if ($_Num > 0 && $_Num < 160) {
            return chr($_Num);
        } elseif ($_Num < -20319 || $_Num > -10247) {
            return '';
        } else {
            foreach ($_Data as $k => $v) {
                if ($v <= $_Num) {
                    break;
                }
            }
            return $k;
        }
    }

    private function U2_Utf8_Gb($_C)
    {
        $_String = '';
        if ($_C < 0x80) {
            $_String .= $_C;
        } elseif ($_C < 0x800) {
            $_String .= chr(0xC0 | $_C >> 6);
            $_String .= chr(0x80 | $_C & 0x3F);
        } elseif ($_C < 0x10000) {
            $_String .= chr(0xE0 | $_C >> 12);
            $_String .= chr(0x80 | $_C >> 6 & 0x3F);
            $_String .= chr(0x80 | $_C & 0x3F);
        } elseif ($_C < 0x200000) {
            $_String .= chr(0xF0 | $_C >> 18);
            $_String .= chr(0x80 | $_C >> 12 & 0x3F);
            $_String .= chr(0x80 | $_C >> 6 & 0x3F);
            $_String .= chr(0x80 | $_C & 0x3F);
        }
        return iconv('UTF-8', 'GB2312', $_String);
    }

    private function Arr_Combine($_Arr1, $_Arr2)
    {
        for ($i = 0; $i < count($_Arr1); $i++) {
            $_Res[$_Arr1[$i]] = $_Arr2[$i];
        }
        return $_Res;
    }


}
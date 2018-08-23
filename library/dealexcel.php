<?php
/**
 * Created by PhpStorm.
 * User: 陈未远
 * Date: 14-11-24
 * Time: 下午2:42
 */
class Dealexcel{
    /**
     * 将数组第一行设置为键
     * @param unknown $data
     * @return Ambigous <multitype:, unknown>
     */
    public static function arrangedata($data)
    {
        $i        = 1;
        $new_data = array();
        foreach($data as $da){
            foreach($da as $d){
                $head                    = each($data[1]);
                $headunit                = $head['value'];
                $new_data[$i][$headunit] = $d;
            }
            ++$i;
            reset($data[1]);
        }
        unset($new_data[1]);
        return $new_data;
    }

    /*
     * 设置Excel的表头 A开始
     */
    private function deal_excel_head($max)
    {
        $key   = array();
        $count = $max*5+9+1;
        for($i = 1;$i<=$count;$i++){
            $second = is_int($i/26)?$i/26-1+64:floor($i/26)+64;
            $first  = ($i%26)+64;
            if($i%26==0){
                $first = $first+26;
            }
            $second = chr($second);
            $first  = chr($first);
            $key[]  = ord($second)>64?$second.$first:$first;
        }
        $this->excel_head = array_flip($key);
    }

}
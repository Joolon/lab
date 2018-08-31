<?php
/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2018/4/3
 * Time: 9:12
 */

class HttpTool{


    /**
     * 判断请求URL是否有效
     * @param $url
     * @return bool
     */
    public static function checkUrlIsValid($url){
        $result = get_headers($url,1);
        if(preg_match('/200/',$result[0])){
            return true;
        }else{
            return false;
        }
    }


}
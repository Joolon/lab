<?php

/**
 * 自定义的 异常处理类
 * Created by Lon
 * User: Lon <179777072@qq.com>
 * Date: 2018/4/13
 * Time: 10:54
 */
class CustomException extends Exception {

    public function errorMessage()
    {
        //error message
        $errorMsg = 'Error on line '.$this->getLine().' in '.$this->getFile().' message '.$this->getMessage();
        $errorMsg = $this->getMessage();
        return $errorMsg;
    }


}
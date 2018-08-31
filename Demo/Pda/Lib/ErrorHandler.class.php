<?php

/**
 * Class ErrorHandler
 * 请求返回结果信息
 */
class ErrorHandler {

    public static function apiErrType()
    {
        return array(
            // 操作结果
            'API_0000' => '成功',
            'API_0001' => '查询参数缺失',
            'API_0011' => '操作数据异常',
            'API_0002' => '未找到目标结果',

            // 用户权限认证
            'API_1000' => '无权限',
            'API_1001' => '抱歉，您的身份已过期，请重新登录',

            'API_2000' => '需确认请求',
        );
    }

    public static function catchError(){

        $errors = error_get_last();
        if($errors){
            // type=1 致命错误
            if($errors['type'] != 1) return null;

            $type = $errors['type'];
            $message = $errors['message'];
            $file = $errors['file'];
            $line = $errors['line'];

            $show_msg = 'Type '.$type.' Message '.$message.' File '.$file.' Line '.$line;

            return $message;
        }else{
            return null;
        }
    }

}
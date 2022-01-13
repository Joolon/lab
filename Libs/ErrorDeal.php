<?php


/**
 * Class ErrorDeal
 * PHP 抛出错误记录处理器
 *
 * @author Jolon
 */
class ErrorDeal {


    /**
     * 错误类型转成中文
     *
     * @param $level
     * @return mixed|string
     */
    public function getErrorLevels($level){
        $levels = array(
            E_ERROR           => 'Error',
            E_WARNING         => 'Warning',
            E_PARSE           => 'Parsing Error',
            E_NOTICE          => 'Notice',
            E_CORE_ERROR      => 'Core Error',
            E_CORE_WARNING    => 'Core Warning',
            E_COMPILE_ERROR   => 'Compile Error',
            E_COMPILE_WARNING => 'Compile Warning',
            E_USER_ERROR      => 'User Error',
            E_USER_WARNING    => 'User Warning',
            E_USER_NOTICE     => 'User Notice',
            E_STRICT          => 'Runtime Notice'
        );

        return isset($levels[$level]) ? $levels[$level] : 'Undefined';
    }

    /**
     * 添加 系统操作错误日志
     * @param string $message 错误信息
     * @return mixed
     * @author Jolon
     */
    public function insertLog($message){
        if(!is_string($message))
            $message = json_encode($message);

        $insert_data = [
            'operator_user' => !empty($userName) ? $userName : 'system',
            'operate_ip'    => !empty($operate_ip) ? $operate_ip : '0.0.0.0',
            'operate_route' => uri_string(),
            'log_time'      => date('Y-m-d H:i:s'),
            'message'       => $message,
        ];

        // 插入数据库
        return true;
    }

    /**
     * 添加 系统操作错误日志
     * @param $err_no
     * @param $err_str
     * @param $err_file
     * @param $err_line
     */
    public function saveSystemErrorLog($err_no, $err_str, $err_file, $err_line){
        $severity = $this->getErrorLevels($err_no);

        $message = "错误编号: $severity \n"
            ."$err_file 文件在 $err_line 行发生错误：$err_str";

        // 获取错误追溯信息
        $index           = 0;
        $message_details = "错误概要：".$message." \n\n";
        $message_details .= "#".($index++)." Severity:    $severity \n";
        $message_details .= "#".($index++)." Message:     $err_str \n";
        $message_details .= "#".($index++)." Filename:    $err_file \n";
        $message_details .= "#".($index++)." Line Number: $err_line \n\n\n";

        $trace           = debug_backtrace();
        $message_details .= "#".($index++)." Backtrace:";
        foreach($trace as $error):
            if(isset($error['file']) && strpos($error['file'], realpath(BASEPATH)) !== 0):
                $message_details .= "#".($index++)." File: ".$error['file']."\n";
                $message_details .= "#".($index++)." Line: ".$error['line']."\n";
                $message_details .= "#".($index++)." Function: ".$error['function']."\n\n\n";
            endif;
        endforeach;

        $this->insertLog($message_details);
    }

}


/**
 * 自定义错误处理函数（错误自动抛出异常）
 * @param $err_no
 * @param $err_str
 * @param $err_file
 * @param $err_line
 * @throws Exception
 * @author Jolon
 */
function _my_error_handler($err_no, $err_str, $err_file, $err_line){
    $errorDeal = new ErrorDeal();
    $errorDeal->saveSystemErrorLog($err_no, $err_str, $err_file, $err_line);

    echo '程序发生错误请联系技术处理';
    exit;
}


set_error_handler('_my_error_handler');


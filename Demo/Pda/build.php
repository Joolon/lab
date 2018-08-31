<?php
/**
 * Created by JoLon
 * User: JoLon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 19:58
 */


// 判断请求是否是AJAX方式发起
if(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest"){       // ajax 请求的处理方式
    define('IS_AJAX',1);
}else{// 正常请求的处理方式
    define('IS_AJAX',0);
};

define('APP_BASE',              '../');// V2项目根目录
define('PDA_BASE_PATH',         './');// PDA根目录
define('PDA_APP',               PDA_BASE_PATH.'App/');// 应用目录
define('PDA_APP_ACTION',        PDA_APP.'Action/');// 控制器目录
define('PDA_APP_API',           PDA_APP.'Api/');// API目录
define('PDA_APP_MODEL',         PDA_APP.'Model/');// 模型层
define('PDA_LIB',               PDA_BASE_PATH.'Lib/');// 类库
define('PDA_TPL',               PDA_BASE_PATH.'Tpl/');// 模板文件
define('PDA_CSS',               PDA_BASE_PATH.'Css/');// 界面样式
define('PDA_JS',                PDA_BASE_PATH.'Js/');//
define('PDA_IMAGE',             PDA_BASE_PATH.'Images/');//

//echo PDA_TPL;exit;
//echo PDA_IMAGE;exit;

include_once PDA_APP.'Common/function.php';// 包含公共方法文件

// 注册自动加载类方法
include_once PDA_LIB.'Loader.class.php';
spl_autoload_register('Loader::load');

// 引入数据库操作方法文件
include_once PDA_LIB.'DB.class.php';

// 错误类型
$apiErrType = ErrorHandler::apiErrType();

// 注册一个错误 捕获函数
function error_handler(){
    if($show_msg = ErrorHandler::catchError()){
        api_return(api_error('致命错误->'.$show_msg));
    }
}
register_shutdown_function("error_handler");// 捕获程序退出时出现的错误（没有错误也会去捕获）












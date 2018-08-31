<?php
/**
 * 登录过滤器 验证用户身份
 * Created by JoLon
 * User: JoLon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 20:11
 */
// $m：当前操作的方法
if((empty($_SESSION['userid']) OR empty($_SESSION['username']))
    AND (!in_array($m,array('login','loginCheck','index','exit')))){
    $isLogin = false;
}else{
    $isLogin = true;
}

if($isLogin === false){
    if(defined('IS_AJAX') AND IS_AJAX){// 定义请求类型并且请求类型是AJAX
        if(IS_AJAX){
            $ret = api_error('API_1001');
            echo json_encode($ret);
            exit;
        }
    }else{
        echo "<script>alert('抱歉，您的身份已过期，请重新登录!');location.href='pda.php?c=Index&m=login'</script>";
        exit;
    }
}
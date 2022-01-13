<?php

/**
 * 
 * @author kdw
 * 生成token，页面数据验证，防止重复提交
 * 两种方法
 */
 
class Formtoken {
	private static $token_name='form_token';
	private static $key='wms_check_repeat';
	private static $time_out='time_out';	
	
	/*
	 * 过期时间法
	 */
	public static function checkRepeatSubmit($uniqueid = '', $expire = 30) {
		$session=Session::instance();
		$token_prev=$session->get(self::token_name);
        $uniqueid = empty($uniqueid) ? time().User::active()->id:time().$uniqueid;
        $token = md5(self::$key . $uniqueid);
			
        $time = time();
        if (isset($token_prev) && !empty($token_prev) && $token_prev == $token && ($time - $session->get(self::$time_out) < $expire)) {
            return false;
        } else {
            $_SESSION['token'] = $token;
            $session->set(self::$token_name,$token);
            $session->set(self::$time_out,$time);
            //session写入的时候会等待整个页面加载完成，用此函数可以立即写入
            session_write_close();
            return true;
        }
    }

   //删除存入的值
   public static function cancelRepeatSubmit() {
   	   $session=Session::instance();
	   $session->delete(self::$token_name);
	   $session->delete(self::$time_out);
   }
   
   	/*
     * token销毁法
     */
    public static function createToken($uniqueid=null) {
        $uniqueid = empty($uniqueid) ? time().User::active_user()->id: time().$uniqueid;
        $token = md5(self::$key . $uniqueid);
        $session=Session::instance();
        $session->set(self::$token_name,$token);
		session_write_close();
        return $token;
    }
	
	//检查token
    public static function checkToken($token) {
    	$session=Session::instance();
    	$token_prev = $session->get(self::$token_name);
        if (!isset($token_prev) || empty($token_prev) || $token_prev != $token) {
            return false;
        } else {
            $session->delete(self::$token_name);
            return true;
        }
    }
}
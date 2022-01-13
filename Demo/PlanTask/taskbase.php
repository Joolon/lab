<?php
/**
 * 计划任务运行的公共模块
 * Created by JoLon.
 * User: Jolon
 * Date: 2017/11/16
 * Time: 16:18
 */

header("Content-type: text/html; charset=utf-8");
date_default_timezone_set ("Asia/Chongqing");

define('BASE_PATH', dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR);
define('TASK_BASE_BATH',BASE_PATH.'PlanTask'.DIRECTORY_SEPARATOR);

//echo BASE_PATH;exit;

$user = $truename = $_SESSION['user'] = 'otw';

/* 创建日志文件 */
function subOperation($txtfilename,$txtlog){
    global $truename;
    $date	= date("Y-m-d H:i:s");																					//取得系统时间
    $ip 	= $_SERVER['REMOTE_ADDR']; 																				//取得发言的IP地址
    $fp		= fopen("/".$txtfilename.".csv","a");																	//以写方式打开文件如果不存在就创建
    //$str =$ip.",".$_SESSION['clientname'].",".$date.",".$txtlog."\r\n";											//将所有留言的数据赋予变量$str，"|"的目的是用来今后作数据分割时的数据间隔符号。
    $str 	= $ip.",".",".$date.",".$txtlog."\r\n";																	//将所有留言的数据赋予变量$str，"|"的目的是用来今后作数据分割时的数据间隔符号。
    //fputs($fp,$str);																								//将数据写入文件
    fwrite($fp,$str);																								//将数据写入文件
    fclose($fp);																									//关闭文件

}

// 更新计划任务开始时间
function planStart($planId){
    global $dbcon;
    $uptSystemTask  = "update system_task set taskstatus=1,taskstarttime=" . time() . ",RunTime=0 where ID='$planId' ";
    $dbcon->execute($uptSystemTask);
}

// 更新计划任务结束时间、耗时、状态
function planEnd($planId,$starttime){
    global $dbcon;
    $useTime = (time() - $starttime) / 60;
    $uptSystemTask = "update system_task set Runtime=" . time() . ",UseTime=" . $useTime . ",taskstatus=0 where ID='$planId' ";
    $dbcon->execute($uptSystemTask);
}
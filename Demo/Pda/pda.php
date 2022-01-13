<?php

header('Content-type: text/html;charset=utf-8');
date_default_timezone_set("Asia/Shanghai");
error_reporting(0);
session_start();
ini_set('session.gc_maxlifetime', 600);


include_once 'build.php';// 定义系统环境
include_once 'router.php';// 路由解析


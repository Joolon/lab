<?php

// curl的用途
// 制作一个简单的网页爬虫（抓取网页信息）
// 调用WebService API获取信息
// 模拟用户登录并获取用户中心信息（设置COOKIE来保存模拟登录的信息）
// 从FTP服务器上下载文件到本地,上传文件到FTP
// 从HTTPS服务器获取资源,设置 CURLOPT_SSL_VERIFYPEER=0即可（只需设置跳过HTTPS校验）

function curl_get_userInfo(){
	
		
	// Cookie相关设置，这部分设置需要在所有会话开始之前设置
	date_default_timezone_set("PRC");// 设置COOKIE时，必须先设置时区


	$data='username=18503051504&password=Xml654321$remember=1';


	$curlObj = curl_init();

	curl_setopt($curlObj,CURLOPT_URL,"http://www.imooc.com/user/login");// 登录页面
	curl_setopt($curlObj,CURLOPT_RETURNTRANSFER,true);


	// 这四行是固定的
	curl_setopt($curlObj,CURLOPT_COOKIESESSION,true);
	curl_setopt($curlObj,CURLOPT_COOKIEFILE,"cookiefile");// cookie缓存文件位置(不是要自己设置 cookie的具体内容，是要设置 cookie 的存储和读取的文件名)
	curl_setopt($curlObj,CURLOPT_COOKIEJAR,"cookiefile");
	curl_setopt($curlObj,CURLOPT_COOKIE,session_name() . '=' . session_id());

	curl_setopt($curlObj,CURLOPT_HEADER,0);// 禁止输出头信息
	curl_setopt($curlObj,CURLOPT_FOLLOWLOCATION,1);// 设置让CURL支持页面链接跳转
	curl_setopt($curlObj,CURLOPT_POST,1);// POST方式访问
	curl_setopt($curlObj,CURLOPT_POSTFIELDS,$data);

	$header = array(
		"appliaction/x-www-form-urlencoded;charset=utf-8",
		"Content-length:".strlen($data)
	);
	curl_setopt($curlObj,CURLOPT_HTTPHEADER,$header);
	curl_exec($curlObj);// 执行

	$curl_1 = "https://www.imooc.com/u/index/allcourses";
	//$curl_1 = "http://www.imooc.com/space/index";
	$curl_1 = "http://www.imooc.com/u/7617803";
	curl_setopt($curlObj,CURLOPT_URL,$curl_1);
	curl_setopt($curlObj,CURLOPT_POST,0);// 非POST方式，转换为GET方式

	$header = array("Content-type:text/html");
	curl_setopt($curlObj,CURLOPT_HTTPHEADER,$header);

	$output = curl_exec($curlObj);
	curl_close($curlObj);
	echo $output;
		
}




















































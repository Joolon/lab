<?php
//检测是否已安装系统
if (file_exists("./Install/") && !file_exists("./Install/install.lock")) {
    if ($_SERVER['PHP_SELF'] != '/index.php') {// 当前文件相对于网站根目录的位置地址
        exit("请在域名根目录下安装,如:<br/> www.xxx.com/index.php 正确 <br/>  www.xxx.com/www/index.php 错误");
    }
    header('Location:/Install/index.php');
    exit();
}
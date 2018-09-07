<?php
/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2016/10/14
 * Time: 11:29
 */

/**
 * 定义一个自动加载类文件方法
 * 分析：加载类时如果找不到类则根据该类的命名空间加载该类的文件
 */
spl_autoload_register(function ($class) {
    // $class 其实是命名空间
    if ($class) {
        // $file = str_replace('\\', '/', $class);// 命名空间分隔线与服务器系统目录分隔线对应
        $file = $class . '.class.php';
        if (file_exists($file)) {
            include $file;
        }else{
            throw new Exception('Class Not Found');
        }
    }
});
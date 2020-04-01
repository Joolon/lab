<?php
/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2016/10/14
 * Time: 11:51
 */

return array(
    //'配置项'=>'配置值'
    /* 数据库设置 */
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  '1localhost', // 服务器地址
    'DB_NAME'               =>  'wms1',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'Admin.123456',          // 密码
    'LAYOUT_ON'             =>   true,
    'LAYOUT_NAME'           =>  'Common/layout',
    'REDIS_HOST'            =>  '127.0.0.1',
    'REDIS_PORT'            =>  '6379',
    'URL_CASE_INSENSITIVE'  =>   false,
    'LOG_RECORD'            =>   false,   // 默认不记录日志
    'LOG_TYPE'              =>  'File', // 日志记录类型 默认为文件方式
    'LOG_LEVEL'             =>  'EMERG,ALERT,CRIT,ERR',// 允许记录的日志级别
    'LOG_EXCEPTION_RECORD'  =>   false,    // 是否记录异常信息日志

    // 超级管理员用户 数据库:admin  用户名:admin 密码:abc123
    // 超级用户角色   数据库:admin  用户名:root  密码:root
    'MONGODB_HOST'          => '47.107.183.46',
    'MONGODB_USER'          => 'root',
    'MONGODB_PWD'           => 'root',
    'MONGODB_PORT'          => '27017',
    'MONGODB_DB'            => 'test',

    'USER_INFO'             => array(
        'URL_MODEL'     => 1,
        'MENU_STYLE'    => 'SPECIAL'
    )
);
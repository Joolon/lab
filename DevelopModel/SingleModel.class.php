<?php
namespace DevelopModel;

/**
 * 单例模式
 * Created by JoLon.
 * User: JoLon
 * Date: 2016/9/26
 * Time: 17:50
 */

class SingleModel
{
    /**
     * 单例模式说明
     * 1、某个类只能有一个实例（单例）
     * 2、必须自行创建实例（不能外部创建，否则可多次创建）
     * 3、必须自行向整个系统提供这个实例
     * 4、必须声明一个私有的静态变量保存这个实例
     * 5、构造函数和克隆函数必须私有化（防止外部new创建实例）
     * 6、getInstance()方法必须声明为公共，以便外部调用
     * 7、严格意义的单例不存在，PHP解释运行机制会在页面执行完后自动释放内存，所有资源会被回收（PHP特有）
     *     PHP在所有变量都是页面级别的（全局变量、静态变量等等），页面执行完后会自动清空
     *
     * 应用场景
     * 1、最常用的地方是数据库连接（控制资源使用）
     * 2、应用多个页面同时访问一个类
     */
    private static $_instance = null;
    public static $test = null;
    public $name = null;

    private function __construct()
    {
    }// 私有化构造函数

    private function __clone()
    {
    }// 私有化克隆函数


    // 静态的实例访问方法
    public static function getInstance()
    {
        // 没有实例则自行创建实例
        if (!self::$_instance instanceof self) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    // 其他方法设置成公共的
    public function setName($name)
    {
        $this->name = $name;
        self::$test = $name . '-test';
    }

    public function getName()
    {
        return $this->name;
    }

    public static function test()
    {
        return self::$test;
    }

}

// 使用方法
SingleModel::getInstance()->setName('zhangsan');
echo SingleModel::getInstance()->test();

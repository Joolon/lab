<?php
error_reporting(E_ALL | E_STRICT);

/**
 * PHP-OOP_VAR 让php的变量变成一个对象
 *      原理：通过 PHP 类中的魔法方法调用用户自定义的函数（或类的方法）来达到链式调用。
 *
 *
 * @version 0.0.1
 * @author momodev
 * @website http://momodev.blog.51cto.com
 * @license GPL v3 - http://vork.us/go/mvz5
 */
Abstract Class Base_OOP_VAR
{

    /**
     * 追溯数据，用来进行调试
     * @var array
     */
    private $_trace_data = array();

    /**
     * 保存可用的方法列表
     * @var array
     */
    protected $_methods = array();

    /**
     * 数据本身
     * @var null
     */
    protected $data;

    /**
     * 初始化变量
     * @param var
     * @return mixed
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->_trace_data['__construct'] = $data;
        return $this->data;
    }

    /**
     * 魔术方法，当试图对对象进行打印如 echo 或print的时候，调用这个方法
     *
     * 比如:
     * $a = new stdClass;
     * echo $a;
     * 等价于 echo $a->__toString();
     *
     * @return mixed $data
     */
    public function __toString()
    {
        if (is_int($this->data) || is_float($this->data))
            $this->data = (string)$this->data;
        return $this->data;
    }

    /**
     *
     * 魔术方法，当试图调用一个不存在的方法时，这个函数会接管这个请求
     *
     * 比如
     * $a= new stdClass;
     * $a->output();
     * 等价于
     * $a->__call("output");
     *
     * @param string $name
     * @param string $args
     * @return object
     */
    public function __call($name, $args)
    {
        $this->valid_func($name);
        if (!$args) $args = $this->data;
        $this->data = call_user_func($name, $args);// 调用的是已经定义的函数
        $this->_trace_data[$name] = $this->data;
        return $this;
    }

    /**
     * 检查方法是否是有效的
     * @param string $name
     * @throws Exception
     */
    private function valid_func($name)
    {
        if (!in_array($name, $this->_methods)) {
            throw new Exception("invalid method");
        }
    }

    /**
     * 对数据进行追溯
     * 比如
     * $a = new String(" Hello World");
     * $a->trim()->strlen();
     * 在调用trim的时候，实际上把前后的空格给去掉了，所以数据是
     * Hello World
     * 在调用strlen的时候
     * 得到了一个字符串长度的值
     * 追溯数据方便检查在哪个环节数据出现了问题
     *
     */
    public function trace()
    {
        echo "<pre>";
        var_dump($this->_trace_data);
        echo "</pre>";
    }

}

/**
 * ex. 怎么来使用这个抽象类
 *
 * 声明一个字符串对象
 */
class OOP_String extends Base_OOP_VAR
{
    //添加可用的方法
    protected $_methods = array('trim','strlen','gettype');
}

//使用这个对象
$a = new OOP_String(" Hello world");
echo $a->trim('1')->strlen('2')->gettype('3');
$a->trace();

echo 1;exit;

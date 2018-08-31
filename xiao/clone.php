<?php

/**
 * Created by JoLon.
 * User: ver
 * Date: 15-9-1
 * Time: 下午7:58
 */
class TestClone
{
    private $name;
    private $age;

    function setName($name)
    {
        $this->name = $name;
    }

    function getName()
    {
        return $this->name;
    }

    function setAge($age)
    {
        $this->age = $age;
    }

    function getAge()
    {
        return $this->age;
    }
}


$drone1 = new TestClone;
// 赋值的方法只是两者指向同一个对象
//$drone2 = $drone1;
// 克隆方法可以复制一个对象，对他们的操作是两个不同对象的操作
// 和new 关键字不同的是clone会把设置的属性值一并赋予
$drone1->setName('lisi');
$drone1->setAge(12);
$drone2 = clone $drone1;
$drone2->setName('wangwu');
echo $drone2->getName();
echo $drone2->getAge();

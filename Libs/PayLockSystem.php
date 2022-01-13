<?php
/**
 * LockSystem.php
 *
 * PHP 锁机制
 *  -策略模式：提供一系列算法，让算法随着客户的需求变化而变化。
 *  -工厂模式：实例化的对象随着传入参数的变化而变化，要实例化哪个类就传入哪个类的名称。
 */
class LockSystem
{
    const LOCK_TYPE_DB = 'SQLLock';// 性能中等
    const LOCK_TYPE_FILE = 'FileLock';// 性能最低
    const LOCK_TYPE_MEMCACHE = 'MemcacheLock';// 性能最高

    private $_lock = null;
    private static $_supportLocks = array('FileLock', 'SQLLock', 'MemcacheLock');

    public $message_error = null;

    public function __construct($type, $options = array())
    {
        if (false == empty($type)) {
            $this->createLock($type, $options);
        }
    }

    /**
     * 实例化锁句柄
     * @param $type
     * @param array $options
     * @return bool
     */
    public function createLock($type, $options = array())
    {
        if (false == in_array($type, self::$_supportLocks)) {
            $this->message_error = "not support lock of ${type}";
            return false;
        }
        $this->_lock = new $type($options);
        return true;
    }

    /**
     * 获取锁并设置锁定
     * @param $key
     * @param int $timeout
     * @return mixed
     */
    public function getLock($key, $timeout = ILock::EXPIRE)
    {
        if (false == $this->_lock instanceof ILock) {
            $this->message_error = 'false == $this->_lock instanceof ILock';
            return false;
        }

        if(false == $this->_lock->getLock($key, $timeout)){
            $this->message_error = $this->_lock->getError();
            return false;
        }

        return true;
    }

    /**
     * 释放锁
     * @param $key
     * @return mixed
     * @throws Exception
     */
    public function releaseLock($key)
    {
        if (false == $this->_lock instanceof ILock) {
            $this->message_error = 'false == $this->_lock instanceof ILock';
            return false;
        }

        if(false == $this->_lock->releaseLock($key)){
            $this->message_error = $this->_lock->getError();
            return false;
        }

        return true;
    }
}


/**
 * Interface ILock
 * 定义锁接口
 */
interface ILock
{
    const EXPIRE = 5;// 锁定默认有效期

    /**
     * 获得锁
     * @param $key
     * @param int $timeout
     * @return mixed
     */
    public function getLock($key, $timeout = self::EXPIRE);

    /**
     * 释放锁
     * @param $key
     * @return mixed
     */
    public function releaseLock($key);

    /**
     * 设置错误信息
     * @param $msg
     */
    public function setError($msg);

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError();

}


/**
 * Class FileLock
 * 文件锁
 */
class FileLock implements ILock
{
    private $_fp;
    private $_single;
    private $_lockPath;

    private $_errorMsg;

    public function __construct($options)
    {
        // 设定文件锁文件存放位置
        if (isset($options['path']) && is_dir($options['path'])) {
            $this->_lockPath = $options['path'] . '/';
        } else {
            $this->_lockPath = '/tmp/';
        }

        $this->_single = isset($options['single']) ? $options['single'] : false;
    }

    /**
     * 获得锁
     * @param $key
     * @param int $timeout
     * @return bool
     * @throws Exception
     */
    public function getLock($key, $timeout = self::EXPIRE)
    {
        $file = md5(__FILE__ . $key);
        $this->_fp = fopen($this->_lockPath . $file . '.lock', "w+");
        if (true || $this->_single) {
            $op = LOCK_EX + LOCK_NB;
        } else {
            $op = LOCK_EX;
        }
        if (false == flock($this->_fp, $op, $a)) {
            return false;
        }

        return true;
    }

    /**
     * 释放锁
     * @param $key
     * @return mixed
     */
    public function releaseLock($key)
    {
        flock($this->_fp, LOCK_UN);
        fclose($this->_fp);
        return true;
    }

    /**
     * 设置错误信息
     * @param $msg
     */
    public function setError($msg)
    {
        $this->_errorMsg = $msg;
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->_errorMsg;
    }
}


/**
 * Class SQLLock
 * 数据库锁
 */
class SQLLock implements ILock
{
    private $_db;

    private $_errorMsg;

    public function __construct($options)
    {
        $this->_db = new mysqli();
    }

    /**
     * 获得锁
     * @param $key
     * @param int $timeout
     * @return bool|mysqli_result
     */
    public function getLock($key, $timeout = self::EXPIRE)
    {
        $sql = "SELECT GET_LOCK('{$key}','{$timeout}')";
        $res = $this->_db->query($sql);
        return $res;
    }

    /**
     * 释放锁
     * @param $key
     * @return bool|mysqli_result
     */
    public function releaseLock($key)
    {
        $sql = "SELECT RELEASE_LOCK('{$key}')";
        return $this->_db->query($sql);
    }

    /**
     * 设置错误信息
     * @param $msg
     */
    public function setError($msg)
    {
        $this->_errorMsg = $msg;
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->_errorMsg;
    }
}


/**
 * Class MemcacheLock
 * 缓存锁
 */
class MemcacheLock implements ILock
{
    private $_memcache;

    private $_errorMsg;

    public function __construct($options)
    {

        $this->_memcache = new Memcache();
    }

    /**
     * 获取锁
     * @param $key
     * @param int $timeout
     * @return bool
     */
    public function getLock($key, $timeout = self::EXPIRE)
    {
        $waitTime = 20000;// 微妙，0.02秒
        $totalWaitTime = 0;
        $time = $timeout * 1000000;// 获取锁最长等待时间，

        // while循环获取锁
        while ($totalWaitTime < $time && false == $this->_memcache->add($key, 1, $timeout)) {
            usleep($waitTime);// 以微妙间隔等待
            $totalWaitTime += $waitTime;
        }

        if ($totalWaitTime >= $time) {// 获取锁超时
            return false;
        } else {
            return true;
        }
    }

    /**
     * 释放锁
     * @param $key
     * @return mixed
     */
    public function releaseLock($key)
    {
        $this->_memcache->delete($key);
        return true;
    }

    /**
     * 设置错误信息
     * @param $msg
     */
    public function setError($msg)
    {
        $this->_errorMsg = $msg;
    }

    /**
     * 获取错误信息
     * @return mixed
     */
    public function getError()
    {
        return $this->_errorMsg;
    }
}
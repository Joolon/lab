<?php

namespace Libs;

/**
 * Created by JoLon.
 * 文件处理方法类
 * User: JoLon
 * Date: 2016/10/12
 * Time: 8:55
 */
class FileLockDeal {


    /**
     * lockThisFile：获得独享锁
     * @param string  $tmpFileStr 用来作为共享锁文件的文件名（可以随便起一个名字）
     * @param boolean $lockType   锁类型，缺省为false(非阻塞型，也就是一旦加锁失败则直接返回false),设置为true则会一直等待加锁成功才返回
     * @return mixed 如果加锁成功，则返回锁实例(当使用 unlockThisFile 方法的时候需要这个参数)，加锁失败则返回false.
     */
    public function lockThisFile($tmpFileStr, $lockType = false){
        if($lockType == false)
            $lockType = LOCK_EX | LOCK_NB;
        $can_write = 0;
        $lockFP    = @fopen($tmpFileStr.".lock", "w");
        if($lockFP){
            $can_write = @flock($lockFP, $lockType);
        }
        if($can_write){
            return $lockFP;
        }else{
            if($lockFP){
                @fclose($lockFP);
                @unlink($tmpFileStr.".lock");
            }

            return false;
        }
    }

    /**
     * unlockThisFile：对先前取得的锁实例进行解锁
     * @param resource $fp         lockThisFile 方法的返回值
     * @param string   $tmpFileStr 用来作为共享锁文件的文件名（可以随便起一个名字）
     */
    public function unlockThisFile($fp, $tmpFileStr){
        @flock($fp, LOCK_UN);
        @fclose($fp);
        @unlink($tmpFileStr.".lock");
    }


}


// 使用举例
$fileLockDeal = new FileLockDeal();
$tmpFileStr   = "/tmp/mylock.loc";

// 等待取得操作权限,如果要立即返回则把第二个参数设为false.
$lockHandle = $fileLockDeal->lockThisFile($tmpFileStr, true);
if($lockHandle){
    // 在这里进行所有需要独占的事务处理。
    // ... ...
    // 事务处理完毕。
    $fileLockDeal->unlockThisFile($lockHandle, $tmpFileStr);
}


// 使用场景
// 文件锁的可能应用场景为:
// 1.限制并发多进程或多台服务器需要对同一文件进行访问和修改;
// 2.对参与文件I/O的进程队列化和人为阻塞;
// 3.在业务逻辑中对文件内容进行守护;
// 4.可以对文件是否可以获取锁，来判断多进程系统中，当前进程是否占用---性能太低，Redis 就很完美
// 5.线程安全：多进程系统中，编辑同一文件


// 进程锁：
//      与文件锁不同的是,进程锁并不用于阻止对文件的I/O,而是用于防止多进程并发造成的预期之外的后果.
//      所以需要在多进程并发时将其队列化,即在某进程的关键逻辑执行结束前阻塞其他并发进程的逻辑执行。
// 1.memcached的过期时间不可少于程序运行的实际时间,因此建议稍微长一点,逻辑执行结束后进行回收;
// 2.在非阻塞模型中,若状态被判定为false,应该将进程中止或block,避免业务逻辑的继续执行;
// 3.在实际应用中,设置一个重试时间很有必要,这样可以很大程度上减少针对memcached的大量I/O并发,减轻服务器压力;
//
// 文件锁：
//      锁机制之所以存在是因为并发导致的资源竞争，为了确保操作的有效性和完整性，可以通过锁机制将并发状态转换成串行状态。
//      作为锁机制中的一种，PHP的文件锁也是为了应对资源竞争。假设一个应用场景，在存在较大并发的情况下，
//      通过fwrite向文件尾部多次有序的写入数据，不加锁的情况下会发生什么？多次有序的写入操作相当于一个事务，我们此时需要保证这个事务的完整性。
// 如果我们有两个程序同时向某个文件中写入数据，为了保证数据的完整性，可以加一个文件锁，先让程序1执行，程序1执行完后，解锁，再让程序2执行。
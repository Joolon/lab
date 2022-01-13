<?php
set_time_limit(0);
use DevelopModel\RedisHandle;

$redis = RedisHandle::getRedis();

$key = 'test_sec_kill';

/**
 * Redis 消息队列
 * Redis 处理简单的消息队列，Redis 能满足大多场景，但是它的设计初衷并不是处理队列，主要是用来缓存的
 *      瓶颈：多个程序、多个服务器操作同一个或多个队列时，Redis 就会力不从心了。（利用更专业的消息队列处理程序 RabbitMQ）
 *
 *
 * @link https://blog.csdn.net/dd18709200301/article/details/79077839
 * Redis 与 RabbitMQ 消息队列的区别：
 *
 *  可靠性：redis 消息发布后数据即消失，rabbitmq 有消息消费确认机制所以更可靠，每个消息只能被处理一次
 *  实时性：redis 非常优越，高效的缓存服务器
 *  消费者负载均衡：rabbitmq 的消息消费确认机制可以根据消费能力而调整它的负载，redis 将消息发送给每个订阅者，属于广播模式，无负载均衡能力
 *  持久性：redis 是针对整个redis缓存的内容持久化，而rabbitmq可以选择性的持久化每条消息
 *  队列监控：rabbitmq有后台监控平台，可以查看到所有队列的详细情况，redis没有
 *
 *
 * 总结：
 *      redis ：轻量级，低延迟，高并发，低可靠性；
 *      rabbitmq ：重量级，高可靠，异步，不保证实时；
 *
 *     Redis 主攻缓存，RabbitMQ 专业的AMQP协议队列程序
 */
for($i = 0;$i < 3000 ;$i ++){

    $len = $redis->lLen($key);

    if($len >= 20000){
        echo $i.'   秒杀失败';echo '<br/>';
    }else{
        $redis->lPush($key,rand(0,100000));
    }
}

echo 'sss';exit;

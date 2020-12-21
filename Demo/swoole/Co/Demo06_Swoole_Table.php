<?php

namespace Demo\swoole\Co;

use Swoole;
use Swoole\Coroutine;
use Swoole\Table;

/**
 * swoole 内置的高性能内存共享
 * Class Demo06_Swoole_Table
 * @package Demo\swoole\Co
 */
class Demo06_Swoole_Table {

    /**
     * 定时器的使用
     */
    public function cookByCo(){
        $table = new Table(10);
        $table->column('fd', Table::TYPE_INT);
        $table->column('reactor_id', Table::TYPE_INT);
        $table->column('data', Table::TYPE_STRING, 64);
        $table->create();

        $serv = new Swoole\Server('127.0.0.1', 9501);
        $serv->set(['dispatch_mode' => 1]);
        $serv->table = $table;

        /**
         * telnet 1270.0.1 9501
         * {"st_action":"set","fd":"1","reactor_id":"2","data":"1212"}
         * {"st_action":"get"}
         */
        $serv->on('receive', function ($serv, $fd, $reactor_id, $data_json) {
            $st_action_action = $st_key = null;

            $data_arr = json_decode($data_json, true);
            if(isset($data_arr['st_action'])){
                $st_action_action = $data_arr['st_action'];
                unset($data_arr['st_action']);
            }

            if(isset($data_arr['st_key'])){
                $st_key = $data_arr['st_key'];
                unset($data_arr['st_key']);
            }

            if (empty($st_key)) $st_key = $fd;

            if ($st_action_action == 'get') {//get
                $info = $serv->table->get($st_key);

                if($info){
                    $serv->send($fd, json_encode($info));
                }else{
                    $serv->send($fd, "NULL");
                }
            }if ($st_action_action == 'getAll') {//getAll
                // 循环遍历获取所有数据
                $list = [];
                foreach($serv->table as $n_key => $n_row){
                    $list[$n_key] = $n_row;
                }

                if($list){
                    $return_data = [
                        'st_size' => $serv->table->size,
                        'st_memorySize' => $serv->table->memorySize,
                        'st_count' => $serv->table->count(),
                        'st_list' => $list
                    ];

                    $serv->send($fd, json_encode($return_data));
                }else{
                    $serv->send($fd, "NULL");
                }
            } elseif ($st_action_action == 'set') {//set
                $ret = $serv->table->set($st_key, $data_arr);
                if ($ret === false) {
                    $serv->send($fd, "ERROR");
                } else {
                    $serv->send($fd, "OK");
                }
            } else {
                $serv->send($fd, "command error.");
            }
        });


        $serv->start();


    }
}
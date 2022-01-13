<?php
namespace Libs;

class PushDataQueueList
{

    /**
     * 创建表格
     */
    public function createQueueTable(){

        $sql = "
            CREATE TABLE IF NOT EXISTS `pur_push_data_queue_list` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `record_number` varchar(50) DEFAULT NULL COMMENT '操作记录编号',
              `record_type` varchar(100) DEFAULT NULL COMMENT '操作记录类型',
              `add_user` varchar(20) DEFAULT NULL COMMENT '操作人',
              `add_time` datetime DEFAULT NULL COMMENT '操作时间',
              `request_host` varchar(255) DEFAULT NULL COMMENT '请求主机地址',
              `request_url` varchar(255) DEFAULT NULL COMMENT '请求URL',
              `request_header_param` text COMMENT '请求header参数',
              `push_data` text COMMENT '推送的数据',
              `push_status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '推送状态（默认0.为推送,1.已推送）',
              `push_time_plan` datetime DEFAULT NULL COMMENT '计划推送时间',
              `push_times` int(11) unsigned DEFAULT '0' COMMENT '推送次数',
              `result_status` varchar(50) DEFAULT NULL COMMENT '推送结果状态',
              `result_detail` text COMMENT '推送结果明细信息',
              `result_time` datetime DEFAULT NULL COMMENT '实际推送时间',
              `result_remark` text COMMENT '推送结果备注',
              PRIMARY KEY (`id`),
              KEY `record_number` (`record_number`),
              KEY `record_type` (`record_type`),
              KEY `push_status` (`push_status`),
              KEY `result_status` (`result_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ";

    }

    /**
     * 添加操作日志
     * @param array $data   要保存的数据
     * @return bool  true.成功,false.失败
     *
     * @example
     *      $data = array(
     *          id                      => 目标记录编号（int|string）
     *          type                    => 操作类型（关联模型）
     *          add_user                => 添加人（默认当前用户）
     *          add_time                => 操作时间（exp.2018-11-23 16:16:16  默认当前时间）
     *          request_host            => 请求主机地址
     *          request_url             => 请求URL
     *          request_header_param    => 请求header参数
     *          push_data               => 推送的数据
     *          push_time_plan          => 计划推送时间
     *      )
     */
    public static function insertOneQueue($data){
        $model                  = new self();
        $model->record_number   = isset($data['id'])?$data['id']:'';
        $model->record_type     = isset($data['type'])?$data['type']:'';
        $model->add_user        = (isset($data['user']) AND $data['user'])?$data['user']:User::getActive()->username;
        $model->add_time        = isset($data['time'])?$data['time']:date('Y-m-d H:i:s');
        $model->request_host            = isset($data['host'])?$data['host']:'';
        $model->request_url             = isset($data['url'])?$data['url']:'';
        $model->request_header_param    = isset($data['headers'])?$data['headers']:'';
        $model->push_data               = isset($data['push_data'])?serialize($data['push_data']):'';
        $model->push_time_plan          = isset($data['push_time_plan'])?$data['push_time_plan']:NULL;

        $status = $model->save(false);
        return $status;
    }


    /**
     * @param array $query  查询条件
     * @return bool|array   array.结果集，false.查询条件缺失     *
     *
     * @example
     *      $query = array(
     *          id          => 目标记录编号（int|string）
     *          type        => 操作类型（关联模型）
     *          content     => 改变的内容（简略信息,支持搜索）
     *          user        => 操作人（默认当前用户）
     *          is_show     => 标记日志类型（1.展示日志，2.非展示日志，默认 1）
     *     )
     */
    public static function queryLogs($query){
        $real_query = array();
        isset($query['id'])         AND $real_query['id'] = $query['id'];
        isset($query['type'])       AND $real_query['record_type'] = $query['record_type'];
        isset($query['content'])    AND $real_query['content'] = $query['content'];
        isset($query['user'])       AND $real_query['operator'] = $query['user'];
        isset($query['is_show'])    AND $real_query['is_show'] = $query['is_show'];

        if(empty($real_query)) return false;// 查询条件缺失

        $results = (new yii\db\Query())->select("*")
            ->from("change_log")
            ->where($real_query)
            ->all();

        return $results ? $results:array();
    }

}
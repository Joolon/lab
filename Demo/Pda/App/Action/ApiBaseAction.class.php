<?php

/**
 * API 请求基础类
 * Created by JoLon
 * User: JoLon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 20:11
 */
class ApiBaseAction extends BaseAction
{

    /**
     * AJAX返回组装的数据
     * @param $data
     */
    public function ajaxReturn($data){
        echo json_encode($data);
        exit;
    }

}
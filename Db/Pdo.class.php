<?php
/**
 * Created by JoLon.
 * User: JoLon
 * Date: 2016/8/29
 * Time: 11:31
 */

namespace Db;
class PdoDeal
{
    public function deal()
    {

        $pdo = new PDO('mysql:host=localhost;dbname=niceshop', 'root', 'root',
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        $sql = 'SELECT * FROM `tp_admin`';

        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_UPPER);

        $result = $pdo->query($sql);// 返回PDOStatement对象
        //$result->setFetchMode(PDO::FETCH_NUM);// 数字数组
        //$result->setFetchMode(PDO::FETCH_BOTH);// 两者都有（缺省）
        //$result->setFetchMode(PDO::FETCH_OBJ);// 对象的形式
        $result->setFetchMode(PDO::FETCH_ASSOC);// 关联数组
        $datas = $result->fetchAll();
    }

}
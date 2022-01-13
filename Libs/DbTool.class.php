<?php

namespace Libs;


/**
 * Created by JoLon.
 * 数据库处理的工具类
 * User：Jolon
 * Time: 15-2-9 下午9:13
 */
class DbTool {

    /**
     * 读取 数据库中的所有表和字段名称
     */
    public function getDbColumns(){
        $list = $this->purchase_db->select('table_name')
            ->where("table_schema='yibai_purchase'")
            ->get("information_schema.tables")
            ->result_array();

        foreach($list as $value){
            $columns = $this->purchase_db->select('column_name')
                ->where("table_schema='yibai_purchase'")
                ->where('table_name', $value['table_name'])
                ->get("information_schema.columns")
                ->result_array();

            if($columns){
                foreach($columns as $v2){
                    if(stripos($v2['column_name'], 'product_img_url') !== false){
                        echo $value['table_name'].'.'.$v2['column_name'];
                        echo '<br/>';
                    }
                }
            }
        }
        echo 1;
        exit;

    }

    /**
     * 把上传的文件保存到数据库中
     */
    public static function saveFileToDB()
    {
        /*
         * 保存文件到数据库（以保存内容的形式）
         */
        if ($_FILES['file']['error'] == 0) {
            $content = mysql_escape_string(self::readFile("upload/" . $_FILES["file"]["name"]));
            $name = mysql_escape_string($_FILES['file']['name']);
            $size = mysql_escape_string($_FILES['file']['size']);
            $type = mysql_escape_string($_FILES['file']['type']);

            $conn = mysql_connect("localhost", "root", "123");
            mysql_select_db('test');
            $sql = "INSERT INTO files(name,  size,type, content)
                VALUES ('$name', $size, '$type','$content')";

            mysql_query($sql, $conn);
            mysql_close($conn);
        }
    }
}

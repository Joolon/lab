<?php

namespace ImageLib;

class ImageTool
{
    public function __construct()
    {

    }

    public static function s()
    {

        $id = mysql_escape_string(trim($_GET['id']));

        $conn = mysql_connect('localhost', 'root', 'root');
        mysql_select_db('test');
        $sql = "select * from image where id='$id'";
        $result = mysql_query($sql, $conn);

        if (!$result)
            die("读取图片失败！");
        $num = mysql_num_rows($result);// 计算结果集中的行数
        if ($num < 1)
            die("暂无图片");

        $row = mysql_fetch_array($result);
        echo "<img src='" . $row['photo'] . "' />";//显示图片

        $data['picture'] = mysql_result($result, 0, 'picture');// 返回从 0 行开始字段名为 picture 的列
        $data['type'] = mysql_result($result, 0, 'type');// 返回从 0 行开始字段名为 type 的列
        mysql_close($id);
        header("Content-type: {$data['type']}");
        echo $data;
    }


    public function getImageInfo()
    {
        // 返回图像资源
        // getimagesize( filename )
        // imagecreatetruecolor()
        // imagecreatefromjpeg() // .jpg格式图片
        // imagecreatefrompng()  // .png格式图片
        // imagesx( resource img );// 参数是资源类型 resource
        // imagesy( resource img )

        $image_url = "images/773271E845FB.jpg";

        $image_arr = getimagesize($image_url);
        /*
         $image_arr = array(7) {
              [0]=>      int(600)  // 宽度
              [1]=>      int(479)  // 高度
              [2]=>      int(2)
              [3]=>      string(24) "width="600" height="479""
              ["bits"]=>      int(8)
              ["channels"]=>      int(3)
              ["mime"]=>      string(10) "image/jpeg"
        }
         */

        $img = imagecreatefromjpeg($image_url);

        $image['x'] = imagesx($img);// 参数是资源类型 resource
        $image['y'] = imagesy($img);

        var_dump($image);
    }

    public function saveToDb()
    {
        $conn = @mysql_connect("localhost", "root", "123");
        @mysql_select_db("test");
        $content_photo = mysql_escape_string(file_get_contents($_FILES['photo']['tmp_name']));// 必须使用tmp_name属性
        $type = $_FILES['photo']['type'];
        $sql = "INSERT INTO photo(t,photo)
        VALUES('$type','$content_photo')";
        @mysql_query($sql, $conn);
    }


}

?>
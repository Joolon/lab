<?php
namespace Libs;

/**
 * Created by JoLon.
 * 文件处理方法类
 * User: JoLon
 * Date: 2016/10/12
 * Time: 8:55
 */

class FileDeal
{

    /**
     * 在指定文件夹中创建文件
     * @param $file_path
     * @param $file_name
     * @return bool
     */
    public static function createFile($file_path, $file_name)
    {

        if (!file_exists($file_path . $file_name)) {
            if (@mkdir($file_path . $file_name, 0777, true)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    /**
     * 读取文件的字节数或行数
     * @param string $file_path
     * @param int $type
     * @param int $length
     * @return array|string
     */
    public static function readFile($file_path = "upload/a.doc", $type = 1, $length = 0)
    {
        /*
         * readfile($file_path)读取文件并写入到输出缓冲，返回字节的个数
         * 缺点：无论如何都会显示文件中的内容，不能控制显示字符个数
         */
        $content = '';

        /*
         * 可以控制读取字符个数
         * fopen()打开不存在的文件则会创建该文件
         */
        if (!$handle_file = @fopen($file_path, "r")) {
            echo '不能打开文件：' . $file_path;
            exit;
        }

        if ($type == 1) {// 设置为读取字节数
            if (!is_int($length) || $length <= 0) {
                $content = fread($handle_file, filesize($file_path));// 读取整个文件
            } else {
                $content = fread($handle_file, $length);// 读取字$length节
            }
        } elseif ($type = 2) {// 设置为读取行数
            // echo fgetc()      // 读取单个字符,文件指针会移动到下一个字符
            if (!is_int($length) || $length <= 0) {
                while (!feof($handle_file)) {   // 函数检查是否已到达 "end-of-file" (EOF)
                    $content[] = fgets($handle_file); // 读取单行，文件指针会移动到下一行。
                }
            } else {
                $i = 0;
                while (!feof($handle_file) AND $i < $length) {
                    $content[] = fgets($handle_file);
                    $i++;
                }
            }
        }
        fclose($handle_file);// 关闭文件释放缓存资源

        return $content;
    }

    /**
     * 注意：文件权限
     * fopen()打开不存在的文件则会创建该文件(所以可以用来创建文件)
     */
    public static function writeFile($file_name = "upload/testfile.txt", $content = "Bill Gatess\n")
    {
        // 判斷文件是否可写
        if (is_writable($file_name)) {
            // 打开文件（可设置读写模式打开）
            if (!$handle_file = @fopen($file_name, 'w')) {
                echo '不能打开文件：' . $file_name;
                exit;
            }

            if (fwrite($handle_file, $content) === false) {
                echo '不能写入到文件：' . $file_name;
                exit;
            }
            echo $content . '成功写入到文件：' . $file_name . '(文件大小' . filesize($file_name) . ')';
            fclose($handle_file);// 不关闭文件可以多次写入
        } else {
            echo '不可写文件：' . $file_name;
            exit;
        }
    }

	/**
	 * 下载一个远程文件到指定的 文件中
	 * @param string $ap_fileName 文件
	 * @param string $url 远程文件
	 */
	public static function downFile($ap_fileName,$url){
		$dir = dirname($ap_fileName);
		self::createFile($dir);

		$fp = @fopen($ap_fileName,'w+');
		$content =  file_get_contents($url);
		fwrite($fp, $content);

	}

	/**
	 * 下载本地文件目录中的一个文件 到客户端
	 * @param string $file_dir  文件路径
	 * @param string $file_name 文件名
	 */
    public static function downloadFile($file_dir = "upload/", $file_name = "a.doc")
    {
        $file_path = $file_dir . $file_name;// 要下载的文件名
        if (file_exists($file_path)) {
            if (!$handle_file = @fopen($file_path, "r")) {
                //输入文件标签
                header("Content-type:application/octet-stream ");
                header("Accept-Ranges:bytes ");
                header("Accept-Length:" . filesize($file_path));
                header("Content-Disposition:   attachment;   filename= " . $file_name);

                echo fread($handle_file, filesize($file_path));// 输出文件内容
                fclose($handle_file);
                exit;
            } else {
                echo '不能打开文件：' . $file_path;
                exit;
            }
        } else {
            echo "文件找不到：" . $file_path;
            exit;
        }
    }


    public static function uploadFile()
    {
        header('Content-type: text/html; charset=utf8');

        /*
         * 设置限制条件
         * 上传文件有危险：应该设置可信用户上传权限
         */
        if (!(($_FILES['file']['type'] == 'image/gif'
                || $_FILES['file']['type'] == 'image/jpeg'
                || $_FILES['file']['type'] == 'image/pjpeg')
            && $_FILES["file"]["size"] < 20000)
        ) {
            return array(
                'code' => 'N',
                'msg' => 'Invalid file！'
            );
        }

        // 名字和表单的name="file"的input相同
        if (($_FILES["file"]["size"] < 20000)) {
            if ($_FILES['file']['error'] > 0) {
                return array(
                    'code' => 'N',
                    'msg' => 'Error:' . $_FILES['file']['error'] . "<br/>"
                );
            } else {

                /*
                 * 字节大小单位：字节数
                 * 一个英文字母（大小写一样）占一个字节，文本结束符占一个字节，中文占3个字节，空格占一个字节
                 */
                $file_info = array();
                $file_info['name'] = $_FILES['file']['name'];
                $file_info['size'] = $_FILES['file']['size'];
                $file_info['tmp_name'] = $_FILES['file']['tmp_name'];
                $file_info['type:'] = $_FILES['file']['type'];
                $file_info['path'] = 'upload/' . $_FILES["file"]["name"];

                // 如果不存在，则把文件拷贝到指定的文件夹
                if (file_exists('upload/' . $_FILES["file"]["name"])) {
                    $file_info['error'] = $_FILES["file"]["name"] . " already exists. ";
                } else {
                    move_uploaded_file($_FILES["file"]["tmp_name"], 'upload/' . $_FILES["file"]["name"]);// 移动文件,可以重命名
                }
                return array(
                    'code' => 'Y',
                    'msg' => $file_info,
                );
            }
        } else {
            return array(
                'code' => 'N',
                'msg' => 'Invalid file！'
            );
        }
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


    /**
     * 删除文件夹中的内容(文件和子文件夹)
     * @param $directory
     */
    public function deleteDir($directory)
    {
        $handle_dir = @opendir($directory);
        while ($file_name = @readdir($handle_dir)) {
            if ($file_name != "." && $file_name != "..") {// .代表当前目录 ..代表上级目录
                $full_path = $directory . "/" . $file_name;

                if (!is_dir($full_path)) {
                    @unlink($full_path);// 删除文件
                } else {
                    @$this->deleteDir($full_path);// 回调
                }
            }
        }
        closedir($handle_dir);
    }


    /**
     * 删除指定的文件夹下所有的文件（递归删除）
     * @param string $dirName  文件夹路径
     * @return int $fileCount 已删除的文件的个数
     */
    function delFileInDir($dirName){
        $fileCount = 0;
        if(file_exists($dirName) && $handle=opendir($dirName)){
            while(false!==($item = readdir($handle))){
                if($item!= "." && $item != ".."){
                    if(file_exists($dirName.'/'.$item) && is_dir($dirName.'/'.$item)){
                        $fileCount += delFileInDir($dirName.'/'.$item);
                    }else{
                        if(unlink($dirName.'/'.$item)){
                            $fileCount ++;
                        }
                    }
                }
            }
            closedir( $handle);
        }
        return $fileCount;
    }
	
    /**
     * 删除一个文件夹下面的子文件夹以及所有文件，并且删除根目录（递归删除）
     * @param $dirName
     * @return bool
     */
    public function removeDir($dirName){
        if(!is_dir($dirName)){
            return false;
        }
        $handle = @opendir($dirName);
        while(($file = @readdir($handle)) !== false){
            //判断是不是文件 .表示当前文件夹 ..表示上级文件夹 =2
            if($file != '.' && $file != '..'){
                $dir = $dirName.'/'.$file;
                if(is_dir($dir)){
                    $this->removeDir($dir);
                    @rmdir($dir);// 删除所有文件后删除文件夹
                }else{
                    @unlink($dir);
                }
            }
        }
        closedir($handle);
        @rmdir($dir);// 删除所有文件后删除文件夹
        return true;
    }
	
    /**
     * 删除一个文件夹下面的子文件夹以及所有文件，不会删除根目录（递归删除）
     * @param $dirName
     * @return bool
     */
    public function removeDir($dirName){
        if(!is_dir($dirName)){
            return false;
        }
        $handle = @opendir($dirName);
        while(($file = @readdir($handle)) !== false){
            //判断是不是文件 .表示当前文件夹 ..表示上级文件夹 =2
            if($file != '.' && $file != '..'){
                $dir = $dirName.'/'.$file;
                if(is_dir($dir)){
                    $this->removeDir($dir);
                    @rmdir($dir);// 删除所有文件后删除文件夹
                }else{
                    @unlink($dir);
                }
            }
        }
        closedir($handle);
        return true;
    }


    public function getInfoFromWeb($url)
    {
        $html = file_get_contents($url);
        return $html;
    }


}
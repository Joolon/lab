<?php
namespace Libs;

/**
 * CURL 获取远程文件 并保存到本地文件夹
 * User: Jolon
 * Date: 2018/8/21
 * Time: 10:35
 */
class ImageDeal
{


    /**
     * 创建图片文件夹
     * @param $path
     * @return bool
     */
    public function mkDirs($path){
        if(!is_dir($path)){
            $this->mkDirs(dirname($path));
            if(!mkdir($path, 0777)){
                return false;
            }else{
                chmod($path, 0777);
            }
        }
        return true;
    }


    /**
     * 获取本地图片的信息
     */
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

        return $image;
    }

    /**
     * 从一个网络地址获取图片保存到本地
     * @param $url
     * @param $file_path
     * @param string $filename
     * @return array|bool
     */
    public function getGrabImage($url,$file_path, $filename = "") {
        if ($url == "") return false;

        if ($filename == ""){
            $ext = strrchr($url, ".");
            $filename = $file_path.date('mdHis').$ext;
        }else{
            $filename = $file_path.$filename;
        }

        ob_start();//打开输出
        readfile($url);//输出图片文件
        $img    = ob_get_contents();//得到浏览器输出
        ob_end_clean();//清除输出并关闭
        $size   = strlen($img);//得到图片大小
        $fp2    = @fopen($filename, "a");
        fwrite($fp2, $img);//向当前目录写入图片文件，并重新命名
        fclose($fp2);

        return array('fileName' => $filename,'filePath' => $filename,'fileSize' =>  $size,'fileUrl' => $url);
    }


    /**
     * 生成缩略图
     * @param string     源图绝对完整地址{带文件名及后缀名}
     * @param string     目标图绝对完整地址{带文件名及后缀名}
     * @param int        缩略图宽{0:此时目标高度不能为0，目标宽度为源图宽*(目标高度/源图高)}
     * @param int        缩略图高{0:此时目标宽度不能为0，目标高度为源图高*(目标宽度/源图宽)}
     * @param int        是否裁切{宽,高必须非0}
     * @param int/float  缩放{0:不缩放, 0<this<1:缩放到相应比例(此时宽高限制和裁切均失效)}
     * @return boolean
     */
    public function img2thumb($src_img, $dst_img, $width = 75, $height = 75, $cut = 0, $proportion = 0)
    {
        if(!is_file($src_img))
        {
            return false;
        }
        $ot = pathinfo($dst_img, PATHINFO_EXTENSION);
        $otfunc = 'image' . ($ot == 'jpg' ? 'jpeg' : $ot);
        $srcinfo = getimagesize($src_img);
        $src_w = $srcinfo[0];
        $src_h = $srcinfo[1];
        $type  = strtolower(substr(image_type_to_extension($srcinfo[2]), 1));
        $createfun = 'imagecreatefrom' . ($type == 'jpg' ? 'jpeg' : $type);

        $dst_h = $height;
        $dst_w = $width;
        $x = $y = 0;

        /**
         * 缩略图不超过源图尺寸（前提是宽或高只有一个）
         */
        if(($width> $src_w && $height> $src_h) || ($height> $src_h && $width == 0) || ($width> $src_w && $height == 0))
        {
            $proportion = 1;
        }
        if($width> $src_w)
        {
            $dst_w = $width = $src_w;
        }
        if($height> $src_h)
        {
            $dst_h = $height = $src_h;
        }

        if(!$width && !$height && !$proportion)
        {
            return false;
        }
        if(!$proportion)
        {
            if($cut == 0)
            {
                if($dst_w && $dst_h)
                {
                    if($dst_w/$src_w> $dst_h/$src_h)
                    {
                        $dst_w = $src_w * ($dst_h / $src_h);
                        $x = 0 - ($dst_w - $width) / 2;
                    }
                    else
                    {
                        $dst_h = $src_h * ($dst_w / $src_w);
                        $y = 0 - ($dst_h - $height) / 2;
                    }
                }
                else if($dst_w xor $dst_h)
                {
                    if($dst_w && !$dst_h)  //有宽无高
                    {
                        $propor = $dst_w / $src_w;
                        $height = $dst_h  = $src_h * $propor;
                    }
                    else if(!$dst_w && $dst_h)  //有高无宽
                    {
                        $propor = $dst_h / $src_h;
                        $width  = $dst_w = $src_w * $propor;
                    }
                }
            }
            else
            {
                if(!$dst_h)  //裁剪时无高
                {
                    $height = $dst_h = $dst_w;
                }
                if(!$dst_w)  //裁剪时无宽
                {
                    $width = $dst_w = $dst_h;
                }
                $propor = min(max($dst_w / $src_w, $dst_h / $src_h), 1);
                $dst_w = (int)round($src_w * $propor);
                $dst_h = (int)round($src_h * $propor);
                $x = ($width - $dst_w) / 2;
                $y = ($height - $dst_h) / 2;
            }
        }
        else
        {
            $proportion = min($proportion, 1);
            $height = $dst_h = $src_h * $proportion;
            $width  = $dst_w = $src_w * $proportion;
        }

        $src = $createfun($src_img);
        $dst = imagecreatetruecolor($width ? $width : $dst_w, $height ? $height : $dst_h);
        $white = imagecolorallocate($dst, 255, 255, 255);
        imagefill($dst, 0, 0, $white);

        if(function_exists('imagecopyresampled'))
        {
            imagecopyresampled($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        else
        {
            imagecopyresized($dst, $src, $x, $y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        }
        $otfunc($dst, $dst_img);
        imagedestroy($dst);
        imagedestroy($src);
        return true;
    }

    /**
     * INPUT 上传图片到服务器
     * @param $filePath
     * @return array
     */
    public function uploadImg($filePath){
        $extArr = array("jpg", "png", "gif");//设置上传的文件格式

        // 图片验证
        $flag2  = true;
        $return = array();// 验证结果原因
        $count  = count($_FILES['imgFile']['name']);// 图片个数
        for ($i = 0; $i < $count; $i++) {
            $name       = $_FILES['imgFile']['name'][$i];
            $size       = $_FILES['imgFile']['size'][$i];// 获取图片大小
            $type       = $_FILES['imgFile']['type'][$i];// 图片类型
            $ext        = substr($name, -3);//截取文件后缀名
            $nameTmp    = substr($name,0, strrpos($name, '.'));

            if(empty($name)){
                $return = array('code' => '0X0001','msg' => "图片名称为空");
            }
            if (!in_array(strtolower($ext), $extArr)) {
                $return = array('code' => '0X0001','msg' => "图片格式");
            }
            //判断文件大小是否超过10M
            if ($size > (10000 * 1024)) {
                $return = array('code' => '0X0001','msg' => "图片大小不能超过10M");
            }

            if(!preg_match("/^[0-9a-zA-Z_,\-]+$/", $nameTmp)){// 禁止含有中文
                $return = array('code' => '0X0001','msg' => "上传失败,文件名只能含有字母、数字、下划线、短横线、英文逗号:$name");
            }

            if($return['code'] == '0X0001'){
                $flag2 = false;
                break;
            }
        }

        if($flag2 === false){
            return $return;
        }

        $path_arr = explode('/',trim($filePath,'/'));
        $path_str1 = '/'.$path_arr[0].'/'.$path_arr[1];
        $path_str2 = '/'.$path_arr[0].'/'.$path_arr[1].'/'.$path_arr[2];

        $res1 = mkdir($path_str1,0777,true);
        $res2 = chmod($path_str1,0777);
        $res3 = mkdir($path_str2,0777,true);
        $res4 = chmod($path_str2,0777);
        echo $path_str1;echo $path_str2;
        var_dump($res1);var_dump($res2);var_dump($res3);var_dump($res4);

        if($res4 === false ){
            $return = array('code' => '0X0001', 'msg' => "目录创建失败（" . $filePath . "）");
            return $return;
        }

        if (!is_dir($filePath)) {
            $res = $this->mkdirs($filePath);
            if ($res === false) {
                $return = array('code' => '0X0001', 'msg' => "目录创建失败（" . $filePath . "）");
                return $return;
            }
        }


        $savePaths = array();

        $count = count($_FILES['imgFile']['name']);
        for($i = 0; $i < $count; $i ++) {
            $name       = $_FILES['imgFile']['name'][$i];
            $tmpName    = $_FILES['imgFile']['tmp_name'][$i];
            $ext        = substr($name, -3);//截取文件后缀名
            if ($tmpName != ""){
                $newFilePath = $filePath . $name;

                if(move_uploaded_file($tmpName, $newFilePath)) {// 若文件存在则会覆盖，否则创建
                    $savePaths[$name] = $newFilePath;
                    chmod($newFilePath,0777);
                }
            }
        }
        chmod($filePath,0777);

        $return = array('code' => '0X0000','msg' => '创建成功','data' => $savePaths);

        return $return;

    }

    /**
     * 删除指定地址的图片
     * @param $filePath
     * @return int
     */
    public function removeImgFile($filePath){

        if(file_exists($filePath)){
            if(@unlink($filePath)){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 1;
        }
    }



}

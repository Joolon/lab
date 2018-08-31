<?php
/**
 * 自动加载类库文件
 * Created by JoLon
 * User: JoLon <179777072@qq.com>
 * Date: 2017/12/13
 * Time: 20:18
 */
class Loader {

    /**
     * 自动加载类文件
     * @param string $name  类名
     * @return bool|mixed
     */
    public static function load($name) {
        $file = self::filePath($name);

        if ($file) {
            return include_once $file;
        }else{
            return false;
        }
    }

    /**
     * 在指定目录中查找类文件并返回文件路径
     * @param $name
     * @param string $ext
     * @return string
     * @throws string|CustomException
     */
    public static function filePath($name, $ext = '.class.php') {
        if (!$ext) {
            $ext = '.class.php';
        }

        $filename = $name.$ext;

        if(strpos($filename,'Action') AND file_exists(PDA_APP.'Action/'.$filename)){// 控制器
            return PDA_APP.'Action/'.$filename;

        }elseif(strpos($filename,'Model') AND file_exists(PDA_APP.'Model/'.$filename)){// 模型方法
            return PDA_APP.'Model/'.$filename;

        }elseif(strpos($filename,'Api') AND file_exists(PDA_APP.'Api/'.$filename)){// API请求
            return PDA_APP.'Api/'.$filename;

        }elseif(file_exists(APP_BASE.'class/'.$filename)){// V2类库
            return APP_BASE.'class/'.$filename;

        }elseif(file_exists(PDA_BASE_PATH.'Lib/'.$filename)){// PDA类库
            return PDA_BASE_PATH.'Lib/'.$filename;

        }
        throw new CustomException($filename.' 类文件不存在');

    }
}
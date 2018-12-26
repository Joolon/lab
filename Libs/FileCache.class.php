<?php

/**
 * 文件缓存类
 * Created by PhpStorm.
 * User: Jolon
 * Date: 2017/6/2
 * Time: 13:38
 */
class FileCache
{
    //缓存目录
    public static $cacheRoot = "cache/Data/";
    //缓存更新时间秒数，0为不缓存
    public static $cacheLimitTime = 60;
    //缓存文件名
    public static $cacheFileName = "";
    //缓存扩展名
    public static $cacheFileExt = "txt";

    /**
     * 构造函数
     * int $cacheLimitTime 缓存更新时间
     */
    function __construct()
    {

    }

    /**
     * 返回并检查缓存文件是否在设置更新时间之内
     * 返回：如果在更新时间之内则返回文件内容，反之则返回失败
     * @param string $fileName 保存缓存内容的文件名
     * @param int|boolean|string $cacheLimitTime 缓存有效期（支持字符串类型）
     *              为false则跳过有效期判断直接获取内容
     * @return mixed
     */
    public static function getCacheFileContent($fileName, $cacheLimitTime = false)
    {
        if($cacheLimitTime !== false AND empty($cacheLimitTime))
            $cacheLimitTime = self::$cacheLimitTime;

        $cacheFilePath = self::getCacheFileName($fileName);
//        echo $cacheFilePath;exit;
        if (file_exists($cacheFilePath)) {
            if (is_string($cacheLimitTime)) {
                $cacheLimitTime = strtotime($cacheLimitTime) - time();
            }
            //echo $cacheLimitTime;exit;
            $cTime = self::getFileCreateTime($cacheFilePath);
            if($cacheLimitTime === false OR (time() - $cTime < $cacheLimitTime) ) {
                $contents = file_get_contents($cacheFilePath);
                return json_decode($contents, true);
            }
        }
        return false;
    }


    /**
     * 清除缓存文件
     * @param string $fileName 指定文件名(含函数)或者all（全部）
     * @return boolean 返回：清除成功返回true，反之返回false
     */
    public static function clearCache($fileName = "all")
    {
        if ($fileName != "all") {
            $fileName = self::$cacheRoot . strtoupper(md5($fileName)) . "." . self::$cacheFileExt;
            if (file_exists($fileName)) {
                return @unlink($fileName);
            } else return false;
        }
        if (is_dir(self::$cacheRoot)) {
            if ($dir = @opendir(self::$cacheRoot)) {
                while ($file = @readdir($dir)) {
                    $check = is_dir($file);
                    if (!$check)
                        @unlink(self::$cacheRoot . $file);
                }
                @closedir($dir);
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 根据当前动态文件生成缓存文件名
     * @param $fileName
     * @return string
     */
    public static function getCacheFileName($fileName)
    {
        return dirname(dirname(__FILE__)).'/' . self::$cacheRoot . $fileName . "." . self::$cacheFileExt;
        //return $this->cacheRoot . strtoupper(md5($_SERVER["REQUEST_URI"])) . "." . $this->cacheFileExt;
    }

    /**
     * 缓存文件建立时间
     * @param string $fileName 缓存文件名（含相对路径）
     * @return int 返回：文件生成时间秒数，文件不存在返回0
     */
    public static function getFileCreateTime($fileName)
    {
        if (!trim($fileName)) return 0;
        if (file_exists($fileName)) {
            return intval(filemtime($fileName));
        } else return 0;
    }

    /**
     * 缓存内容到指定文件中
     * @param string $fileName 文件名（含相对路径）
     * @param string $text 文件内容
     * @return boolean 返回：成功返回ture，失败返回false
     */
    public static function saveCacheFile($fileName, $text)
    {
        if (!$fileName || !$text) return false;
        $cacheFilePath = self::getCacheFileName($fileName);
        if (file_exists($cacheFilePath)) {
            @unlink($cacheFilePath);
        }
        //var_dump($cacheFilePath);exit;
        $text = json_encode($text);
        if ($fp = fopen($cacheFilePath, "w")) {
            if (@fwrite($fp, $text)) {
                fclose($fp);
                return true;
            } else {
                fclose($fp);
                return false;
            }
        }
        return false;
    }

    /**
     * 连续建目录
     * @param string $dir 目录字符串
     * @param string $mode 权限数字
     * @return boolean 返回：顺利创建或者全部已建返回true，其它方式返回false
     */
    public static function makeDir($dir, $mode = "0777")
    {
        if (!$dir) return 0;
        $dir = str_replace("\\", "/", $dir);
        $mdir = "";
        foreach (explode("/", $dir) as $val) {
            $mdir .= $val . "/";
            if ($val == ".." || $val == "." || trim($val) == "") continue;
            if (!file_exists($mdir)) {
                if (!@mkdir($mdir, $mode)) {
                    return false;
                }
            }
        }
        return true;
    }
}

?>
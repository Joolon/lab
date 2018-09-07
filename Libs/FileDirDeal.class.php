<?php
namespace Libs;

/**
 * Created by JoLon.
 * 文件夹处理
 * User: JoLon
 * Date: 2016/10/12
 * Time: 8:55
 */

class FileDirDeal
{

    /**
     * 获取文件夹下所有文件
     * @param string            $fileDir        目标文件夹路径
     * @param array|string      $fileExt        文件类型（默认空，返回所有）
     * @param bool              $isRecursion    是否递归读取子文件夹（默认使用递归）
     * @param bool              $isRealPath     是否返回真实路径
     * @param bool              $onlyFile       是否只是查找文件（默认所有）
     * @return array|bool
     */
    public static function readAllFile($fileDir,$fileExt = '',$isRecursion = true,$isRealPath = true,$onlyFile = false)
    {
        if (!is_dir($fileDir)) return false;

        static  $fileList   = [];

        $handle     = opendir($fileDir);

        if ($handle) {
            while (($nowFile = readdir($handle)) !== false) {
                $temp = $fileDir . DIRECTORY_SEPARATOR . $nowFile;// 文件或文件夹路径

                // 是否读取子文件夹
                if (is_dir($temp) AND $nowFile != '.' AND $nowFile != '..' ) {
                    if($onlyFile === false){// 是否返回文件夹
                        if($isRealPath){
                            $fileList[] = $temp;// 返回的是绝对路径
                        }else{
                            $fileList[] = $nowFile;// 返回的是文件名
                        }
                    }

                    if($isRecursion){// 执行递归
                        self::readAllFile($temp,$fileExt,$isRecursion,$isRealPath,$onlyFile);
                    }
                } else {
                    if ($nowFile != '.' AND $nowFile != '..') {
                        if(!empty($fileExt)){// 判断是否是指定的格式的文件
                            if(strrpos($nowFile,'.') === false ) continue;// 指定了文件格式，跳过无格式的文件

                            // 判断文件后缀
                            $suffix = substr($nowFile,strrpos($nowFile,'.') + 1);
                            if(is_array($fileExt)  AND !in_array($suffix,$fileExt)) continue;
                            if(is_string($fileExt) AND $suffix != $fileExt) continue;
                        }

                        if($isRealPath){
                            $fileList[] = $temp;// 返回的是绝对路径
                        }else{
                            $fileList[] = $nowFile;// 返回的是文件名
                        }

                    }
                }
            }
        }

        return $fileList;
    }



}
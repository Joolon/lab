<?php

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
     * @param string $dir       文件夹路径
     * @param bool $isRecursion 是否递归读取子文件夹
     * @param bool $isRealPath  是否返回绝对路径
     * @return array|bool
     */
    public static function readAllFile($dir,$isRecursion = true,$isRealPath = true)
    {
        if (!is_dir($dir)) return false;

        $handle     = opendir($dir);
        $fileList   = [];

        if ($handle) {
            while (($fl = readdir($handle)) !== false) {
                $temp = $dir . DIRECTORY_SEPARATOR . $fl;// 文件或文件夹路径

                // 是否读取子文件夹
                if (is_dir($temp) AND $fl != '.' AND $fl != '..' AND $isRecursion ) {
                    self::readAllFile($temp);
                } else {
                    if ($fl != '.' && $fl != '..') {
                        if($isRealPath){
                            $fileList[] = $temp;// 绝对路径
                        }else{
                            $fileList[] = $fl;// 文件名
                        }

                    }
                }
            }
        }

        return $fileList;
    }



}
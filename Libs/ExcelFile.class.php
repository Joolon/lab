<?php
namespace Libs;

/**
 * Created by JoLon.
 * EXCEL：数据导入导出，相关处理方法
 * User: JoLon
 * Date: 2018/09/04
 * Time: 18:55
 */
class ExcelFile
{

    /**
     * 读取EXCEL文件所有数据
     * @return array
     */
    public static function import(){
        $files      = $_FILES;

        $file_name  = $files['name']['file_execl'];
        $tmp_name   = $files['tmp_name']['file_execl'];

        $fileExp = explode('.', $file_name);
        $fileExp = strtolower($fileExp[count($fileExp) - 1]);//文件后缀

        if ($fileExp != 'xls' AND $fileExp != 'xlsx' ) {
            exit('只能导入EXCEL文件');
        }

        // 此处 导入 EXCEL 类文件
        if ($fileExp == 'xls')  $PHPReader = new \PHPExcel_Reader_Excel5();
        if ($fileExp == 'xlsx') $PHPReader = new \PHPExcel_Reader_Excel2007();

        // 文件保存路径
        $path       = '/web/files/';
        $filePath   = $path . date('YmdHis') . '.' . $fileExp;

        $sheetData = [];
        if (move_uploaded_file($tmp_name, $filePath)) {

            $PHPReader      = $PHPReader->load($filePath);
            $currentSheet   = $PHPReader->getSheet(0);
            $totalRows      = $currentSheet->getHighestRow();

            // 读取所有数据
            $sheetData = $currentSheet->toArray(null, true, true, true);

        }

        return $sheetData;

    }
	
    /**
     * EXCEL 数据列的序号(数字)转成 EXCEL 列字母
     * @param int	    $index  当前列索引（从0开始 如A:0,B:1,C:3,...,Z:25,AZ:26,AB:27...）
     * @return string
     */
    public static function getColumnName($index){
        $offset = 65;// 偏移量

        $str = '';
        if (floor($index / 26) > 0) {
            $str .= self::getColumnName(floor($index / 26)-1);
        }
        return $str . chr($index % 26 + $offset);
    }
	


}

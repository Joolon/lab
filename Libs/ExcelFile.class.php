<?php
namespace Libs;

/**
 * Created by JoLon.
 * EXCEL：数据导入导出
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
    public function import(){
        $files      = $_FILES;

        $file_name  = $files['name']['file_execl'];
        $tmp_name   = $files['tmp_name']['file_execl'];

        $fileExp = explode('.', $file_name);
        $fileExp = strtolower($fileExp[count($fileExp) - 1]);//文件后缀

        if ($fileExp != 'xls' AND $fileExp != 'xlsx' ) {
            exit('只能导入EXCEL文件');
        }

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


}

<?php
namespace Libs;

/**
 * Created by JoLon.
 * CSV：数据导入导出
 * User: JoLon
 * Date: 2018/09/04
 * Time: 18:55
 */
class IOCSVFile
{

    /**
     * 导出csv
     * @param array $columns 标题
     * @param array $dataList 数据
     * @param string $filename 文件名
     */
    public function exportCsv($columns = array(), $dataList, $filename = 'exportData')
    {
        $filename .= date('YmdHis') . '.csv';
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');

        $str_columns = '';
        if ($columns) {
            foreach ($columns as $key => $value) {
                $str_columns .= ',' . $value;
            }
            $str_columns = ltrim($str_columns, ',');
        }
        if ($str_columns) {
            echo $str_columns . "\n";
        }

        if ($dataList) {
            foreach ($dataList as $data) {
                $str_datas = '';
                foreach ($data as $key2 => $value2) {
                    $value2 = specialDealContent($value2);
                    $str_datas .= ',' . $value2;
                }
                echo ltrim($str_datas, ',') . "\n";
            }
        }
        exit;
    }


    /**
     * 保存数据到CSV文件中 以便于下载大数据
     *      文件以追加的方式保存数据
     *      （数字或数字字符串长度超过11位则后面会自动添加制表符\t）
     * @param array $columns
     * @param $dataList
     * @param $filePath
     * @return bool
     */
    public function exportCsvFile($columns = array(), $dataList, $filePath)
    {
        if (empty($filePath)) return false;

        $csv_data = '';
        if ($columns) {
            foreach ($columns as $value) {
                $csv_data .= iconv('utf-8', 'gbk//ignore', $value) . ',';
            }
            $csv_data = rtrim($csv_data, ',');
            $csv_data .= "\n";
        }

        if ($dataList) {
            foreach ($dataList as $k => $row) {
                foreach ($row as $val) {
                    // 数字类型的增加制表符 防止EXCEL打开数据失真
                    if (is_numeric($val) AND strlen($val) > 11) {
                        $val = $val . "\t";
                    }
                    $csv_data .= iconv('utf-8', 'gbk//ignore', $val) . ',';
                }
                $csv_data = rtrim($csv_data, ',');
                $csv_data .= "\n";
                unset($dataList[$k]);
            }
        }
        
        file_put_contents($filePath, $csv_data, FILE_APPEND);
        return $filePath;
    }


    /**
     * 导出CSV文件
     * @param array $data 数据
     * @param array $columns 首行数据（如标题）
     * @param string $file_name 文件名称
     * @return string
     */
    public function exportCsv2($data = [], $columns = [], $file_name = '')
    {
        $file_name .= date('YmdHis') . '.csv';
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename=' . $file_name);
        header('Cache-Control: max-age=0');
        $fp = fopen('php://output', 'a');
        if (!empty($columns)) {
            foreach ($columns as $key => $value) {
                $columns[$key] = iconv('utf-8', 'gbk', $value);
            }
            fputcsv($fp, $columns);
        }

        $num = 0;
        $limit = 100000;//每隔$limit行，刷新一下输出buffer，不要太大，也不要太小
        $count = count($data);//逐行取出数据，不浪费内存
        if ($count > 0) {
            for ($i = 0; $i < $count; $i++) {
                $num++;
                //刷新一下输出buffer，防止由于数据过多造成问题
                if ($limit == $num) {
                    ob_flush();
                    flush();
                    $num = 0;
                }
                $row = $data[$i];
                foreach ($row as $key => $value) {
                    $row[$key] = iconv('utf-8', 'gbk', $value);
                }
                fputcsv($fp, $row);
            }
        }
        fclose($fp);
        
        return $file_name;
    }


    /**
     * 读取CSV文件中指定的行数
     * @param string $csv_file csv文件路径
     * @param int $lines 读取的行数(0为返回所有行)
     * @param int $offset 跳过的行数
     * @return array|bool
     */
    public function readCsvLines($csv_file = '', $lines = 0, $offset = 0)
    {
        // 打开并读取文件
        if (!$fp = fopen($csv_file, 'r')) {
            return false;
        }
        $i = $j = 0;
        // 获取指向文件的行数，计算偏移量
        if ($offset > 0) {
            while (++$i <= $offset) {
                if (false !== ($line = fgets($fp))) {
                    continue;
                }
                break;
            }
        }
        $data = array();
        if ($lines > 0) {// 大于0则读取 $lines 的行数
            while ((($j++ < $lines) && !feof($fp))) {
                $nowdata = fgetcsv($fp);
                array_walk($nowdata, 'myConvert');// 转码
                $data[] = $nowdata;
            }
        } else {// 读取所用行数据
            while (!feof($fp)) {
                $nowdata = fgetcsv($fp);
                array_walk($nowdata, 'myConvert');
                $data[] = $nowdata;
            }
        }

        fclose($fp);
        return $data;
    }


    /**     * 文章访问日志     *
     * 下载的日志文件通常很大, 所以先设置csv相关的Header头, 然后打开     * PHP output流,
     * 渐进式的往output流中写入数据, 写到一定量后将系统缓冲冲刷到响应中     * 避免缓冲溢出     */

    public static function exportExcel($columns,$dataList,$file_name = '')
    {
        set_time_limit(0);
        $file_name .= date('YmdHis') . '.xlsx';

        //设置好告诉浏览器要下载excel文件的headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');

        $fp = fopen('php://output', 'a');//打开output流

        mb_convert_variables('GBK', 'UTF-8', $columns);
        fputcsv($fp, $columns);//将数据格式化为CSV格式并写入到output流中
        foreach($dataList as $data){
            mb_convert_variables('GBK', 'UTF-8', $data);
            fputcsv($fp, $data);// 数组数据自动根据逗号拼接成字符串，所以生成的xlsx文件里面 数据都只能在一列里面，并不会展示到对应的数据列

            // 实现 文件下载进度，每生成一部分数据就输出到文件中，文件一直在增大，下载的时候并不能知道文件大小
            ob_flush();
            flush();//必须同时使用 ob_flush() 和flush() 函数来刷新输出缓冲。
        }

        fclose($fp);
        exit();
    }


}


/**
 * 字符串编码转换（用于 array_walk 回调）
 * @param $value
 * @param $key
 */
function myConvert(&$value, $key)
{
    $value = iconv('gbk', 'utf-8', $value);
}

/**
 * 处理 字符串中含有逗号和双引号的内容
 * @param $content
 * @return string
 */
function specialDealContent($content){
    if(stripos($content,'"') !== false){
        // 如果该字段中有双引号，该双引号前要再加一个双引号，然后把该字段使用双引号括起来。
        $content = str_replace('"','""',$content);
        $content = '"'.$content.'"';
    }elseif(stripos($content,',') !== false){
        // 如果字段中有逗号（,），该字段使用双引号（”）括起来
        $content = '"'.$content.'"';
    }

    return $content;
}

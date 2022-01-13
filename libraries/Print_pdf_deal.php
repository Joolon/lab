<?php

/**
 * PDF操作类
 * User: Jolon
 * Date: 2020/11/14 10:23
 */
class Print_pdf_deal{

    public function __construct(){

    }

    /**
     * 生成 或 预览PDF文件
     * @param        $content
     * @param string $title
     * @param        $fileName
     * @param string $type
     * @param string $print_type 纸打印类型 A4 A3
     */
    public static function writePdf($content, $title = '', $fileName, $type = 'D', $file_css = '',$print_type='A4',$footer =''){
        require_once APPPATH."third_party/mpdf/mpdf.php";
        if (ob_get_contents()) ob_end_clean();
        $mpdf                   = new \mPDF('', $print_type, '', '', 0, 0);// A5-L A5横向
        $mpdf->autoScriptToLang = 1;
        $mpdf->autoLangToFont   = 1;
        $mpdf->SetDisplayMode(100);
        //添加页脚,页码到pdf中
        if(!empty($footer)){
            $mpdf->SetHTMLFooter($footer);
        }

        if(empty($fileName)){// 文件名为空的自动设置
            $file = 'HT-'.date('YmdHis').'.pdf';
        }elseif(stripos($fileName,'pdf') === false){// 没有文件后缀的自动添加
            $file = $fileName.'.pdf';
        }else{
            $file = $fileName;
        }
        if(empty($file_css)){
            $css1 = VIEWPATH."mycss/requestion.css";
        }else{
            $css1 = VIEWPATH."mycss/".$file_css;
        }
        $style1 = file_get_contents($css1);
        $mpdf->WriteHTML($style1, 1);
        $mpdf->WriteHTML($content, 2);
        $mpdf->Output($file, $type);
    }

    /**
     * 无css载入
     * @param $content
     * @param string $title
     * @param $fileName
     * @param string $type
     * @param string $file_css
     * @param string $print_type
     * @param string $footer
     * @throws MpdfException
     */
    public static function writePdfOnDom($content, $title = '', $fileName, $type = 'D', $file_css = '',$print_type='A4',$footer =''){
        require_once APPPATH."third_party/mpdf/mpdf.php";
        $mpdf = new \mPDF('', 'A4-L', '', '', 0, 0);// A5-L A5横向
        $mpdf->autoScriptToLang = 1;
        $mpdf->autoLangToFont   = 1;
        if(!empty($footer)){
            $mpdf->SetHTMLFooter($footer);
        }
        $mpdf->DefOrientation = "P";
        $mpdf->CurOrientation = "P";
        if(empty($fileName)){
            $file = 'SHD-'.date('YmdHis').'.pdf';
        }else{
            $file = $fileName.'.pdf';
        }
        $mpdf->WriteHTML($content);
        $mpdf->Output($file, $type);
    }

    /**
     * @param $content
     * @param $path
     * @param $fileName
     * @param string $type
     * @param string $print_type
     * @return false|string
     * @throws MpdfException
     */
    public static function fPdf($content,$path,$fileName, $type = 'f',$print_type='A4'){
        require_once APPPATH."third_party/mpdf/mpdf.php";
        $mpdf = new \mPDF('', $print_type, '', '', 0, 0);// A5-L A5横向
        $mpdf->autoScriptToLang = 1;
        $mpdf->autoLangToFont   = 1;
        $mpdf->SetDisplayMode(100);
        if(empty($fileName)){
            $fileName = 'HT-'.date('YmdHis').'.pdf';
        }else{
            $fileName = $fileName.'.pdf';
        }
        $filename= iconv("utf-8","gb2312",$fileName);
        $css1 = VIEWPATH_PRINT_TEMPLATE."print_template/mycss/requestion.css";
        $content = file_get_contents($content);
        $style1 = file_get_contents($css1);
        $mpdf->WriteHTML($style1, 1);
        $mpdf->WriteHTML($content, 2);
        $mpdf->Output($path.'/'.$filename,$type);
        return $filename;
    }

    /**
     * 文件生成压缩包
     * @param $file_list    绝对路径文件列表
     * @param $zip_file_path    绝对路径保存到的ZIP压缩包
     * @return bool|string
     */
    public static function download_zip_package($file_list,$zip_file_path){
        if($file_list){
            require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Php_zip.php';
            $php_zip = new Php_zip();

            foreach ($file_list as $filename => $file_path) {
                $suffix = explode('.', basename($file_path));   //文件后缀
                if (empty($suffix)) continue;                    //获取后缀失败,跳过操作
                $suffix = '.' . array_pop($suffix);
                $filename = $filename.$suffix;

                $php_zip->add_file(file_get_contents($file_path), $filename);
            }
            //----------------------
            $php_zip->output($zip_file_path);

            return true;
        }else{
            return '文件列表为空';
        }
    }


    /**
     * 文件生成压缩包
     * @param $dir_path    模板文件夹路径
     * @param $zip_file_path    绝对路径保存到的ZIP压缩包
     * @return bool|string
     */
    public static function create_zip_package($dir_path,$zip_file_path){
        if(!is_dir($dir_path)){
            return '文件夹路径错误';
        }
        require_once dirname(__FILE__).DIRECTORY_SEPARATOR.'Php_zip.php';
        $php_zip = new Php_zip();
        $php_zip->add_path($dir_path);
        $php_zip->output($zip_file_path);

        return true;
    }


    /**
     * 下载文件处理
     * @author Jaxton 2018/03/01
     * /compact/compact/download_compact_handle
     * @param  array $data
     * @param string $file_alias_name 文件别名
     * @throws Exception
     */
    public static function download_compact_handle($file_list,$file_alias_name){
        $this->load->library('php_zip');

        $dfile = 'statement_pdf/'.$file_alias_name;

        foreach ($file_list as $filename => $file_path) {
            $suffix = explode('.', basename($file_path));   //文件后缀
            if (empty($suffix)) continue;                    //获取后缀失败,跳过操作
            $suffix = '.' . array_pop($suffix);
            $filename = $filename.$suffix;

            $this->php_zip->add_file(file_get_contents($file_path), $filename);
        }
        //----------------------
        $this->php_zip->output($dfile);

        if(file_exists($dfile)){
            $down_file_url = 'statement_pdf/'.$file_alias_name;
            return 'http://'.$_SERVER['HTTP_HOST'].'/'.$down_file_url;
        }else{
            return false;
        }
    }
}



// 使用实例1
$print_pdf_deal = new Print_pdf_deal();

$html = 'html内容';
$key = '单据号唯一KEY';

if($key == 'tax_compact'){
    $css_file_name = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Demo/taxRefundTemplate.css';
}else{
    $css_file_name = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'Demo/nonRefundableTemplate.css';
}
$file_name = 'ABT-HT000001';

//设置PDF页脚内容
$footer = "<p style='text-align:center'>第<span>{PAGENO}</span>页,共<span>{nb}</span>页</p>";
// 下载文件
Print_pdf_deal::writePdf($html,'',$file_name,'D',$css_file_name,'',$footer);
// 查看PDF格式
Print_pdf_deal::writePdf($html,'',$file_name,'F');



// 使用实例2
$success_list = [
    '单据号1' => 'html内容1',
    '单据号2' => 'html内容2'
];
$error_list = [
    '单据号3数据格式错误',
    '单据号4数据格式错误'
];

// 生成文件夹
// HTML 生成 PDF 文件
foreach($success_list as $pur_number => $html){
    $fileName = $pur_number.'.pdf';
    Print_pdf_deal::writePdf($html,'',$fileName,'F');
    $success_list[$pur_number] = $fileName;
}
if($error_list){
    $fileName = 'errors.pdf';
    Print_pdf_deal::writePdf(implode("<br/>",$error_list),'',$fileName,'F');
    $success_list['errors'] = $fileName;
}
$zip_file_name  = 'fukuanshenqing-'.date('Ymd').'-'.rand(1000,9999).'.zip';
$file_name      = $zip_file_name;
$file_type      = 'zip';// 压缩包

try{

    $down_file_url  = Print_pdf_deal::download_compact_handle($success_list,$zip_file_name);
    if(empty($down_file_url)){
        throw new Exception('压缩文件生成失败');
    }
}catch (Exception $e){

}


// 使用实例3
if($success_list){
    // HTML 生成 PDF 文件
    foreach($success_list as $supplier_name => $statement_data){
        foreach($statement_data as $statement_number => $statement_value){
            $html_pdf       = $statement_data[$statement_number]['html_pdf'];
            $html_excel     = $statement_data[$statement_number]['html_excel'];
            $css_file_name  = 'printStatementTemplate.css';

            // 生成文件到 指定文件夹位置
            $file_save_path = '压缩包文件夹根目录绝对路径/'.$supplier_name.'/';// 生成供应商名称对应的目录
            $fileNamePdf    = $file_save_path.$statement_number.$supplier_name.'.pdf';
            $fileNameExcel  = $file_save_path.$statement_number.$supplier_name.".xls";

            //设置PDF页脚内容
            $footer = "<p style='text-align:center'>第<span>{PAGENO}</span>页,共<span>{nb}</span>页</p>";
            $this->print_pdf_deal->writePdf($html_pdf,'',$fileNamePdf,'F', $css_file_name, '', $footer);

            file_put_contents($fileNameExcel,$html_excel);// 输出EXCEL内容

            $download_file_list[md5($fileNamePdf)] = $fileNamePdf;
            $download_file_list[md5($fileNameExcel)] = $fileNamePdf;
        }
    }
}
if($error_list){
    $fileName = '压缩包文件夹根目录绝对路径/errors.pdf';
    $this->print_pdf_deal->writePdf(implode("<br/>",$error_list),'',$fileName,'F');
    $download_file_list['errors'] = $fileName;
}

$down_file_url  = Print_pdf_deal::create_zip_package('压缩包文件夹根目录绝对路径','ZIP压缩包文件');
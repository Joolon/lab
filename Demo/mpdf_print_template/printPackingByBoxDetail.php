<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="GBK">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Document</title>
    </head>
<style>
    /* .ui-title {
        font-size: 28px;
        padding-top: 10px;
        font-weight: 540;
        margin-left: 350px;
    } */
    .ui-box {
        display: flex;
        justify-content: center;
        width: 1000px;
        text-align:center
    }
    .ui-basicInformation {
        margin:0 auto;
        font-size: 14px;
        border-spacing: 1px;
        border-collapse: collapse;
        margin-top:-20px;
    }
    td{
        background-color: white;
        line-height: 40px;
        border: 1px solid gray;
    }
    th{
        width:2%;
        min-width:20px;
        min-height:40px;
    }
</style>
<?php 
$data = json_decode(file_get_contents('php://input'), true);
if(empty($data)){
    echo '数据缺失';exit;
}
$items_list   = $data['items_list'];
$summary_list = $data['summary_list'];
unset($data['items_list'],$data['summary_list']);
$statement_data = $data;
?>
<body>
    <div style="text-align:center">
        <h1 class="ui-title"> 装箱明细</h1>
        <h2><?php echo $statement_data['supplier_name'] ?></h2>
        <h3><?php echo $statement_data['statement_number'] ?></h3>
        <p"><span>地址：<?php echo $statement_data['supplier_address'] ?></span><span style="margin-left:20px">联系人：<?php echo $statement_data['create_user'] ?></span><span style="margin-left:20px">联系方式：<?php echo $statement_data['create_user_phone'] ?></span></p>
    </div>
    <div class="ui-box">
        <table class="ui-basicInformation" border="1">
            <tr class="ui-base-tr">
                    <th>箱号</th>
                    <th>sku</th>
                    <th style="width:4%">PO</th>
                    <th>图片</th>
                    <th style="width:8%">产品名称</th>
                    <th style="width:3%">产品品牌产品型号</th>
                    <th>箱内数</th>
                    <th style="width:4%">外箱尺寸</th>
                    <th style="width:4%">单箱体积-导入</th>
                    <th style="width:4%">净重KG-导入 总净重KG-导入</th>
                    <th style="width:4%">毛重KG-导入 总毛重KG-导入</th>
                    <th style="width:4%">采购单价 总金额</th>
                    <th>是否退税</th>
                    <th>箱号是否有效</th>
                    <th style="width:4%">最新更新时间</th>
            </tr>
            <?php foreach ($items_list as  $key => $value) { ?>
            <tr>
                <td><?php echo $value['箱号']?></td>
                <td><?php echo $value['sku']?></td>
                <td><?php echo $value['PO']?></td>
                <td><img src="<?php echo $value['图片']; ?>"></td>
                <td><?php echo $value['产品名称']?></td>
                <td><?php echo $value['产品品牌产品型号']?></td>
                <td><?php echo $value['箱内数']?></td>
                <td><?php echo $value['外箱尺寸']?></td>
                <td><?php echo $value['单箱体积-导入']?></td>
                <td><?php echo $value['净重KG-导入 总净重KG-导入']?></td>
                <td><?php echo $value['毛重KG-导入 总毛重KG-导入']?></td>
                <td><?php echo $value['采购单价 总金额']?></td>
                <td><?php echo $value['是否退税']?></td>
                <td><?php echo $value['箱号是否有效']?></td>
                <td><?php echo $value['最新更新时间']?></td>
            </tr>
        </table>
    </div>
</body>
</html>

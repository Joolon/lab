<?php
include_once PDA_TPL.'head.php';
?>

<!-- JS 分页代码 -->
<link rel="stylesheet" href="<?php echo PDA_JS?>pdapage/pdapage.css">
<script type="text/javascript" src="<?php echo PDA_JS ?>pdapage/pdapage.js"></script>

<link rel="stylesheet" href="<?php echo PDA_CSS?>stock/putinstock.css">

<style>
    /*.thisbox2{position: fixed; width: 100%; height:calc(100% - 0%); left: 0px;top:0%;}*/

    table{
        width:96%; margin: 5px 3px 3px 5px;background-color: #FFFFFF;
    }
    table .table-head{
        background-color: #E4E4E4;
    }
    table tr {
        font-size: 14px;
        height: 25px
    }
    table tr th,td {
        text-align: left;
    }
    table tr th span {
        margin-left: 5px;
    }
    table tr td input {
        width: 120px;
        margin: 1px 0px 1px 5px;
    }



    /*.thisbox2 .div-content{*/
        /*position:relative;background-color:#D7D7D7;float: left;width: 100%;height: 100%;margin-top:5px;*/
    /*}*/

    /*.thisbox2 .div-content .div-data{*/
        /*background-color:#D7D7D7;float: left;width: 100%;height: auto;margin-top:5px;*/
    /*}*/

    .div-data .query-form{
        font-size: 18px;
    }
    .div-data .query-form select,input{
        width: 60%;
        height: 30px;
    }

</style>


<div class="thisbox">
    <div class="top-title" >
        <div style="">
            <div class="top-title-left"></div>
            <div class="top-title-right">
                当前操作：库存查询&nbsp;&nbsp;<font size="2">-<?php echo $this->active_store_name ;?><br/>
                <font style="font-size: 9px;margin-top: -2px;">(<?php echo  $this->username;?> <?php echo date('Y-m-d H:i:s');?>)</font>
            </div>
        </div>
    </div>
    <div class="div-content">
        <div class="div-data">
            <div style="margin-top: 1%;" class="query-form">
                <label style="width: 30%;text-align: right">选项：</label>
                <select name="query_type" id="query_type" >
                    <option value="sku_code">产品条码</option>
                    <option value="storage_sn">仓位</option>
                    <option value="sku">SKU</option>
                </select>
                <label style="width: 30%;text-align: right">值：</label>
                <input type="text" name="query_value" id="query_value"
                       onchange="this.value=convertStorageSn(this.value)" />
                <font color="red"><span onclick="removeInputValue('query_value')">X</span></font>
            </div>
            <table border="1" >
                <thead>
                <tr class="table-head">
                    <th><span>仓位</span></th>
                    <th><span>SKU</span></th>
                    <th><span>数量</span></th>
                </tr>
                </thead>
                <tbody id="body-list">
                <tr class="selStSn">
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="selStSn">
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <tr class="selStSn">
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="div-data">
            <!--  分页展示界面：Start （占用一个DIV与其他标签分离）-->
            <?php include_once PDA_TPL.'pdapage.php' ?>
            <!--  分页展示界面：End-->
        </div>

        <div class="boot-menu" >
            <div class="boot-left"><button type="button" class="btn btn-primary query-data-start">查询</button></div>
            <div class="boot-right">
                <?php include_once PDA_TPL.'Common/menu.php' ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo PDA_JS ?>putinstock.js"></script>

<script  type="text/javascript">
    $("#query_value").focus();

</script>
<!-- 分页代码 开始-->
<script  type="text/javascript">

    // 开始查询的触发按钮 类型为 button class属性必须为 query-data-start
    // 查询按钮  eg. <button type="button" class="query-data-start">查询</button>
    // 查询数据请求的URL
    // 返回的数据必须是JSON格式,
    // 成功实例  eg. {"code":"0X0000","list":{"page_index":"1","page_num":21,"list":[{"sku":"A2598"},{"sku":"18575716"}]}}
    // 失败实例  eg. {"code":"0X0001","msg":"未查询到结果"]}}
    var query_url = '<?php echo myUrl("c=StockApi&m=queryStockSave")?>';
    // 查询条件数据域的ID列表
    var query_ids_ist = new Array('query_type','query_value');


    // 用户自定义的展示数据的方法（方法名固定）
    function showDataListBody(showList){
        // 展示数据列表
        $("#body-list").html('');

        // 拼装展示列表的数据
        for(var i = 0; i < showList.length; i ++){
            var subHtml = '<tr  class="selStSn">' +
                '<td>&nbsp;'+showList[i].storage_sn+'</td>' +
                '<td>&nbsp;'+showList[i].sku+'</td>' +
                '<td>&nbsp;'+showList[i].amount+'</td>' +
                '</tr>';
            $("#body-list").append(subHtml);
        }
    }

</script>
<!-- 分页代码 结束-->

<?php
include_once PDA_TPL.'boot.php';
?>



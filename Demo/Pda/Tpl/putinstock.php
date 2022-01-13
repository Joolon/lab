<?php
include_once PDA_TPL.'head.php';
?>

<link rel="stylesheet" href="<?php echo PDA_CSS?>stock/putinstock.css">

<style>

    table tr td .advise-left {
        width: 50% !important;
        float: left
    }
    table tr td .advise-right {
        width: 10% !important;
        float: left
    }
</style>
<style>
</style>
<div class="thisbox">
    <div class="top-title" >
        <div style="">
            <div class="top-title-left"></div>
            <div class="top-title-right">
                当前操作：入库上架&nbsp;&nbsp;<font size="2">-<?php echo $this->active_store_name ;?><br/>
                <font style="font-size: 9px;margin-top: -2px;">(<?php echo  $this->username.''.date('Y-m-d H:i:s').' ';?>)</font>
            </div>
        </div>
    </div>
    <div class="div-content">
        <div class="div-data">
            <table border="1" >
                <thead>
                <tr class="table-head">
                    <th><span>项目名称</span></th>
                    <th><span>数值</span></th>
                </tr>
                </thead>

                <tbody id="table-Body">
                <tr>
                    <td><span>产品条码</span></td>
                    <td><input type="text" name="sku_code" id="sku_code" onblur="completeSkuInfo(1)" onkeypress="nextInput('goods_count')"/></td>
                </tr>
                <tr>
                    <td><span>SKU</span></td>
                    <td><input type="text" name="sku" id="sku" onblur="completeSkuInfo(2)" onkeypress="nextInput('goods_count')"/></td>
                </tr>
                <tr>
                    <td><span>产品名称</span></td>
                    <td><span class="goods_name" id="goods_name" style="margin-left: 15px;">—</span></td>
                </tr>
                <tr>
                    <td><span>数量</span></td>
                    <td>
                        <input type="text" name="goods_count" id="goods_count" onkeypress="nextInput('storage_sn')" style="width: 100px;"/>
                        <span id="show_qty" style="display: none;">(<span class="received_quantity">0</span>&nbsp;/<span class="total_quantity">0</span>)</span>
                    </td>
                </tr>
                <tr>
                    <td><span>仓位标签</span></td>
                    <td><input type="text" name="storage_sn" id="storage_sn"
                               onchange="this.value=convertStorageSn(this.value)"/></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="boot-menu" >
            <div class="boot-left"><button type="button" class="btn btn-primary submit-data">确认上架</button></div>
            <div class="boot-right">
                <?php include_once PDA_TPL.'Common/menu.php' ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo PDA_JS ?>putinstock.js"></script>
<script  type="text/javascript">
    $(function () {

        $("#sku_code").focus();

        $(".submit-data").click(function () {
            var data = checkData();
            if(data){
                $.post('<?php echo MyUrl('c=StockApi&m=putInStockSave');?>',{data:data},
                    function(re_data){
                        var re_data_tmp = jQuery.parseJSON(re_data);
                        if(re_data_tmp.code == '0X0000'){
                            alert('操作成功');
                            openUrl(request_url_sel);
                        }else if(re_data_tmp.msg == '需确认请求'){
                            if(confirm( '本次入库数量+已入库数量大于采购总数,是否继续入库？')){
                                $.post('<?php echo MyUrl('c=StockApi&m=putInStockSave');?>',{data:data,force:true},
                                    function(re_data){
                                        var re_data_tmp = jQuery.parseJSON(re_data);
                                        if(re_data_tmp.code == '0X0000'){
                                            alert('操作成功');
                                            openUrl(request_url_sel);
                                        }else{
                                            alert("操作失败：\n"+re_data_tmp.msg);
                                        }
                                    }
                                );
                            }
                        }else{
                            alert("操作失败：\n"+re_data_tmp.msg);
                        }
                    }
                );
            }
        });


    });


    function checkData() {
        var sku_code = $("#sku_code").val();
        if(sku_code == ''){
            webToast("请输入产品条码","top",1000);
            $("#sku_code").focus();
            return false;
        }
        var sku = $("#sku").val();
        if(sku == ''){
            webToast("请输入SKU","top",1000);
            $("#sku").focus();
            return false;
        }
        var goods_count = $("#goods_count").val();
        if(goods_count <= 0){
            webToast("请输入合法的数量","top",1000);
            $("#goods_count").focus();
            return false;
        }
        var storage_sn = $("#storage_sn").val();
        if(storage_sn == ''){
            webToast("请输入仓位","top",1000);
            $("#storage_sn").focus();
            return false;
        }

        var data = {
            sku_code:sku_code,
            sku:sku,
            goods_count:goods_count,
            storage_sn:storage_sn
        };

        return data;
    }

</script>

<?php
include_once PDA_TPL.'boot.php';
?>



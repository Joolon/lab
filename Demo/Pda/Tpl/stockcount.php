<?php
include_once PDA_TPL.'head.php';
?>

<link rel="stylesheet" href="<?php echo PDA_CSS?>stock/putinstock.css">


<style>
    table tr td .advise-left {
        margin-left: 5px;
        width: auto;
        float: left
    }
    table tr td .advise-right {
        margin-left: 10px;
        width: 6%;
        float: left
    }
</style>

<div class="thisbox">
    <div class="top-title" >
        <div style="">
            <div class="top-title-left"></div>
            <div class="top-title-right">
                当前操作：仓位盘点&nbsp;&nbsp;<font size="2">-<?php echo $this->active_store_name ;?></font><br/>
                <font style="font-size: 9px;margin-top: -2px;">(<?php echo  $this->username;?> <?php echo date('Y-m-d H:i:s');?>)</font>
            </div>
        </div>
    </div>
    <div class="div-content">
        <div class="div-data">
            <table border="1" >
                <thead>
                <tr class="table-head">
                    <th width="30%"><span>项目名称</span></th>
                    <th width="65%"><span>数值</span></th>
                </tr>
                </thead>
                <tbody id="table-Body">
                <tr>
                    <td><span>产品条码</span></td>
                    <td><input type="text" name="sku_code" id="sku_code" onblur="completeSkuInfo(1)" onkeypress="nextInput('sku')"/></td>
                </tr>
                <tr>
                    <td><span>SKU</span></td>
                    <td><input type="text" name="sku" id="sku" onblur="completeSkuInfo(2)" onkeypress="nextInput('goods_count')"/></td>
                </tr>
                <tr>
                    <td><span>产品名称</span></td>
                    <td><span id="goods_name" style="margin-left: 15px;">—</span></td>
                </tr>
                <tr>
                    <td><span>库存信息</span></td>
                    <td id='stListInfo'>
                        
                    </td>
                </tr>
                <tr>
                    <td><span>实际数量</span></td>
                    <td>
                        <input type="text" name="goods_count" id="goods_count"  style="width: 100px;"  onkeypress="nextInput('storage_sn')"/>
                    </td>
                </tr>
                <tr>
                    <td><span>仓位标签</span></td>
                    <td><input type="text" name="storage_sn" id="storage_sn" onchange="this.value=convertStorageSn(this.value)"/></td>
                </tr>
                </tbody>
            </table>
        </div>


        <div class="boot-menu" >
            <div class="boot-left"><button type="button"  class="btn btn-primary submit-data">保存</button></div>
            <div class="boot-right">
                <?php include_once PDA_TPL.'Common/menu.php' ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo PDA_JS ?>skuhelpcommon.js"></script>

<script  type="text/javascript">
    $(function () {
        $("#sku_code").focus();

        $(".submit-data").click(function () {
            var data = checkData();
            if(data){
                $.post('<?php echo MyUrl('c=StockApi&m=stockCountSave');?>',{data:data},
                    function(re_data){
                        var data = jQuery.parseJSON(re_data);
                        if(data.code == '0X0000'){
                            alert('操作成功');
                            openUrl(request_url_sel);
                        }else{
                            alert("操作失败：\n"+data.msg);
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



    /**
     * 拼接生成推荐仓位列表
     * @param stList
     * @returns {string}
     */
    function jointStorageSelect(stList){
        var html = '';
        for(var i=0;i < stList.length;i++){
            var num = i + 1;
            html += '<tr class="selStSn">'
                +'<td colspan="2" onclick="selStSn(this)" >'
                +'<div class="advise-left" >仓位推荐'+num+'：'+stList[i].storage_sn+'</div>'
                +'<div class="advise-right" >'
                +'<input type="radio" name="selStSn" data-stsn="'+stList[i].storage_sn+'"/>'
                +'</div>'
                +'</td>'
                +'</tr>';
        }
        return html;
//        alert(html);
    }

    /**
     * 推荐仓位点击事件
     * @param obj
     */
    function selStSn(obj){
        var st_sn = $(obj).find('input[name="selStSn"]').attr('data-stsn');

        $("#storage_sn").val(st_sn);
    }

</script>

<?php
include_once PDA_TPL.'boot.php';
?>



<?php
include_once PDA_TPL.'head.php';
?>

<link rel="stylesheet" href="<?php echo PDA_CSS?>stock/putinstock.css">

<style>
    table tr td .advise-left {
        width: 50%;
        float: left
    }
    table tr td .advise-right {
        margin-left: 2px;
        width: 6%;
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
                当前操作：库存转移&nbsp;&nbsp;<font size="2">-<?php echo $this->active_store_name ;?></font><br/>
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
                    <td><input type="text" name="sku_code" id="sku_code" onblur="completeSkuInfo(1)"  onkeypress="nextInput('sku')"/></td>
                </tr>
                <tr>
                    <td><span>SKU</span></td>
                    <td><input type="text" name="sku" id="sku" onblur="completeSkuInfo(2)"  onkeypress="nextInput('from_storage_sn')"/></td>
                </tr>
                <tr>
                    <td><span>产品名称</span></td>
                    <td><span id="goods_name" style="margin-left: 15px;">—</span></td>
                </tr>
                <tr>
                    <td><span>仓位（从）</span></td>
                    <td><input type="text" name="from_storage_sn" id="from_storage_sn" onfocus="setNowFocusName('from_storage_sn')"
                               onkeypress="nextInput('to_storage_sn')" onchange="this.value=convertStorageSn(this.value)"/></td>
                </tr>
                <tr>
                    <td><span>数量</span></td>
                    <td><span id="goods_count"  style="margin-left: 15px;">—</span></td>
                </tr>
                <tr>
                    <td><span>仓位（到）</span></td>
                    <td><input type="text" name="to_storage_sn" id="to_storage_sn" onfocus="setNowFocusName('to_storage_sn')"
                               onchange="this.value=convertStorageSn(this.value)"/></td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="boot-menu" >
            <div class="boot-left"><button type="button" class="btn btn-primary submit-data">确认更新</button></div>
            <div class="boot-right">
                <?php include_once PDA_TPL.'Common/menu.php' ?>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo PDA_JS ?>skuhelpcommon.js"></script>
<script  type="text/javascript">
    $("#sku_code").focus();

    var focus_name = '';// 当前焦点所在的位置
    $(function () {

        $(".submit-data").click(function () {
            var data = checkData();
            if(data){
                $.post('<?php echo MyUrl('c=StockApi&m=updateLocationSave');?>',{data:data},
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
        var from_storage_sn = $("#from_storage_sn").val();
        if(from_storage_sn == ''){
            webToast("请输入合法的仓位(从)","top",1000);
            $("#goods_count").focus();
            return false;
        }
        var to_storage_sn = $("#to_storage_sn").val();
        if(to_storage_sn == ''){
            webToast("请输入的仓位(到)","top",1000);
            $("#to_storage_sn").focus();
            return false;
        }

        var data = {
            sku_code:sku_code,
            sku:sku,
            from_storage_sn:from_storage_sn,
            to_storage_sn:to_storage_sn
        };

        return data;
    }

    // 改变当前焦点所在的位置
    function setNowFocusName(name) {
        focus_name = name;
    }

    // 组装 提示仓位的列表数据
    function jointStorageSelect(stList){
        var html = '';
        for(var i=0;i < stList.length;i++){
            var num = i + 1;
            html += '<tr class="selStSn">'
                +'<td colspan="2" >'
                +'<div class="advise-left" >仓位'+num+':'+stList[i].storage_sn+'&nbsp;('+stList[i].amount+')</div>'
                +'<div class="advise-right" >'
                +'<input type="radio" name="selStSn" onclick="selStSn(this)" data-stsn="'+stList[i].storage_sn+'" data-stqty="'+stList[i].amount+'"/>'
                +'</div>'
                +'</td>'
                +'</tr>';
        }
        return html;
//        alert(html);
    }

    // 点击提示仓位触发事件
    function selStSn(obj){
        var st_sn = $(obj).attr('data-stsn');
        var st_qty = $(obj).attr('data-stqty');

        if(focus_name == 'from_storage_sn'){
            $("#from_storage_sn").val(st_sn);
            $("#goods_count").html(st_qty);

            $("#to_storage_sn").focus();
        }else if(focus_name == 'to_storage_sn'){
            $("#to_storage_sn").val(st_sn);
        }

        var from_storage_sn = $("#from_storage_sn").val();
        var to_storage_sn = $("#to_storage_sn").val();
        if(from_storage_sn == to_storage_sn){
            webToast('仓位相同，请重新选择','top',1000);
            $("#to_storage_sn").val('');
            $("#to_storage_sn").focus();
        }
    }

</script>

<?php
include_once PDA_TPL.'boot.php';
?>



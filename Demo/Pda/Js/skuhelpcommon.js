

function completeSkuInfo(type) {

    if(type == 1){
        var sku_code = $("#sku_code").val();
    }else if(type == 2){
        var sku_code = $("#sku_code").val();
        var sku = $("#sku").val();
    }
    $('#stListInfo').html('');//清空信息
    if(sku_code || sku){
        $.get(index_name+"?c=SkuHelpApi&m=findSkuInfo2",{sku_code:sku_code,sku:sku,have_st_sn:1},
            function(re_data){
                var data = jQuery.parseJSON(re_data);
                if(data.code == '0X0000'){
                    $("#sku_code").val(data.list.sku_code);
                    $("#goods_name").html(data.list.goods_name);
                    $("#sku").val(data.list.sku);
                    if(data.list.stList.length>0){
                        var _str = '';
                        for(var i=0;i<data.list.stList.length;i++){
                            var obj = data.list.stList[i];
                            _str = _str + "仓位:"+obj.storage_sn+" 库存:"+obj.amount+"<br/>";
                        }
                        $('#stListInfo').html(_str);
                    }
                    removeStorageSelect();
                    var stList = data.list.stList;
                    var subHtml = jointStorageSelect(stList);
                    $("#table-Body").append(subHtml);

                    $("#from_storage_sn").focus();
                }else{
                    webToast("操作结果："+data.msg,"top",2000);

                    removeStorageSelect();// 清空仓位推荐列表
                    emptyAllInputAndSelect();// 清空输入的数据

                    if(type == 1){
                        $("#sku_code").focus();
                    }else{
                        $("#sku").focus();
                    }
                }
            });
    }
}

function removeStorageSelect() {
    $("#table-Body").find('tr.selStSn').remove();
}

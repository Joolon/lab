/**
 * 自动完成SKU的基本资料
 * @param type
 */
function completeSkuInfo(type) {
    if(type == 1){
        var sku_code = $("#sku_code").val();
    }else if(type == 2){
        var sku_code = $("#sku_code").val();
        var sku = $("#sku").val();
    }

    if(sku_code || sku){
        $.get(index_name+"?c=SkuHelpApi&m=findSkuInfo",{sku_code:sku_code,sku:sku,have_st_sn:1},
            function(re_data){
                var data = jQuery.parseJSON(re_data);
                if(data.code == '0X0000'){
                    $("#sku_code").val(data.list.sku_code);
                    $("#sku").val(data.list.sku);
                    $("#goods_name").html(data.list.goods_name);
                    $("#goods_count").focus();

                    if($("#show_qty").length > 0){// 显示采购已入库数量信息
                        $("#show_qty").show();
                        $(".received_quantity").html(data.list.received_quantity);
                        $(".total_quantity").html(data.list.total_quantity);
                    }

                    removeStorageSelect();
                    var stList = data.list.stList;
                    var subHtml = jointStorageSelect(stList);
                    $("#table-Body").append(subHtml);
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

/**
 * 移除推荐仓位列表
 */
function removeStorageSelect() {
    $("#table-Body").find('tr.selStSn').remove();
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
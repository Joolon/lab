/**
 * 使用教程
 * 1.引入 pdapage.css与pdapage.js文件
 * 2.包含 pdapage.php HTML容器文件
 * 3.定义 一个class为query-data-start，类型为button的按钮触发查询
 * 4.定义 query_url API查询方法
 * 5.定义 query_ids_ist 查询参数数据域的ID列表（自动根据ID获取标签域的值）
 * 6.自定义 showDataListBody(showList) 方法用来回调展示HTML数据

// 实例
// <link rel="stylesheet" href="<?php echo PDA_JS?>pdapage/pdapage.css">
// <script type="text/javascript" src="<?php echo PDA_JS ?>pdapage/pdapage.js"></script>
// <div>
// <!--  分页展示界面：Start （占用一个DIV与其他标签分离）-->
// <?php include_once PDA_TPL.'pdapage.php' ?>
// <!--  分页展示界面：End-->
// </div>


// 开始查询的触发按钮 类型为 button class属性必须为 query-data-start
// 查询按钮  eg. <button type="button" class="query-data-start">查询</button>
// 查询数据请求的URL
// 返回的数据必须是JSON格式,
// 成功实例  eg. {"code":"0X0000","list":{"page_index":"1","page_num":21,"list":[{"sku":"A2598"},{"sku":"18575716"}]}}
// 失败实例  eg. {"code":"0X0001","msg":"未查询到结果"]}}
var query_url = '<?php echo myUrl("c=StockApi&m=queryStockSave")?>';
// 查询条件数据域的ID列表
var query_ids_ist = new Array('query_type','query_value');

function showDataListBody(showList){}

*/


var isRestart = false;// 是否是从新查询（点击查询按钮）

$(function () {

    // 查询按钮触发事件
    $(".query-data-start").click(function () {
        isRestart = true;// 标记为重新点击查询按钮查询(重新设置为第一页)
        $(".pageGro li.page-left2").click();
    });

    // 分页展示 按钮触发点击事件
    $(".pageGro li").click(function () {
        var query_data = getQueryParams($(this), query_ids_ist);

//        console.log(query_data);return
        // 发送请求获取数据
        $.get(query_url, query_data, function (re_data) {
            var data = jQuery.parseJSON(re_data);
            if (data.code == '0X0000') {
                $(".pageGro").show();

                var data = data.list;
                var page_num = data.page_num;
                var page_index = data.page_index;
                var showList = data.list;

                showDataListBody(showList);// 展示数据的方法，由用户自定义

                recombinePageShow(page_num, page_index);//更新分页数据
            } else {
                webToast("查询结果：\n" + data.msg, 'top', 2000);
            }
        });

    });
});

/**
 * 分页 页码数转换
 * @param page_index
 * @returns {*}
 */
function convertIndex(page_index) {
    if(isRestart == true){// 重新查询，设置当前页为第一页
        isRestart = false;
        return 1;
    }

    if(page_index == '首页'){
        page_index = 1;
    }
    if(page_index == '尾页'){
        page_index = 'LAST';
    }
    if(page_index == 'PREV'){
        page_index = parseInt($(".page-left2").html())-1;
    }
    if(page_index == 'NEXT'){
        page_index = parseInt($(".page-right2").html())+1;
    }

    return page_index;
}


/**
 * 根据预设的 查询数据域 的ID列表 拼装查询条件
 * @param obj 点击的分页展示按钮的对象
 * @param query_ids_ist 查询数据域 的ID列表
 * @returns {{page_index}}
 */
function getQueryParams(obj,query_ids_ist) {
    var page_index = obj.html();//获取当前页数
    page_index = convertIndex(page_index);

    var query_data = {page_index:page_index};
    for(var i = 0;i< query_ids_ist.length; i++){
        var now_query_id = query_ids_ist[i];
        query_data[now_query_id] = $("#"+now_query_id).val();
    }

    return query_data;
}

/**
 * 更新 分页展示的数据
 * @param page_num  查询数据的总页数
 * @param page_index  当前展示的数据是第几页
 */
function recombinePageShow(page_num,page_index){

    $(".page-on").html(page_index);

    // 首页、上一页 按钮隐藏与显示
    if(parseInt(page_index)-2 <= 1){
        $(".page-first").hide();
        $(".page-prev").hide();
    }else{
        $(".page-first").show();
        $(".page-prev").show();
    }

    if(parseInt(page_index)-1 > 0){
        $(".page-left1").show();
        $(".page-left1").html(parseInt(page_index)-1);
    }else{
        $(".page-left1").hide();
    }
    if(parseInt(page_index)-2 > 0){
        $(".page-left2").html(parseInt(page_index)-2);
        $(".page-left2").show();
    }else{
        $(".page-left2").hide();
    }
    if(parseInt(page_index)+1 <= page_num){
        $(".page-right1").html(parseInt(page_index)+1);
        $(".page-right1").show();
    }else{
        $(".page-right1").hide();
    }
    if(parseInt(page_index)+2 <= page_num){
        $(".page-right2").html(parseInt(page_index)+2);
        $(".page-right2").show();
    }else{
        $(".page-right2").hide();
    }

    // 尾页、下一页 按钮隐藏与显示
    if(parseInt(page_index) + 2 >= page_num){
        $(".page-next").hide();
        $(".page-last").hide();
    }else{
        $(".page-next").show();
        $(".page-last").show();
    }

}

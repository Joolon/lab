

</div>

</div>

</body>


<script  type="text/javascript">
    var index_name = 'pda.php';
    var url_self = "<?php echo $_SERVER['PHP_SELF']?>";
    var request_url_sel = '<?php echo $_SERVER["REQUEST_URI"] ?>';


    // 页面跳转
    function openUrl(url){
//        alert(url);return;
        location.href=url;
    }

    // 清空所有输入框（INPUT和SELECT）的值
    function emptyAllInputAndSelect() {
        $(".div-data input").each(function(){
            $(this).val('');
        });
        $(".div-data select").each(function(){
            $(this).val('');
        });
    }

    $(function () {
        $(".main_menu").click(function () {
            openUrl(url_self+'?c=Index&m=main');
        });
        // 退出系统
        $(".exit_system").click(function () {
            openUrl(url_self+'?c=Index&m=logout');
        });

    });

    // 捕获回车按钮（ENTER），根据INPUT.ID属性移动焦点
    function nextInput(nextInputName) {
        if (event.keyCode == 13) {
            $("#"+nextInputName).focus();
        }
    }

    // 去除仓位条码前面的仓库ID，如 32-A01.01.01变成 A01.01.01
    function convertStorageSn(st_sn){

        var newStr = st_sn.substring(st_sn.indexOf('-') + 1, st_sn.length)
        return newStr;
    }

    // 根据INPUT.ID去除其值并设置焦点
    function removeInputValue(id){
        $("#"+id).val('');
        $("#"+id).focus();
    }

</script>

</html>

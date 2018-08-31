<?php
include_once PDA_TPL.'head.php';
?>


<style>
    .main-menu{
        font-size: 20px;
        text-align: center;
        margin-top: 30%;
    }
    select,button {
        margin-bottom: 4%;
        background-color: #169BD5;
        height: 40px;
        width: 50%;
        color: #FFFFFF;
        font-size: 25px ;
    }
</style>

<div class="main-menu">
    <div>
        <span>仓库：</span>
        <select name="store_id" id="store_id" style="width: 60%;height: 30px;">
            <option value="">请选择仓库</option>
            <?php foreach($this->warehouseList as $id => $store_name){
                ?>
                <option value="<?php echo $id?>" <?php if($this->last_store_id == $id){ echo 'selected';}?>><?php echo $store_name?></option>
            <?php
            }?>
        </select>
    </div>
    <div class="rber_me">
        <input type="checkbox" id="rber_me" checked />&nbsp;&nbsp;是否记住仓库
    </div>
    <br/>
    <div><button  type="button" class="in_menu">进入菜单</button></div>
    <div><button  type="button" class="exit_system">退出系统</button></div>
</div>

<script  type="text/javascript">
    $(function () {

        $(".rber_me").click(function(){
            var rber_me = $("#rber_me").prop('checked');
            if(rber_me == 'true' || rber_me == true){
                $("#rber_me").prop('checked',false);
            }else{
                $("#rber_me").prop('checked',true);
            }
        });

        $(".in_menu").click(function () {
            var store_id = $("#store_id").val();
            var rber_me = $("#rber_me").prop('checked');
//            alert(rber_me);
            if(store_id == ''){
                webToast('请选择目标仓库!','top',1000);
                return false;
            }
            if(rber_me == 'true' || rber_me == true){
                rber_me = 1;
            }else{
                rber_me = 0;
            }
            var url = url_self+'?c=Index&m=index&store_id='+store_id+'&rber_me='+rber_me;
            openUrl(url);
        });

    });
</script>

<?php
include_once PDA_TPL.'boot.php';
?>



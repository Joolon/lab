<?php
include_once PDA_TPL.'head.php';
?>


<style>
    .main-menu{
        text-align: center;
        margin-top: 10%;
    }
    button {
        margin-bottom: 4%;
        background-color: #169BD5;
        height: 40px;
        width: 50%;
        color: #FFFFFF;
        font-size: 25px ;
    }
</style>

<script type="text/javascript" src="<?php echo PDA_JS ?>menu.js"></script>

<div class="main-menu">
    <div><button  type="button" class="put_in" >入库上架</button></div>
    <div><button  type="button" class="update_location">库存转移</button></div>
    <div><button  type="button" class="stock_count">库存盘点</button></div>
    <div><button  type="button" class="query_stock">库存查询</button></div>
    <div><button  type="button" class="change_store">切换仓库</button></div>
    <div><button  type="button" class="exit_system">退出系统</button></div>
</div>

<?php
include_once PDA_TPL.'boot.php';
?>



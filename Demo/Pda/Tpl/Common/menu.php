<style>
    .change_menu{
        font-size: 20px;
        margin-left: 5px;
        /*background-color: #00B83F;*/
    }
</style>

<div class="btn-group dropup">
    <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">主菜单
        <span class="caret"></span>
    </button>
    <ul class="dropdown-menu" role="menu">
        <li class="change_menu main_menu" data-menu="put_in">菜单列表</li>
        <li class="change_menu divider" style="" ></li>
        <li class="change_menu put_in" data-menu="put_in">入库上架</li>
        <li class="change_menu update_location" data-menu="update_location">库存转移</li>
        <li class="change_menu stock_count" data-menu="stock_count">库存盘点</li>
        <li class="change_menu query_stock" data-menu="query_stock">库存查询</li>
        <li class="change_menu change_store" data-menu="change_store">切换仓库</li>
        <li class="change_menu exit_system" data-menu="exit_system">退出系统</li>
    </ul>
</div>

<script type="text/javascript" src="<?php echo PDA_JS ?>menu.js"></script>
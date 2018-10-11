<?php

use yii\helpers\Html;
use app\config\Vhelper;

?>

<div class="row">
    <div class="col-md-2">备货名称：</div>
    <div class="col-md-2"><?= $form->field($model, 'tactics_name')->input('text',['style'=>'width:180px;height:25px;'])->label(false) ?></div>
    <div class="col-md-8"></div>
</div>

<div class="row">
    <div class="col-md-2">1.配置新品备货：</div><div class="col-md-1">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;单价</div>
    <div class="col-md-2"><?= $form->field($model, 'single_price')->input('text',['style'=>'width:120px;height:25px;','placeholder' => '<=','class' => 'float_int_input'])->label(false) ?></div><div class="col-md-1">元</div>
    <div class="col-md-2">库存持有量：</div>
    <div class="col-md-2"><?= $form->field($model, 'inventory_holdings')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input'])->label(false) ?></div><div class="col-md-1">个</div>
</div>

<div class="row">
    <div class="col-md-2">2.大单配置：</div><div class="col-md-2">保留最大值</div>
    <div class="col-md-2"><?= $form->field($model, 'reserved_max')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input'])->label(false) ?></div><div class="col-md-1">个</div>
</div>



<div  id="daily-sales-list">
    <?php if($model->purchaseTacticsDailySales) {
        foreach ($model->purchaseTacticsDailySales as $key => $value) {
            ?>
            <div class="row">
                <div class="col-md-2"><?php if ($key == 0) { echo '3.日均销量：';} ?></div>
                <div class="col-md-1">比值</div>
                <div class="col-md-2"><?= $form->field($model, 'daily_sales_value[]')->input('text', ['style' => 'width:80px;height:25px;' , 'value' => $value->day_sales,'class' => 'float_int_input'])->label(false) ?></div>
                <div class="col-md-2">销量平均值：</div>
                <div class="col-md-2"><?= $form->field($model, 'daily_sales_day[]')->input('text', ['style' => 'width:120px;height:25px;', 'value' => $value->day_value,'class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-1">天</div>
                <?php if ($key == 0) { ?><div class="col-md-1" style="width: 10px;"><span class="glyphicon glyphicon-plus" id="plus-daily"></span></div>
                <?php } else { ?><div class="col-md-1" style="width: 10px;"><span class="glyphicon glyphicon-minus" id="minus-daily"></span></div><?php } ?>
            </div>
        <?php }
    } else{ ?>
        <div class="row">
            <div class="col-md-2">3.日均销量：</div>
            <div class="col-md-1">比值</div>
            <div class="col-md-2"><?= $form->field($model, 'daily_sales_value[]')->input('text',['style'=>'width:80px;height:25px;','class' => 'float_int_input'])->label(false) ?></div>
            <div class="col-md-2">销量平均值：</div>
            <div class="col-md-2"><?= $form->field($model, 'daily_sales_day[]')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input'])->label(false) ?></div><div class="col-md-1">天</div>
            <div class="col-md-1" style="width: 10px;"><span class="glyphicon glyphicon-plus" id="plus-daily"></span></div>
        </div>
        <div class="row" >
            <div class="col-md-2"></div>
            <div class="col-md-1">比值</div>
            <div class="col-md-2"><?= $form->field($model, 'daily_sales_value[]')->input('text',['style'=>'width:80px;height:25px;','class' => 'float_int_input'])->label(false) ?></div>
            <div class="col-md-2">销量平均值：</div>
            <div class="col-md-2"><?= $form->field($model, 'daily_sales_day[]')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input'])->label(false) ?></div><div class="col-md-1">天</div>
            <div class="col-md-1" style="width: 10px;"><span class="glyphicon glyphicon-minus" id="minus-daily"></span></div>
        </div>
    <?php
    } ?>
</div>
<div id="plus-daily-sales" style="display: none;">
    <div class="row" >
        <div class="col-md-2"></div>
        <div class="col-md-1">比值</div>
        <div class="col-md-2"><?= $form->field($model, 'daily_sales_value[]')->input('text',['style'=>'width:80px;height:25px;','class' => 'float_int_input'])->label(false) ?></div>
        <div class="col-md-2">销量平均值：</div>
        <div class="col-md-2"><?= $form->field($model, 'daily_sales_day[]')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input'])->label(false) ?></div><div class="col-md-1">天</div>
        <div class="col-md-1" style="width: 10px;"><span class="glyphicon glyphicon-minus" id="minus-daily"></span></div>
    </div>
</div>

<div class="row">
    <div class="col-md-3">4.销量标准差取值范围：</div>
    <div class="col-md-2"><?= $form->field($model, 'sales_sd_value_range')->input('text',['style'=>'width:120px;height:25px;'])->label(false) ?></div><div class="col-md-1">天</div>
</div>
<div class="row">
    <div class="col-md-3">5.提前期取值范围：</div>
    <div class="col-md-2"><?= $form->field($model, 'lead_time_value_range')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input'])->label(false) ?></div><div class="col-md-1">天</div>
</div>
<div class="row">
    <div class="col-md-3">6.权均交期取值范围：</div>
    <div class="col-md-2"><?= $form->field($model, 'weight_avg_period_value_range')->input('text',['style'=>'width:120px;height:25px;','class' => 'int_input'])->label(false) ?></div><div class="col-md-1">天</div>
</div>

<?php


$js = <<<JS

// 点击 加号 追加一行 输入框
$("#plus-daily").click(function(){
    var html = $("#plus-daily-sales").html();
    $("#daily-sales-list").append($(html));
});

// 注意不能用 $("#minus-daily").on("click" 的形式，否则不能根据 ID 触发 点击事件
// 点击 减号 去掉当前一行输入框
$(document).on("click","#minus-daily",function(){
    $(this).parent().parent().remove();
});


JS;
$this->registerJs($js);

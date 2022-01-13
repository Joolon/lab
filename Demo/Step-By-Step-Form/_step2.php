<?php

use app\models\PurchaseTacticsSuggest;

$suggest = new PurchaseTacticsSuggest();
$suggestList = [];
foreach($model->purchaseTacticsSuggest as $suggest){
    if($suggest->type == 1){
        $suggestList[1][] = $suggest;// 定期备货
    }elseif($suggest->type == 2){
        $suggestList[2][] = $suggest;// 定量备货
    }else{
        $suggestList[3][] = $suggest;// 最大最小备货
    }
}

?>
<div class="row">定期备货：</div>
<br/>
<div id="suggest-type1">

    <?php
    if(isset($suggestList[1]) AND $suggestList[1]) {
        foreach ($suggestList[1] as $key => $suggest) { ?>
            <div>
                <div class="row">
                    <div class="col-md-1" style="width: 100px;">销量占比(%)</div>
                    <div class="col-md-1"><?= $form->field($model, 'percent_start[type1][]')->input('text', ['value' => $suggest->percent_start, 'style' => 'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                    <div class="col-md-1" style="width: 10px;">-</div>
                    <div class="col-md-1"><?= $form->field($model, 'percent_end[type1][]')->input('text', ['value' => $suggest->percent_end, 'style' => 'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                    <div class="col-md-2"><span style="float: right">备货周期(天)：</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'stockup_days[type1][]')->input('text', ['value' => $suggest->stockup_days, 'style' => 'width:80px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                    <div class="col-md-2"><span style="float: right">服务系数：</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'service_coefficient[type1][]')->input('text', ['value' => $suggest->service_coefficient, 'style' => 'width:80px;height:25px;','class' => 'float_int_input'])->label(false) ?></div>
                    <div class="col-md-1" style="width: 20px;"></div>
                    <?php if ($key == 0) { ?>
                        <div class="col-md-1"><span class="glyphicon glyphicon-plus" id="plus-suggest-type1"></span>
                        </div>
                    <?php } else { ?>
                        <div class="col-md-1"><span class="glyphicon glyphicon-minus" id="minus-suggest-type1"></span>
                        </div> <?php } ?>

                    <?= $form->field($model, 'maximum[type1][]')->input('hidden', ['style' => 'width:80px;height:25px;'])->label(false) ?>
                    <?= $form->field($model, 'minimum[type1][]')->input('hidden', ['style' => 'width:80px;height:25px;'])->label(false) ?>
                </div>
            </div>
            <?php
        }
    }else{
        ?>
        <div>
            <div class="row">
                <div class="col-md-1" style="width: 100px;">销量占比(%)</div>
                <div class="col-md-1"><?= $form->field($model, 'percent_start[type1][]')->input('text', ['value' => $suggest->percent_start, 'style' => 'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-1" style="width: 10px;">-</div>
                <div class="col-md-1"><?= $form->field($model, 'percent_end[type1][]')->input('text', ['value' => $suggest->percent_end, 'style' => 'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-2"><span style="float: right">备货周期(天)：</span></div>
                <div class="col-md-1"><?= $form->field($model, 'stockup_days[type1][]')->input('text', ['value' => $suggest->stockup_days, 'style' => 'width:80px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-2"><span style="float: right">服务系数：</span></div>
                <div class="col-md-1"><?= $form->field($model, 'service_coefficient[type1][]')->input('text', ['value' => $suggest->service_coefficient, 'style' => 'width:80px;height:25px;','class' => 'float_int_input'])->label(false) ?></div>
                <div class="col-md-1" style="width: 20px;"></div>
                <div class="col-md-1"><span class="glyphicon glyphicon-plus" id="plus-suggest-type1"></span></div>

                <?= $form->field($model, 'maximum[type1][]')->input('hidden', ['style' => 'width:80px;height:25px;'])->label(false) ?>
                <?= $form->field($model, 'minimum[type1][]')->input('hidden', ['style' => 'width:80px;height:25px;'])->label(false) ?>
            </div>
        </div>
        <?php
    }?>
</div>


<br/>
<div class="row">定量备货：</div>
<br/>
<div id="suggest-type2">
    <?php
    if(isset($suggestList[2]) AND $suggestList[2]) {
        foreach ($suggestList[2] as $key => $suggest) { ?>
            <div>
                <div class="row">
                    <div class="col-md-1" style="width: 100px;">销量占比(%)</div>
                    <div class="col-md-1"><?= $form->field($model, 'percent_start[type2][]')->input('text', ['value' => $suggest->percent_start, 'style' => 'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                    <div class="col-md-1" style="width: 10px;">-</div>
                    <div class="col-md-1"><?= $form->field($model, 'percent_end[type2][]')->input('text', ['value' => $suggest->percent_end, 'style' => 'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                    <div class="col-md-2"><span style="float: right">备货天数(天)：</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'stockup_days[type2][]')->input('text', ['value' => $suggest->stockup_days, 'style' => 'width:80px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                    <div class="col-md-2"><span style="float: right">服务系数：</span></div>
                    <div class="col-md-1"><?= $form->field($model, 'service_coefficient[type2][]')->input('text', ['value' => $suggest->service_coefficient, 'style' => 'width:80px;height:25px;','class' => 'float_int_input'])->label(false) ?></div>
                    <div class="col-md-1" style="width: 20px;"></div>
                    <?php if ($key == 0) { ?>
                        <div class="col-md-1"><span class="glyphicon glyphicon-plus" id="plus-suggest-type2"></span>
                        </div>
                    <?php } else { ?>
                        <div class="col-md-1"><span class="glyphicon glyphicon-minus" id="minus-suggest-type2"></span>
                        </div> <?php } ?>

                    <?= $form->field($model, 'maximum[type2][]')->input('hidden', ['style' => 'width:80px;height:25px;'])->label(false) ?>
                    <?= $form->field($model, 'minimum[type2][]')->input('hidden', ['style' => 'width:80px;height:25px;'])->label(false) ?>
                </div>
            </div>

            <?php
        }
    }else{
        ?>
        <div>
            <div class="row">
                <div class="col-md-1" style="width: 100px;">销量占比(%)</div>
                <div class="col-md-1"><?= $form->field($model, 'percent_start[type2][]')->input('text', ['value' => $suggest->percent_start, 'style' => 'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-1" style="width: 10px;">-</div>
                <div class="col-md-1"><?= $form->field($model, 'percent_end[type2][]')->input('text', ['value' => $suggest->percent_end, 'style' => 'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-2"><span style="float: right">备货天数(天)：</span></div>
                <div class="col-md-1"><?= $form->field($model, 'stockup_days[type2][]')->input('text', ['value' => $suggest->stockup_days, 'style' => 'width:80px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-2"><span style="float: right">服务系数：</span></div>
                <div class="col-md-1"><?= $form->field($model, 'service_coefficient[type2][]')->input('text', ['value' => $suggest->service_coefficient, 'style' => 'width:80px;height:25px;','class' => 'float_int_input'])->label(false) ?></div>
                <div class="col-md-1" style="width: 20px;"></div>
                <div class="col-md-1"><span class="glyphicon glyphicon-plus" id="plus-suggest-type2"></span></div>

                <?= $form->field($model, 'maximum[type2][]')->input('hidden', ['style' => 'width:80px;height:25px;'])->label(false) ?>
                <?= $form->field($model, 'minimum[type2][]')->input('hidden', ['style' => 'width:80px;height:25px;'])->label(false) ?>
            </div>
        </div>

        <?php
    }?>
</div>


<br/>
<div class="row">最大最小备货：</div>
<br/>
<div id="suggest-type3">
    <?php
    if(isset($suggestList[3]) AND $suggestList[3]){
    foreach($suggestList[3] as $key => $suggest){ ?>
    <div>
        <div class="row">
            <div class="col-md-1" style="width: 100px;">销量占比(%)</div>
            <div class="col-md-1"><?= $form->field($model, 'percent_start[type3][]')->input('text',['value'=> $suggest->percent_start,'style'=>'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
            <div class="col-md-1" style="width: 10px;">-</div>
            <div class="col-md-1"><?= $form->field($model, 'percent_end[type3][]')->input('text',['value'=> $suggest->percent_end,'style'=>'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
            <div class="col-md-2"><span style="float: right">最大值(天)：&nbsp;</span></div>
            <div class="col-md-1"><?= $form->field($model, 'maximum[type3][]')->input('text',['value'=> $suggest->maximum,'style'=>'width:80px;height:25px;','class' => 'int_input'])->label(false) ?></div>
            <div class="col-md-2"><span style="float: right">最小值(天)：&nbsp;</span></div>
            <div class="col-md-1"><?= $form->field($model, 'minimum[type3][]')->input('text',['value'=> $suggest->minimum,'style'=>'width:80px;height:25px;','class' => 'int_input'])->label(false) ?></div>
            <div class="col-md-1" style="width: 20px;"></div>
            <?php if($key == 0){?>  <div class="col-md-1"><span class="glyphicon glyphicon-plus" id="plus-suggest-type3"></span></div>
            <?php }else{ ?><div class="col-md-1"><span class="glyphicon glyphicon-minus" id="minus-suggest-type3"></span></div> <?php }?>


            <?= $form->field($model, 'stockup_days[type3][]')->input('hidden',['style'=>'width:80px;height:25px;'])->label(false) ?>
            <?= $form->field($model, 'service_coefficient[type3][]')->input('hidden',['style'=>'width:80px;height:25px;'])->label(false) ?>
        </div>
    </div>
        <?php
    }
    }else{
        ?>
        <div>
            <div class="row">
                <div class="col-md-1" style="width: 100px;">销量占比(%)</div>
                <div class="col-md-1"><?= $form->field($model, 'percent_start[type3][]')->input('text',['value'=> $suggest->percent_start,'style'=>'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-1" style="width: 10px;">-</div>
                <div class="col-md-1"><?= $form->field($model, 'percent_end[type3][]')->input('text',['value'=> $suggest->percent_end,'style'=>'width:70px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-2"><span style="float: right">最大值(天)：&nbsp;</span></div>
                <div class="col-md-1"><?= $form->field($model, 'maximum[type3][]')->input('text',['value'=> $suggest->maximum,'style'=>'width:80px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-2"><span style="float: right">最小值(天)：&nbsp;</span></div>
                <div class="col-md-1"><?= $form->field($model, 'minimum[type3][]')->input('text',['value'=> $suggest->minimum,'style'=>'width:80px;height:25px;','class' => 'int_input'])->label(false) ?></div>
                <div class="col-md-1" style="width: 20px;"></div>
                <div class="col-md-1"><span class="glyphicon glyphicon-plus" id="plus-suggest-type3"></span></div>

                <?= $form->field($model, 'stockup_days[type3][]')->input('hidden',['style'=>'width:80px;height:25px;'])->label(false) ?>
                <?= $form->field($model, 'service_coefficient[type3][]')->input('hidden',['style'=>'width:80px;height:25px;'])->label(false) ?>
            </div>
        </div>
        <?php
    }
    ?>
</div>

<?php

$js = <<<JS


// 点击 加号 追加一行 输入框
// $("#plus-suggest-type1,#plus-suggest-type2,#plus-suggest-type3") 表示可以响应 三个 ID 的点击事件
$("#plus-suggest-type1,#plus-suggest-type2,#plus-suggest-type3").click(function(){
    var ptr = $(this).parent().parent().parent().parent();
    var html = ptr.children('div').eq(0).html();
    html = html.replace('glyphicon-plus','glyphicon-minus');
    html = html.replace('plus-suggest','minus-suggest');
    ptr.append(html);
    
    ptr.children('div').last().find('input').val('');// 添加的 HTML 标签INPUT输入框的值设为空
});

// 注意不能用 $("#minus-daily").on("click" 的形式，否则不能根据 ID 触发 点击事件
// 点击 减号 去掉当前一行输入框
$(document).on("click","#minus-suggest-type1,#minus-suggest-type2,#minus-suggest-type3",function(){
    $(this).parent().parent().remove();
});


JS;
$this->registerJs($js);
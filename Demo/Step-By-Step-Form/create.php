<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/* @var $this yii\web\View */
?>
    <div class="purchase-order-form" >
        <?php $form = ActiveForm::begin([
                'id' => 'form-id',
            ]
        ); ?>

        <?= $form->field($model, 'id')->input('hidden')->label(false) ?>

        <!--配置补货策略-->
        <div id="step1" style="display: none;padding-left: 20px;">
            <?= $this->render('_step1', ['form' => $form,'model' => $model]) ?>
        </div>

        <!--创建逻辑-->
        <div id="step2" style="display: none;padding-left: 20px;">
            <?= $this->render('_step2', ['form' => $form,'model' => $model]) ?>
        </div>

        <!--仓库配置-->
        <div id="step3" style="display: none;padding-left: 20px;">
            <?= $this->render('_step3', ['form' => $form,'model' => $model,'warehouseList' => $warehouseList]) ?>
        </div>




        <div class="col-md-12"></div>

        <br/>
        <br/>
        <br/>
        <div class="form-group clearfix" style="margin-left: 700px;">
            <span id="span-back"><a id="back">返回上一步</a></span>&nbsp;&nbsp;
            <span id="span-next"><?= Html::button('下一步',['class' => 'btn btn-primary','id' => 'next']) ?></span>&nbsp;&nbsp;
            <span id="span-submit"><?= Html::button('确认',['class' => 'btn btn-primary','id' => 'do_save']) ?></span>
        </div>



        <?php ActiveForm::end(); ?>

    </div>
<?php

$checkTacticsInfoUrl   = Url::toRoute('check-tactics-info');


$js = <<<JS

var now_step = 'step1';// 全局变量 缓存的当前 步骤页面
change_content();

// 点击下一步时 改变缓存的当前 步骤页面
$("#next").click(function(){
    if(now_step == 'step1'){
        now_step = 'step2';
    }else if(now_step == 'step2'){
        now_step = 'step3';
    }
    
    change_content();
});

// 点击下一步时 改变缓存的当前 步骤页面
$("#back").click(function(){
    if(now_step == 'step3'){
        now_step = 'step2';
    }else if(now_step == 'step2'){
        now_step = 'step1';
    }
    
    change_content();
});

$("#do_save").click(function(){
    // 验证比值之和 是否为1
    var daily_sales_value   = document.getElementsByName('PurchaseTacticsSearch[daily_sales_value][]');
    var daily_sales_day     = document.getElementsByName('PurchaseTacticsSearch[daily_sales_day][]');
    
    var total_value = 0;
    for(var i=0;i< daily_sales_value.length;i++){
        if(daily_sales_value[i].value == '' && daily_sales_day[i].value == '') continue;
        if(parseFloat(daily_sales_value[i].value) == 0){
            layer.alert('日均销量 比值必须大于 0');
            return;
        }
        total_value = total_value + parseFloat(daily_sales_value[i].value);
    }
    if(total_value !== 1){
        layer.alert('日均销量 比值加起来必须为 1');
        return;
    }
    
    var tactics_id = $("#purchasetacticssearch-id").val();
    var tactics_name = $("#purchasetacticssearch-tactics_name").val();
    
    $.get("$checkTacticsInfoUrl" ,{tactics_id:tactics_id,tactics_name:tactics_name,data:$("#form-id").serializeArray()},function (data) {
        var re_data = jQuery.parseJSON(data);
        if(re_data.status == 'error'){
            layer.alert(re_data.message);
            return ;
        }else{
            $("#form-id").submit();
        }
    });
    
});

// 改变页面显示的内容
function change_content(){
    if(now_step == 'step1'){
        $("#modal-title").html('配置补货策略');
    }else if(now_step == 'step2'){
        $("#modal-title").html('创建逻辑');
    }else{
        $("#modal-title").html('仓库配置');
    }
    
    // 把页面所有 分区DIV都隐藏
    document.getElementById("step1").style.display = "none";
    document.getElementById("step2").style.display = "none";
    document.getElementById("step3").style.display = "none";
    // 展示当前 步骤页面 的内容
    document.getElementById(now_step).style.display = "block";
    
    
    // 控制 返回上一步、下一步、提交 的显示与隐藏
    if(now_step == 'step1'){
        $("#span-back").hide();
        $("#span-next").show();
        $("#span-submit").hide();
    }
    
    if(now_step == 'step2'){
        $("#span-back").show();
        $("#span-next").show();
        $("#span-submit").hide();
    }
    
    if(now_step == 'step3'){
        $("#span-back").show();
        $("#span-next").hide();
        $("#span-submit").show();
    }
}


/* 自动替换掉 输入框中的非数字 字符 */
$(document).on("keyup",".int_input",function(){
    $(this).val($(this).val().replace(/[^\d]/g,''));
});

/* 自动替换掉 输入框中的非数字和小数点 字符 */
$(document).on("keyup",".float_int_input",function(){
    $(this).val($(this).val().replace(/[^\d.]/g, ''));
});

JS;
$this->registerJs($js);
?>
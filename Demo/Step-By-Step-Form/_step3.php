<?php
use yii\helpers\Html;

use yii\web\JsExpression;
use kartik\select2\Select2;
use app\models\PurchaseTacticsWarehouse;


?>

<div class="row">
    <div class="col-md-1" style="width: 100px;">仓库名称：</div>
    <div class="col-md-4" style="float: left">
        <?= $form->field($model, 'warehouse_list_select')->widget(Select2::classname(), [
            'options' => ['placeholder' => '搜索仓库'],
            'data' => $warehouseList,
            'pluginOptions' => [ 'language' => ['errorLoading' => new JsExpression("function () { return 'Waiting...'; }"),] ]
        ])->label(false)?>
    </div>
</div>
<br/>

<?php
if($warehouseList){
    // 一个仓库只能有一个备货策略，一个策略可以有多个仓库
    // 查询已经有备货策略的仓库（不展示出来）
    $noShowWarehouseList =  PurchaseTacticsWarehouse::find()
        ->select('warehouse_code')
        ->andFilterWhere(['!=','tactics_id',$model->id])
        ->groupBy('warehouse_code')
        ->createCommand()
        ->queryAll();
    $noShowWarehouseList = array_column($noShowWarehouseList,'warehouse_code');

    // 编辑时 之前的适用仓库列表
    $purchaseTacticsWarehouse = $model->purchaseTacticsWarehouse;
    $oldWarehouseList = [];
    foreach($purchaseTacticsWarehouse as $value){
        $oldWarehouseList[] = $value->warehouse_code;
    }

    // 去掉不展示出来的仓库
    foreach($noShowWarehouseList as $code){
        unset($warehouseList[$code]);
    }

    // 所有仓库列表 拆分成多行排列，每行显示4个仓库
    $warehouseListTmp = array_chunk($warehouseList,3,true);
    foreach($warehouseListTmp as $warehouse){ ?>
        <?= $form->field($model, 'warehouse_list[][]')->checkboxList($warehouse,[ 'value' => $oldWarehouseList,
            'item' => function($index, $label, $name, $checked, $value){
                $checkStr = $checked?"checked":"";
                return '<div class="col-md-4"><input type="checkbox" name="'.$name.'" value="'.$value.'" '.$checkStr.' >'.$label.'</div>';
            }
        ])->label(false); ?>
        <?php
    }
}
?>
<?php

$js = <<<JS

$("#purchasetacticssearch-warehouse_list_select").change(function(){
    var code = $(this).val();
    if(code){
        $("input[name='PurchaseTacticsSearch[warehouse_list][][]']").each(function(){
            if($(this).val() == code){
                $(this).prop('checked',true);
            }
        });
    }
});

JS;
$this->registerJs($js);


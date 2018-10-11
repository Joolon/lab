<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\daterange\DateRangePicker;
use app\services\BaseServices;
use app\services\SupplierServices;
use app\services\PurchaseOrderServices;
use kartik\select2\Select2;
use yii\web\JsExpression;


?>
<div class="purchase-order-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="col-md-1"><?=$form->field($model, 'sku')->label('SKU') ?></div>
    <div class="col-md-1">
        <?= $form->field($model, 'audit_status')->dropDownList(['-1'=>'待审核','100'=>'已审核','1' => '审核通过','2' =>'审核不通过'],['prompt'=>'请选择'])->label('审核状态') ?>
    </div>
    <div class="col-md-3" ><label class="control-label" for="purchaseorderpaysearch-applicant">创建时间：</label><?php
        $addon = <<< HTML
<span class="input-group-addon">
    <i class="glyphicon glyphicon-calendar"></i>
</span>
HTML;
        echo '<div class="input-group drp-container">';
        echo DateRangePicker::widget([
                'name' => 'ProductRepackageSearch[time]',
                'useWithAddon' => true,
                'convertFormat' => true,
                'startAttribute' => 'ProductRepackageSearch[start_time]',
                'endAttribute' => 'ProductRepackageSearch[end_time]',
                'startInputOptions' => ['value' => !empty($model->start_time) ? $model->start_time : date('Y-m-d H:i:s', strtotime("last month"))],
                'endInputOptions' => ['value' => !empty($model->end_time) ? $model->end_time : date('Y-m-d 23:59:59', time())],
                'pluginOptions' => [
                    'locale' => ['format' => 'Y-m-d H:i:s'],
                ]
            ]) . $addon;
        echo '</div>';
        ?></div>
    <div class="form-group col-md-2" style="margin-top: 24px;float:left">
        <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>

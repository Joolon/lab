<?php

use app\config\Vhelper;
use mdm\admin\components\Helper;
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\bootstrap\Modal;
use yii\helpers\Url;
use app\models\User;

use app\models\PurchaseTactics;
use app\models\PurchaseTacticsSuggest;
use app\models\PurchaseTacticsWarehouse;
use app\models\PurchaseTacticsSearch;

$this->title = 'MRP备货逻辑配置';
$this->params['breadcrumbs'][] = $this->title;


Modal::begin([
    'id' => 'create-modal',
    'header' => '<h4 class="modal-title" id="modal-title">配置补货策略</h4>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();


Modal::begin([
    'id' => 'view-modal',
    'header' => '<h4 class="modal-title" id="modal-title">配置详情</h4>',
    'size'=>'modal-lg',
    'options'=>[
        'data-backdrop'=>'static',//点击空白处不关闭弹窗
    ],
]);
Modal::end();

?>
<?php
if (Helper::checkRoute('create')) {
    echo Html::a('创建MRP逻辑', ['create-data'], ['class' => 'btn btn-sm btn-primary', 'id' => 'create', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
}
?>

<?php
echo GridView::widget([
    'dataProvider'=>$dataProvider,
    'filterSelector' => "select[name='".$dataProvider->getPagination()->pageSizeParam."'],input[name='".$dataProvider->getPagination()->pageParam."']",

    'pager'=>[
        'options'=>['class' => 'pagination','style'=> "display:block;"],
        'class'=>\liyunfang\pager\LinkPager::className(),
        'pageSizeList' => [20, 50, 100, 200],
        'firstPageLabel'=>"首页",
        'prevPageLabel'=>'上一页',
        'nextPageLabel'=>'下一页',
        'lastPageLabel'=>'末页',
    ],
    'options'=>[
        'id'=>'tactics_list',
    ],
    'columns'=>[
        [
            'class'=>'kartik\grid\CheckboxColumn',
            'name'=>"id",
            'checkboxOptions'=>function ($model,$key,$index,$column){
                return ['value'=>$model->id];
            }
        ],
        [
            'label'=>'备货名称',
            'attribute'=>'tactics_name',
            'value'=>function($model){
                $html = $model->tactics_name;
                return $html;
            }
        ],
        [
            'label'=>'适用仓库',
            'format'=>'raw',
            'attribute'=>'warehouse_list',
            'width' => '25%',
            'value'=>function($model){
                $warehouseList = PurchaseTacticsSearch::getWarehouseList();

                $purchaseTacticsWarehouse = $model->purchaseTacticsWarehouse;

                $html = '';
                foreach($purchaseTacticsWarehouse as $value){
                    $html .= $warehouseList[$value->warehouse_code].'、';
                }
                return trim($html,'、');
            }
        ],
        [
            'label'=>'创建人',
            'format'=>'raw',
            'attribute'=>'creator',
            'value'=>function($model){
                $userInfo = User::findOne($model->creator);
                if($userInfo){
                    return $userInfo->username;
                }
            }
        ],
        [
            'label'=>'创建时间',
            'format'=>'raw',
            'attribute'=>'created_at',
            'value'=>function($model){
                $html = $model->created_at;
                return $html;
            }
        ],
        [
            'label'=>'是否启用',
            'format'=>'raw',
            'attribute'=>'status',
            'width' => '100px;',
            'value'=>function($model){
                if($model->status == 1){
                    $html = '<span class="glyphicon glyphicon-ok" style="color: green"></span>';
                }else{
                    $html = '<span class="glyphicon glyphicon-remove" style="color: red"></span>';
                }
                return $html;
            }
        ],
        [
            'header' => '操作',
            'class' => 'yii\grid\ActionColumn',
            'template'=> ' {status} {edit} {view}',
            'headerOptions' => ['width' => '140'],
            'buttons' => [
                'edit' => function ($url, $model, $key) {
                    return Html::a('编辑',['create-data', 'id' => $model->id], ['class' => "btn btn-xs btn-success",'id' => 'create', 'title' => '编辑', 'data-toggle' => 'modal', 'data-target' => '#create-modal']);
                },
                'view' => function ($url, $model, $key) {
                    return Html::a('查看',['view', 'id' => $model->id], ['class' => "btn btn-xs btn-warning",'id' => 'view', 'title' => '查看', 'data-toggle' => 'modal', 'data-target' => '#view-modal']);
                },
                'status' => function ($url, $model, $key) {
                    if($model->status == 1){
                        return Html::a('禁用',['change-status', 'id' => $model->id,'status' => 2], ['class' => "btn btn-xs btn-primary",'id' => 'change-status', 'title' => '禁用此策略']);
                    }else{
                        return Html::a('启用',['change-status', 'id' => $model->id,'status' => 1], ['class' => "btn btn-xs btn-primary",'id' => 'change-status', 'title' => '启用此策略']);
                    }
                },
            ]
        ],
    ],
    'containerOptions' => ["style" => "overflow:auto"],
    'pjax' => false,
    'bordered' => true,
    'striped' => false,
    'condensed' => true,
    'responsive' => true,
    'hover' => true,
    'floatHeader' => false,
    'showPageSummary' => false,
    'exportConfig' => [
        GridView::EXCEL => [],
    ],
    'panel' => [
        'before' => false,
        'after' => false,
        'heading' => '<h3 class="panel-title"><i class="glyphicon glyphicon-stats"></i> </h3>',
        'type' => 'success',
        //'after'=>Html::a('<i class="glyphicon glyphicon-repeat"></i> 刷新', ['index'], ['class' => 'btn btn-info']),
    ]
]);
?>

<?php

$js = <<<JS
$(function() {
    /**
     * 修改状态
     */
    $(document).on('click', '#change-status', function () {
        $.get($(this).attr('href'), { },function (data) {
            
        });
    });
    
    /**
     * 新增
     */
    $(document).on('click', '#create', function () {
        $.get($(this).attr('href'), { },function (data) {
            $('#create-modal .modal-body').html(data);
        });
    });
    
    /**
     * 查看
     */
    $(document).on('click', '#view', function () {
        $.get($(this).attr('href'), { },function (data) {
            $('#view-modal .modal-body').html(data);
        });
    });

});
JS;
$this->registerJs($js);
?>
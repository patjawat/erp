<?php

use kartik\grid\GridView;
use kartik\widgets\SwitchInput;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */
/** @var yii\widgets\ActiveForm $form */
?>
    <?php Pjax::begin(['id' => 'am-group-container', 'timeout' => 5000]);?>
<?=
GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => 'code',
            'format' => 'raw',
            'hAlign' => 'center',
            'vAlign' => 'middle',
            'width' => '80px',
            'label' => 'หมวดหมู่',
        ],
        [
            //'class' => 'kartik\grid\EditableColumn',
            'label' => 'สถานะ',
            'hAlign' => 'center',
            'vAlign' => 'middle',
            'mergeHeader' => true,
            'format' => 'raw',
            'value' => function ($model) {
                return SwitchInput::widget([
                    'name' => 'active',
                    'value' => $model->active,
                    'id' => $model->id,
                    'pluginEvents' => [
                        "init.bootstrapSwitch" => "function() { console.log('ini'); }",
                        "switchChange.bootstrapSwitch" => "function() {
                                // console.log($(this).is(':checked'));
                                var checked = $(this).is(':checked');
                               var id = $(this).attr('id');
                                updateStatusFsnGroup(id,checked);
                             }",
                    ],
                    'pluginOptions' => [
                        'labelText' => '<i class="fas fa-fullscreen"></i>',
                        'onText' => '<i class="fas fa-check"></i>',
                        'offText' => '<i class="fas fa-remove"></i>',
                        'onColor' => 'success',
                        'offColor' => 'danger',
                        'size' => 'mini',
                        // 'onText' => 'ปิด',
                        // 'offText' => 'เปิด',
                    ],
                    'labelOptions' => ['style' => 'font-size: 12px;'],
                ]);
            },
        ],
        'title',
        // ['class' => 'yii\grid\ActionColumn'],
    ],

])
?>
 <?php Pjax::end();?>

<?php
$activeUrl = Url::to(['/am/fsn/update-status-group']);
$js = <<< JS
function updateStatusFsnGroup(id,active)
{
    $.ajax({
        type: "post",
        url: "$activeUrl",
        data: {
            id:id,
            active:active
        },
        dataType: "json",
        success: function (response) {
            if(response.status == 'success'){
                $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});
                success();
            }
            console.log(response);
        }
    });
}

JS;
$this->registerJS($js);
?>

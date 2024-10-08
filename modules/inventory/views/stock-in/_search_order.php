<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\OrderSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
        'action' => ['/inventory/stock-in/list-pending-order'],
        'method' => 'get',
        'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="d-flex justify-content-between gap-3 align-items-center align-middle">
    <?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>

    <?=$form->field($model, 'order_type_name')->widget(Select2::classname(), [
                                    'data' => ArrayHelper::map($model->ListItemTypeOrder(),'id','name'),
                                    'options' => ['placeholder' => 'เลือกประเภท'],
                                    'pluginOptions' => [
                                        'width' => '200px',
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        'select2:select' => "function(result) { 
                                                  $(this).submit()
                                                }",
                                                'select2:unselecting' => "function(result) { 
                                                    $(this).submit()
                                                  }",
                                                
                                    ]
                                ])->label(false);
                        ?>


</div>
<?php ActiveForm::end(); ?>
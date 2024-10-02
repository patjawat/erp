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
        'action' => ['index'],
        'method' => 'get',
        'fieldConfig' => ['options' => ['class' => 'form-group mb-0']],
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="d-flex justify-content-between gap-3 align-items-center align-middle">
<?= $form->field($model, 'q')->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>
    <?php
                                echo $form->field($model, 'status')->widget(Select2::classname(), [
                                    'data' => ArrayHelper::map($model->ListStatus(),'code','title'),
                                    'options' => ['placeholder' => 'เลือกสถานะ'],
                                    'pluginOptions' => [
                                        'width' => '200px',
                                    'allowClear' => true,
                                    ],
                                    'pluginEvents' => [
                                        'select2:select' => "function(result) { 
                                                  $(this).submit()
                                                }",
                                    ]
                                ])->label(false);
                        ?>
                         <?= Html::a('<i class="bi bi-list-ul"></i>', ['#', 'view' => 'list'], ['class' => 'btn btn-outline-primary']) ?>
                         <?= Html::a('<i class="bi bi-grid"></i>', ['#', 'view' => 'grid'], ['class' => 'btn btn-outline-primary']) ?>
                        </div>
    <?php ActiveForm::end(); ?>
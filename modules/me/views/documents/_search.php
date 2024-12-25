<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="d-flex justify-content-between align-top align-items-center gap-1">
    <?= $form->field($model, 'q')->label('คำค้นหา...') ?>
    <?php
        echo $form->field($model, 'thai_year')->widget(Select2::classname(), [
            'data' => $model->ListThaiYear(),
            'options' => ['placeholder' => 'เลือก พ.ศ.'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '130px',
            ],
            'pluginEvents' => [
                'select2:select' => 'function(result) { 
                        $(this).submit()
                        }',
                'select2:unselecting' => 'function() {
                            $(this).submit()
                        }',
            ]
        ])->label('ปี พ.ศ.');
        ?>
    <?= $form->field($model, 'document_group')->hiddenInput()->label(false) ?>
        <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary mt-4']) ?>
    </div>

    <?php ActiveForm::end(); ?>


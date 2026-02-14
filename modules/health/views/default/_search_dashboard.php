<?php

use kartik\widgets\Select2;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeeDetailSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="employee-detail-search">

    <?php $form = ActiveForm::begin([
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <div class="d-flex align-items-center gap-2">
        <?= $form->field($model, 'thai_year', ['showLabels' => false])->widget(Select2::classname(), [
            'data' => $model->getYearList(), // สร้างฟังก์ชันดึงปีใน Model
            'options' => ['placeholder' => 'เลือกปีที่ตรวจ...'],
            'pluginOptions' => [
                'allowClear' => true,
                'width' => '200px'
            ],
        ]) ?>
        <?= Html::submitButton('<i data-lucide="search"></i> ', ['class' => 'btn btn-sm btn-primary mb-3']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
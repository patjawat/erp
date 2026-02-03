<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\models\CategoriseSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1,
            'class' => 'form-inline' // ใช้ class นี้ถ้าต้องการให้ทุกอย่างอยู่ในบรรทัดเดียว
        ],
    ]); ?>

    <div class="row w-100 align-items-end mb-4">
        <div class="col-md-10">
            <?= $form->field($model, 'title', [
                'options' => ['class' => 'form-group mb-0'], // ลด margin ด้านล่าง
            ])->textInput(['placeholder' => 'ระบุคำค้นหา...'])->label(false) ?>
        </div>
        
        <div class="col-md-2">
            <div class="form-group mb-0">
                <?= Html::submitButton('<i class="fas fa-search"></i>', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>
    </div>

    <?php ActiveForm::end(); ?>


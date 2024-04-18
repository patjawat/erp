<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="employees-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>
    <div class="d-flex gap-3">

        
        <?= $form->field($model, 'fullname')->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('<i class="fa-solid fa-rotate-right"></i> คืนค่า', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

</div>

    <?php ActiveForm::end(); ?>

</div>

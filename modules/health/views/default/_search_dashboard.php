<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

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

<div class="d-flex">
    <?= $form->field($model, 'thai_year')->label(false) ?>
    <div class="form-group">
        <?= Html::submitButton('<i data-lucide="search"></i> ', ['class' => 'btn btn-primary']) ?>
    </div>
</div>

    <?php ActiveForm::end(); ?>

</div>

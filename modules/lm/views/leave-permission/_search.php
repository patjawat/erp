<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\LeavePermissionSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
    'action' => ['index'],
    'method' => 'get',
    'options' => [
        'data-pjax' => 1
    ],
]); ?>

<div class="d-flex justify-content-between gap-3">
    <?= $form->field($model, 'thai_year') ?>
</div>

    <?php ActiveForm::end(); ?>


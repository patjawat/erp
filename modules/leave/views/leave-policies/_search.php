<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\LeavePoliciesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="leave-policies-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id')->label(false) ?>


    <div class="form-group">
        <?php //  Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?php //  Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

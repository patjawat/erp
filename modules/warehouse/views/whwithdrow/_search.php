<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\warehouse\models\WhwithdrowSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="whwithdrow-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'ref') ?>

    <?= $form->field($model, 'withdrow_code') ?>

    <?= $form->field($model, 'withdrow_date') ?>

    <?= $form->field($model, 'withdrow_store') ?>

    <?php // echo $form->field($model, 'withdrow_dep') ?>

    <?php // echo $form->field($model, 'withdrow_hr') ?>

    <?php // echo $form->field($model, 'withdrow_pay') ?>

    <?php // echo $form->field($model, 'withdrow_status') ?>

    <?php // echo $form->field($model, 'data_json') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

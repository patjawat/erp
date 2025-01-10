<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\dms\models\DocumentsDetailSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="documents-detail-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'ref') ?>

    <?= $form->field($model, 'name') ?>

    <?= $form->field($model, 'document_id') ?>

    <?= $form->field($model, 'to_id') ?>

    <?php // echo $form->field($model, 'to_name') ?>

    <?php // echo $form->field($model, 'to_type') ?>

    <?php // echo $form->field($model, 'from_id') ?>

    <?php // echo $form->field($model, 'from_name') ?>

    <?php // echo $form->field($model, 'from_type') ?>

    <?php // echo $form->field($model, 'created_at') ?>

    <?php // echo $form->field($model, 'updated_at') ?>

    <?php // echo $form->field($model, 'created_by') ?>

    <?php // echo $form->field($model, 'updated_by') ?>

    <?php // echo $form->field($model, 'deleted_at') ?>

    <?php // echo $form->field($model, 'deleted_by') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

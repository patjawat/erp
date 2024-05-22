<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\ProductSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="product-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<?php
echo $form->field($model, 'data_json[checkbox1]')->checkboxList(
    $model->ListProductType(),
    ['custom' => true, 'id' => 'custom-checkbox-list']
)->label('ประเภทของวัสดุ');
?>

    <?php // echo $form->field($model, 'fsn_number') ?>

    <?php // echo $form->field($model, 'qty') ?>

    <?php // echo $form->field($model, 'receive_date') ?>

    <?php // echo $form->field($model, 'price') ?>

    <?php // echo $form->field($model, 'purchase') ?>

    <?php // echo $form->field($model, 'department') ?>

    <?php // echo $form->field($model, 'repair') ?>

    <?php // echo $form->field($model, 'owner') ?>

    <?php // echo $form->field($model, 'life') ?>

    <?php // echo $form->field($model, 'device_items') ?>

    <?php // echo $form->field($model, 'on_year') ?>

    <?php // echo $form->field($model, 'dep_id') ?>

    <?php // echo $form->field($model, 'depre_type') ?>

    <?php // echo $form->field($model, 'budget_year') ?>

    <?php // echo $form->field($model, 'asset_status') ?>

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

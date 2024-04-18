<?php

use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var frontend\models\EmployeesSearch $model */
/** @var yii\widgets\ActiveForm $form */
?>


<?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
        'type' => ActiveForm::TYPE_FLOATING,
        'options' => [
            'data-pjax' => 1
        ],
    ]); ?>

<div class="row filter-row">
    <div class="col-sm-6 col-md-3">
        <?= $form->field($model, 'lname') ?>
    </div>
    <div class="col-sm-6 col-md-3">
        <?= $form->field($model, 'fname')->label('ชื่อ-สกุล') ?>

    </div>
    <div class="col-sm-6 col-md-3" data-select2-id="select2-data-24-yy9w">
        <?= $form->field($model, 'id') ?>

    </div>
    <div class="col-sm-6 col-md-3">
        <div class="form-group mt-3">
            <?= Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-success w-100']) ?>
        </div>

    </div>
</div>



<?php ActiveForm::end(); ?>
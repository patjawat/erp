<?php

use yii\helpers\Html;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm; // or kartik\widgets\ActiveForm
use app\components\AppHelper;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeeDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<style>
    .modal-footer {
        display: none !important;
    }
</style>
<div class="employee-detail-form">

    <?php
    $form = ActiveForm::begin([
        'id' => 'form-emp-detail',
        'enableAjaxValidation'      => true, //เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/hr/employee-detail/validator']
    ]);
    ?>

    <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $this->render($model->name, ['form' => $form, 'model' => $model]); ?>
    <div class="form-group mt-4 d-flex justify-content-center">
        <?= AppHelper::BtnSave(); ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>


<?php
$js = <<<JS

    handleFormSubmit('#form-emp-detail', null, async function(response) {
        await location.reload();
    });
JS;
$this->registerJS($js, View::POS_END)
?>
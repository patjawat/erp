<?php

use yii\helpers\Html;
// use yii\bootstrap5\ActiveForm;
use kartik\form\ActiveForm; // or kartik\widgets\ActiveForm
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeeDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

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
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-4">
            <?= $this->render($model->name, ['form' => $form, 'model' => $model]); ?>
        </div>

        <?php if ($model->name !== 'health'): ?>
        <div class="card-footer bg-body border-top px-4 py-3">
            <div class="d-flex flex-column flex-sm-row justify-content-center align-items-sm-center gap-3">
                <div class="d-flex flex-wrap gap-2">
                    <?= Html::submitButton('<i class="fa-solid fa-check me-1"></i> บันทึกข้อมูล', [
                        'class' => 'btn btn-primary rounded-3 fw-semibold',
                    ]) ?>
                    <?= Html::button('<i class="fa-solid fa-xmark me-1"></i> ปิด', [
                        'class' => 'btn btn-outline-secondary rounded-3 fw-semibold',
                        'data-bs-dismiss' => 'modal',
                    ]) ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>


<?php
$js = <<<JS

    handleFormSubmit('#form-emp-detail', null, async function(response) {
        if (response && response.container) {
            if (typeof window.erpReloadPjax === 'function' && window.erpReloadPjax(response.container)) {
                return;
            }
            location.reload();
        }
    });
JS;
$this->registerJS($js, View::POS_END)
?>

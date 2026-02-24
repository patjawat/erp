<?php

use yii\web\View;
use yii\helpers\Url;

use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\modules\hr\models\Employees;
use app\modules\inventory\models\Warehouse;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
/** @var yii\widgets\ActiveForm $form */
?>

<?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/inventory/stock-in/create-validator']
]); ?>
<?= $form->field($model, 'movement_date')->widget(\app\widgets\datepicker\DatepickerThai::class)->label('วันที่จ่าย'); ?>

<div class="form-group mt-3 d-flex justify-content-center gap-2">
    <?= Html::submitButton('<i class="fa-solid fa-circle-check"></i> ตกลง', ['class' => 'btn btn-primary shadow', 'id' => 'summit']) ?>
    <?= Html::button('<i class="fa-solid fa-circle-xmark"></i> ปิด', [
        'class' => 'btn btn-secondary',
        'data-bs-dismiss' => 'modal'
    ]) ?>
</div>

<?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS

handleFormSubmit('#form', null, async function(response) {
    if (response.status === 'success') {
        success("บันทึกสำเร็จ!");
        setTimeout(() => {
            window.location.href = response.url;
        }, 1000); // หน่วง 1 วินาที
    }
});
JS;
$this->registerJS($js)
?>
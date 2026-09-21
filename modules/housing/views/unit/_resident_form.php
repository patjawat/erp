<?php

use app\components\AppHelper;
use app\modules\housing\models\Resident;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var Resident $model */
/** @var \app\modules\housing\models\Occupancy $occupancy */

$formId = 'housing-resident-form';
$isEmployee = $model->isEmployee();
$birthThai = $model->birth_date ? AppHelper::convertToThai($model->birth_date) : '';
$location = trim(($occupancy->unit?->name ?? '') . ($occupancy->room ? ' / ' . $occupancy->room->name : ''));
$form = ActiveForm::begin(['id' => $formId]);
?>
<div class="bg-body-tertiary border rounded-3 p-3 mb-3">
    <div class="fw-semibold"><?= Html::encode($occupancy->employee?->fullname() ?: ('รหัสบุคลากร ' . $occupancy->emp_id)) ?></div>
    <div class="small text-body-secondary"><?= Html::encode($location ?: 'บ้านพัก') ?></div>
</div>
<?php if ($isEmployee): ?>
    <div class="alert alert-info small mb-3"><i class="bi bi-info-circle"></i> รายการนี้คือเจ้าหน้าที่ผู้ครอบครองห้องพัก แก้ไขได้เฉพาะข้อมูลติดต่อ</div>
<?php endif; ?>
<div class="row g-3">
    <div class="col-4 col-md-3"><?= $form->field($model, 'prefix')->textInput(['maxlength' => true]) ?></div>
    <div class="col-8 col-md-4"><?= $form->field($model, 'first_name')->textInput(['maxlength' => true, 'readonly' => $isEmployee]) ?></div>
    <div class="col-12 col-md-5"><?= $form->field($model, 'last_name')->textInput(['maxlength' => true, 'readonly' => $isEmployee]) ?></div>
    <div class="col-md-6">
        <?= $form->field($model, 'relationship')->dropDownList(Resident::relationshipOptions(), [
            'prompt' => '— เลือกความสัมพันธ์ —',
            'disabled' => $isEmployee,
        ])->hint($isEmployee ? 'เจ้าหน้าที่ผู้ครอบครอง (หัวหน้าครัวเรือน)' : '') ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">วันเดือนปีเกิด</label>
        <?= DatepickerThai::widget(['name' => 'Resident[birth_date]', 'value' => $birthThai, 'options' => ['id' => 'resident-birth-date']]) ?>
        <div class="form-text">ใช้คำนวณผู้พักอายุเกิน 15 ปีสำหรับคิดค่าใช้จ่ายรายหัว</div>
    </div>
    <div class="col-md-6"><?= $form->field($model, 'citizen_id')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'phone')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'status')->dropDownList(Resident::statusOptions()) ?></div>
    <div class="col-md-6 d-flex align-items-center">
        <div class="form-check mt-4">
            <?= Html::activeCheckbox($model, 'count_for_charge', ['label' => 'นับรวมคิดค่าใช้จ่ายรายหัว']) ?>
        </div>
    </div>
    <div class="col-12"><?= $form->field($model, 'note')->textarea(['rows' => 2]) ?></div>
</div>
<div class="mt-3 d-flex justify-content-end gap-2">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton($model->isNewRecord ? 'เพิ่มผู้พักอาศัย' : 'บันทึก', ['class' => 'btn btn-primary']) ?>
</div>
<?php
ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){if(r&&r.redirect){location.href=r.redirect;return;}location.reload();});");
?>

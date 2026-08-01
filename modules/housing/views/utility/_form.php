<?php
use app\modules\housing\models\ChargeType;
use app\modules\housing\models\Meter;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['id' => 'utility-form']);
?>
<?php if ($kind === 'charge-type'): ?>
<div class="row g-3">
    <div class="col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'เช่น WATER, RENT_FRIDGE']) ?></div>
    <div class="col-md-8"><?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'เช่น ค่าน้ำประปา']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'category')->dropDownList(ChargeType::categoryOptions()) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'calculation_method')->dropDownList(ChargeType::methodOptions()) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'unit_name')->textInput(['maxlength' => true, 'placeholder' => 'หน่วย, ห้อง, คน, เครื่อง']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'sort_order')->input('number', ['min' => 0]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'status')->dropDownList(['active' => 'เปิดใช้งาน', 'inactive' => 'ปิดใช้งาน']) ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 3]) ?></div>
</div>
<?php elseif ($kind === 'period'): ?>
<div class="row g-3">
    <div class="col-md-4"><?= $form->field($model, 'period_code')->textInput(['placeholder' => 'เช่น 2026-07']) ?></div>
    <div class="col-md-8"><?= $form->field($model, 'name')->textInput(['placeholder' => 'เช่น กรกฎาคม 2569']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'start_date')->input('date') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'end_date')->input('date') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'due_date')->input('date') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'external_electric_total')->input('number', ['step' => '.01', 'min' => 0]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'external_water_total')->input('number', ['step' => '.01', 'min' => 0]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'status')->dropDownList(['open' => 'เปิดรับข้อมูล', 'closed' => 'ปิดรอบ', 'cancelled' => 'ยกเลิก']) ?></div>
    <div class="col-12"><?= $form->field($model, 'note')->textarea(['rows' => 2]) ?></div>
</div>
<?php elseif ($kind === 'rate'): ?>
<div class="row g-3">
    <div class="col-md-6"><?= $form->field($model, 'charge_type_id')->dropDownList($chargeTypes, ['prompt' => 'เลือกประเภท']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'calculation_type')->dropDownList(['flat' => 'เหมาจ่าย', 'per_unit' => 'ต่อหน่วย']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'building_id')->dropDownList($buildings, ['prompt' => 'ทุกบ้านพัก']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'unit_id')->dropDownList($units, ['prompt' => 'ทุกห้อง']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'rate')->input('number', ['step' => '.01']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'minimum_charge')->input('number', ['step' => '.01']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'effective_from')->input('date') ?></div>
    <div class="col-md-3"><?= $form->field($model, 'effective_to')->input('date') ?></div>
</div>
<?php elseif ($kind === 'meter'): ?>
<div class="row g-3">
    <div class="col-md-4"><?= $form->field($model, 'meter_type')->dropDownList(Meter::typeOptions()) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'meter_no')->textInput() ?></div>
    <div class="col-md-4"><?= $form->field($model, 'name')->textInput() ?></div>
    <div class="col-md-6"><?= $form->field($model, 'building_id')->dropDownList($buildings, ['prompt' => 'เลือกอาคาร']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'unit_id')->dropDownList($units, ['prompt' => 'ไม่ระบุห้อง']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'installed_at')->input('date') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'status')->dropDownList(['active' => 'ใช้งาน', 'inactive' => 'งดใช้งาน']) ?></div>
</div>
<?php else: ?>
<div class="row g-3">
    <div class="col-md-6"><?= $form->field($model, 'meter_id')->dropDownList($metersList, ['prompt' => 'เลือกมิเตอร์']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'billing_period_id')->dropDownList($periods, ['prompt' => 'ยังไม่ผูกรอบบิล']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'reading_date')->input('date') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'previous_value')->input('number', ['step' => '.01', 'readonly' => true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'current_value')->input('number', ['step' => '.01']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'unit_rate')->input('number', ['step' => '.01']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'minimum_charge')->input('number', ['step' => '.01']) ?></div>
</div>
<?php endif; ?>
<div class="mt-3 text-end">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('บันทึกข้อมูล', ['class' => 'btn btn-primary']) ?>
</div>
<?php
ActiveForm::end();
$this->registerJs("handleFormSubmit('#utility-form',null,function(r){if(r&&r.redirect)location.href=r.redirect;});");
?>

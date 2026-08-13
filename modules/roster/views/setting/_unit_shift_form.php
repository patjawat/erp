<?php

use app\modules\roster\models\UnitShift;
use kartik\widgets\TimePicker;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var UnitShift $model */
/** @var app\modules\roster\models\ShiftType[] $types */

$typeOptions = ArrayHelper::map($types, 'id', 'title');

// เฉพาะตำแหน่งที่มีคนขึ้นเวรจริง — ไม่งั้นดรอปดาวน์ยาวเป็นร้อยรายการ
$positionOptions = ArrayHelper::map(
    (new \yii\db\Query())
        ->select(['ep.id', 'ep.title'])
        ->from(['ep' => 'employee_position'])
        ->innerJoin(['e' => 'employees'], 'e.employee_position_id = ep.id AND e.status = 1')
        ->where(['ep.active' => 1])
        ->groupBy(['ep.id', 'ep.title'])
        ->orderBy(['ep.title' => SORT_ASC])
        ->all(),
    'id',
    'title'
);
?>
<?php $form = ActiveForm::begin(['id' => 'form', 'options' => ['data-pjax' => false]]); ?>

<?= Html::activeHiddenInput($model, 'unit_id') ?>

<div class="row g-3">
    <div class="col-12 col-md-6">
        <?= $form->field($model, 'name')->textInput([
            'maxlength' => 100,
            'placeholder' => 'เช่น บ่ายดึก / Refer / On call',
        ])->hint('ชื่อที่หน่วยงานนี้ใช้เรียก') ?>
    </div>
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'short_name')->textInput([
            'maxlength' => 10,
            'placeholder' => 'บด / R / OC',
        ])->hint('ตัวที่แสดงในช่องกริด') ?>
    </div>
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'sort_order')->input('number', ['min' => 0, 'step' => 1]) ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-md-6">
        <?= $form->field($model, 'shift_type_id')->dropDownList($typeOptions, ['class' => 'form-select'])
            ->hint('ใช้จัดกลุ่มในรายงานรวมข้ามหน่วยงาน และผูกกับกฎเวรต่อเนื่อง — บ่ายดึกเลือก “ควบเวร” · วันหยุดเลือก “วันหยุด”') ?>
    </div>
    <div class="col-12 col-md-6">
        <?= $form->field($model, 'position_id')->dropDownList($positionOptions, [
            'prompt' => '— ไม่จำกัดวิชาชีพ —',
            'class' => 'form-select',
        ])->hint('ระบุเมื่อแยกเวรตามวิชาชีพเพราะอัตราต่างกัน (เช่น ชพ=พยาบาล ชผ=ผู้ช่วย) ระบบจะเตือนถ้าจัดคนผิดวิชาชีพ') ?>
    </div>
</div>

<div class="row g-3">
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'start_time')->widget(TimePicker::class, [
            'pluginOptions' => ['showMeridian' => false, 'defaultTime' => false, 'minuteStep' => 5],
            'options' => ['placeholder' => '16:00', 'class' => 'form-control shift-time'],
        ]) ?>
    </div>
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'end_time')->widget(TimePicker::class, [
            'pluginOptions' => ['showMeridian' => false, 'defaultTime' => false, 'minuteStep' => 5],
            'options' => ['placeholder' => '08:00', 'class' => 'form-control shift-time'],
        ]) ?>
    </div>
</div>

<div class="alert alert-info border-0 py-2 small" id="shift-hours-preview">
    <i class="bi bi-clock"></i> <span id="shift-hours-text">กรอกเวลาเข้า-ออกเพื่อคำนวณชั่วโมง</span>
</div>

<label class="form-label fw-semibold mt-2">จำนวนคนที่ต้องการ</label>
<div class="row g-2 mb-1">
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'required_staff')->input('number', ['min' => 0, 'max' => 99]) ?>
    </div>
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'required_sat')->input('number', ['min' => 0, 'max' => 99, 'placeholder' => 'ตามวันธรรมดา']) ?>
    </div>
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'required_sun')->input('number', ['min' => 0, 'max' => 99, 'placeholder' => 'ตามวันธรรมดา']) ?>
    </div>
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'required_holiday')->input('number', ['min' => 0, 'max' => 99, 'placeholder' => 'ตามวันธรรมดา']) ?>
    </div>
</div>
<div class="form-text mb-3">
    เว้นว่าง = ใช้ค่าวันธรรมดา · ใช้เตือนว่าจัดครบหรือยัง และเป็นเป้าหมายของการจัดเวรอัตโนมัติ
</div>

<div class="row g-3">
    <div class="col-6 col-md-4">
        <?= $form->field($model, 'pay_rate')->input('number', ['min' => 0, 'step' => '0.01', 'placeholder' => '600.00'])
            ->label('ค่าตอบแทน (บาท)') ?>
    </div>
    <div class="col-6 col-md-3">
        <?= $form->field($model, 'pay_unit')->dropDownList(UnitShift::payUnitLabels(), ['class' => 'form-select']) ?>
    </div>
    <div class="col-6 col-md-2 d-flex align-items-end pb-3">
        <div class="form-check form-switch">
            <?= Html::activeCheckbox($model, 'active', [
                'class' => 'form-check-input', 'label' => 'ใช้งาน',
                'labelOptions' => ['class' => 'form-check-label'],
            ]) ?>
        </div>
    </div>
</div>

<hr class="my-3">

<div class="form-check form-switch">
    <?= Html::activeCheckbox($model, 'is_standby', [
        'class' => 'form-check-input',
        'label' => 'เวรรอเรียก / ออกนอกหน่วย (On call, Refer)',
        'labelOptions' => ['class' => 'form-check-label fw-semibold'],
    ]) ?>
</div>
<div class="text-body-secondary small ms-4">
    ยกเว้นจากกฎ “พักระหว่างเวรขั้นต่ำ” และ “ทำงานติดต่อกันสูงสุด” เพราะไม่ใช่ชั่วโมงทำงานจริง<br>
    ถ้าไม่ติ๊ก คนที่รับ On call ทุกคืนจะถูกเตือนว่าทำงานติดกันทั้งเดือน
</div>

<?php ActiveForm::end(); ?>

<?php
$startId = Html::getInputId($model, 'start_time');
$endId = Html::getInputId($model, 'end_time');
$js = <<<JS
window.rosterShiftFormInit = function () {
    function calc() {
        var s = jQuery('#{$startId}').val(), e = jQuery('#{$endId}').val();
        var \$out = jQuery('#shift-hours-text');
        if (!s || !e) { \$out.text('กรอกเวลาเข้า-ออกเพื่อคำนวณชั่วโมง'); return; }
        var sp = s.split(':'), ep = e.split(':');
        var sm = parseInt(sp[0], 10) * 60 + parseInt(sp[1], 10);
        var em = parseInt(ep[0], 10) * 60 + parseInt(ep[1], 10);
        var cross = em <= sm;
        if (cross) { em += 1440; }
        var h = ((em - sm) / 60).toFixed(2).replace(/\\.?0+\$/, '');
        \$out.html('เวรนี้ยาว <strong>' + h + ' ชั่วโมง</strong>' + (cross ? ' · ข้ามเที่ยงคืน' : ''));
    }
    jQuery('#{$startId}, #{$endId}').off('change.shiftForm changeTime.shiftForm')
        .on('change.shiftForm changeTime.timepicker', calc);
    calc();

    // ActiveForm ใน modal ต้องผูก handleFormSubmit เอง ไม่งั้นจะ submit แบบเต็มหน้า
    // แล้วเบราว์เซอร์เด้งไปหน้า JSON ดิบแทนที่จะปิด modal
    handleFormSubmit('#form', null, async function (response) {
        if (response.container && typeof erpReloadPjax === 'function'
            && erpReloadPjax(response.container)) {
            return;
        }
        location.reload();
    });
};
window.rosterShiftFormInit();
JS;
$this->registerJs($js);
?>

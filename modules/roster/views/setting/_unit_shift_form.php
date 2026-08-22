<?php

use app\modules\roster\models\UnitShift;
use kartik\widgets\TimePicker;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var UnitShift $model */
/** @var app\modules\roster\models\ShiftType[] $types */
/** @var array $takenShorts อักษรย่อที่หน่วยนี้ใช้ไปแล้ว [ย่อ => ชื่อเวร] */

$typeOptions = ArrayHelper::map($types, 'id', 'title');

// สีของชิปมาจากหมวด จึงต้องส่งไปให้ JS อัปเดตตัวอย่างตอนเปลี่ยนหมวด
$typeClasses = [];
foreach ($types as $type) {
    $typeClasses[(int) $type->id] = $type->cellClass();
}

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
            'maxlength' => 2,
            'placeholder' => '1ช',
        ])->hint('2 ตัว: หน้า = สาย · หลัง = เวลา') ?>
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

<!-- ตัวอย่างชิปตามที่จะออกมาจริงในกริด — เห็นก่อนบันทึกว่าอ่านออกไหม ชนกับเวรอื่นหรือเปล่า -->
<div class="card border bg-body-tertiary mb-3">
    <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
        <span class="text-body-secondary small">ในตารางจะเห็นเป็น</span>
        <span class="shift-preview-chip" id="shift-preview-chip">—</span>
        <span class="small" id="shift-preview-text">—</span>
        <span class="ms-auto small" id="shift-preview-dupe"></span>
    </div>
</div>

<?php if ($takenShorts): ?>
    <div class="small text-body-secondary mb-3">
        อักษรย่อที่หน่วยนี้ใช้แล้ว:
        <?php foreach ($takenShorts as $code => $shiftName): ?>
            <span class="badge bg-body-secondary text-body border me-1"
                  title="<?= Html::encode($shiftName) ?>"><?= Html::encode($code) ?></span>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

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
$this->registerCss(<<<'CSS'
.shift-preview-chip {
    display: inline-block; min-width: 26px; padding: 2px 6px;
    border-radius: var(--bs-border-radius); font-size: .9rem; font-weight: 700; line-height: 1.35;
}
CSS);

$startId = Html::getInputId($model, 'start_time');
$endId = Html::getInputId($model, 'end_time');
$shortId = Html::getInputId($model, 'short_name');
$nameId = Html::getInputId($model, 'name');
$typeId = Html::getInputId($model, 'shift_type_id');
$rateId = Html::getInputId($model, 'pay_rate');
$unitId = Html::getInputId($model, 'pay_unit');
$typeClassJson = json_encode($typeClasses, JSON_UNESCAPED_UNICODE);
$takenJson = json_encode($takenShorts, JSON_UNESCAPED_UNICODE);

$js = <<<JS
window.rosterShiftFormInit = function () {
    var typeClasses = {$typeClassJson};
    var taken = {$takenJson};

    // ตัวอย่างชิป — ให้เห็นหน้าตาจริงในกริดก่อนบันทึก และรู้ทันทีถ้าอักษรย่อชนกับเวรอื่น
    function preview() {
        var short = (jQuery('#{$shortId}').val() || '').trim();
        var name = (jQuery('#{$nameId}').val() || '').trim();
        // ไม่กรอกอักษรย่อ = ระบบตัด 2 ตัวแรกของชื่อมาใช้ (ตรงกับ displayShort() ฝั่ง PHP)
        var label = short || name.substring(0, 2) || '—';
        var cls = typeClasses[jQuery('#{$typeId}').val()] || 'bg-secondary-subtle text-secondary-emphasis';

        jQuery('#shift-preview-chip').attr('class', 'shift-preview-chip ' + cls).text(label);

        var rate = parseFloat(jQuery('#{$rateId}').val());
        var perHour = jQuery('#{$unitId}').val() === 'hour';
        var money = (rate > 0)
            ? rate.toLocaleString() + (perHour ? ' บาท/ชม.' : ' บาท/เวร')
            : 'ยังไม่ระบุค่าตอบแทน';
        jQuery('#shift-preview-text').text((name || 'ยังไม่ระบุชื่อ') + ' · ' + money);

        var \$dupe = jQuery('#shift-preview-dupe');
        if (short && taken[short]) {
            \$dupe.html('<i class="bi bi-exclamation-triangle-fill"></i> ซ้ำกับ “' + taken[short] + '”')
                 .attr('class', 'ms-auto small text-danger-emphasis fw-semibold');
        } else {
            \$dupe.text('').attr('class', 'ms-auto small');
        }
    }
    jQuery('#{$shortId}, #{$nameId}, #{$typeId}, #{$rateId}, #{$unitId}')
        .off('input.shiftPreview change.shiftPreview')
        .on('input.shiftPreview change.shiftPreview', preview);
    preview();

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

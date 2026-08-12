<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;
use app\modules\purchase\models\Bond;
use app\modules\purchase\models\Contract;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Bond $model */

/**
 * คำอธิบายเกณฑ์ในกล่องคำแนะนำถูกดึงจากเซิร์ฟเวอร์ผ่าน bond/policy เสมอ
 * ไม่ได้เขียนเงื่อนไขวงเงินซ้ำไว้ใน JS — ที่ต้องทำแบบนี้เพราะโปรแกรมต้นแบบเขียน
 * ข้อความอธิบายเกณฑ์ไว้คนละที่กับตัวคำนวณ แล้วสองอย่างพูดไม่ตรงกันที่ยอด 100,000 พอดี
 */

$contracts = [];
foreach (
    Contract::find()
        ->where(['deleted_at' => null])
        ->orderBy(['id' => SORT_DESC])
        ->limit(300)
        ->all() as $contract
) {
    $contracts[$contract->id] = ($contract->contract_no ?: ($contract->doc_no ?: ('id ' . $contract->id)))
        . ' · ' . $contract->title;
}

// เฟสนี้ยังไม่มีตัวเลือกใบสั่งซื้อ จึงไม่เสนอให้เลือก เว้นแต่ใบนี้ผูกไว้อยู่แล้ว
$sourceTypes = Bond::sourceTypeList();
if ($model->source_type !== Bond::SOURCE_ORDER) {
    unset($sourceTypes[Bond::SOURCE_ORDER]);
}

$form = ActiveForm::begin([
    'id' => 'bond-form',
    'options' => ['autocomplete' => 'off'],
]);
?>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

<div class="row g-3">

    <div class="col-12">
        <div class="alert alert-light border mb-0 small">
            <i class="bi bi-info-circle me-1"></i>
            <strong>ประเภท</strong> คือหลักประกันตามกฎหมาย เป็นตัวบอกว่าคืนเมื่อไร
            (หลักประกันสัญญาคืนเมื่อพ้นข้อผูกพัน หลักประกันผลงานอยู่ต่อจนพ้นระยะรับประกัน)
            ส่วน <strong>รูปแบบ</strong> คือสิ่งที่คู่สัญญานำมาวาง เป็นตัวบอกว่าคืนอย่างไร
        </div>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'source_type')->dropDownList($sourceTypes, [
            'id' => 'bond-source-type',
        ])->hint('การผูกกับใบสั่งซื้อจะเปิดใช้ในเฟสถัดไป') ?>
    </div>
    <div class="col-md-8" id="bond-source-wrap">
        <?= $form->field($model, 'source_id')->dropDownList($contracts, [
            'prompt' => '— เลือกสัญญา —',
            'id' => 'bond-source-id',
            'class' => 'form-select',
        ])->label('สัญญาที่ผูก') ?>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'title')->textInput([
            'maxlength' => true,
            'placeholder' => 'เช่น ซื้อครุภัณฑ์คอมพิวเตอร์ จำนวน 10 เครื่อง',
        ]) ?>
    </div>

    <div class="col-md-5">
        <?= $form->field($model, 'vendor_id')->dropDownList(Bond::listVendor(), [
            'prompt' => '— เลือกจากทะเบียนผู้แทนจำหน่าย —',
            'class' => 'form-select',
        ]) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'vendor_name')->textInput([
            'maxlength' => true,
            'placeholder' => 'กรอกเองเมื่อยังไม่มีในทะเบียน',
        ])->hint('ชื่อที่พิมพ์ลงทะเบียนคุม') ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'thai_year')->input('number', ['min' => 2500, 'max' => 2700]) ?>
    </div>

    <div class="col-md-4">
        <?= $form->field($model, 'bond_type')->dropDownList(Bond::typeList()) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'bond_form')->dropDownList(Bond::formList()) ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'doc_ref')->textInput([
            'maxlength' => true,
            'placeholder' => 'เช่น LG-2569-001',
        ]) ?>
    </div>

    <div class="col-md-6">
        <?= $form->field($model, 'issuer')->textInput([
            'maxlength' => true,
            'placeholder' => 'เช่น ธนาคารกรุงไทย สาขาด่านซ้าย',
        ]) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'base_amount')->input('number', [
            'step' => '0.01',
            'min' => 0,
            'id' => 'bond-base-amount',
        ]) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'rate')->input('number', [
            'step' => '0.01',
            'min' => 0,
            'max' => 100,
            'id' => 'bond-rate',
        ]) ?>
    </div>
    <div class="col-md-2">
        <?= $form->field($model, 'amount')->input('number', [
            'step' => '0.01',
            'min' => 0,
            'id' => 'bond-amount',
        ]) ?>
    </div>

    <div class="col-12">
        <div class="alert alert-secondary mb-0" id="bond-policy-box">
            <i class="bi bi-shield-check me-1"></i>
            <span id="bond-policy-text">กรอกวงเงินที่ใช้เป็นฐาน แล้วระบบจะบอกเกณฑ์ที่ใช้กับวงเงินนั้น</span>
            <div class="mt-2 d-none" id="bond-policy-action">
                <button type="button" class="btn btn-sm btn-primary" id="bond-policy-fill">
                    <i class="bi bi-calculator me-1"></i>เติมอัตราและวงเงินตามเกณฑ์
                </button>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <?= $form->field($model, 'place_date')->input('date') ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'expiry_date')->input('date')
            ->hint('ระบบเตือนล่วงหน้าจากวันนี้') ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'status')->dropDownList(Bond::statusList(), [
            'id' => 'bond-status',
        ]) ?>
    </div>
    <div class="col-md-3" id="bond-exempt-wrap">
        <?= $form->field($model, 'exempt_reason')->textInput([
            'maxlength' => true,
            'placeholder' => 'เช่น วงเงินไม่เกินเกณฑ์และตรวจรับก่อนจ่ายเงิน',
        ]) ?>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'note')->textarea(['rows' => 2]) ?>
    </div>
</div>

<div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-3">
    <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึก', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$policyUrl = Url::to(['policy']);
$sourceContract = Json::encode(Bond::SOURCE_CONTRACT);
$statusExempt = Json::encode(Bond::STATUS_EXEMPT);

$this->registerJs(<<<JS
(function () {
    var \$type = $('#bond-source-type'),
        \$sourceWrap = $('#bond-source-wrap'),
        \$sourceId = $('#bond-source-id'),
        \$base = $('#bond-base-amount'),
        \$rate = $('#bond-rate'),
        \$amount = $('#bond-amount'),
        \$status = $('#bond-status'),
        \$exempt = $('#bond-exempt-wrap'),
        \$box = $('#bond-policy-box'),
        \$text = $('#bond-policy-text'),
        \$action = $('#bond-policy-action'),
        current = null;

    function toggleSource() {
        var isContract = \$type.val() === $sourceContract;
        \$sourceWrap.toggleClass('d-none', !isContract);
        if (!isContract) { \$sourceId.val(''); }
    }

    function toggleExempt() {
        \$exempt.toggleClass('d-none', \$status.val() !== $statusExempt);
    }

    function render(data) {
        current = data.policy;
        \$box.removeClass('alert-secondary alert-warning alert-info alert-danger');
        \$box.addClass(!data.policy.configured ? 'alert-danger'
            : (data.policy.required ? 'alert-warning' : 'alert-info'));

        var html = '<i class="bi bi-shield-check me-1"></i>' + data.policy.reason;
        if (data.policy.range) {
            html += ' <span class="text-muted">(ช่วง ' + data.policy.range + ')</span>';
        }
        if (data.policy.required) {
            html += '<div class="fw-semibold mt-1">ต้องวาง ' + data.policy.rate + '% = '
                + Number(data.policy.amount).toLocaleString(undefined, {minimumFractionDigits: 2}) + ' บาท</div>';
        }
        if (data.policy.law) {
            html += '<div class="small text-muted mt-1">อ้างอิง: ' + data.policy.law + '</div>';
        }
        \$text.html(html);
        \$action.toggleClass('d-none', !data.policy.required);
    }

    function refresh(fillFromContract) {
        var params = {amount: \$base.val()};
        if (\$type.val() === $sourceContract && \$sourceId.val()) {
            params.contract_id = \$sourceId.val();
        }
        $.getJSON('$policyUrl', params, function (data) {
            if (fillFromContract && data.contract) {
                if (!\$base.val() || Number(\$base.val()) === 0) { \$base.val(data.contract.budget); }
                if (!$('#bond-title').val()) { $('#bond-title').val(data.contract.title); }
                refresh(false);
                return;
            }
            render(data);
        });
    }

    \$type.on('change', function () { toggleSource(); refresh(false); });
    \$sourceId.on('change', function () { refresh(true); });
    \$status.on('change', toggleExempt);
    \$base.on('change', function () { refresh(false); });

    $('#bond-policy-fill').on('click', function () {
        if (!current || !current.required) { return; }
        \$rate.val(current.rate);
        \$amount.val(Number(current.amount).toFixed(2));
    });

    toggleSource();
    toggleExempt();
    refresh(false);
})();
JS);
?>

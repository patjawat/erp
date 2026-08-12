<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;
use kartik\editors\Summernote;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\WhtRate;
use app\modules\purchase\models\ContractMilestone;
use app\modules\purchase\components\ContractCalculator;

/** @var yii\web\View $this */
/** @var app\modules\purchase\models\Contract $model */
/** @var app\modules\purchase\models\ContractMilestone[] $milestones */

/**
 * Toolbar จำกัดเท่าที่ PhpWord แปลงลงไฟล์ Word ได้จริง ให้ตรงกับ Contract::ALLOWED_HTML
 * เหตุผลเดียวกับฟอร์ม TOR — กันหน้าจอไม่ตรงกับที่พิมพ์ออกมา
 */
$editorOptions = [
    'useKrajeePresets' => false,
    'pluginOptions' => [
        'height' => 200,
        'minHeight' => 200,
        'maxHeight' => 600,
        'toolbar' => [
            ['style', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['insert', ['table']],
            ['misc', ['undo', 'redo', 'codeview']],
        ],
        'disableDragAndDrop' => true,
    ],
];

// อัตราภาษีส่งไปให้ JS คำนวณตัวอย่างบนหน้าจอ — ค่าที่บันทึกจริงคำนวณซ้ำฝั่งเซิร์ฟเวอร์
// ใน Contract::applyWht() เสมอ หน้าจอเป็นเพียงตัวอย่างให้ผู้ใช้เห็นก่อนบันทึก
$whtRates = [];
foreach (WhtRate::find()->where(['active' => 1])->all() as $rate) {
    $whtRates[$rate->code . '|' . $rate->party_type] = [
        'rate' => (float) $rate->rate,
        'threshold' => (float) $rate->threshold,
        'law' => $rate->law_ref,
    ];
}

$rows = $milestones ?: [];
$form = ActiveForm::begin([
    'id' => 'contract-form',
    'options' => ['autocomplete' => 'off'],
]);
?>

<ul class="nav nav-tabs mb-3" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-main" type="button">ข้อมูลสัญญา</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-time" type="button">กำหนดเวลาและค่าปรับ</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-milestone" type="button">งวดงาน</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-term" type="button">เงื่อนไขเพิ่มเติม</button></li>
</ul>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger']) ?>

<div class="tab-content">

    <!-- ─── ข้อมูลสัญญา ──────────────────────────────────────────────── -->
    <div class="tab-pane fade show active" id="tab-main">

        <div class="alert alert-light border d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="small">
                <?php if ($model->order_id): ?>
                    <i class="bi bi-link-45deg text-primary me-1"></i>
                    ผูกกับใบสั่งซื้อในระบบแล้ว — ข้อมูลด้านล่างเป็นสำเนาของสัญญา แก้ไขได้โดยไม่กระทบใบสั่งซื้อ
                <?php else: ?>
                    <i class="bi bi-info-circle me-1"></i>
                    เลือกใบสั่งซื้อที่ออกไว้แล้ว ระบบจะเติมคู่สัญญา วงเงิน และวันที่ให้อัตโนมัติ
                    หรือกรอกเองทั้งหมดก็ได้ถ้าสัญญานี้ไม่มีใบสั่งซื้อในระบบ
                <?php endif; ?>
            </div>
            <?php if ($model->isNewRecord): ?>
                <?= Html::a('<i class="bi bi-cart-check me-1"></i>เลือกจากใบสั่งซื้อ', ['order-picker', 'title' => 'เลือกใบสั่งซื้อ'], [
                    'class' => 'btn btn-sm btn-primary rounded-pill px-3 open-modal',
                    'data' => ['size' => 'modal-xl'],
                ]) ?>
            <?php endif; ?>
        </div>

        <?= $form->field($model, 'order_id')->hiddenInput()->label(false) ?>

        <div class="row g-3">
            <div class="col-12">
                <?= $form->field($model, 'title')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'เช่น ซื้อครุภัณฑ์คอมพิวเตอร์ จำนวน 10 เครื่อง',
                ]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'contract_type')->dropDownList(Contract::typeList()) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'contract_no')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'เลขที่ตามที่ออกจริง',
                ])->hint('เว้นว่างได้ ระบบออกเลขให้เอง') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'thai_year')->input('number', ['min' => 2500, 'max' => 2700]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'status')->dropDownList(Contract::statusList()) ?>
            </div>

            <div class="col-md-5">
                <?= $form->field($model, 'vendor_id')->dropDownList(Contract::listVendor(), [
                    'prompt' => '— เลือกจากทะเบียนผู้แทนจำหน่าย —',
                    'class' => 'form-select',
                ]) ?>
            </div>
            <div class="col-md-4">
                <?= $form->field($model, 'vendor_name')->textInput([
                    'maxlength' => true,
                    'placeholder' => 'กรอกเองเมื่อยังไม่มีในทะเบียน',
                ])->hint('ชื่อที่พิมพ์ลงเอกสาร') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'party_type')->dropDownList(WhtRate::partyTypeList()) ?>
            </div>

            <div class="col-md-3">
                <?= $form->field($model, 'budget')->input('number', ['step' => '0.01', 'min' => 0]) ?>
            </div>
            <div class="col-md-3 d-flex align-items-center">
                <div class="mb-3 w-100">
                    <?= $form->field($model, 'vat_included')->checkbox() ?>
                </div>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'egp_no')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'tor_id')->input('number', ['min' => 1])
                    ->hint('เลข id ของ TOR ที่ใช้ (ถ้ามี)') ?>
            </div>

            <div class="col-12">
                <div class="alert alert-secondary mb-0" id="contract-wht-box">
                    <i class="bi bi-calculator me-1"></i>
                    <span id="contract-wht-text">—</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── กำหนดเวลาและค่าปรับ ─────────────────────────────────────── -->
    <div class="tab-pane fade" id="tab-time">
        <div class="row g-3">
            <div class="col-md-3"><?= $form->field($model, 'sign_date')->input('date') ?></div>
            <div class="col-md-3"><?= $form->field($model, 'start_date')->input('date') ?></div>
            <div class="col-md-3"><?= $form->field($model, 'end_date')->input('date') ?></div>
            <div class="col-md-3"><?= $form->field($model, 'warranty_end')->input('date') ?></div>

            <div class="col-md-3">
                <?= $form->field($model, 'delivery_date')->input('date')
                    ->hint('วันที่ผู้ขายส่งมอบครบถ้วน — ค่าปรับหยุดนับที่วันนี้') ?>
            </div>
            <div class="col-md-3"><?= $form->field($model, 'receive_date')->input('date') ?></div>

            <div class="col-md-3">
                <?= $form->field($model, 'fine_rate')->input('number', ['step' => '0.0001', 'min' => 0])
                    ->hint('เช่น 0.01 = ร้อยละ 0.01 ต่อวัน') ?>
            </div>
            <div class="col-md-3">
                <?= $form->field($model, 'fine_base')->dropDownList(Contract::fineBaseList()) ?>
            </div>

            <div class="col-12">
                <div class="alert alert-secondary mb-0" id="contract-fine-box">
                    <i class="bi bi-cash-stack me-1"></i>
                    <span id="contract-fine-text">—</span>
                </div>
            </div>

            <div class="col-12">
                <?= $form->field($model, 'note')->textarea(['rows' => 2]) ?>
            </div>
        </div>
    </div>

    <!-- ─── งวดงาน ──────────────────────────────────────────────────── -->
    <div class="tab-pane fade" id="tab-milestone">
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-1"></i>
            บันทึกงวดงานเมื่อสัญญาแบ่งส่งมอบเป็นงวด — เลือกฐานคิดค่าปรับเป็น
            <span class="fw-medium">“คิดจากวงเงินของงวดที่ล่าช้า”</span>
            ในแท็บกำหนดเวลา แล้วระบบจะคิดค่าปรับแยกรายงวดให้
        </div>

        <div class="table-responsive">
            <table class="table table-bordered align-middle" id="milestone-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:56px" class="text-center">งวด</th>
                        <th style="min-width:200px">รายละเอียดงาน</th>
                        <th style="width:90px" class="text-end">%</th>
                        <th style="width:140px" class="text-end">วงเงิน</th>
                        <th style="width:150px">กำหนดส่งมอบ</th>
                        <th style="width:150px">ส่งมอบจริง</th>
                        <th style="width:140px">สถานะ</th>
                        <th style="width:56px"></th>
                    </tr>
                </thead>
                <tbody id="milestone-body">
                    <?php foreach ($rows as $i => $row): ?>
                        <tr>
                            <td class="text-center ms-row-no"><?= $i + 1 ?></td>
                            <td><?= Html::textInput("milestones[$i][detail]", $row->detail, ['class' => 'form-control']) ?></td>
                            <td><?= Html::input('number', "milestones[$i][percent]", $row->percent, [
                                    'class' => 'form-control text-end ms-percent',
                                    'step' => '0.01',
                                    'min' => 0,
                                ]) ?></td>
                            <td><?= Html::input('number', "milestones[$i][amount]", $row->amount, [
                                    'class' => 'form-control text-end ms-amount',
                                    'step' => '0.01',
                                    'min' => 0,
                                ]) ?></td>
                            <td><?= Html::input('date', "milestones[$i][due_date]", $row->due_date, ['class' => 'form-control ms-due']) ?></td>
                            <td><?= Html::input('date', "milestones[$i][delivered_date]", $row->delivered_date, ['class' => 'form-control ms-delivered']) ?></td>
                            <td><?= Html::dropDownList("milestones[$i][status]", $row->status, ContractMilestone::statusList(), ['class' => 'form-select']) ?></td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger ms-row-remove" title="ลบงวด">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" id="milestone-add">
            <i class="bi bi-plus-lg me-1"></i>เพิ่มงวดงาน
        </button>
        <div class="small text-muted mt-2" id="milestone-sum"></div>
    </div>

    <!-- ─── เงื่อนไขเพิ่มเติม ────────────────────────────────────────── -->
    <div class="tab-pane fade" id="tab-term">
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            ร่างสัญญาที่ระบบพิมพ์ออกมาเป็นแบบสรุปสาระสำคัญ
            <span class="fw-medium">ไม่ใช่แบบสัญญามาตรฐานตามที่คณะกรรมการว่าด้วยการพัสดุกำหนด</span>
            ใช้เป็นร่างตั้งต้นแล้วนำไปปรับต่อก่อนลงนาม ข้อความที่พิมพ์ในช่องนี้จะไหลลงร่างสัญญา
        </div>
        <?= $form->field($model, 'extra_term')->widget(Summernote::class, $editorOptions) ?>
    </div>
</div>

<!-- แม่แบบแถวงวดงานสำหรับปุ่มเพิ่ม -->
<template id="milestone-template">
    <tr>
        <td class="text-center ms-row-no"></td>
        <td><input type="text" name="milestones[__I__][detail]" class="form-control"></td>
        <td><input type="number" name="milestones[__I__][percent]" class="form-control text-end ms-percent" step="0.01" min="0"></td>
        <td><input type="number" name="milestones[__I__][amount]" class="form-control text-end ms-amount" step="0.01" min="0"></td>
        <td><input type="date" name="milestones[__I__][due_date]" class="form-control ms-due"></td>
        <td><input type="date" name="milestones[__I__][delivered_date]" class="form-control ms-delivered"></td>
        <td>
            <select name="milestones[__I__][status]" class="form-select">
                <?php foreach (ContractMilestone::statusList() as $value => $label): ?>
                    <option value="<?= Html::encode($value) ?>"><?= Html::encode($label) ?></option>
                <?php endforeach; ?>
            </select>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger ms-row-remove" title="ลบงวด">
                <i class="bi bi-x-lg"></i>
            </button>
        </td>
    </tr>
</template>

<div class="d-grid d-sm-flex justify-content-sm-end gap-2 mt-4">
    <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกสัญญา', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('ยกเลิก', $model->isNewRecord ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$ids = [
    'budget' => Html::getInputId($model, 'budget'),
    'vat' => Html::getInputId($model, 'vat_included'),
    'type' => Html::getInputId($model, 'contract_type'),
    'party' => Html::getInputId($model, 'party_type'),
    'fineRate' => Html::getInputId($model, 'fine_rate'),
    'fineBase' => Html::getInputId($model, 'fine_base'),
    'endDate' => Html::getInputId($model, 'end_date'),
    'delivery' => Html::getInputId($model, 'delivery_date'),
    'receive' => Html::getInputId($model, 'receive_date'),
    'orderId' => Html::getInputId($model, 'order_id'),
    'vendorId' => Html::getInputId($model, 'vendor_id'),
    'vendorName' => Html::getInputId($model, 'vendor_name'),
    'title' => Html::getInputId($model, 'title'),
    'egp' => Html::getInputId($model, 'egp_no'),
    'sign' => Html::getInputId($model, 'sign_date'),
    'warranty' => Html::getInputId($model, 'warranty_end'),
    'year' => Html::getInputId($model, 'thai_year'),
];
$idsJson = Json::encode($ids);
$ratesJson = Json::encode($whtRates);
$vatRate = ContractCalculator::VAT_RATE;
$warnRatio = ContractCalculator::WARN_RATIO;
$fineBaseMilestone = Contract::FINE_BASE_MILESTONE;
$whtSettingUrl = Url::to(['/purchase/wht-rate']);

$js = <<<JS
(function () {
    var ID = {$idsJson};
    var RATES = {$ratesJson};
    var VAT = {$vatRate};
    var WARN = {$warnRatio};
    var el = function (key) { return document.getElementById(ID[key]); };
    var num = function (key) { return parseFloat((el(key) || {}).value) || 0; };
    var money = function (v) {
        return v.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };

    // ── ตัวอย่างภาษีหัก ณ ที่จ่าย ────────────────────────────────────────
    // เป็นเพียงตัวอย่างบนหน้าจอ ค่าที่บันทึกจริงคำนวณซ้ำฝั่งเซิร์ฟเวอร์เสมอ
    function renderWht() {
        var box = document.getElementById('contract-wht-text');
        if (!box) return;
        var budget = num('budget');
        var key = (el('type') || {}).value + '|' + (el('party') || {}).value;
        var setting = RATES[key];

        if (!setting) {
            box.innerHTML = 'ยังไม่ได้ตั้งอัตราภาษีหัก ณ ที่จ่ายของสัญญาประเภทนี้ ' +
                '<a href="{$whtSettingUrl}">ไปที่หน้าตั้งค่า</a>';
            return;
        }
        if (budget <= 0) { box.textContent = 'ภาษีหัก ณ ที่จ่าย: ระบุวงเงินก่อน'; return; }
        if (budget < setting.threshold) {
            box.textContent = 'ภาษีหัก ณ ที่จ่าย: ไม่ต้องหัก (ยอดต่ำกว่าเกณฑ์ ' +
                money(setting.threshold) + ' บาท)';
            return;
        }

        var vatIncluded = el('vat') ? el('vat').checked : true;
        var base = vatIncluded ? budget * 100 / (100 + VAT) : budget;
        var amount = base * setting.rate / 100;
        box.textContent = 'ภาษีหัก ณ ที่จ่าย ' + setting.rate + '% ของฐาน ' + money(base) +
            ' บาท = ' + money(amount) + ' บาท' +
            (vatIncluded ? ' (ถอด VAT ออกจากวงเงินแล้ว)' : ' (วงเงินยังไม่รวม VAT)') +
            (setting.law ? ' · ' + setting.law : '');
    }

    // ── ตัวอย่างค่าปรับ ─────────────────────────────────────────────────
    function daysBetween(fromStr, toStr) {
        if (!fromStr || !toStr) return 0;
        var from = new Date(fromStr), to = new Date(toStr);
        if (isNaN(from) || isNaN(to) || to <= from) return 0;
        return Math.floor((to - from) / 86400000);
    }

    function renderFine() {
        var box = document.getElementById('contract-fine-text');
        if (!box) return;
        var budget = num('budget');
        var rate = num('fineRate');
        var byMilestone = (el('fineBase') || {}).value === '{$fineBaseMilestone}';
        var days = 0, raw = 0;

        if (byMilestone) {
            \$('#milestone-body tr').each(function () {
                var due = \$(this).find('.ms-due').val();
                var delivered = \$(this).find('.ms-delivered').val();
                var amount = parseFloat(\$(this).find('.ms-amount').val()) || 0;
                var d = daysBetween(due, delivered);
                raw += amount * rate / 100 * d;
                days = Math.max(days, d);
            });
        } else {
            var closing = (el('delivery') || {}).value || (el('receive') || {}).value;
            days = daysBetween((el('endDate') || {}).value, closing);
            raw = budget * rate / 100 * days;
        }

        raw = Math.round(raw * 100) / 100;
        if (days <= 0 || raw <= 0) {
            box.textContent = 'ค่าปรับ: ไม่มี (ส่งมอบภายในกำหนด หรือยังไม่ได้ระบุวันส่งมอบ)';
            box.parentNode.className = 'alert alert-secondary mb-0';
            return;
        }

        var capped = budget > 0 && raw > budget;
        var amount = capped ? budget : raw;
        var ratio = budget > 0 ? amount * 100 / budget : 0;

        var text = 'ล่าช้า ' + days + ' วัน — ค่าปรับ ' + money(amount) + ' บาท';
        if (capped) text += ' (คำนวณได้ ' + money(raw) + ' บาท แต่ถูกจำกัดไว้เท่าวงเงินตามสัญญา)';
        if (ratio >= WARN) text += ' · คิดเป็น ' + ratio.toFixed(2) + '% ของวงเงิน ถึงเกณฑ์ที่ต้องพิจารณาบอกเลิกสัญญา';
        box.textContent = text;
        box.parentNode.className = ratio >= WARN ? 'alert alert-danger mb-0' : 'alert alert-warning mb-0';
    }

    // ── งวดงาน ─────────────────────────────────────────────────────────
    function renumber() {
        \$('#milestone-body tr').each(function (i) {
            \$(this).find('.ms-row-no').text(i + 1);
            \$(this).find('input, select').each(function () {
                var name = \$(this).attr('name');
                if (name) \$(this).attr('name', name.replace(/milestones\\[[^\\]]*\\]/, 'milestones[' + i + ']'));
            });
        });
    }

    function renderSum() {
        var total = 0, pct = 0;
        \$('#milestone-body tr').each(function () {
            total += parseFloat(\$(this).find('.ms-amount').val()) || 0;
            pct += parseFloat(\$(this).find('.ms-percent').val()) || 0;
        });
        var box = document.getElementById('milestone-sum');
        if (!box) return;
        if (!\$('#milestone-body tr').length) { box.textContent = ''; return; }

        var budget = num('budget');
        var text = 'รวมงวดงาน ' + money(total) + ' บาท (' + pct.toFixed(2) + '%)';
        if (budget > 0 && Math.abs(total - budget) >= 0.01) {
            text += ' — ไม่เท่ากับวงเงินตามสัญญา ' + money(budget) + ' บาท';
            box.className = 'small text-danger mt-2';
        } else {
            box.className = 'small text-muted mt-2';
        }
        box.textContent = text;
    }

    \$('#milestone-add').on('click', function () {
        var index = \$('#milestone-body tr').length;
        var html = document.getElementById('milestone-template').innerHTML.replace(/__I__/g, index);
        \$('#milestone-body').append(html);
        renumber();
        renderSum();
    });

    \$('#milestone-body').on('click', '.ms-row-remove', function () {
        \$(this).closest('tr').remove();
        renumber();
        renderSum();
        renderFine();
    });

    // กรอก % แล้วเติมวงเงินงวดให้จากวงเงินสัญญา (ยังแก้ทับได้เอง)
    \$('#milestone-body').on('input', '.ms-percent', function () {
        var pct = parseFloat(\$(this).val());
        var budget = num('budget');
        if (!isNaN(pct) && budget > 0) {
            \$(this).closest('tr').find('.ms-amount').val((budget * pct / 100).toFixed(2));
        }
        renderSum();
        renderFine();
    });

    \$('#milestone-body').on('input change', '.ms-amount, .ms-due, .ms-delivered', function () {
        renderSum();
        renderFine();
    });

    // ── ผูกเหตุการณ์ ───────────────────────────────────────────────────
    ['budget', 'vat', 'type', 'party'].forEach(function (key) {
        var node = el(key);
        if (node) node.addEventListener('change', function () { renderWht(); renderSum(); renderFine(); });
        if (node) node.addEventListener('input', function () { renderWht(); renderSum(); renderFine(); });
    });
    ['fineRate', 'fineBase', 'endDate', 'delivery', 'receive'].forEach(function (key) {
        var node = el(key);
        if (node) node.addEventListener('change', renderFine);
        if (node) node.addEventListener('input', renderFine);
    });

    // เลือกผู้ขายจากทะเบียน = เติมชื่อที่จะพิมพ์ลงเอกสารให้ แต่ยังพิมพ์ทับได้
    var vendorSelect = el('vendorId');
    if (vendorSelect) {
        vendorSelect.addEventListener('change', function () {
            var name = this.options[this.selectedIndex] ? this.options[this.selectedIndex].text : '';
            var target = el('vendorName');
            if (target && this.value) target.value = name;
        });
    }

    // ── รับค่าจากหน้าต่างเลือกใบสั่งซื้อ ─────────────────────────────────
    window.contractApplyOrder = function (data) {
        var map = {
            orderId: 'order_id', title: 'title', vendorId: 'vendor_id', vendorName: 'vendor_name',
            egp: 'egp_no', budget: 'budget', sign: 'sign_date', endDate: 'end_date',
            delivery: 'delivery_date', receive: 'receive_date', warranty: 'warranty_end', year: 'thai_year'
        };
        Object.keys(map).forEach(function (key) {
            var node = el(key);
            var value = data[map[key]];
            if (!node || value === null || value === undefined || value === '') return;
            node.value = value;
            \$(node).trigger('change');
        });

        renderWht();
        renderFine();

        if (typeof erpHideModal === 'function') erpHideModal('#main-modal');
        else \$('#main-modal').modal('hide');

        if (typeof toastr !== 'undefined') {
            toastr.success('เติมข้อมูลจากใบสั่งซื้อ ' + (data.po_number || '') +
                ' แล้ว — ตรวจสอบและแก้ไขให้ตรงกับสัญญาจริงก่อนบันทึก');
        }
    };

    renderWht();
    renderFine();
    renderSum();
})();
JS;

$this->registerJs($js, View::POS_READY);
?>

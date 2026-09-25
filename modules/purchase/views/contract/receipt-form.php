<?php

use yii\web\View;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\ActiveForm;
use app\components\AppHelper;
use app\widgets\datepicker\DatepickerThai;
use app\modules\purchase\models\Contract;
use app\modules\purchase\models\ContractReceipt;
use app\modules\purchase\components\ContractCalculator;

/**
 * บันทึกตรวจรับรายงวด
 *
 * @var yii\web\View $this
 * @var Contract $contract
 * @var ContractReceipt $model
 * @var app\modules\purchase\models\ContractReceiptItem[] $lines
 * @var float $used ยอดของงวดอื่นที่ไม่ถูกยกเลิก
 */

$this->title = ($model->isNewRecord ? 'บันทึกตรวจรับ' : 'แก้ไขตรวจรับ') . ' งวดที่ ' . $model->seq;
$this->params['breadcrumbs'][] = ['label' => 'บริหารสัญญา', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $contract->title, 'url' => ['view', 'id' => $contract->id]];
$this->params['breadcrumbs'][] = $this->title;

$thai = fn($value) => $value ? AppHelper::convertToThai($value) : '';
$dateField = function ($form, $attr) use ($model, $thai) {
    return $form->field($model, $attr, ['enableClientValidation' => false])->widget(DatepickerThai::class, [
        'options' => ['value' => $thai($model->$attr), 'placeholder' => 'วว/ดด/พ.ศ.', 'autocomplete' => 'off'],
    ]);
};

$isUnitPrice = $contract->billing_mode === Contract::BILLING_UNIT_PRICE;
$milestones = $contract->milestones;
$milestoneOptions = [];
$milestoneAmounts = [];
foreach ($milestones as $ms) {
    $milestoneOptions[$ms->id] = 'งวดที่ ' . $ms->seq . ($ms->detail ? ' — ' . $ms->detail : '') . ' (' . number_format((float) $ms->amount, 2) . ' บาท)';
    $milestoneAmounts[$ms->id] = (float) $ms->amount;
}
$budget = (float) $contract->budget;
?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <i class="bi bi-clipboard-check"></i> <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?= Html::encode($contract->title) ?> · <?= Html::encode($contract->vendor_name ?: '—') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับหน้าสัญญา', ['view', 'id' => $contract->id, '#' => 'receipts'], [
    'class' => 'btn btn-sm btn-outline-secondary',
]) ?>
<?php $this->endBlock(); ?>

<?php $form = ActiveForm::begin(['id' => 'receipt-form', 'options' => ['autocomplete' => 'off']]); ?>

<?= $form->errorSummary($model, ['class' => 'alert alert-danger', 'header' => '<div class="fw-medium mb-1"><i class="bi bi-exclamation-octagon me-1"></i>บันทึกไม่ได้</div>']) ?>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">ข้อมูลงวด</h6></div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3"><?= $dateField($form, 'period_start') ?></div>
                    <div class="col-md-3"><?= $dateField($form, 'period_end') ?></div>
                    <div class="col-md-3"><?= $form->field($model, 'invoice_no')->textInput(['maxlength' => true, 'placeholder' => 'ใบแจ้งหนี้ของผู้รับจ้าง']) ?></div>
                    <div class="col-md-3"><?= $dateField($form, 'invoice_date') ?></div>
                    <div class="col-md-3"><?= $dateField($form, 'delivered_date') ?></div>
                    <div class="col-md-3"><?= $dateField($form, 'receive_date') ?></div>
                    <div class="col-md-6"><?= $form->field($model, 'vat_type')->dropDownList(ContractReceipt::vatTypeList(), ['id' => 'rc-vat']) ?></div>
                    <?php if (!$isUnitPrice && $milestoneOptions): ?>
                        <div class="col-md-6">
                            <?= $form->field($model, 'milestone_id')->dropDownList($milestoneOptions, [
                                'prompt' => '— ไม่อ้างงวดตามสัญญา —',
                                'id' => 'rc-milestone',
                            ])->hint('เลือกแล้วระบบเติมยอดงวดให้ในรายการแรก') ?>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-3"><?= $form->field($model, 'fine_amount')->input('number', ['step' => '0.01', 'min' => 0, 'id' => 'rc-fine']) ?></div>
                    <div class="col-md-9"><?= $form->field($model, 'note')->textInput(['maxlength' => true]) ?></div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">รายการที่ตรวจรับงวดนี้</h6>
                <span class="small text-muted">
                    <?= $isUnitPrice ? 'กรอกปริมาณจริงของงวดนี้ × ราคาต่อหน่วยตามสัญญา' : 'กรอกยอดงวด (ปริมาณ 1 × ยอดเงิน) หรือตามปริมาณจริง' ?>
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="rc-lines">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th>รายการ</th>
                                <th style="width:90px">หน่วย</th>
                                <th style="width:150px" class="text-end">ราคาต่อหน่วย</th>
                                <th style="width:130px" class="text-end">ปริมาณ</th>
                                <th style="width:150px" class="text-end">จำนวนเงิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!$lines): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">สัญญานี้ไม่ได้ผูกใบสั่งซื้อ — ไม่มีรายการตั้งต้น</td></tr>
                            <?php endif; ?>
                            <?php foreach ($lines as $i => $line): ?>
                                <tr>
                                    <td>
                                        <?= Html::encode($line->item_name) ?>
                                        <?= Html::hiddenInput("lines[$i][order_item_id]", $line->order_item_id) ?>
                                        <?= Html::hiddenInput("lines[$i][asset_item]", $line->asset_item) ?>
                                        <?= Html::hiddenInput("lines[$i][item_name]", $line->item_name) ?>
                                    </td>
                                    <td><?= Html::textInput("lines[$i][unit_name]", $line->unit_name, ['class' => 'form-control', 'maxlength' => 50, 'placeholder' => 'ครั้ง']) ?></td>
                                    <td><?= Html::input('number', "lines[$i][unit_price]", (float) $line->unit_price ?: '', ['class' => 'form-control text-end rc-price', 'step' => '0.0001', 'min' => 0, 'placeholder' => 'ราคาต่อหน่วย']) ?></td>
                                    <td><?= Html::input('number', "lines[$i][qty]", (float) $line->qty ?: '', ['class' => 'form-control text-end rc-qty', 'step' => '0.01', 'min' => 0, 'placeholder' => '0']) ?></td>
                                    <td class="text-end fw-medium rc-line-amount">0.00</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">ยอดงวดนี้</h6></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-7 fw-normal">รวมรายการ</dt><dd class="col-5 text-end" id="rc-sum">0.00</dd>
                    <dt class="col-7 fw-normal">ยอดก่อน VAT</dt><dd class="col-5 text-end" id="rc-before">0.00</dd>
                    <dt class="col-7 fw-normal">VAT</dt><dd class="col-5 text-end" id="rc-vatamt">0.00</dd>
                    <dt class="col-7">ยอดเรียกเก็บ (ตั้งหนี้)</dt><dd class="col-5 text-end fw-semibold" id="rc-total">0.00</dd>
                    <dt class="col-7 fw-normal text-danger">หักค่าปรับ</dt><dd class="col-5 text-end text-danger" id="rc-fine-view">0.00</dd>
                </dl>
                <div class="small text-muted mt-2">ภาษีหัก ณ ที่จ่ายระบบคำนวณตอนบันทึก ตามอัตราในหน้าตั้งค่าและเกณฑ์ต่อการจ่ายแต่ละครั้ง</div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0">วงเงินสัญญา</h6></div>
            <div class="card-body">
                <dl class="row mb-2 small">
                    <dt class="col-7 fw-normal">วงเงินตามสัญญา</dt><dd class="col-5 text-end"><?= number_format($budget, 2) ?></dd>
                    <dt class="col-7 fw-normal">ใช้แล้ว (งวดอื่น)</dt><dd class="col-5 text-end"><?= number_format($used, 2) ?></dd>
                    <dt class="col-7 fw-normal">งวดนี้</dt><dd class="col-5 text-end" id="rc-this">0.00</dd>
                    <dt class="col-7">คงเหลือหลังงวดนี้</dt><dd class="col-5 text-end fw-semibold" id="rc-remain"><?= number_format($budget - $used, 2) ?></dd>
                </dl>
                <div class="progress" style="height:8px" role="progressbar" aria-label="ใช้วงเงิน">
                    <div class="progress-bar" id="rc-bar" style="width:0%"></div>
                </div>
                <div class="small mt-1" id="rc-bar-text"></div>
            </div>
        </div>

        <div class="d-grid gap-2">
            <?= Html::submitButton('<i class="bi bi-check2-circle me-1"></i>บันทึกและยืนยันตรวจรับ', [
                'class' => 'btn btn-success',
                'name' => 'confirm',
                'value' => '1',
            ]) ?>
            <?php if ($model->status === ContractReceipt::STATUS_DRAFT): ?>
                <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกร่าง', ['class' => 'btn btn-outline-primary']) ?>
            <?php else: ?>
                <?= Html::submitButton('<i class="bi bi-save me-1"></i>บันทึกการแก้ไข', ['class' => 'btn btn-outline-primary']) ?>
            <?php endif; ?>
            <?= Html::a('ยกเลิก', ['view', 'id' => $contract->id, '#' => 'receipts'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$cfg = Json::encode([
    'vatRate' => ContractCalculator::VAT_RATE,
    'budget' => $budget,
    'used' => (float) $used,
    'milestones' => $milestoneAmounts,
]);
$js = <<<JS
(function () {
    var C = {$cfg};
    var money = function (v) { return v.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };
    var num = function (v) { return parseFloat(v) || 0; };

    function recalc() {
        var sum = 0;
        \$('#rc-lines tbody tr').each(function () {
            var price = num(\$(this).find('.rc-price').val());
            var qty = num(\$(this).find('.rc-qty').val());
            var amt = Math.round(price * qty * 100) / 100;
            sum += amt;
            \$(this).find('.rc-line-amount').text(money(amt));
        });
        var vat = \$('#rc-vat').val(), before, vatAmt, total;
        if (vat === 'EX') { before = sum; vatAmt = Math.round(sum * C.vatRate) / 100; total = before + vatAmt; }
        else if (vat === 'IN') { total = sum; before = Math.round(sum * 10000 / (100 + C.vatRate)) / 100; vatAmt = total - before; }
        else { before = total = sum; vatAmt = 0; }

        \$('#rc-sum').text(money(sum));
        \$('#rc-before').text(money(before));
        \$('#rc-vatamt').text(money(vatAmt));
        \$('#rc-total').text(money(total));
        \$('#rc-fine-view').text(money(num(\$('#rc-fine').val())));
        \$('#rc-this').text(money(total));

        var remain = C.budget - C.used - total;
        var pct = C.budget > 0 ? (C.used + total) / C.budget * 100 : 0;
        var over = remain < -0.005;
        \$('#rc-remain').text(money(remain)).toggleClass('text-danger', over);
        \$('#rc-bar').css('width', Math.min(pct, 100) + '%').toggleClass('bg-danger', over);
        \$('#rc-bar-text').text('ใช้วงเงินรวม ' + pct.toFixed(1) + '%' + (over ? ' — เกินวงเงินสัญญา บันทึกไม่ได้' : ''))
            .toggleClass('text-danger', over).toggleClass('text-muted', !over);
    }

    // เลือกงวดตามสัญญา → เติมยอดงวดในรายการแรก (ปริมาณ 1)
    \$('#rc-milestone').on('change', function () {
        var amt = C.milestones[this.value];
        if (amt === undefined) return;
        var row = \$('#rc-lines tbody tr').first();
        row.find('.rc-price').val(amt.toFixed(2));
        row.find('.rc-qty').val(1);
        recalc();
    });

    \$('#rc-lines').on('input change', '.rc-price, .rc-qty', recalc);
    \$('#rc-vat, #rc-fine').on('input change', recalc);
    recalc();
})();
JS;
$this->registerJs($js, View::POS_READY);

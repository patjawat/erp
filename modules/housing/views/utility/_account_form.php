<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin(['id' => 'monthly-account-form']);
$location = implode(' / ', array_filter([$account->building_name, $account->unit_name, $account->room_name]));
?>
<style>
.monthly-entry{--entry-bg:#f6f9fc;--entry-border:#dce6f0;--entry-ink:#26384a;--entry-muted:#60758a;color:var(--entry-ink)}
.monthly-entry .entry-context{background:var(--entry-bg);border:1px solid var(--entry-border);border-radius:.75rem;padding:1rem}
.monthly-entry .entry-location{color:var(--entry-muted);font-size:.875rem;margin-top:.2rem}
.monthly-entry .entry-meta{display:flex;flex-wrap:wrap;gap:.45rem;margin-top:.75rem}
.monthly-entry .entry-chip{background:#fff;border:1px solid var(--entry-border);border-radius:999px;color:#425b72;font-size:.8rem;padding:.3rem .65rem}
.monthly-entry .expense-list{border:1px solid var(--entry-border);border-radius:.75rem;overflow:hidden}
.monthly-entry .expense-row{display:grid;grid-template-columns:minmax(150px,1fr) minmax(140px,180px) minmax(170px,1.2fr);gap:.75rem;align-items:center;padding:.75rem 1rem;border-bottom:1px solid var(--entry-border)}
.monthly-entry .expense-row:last-child{border-bottom:0}
.monthly-entry .expense-row:focus-within{background:#f8fbff}
.monthly-entry .expense-name{font-weight:600}
.monthly-entry .expense-unit{color:var(--entry-muted);font-size:.78rem}
.monthly-entry .amount-wrap{position:relative}
.monthly-entry .amount-wrap::after{content:"บาท";position:absolute;right:.75rem;top:50%;transform:translateY(-50%);color:#687d91;font-size:.8rem;pointer-events:none}
.monthly-entry .amount-input{padding-right:3.2rem;font-variant-numeric:tabular-nums;font-weight:600}
.monthly-entry .payment-panel{background:#f4f8fd;border:1px solid #ccdded;border-radius:.75rem;padding:1rem}
.monthly-entry .payment-total{font-size:1.35rem;font-weight:700;font-variant-numeric:tabular-nums}
.monthly-entry .balance-preview{font-variant-numeric:tabular-nums}
.monthly-entry .entry-footer{position:sticky;bottom:-1rem;background:#fff;border-top:1px solid var(--entry-border);margin:1rem -1rem -1rem;padding:.85rem 1rem;z-index:2}
@media(max-width:767.98px){
 .monthly-entry .expense-row{grid-template-columns:1fr 145px}
 .monthly-entry .expense-note{grid-column:1/-1}
 .monthly-entry .entry-footer .btn{min-height:44px}
}
</style>

<div class="monthly-entry">
    <div class="entry-context mb-3">
        <div class="d-flex flex-wrap justify-content-between gap-2">
            <div>
                <div class="fw-bold fs-6"><?= Html::encode($account->payer_name ?: 'ห้องว่าง') ?></div>
                <div class="entry-location"><?= Html::encode($location ?: 'ไม่ระบุสถานที่') ?></div>
            </div>
            <span class="badge bg-<?= $account->payer_name ? 'primary' : 'secondary' ?>-subtle text-<?= $account->payer_name ? 'primary' : 'secondary' ?>-emphasis align-self-start">
                <?= $account->payer_name ? 'เจ้าหน้าที่ผู้รับผิดชอบ' : 'ค่าใช้จ่ายประจำห้อง' ?>
            </span>
        </div>
        <div class="entry-meta">
            <span class="entry-chip">หมายเลขผู้ใช้ไฟฟ้า: <?= Html::encode($account->electric_account_no ?: 'ยังไม่ระบุ') ?></span>
            <span class="entry-chip">ผู้พักอายุเกิน 15 ปี: <?= (int) $account->occupants_over_15 ?> คน</span>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-end mb-2">
        <div>
            <div class="fw-semibold">รายการค่าใช้จ่าย</div>
            <div class="small text-muted">กรอกเฉพาะรายการที่เกิดขึ้นในเดือนนี้</div>
        </div>
    </div>

    <div class="expense-list mb-3">
        <?php foreach ($types as $type):
            $item = $existing[$type->id] ?? null;
            $amount = $item?->amount ?? $defaults[$type->id] ?? 0;
        ?>
            <div class="expense-row">
                <label class="mb-0" for="expense-<?= $type->id ?>">
                    <span class="expense-name"><?= Html::encode($type->name) ?></span>
                    <span class="expense-unit d-block"><?= Html::encode($type->unit_name ?: 'บาท') ?></span>
                </label>
                <div class="amount-wrap">
                    <?= Html::input('number', "items[{$type->id}][amount]", $amount, [
                        'id' => "expense-{$type->id}",
                        'class' => 'form-control text-end js-item-amount amount-input',
                        'step' => '.01',
                        'min' => 0,
                        'disabled' => $isLocked,
                        'aria-label' => 'จำนวนเงิน ' . $type->name,
                    ]) ?>
                </div>
                <div class="expense-note">
                    <?= Html::textInput("items[{$type->id}][note]", $item?->note ?? '', [
                        'class' => 'form-control',
                        'placeholder' => 'หมายเหตุ (ถ้ามี)',
                        'disabled' => $isLocked,
                        'aria-label' => 'หมายเหตุ ' . $type->name,
                    ]) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="payment-panel">
        <div class="row g-3 align-items-end">
            <div class="col-lg-4">
                <div class="small text-muted">ค่าใช้จ่ายรวม</div>
                <div class="payment-total"><span id="account-total">0.00</span> <span class="fs-6 fw-normal">บาท</span></div>
            </div>
            <div class="col-lg-4">
                <label class="form-label fw-semibold" for="paid-amount">ยอดที่ชำระแล้ว</label>
                <div class="amount-wrap">
                    <?= Html::input('number', 'paid_amount', $account->paid_amount, [
                        'id' => 'paid-amount',
                        'class' => 'form-control text-end amount-input',
                        'step' => '.01',
                        'min' => 0,
                        'disabled' => $isLocked,
                    ]) ?>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="small text-muted">ยอดคงเหลือ</div>
                <div class="fs-5 fw-semibold balance-preview"><span id="account-balance">0.00</span> บาท</div>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold" for="account-note">หมายเหตุรวม</label>
                <?= Html::textarea('note', $account->note, [
                    'id' => 'account-note',
                    'class' => 'form-control',
                    'rows' => 2,
                    'placeholder' => 'ข้อมูลเพิ่มเติมของรายการประจำเดือน (ถ้ามี)',
                    'disabled' => $isLocked,
                ]) ?>
            </div>
        </div>
    </div>

    <div class="entry-footer d-flex flex-wrap justify-content-end gap-2">
        <?= Html::button($isLocked ? 'ปิด' : 'ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
        <?php if (!$isLocked): ?>
            <?= Html::submitButton('บันทึกค่าใช้จ่าย', ['class' => 'btn btn-primary px-4']) ?>
        <?php endif; ?>
    </div>
</div>

<?php
ActiveForm::end();
$this->registerJs(<<<'JS'
function updateAccountSummary(){
    let total = 0;
    document.querySelectorAll('.js-item-amount').forEach(input => total += parseFloat(input.value || 0));
    const paid = parseFloat(document.getElementById('paid-amount')?.value || 0);
    const options = {minimumFractionDigits: 2, maximumFractionDigits: 2};
    document.getElementById('account-total').textContent = total.toLocaleString('th-TH', options);
    document.getElementById('account-balance').textContent = Math.max(total - paid, 0).toLocaleString('th-TH', options);
}
document.querySelectorAll('.js-item-amount, #paid-amount').forEach(input => input.addEventListener('input', updateAccountSummary));
updateAccountSummary();
document.getElementById('monthly-account-form')?.addEventListener('submit', function(event){
    let total = 0;
    document.querySelectorAll('.js-item-amount').forEach(input => total += parseFloat(input.value || 0));
    if (total <= 0 && !window.confirm('ค่าใช้จ่ายรวมเป็น 0 บาท ยืนยันว่าเดือนนี้ไม่มีค่าใช้จ่ายและให้ถือว่าชำระครบแล้วหรือไม่?')) {
        event.preventDefault();
        event.stopImmediatePropagation();
    }
}, true);
handleFormSubmit('#monthly-account-form', null, function(response){
    if (response && response.redirect) location.href = response.redirect;
});
JS);
?>

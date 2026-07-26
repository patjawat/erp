<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$location = implode(' / ', array_filter([$account->building_name, $account->unit_name, $account->room_name]));
$formWidget = ActiveForm::begin(['id' => 'housing-payment-form']);
?>
<style>
.payment-entry{--line:rgba(15,23,42,.1);--surface-2:#f7f9fc;--ink-2:#4a5568}.payment-context{padding:1rem;background:var(--surface-2);border:1px solid var(--line);border-radius:10px}.payment-balance{font-size:1.35rem;font-weight:700;font-variant-numeric:tabular-nums}.payment-methods{display:grid;grid-template-columns:1fr 1fr;gap:.5rem}.payment-methods label{display:flex;align-items:center;gap:.5rem;padding:.7rem .8rem;border:1px solid var(--line);border-radius:8px;cursor:pointer}.payment-methods label:has(input:checked){border-color:#0d6efd;box-shadow:0 0 0 3px rgba(13,110,253,.08)}
</style>
<div class="payment-entry">
    <div class="payment-context mb-3">
        <div class="small text-muted"><?= Html::encode($account->period?->name) ?></div>
        <strong><?= Html::encode($account->payer_name) ?></strong>
        <div class="small text-muted mt-1"><?= Html::encode($location) ?></div>
        <div class="d-flex justify-content-between align-items-end mt-3 pt-3 border-top">
            <span>ยอดคงเหลือ</span><span class="payment-balance"><?= Yii::$app->formatter->asDecimal($account->balance_amount, 2) ?> บาท</span>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-md-6"><?= $formWidget->field($form, 'paid_at')->input('datetime-local') ?></div>
        <div class="col-md-6"><?= $formWidget->field($form, 'amount')->input('number', ['step' => '.01', 'min' => '.01', 'max' => $account->balance_amount, 'class' => 'form-control text-end fw-semibold']) ?></div>
        <div class="col-12"><label class="form-label fw-semibold">วิธีชำระ</label><div class="payment-methods">
            <label><?= Html::radio('DynamicModel[payment_method]', $form->payment_method === 'cash', ['value' => 'cash']) ?> เงินสด</label>
            <label><?= Html::radio('DynamicModel[payment_method]', $form->payment_method === 'transfer', ['value' => 'transfer']) ?> เงินโอน</label>
        </div><?= Html::error($form, 'payment_method', ['class' => 'text-danger small']) ?></div>
        <div class="col-md-6"><?= $formWidget->field($form, 'reference_no')->textInput(['placeholder' => 'เช่น เลขรายการโอน']) ?></div>
        <div class="col-md-6"><?= $formWidget->field($form, 'note')->textInput(['placeholder' => 'ถ้ามี']) ?></div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
        <?= Html::submitButton('ยืนยันรับชำระและออกใบเสร็จ', ['class' => 'btn btn-primary']) ?>
    </div>
</div>
<?php ActiveForm::end();
$this->registerJs("handleFormSubmit('#housing-payment-form', null, function(r){if(r&&r.redirect){window.location.href=r.redirect;}});");
?>

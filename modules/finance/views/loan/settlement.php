<?php

use app\components\AppHelper;
use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanSettlement;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** ช่องวันที่มาตรฐาน ERP — datepicker ไทย (พ.ศ. วว/ดด/พ.ศ.) */
$thaiDate = static function ($model, string $attr): string {
    $id = Html::getInputId($model, $attr);
    return '<label class="form-label" for="' . $id . '">' . Html::encode($model->getAttributeLabel($attr)) . '</label>'
        . DatepickerThai::widget([
            'name' => Html::getInputName($model, $attr),
            'value' => $model->$attr ? AppHelper::convertToThai($model->$attr) : '',
            'options' => ['id' => $id, 'autocomplete' => 'off', 'placeholder' => 'วว/ดด/พ.ศ.'],
        ])
        . Html::error($model, $attr, ['class' => 'invalid-feedback d-block']);
};

/** @var yii\web\View $this */
/** @var FinanceLoan $loan */
/** @var FinanceLoanSettlement $settlement */
/** @var string $title */

$this->title = $title;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเงินยืม', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $loan->contract_no, 'url' => ['view', 'id' => $loan->id]];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-cash-coin" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?><?= Html::encode($loan->contract_no) ?> · <?= Html::encode($loan->borrower_name) ?><?php $this->endBlock();

// ยอดคงค้างก่อนรายการนี้ = ยอดยืม ลบผลรวมของรายการที่มาก่อนหน้าตามลำดับครั้งที่
$before = (float) $loan->approved_amount;
foreach ($loan->settlements as $row) {
    if ((int) $row->seq < (int) $settlement->seq) {
        $before -= $row->totalAmount();
    }
}
$before = round(max(0, $before), 2);
?>

<div class="row g-3">
<div class="col-12 col-xl-8">
<?php $form = ActiveForm::begin(); ?>
<?= $form->errorSummary($settlement, ['class' => 'alert alert-danger']) ?>

<section class="card border mb-3" aria-labelledby="settle-heading">
    <div class="card-header bg-body d-flex justify-content-between align-items-center gap-2">
        <h5 class="mb-0" id="settle-heading">ครั้งที่ <?= (int) $settlement->seq ?></h5>
        <span class="text-body-secondary small">คงค้างก่อนรายการนี้ <span class="font-monospace"><?= number_format($before, 2) ?></span> บาท</span>
    </div>
    <div class="card-body"><div class="row g-3">
        <div class="col-md-4"><?= $thaiDate($settlement, 'settled_at') ?></div>
        <div class="col-md-4">
            <?= $form->field($settlement, 'voucher_amount')->textInput(['type' => 'number', 'step' => '0.01', 'min' => 0, 'class' => 'form-control text-end', 'id' => 'settle-voucher']) ?>
            <div class="form-text">ยอดตามใบสำคัญที่นำมาชดใช้</div>
        </div>
        <div class="col-md-4">
            <?= $form->field($settlement, 'cash_amount')->textInput(['type' => 'number', 'step' => '0.01', 'min' => 0, 'class' => 'form-control text-end', 'id' => 'settle-cash']) ?>
            <div class="form-text">เงินเหลือจ่ายที่คืนเป็นเงินสด</div>
        </div>
        <div class="col-12">
            <div class="alert alert-light border mb-0 d-flex flex-wrap justify-content-between gap-2" role="status">
                <span>รวมส่งใช้ครั้งนี้ <strong class="font-monospace" id="settle-total">0.00</strong> บาท</span>
                <span>คงค้างหลังบันทึก <strong class="font-monospace" id="settle-balance"><?= number_format($before, 2) ?></strong> บาท</span>
            </div>
        </div>
    </div></div>
</section>

<section class="card border mb-3" aria-labelledby="evidence-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="evidence-heading">หลักฐานประกอบ</h5></div>
    <div class="card-body"><div class="row g-3">
        <div class="col-md-4">
            <?= $form->field($settlement, 'receipt_no')->textInput(['maxlength' => true, 'placeholder' => 'เช่น บค.201']) ?>
            <div class="form-text">บร. สำหรับเงินสด · บค. สำหรับใบสำคัญ</div>
        </div>
        <div class="col-md-4"><?= $form->field($settlement, 'document_no')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-4"><?= $thaiDate($settlement, 'evidence_sent_at') ?></div>
        <div class="col-md-3"><?= $form->field($settlement, 'receipt_book_no')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-3"><?= $form->field($settlement, 'receipt_number')->textInput(['maxlength' => true]) ?></div>
        <div class="col-md-6">
            <?= $form->field($settlement, 'late_reason')->textInput(['maxlength' => true]) ?>
            <div class="form-text">กรอกเมื่อส่งใช้ล่าช้า หรือใบสำคัญต่ำกว่า 70% — ใช้พิมพ์ลงบันทึกนำส่งหลักฐาน</div>
        </div>
        <div class="col-12"><?= $form->field($settlement, 'note')->textInput(['maxlength' => true]) ?></div>
    </div></div>
</section>

<div class="d-flex justify-content-end gap-2 mb-4">
    <?= Html::a('ยกเลิก', ['view', 'id' => $loan->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="bi bi-check2-circle me-1"></i> บันทึกการส่งใช้', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>
</div>

<div class="col-12 col-xl-4">
    <section class="card border" aria-labelledby="loan-summary-heading">
        <div class="card-header bg-body"><h5 class="mb-0" id="loan-summary-heading">ยอดของใบยืมนี้</h5></div>
        <div class="card-body">
            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-body-secondary">ยอดเงินยืม</span><span class="font-monospace"><?= number_format($loan->approved_amount, 2) ?></span></div>
            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-body-secondary">ส่งใช้แล้ว</span><span class="font-monospace"><?= number_format($loan->voucher_amount + $loan->cash_return_amount, 2) ?></span></div>
            <div class="d-flex justify-content-between py-2 fw-semibold"><span>คงเหลือปัจจุบัน</span><span class="font-monospace"><?= number_format($loan->outstanding_amount, 2) ?></span></div>
            <div class="text-body-secondary small mt-2">กำหนดการคืน <?= $loan->due_at ? Yii::$app->formatter->asDate($loan->due_at, 'php:d/m/Y') : 'ยังไม่กำหนด' ?></div>
        </div>
        <?php if ($loan->settlements): ?>
        <ul class="list-group list-group-flush">
            <?php foreach ($loan->settlements as $row): ?>
            <li class="list-group-item d-flex justify-content-between gap-2 small<?= (int) $row->seq === (int) $settlement->seq ? ' bg-body-tertiary fw-semibold' : '' ?>">
                <span>ครั้งที่ <?= (int) $row->seq ?> · <?= Yii::$app->formatter->asDate($row->settled_at, 'php:d/m/Y') ?></span>
                <span class="font-monospace"><?= number_format($row->totalAmount(), 2) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </section>
</div>
</div>

<?php
$this->registerJs(<<<JS
(function () {
    var before = {$before};
    var voucher = document.getElementById('settle-voucher');
    var cash = document.getElementById('settle-cash');
    var totalOut = document.getElementById('settle-total');
    var balanceOut = document.getElementById('settle-balance');
    var format = function (n) {
        return n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    };
    var update = function () {
        var total = (parseFloat(voucher.value) || 0) + (parseFloat(cash.value) || 0);
        totalOut.textContent = format(total);
        var balance = before - total;
        balanceOut.textContent = format(balance);
        // ส่งใช้เกินยอดที่ยืมไปแปลว่ากรอกผิด เตือนตั้งแต่ยังไม่กดบันทึก
        balanceOut.classList.toggle('text-danger-emphasis', balance < -0.005);
    };
    [voucher, cash].forEach(function (el) { el.addEventListener('input', update); });
    update();
})();
JS);

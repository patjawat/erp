<?php

use app\modules\finance\models\FinanceLoan;
use app\modules\finance\models\FinanceLoanFollowup;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var FinanceLoan $loan */
/** @var FinanceLoanFollowup $letter */

$this->title = 'ออกหนังสือติดตาม ครั้งที่ ' . (int) $letter->letter_seq;
$this->params['breadcrumbs'][] = ['label' => 'การเงิน', 'url' => ['/finance/dashboard']];
$this->params['breadcrumbs'][] = ['label' => 'ทะเบียนเงินยืม', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $loan->contract_no, 'url' => ['view', 'id' => $loan->id]];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="mb-0 d-flex align-items-center gap-2"><i class="bi bi-envelope-paper" aria-hidden="true"></i><?= Html::encode($this->title) ?></h4>
<?php $this->endBlock();
$this->beginBlock('sub-title'); ?><?= Html::encode($loan->contract_no) ?> · <?= Html::encode($loan->borrower_name) ?><?php $this->endBlock();
?>

<div class="row g-3">
<div class="col-12 col-xl-8">
<?php $form = ActiveForm::begin(); ?>
<?= $form->errorSummary($letter, ['class' => 'alert alert-danger']) ?>

<section class="card border mb-3" aria-labelledby="letter-heading">
    <div class="card-header bg-body"><h5 class="mb-0" id="letter-heading">รายละเอียดหนังสือ</h5></div>
    <div class="card-body"><div class="row g-3">
        <div class="col-md-5">
            <?= $form->field($letter, 'letter_no')->textInput(['maxlength' => true, 'placeholder' => 'เช่น ลย 0033.301.05/123']) ?>
        </div>
        <div class="col-md-3"><?= $form->field($letter, 'letter_date')->input('date') ?></div>
        <div class="col-md-4">
            <?= $form->field($letter, 'new_due_at')->input('date') ?>
            <div class="form-text">วันที่ระบุในหนังสือว่าให้ส่งใช้ให้แล้วเสร็จภายใน</div>
        </div>
        <div class="col-12"><?= $form->field($letter, 'note')->textarea(['rows' => 2])->label('หมายเหตุภายใน (ไม่พิมพ์ลงหนังสือ)') ?></div>
    </div></div>
</section>

<div class="alert alert-info d-flex align-items-start gap-2" role="status">
    <i class="bi bi-info-circle mt-1" aria-hidden="true"></i>
    <div>
        <strong>เมื่อบันทึกแล้วระบบจะทำสามอย่าง</strong>
        <div class="small">
            บันทึกหนังสือครั้งที่ <?= (int) $letter->letter_seq ?> ลงประวัติการติดตาม ·
            แจ้งผู้ยืมทาง Telegram ว่ามีหนังสือออก ·
            เตรียมแม่แบบให้กด “พิมพ์หนังสือ” เพื่อตรวจแก้ก่อนพิมพ์
        </div>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mb-4">
    <?= Html::a('ยกเลิก', ['view', 'id' => $loan->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('<i class="bi bi-send me-1"></i> ออกหนังสือติดตาม', ['class' => 'btn btn-danger']) ?>
</div>
<?php ActiveForm::end(); ?>
</div>

<div class="col-12 col-xl-4">
    <section class="card border mb-3" aria-labelledby="debt-heading">
        <div class="card-header bg-body"><h5 class="mb-0" id="debt-heading">สถานะหนี้</h5></div>
        <div class="card-body">
            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-body-secondary">ยอดเงินยืม</span><span class="font-monospace"><?= number_format($loan->approved_amount, 2) ?></span></div>
            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-body-secondary">ส่งใช้แล้ว</span><span class="font-monospace"><?= number_format($loan->voucher_amount + $loan->cash_return_amount, 2) ?></span></div>
            <div class="d-flex justify-content-between py-2 fw-semibold"><span>คงเหลือ</span><span class="font-monospace text-danger-emphasis"><?= number_format($loan->outstanding_amount, 2) ?></span></div>
            <div class="mt-3">
                <span class="badge <?= $loan->dueBadgeClass() ?>"><?= Html::encode($loan->dueLabel()) ?></span>
                <div class="text-body-secondary small mt-2">
                    ครบกำหนด <?= $loan->due_at ? Yii::$app->formatter->asDate($loan->due_at, 'php:d/m/Y') : 'ไม่ระบุ' ?>
                </div>
            </div>
        </div>
    </section>

    <?php $letters = array_filter($loan->followups, static fn($f) => $f->channel === FinanceLoanFollowup::CHANNEL_LETTER); ?>
    <?php if ($letters): ?>
    <section class="card border" aria-labelledby="history-heading">
        <div class="card-header bg-body"><h5 class="mb-0" id="history-heading">หนังสือที่ออกไปแล้ว</h5></div>
        <ul class="list-group list-group-flush">
            <?php foreach ($letters as $previous): ?>
            <li class="list-group-item small">
                <div class="fw-semibold">ครั้งที่ <?= (int) $previous->letter_seq ?> · <?= Html::encode($previous->letter_no ?: 'ไม่ระบุเลขที่') ?></div>
                <div class="text-body-secondary">
                    ลงวันที่ <?= $previous->letter_date ? Yii::$app->formatter->asDate($previous->letter_date, 'php:d/m/Y') : '—' ?>
                    <?= $previous->new_due_at ? ' · กำหนดใหม่ ' . Yii::$app->formatter->asDate($previous->new_due_at, 'php:d/m/Y') : '' ?>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
    </section>
    <?php endif; ?>
</div>
</div>

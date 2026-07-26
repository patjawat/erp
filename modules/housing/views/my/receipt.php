<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\housing\models\Receipt $receipt */

$this->title = 'ใบเสร็จรับเงิน ' . $receipt->receipt_no;
$payment = $receipt->payment;
$allocation = $payment->allocations[0] ?? null;
$account = $allocation?->invoice?->monthlyAccount;
?>
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
        <div>
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับไปข้อมูลบ้านพัก', ['/profile', 'name' => 'housing', 'housing_tab' => 'documents'], ['class' => 'text-decoration-none small']) ?>
            <h1 class="h4 mt-2 mb-0"><?= Html::encode($this->title) ?></h1>
        </div>
        <?= Html::a('<i class="bi bi-printer me-1"></i>พิมพ์ใบเสร็จ', ['receipt', 'id' => $receipt->id, 'print' => 1], [
            'class' => 'btn btn-outline-primary',
            'target' => '_blank',
        ]) ?>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <div class="row g-4">
                <div class="col-md-6"><div class="text-muted small">เลขที่ใบเสร็จ</div><div class="fw-semibold"><?= Html::encode($receipt->receipt_no) ?></div></div>
                <div class="col-md-6"><div class="text-muted small">สถานะ</div><span class="badge <?= $receipt->status === 'issued' ? 'text-bg-success' : 'text-bg-danger' ?>"><?= $receipt->status === 'issued' ? 'ออกใบเสร็จแล้ว' : 'ยกเลิกแล้ว' ?></span></div>
                <div class="col-md-6"><div class="text-muted small">วันที่รับชำระ</div><div><?= Yii::$app->formatter->asDatetime($payment->paid_at, 'php:d/m/Y H:i') ?></div></div>
                <div class="col-md-6"><div class="text-muted small">วิธีชำระ</div><div><?= $payment->payment_method === 'transfer' ? 'เงินโอน' : 'เงินสด' ?></div></div>
                <div class="col-md-6"><div class="text-muted small">รอบค่าใช้จ่าย</div><div><?= Html::encode($account?->period?->name ?? '-') ?></div></div>
                <div class="col-md-6"><div class="text-muted small">เลขอ้างอิง</div><div><?= Html::encode($payment->reference_no ?: '-') ?></div></div>
            </div>
            <div class="rounded-3 bg-success-subtle p-4 mt-4 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">จำนวนเงินที่รับ</span>
                <span class="fs-4 fw-bold text-success"><?= Yii::$app->formatter->asDecimal($receipt->amount, 2) ?> บาท</span>
            </div>
            <?php if ($receipt->status === 'cancelled' && $receipt->cancel_reason): ?>
                <div class="alert alert-danger mt-4 mb-0"><strong>เหตุผลที่ยกเลิก:</strong> <?= Html::encode($receipt->cancel_reason) ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>

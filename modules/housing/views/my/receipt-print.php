<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\housing\models\Receipt $receipt */

$payment = $receipt->payment;
$allocation = $payment->allocations[0] ?? null;
$account = $allocation?->invoice?->monthlyAccount;
$occupancy = $account?->occupancy;
$resident = $occupancy?->employee;
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title><?= Html::encode('ใบเสร็จรับเงิน ' . $receipt->receipt_no) ?></title>
    <style>
        body{font-family:"Sarabun",Tahoma,sans-serif;color:#1f2937;margin:32px}.receipt{max-width:760px;margin:auto;border:1px solid #d1d5db;padding:36px}h1{text-align:center;font-size:24px;margin:0 0 6px}.subtitle{text-align:center;color:#6b7280;margin-bottom:32px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:18px 30px}.label{color:#6b7280;font-size:13px;margin-bottom:4px}.amount{margin-top:30px;padding:20px;background:#f0fdf4;display:flex;justify-content:space-between;font-size:20px;font-weight:700}.cancelled{margin-top:20px;padding:12px;color:#991b1b;background:#fef2f2}.signature{display:flex;justify-content:flex-end;margin-top:70px;text-align:center}.signature div{width:260px;border-top:1px solid #374151;padding-top:8px}@media print{body{margin:0}.receipt{border:0}}
    </style>
</head>
<body onload="window.print()">
<div class="receipt">
    <h1>ใบเสร็จรับเงิน</h1>
    <div class="subtitle">ระบบบ้านพัก</div>
    <div class="grid">
        <div><div class="label">เลขที่ใบเสร็จ</div><?= Html::encode($receipt->receipt_no) ?></div>
        <div><div class="label">วันที่รับชำระ</div><?= Yii::$app->formatter->asDatetime($payment->paid_at, 'php:d/m/Y H:i') ?></div>
        <div><div class="label">ได้รับเงินจาก</div><?= Html::encode($resident?->fullname() ?? '-') ?></div>
        <div><div class="label">รอบค่าใช้จ่าย</div><?= Html::encode($account?->period?->name ?? '-') ?></div>
        <div><div class="label">วิธีชำระ</div><?= $payment->payment_method === 'transfer' ? 'เงินโอน' : 'เงินสด' ?></div>
        <div><div class="label">เลขอ้างอิง</div><?= Html::encode($payment->reference_no ?: '-') ?></div>
    </div>
    <div class="amount"><span>จำนวนเงินที่รับ</span><span><?= Yii::$app->formatter->asDecimal($receipt->amount, 2) ?> บาท</span></div>
    <?php if ($receipt->status === 'cancelled'): ?><div class="cancelled">เอกสารนี้ถูกยกเลิก<?= $receipt->cancel_reason ? ': ' . Html::encode($receipt->cancel_reason) : '' ?></div><?php endif; ?>
    <div class="signature"><div>ผู้รับเงิน</div></div>
</div>
</body>
</html>

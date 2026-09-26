<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinancePayable;

/** เมนูงานเจ้าหนี้ (การเงิน) — กล่องรอรับ / ทะเบียนคุมเจ้าหนี้ / รับวางบิล / เจ้าหนี้ค้างชำระ */
$active = $active ?? '';

// จำนวนรายการในกล่องรอรับที่ยังรอการเงินตรวจ (รวมที่ขอข้อมูลเพิ่ม)
$inboxPending = (int) FinanceInbox::find()
    ->where(['status' => [FinanceInbox::STATUS_PENDING_REVIEW, FinanceInbox::STATUS_NEEDS_INFORMATION]])
    ->count();

// บิลที่อนุมัติเข้าทะเบียนแล้วแต่ผู้ขายยังไม่มาวางบิล
$billingPending = (int) FinancePayable::find()
    ->where(['status' => FinancePayable::STATUS_APPROVED, 'billing_id' => null])
    ->count();

$items = [
    ['key' => 'inbox', 'label' => 'กล่องรอรับ', 'icon' => 'bi-inbox', 'url' => ['/finance/inbox'], 'badge' => $inboxPending],
    ['key' => 'payable', 'label' => 'ทะเบียนคุมเจ้าหนี้', 'icon' => 'bi-journal-text', 'url' => ['/finance/payable']],
    ['key' => 'billing', 'label' => 'ทะเบียนรับวางบิล', 'icon' => 'bi-receipt', 'url' => ['/finance/billing'], 'badge' => $billingPending],
    ['key' => 'aging', 'label' => 'เจ้าหนี้ค้างชำระ', 'icon' => 'bi-hourglass-split', 'url' => ['/finance/payable/aging']],
    ['key' => 'payments', 'label' => 'รอบจ่าย', 'icon' => 'bi-clock-history', 'url' => ['/finance/payable/payments']],
    ['key' => 'cheque', 'label' => 'พิมพ์เช็ค', 'icon' => 'bi-cash-stack', 'url' => ['/finance/cheque']],
];
?>
<nav class="d-flex flex-wrap gap-2 mb-3" aria-label="เมนูงานเจ้าหนี้">
    <?php foreach ($items as $item): ?>
        <?php $isActive = $active === $item['key']; ?>
        <a href="<?= Url::to($item['url']) ?>" class="btn <?= $isActive ? 'btn-primary' : 'btn-outline-primary' ?> position-relative">
            <i class="bi <?= Html::encode($item['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($item['label']) ?>
            <?php if (!empty($item['badge'])): ?>
                <span class="badge rounded-pill <?= $isActive ? 'text-bg-light' : 'text-bg-danger' ?> ms-1"><?= number_format($item['badge']) ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>

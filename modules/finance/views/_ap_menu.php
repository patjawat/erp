<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayablePayment;

/**
 * เมนูงานเจ้าหนี้ (การเงิน) — page-nav แถวเดียว (แคปซูล/pill อัตโนมัติจาก .action-box)
 * กล่องรอรับ / ทะเบียนเจ้าหนี้ / ทะเบียนรับวางบิล / [จ่ายชำระ ▾ = dropdown]
 * กลุ่มจ่ายชำระรวมไว้ใน dropdown: จ่ายชำระ / รอบจ่าย / เจ้าหนี้ค้างชำระ / พิมพ์เช็ค
 */
$active = $active ?? '';
$payGroup = ['pay', 'payments', 'aging', 'cheque'];
$payActive = in_array($active, $payGroup, true);

$inboxPending = (int) FinanceInbox::find()->where(['status' => FinanceInbox::STATUS_PENDING_REVIEW])->count();
$billingPending = (int) FinancePayable::find()->where(['status' => FinancePayable::STATUS_APPROVED, 'billing_id' => null])->count();
$approvalPending = (int) FinancePayablePayment::find()->where(['status' => 'pending'])->count();

$items = [
    ['key' => 'inbox', 'label' => 'กล่องรอรับ', 'icon' => 'bi-inbox', 'url' => ['/finance/inbox'], 'badge' => $inboxPending],
    ['key' => 'payable', 'label' => 'ทะเบียนเจ้าหนี้', 'icon' => 'bi-journal-text', 'url' => ['/finance/payable']],
    ['key' => 'billing', 'label' => 'ทะเบียนรับวางบิล', 'icon' => 'bi-receipt', 'url' => ['/finance/billing'], 'badge' => $billingPending],
];
$payItems = [
    ['key' => 'pay', 'label' => 'จ่ายชำระ', 'icon' => 'bi-cash-stack', 'url' => ['/finance/payable/pay']],
    ['key' => 'payments', 'label' => 'รอบจ่าย', 'icon' => 'bi-list-check', 'url' => ['/finance/payable/payments'], 'badge' => $approvalPending],
    ['key' => 'aging', 'label' => 'เจ้าหนี้ค้างชำระ', 'icon' => 'bi-hourglass-split', 'url' => ['/finance/payable/aging']],
    ['key' => 'cheque', 'label' => 'พิมพ์เช็ค', 'icon' => 'bi-cash-stack', 'url' => ['/finance/cheque']],
];
?>
<nav class="d-flex flex-wrap gap-2 mb-3" aria-label="เมนูงานเจ้าหนี้">
    <?php foreach ($items as $item): ?>
        <?php $isActive = $active === $item['key']; ?>
        <a href="<?= Url::to($item['url']) ?>" class="btn <?= $isActive ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <i class="bi <?= Html::encode($item['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($item['label']) ?>
            <?php if (!empty($item['badge'])): ?>
                <span class="badge rounded-pill <?= $isActive ? 'text-bg-light' : 'text-bg-danger' ?> ms-1"><?= number_format($item['badge']) ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>

    <div class="dropdown">
        <button type="button" class="btn <?= $payActive ? 'btn-primary' : 'btn-outline-secondary' ?> dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-cash-stack me-1" aria-hidden="true"></i>จ่ายชำระ
            <?php if ($approvalPending): ?>
                <span class="badge rounded-pill <?= $payActive ? 'text-bg-light' : 'text-bg-danger' ?> ms-1"><?= number_format($approvalPending) ?></span>
            <?php endif; ?>
        </button>
        <ul class="dropdown-menu shadow-sm">
            <?php foreach ($payItems as $p): $pActive = $active === $p['key']; ?>
                <li>
                    <?php // ไม่ใช้ class .active ของธีม (บางหน้าให้ตัวอักษรขาวมองไม่เห็น) — กำหนดสีเองให้อ่านออกเสมอ ?>
                    <a class="dropdown-item d-flex align-items-center justify-content-between" href="<?= Url::to($p['url']) ?>"
                       style="<?= $pActive ? 'background-color:#eef0ff;color:#3d3fa4;font-weight:600;' : '' ?>">
                        <span><i class="bi <?= $pActive ? 'bi-check2' : Html::encode($p['icon']) ?> me-2" aria-hidden="true"></i><?= Html::encode($p['label']) ?></span>
                        <?php if (!empty($p['badge'])): ?>
                            <span class="badge rounded-pill text-bg-danger ms-3"><?= number_format($p['badge']) ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

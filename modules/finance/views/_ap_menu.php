<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\finance\models\FinanceInbox;
use app\modules\finance\models\FinancePayable;
use app\modules\finance\models\FinancePayablePayment;

/**
 * เมนูงานเจ้าหนี้ (การเงิน) — page-nav มาตรฐาน (แคปซูล/pill อัตโนมัติจาก .action-box)
 * ชั้นบน 4 กลุ่ม: กล่องรอรับ / ทะเบียนเจ้าหนี้ / ทะเบียนรับวางบิล / จ่ายชำระ
 * ชั้นล่าง (เฉพาะกลุ่มจ่ายชำระ) เป็น pill ขนาดเล็ก: รอบจ่าย / เจ้าหนี้ค้างชำระ / พิมพ์เช็ค
 */
$active = $active ?? '';
$payGroup = ['pay', 'payments', 'aging', 'cheque'];

$inboxPending = (int) FinanceInbox::find()->where(['status' => FinanceInbox::STATUS_PENDING_REVIEW])->count();
$billingPending = (int) FinancePayable::find()->where(['status' => FinancePayable::STATUS_APPROVED, 'billing_id' => null])->count();
$approvalPending = (int) FinancePayablePayment::find()->where(['status' => 'pending'])->count();

$items = [
    ['key' => 'inbox', 'label' => 'กล่องรอรับ', 'icon' => 'bi-inbox', 'url' => ['/finance/inbox'], 'badge' => $inboxPending],
    ['key' => 'payable', 'label' => 'ทะเบียนเจ้าหนี้', 'icon' => 'bi-journal-text', 'url' => ['/finance/payable']],
    ['key' => 'billing', 'label' => 'ทะเบียนรับวางบิล', 'icon' => 'bi-receipt', 'url' => ['/finance/billing'], 'badge' => $billingPending],
    ['key' => 'pay', 'label' => 'จ่ายชำระ', 'icon' => 'bi-cash-stack', 'url' => ['/finance/payable/pay'], 'group' => $payGroup],
];
$subItems = [
    ['key' => 'payments', 'label' => 'รอบจ่าย', 'icon' => 'bi-list-check', 'url' => ['/finance/payable/payments'], 'badge' => $approvalPending],
    ['key' => 'aging', 'label' => 'เจ้าหนี้ค้างชำระ', 'icon' => 'bi-hourglass-split', 'url' => ['/finance/payable/aging']],
    ['key' => 'cheque', 'label' => 'พิมพ์เช็ค', 'icon' => 'bi-cash-stack', 'url' => ['/finance/cheque']],
];
?>
<nav class="d-flex flex-wrap gap-2 mb-2" aria-label="เมนูงานเจ้าหนี้">
    <?php foreach ($items as $item): ?>
        <?php $isActive = $active === $item['key'] || in_array($active, $item['group'] ?? [], true); ?>
        <a href="<?= Url::to($item['url']) ?>" class="btn <?= $isActive ? 'btn-primary' : 'btn-outline-secondary' ?>">
            <i class="bi <?= Html::encode($item['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($item['label']) ?>
            <?php if (!empty($item['badge'])): ?>
                <span class="badge rounded-pill <?= $isActive ? 'text-bg-light' : 'text-bg-danger' ?> ms-1"><?= number_format($item['badge']) ?></span>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</nav>
<?php if (in_array($active, $payGroup, true)): ?>
    <nav class="d-flex flex-wrap gap-2 mb-3 ms-lg-4 ps-lg-1" aria-label="เมนูจ่ายชำระ">
        <?php foreach ($subItems as $s): ?>
            <?php $sActive = $active === $s['key']; ?>
            <a href="<?= Url::to($s['url']) ?>" class="btn btn-sm <?= $sActive ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi <?= Html::encode($s['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($s['label']) ?>
                <?php if (!empty($s['badge'])): ?>
                    <span class="badge rounded-pill <?= $sActive ? 'text-bg-light' : 'text-bg-danger' ?> ms-1"><?= number_format($s['badge']) ?></span>
                <?php endif; ?>
            </a>
        <?php endforeach; ?>
    </nav>
<?php endif; ?>

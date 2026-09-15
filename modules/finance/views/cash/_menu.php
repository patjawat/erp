<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $active */
$active = $active ?? '';

// เมนูระดับ 2 ของระบบรับ-จ่ายเงินบำรุง (pills แนวนอน ตามมาตรฐาน ERP)
// รายการที่ยังไม่ทำในเฟส 1 แสดงเป็น disabled พร้อมป้ายเฟส เพื่อให้เห็น roadmap
$items = [
    ['key' => 'overview', 'label' => 'ภาพรวม', 'icon' => 'bi-graph-up-arrow', 'url' => ['/finance/cash/overview']],
    ['key' => 'income', 'label' => 'รายรับ', 'icon' => 'bi-cash-coin', 'url' => ['/finance/cash/income']],
    ['key' => 'expense', 'label' => 'รายจ่าย', 'icon' => 'bi-receipt', 'url' => ['/finance/cash/expense']],
    ['key' => 'receipt', 'label' => 'ทะเบียนคุมใบเสร็จ', 'icon' => 'bi-receipt-cutoff', 'url' => ['/finance/receipt']],
    ['key' => 'plan', 'label' => 'แผนประจำปี', 'icon' => 'bi-calendar3', 'url' => ['/finance/cash/plan']],
    ['key' => 'close', 'label' => 'ปิดบัญชี', 'icon' => 'bi-lock', 'url' => ['/finance/cash/close-daily']],
    ['key' => 'account', 'label' => 'บัญชีเงิน', 'icon' => 'bi-bank', 'url' => ['/finance/account/bank']],
    ['key' => 'category', 'label' => 'จัดการผังบัญชี', 'icon' => 'bi-diagram-3', 'url' => ['/finance/cash/category']],
];
?>
<nav class="d-flex flex-wrap gap-2 mb-3" aria-label="เมนูรับ-จ่ายเงินบำรุง">
    <?php foreach ($items as $item): ?>
        <?php if ($item['url'] === null): ?>
            <span class="btn btn-outline-secondary disabled d-inline-flex align-items-center" tabindex="-1" aria-disabled="true">
                <i class="bi <?= Html::encode($item['icon']) ?> me-1" aria-hidden="true"></i>
                <?= Html::encode($item['label']) ?>
                <span class="badge bg-secondary-subtle text-secondary-emphasis ms-2"><?= Html::encode($item['phase']) ?></span>
            </span>
        <?php else: ?>
            <a href="<?= Url::to($item['url']) ?>"
               class="btn rounded-pill <?= $active === $item['key'] ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <i class="bi <?= Html::encode($item['icon']) ?> me-1" aria-hidden="true"></i>
                <?= Html::encode($item['label']) ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

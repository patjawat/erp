<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $active */
$active = $active ?? '';
$view = Yii::$app->user->can('financeView');
$operate = Yii::$app->user->can('financeOperate');
$payroll = Yii::$app->user->can('payrollView');

/**
 * เมนูระบบการเงิน — ปุ่มแบนเรียงตรง ๆ (ไม่มี dropdown เพื่อไม่ให้สับสนกับแถวปุ่มย่อยในแต่ละหน้า)
 * active = ตรง key หรืออยู่ใน alias
 */
$items = [
    ['show' => $view, 'key' => 'dashboard', 'label' => 'ภาพรวม', 'icon' => 'bi-speedometer2', 'url' => ['/finance/dashboard']],
    ['show' => $operate, 'key' => 'payment', 'label' => 'รับ-จ่ายเงินบำรุง', 'icon' => 'bi-bank', 'url' => ['/finance/cash']],
    ['show' => $view, 'key' => 'receipt', 'label' => 'ทะเบียนใบเสร็จ', 'icon' => 'bi-receipt-cutoff', 'url' => ['/finance/receipt']],
    ['show' => $view, 'key' => 'ar', 'label' => 'ลูกหนี้ค่ารักษา', 'icon' => 'bi-clipboard2-pulse', 'url' => ['/finance/ar']],
    ['show' => $view, 'key' => 'project', 'label' => 'โครงการเงินบำรุง', 'icon' => 'bi-diagram-3', 'url' => ['/finance/project']],
    ['show' => $view, 'key' => 'payable', 'label' => 'งานเจ้าหนี้', 'icon' => 'bi-journal-text', 'url' => ['/finance/payable'], 'alias' => ['aging']],
    ['show' => $operate, 'key' => 'loan', 'label' => 'เงินยืม', 'icon' => 'bi-person-vcard', 'url' => ['/finance/loan']],
    ['show' => $operate, 'key' => 'petty', 'label' => 'เงินสดย่อย', 'icon' => 'bi-wallet2', 'url' => ['/finance/petty-cash']],
    ['show' => $payroll, 'key' => 'payroll', 'label' => 'เงินเดือน', 'icon' => 'bi-cash-stack', 'url' => ['/finance/payroll']],
    ['show' => $view, 'key' => 'bankrec', 'label' => 'งบพิสูจน์ยอดเงินฝาก', 'icon' => 'bi-bank', 'url' => ['/finance/bank-reconcile']],
    ['show' => $view, 'key' => 'budget', 'label' => 'เงินงบประมาณ', 'icon' => 'bi-bank2', 'url' => ['/finance/budget']],
    ['show' => $view, 'key' => 'register', 'label' => 'ทะเบียนคุม', 'icon' => 'bi-journals', 'url' => ['/finance/register']],
];
?>
<nav class="d-flex flex-wrap gap-2" aria-label="เมนูระบบการเงิน">
    <?php foreach ($items as $item): ?>
        <?php if (empty($item['show'])) continue; ?>
        <?php $isActive = $active === $item['key'] || in_array($active, $item['alias'] ?? [], true); ?>
        <a href="<?= Url::to($item['url']) ?>"
           class="btn btn-sm <?= $isActive ? 'btn-primary' : 'btn-outline-primary' ?>">
            <i class="bi <?= Html::encode($item['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($item['label']) ?>
        </a>
    <?php endforeach; ?>
</nav>

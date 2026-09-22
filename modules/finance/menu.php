<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var string $active */
$active = $active ?? '';
$view = Yii::$app->user->can('financeView');
$operate = Yii::$app->user->can('financeOperate');
$payroll = Yii::$app->user->can('payrollView');

/**
 * เมนูระบบการเงิน — จัดกลุ่มเมนูหลัก + เมนูย่อย
 * type=link  : ปุ่มลิงก์เดี่ยว (active = ตรง key)
 * type=group : ปุ่ม dropdown (active = อยู่ใน activeKeys)
 */
$menu = [
    ['type' => 'link', 'show' => $view, 'key' => 'dashboard', 'label' => 'ภาพรวม', 'icon' => 'bi-speedometer2', 'url' => ['/finance/dashboard']],
    ['type' => 'group', 'label' => 'เงินบำรุง', 'icon' => 'bi-cash-coin', 'activeKeys' => ['payment', 'petty', 'project', 'bankrec', 'receipt'], 'items' => [
        ['show' => $operate, 'label' => 'รับ-จ่ายเงินบำรุง', 'icon' => 'bi-bank', 'url' => ['/finance/cash']],
        ['show' => $operate, 'label' => 'เงินสดย่อย/ทดรองจ่าย', 'icon' => 'bi-wallet2', 'url' => ['/finance/petty-cash']],
        ['show' => $view, 'label' => 'โครงการเงินบำรุง', 'icon' => 'bi-diagram-3', 'url' => ['/finance/project']],
        ['show' => $view, 'label' => 'งบพิสูจน์ยอดเงินฝาก', 'icon' => 'bi-bank', 'url' => ['/finance/bank-reconcile']],
        ['show' => $view, 'label' => 'ทะเบียนใบเสร็จ', 'icon' => 'bi-receipt-cutoff', 'url' => ['/finance/receipt']],
    ]],
    ['type' => 'group', 'label' => 'เจ้าหนี้–ลูกหนี้', 'icon' => 'bi-journal-text', 'activeKeys' => ['payable', 'ar'], 'items' => [
        ['show' => $view, 'label' => 'งานเจ้าหนี้ (AP)', 'icon' => 'bi-journal-text', 'url' => ['/finance/payable']],
        ['show' => $view, 'label' => 'ลูกหนี้ค่ารักษา (AR)', 'icon' => 'bi-clipboard2-pulse', 'url' => ['/finance/ar']],
    ]],
    ['type' => 'link', 'show' => $view, 'key' => 'budget', 'label' => 'เงินงบประมาณ', 'icon' => 'bi-bank2', 'url' => ['/finance/budget']],
    ['type' => 'link', 'show' => $operate, 'key' => 'loan', 'label' => 'เงินยืม', 'icon' => 'bi-person-vcard', 'url' => ['/finance/loan']],
    ['type' => 'link', 'show' => $view, 'key' => 'register', 'label' => 'ทะเบียนคุม', 'icon' => 'bi-journals', 'url' => ['/finance/register']],
    ['type' => 'link', 'show' => $payroll, 'key' => 'payroll', 'label' => 'เงินเดือน', 'icon' => 'bi-cash-stack', 'url' => ['/finance/payroll']],
    ['type' => 'group', 'label' => 'เพิ่มเติม', 'icon' => 'bi-three-dots', 'activeKeys' => [], 'items' => [
        ['show' => $view, 'label' => 'รายงาน', 'icon' => 'bi-bar-chart-line', 'disabled' => true],
        ['show' => $view, 'label' => 'ตั้งค่าระบบ', 'icon' => 'bi-gear', 'disabled' => true],
    ]],
];
?>
<nav class="d-flex flex-wrap gap-2" aria-label="เมนูระบบการเงิน">
    <?php foreach ($menu as $node): ?>
        <?php if (($node['type'] ?? 'link') === 'link'): ?>
            <?php if (empty($node['show'])) continue; ?>
            <a href="<?= Url::to($node['url']) ?>"
               class="btn <?= $active === $node['key'] ? 'btn-primary' : 'btn-outline-primary' ?>">
                <i class="bi <?= Html::encode($node['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($node['label']) ?>
            </a>
        <?php else: ?>
            <?php
            $children = array_filter($node['items'], fn ($it) => !empty($it['show']));
            if (!$children) continue;
            $groupActive = in_array($active, $node['activeKeys'], true);
            ?>
            <div class="dropdown">
                <button class="btn dropdown-toggle <?= $groupActive ? 'btn-primary' : 'btn-outline-primary' ?>" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi <?= Html::encode($node['icon']) ?> me-1" aria-hidden="true"></i><?= Html::encode($node['label']) ?>
                </button>
                <ul class="dropdown-menu">
                    <?php foreach ($children as $it): ?>
                        <li>
                            <?php if (!empty($it['disabled'])): ?>
                                <button class="dropdown-item" type="button" disabled><i class="bi <?= Html::encode($it['icon']) ?> me-2"></i><?= Html::encode($it['label']) ?></button>
                            <?php else: ?>
                                <a class="dropdown-item" href="<?= Url::to($it['url']) ?>"><i class="bi <?= Html::encode($it['icon']) ?> me-2"></i><?= Html::encode($it['label']) ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</nav>

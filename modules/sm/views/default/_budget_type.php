<?php

/**
 * มูลค่าขอซื้อ แยกตามประเภทเงิน — สไตล์ Dashboard V2 · สีจาก Bootstrap CSS var
 *
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 */

use yii\helpers\Html;

$rows = array_values(array_filter($dashboard->actualByBudgetType(), fn($b) => (float) $b['total'] > 0));
$max = 0.0;
foreach ($rows as $b) {
    $max = max($max, (float) $b['total']);
}
$vars = ['--bs-primary', '--bs-teal', '--bs-orange', '--bs-pink', '--bs-cyan', '--bs-purple', '--bs-green'];
?>
<div class="card border-0 shadow-sm h-100">
    <div class="card-header border-bottom d-flex align-items-center gap-2">
        <div class="erp-icon-box bg-secondary bg-opacity-10">
            <i class="bi bi-wallet2 text-secondary"></i>
        </div>
        <h6 class="text-body-secondary m-0">ขอซื้อแยกตามประเภทเงิน</h6>
    </div>
    <div class="card-body">
        <?php if (empty($rows)): ?>
            <p class="text-body-secondary small mb-0">ยังไม่มีข้อมูล</p>
        <?php else: ?>
            <?php foreach ($rows as $i => $b): $val = (float) $b['total']; $var = $vars[$i % count($vars)]; ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between small mb-1">
                        <span><?= Html::encode($b['title']) ?></span>
                        <span class="fw-medium"><?= number_format($val, 0) ?></span>
                    </div>
                    <div class="progress" style="height:6px;">
                        <div class="progress-bar" role="progressbar"
                             style="width:<?= $max > 0 ? round($val / $max * 100) : 0 ?>%;background:var(<?= $var ?>);"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php

/**
 * ท่อสถานะงานพัสดุ (pipeline) — เห็นว่างานไปกองอยู่ขั้นไหน
 * สไตล์ Dashboard V2 · สีจาก Bootstrap CSS var
 *
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 */

use yii\helpers\Html;

$stages = $dashboard->pipeline();
$maxVal = 0.0;
foreach ($stages as $s) {
    $maxVal = max($maxVal, $s['price']);
}
?>
<div class="card border-0 shadow-sm h-100">
    <div class="card-header border-bottom d-flex align-items-center gap-2">
        <div class="erp-icon-box bg-primary bg-opacity-10">
            <i class="bi bi-diagram-3 text-primary"></i>
        </div>
        <h6 class="text-body-secondary m-0">ท่อสถานะงานพัสดุ</h6>
    </div>
    <div class="card-body">
        <?php foreach ($stages as $s):
            $wd = $maxVal > 0 ? max(3, round($s['price'] / $maxVal * 100)) : 0;
            ?>
            <div class="mb-2">
                <div class="d-flex justify-content-between small mb-1">
                    <span>
                        <span class="d-inline-block rounded-circle me-1" style="width:9px;height:9px;background:var(<?= $s['cssvar'] ?>);"></span>
                        <?= Html::encode($s['label']) ?>
                        <span class="text-body-secondary">· <?= number_format($s['cnt']) ?> ใบ</span>
                    </span>
                    <span class="fw-medium"><?= number_format($s['price'], 0) ?></span>
                </div>
                <div class="progress" style="height:6px;">
                    <div class="progress-bar" role="progressbar"
                         style="width:<?= $wd ?>%;background:var(<?= $s['cssvar'] ?>);"></div>
                </div>
            </div>
        <?php endforeach; ?>
        <p class="small text-body-secondary mb-0 mt-3">
            <i class="bi bi-info-circle me-1"></i>ยอดกองที่ "ออกใบสั่งซื้อ" คือของที่สั่งแล้วรอส่งมอบ/ตรวจรับ
        </p>
    </div>
</div>

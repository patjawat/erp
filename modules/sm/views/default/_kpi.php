<?php

/**
 * การ์ด KPI ภาพรวมงานพัสดุ — สไตล์ Dashboard V2 (card border-0 shadow-sm)
 * ไม่ hardcode สี ใช้ Bootstrap theme classes ตาม DESIGN.md
 *
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 */

use yii\helpers\Html;

$k = $dashboard->kpi();

/** การ์ด KPI มาตรฐาน 1 ใบ */
$card = function (string $label, string $icon, string $role, string $value, string $unit, string $sub) {
    ob_start(); ?>
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="small text-body-secondary fw-semibold"><?= $label ?></div>
                    <i class="bi <?= $icon ?> text-<?= $role ?> opacity-50 fs-2"></i>
                </div>
                <div class="d-flex align-items-end gap-2">
                    <span class="fw-bold text-body lh-1 fs-2"><?= $value ?></span>
                    <?php if ($unit !== ''): ?><span class="small text-body-secondary mb-1"><?= $unit ?></span><?php endif; ?>
                </div>
                <div class="small text-body-secondary mt-2"><?= $sub ?></div>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
};

$prSub = '<span class="badge rounded-pill bg-primary-subtle text-primary-emphasis">' . number_format($k['pr']['total']) . ' รายการ</span> ทั้งปี';
$ipSub = '<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">' . number_format($k['inProgress']['total']) . ' รายการ</span> ยังไม่ตรวจรับ';
$rcSub = '<span class="badge rounded-pill bg-success-subtle text-success-emphasis">' . number_format($k['received']['total']) . ' รายการ</span> ตั้งแต่ตรวจรับ';
?>
<div class="row g-3">
    <?= $card('ขอซื้อ/ขอจ้าง', 'bi-cart-plus', 'primary', number_format($k['pr']['price'], 2), 'บาท', $prSub) ?>
    <?= $card('อยู่ระหว่างดำเนินการ', 'bi-hourglass-split', 'warning', number_format($k['inProgress']['price'], 2), 'บาท', $ipSub) ?>
    <?= $card('ตรวจรับแล้ว', 'bi-bag-check', 'success', number_format($k['received']['price'], 2), 'บาท', $rcSub) ?>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="small text-body-secondary fw-semibold">งบเงินบำรุง (ใช้ไป)</div>
                    <i class="bi bi-wallet2 text-info opacity-50 fs-2"></i>
                </div>
                <?php if ($k['planUsedPct'] !== null): ?>
                    <div class="d-flex align-items-end gap-2">
                        <span class="fw-bold text-body lh-1 fs-2"><?= number_format($k['planUsedPct'], 1) ?></span>
                        <span class="small text-body-secondary mb-1">%</span>
                    </div>
                    <div class="progress mt-2" style="height:6px;">
                        <div class="progress-bar <?= $k['planUsedPct'] > 100 ? 'bg-danger' : 'bg-success' ?>"
                             role="progressbar" style="width:<?= min(100, $k['planUsedPct']) ?>%"></div>
                    </div>
                    <div class="small text-body-secondary mt-1">จากแผน <?= number_format($k['planTotal'], 0) ?> บาท</div>
                <?php else: ?>
                    <div class="d-flex align-items-end gap-2">
                        <span class="fw-bold text-body-secondary lh-1 fs-2">—</span>
                    </div>
                    <div class="small text-warning-emphasis mt-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>ยังไม่บันทึกแผน
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

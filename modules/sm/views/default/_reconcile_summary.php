<?php

/**
 * สรุปกระทบยอด ตรวจรับ ↔ เข้าคลัง — สไตล์ Dashboard V2 · Bootstrap theme classes
 *
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 */

$rc = $dashboard->reconcile();
$pct = $rc['pct'];
$pendingPct = $rc['received'] > 0 ? round($rc['pending'] / $rc['received'] * 100, 1) : 0;
$ok = $rc['pending'] <= 0;
$pendingUrl = \yii\helpers\Url::to(['/sm/default/pending-items', 'thai_year' => $dashboard->year, 'label' => 'ทั้งหมด']);
?>
<div class="card border-0 shadow-sm h-100">
    <div class="card-header border-bottom d-flex align-items-center gap-2">
        <div class="erp-icon-box bg-success bg-opacity-10">
            <i class="bi bi-clipboard-check text-success"></i>
        </div>
        <h6 class="text-body-secondary m-0">สรุปกระทบยอด</h6>
    </div>
    <div class="card-body d-flex flex-column">
        <div class="d-flex justify-content-between align-items-baseline mb-2">
            <span class="text-body-secondary">มูลค่าตรวจรับ</span>
            <span class="h5 mb-0"><?= number_format($rc['received'], 0) ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-baseline mb-2">
            <span class="text-success-emphasis"><i class="bi bi-box-seam me-1"></i>เข้าคลังแล้ว</span>
            <span class="h5 mb-0 text-success-emphasis"><?= number_format($rc['stocked'], 0) ?></span>
        </div>
        <div class="d-flex justify-content-between align-items-baseline mb-3">
            <span class="<?= $ok ? 'text-body-secondary' : 'text-danger-emphasis' ?>">
                <i class="bi bi-exclamation-triangle me-1"></i>ค้างเข้าคลัง
            </span>
            <?php if ($ok): ?>
                <span class="h5 mb-0 text-body-secondary"><?= number_format($rc['pending'], 0) ?></span>
            <?php else: ?>
                <?= \yii\helpers\Html::a(
                    number_format($rc['pending'], 0) . ' <i class="bi bi-chevron-right small opacity-75"></i>',
                    $pendingUrl,
                    ['class' => 'h5 mb-0 open-modal link-danger text-decoration-none', 'data' => ['size' => 'modal-lg', 'pjax' => 0], 'title' => 'ดูใบที่ค้างเข้าคลังทั้งหมด']
                ) ?>
            <?php endif; ?>
        </div>

        <div class="mt-auto">
            <div class="d-flex justify-content-between small mb-1">
                <span>ตรงกัน (เข้าคลัง/ตรวจรับ)</span>
                <span class="fw-semibold"><?= $pct !== null ? number_format($pct, 1) . '%' : '—' ?></span>
            </div>
            <div class="progress" style="height:8px;">
                <div class="progress-bar bg-success" role="progressbar" style="width:<?= $pct !== null ? min(100, $pct) : 0 ?>%"></div>
            </div>
            <?php if (!$ok): ?>
                <p class="small text-danger-emphasis mt-2 mb-0">
                    <i class="bi bi-info-circle me-1"></i>
                    มีมูลค่าตรวจรับแล้ว <?= number_format($pendingPct, 0) ?>% ที่ยังไม่บันทึกเข้าคลัง ควรตรวจสอบ
                </p>
            <?php else: ?>
                <p class="small text-success-emphasis mt-2 mb-0"><i class="bi bi-check-circle me-1"></i>ตรวจรับและเข้าคลังตรงกันทั้งหมด</p>
            <?php endif; ?>
        </div>
    </div>
</div>

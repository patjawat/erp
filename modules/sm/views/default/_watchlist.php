<?php

/**
 * แผงเฝ้าระวัง — ใบที่ "ตรวจรับแล้วยังไม่เข้าคลัง" เรียงค้างนานสุด + คลิกเปิดใบ
 * สไตล์ Dashboard V2 · Bootstrap theme classes ตาม DESIGN.md
 *
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 */

use yii\helpers\Html;

$w = $dashboard->pendingStockWatchlist(10);
$b = $w['buckets'];
$clean = $w['count'] === 0;
$role = $clean ? 'success' : 'danger';

/** ป้ายจำนวนวันตามช่วงอายุ (theme-aware) */
$dayBadge = function (int $d): string {
    if ($d > 60) {
        $r = 'danger';
    } elseif ($d >= 31) {
        $r = 'warning';
    } else {
        $r = 'secondary';
    }
    return '<span class="badge rounded-pill bg-' . $r . '-subtle text-' . $r . '-emphasis">' . $d . ' วัน</span>';
};
?>
<div class="card border-0 shadow-sm h-100">
    <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-<?= $role ?> bg-opacity-10">
                <i class="bi bi-exclamation-diamond-fill text-<?= $role ?>"></i>
            </div>
            <h6 class="text-body-secondary m-0">จุดที่ต้องดำเนินการ — ตรวจรับแล้วยังไม่เข้าคลัง</h6>
        </div>
        <div class="d-flex gap-1 flex-wrap">
            <span class="badge rounded-pill bg-body-secondary text-body-emphasis border">รวม <?= number_format($w['count']) ?> ใบ · <?= number_format($w['totalValue'], 0) ?> บาท</span>
            <?php if ($b['gt60'] > 0): ?><span class="badge rounded-pill bg-danger-subtle text-danger-emphasis">เกิน 60 วัน <?= $b['gt60'] ?></span><?php endif; ?>
            <?php if ($b['d31_60'] > 0): ?><span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">31–60 วัน <?= $b['d31_60'] ?></span><?php endif; ?>
            <?php if ($b['le30'] > 0): ?><span class="badge rounded-pill bg-secondary-subtle text-secondary-emphasis">ไม่เกิน 30 วัน <?= $b['le30'] ?></span><?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?php if ($clean): ?>
            <p class="text-success-emphasis mb-0"><i class="bi bi-check-circle me-1"></i>ไม่มีใบที่ตรวจรับแล้วค้างเข้าคลัง — เรียบร้อยดี</p>
        <?php else: ?>
            <p class="small text-body-secondary mb-2">แสดง <?= count($w['items']) ?> ใบที่ค้างนานที่สุด (คลิกเพื่อเปิดใบ)</p>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">เลขที่ใบ</th>
                            <th scope="col">ประเภทพัสดุ</th>
                            <th scope="col" class="text-end">มูลค่า</th>
                            <th scope="col">วันตรวจรับ</th>
                            <th scope="col" class="text-end">ค้างมาแล้ว</th>
                            <th scope="col"></th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($w['items'] as $r): ?>
                            <tr>
                                <td class="fw-medium"><?= Html::encode($r['pr_number'] ?: '-') ?></td>
                                <td class="fw-light"><?= Html::encode($r['otn'] ?: '-') ?></td>
                                <td class="text-end fw-medium"><?= number_format((float) $r['value'], 0) ?></td>
                                <td class="fw-light text-nowrap small"><?= $r['gr_date'] ? Html::encode($r['gr_date']) : '-' ?></td>
                                <td class="text-end"><?= $dayBadge((int) $r['days']) ?></td>
                                <td class="text-end">
                                    <?= Html::a('<i class="bi bi-box-arrow-up-right"></i>', ['/purchase/order/view', 'id' => $r['id']], [
                                        'class' => 'btn btn-sm btn-outline-secondary',
                                        'title' => 'เปิดใบ ' . ($r['pr_number'] ?: ''),
                                        'data' => ['pjax' => 0],
                                    ]) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if ($w['count'] > count($w['items'])): ?>
                <p class="small text-body-secondary text-end mb-0 mt-2">
                    และอีก <?= number_format($w['count'] - count($w['items'])) ?> ใบ — ดูครบในตาราง "ประเภทพัสดุ" ด้านล่าง
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

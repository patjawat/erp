<?php

/**
 * เทียบแผนเงินบำรุง กับ ขอซื้อ(ผูกพัน) และ ตรวจรับ(รับจริง) รายหมวด + เตือนเกินแผน
 * สไตล์ Dashboard V2 · Bootstrap theme classes ตาม DESIGN.md
 *
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 * @var bool $onlyAlert  true = แบนเนอร์เตือนด้านบน, false = ตารางเทียบแผน
 */

use yii\helpers\Html;

$onlyAlert = $onlyAlert ?? false;

/* ---------- โหมดแบนเนอร์เตือน ---------- */
if ($onlyAlert) {
    $alerts = $dashboard->overPlanAlerts();
    if (empty($alerts)) {
        return;
    }
    ?>
    <div class="alert alert-danger d-flex align-items-start gap-2 mt-2" role="alert">
        <i class="bi bi-exclamation-octagon-fill fs-4"></i>
        <div>
            <strong>เตือน: มีการขอซื้อเกินแผนเงินบำรุง</strong>
            <ul class="mb-0 mt-1">
                <?php foreach ($alerts as $a): ?>
                    <li>
                        <strong><?= Html::encode($a['label']) ?></strong>
                        ขอซื้อ <?= number_format($a['actual'], 2) ?> บาท
                        เกินแผน <?= number_format($a['actual'] - $a['plan'], 2) ?> บาท
                        (<?= number_format($a['pct'], 0) ?>% ของแผน <?= number_format($a['plan'], 0) ?>)
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php
    return;
}

/* ---------- โหมดตารางเทียบแผน ---------- */
$cmp = $dashboard->planComparison();
$t = $cmp['total'];
?>
<div class="card border-0 shadow-sm">
    <div class="card-header border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-primary bg-opacity-10">
                <i class="bi bi-table text-primary"></i>
            </div>
            <h6 class="text-body-secondary m-0">เทียบแผนเงินบำรุง — แผน · ขอซื้อ · ตรวจรับ (รายหมวด)</h6>
        </div>
        <?php if (!$cmp['hasPlan']): ?>
            <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis">
                <i class="bi bi-exclamation-triangle me-1"></i>ยังไม่บันทึกยอดแผนปี <?= $dashboard->year ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th scope="col">หมวดพัสดุ</th>
                        <th scope="col" class="text-end">แผน</th>
                        <th scope="col" class="text-end">ขอซื้อ (ผูกพัน)</th>
                        <th scope="col" class="text-end">ตรวจรับ (รับจริง)</th>
                        <th scope="col" class="text-end">คงเหลือแผน</th>
                        <th scope="col" class="text-end" style="width:130px">% ขอซื้อ/แผน</th>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($cmp['rows'] as $r): ?>
                        <tr class="<?= $r['over'] ? 'table-danger' : '' ?>">
                            <td>
                                <span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:var(<?= $r['cssvar'] ?>);"></span>
                                <?= Html::encode($r['label']) ?>
                                <?php if ($r['over']): ?><i class="bi bi-exclamation-triangle-fill text-danger ms-1" title="ขอซื้อเกินแผน"></i><?php endif; ?>
                            </td>
                            <td class="text-end text-body-secondary"><?= $cmp['hasPlan'] ? number_format($r['plan'], 0) : '—' ?></td>
                            <td class="text-end fw-medium"><?= number_format($r['actual'], 0) ?></td>
                            <td class="text-end text-success-emphasis"><?= number_format($r['received'], 0) ?></td>
                            <td class="text-end <?= $r['remaining'] < 0 ? 'text-danger-emphasis fw-medium' : '' ?>">
                                <?= $cmp['hasPlan'] ? number_format($r['remaining'], 0) : '—' ?>
                            </td>
                            <td class="text-end">
                                <?php if ($r['pct'] !== null): ?>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height:6px;">
                                            <div class="progress-bar <?= $r['over'] ? 'bg-danger' : 'bg-primary' ?>"
                                                 role="progressbar" style="width:<?= min(100, $r['pct']) ?>%"></div>
                                        </div>
                                        <span class="small <?= $r['over'] ? 'text-danger-emphasis fw-semibold' : '' ?>"><?= number_format($r['pct'], 0) ?>%</span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-body-secondary small">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-group-divider">
                    <tr class="fw-semibold">
                        <td>รวม</td>
                        <td class="text-end"><?= $cmp['hasPlan'] ? number_format($t['plan'], 0) : '—' ?></td>
                        <td class="text-end"><?= number_format($t['actual'], 0) ?></td>
                        <td class="text-end text-success-emphasis"><?= number_format($t['received'], 0) ?></td>
                        <td class="text-end <?= $t['remaining'] < 0 ? 'text-danger-emphasis' : '' ?>"><?= $cmp['hasPlan'] ? number_format($t['remaining'], 0) : '—' ?></td>
                        <td class="text-end"><?= $t['pct'] !== null ? number_format($t['pct'], 0) . '%' : '—' ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <?php if (!$cmp['hasPlan']): ?>
            <p class="small text-body-secondary mt-3 mb-0">
                <i class="bi bi-info-circle me-1"></i>
                คอลัมน์ "แผน" และ "คงเหลือ" จะแสดงเมื่อบันทึกยอดแผนจัดซื้อปี <?= $dashboard->year ?> ในระบบแผนแล้ว
                — เมื่อมีแผน ระบบจะเตือนอัตโนมัติเมื่อยอดขอซื้อเกินแผน
            </p>
        <?php endif; ?>
    </div>
</div>

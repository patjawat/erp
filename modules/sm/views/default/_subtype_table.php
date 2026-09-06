<?php

/**
 * ตารางรายละเอียดประเภทพัสดุย่อย (ส่วนที่ AJAX สลับตามเดือน)
 * Bootstrap theme classes ตาม DESIGN.md · สีจุดจาก CSS var
 *
 * @var app\modules\sm\services\PurchaseDashboardService $dashboard
 * @var int|null $month  เดือน (10-9 ตามปีงบ) หรือ null = ทั้งปี
 */

use yii\helpers\Html;
use yii\helpers\Url;

$month = isset($month) && $month !== '' ? (int) $month : null;
$groups = $dashboard->bySubType($month);
$year = $dashboard->year;

/**
 * แสดงค่า "ค้างเข้าคลัง" — ถ้ามากกว่า 0 ทำเป็นลิงก์เปิด modal รายการใบจริง
 * @param array $extra พารามิเตอร์กรอง (cat หรือ subtype) + label
 */
$pendingCell = function (float $pending, array $extra, bool $strong) use ($year, $month) {
    $num = number_format($pending, 0);
    if ($pending <= 0) {
        return '<span class="text-body-secondary">' . $num . '</span>';
    }
    $url = Url::to(array_merge(['/sm/default/pending-items', 'thai_year' => $year, 'month' => $month], $extra));
    $cls = 'open-modal link-danger text-decoration-none' . ($strong ? ' fw-bold' : ' fw-semibold');
    return Html::a(
        '<i class="bi bi-exclamation-triangle-fill small"></i> ' . $num . ' <i class="bi bi-chevron-right small opacity-75"></i>',
        $url,
        ['class' => $cls, 'data' => ['size' => 'modal-lg', 'pjax' => 0], 'title' => 'ดูใบที่ค้างเข้าคลัง']
    );
};

$hasData = false;
foreach ($groups as $g) {
    if ($g['totals']['ordered'] > 0 || $g['totals']['received'] > 0 || $g['totals']['stocked'] > 0) {
        $hasData = true;
        break;
    }
}
?>
<div class="table-responsive">
    <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th scope="col" style="min-width:220px">ประเภทพัสดุ</th>
                <th scope="col" class="text-end">จำนวน</th>
                <th scope="col" class="text-end">ขอซื้อ</th>
                <th scope="col" class="text-end">ตรวจรับ</th>
                <th scope="col" class="text-end">เข้าคลัง</th>
                <th scope="col" class="text-end">ค้างเข้าคลัง</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!$hasData): ?>
                <tr><td colspan="6" class="text-center text-body-secondary py-4">ไม่มีความเคลื่อนไหวในเดือนนี้</td></tr>
            <?php endif; ?>
            <?php foreach ($groups as $k => $g):
                $t = $g['totals'];
                if ($t['ordered'] == 0 && $t['received'] == 0 && $t['stocked'] == 0) {
                    continue;
                }
                $collapseId = 'sub_' . $k;
                ?>
                <tr class="table-active fw-semibold" role="button" data-bs-toggle="collapse" data-bs-target=".<?= $collapseId ?>">
                    <td>
                        <i class="bi bi-caret-down-fill small me-1"></i>
                        <span class="d-inline-block rounded-circle me-1" style="width:10px;height:10px;background:var(<?= $g['cssvar'] ?>);"></span>
                        <?= Html::encode($g['label']) ?>
                    </td>
                    <td class="text-end"><?= number_format($t['cnt']) ?></td>
                    <td class="text-end"><?= number_format($t['ordered'], 0) ?></td>
                    <td class="text-end text-success-emphasis"><?= number_format($t['received'], 0) ?></td>
                    <td class="text-end"><?= number_format($t['stocked'], 0) ?></td>
                    <td class="text-end">
                        <?= $pendingCell((float) $t['pending'], ['cat' => $k, 'label' => $g['label']], true) ?>
                    </td>
                </tr>
                <?php foreach ($g['rows'] as $r): ?>
                    <tr class="collapse show <?= $collapseId ?>">
                        <td class="ps-4 fw-light"><?= Html::encode($r['subtype']) ?></td>
                        <td class="text-end fw-light"><?= number_format($r['cnt']) ?></td>
                        <td class="text-end fw-light"><?= number_format($r['ordered'], 0) ?></td>
                        <td class="text-end fw-light text-success-emphasis"><?= number_format($r['received'], 0) ?></td>
                        <td class="text-end fw-light"><?= number_format($r['stocked'], 0) ?></td>
                        <td class="text-end fw-light">
                            <?= $pendingCell((float) $r['pending'], ['subtype' => $r['subtype'], 'label' => $r['subtype']], false) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

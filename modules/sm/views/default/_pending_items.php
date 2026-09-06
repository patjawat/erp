<?php

/**
 * เนื้อหา modal drill-down — รายการใบที่ตรวจรับแล้วยังไม่เข้าคลัง
 *
 * @var array $data  ['items'=>[], 'count'=>int, 'totalValue'=>float]
 */

use yii\helpers\Html;

$items = $data['items'];

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
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
    <span class="text-body-secondary small">พบ <?= number_format($data['count']) ?> ใบ · รวม <?= number_format($data['totalValue'], 2) ?> บาท</span>
    <span class="small text-body-secondary"><i class="bi bi-info-circle me-1"></i>ควรบันทึกเข้าคลังให้ตรงเดือนที่ตรวจรับ</span>
</div>
<?php if (empty($items)): ?>
    <p class="text-success-emphasis mb-0"><i class="bi bi-check-circle me-1"></i>ไม่มีใบค้างเข้าคลังในกลุ่มนี้</p>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th scope="col">เลขที่ใบ</th>
                    <th scope="col">ประเภทพัสดุ</th>
                    <th scope="col">หน่วยงาน</th>
                    <th scope="col" class="text-end">มูลค่า</th>
                    <th scope="col">วันตรวจรับ</th>
                    <th scope="col" class="text-end">ค้างมาแล้ว</th>
                    <th scope="col" class="text-end">เปิดใบ</th>
                </tr>
            </thead>
            <tbody class="table-group-divider">
                <?php foreach ($items as $r): ?>
                    <tr>
                        <td class="fw-medium"><?= Html::encode($r['pr_number'] ?: '-') ?></td>
                        <td class="fw-light small"><?= Html::encode($r['otn'] ?: '-') ?></td>
                        <td class="fw-light small"><?= Html::encode($r['department'] ?: '-') ?></td>
                        <td class="text-end fw-medium"><?= number_format((float) $r['value'], 0) ?></td>
                        <td class="fw-light small text-nowrap"><?= $r['gr_date'] ? Html::encode($r['gr_date']) : '-' ?></td>
                        <td class="text-end"><?= $dayBadge((int) $r['days']) ?></td>
                        <td class="text-end">
                            <?= Html::a('<i class="bi bi-box-arrow-up-right"></i>', ['/purchase/order/view', 'id' => $r['id']], [
                                'class' => 'btn btn-sm btn-outline-primary',
                                'target' => '_blank',
                                'title' => 'เปิดใบ ' . ($r['pr_number'] ?: ''),
                                'data' => ['pjax' => 0],
                            ]) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

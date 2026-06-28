<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var array $grouped  warehouse_id => ['warehouse_name' => string, 'rows' => array] */
/** @var int $totalCount */
/** @var float $totalNegative */

$this->title = 'ยอดคงเหลือติดลบ — ตรวจสอบและปรับยอด';
$this->params['breadcrumbs'][] = ['label' => 'ปรับยอดคลัง', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ยอดติดลบ';
?>

<div class="card shadow-sm">
    <div class="card-header bg-warning bg-opacity-10 border-warning-subtle d-flex flex-wrap align-items-center gap-2">
        <h6 class="mb-0 fw-semibold text-warning-emphasis">
            <i class="bi bi-exclamation-triangle me-1"></i>
            ยอดคงเหลือที่ติดลบในระบบ
        </h6>
        <span class="badge bg-warning text-dark ms-2">
            พบ <?= number_format($totalCount) ?> รายการ
        </span>
        <?php if ($totalCount > 0): ?>
            <span class="badge bg-danger ms-1">
                รวม <?= number_format($totalNegative, 2) ?>
            </span>
        <?php endif; ?>
        <div class="ms-auto d-flex gap-2">
            <?= Html::a('<i class="bi bi-arrow-clockwise me-1"></i> รีเฟรช', ['negative-balance'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i> ฟอร์มปรับยอด', ['index'], ['class' => 'btn btn-sm btn-primary']) ?>
        </div>
    </div>
    <div class="card-body">
        <p class="text-muted small mb-0">
            รายการเหล่านี้มี <code>balance_qty &lt; 0</code> ในตาราง <code>stock_balance</code> —
            มักเกิดจากการย้ายใบจ่ายจาก V1 ที่ snapshot คงเหลือไม่ครบ หรือ FIFO lot ใน V1 ไม่ตรงกับ V2
            <br>วิธีเคลียร์: ตรวจกับยอดจริงในคลัง แล้วกด <strong>"ปรับยอด"</strong> เพื่อสร้างใบ ADJUST เพิ่มจำนวนให้กลับเป็น 0 หรือเป็นยอดจริง
        </p>
    </div>

    <?php if (empty($grouped)): ?>
        <div class="alert alert-success m-3 mb-4">
            <i class="bi bi-check-circle me-1"></i> ไม่พบยอดติดลบในระบบ — คลังสมบูรณ์
        </div>
    <?php else: ?>
        <?php foreach ($grouped as $wid => $g): ?>
            <div class="border-top">
                <div class="px-3 py-2 bg-light fw-semibold d-flex align-items-center gap-2">
                    <i class="bi bi-building me-1"></i>
                    <?= Html::encode($g['warehouse_name']) ?>
                    <span class="badge bg-secondary ms-1"><?= count($g['rows']) ?> รายการ</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 48px;">#</th>
                                <th>รหัสพัสดุ</th>
                                <th>ชื่อพัสดุ</th>
                                <th>Lot</th>
                                <th class="text-end">ยอดคงเหลือ</th>
                                <th>อัปเดตล่าสุด</th>
                                <th class="text-center" style="width: 140px;">ปรับยอด</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            <?php $n = 1; foreach ($g['rows'] as $r): ?>
                                <tr>
                                    <td class="text-muted"><?= $n++ ?></td>
                                    <td><code><?= Html::encode($r['item_code']) ?></code></td>
                                    <td><?= Html::encode($r['item_name'] ?: '-') ?></td>
                                    <td><code><?= Html::encode($r['lot_number'] ?: '-') ?></code></td>
                                    <td class="text-end text-danger fw-bold">
                                        <?= number_format((float) $r['balance_qty'], 2) ?>
                                    </td>
                                    <td class="text-muted small">
                                        <?= !empty($r['updated_at']) ? date('d/m/Y H:i', (int) $r['updated_at']) : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <?= Html::a(
                                            '<i class="bi bi-pencil-square me-1"></i> ปรับยอด',
                                            Url::to([
                                                'index',
                                                'warehouse_id' => (int) $r['warehouse_id'],
                                                'item_code' => $r['item_code'],
                                            ]),
                                            ['class' => 'btn btn-sm btn-outline-primary']
                                        ) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

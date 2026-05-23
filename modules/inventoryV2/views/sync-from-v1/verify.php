<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var array $rows */
/** @var string $dateFrom */
/** @var string $dateTo */
/** @var int|null $whId */
/** @var bool $onlyDiff */
/** @var array $warehouseOptions */

$this->title = 'Verify เทียบยอด V1 vs V2';
$this->params['breadcrumbs'][] = ['label' => 'Sync V1→V2', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$fmt = static function ($v) { return number_format((float)($v ?? 0), 2); };

// สรุปภาพรวม
$diffCount = 0;
$totalCount = count($rows);
foreach ($rows as $r) {
    if ($r['has_diff']) $diffCount++;
}
?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="text-white mb-0">
            <i class="fa-solid fa-check-double"></i> Verify เทียบยอด V1 vs V2
        </h6>
        <div>
            <span class="badge bg-light text-dark">ทั้งหมด <?= number_format($totalCount) ?> รายการ</span>
            <?php if ($diffCount > 0): ?>
                <span class="badge bg-danger">ไม่ตรง <?= number_format($diffCount) ?> รายการ</span>
            <?php else: ?>
                <span class="badge bg-success"><i class="fa-solid fa-check"></i> ตรงทั้งหมด</span>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <?= $this->render('_filter', [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'whId' => $whId,
            'warehouseOptions' => $warehouseOptions,
            'action' => 'verify',
        ]) ?>

        <div class="d-flex flex-wrap gap-2 mb-2 align-items-center">
            <?= Html::a('<i class="fa-solid fa-arrow-left"></i> กลับ',
                ['index', 'date_from' => $dateFrom, 'date_to' => $dateTo, 'warehouse_id' => $whId],
                ['class' => 'btn btn-outline-secondary btn-sm']) ?>

            <?= Html::a($onlyDiff ? 'แสดงทั้งหมด' : 'แสดงเฉพาะที่ไม่ตรง',
                ['verify',
                    'date_from' => $dateFrom, 'date_to' => $dateTo, 'warehouse_id' => $whId,
                    'only_diff' => $onlyDiff ? 0 : 1,
                ],
                ['class' => 'btn btn-outline-' . ($onlyDiff ? 'primary' : 'danger') . ' btn-sm']) ?>

            <span class="ms-auto small text-muted">
                ช่วง: <?= Html::encode($dateFrom) ?> ถึง <?= Html::encode($dateTo) ?>
                <?= $whId ? '| คลัง: ' . Html::encode($warehouseOptions[$whId] ?? '') : '| ทุกคลัง' ?>
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-sm align-middle">
                <thead class="table-light text-center">
                    <tr>
                        <th rowspan="2">#</th>
                        <th rowspan="2">รหัสสินค้า</th>
                        <th colspan="2">รับเข้า (qty)</th>
                        <th rowspan="2">Diff qty IN</th>
                        <th colspan="2">จ่ายออก (qty)</th>
                        <th rowspan="2">Diff qty OUT</th>
                        <th colspan="2">มูลค่า IN</th>
                        <th rowspan="2">Diff value IN</th>
                        <th colspan="2">มูลค่า OUT</th>
                        <th rowspan="2">Diff value OUT</th>
                    </tr>
                    <tr>
                        <th>V1</th><th>V2</th>
                        <th>V1</th><th>V2</th>
                        <th>V1</th><th>V2</th>
                        <th>V1</th><th>V2</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="14" class="text-center text-muted py-3">— ไม่พบข้อมูล —</td></tr>
                    <?php else: ?>
                        <?php $i = 1; foreach ($rows as $r): ?>
                            <tr class="<?= $r['has_diff'] ? 'table-danger' : '' ?>">
                                <td class="text-center"><?= $i++ ?></td>
                                <td>
                                    <?= Html::encode($r['item_code']) ?>
                                    <?php if ($r['has_diff']): ?>
                                        <i class="fa-solid fa-triangle-exclamation text-danger ms-1" title="ไม่ตรง"></i>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><?= $fmt($r['v1_in_qty']) ?></td>
                                <td class="text-end"><?= $fmt($r['v2_in_qty']) ?></td>
                                <td class="text-end fw-bold <?= abs($r['diff_in_qty']) > 0.001 ? 'text-danger' : 'text-success' ?>">
                                    <?= $fmt($r['diff_in_qty']) ?>
                                </td>
                                <td class="text-end"><?= $fmt($r['v1_out_qty']) ?></td>
                                <td class="text-end"><?= $fmt($r['v2_out_qty']) ?></td>
                                <td class="text-end fw-bold <?= abs($r['diff_out_qty']) > 0.001 ? 'text-danger' : 'text-success' ?>">
                                    <?= $fmt($r['diff_out_qty']) ?>
                                </td>
                                <td class="text-end"><?= $fmt($r['v1_in_value']) ?></td>
                                <td class="text-end"><?= $fmt($r['v2_in_value']) ?></td>
                                <td class="text-end fw-bold <?= abs($r['diff_in_value']) > 0.001 ? 'text-danger' : 'text-success' ?>">
                                    <?= $fmt($r['diff_in_value']) ?>
                                </td>
                                <td class="text-end"><?= $fmt($r['v1_out_value']) ?></td>
                                <td class="text-end"><?= $fmt($r['v2_out_value']) ?></td>
                                <td class="text-end fw-bold <?= abs($r['diff_out_value']) > 0.001 ? 'text-danger' : 'text-success' ?>">
                                    <?= $fmt($r['diff_out_value']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($diffCount > 0): ?>
        <div class="alert alert-warning mt-2 small">
            <i class="fa-solid fa-circle-info"></i>
            <strong>มีรายการไม่ตรง <?= $diffCount ?> รายการ</strong> —
            ลองกด "Run Sync" อีกครั้ง (หรือตรวจสอบว่ารายการที่ไม่ตรงมี
            <code>stock_item</code> รหัสนี้ใน V2 หรือไม่)
        </div>
        <?php endif; ?>
    </div>
</div>

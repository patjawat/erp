<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var app\modules\inventory\models\Stock $stock */
/** @var app\modules\inventoryV2\models\StockItem|null $v2Item */
/** @var array $rows */
/** @var array $stats */
/** @var array $warehouseMap */

$warehouseName = function ($id) use ($warehouseMap) {
    if (!$id) return '<span class="text-muted">-</span>';
    $w = $warehouseMap[(int) $id] ?? null;
    return $w ? Html::encode($w['warehouse_name']) : '<span class="text-danger">#' . (int) $id . ' (ไม่พบ)</span>';
};

$this->title = 'ย้ายประวัติวัสดุ → V2 (history-only)';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="history"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<?php if (Yii::$app->session->hasFlash('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= Yii::$app->session->getFlash('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-2">
            <div class="col-md-6">
                <div class="text-muted small">รหัสวัสดุ (V1)</div>
                <div class="fw-bold font-monospace"><?= Html::encode($stock->asset_item) ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">ชื่อ (V2 master)</div>
                <div class="fw-bold">
                    <?= $v2Item ? Html::encode($v2Item->item_name) : '<span class="text-danger">— ไม่พบใน V2 master —</span>' ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">คลัง V1 (warehouse_id)</div>
                <div><?= (int) $stock->warehouse_id ?></div>
            </div>
            <div class="col-md-6">
                <div class="text-muted small">ลิงก์ดูประวัติเดิม</div>
                <div>
                    <?= Html::a('<i class="fa-solid fa-up-right-from-square"></i> เปิด stock-card V1',
                        Url::to(['/inventory/stock/view-stock-card', 'id' => $stock->id]),
                        ['class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank']) ?>
                </div>
            </div>
            <?php if (!empty($stock->warehouse_id)): ?>
            <div class="col-12">
                <div class="alert alert-warning d-flex justify-content-between align-items-center flex-wrap gap-2 mb-0 mt-2">
                    <span class="small">
                        <i class="fa-solid fa-bolt me-1"></i>
                        <strong>Bulk:</strong> ย้ายประวัติ <em>ทุก asset_item</em> ใน warehouse_id =
                        <code><?= (int) $stock->warehouse_id ?></code> ในคราวเดียว
                        (idempotent · history-only · ใบซ้ำ skip อัตโนมัติ)
                    </span>
                    <?= Html::beginForm(['bulk-item-history-save'], 'post', ['class' => 'd-inline']) ?>
                        <input type="hidden" name="warehouse_id" value="<?= (int) $stock->warehouse_id ?>">
                        <?= Html::submitButton('<i class="fa-solid fa-clone me-1"></i> ย้ายทั้งคลังนี้', [
                            'class' => 'btn btn-warning btn-sm',
                            'data' => ['confirm' => 'ยืนยันย้ายประวัติทุกใบใน warehouse นี้เข้า V2 (history-only, idempotent)?'],
                        ]) ?>
                    <?= Html::endForm() ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2 mb-0">
            <i class="fa-solid fa-list me-1"></i>
            ประวัติใน V1
            <span class="badge bg-light text-dark ms-2">ทั้งหมด <?= count($rows) ?> ใบ</span>
            <span class="badge bg-success ms-1">ย้ายได้ <?= $stats['willMigrate'] ?></span>
            <?php if ($stats['duplicate'] > 0): ?>
                <span class="badge bg-info ms-1">ซ้ำใน V2 แล้ว <?= $stats['duplicate'] ?></span>
            <?php endif; ?>
            <?php if ($stats['noMatch'] > 0): ?>
                <span class="badge bg-warning text-dark ms-1">map ไม่ได้ <?= $stats['noMatch'] ?></span>
            <?php endif; ?>
            <?php if (!empty($stats['noWarehouse']) && $stats['noWarehouse'] > 0): ?>
                <span class="badge bg-danger ms-1">ไม่มี warehouse_id <?= $stats['noWarehouse'] ?></span>
            <?php endif; ?>
        </h6>
    </div>

    <?php if (empty($rows)): ?>
        <div class="alert alert-info m-3">
            <i class="fa-solid fa-info-circle me-1"></i> ไม่พบประวัติของวัสดุนี้ใน V1 (status=success)
        </div>
    <?php else: ?>
        <?= Html::beginForm(['item-history-save'], 'post', ['id' => 'migrate-form']) ?>
            <input type="hidden" name="stock_id" value="<?= (int) $stock->id ?>">

            <div class="card-body border-bottom bg-light">
                <span class="text-muted small">
                    <i class="fa-solid fa-shield-check me-1 text-success"></i>
                    <strong>Auto-map คลัง:</strong> ปลายทาง V2 = warehouse_id ของ V1 record ตรงๆ
                    (V1/V2 ใช้ตาราง <code>warehouses</code> เดียวกัน)
                    · history-only — ไม่กระทบ <code>stock_balance</code>
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 40px;">
                                <input type="checkbox" id="check-all" class="form-check-input">
                            </th>
                            <th style="width: 56px;">#</th>
                            <th>เลขที่ V1</th>
                            <th>ประเภท</th>
                            <th>วันที่</th>
                            <th>คลัง main (auto)</th>
                            <th>คลัง sub (auto)</th>
                            <th class="text-end">รายการ (map/total)</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                    <?php $n = 1; foreach ($rows as $r): ?>
                        <?php
                            $totalLines = count($r['lines']);
                            $matched = (int) $r['matched'];

                            $statusBadge = '';
                            $rowClass = '';
                            if ($r['is_duplicate']) {
                                $statusBadge = '<span class="badge bg-info">ซ้ำใน V2 แล้ว</span>';
                                $rowClass = 'table-secondary text-muted';
                            } elseif (!$r['has_warehouse']) {
                                $statusBadge = '<span class="badge bg-danger">ไม่พบ warehouse_id ใน V2</span>';
                                $rowClass = 'table-secondary text-muted';
                            } elseif (!$r['is_transferable']) {
                                $statusBadge = '<span class="badge bg-warning text-dark">map item ไม่ได้</span>';
                                $rowClass = 'table-secondary text-muted';
                            } elseif ($matched < $totalLines) {
                                $statusBadge = '<span class="badge bg-success">พร้อมย้าย</span> '
                                    . '<span class="badge bg-warning text-dark">บาง line skip</span>';
                            } else {
                                $statusBadge = '<span class="badge bg-success">พร้อมย้าย</span>';
                            }
                            $typeBadge = $r['transaction_type'] === 'IN'
                                ? '<span class="badge bg-primary">IN รับเข้า</span>'
                                : '<span class="badge bg-danger">OUT จ่ายออก</span>';

                            $linesId = 'lines-' . $r['id'];
                            $matchBadgeClass = ($matched === $totalLines)
                                ? 'bg-success'
                                : ($matched > 0 ? 'bg-warning text-dark' : 'bg-danger');
                        ?>
                        <tr class="<?= $rowClass ?>">
                            <td class="text-center">
                                <?php if ($r['is_transferable']): ?>
                                    <input type="checkbox" name="order_ids[]" value="<?= $r['id'] ?>"
                                           class="form-check-input row-check" checked>
                                <?php else: ?>
                                    <input type="checkbox" disabled class="form-check-input">
                                <?php endif; ?>
                            </td>
                            <td><?= $n++ ?></td>
                            <td>
                                <code><?= Html::encode($r['code']) ?></code>
                                <?php if ($totalLines > 0): ?>
                                <button type="button" class="btn btn-link btn-sm p-0 ms-1"
                                        data-bs-toggle="collapse" data-bs-target="#<?= $linesId ?>"
                                        aria-expanded="false" title="ดูทุก line">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                            <td><?= $typeBadge ?></td>
                            <td><?= Html::encode($r['movement_date'] ?: '-') ?></td>
                            <td class="small"><?= $warehouseName($r['main_warehouse_id']) ?></td>
                            <td class="small"><?= $warehouseName($r['sub_warehouse_id']) ?></td>
                            <td class="text-end">
                                <span class="badge <?= $matchBadgeClass ?>">
                                    <?= $matched ?>/<?= $totalLines ?>
                                </span>
                                <?php if (!empty($r['skipped_codes'])): ?>
                                    <div class="small text-warning mt-1" style="max-width:180px;">
                                        skip: <?= Html::encode(implode(', ', $r['skipped_codes'])) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= $statusBadge ?></td>
                        </tr>
                        <?php if ($totalLines > 0): ?>
                        <tr class="collapse" id="<?= $linesId ?>">
                            <td colspan="9" class="p-0">
                                <div class="px-3 py-2 bg-light border-top">
                                    <table class="table table-sm mb-0 align-middle">
                                        <thead>
                                            <tr class="small text-muted">
                                                <th style="width: 40px;">#</th>
                                                <th>item_code</th>
                                                <th class="text-end">qty</th>
                                                <th class="text-end">unit_price</th>
                                                <th>lot</th>
                                                <th>map V2?</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($r['lines'] as $li => $l): ?>
                                                <?php
                                                    $itemCode = trim((string) ($l['asset_item'] ?? ''));
                                                    $isMapped = isset($r['skipped_codes']) && !in_array($itemCode, $r['skipped_codes'], true);
                                                ?>
                                                <tr>
                                                    <td class="text-muted small"><?= $li + 1 ?></td>
                                                    <td><code class="small"><?= Html::encode($itemCode ?: '-') ?></code></td>
                                                    <td class="text-end small"><?= number_format((float) $l['qty'], 2) ?></td>
                                                    <td class="text-end small">
                                                        <?= $l['unit_price'] !== null ? number_format((float) $l['unit_price'], 2) : '-' ?>
                                                    </td>
                                                    <td class="small"><code><?= Html::encode($l['lot_number'] ?: '-') ?></code></td>
                                                    <td>
                                                        <?= $isMapped
                                                            ? '<span class="badge bg-success">OK</span>'
                                                            : '<span class="badge bg-warning text-dark">missing</span>' ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($stats['willMigrate'] > 0): ?>
            <div class="card-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">
                    <i class="fa-solid fa-shield-check me-1 text-success"></i>
                    ย้ายทั้งใบ (ทุก line) · order_no preserved · auto-map คลัง · บาง line ที่ item ไม่อยู่ใน V2 master จะถูก skip
                </span>
                <?= Html::submitButton('<i class="fa-solid fa-clone me-1"></i> ย้ายใบที่เลือก → V2', [
                    'class' => 'btn btn-primary btn-sm',
                    'data' => ['confirm' => 'ยืนยันย้ายประวัติของวัสดุนี้เข้า V2 (history-only, auto-map คลัง)?'],
                ]) ?>
            </div>
            <?php endif; ?>

        <?= Html::endForm() ?>
    <?php endif; ?>
</div>

<?php
$js = <<<JS
document.getElementById('check-all')?.addEventListener('change', function (e) {
    document.querySelectorAll('.row-check:not(:disabled)').forEach(function (cb) {
        cb.checked = e.target.checked;
    });
});
JS;
$this->registerJs($js);

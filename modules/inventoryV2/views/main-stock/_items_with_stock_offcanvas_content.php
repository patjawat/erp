<?php
use yii\helpers\Html;
use yii\helpers\Url;

$warehouseIdParam = $warehouseIdParam ?? 'all';
$q = $q ?? '';
$exportUrl = $exportUrl ?? '#';

$exportParams = [
    'warehouse_id' => $warehouseIdParam,
];
if ($q !== '') {
    $exportParams['q'] = $q;
}
$exportUrl = Url::to(['/inventory-v2/main-stock/export-items-with-stock-excel'] + $exportParams);
?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 fw-normal text-muted">
            <i class="bi bi-box-seam me-1"></i>รายการพัสดุที่มีสต๊อก
            <span class="badge text-bg-primary ms-2"><?= (int)$totalCount ?> รายการ</span>
        </h6>
        <?php if (!empty($currentWarehouseName)): ?>
            <span class="badge text-bg-light text-dark">
                <i class="bi bi-geo-alt me-1"></i><?= Html::encode($currentWarehouseName) ?>
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="p-3 border-bottom">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-lg">
                    <label class="form-label small text-muted mb-1" for="itemsWithStockSearchQ">ค้นหาวัสดุ</label>
                    <input
                        type="text"
                        class="form-control form-control-sm"
                        id="itemsWithStockSearchQ"
                        value="<?= Html::encode($q) ?>"
                        placeholder="ค้นหาด้วยรหัส/ชื่อวัสดุ"
                    />
                </div>
                <div class="col-12 col-lg-auto d-flex gap-2 justify-content-lg-end">
                    <?= Html::a('<i class="bi bi-file-earmark-excel me-1"></i> Export Excel', $exportUrl ?? '#', ['class' => 'btn btn-success btn-sm', 'target' => '_blank']) ?>
                    <button type="button" class="btn btn-primary btn-sm items-with-stock-search-btn">
                        <i class="bi bi-search me-1"></i>ค้นหา
                    </button>
                </div>
            </div>
        </div>

        <?php if (empty($items)): ?>
            <div class="p-4 text-center text-muted">
                <i class="bi bi-inbox fs-2"></i>
                <div class="mt-2 small">ไม่พบพัสดุที่มียอดคงเหลือ &gt; 0</div>
            </div>
        <?php else: ?>
                <div style="max-height: 70vh; overflow: auto;" class="p-3">
                    <div class="row g-3">
                        <?php foreach ($items as $r): ?>
                            <div class="col-12">
                                <div class="card shadow-sm border-0 bg-white">
                                    <div class="card-body p-3 d-flex align-items-center gap-3">
                                        <div class="flex-shrink-0 text-center" style="width:56px;">
                                            <?php if (!empty($r['img'])): ?>
                                                <?= Html::img($r['img'], ['class' => 'rounded', 'style' => 'width:48px;height:48px;object-fit:cover;']) ?>
                                            <?php else: ?>
                                                <i class="bi bi-image text-muted" style="font-size:22px;"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div class="min-w-0 flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start gap-2">
                                                <div class="min-w-0">
                                                    <div class="fw-bold" style="word-break: break-word; overflow-wrap: anywhere;" title="<?= Html::encode($r['item_code']) ?>">
                                                        <?= Html::encode($r['item_code']) ?>
                                                    </div>
                                                    <div class="text-muted small text-truncate"><?= Html::encode($r['item_name']) ?></div>
                                                </div>
                                                <div class="text-end">
                                                    <div class="fw-semibold text-dark">
                                                        <?= number_format((float)$r['total_qty'], 2) ?>
                                                    </div>
                                                    <div class="text-muted small"><?= Html::encode($r['unit_name'] ?? '-') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php if ($totalCount > count($items)): ?>
                <div class="p-3 text-muted small text-center border-top">
                    แสดงเฉพาะบางส่วน (20 รายการ) — ดูทั้งหมดได้ที่หน้า `items-with-stock`
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>


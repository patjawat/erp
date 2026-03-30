<?php
use yii\web\View;
use yii\helpers\Html;
use yii\helpers\Url;

$this->registerJs(<<<JS
    document.addEventListener('change', function (e) {
        var t = e.target;
        if (!t || t.id !== 'warehouseFilter') return;
        var form = t.closest('form');
        if (form) form.submit();
    });
JS);
?>
<?php

$this->title = 'รายการพัสดุที่มีสต๊อก';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'Dashboard คลังหลัก', 'url' => ['/inventory-v2/main-stock/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$items = $items ?? [];
$pagination = $pagination ?? null;
$totalCount = $totalCount ?? count($items);
$warehouses = $warehouses ?? [];
$currentWarehouseId = $currentWarehouseId ?? null;

$currentWarehouseName = 'ทั้งหมด';
if ($currentWarehouseId && !empty($warehouses)) {
    foreach ($warehouses as $w) {
        if ((int)$w->id === (int)$currentWarehouseId) {
            $currentWarehouseName = $w->warehouse_name;
            break;
        }
    }
}
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-box-seam"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0">แสดงพัสดุที่มียอดคงเหลือ &gt; 0 ในคลัง: <?= Html::encode($currentWarehouseName) ?></p>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid px-3 px-md-4">
    <div class="row g-3 align-items-center justify-content-between">
        <div class="col-12 col-lg-auto">
            <form method="get" action="<?= Url::to(['/inventory-v2/main-stock/items-with-stock']) ?>" class="d-inline">
                <label for="warehouseFilter" class="form-label visually-hidden">เลือกคลัง</label>
                <select name="warehouse_id" class="form-select form-select-sm border shadow-sm rounded-pill px-3" id="warehouseFilter" style="min-width: 180px;">
                    <option value="all" <?= $currentWarehouseId === null ? 'selected' : '' ?>>แสดงคลังทั้งหมด</option>
                    <?php foreach ($warehouses as $w): ?>
                        <option value="<?= (int)$w->id ?>" <?= (int)$w->id === (int)$currentWarehouseId ? 'selected' : '' ?>>
                            <?= Html::encode($w->warehouse_name) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <div class="col-12 col-lg-auto">
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['/inventory-v2/main-stock/dashboard'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
        </div>
    </div>
</div>

<div class="container-fluid py-4 px-3 px-md-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="mb-0 fw-normal text-muted">
                <i class="bi bi-list-ul me-1"></i> จำนวนพัสดุ
                <span class="badge text-bg-primary ms-2"><?= (int)$totalCount ?> รายการ</span>
            </h6>
        </div>
        <div class="card-body p-0">
            <?php if (empty($items)): ?>
                <div class="p-4 text-center text-muted">
                    <i class="bi bi-inbox fs-2"></i>
                    <div class="mt-2 small">ไม่พบพัสดุที่มียอดคงเหลือ &gt; 0 ตามตัวกรอง</div>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap">รหัส</th>
                                <th>ชื่อพัสดุ</th>
                                <th class="text-nowrap text-end">ยอดคงเหลือ</th>
                                <th class="text-nowrap">หน่วย</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $r): ?>
                                <tr>
                                    <td class="text-nowrap fw-bold"><?= Html::encode($r['item_code']) ?></td>
                                    <td><?= Html::encode($r['item_name']) ?></td>
                                    <td class="text-end fw-semibold"><?= number_format((float)$r['total_qty'], 2) ?></td>
                                    <td class="text-muted text-nowrap small"><?= Html::encode($r['unit_name'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($pagination && $pagination->getPageCount() > 1): ?>
                    <div class="p-3">
                        <?= \yii\widgets\LinkPager::widget([
                            'pagination' => $pagination,
                            'maxButtonCount' => 5,
                            'params' => [
                                'warehouse_id' => $currentWarehouseId !== null ? (int)$currentWarehouseId : 'all',
                            ],
                        ]) ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

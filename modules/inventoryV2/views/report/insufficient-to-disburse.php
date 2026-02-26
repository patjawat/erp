<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายงานวัสดุไม่พอจ่าย';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$mainWarehouseId = $mainWarehouseId ?? null;
$subWarehouseId = $subWarehouseId ?? null;
$categoryId = $categoryId ?? null;
$rows = $rows ?? [];
$mainWarehouses = $mainWarehouses ?? ['' => '-- ทุกคลังหลัก --'];
$subWarehouses = $subWarehouses ?? ['' => '-- ทุกแผนก/ฝ่ายที่ขอเบิก --'];
$categories = $categories ?? ['' => '-- ทุกประเภท --'];
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0 small">จากรายการที่ขอเบิก (ใบรออนุมัติ + อนุมัติแล้ว) เทียบยอดคงเหลือในคลังหลัก — รวมกรณียังไม่เคยรับเข้าคลัง เพื่อออกใบสั่งซื้อ</p>
</div>
<?php $this->endBlock(); ?>

<?php
$exportUrl = Url::to(array_merge(['/inventory-v2/report/export-insufficient-to-disburse'], array_filter([
    'main_warehouse_id' => $mainWarehouseId !== null && $mainWarehouseId !== '' ? (int)$mainWarehouseId : null,
    'sub_warehouse_id' => $subWarehouseId !== null && $subWarehouseId !== '' ? (int)$subWarehouseId : null,
])));
?>
<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> ย้อนกลับ', ['/inventory-v2/default/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    <?= Html::a('<i class="bi bi-house me-1"></i> Dashboard คลังหลัก', ['/inventory-v2/main-stock/dashboard'], ['class' => 'btn btn-outline-primary btn-sm']) ?>
    <?= Html::a('<i class="bi bi-file-earmark-excel me-1"></i> Export Excel', $exportUrl, ['class' => 'btn btn-success btn-sm']) ?>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4">
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light py-2 px-3">
            <h6 class="mb-0 text-muted fw-normal"><i class="bi bi-funnel me-1"></i> กรอง</h6>
        </div>
        <div class="card-body py-3">
            <form method="get" action="<?= Url::to(['/inventory-v2/report/insufficient-to-disburse']) ?>" class="row g-3 align-items-end">
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label small text-muted mb-1">คลังหลักที่รอจ่าย</label>
                    <select name="main_warehouse_id" class="form-select form-select-sm">
                        <?php foreach ($mainWarehouses as $k => $v): ?>
                            <option value="<?= $k === '' ? '' : (int)$k ?>" <?= (string)$mainWarehouseId === (string)$k ? 'selected' : '' ?>><?= Html::encode($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label small text-muted mb-1">คลังย่อยที่ขอเบิก</label>
                    <select name="sub_warehouse_id" class="form-select form-select-sm">
                        <?php foreach ($subWarehouses as $k => $v): ?>
                            <option value="<?= $k === '' ? '' : (int)$k ?>" <?= (string)$subWarehouseId === (string)$k ? 'selected' : '' ?>><?= Html::encode($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label small text-muted mb-1">ประเภทวัสดุ</label>
                    <select name="category_id" class="form-select form-select-sm">
                        <?php foreach ($categories as $k => $v): ?>
                            <option value="<?= Html::encode($k) ?>" <?= (string)$categoryId === (string)$k ? 'selected' : '' ?>><?= Html::encode($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="bi bi-search me-1"></i> แสดง
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if (empty($rows)): ?>
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>
            ไม่มีรายการวัสดุไม่พอจ่ายตามตัวกรองที่เลือก หรือยังไม่มีใบขอเบิก (รออนุมัติ/อนุมัติแล้ว)
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-warning bg-opacity-10 py-2 px-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-normal">รายการวัสดุไม่พอจ่าย <span class="badge bg-warning text-dark"><?= count($rows) ?> รายการ</span></h6>
                <?= Html::a('<i class="bi bi-file-earmark-excel me-1"></i> Export Excel', $exportUrl, ['class' => 'btn btn-success btn-sm']) ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center" style="width: 4rem;">ลำดับ</th>
                                <th class="text-nowrap">คลังหลักที่รอจ่าย</th>
                                <th class="text-nowrap">รหัส</th>
                                <th>ชื่อวัสดุ</th>
                                <th class="text-nowrap text-center">ประเภท</th>
                                <th class="text-nowrap text-center">หน่วย</th>
                                <th class="text-end">จำนวนที่ขอเบิก</th>
                                <th class="text-end">ยอดคงเหลือในคลัง</th>
                                <th class="text-end fw-bold text-danger">ต้องซื้อเพิ่ม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $i => $r): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $i + 1 ?></td>
                                    <td class="text-nowrap"><?= Html::encode($r['main_warehouse_name']) ?></td>
                                    <td class="text-nowrap fw-bold"><?= Html::encode($r['item_code']) ?></td>
                                    <td><?= Html::encode($r['item_name']) ?></td>
                                    <td class="text-center text-muted small"><?= Html::encode($r['category_title']) ?></td>
                                    <td class="text-center text-muted small"><?= Html::encode($r['unit_name']) ?></td>
                                    <td class="text-end"><?= number_format($r['requested_qty'], 2) ?></td>
                                    <td class="text-end"><?= number_format($r['balance_qty'], 2) ?></td>
                                    <td class="text-end fw-bold text-danger"><?= number_format($r['shortfall'], 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

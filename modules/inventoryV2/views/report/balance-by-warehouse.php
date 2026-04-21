<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'สรุปยอดคงเหลือตามคลัง';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-boxes fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">คลังหลักดูสถานะวัสดุคงเหลือของคลังย่อยได้ — คลังย่อยดูสถานะของตัวเองได้ เลือกคลังด้านล่าง (หลักหรือย่อย)</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap justify-content-end align-items-center gap-2">
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['/inventory-v2/sub-stock/dashboard'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
    <form method="get" action="<?= Url::to(['/inventory-v2/report/balance-by-warehouse']) ?>" class="d-flex align-items-center gap-2">
        <select name="warehouse_id" class="form-select border shadow-sm" style="width: 240px; height: 38px;">
            <?php foreach ($warehouses as $wid => $wname): ?>
                <option value="<?= $wid === '' ? '' : (int)$wid ?>" <?= (string)$warehouseId === (string)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary px-4" style="height: 38px;">
            <i class="bi bi-search me-1"></i> แสดง
        </button>
        <?= Html::a('<i class="bi bi-file-earmark-excel me-1"></i> Excel', array_merge(['/inventory-v2/report/export-balance-by-warehouse'], $warehouseId !== null && $warehouseId !== '' ? ['warehouse_id' => $warehouseId] : []), ['class' => 'btn btn-success', 'style' => 'height: 38px;']) ?>
    </form>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4">

    <?php if (empty($rows)): ?>
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>
            ไม่มียอดคงเหลือในคลังที่เลือก หรือยังไม่มีข้อมูลรับเข้าในคลังหลัก
        </div>
    <?php else: ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <p class="text-muted small mb-1 fw-bold">มูลค่ารวม (บาท)</p>
                        <p class="fs-4 fw-bold text-primary mb-0"><?= number_format($summary['total_value'], 2) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <p class="text-muted small mb-1 fw-bold">จำนวนรายการวัสดุ</p>
                        <p class="fs-4 fw-bold mb-0"><?= number_format($summary['items_count']) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100 border-danger border-opacity-25">
                    <div class="card-body py-3">
                        <p class="text-muted small mb-1 fw-bold">ต่ำกว่าจุดสั่งซื้อขั้นต่ำ (Min)</p>
                        <p class="fs-4 fw-bold text-danger mb-0"><?= number_format($summary['below_min_count']) ?> รายการ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100 border-warning border-opacity-25">
                    <div class="card-body py-3">
                        <p class="text-muted small mb-1 fw-bold">ต่ำกว่าจุดสั่งซื้อขั้นสูง (Max)</p>
                        <p class="fs-4 fw-bold text-warning mb-0"><?= number_format($summary['below_max_count']) ?> รายการ</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-secondary bg-opacity-10 py-2 px-3">
                <h6 class="mb-0 fw-normal">รายการวัสดุคงเหลือตามคลัง</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap text-center" style="width: 4rem;">ลำดับ</th>
                                <th class="text-nowrap">คลัง</th>
                                <th class="text-nowrap">รหัส</th>
                                <th>ชื่อวัสดุ</th>
                                <th class="text-nowrap text-center">ประเภท</th>
                                <th class="text-nowrap text-center">หน่วย</th>
                                <th class="text-end fw-bold">จำนวนคงเหลือ</th>
                                <th class="text-end fw-bold">มูลค่า (บาท)</th>
                                <th class="text-end">Min</th>
                                <th class="text-end">Max</th>
                                <th class="text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $i => $r): ?>
                                <tr class="<?= $r['below_min'] ? 'table-danger' : ($r['below_max'] ? 'table-warning' : '') ?>">
                                    <td class="text-center text-muted"><?= $i + 1 ?></td>
                                    <td class="text-nowrap"><?= Html::encode($r['warehouse_name']) ?></td>
                                    <td class="text-nowrap"><?= Html::encode($r['item_code']) ?></td>
                                    <td><?= Html::encode($r['item_name']) ?></td>
                                    <td class="text-center text-muted small"><?= Html::encode($r['category_title']) ?></td>
                                    <td class="text-center text-muted small"><?= Html::encode($r['unit_name']) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($r['balance_qty'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($r['value'], 2) ?></td>
                                    <td class="text-end"><?= $r['min_qty'] !== null ? number_format($r['min_qty'], 2) : '-' ?></td>
                                    <td class="text-end"><?= $r['max_qty'] !== null ? number_format($r['max_qty'], 2) : '-' ?></td>
                                    <td class="text-center">
                                        <?php if ($r['below_min']): ?>
                                            <span class="badge text-bg-danger">ต่ำกว่า Min</span>
                                        <?php elseif ($r['below_max']): ?>
                                            <span class="badge text-bg-warning text-dark">ต่ำกว่า Max</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-success">พอดี</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

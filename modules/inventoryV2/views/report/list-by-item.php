<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายงานวัสดุคงคลังหลักรายตัว';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-clipboard-data fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0 small">ยอด ยกมา / รับเข้า / จ่ายออก / คงเหลือ ของแต่ละรายการ — รวมข้อมูล V1 + V2 ต่อเนื่อง</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/menu', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="get" action="<?= Url::to(['/inventory-v2/report/list-by-item']) ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">วันที่เริ่ม</label>
                        <input type="date" name="date_start" class="form-control form-control-sm" value="<?= Html::encode($dateStart) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">วันที่สิ้นสุด</label>
                        <input type="date" name="date_end" class="form-control form-control-sm" value="<?= Html::encode($dateEnd) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">ประเภทวัสดุ</label>
                        <select name="asset_type_id" class="form-select form-select-sm">
                            <option value="">-- ทุกประเภท --</option>
                            <?php foreach ($assetTypeOptions as $code => $label): ?>
                                <option value="<?= Html::encode($code) ?>" <?= (string)$assetType === (string)$code ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">คลังหลัก</label>
                        <select name="warehouse_id" class="form-select form-select-sm">
                            <?php foreach ($warehouseOptions as $wid => $wname): ?>
                                <option value="<?= $wid === '' ? '' : (int)$wid ?>" <?= (string)$warehouseId === (string)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-search"></i> ค้นหา</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-normal">
                <i class="bi bi-list-ul me-1"></i> รายการทั้งหมด
                <span class="badge text-bg-secondary ms-1"><?= number_format(count($rows)) ?></span>
            </h6>
            <small class="text-muted">ช่วงเวลา <?= Html::encode($dateStart) ?> — <?= Html::encode($dateEnd) ?></small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh;">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light text-center" style="position: sticky; top: 0; z-index: 5;">
                        <tr>
                            <th rowspan="2" style="width:40px;">#</th>
                            <th rowspan="2" style="width:110px;">รหัส</th>
                            <th rowspan="2">ชื่อวัสดุ</th>
                            <th rowspan="2" style="width:160px;">ประเภท</th>
                            <th rowspan="2" style="width:60px;">หน่วย</th>
                            <th colspan="2" class="bg-info bg-opacity-25">ยอดยกมา</th>
                            <th colspan="2" class="bg-success bg-opacity-25">รับเข้า</th>
                            <th colspan="2" class="bg-warning bg-opacity-25">จ่ายออก</th>
                            <th colspan="2" class="bg-primary bg-opacity-25">คงเหลือ</th>
                        </tr>
                        <tr>
                            <th class="bg-info bg-opacity-10">จำนวน</th>
                            <th class="bg-info bg-opacity-10">มูลค่า</th>
                            <th class="bg-success bg-opacity-10">จำนวน</th>
                            <th class="bg-success bg-opacity-10">มูลค่า</th>
                            <th class="bg-warning bg-opacity-10">จำนวน</th>
                            <th class="bg-warning bg-opacity-10">มูลค่า</th>
                            <th class="bg-primary bg-opacity-10">จำนวน</th>
                            <th class="bg-primary bg-opacity-10">มูลค่า</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="13" class="text-center text-muted py-4">ไม่พบรายการในช่วงเวลาที่เลือก</td></tr>
                    <?php else: foreach ($rows as $i => $r): ?>
                        <tr>
                            <td class="text-center text-muted"><?= $i + 1 ?></td>
                            <td class="text-muted small font-monospace"><?= Html::encode($r['item_code']) ?></td>
                            <td><?= Html::encode($r['asset_name']) ?></td>
                            <td class="small"><?= Html::encode($r['asset_type_name'] ?: '—') ?></td>
                            <td class="text-center small text-muted"><?= Html::encode($r['unit_name'] ?: '—') ?></td>
                            <td class="text-end"><?= number_format($r['begin_qty'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['begin_price'], 2) ?></td>
                            <td class="text-end text-success fw-semibold"><?= number_format($r['qty_in'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['price_in'], 2) ?></td>
                            <td class="text-end text-warning-emphasis fw-semibold"><?= number_format($r['qty_out'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['price_out'], 2) ?></td>
                            <td class="text-end fw-bold"><?= number_format($r['end_qty'], 2) ?></td>
                            <td class="text-end fw-bold text-primary"><?= number_format($r['end_price'], 2) ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot class="table-warning" style="position: sticky; bottom: 0;">
                        <tr class="fw-bold">
                            <td colspan="5" class="text-center">รวมทั้งหมด</td>
                            <td class="text-end"><?= number_format($summary['begin_qty'], 2) ?></td>
                            <td class="text-end"><?= number_format($summary['begin_price'], 2) ?></td>
                            <td class="text-end text-success"><?= number_format($summary['qty_in'], 2) ?></td>
                            <td class="text-end"><?= number_format($summary['price_in'], 2) ?></td>
                            <td class="text-end text-warning-emphasis"><?= number_format($summary['qty_out'], 2) ?></td>
                            <td class="text-end"><?= number_format($summary['price_out'], 2) ?></td>
                            <td class="text-end"><?= number_format($summary['end_qty'], 2) ?></td>
                            <td class="text-end text-primary"><?= number_format($summary['end_price'], 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

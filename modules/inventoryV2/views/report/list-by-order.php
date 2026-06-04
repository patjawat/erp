<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'รายงานวัสดุรับ-จ่าย';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$thaiDate = static function ($iso) {
    if (!$iso) return '—';
    $ts = strtotime($iso);
    if (!$ts) return Html::encode($iso);
    $months = ['', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
    return (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . (date('Y', $ts) + 543);
};
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-arrow-left-right fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0 small">รายละเอียดบรรทัดต่อบรรทัดทุกใบรับ-ใบจ่าย (รวม V1 + V2)</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/menu', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-3">
            <form method="get" action="<?= Url::to(['/inventory-v2/report/list-by-order']) ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">วันที่เริ่ม</label>
                        <input type="date" name="date_start" class="form-control form-control-sm" value="<?= Html::encode($dateStart) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">วันที่สิ้นสุด</label>
                        <input type="date" name="date_end" class="form-control form-control-sm" value="<?= Html::encode($dateEnd) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">ประเภทธุรกรรม</label>
                        <select name="transaction_type" class="form-select form-select-sm">
                            <option value="">ทุกประเภท</option>
                            <option value="IN"  <?= $transactionType === 'IN' ? 'selected' : '' ?>>รับเข้า</option>
                            <option value="OUT" <?= $transactionType === 'OUT' ? 'selected' : '' ?>>จ่ายออก</option>
                        </select>
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
                    <div class="col-md-2">
                        <label class="form-label small text-muted mb-1">คลังหลัก</label>
                        <select name="warehouse_id" class="form-select form-select-sm">
                            <?php foreach ($warehouseOptions as $wid => $wname): ?>
                                <option value="<?= $wid === '' ? '' : (int)$wid ?>" <?= (string)$warehouseId === (string)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex">
                        <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i></button>
                    </div>
                </div>
                <div class="mt-2">
                    <input type="text" name="asset_item" class="form-control form-control-sm" placeholder="รหัสวัสดุเฉพาะ (ถ้าระบุ)" value="<?= Html::encode($assetItem ?? '') ?>" style="max-width:300px;">
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-2 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-normal">
                <i class="bi bi-list-task me-1"></i> รายการ
                <span class="badge text-bg-secondary ms-1"><?= number_format(count($rows)) ?></span>
            </h6>
            <small class="text-muted">มูลค่ารวม: <strong class="text-primary"><?= number_format($totalPrice, 2) ?></strong> บาท</small>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 70vh;">
                <table class="table table-sm table-hover align-middle mb-0">
                    <thead class="table-light text-center" style="position: sticky; top: 0; z-index: 5;">
                        <tr>
                            <th style="width:40px;">#</th>
                            <th style="width:90px;">วันที่</th>
                            <th style="width:120px;">เลขที่</th>
                            <th style="width:70px;">รับ/จ่าย</th>
                            <th style="width:140px;">คลังหลัก</th>
                            <th style="width:180px;">ผู้ขาย / คลังย่อย</th>
                            <th style="width:100px;">รหัส</th>
                            <th>ชื่อวัสดุ</th>
                            <th style="width:130px;">ประเภท</th>
                            <th style="width:60px;">หน่วย</th>
                            <th style="width:80px;" class="text-end">จำนวน</th>
                            <th style="width:90px;" class="text-end">ราคา/หน่วย</th>
                            <th style="width:110px;" class="text-end">รวม</th>
                            <th style="width:60px;">ที่มา</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="14" class="text-center text-muted py-4">ไม่พบรายการ</td></tr>
                    <?php else: foreach ($rows as $i => $r): ?>
                        <tr>
                            <td class="text-center text-muted"><?= $i + 1 ?></td>
                            <td class="small"><?= Html::encode($thaiDate($r['movement_date'])) ?></td>
                            <td class="font-monospace small"><?= Html::encode($r['order_no']) ?></td>
                            <td class="text-center">
                                <?php if ($r['order_type'] === 'IN'): ?>
                                    <span class="badge text-bg-success bg-opacity-75">รับเข้า</span>
                                <?php else: ?>
                                    <span class="badge text-bg-warning text-dark">จ่ายออก</span>
                                <?php endif; ?>
                            </td>
                            <td class="small"><?= Html::encode($r['warehouse_name'] ?: '—') ?></td>
                            <td class="small"><?= Html::encode($r['counterparty_name'] ?: '—') ?></td>
                            <td class="font-monospace small"><?= Html::encode($r['item_code']) ?></td>
                            <td><?= Html::encode($r['item_name']) ?></td>
                            <td class="small"><?= Html::encode($r['asset_type_name'] ?: '—') ?></td>
                            <td class="text-center small text-muted"><?= Html::encode($r['unit_name'] ?: '—') ?></td>
                            <td class="text-end"><?= number_format($r['qty'], 2) ?></td>
                            <td class="text-end"><?= number_format($r['unit_price'], 2) ?></td>
                            <td class="text-end fw-semibold"><?= number_format($r['total_price'], 2) ?></td>
                            <td class="text-center">
                                <span class="badge text-bg-light text-muted small"><?= Html::encode($r['source']) ?></span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                    <tfoot class="table-warning" style="position: sticky; bottom: 0;">
                        <tr class="fw-bold">
                            <td colspan="12" class="text-end">รวมราคาทั้งหมด</td>
                            <td class="text-end text-primary"><?= number_format($totalPrice, 2) ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

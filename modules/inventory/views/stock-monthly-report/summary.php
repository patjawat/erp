<?php

use yii\helpers\Html;
use yii\helpers\Url;
use kartik\select2\Select2;
use app\modules\inventory\models\StockMonthlyReport;

/** @var yii\web\View $this */
/** @var array $querys */
/** @var array $sum */
/** @var int $reportYear */
/** @var int $reportMonth */
/** @var int|null $warehouseId */
/** @var array $yearOptions */
/** @var array $monthOptions */
/** @var array $warehouseOptions */

$monthLabel = StockMonthlyReport::thaiMonthName($reportMonth) . ' ' . ($reportYear + 543);
$this->title = 'สรุปปิดยอดเดือน (แยกประเภทวัสดุ) — ' . $monthLabel;
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$fmt5 = static function ($v) {
    return number_format((float) ($v ?? 0), 2);
};
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="bar-chart-3"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2 mb-0"><i class="fa-solid fa-magnifying-glass"></i> เลือกช่วงที่ต้องการสรุป</h6>
    </div>
    <div class="card-body">
        <form method="get" class="row g-2 align-items-end">
            <div class="col-12 col-md-3">
                <label class="form-label">ปี (ค.ศ.)</label>
                <?= Select2::widget([
                    'name' => 'report_year',
                    'value' => $reportYear,
                    'data' => $yearOptions,
                    'options' => ['placeholder' => 'ปี'],
                ]) ?>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label">เดือน</label>
                <?= Select2::widget([
                    'name' => 'report_month',
                    'value' => $reportMonth,
                    'data' => $monthOptions,
                    'options' => ['placeholder' => 'เดือน'],
                ]) ?>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label">คลังหลัก</label>
                <?= Select2::widget([
                    'name' => 'warehouse_id',
                    'value' => $warehouseId,
                    'data' => $warehouseOptions,
                    'options' => ['placeholder' => 'ทุกคลังหลัก'],
                    'pluginOptions' => ['allowClear' => true],
                ]) ?>
            </div>
            <div class="col-12 col-md-2">
                <div class="d-flex gap-2">
                    <?= Html::submitButton('<i class="fa-solid fa-magnifying-glass"></i> ค้นหา', ['class' => 'btn btn-primary flex-grow-1']) ?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card mt-3">
    <div class="card-header bg-primary-gradient text-white d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="text-white mb-0">
            <i class="fa-solid fa-chart-pie"></i>
            สรุปปิดยอดประจำเดือน <?= Html::encode($monthLabel) ?>
            <?php if ($warehouseId): ?>
                <small class="ms-2">— คลัง: <?= Html::encode($warehouseOptions[$warehouseId] ?? '-') ?></small>
            <?php endif; ?>
        </h6>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark">ที่มา: stock_monthly_report</span>
            <?php if (!empty($querys)): ?>
                <?= Html::a(
                    '<i class="fa-solid fa-file-excel me-1"></i> ส่งออก Excel',
                    array_merge(['summary-excel'], [
                        'report_year' => $reportYear,
                        'report_month' => $reportMonth,
                        'warehouse_id' => $warehouseId,
                    ]),
                    [
                        'class' => 'btn btn-success btn-sm shadow',
                        'target' => '_blank',
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-bordered table-striped mb-0">
            <thead class="align-middle text-center">
                <tr>
                    <th rowspan="2">ที่</th>
                    <th rowspan="2">รายการ (ประเภทวัสดุ)</th>
                    <th rowspan="2">สินค้าคงเหลือ<br><small class="fw-normal">(ยกมา)</small></th>
                    <th rowspan="2">ซื้อระหว่างเดือน</th>
                    <th rowspan="2">รวม</th>
                    <th colspan="3">สินค้าที่ใช้ไป</th>
                    <th rowspan="2">ยอดยกไป<br><small class="fw-normal">(คงเหลือ)</small></th>
                </tr>
                <tr>
                    <th class="text-center">จ่ายส่วนของ รพ.สต.</th>
                    <th class="text-center">จ่ายส่วนของโรงพยาบาล</th>
                    <th class="text-center">รวม</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php if (empty($querys)): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">
                            ยังไม่มีข้อมูลสรุปปิดเดือนของ <?= Html::encode($monthLabel) ?>
                            กรุณาไปที่
                            <?= Html::a('"สรุปคงคลังรายเดือน (ปิดเดือน)"',
                                ['index'],
                                ['class' => 'fw-bold']) ?>
                            แล้วกดปุ่ม "สรุปข้อมูลเดือนนี้" ก่อน
                        </td>
                    </tr>
                <?php else: ?>
                    <?php $num = 1; foreach ($querys as $item): ?>
                        <tr>
                            <td class="text-center"><?= $num++ ?></td>
                            <td>(<?= Html::encode($item['asset_type_code']) ?>) <?= Html::encode($item['asset_type_name']) ?></td>
                            <td class="text-end fw-bolder"><?= $fmt5($item['begin_price']) ?></td>
                            <td class="text-end fw-bolder"><?= $fmt5($item['price_in']) ?></td>
                            <td class="text-end fw-bolder"><?= $fmt5($item['total_price_begin']) ?></td>
                            <td class="text-end fw-bolder"><?= $fmt5($item['branch_price_out']) ?></td>
                            <td class="text-end fw-bolder"><?= $fmt5($item['price_out']) ?></td>
                            <td class="text-end fw-bolder"><?= $fmt5($item['total_price_out']) ?></td>
                            <td class="text-end fw-bolder"><?= $fmt5($item['end_price']) ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <tr class="table-warning">
                        <td></td>
                        <td class="text-center fw-bold">รวมทั้งหมด</td>
                        <td class="text-end fw-bolder"><?= $fmt5($sum['begin_price'] ?? 0) ?></td>
                        <td class="text-end fw-bolder"><?= $fmt5($sum['price_in'] ?? 0) ?></td>
                        <td class="text-end fw-bolder"><?= $fmt5($sum['total_price_begin'] ?? 0) ?></td>
                        <td class="text-end fw-bolder"><?= $fmt5($sum['branch_price_out'] ?? 0) ?></td>
                        <td class="text-end fw-bolder"><?= $fmt5($sum['price_out'] ?? 0) ?></td>
                        <td class="text-end fw-bolder"><?= $fmt5($sum['total_price_out'] ?? 0) ?></td>
                        <td class="text-end fw-bolder"><?= $fmt5($sum['end_price'] ?? 0) ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <div class="card-footer text-muted small d-flex justify-content-between">
        <span>
            สรุปจากตาราง <code>stock_monthly_report</code>
            (รายการรายตัวที่ถูกปิดเดือนแล้ว — ดูได้ที่
            <?= Html::a('หน้าปิดเดือน', ['index']) ?>)
        </span>
        <span>
            <?= count($querys) ?> ประเภท
        </span>
    </div>
</div>

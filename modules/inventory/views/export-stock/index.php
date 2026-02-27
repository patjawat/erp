<?php

use yii\helpers\Url;
use yii\helpers\Html;

$this->title = 'ส่งออกรายงานวัสดุคงคลังรายตัว';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="file-spreadsheet"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventory/menu_dashbroad', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="text-white mt-2 mb-0"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
        <span class="text-white">จำนวน <?= number_format(count($querys)) ?> รายการ</span>
    </div>
    <div class="card-body">
        <?= $this->render('_search', ['model' => $searchModel]) ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary-gradient text-white d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h6 class="text-white mt-2 mb-0">
            <i class="bi bi-ui-checks"></i> รายงานวัสดุคงคลังรายตัว
            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1 text-white">
                <?= number_format(count($querys)) ?> รายการ
            </span>
        </h6>
        <div class="d-flex gap-2">
            <?= Html::a(
                '<i class="fa fa-file-excel-o me-1"></i> ส่งออก Excel',
                array_merge(['/inventory/export-stock/excel'], Yii::$app->request->queryParams),
                ['class' => 'btn btn-light btn-sm']
            ) ?>
            <?= Html::a(
                '<i class="fa-solid fa-share-from-square me-1"></i> ส่งออกไป Inventory V2',
                array_merge(['/inventory/transfer-to-v2/index'], Yii::$app->request->queryParams),
                ['class' => 'btn btn-outline-light btn-sm']
            ) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height: 600px; overflow: auto;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light" style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th rowspan="2" style="width: 48px;" class="text-center align-middle">ลำดับ</th>
                        <th rowspan="2" style="width: 125px;" class="text-center align-middle">รหัสสินค้า</th>
                        <th rowspan="2" class="text-center align-middle">รายการสินค้า</th>
                        <th rowspan="2" class="text-center align-middle">ประเภทวัสดุ</th>
                        <th colspan="2" class="text-center">ยอดยกมา</th>
                        <th colspan="2" class="text-center">รับเข้า</th>
                        <th colspan="2" class="text-center">จ่ายออก</th>
                        <th colspan="2" class="text-center">คงเหลือสิ้นเดือน</th>
                    </tr>
                    <tr>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">มูลค่า</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">มูลค่า</th>
                        <th class="text-center">จำนวน</th>
                        <th class="text-center">มูลค่า</th>
                        <th class="text-center">จำนวนคงเหลือ</th>
                        <th class="text-center">มูลค่าคงเหลือ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php $num = 1; foreach ($querys as $item): ?>
                        <tr>
                            <td class="text-center"><?= $num++ ?></td>
                            <td><?= Html::encode($item['asset_item']) ?></td>
                            <td><?= Html::encode($item['asset_name']) ?></td>
                            <td><?= Html::encode($item['asset_type_name']) ?></td>
                            <td class="text-end"><?= number_format($item['begin_qty'] ?? 0, 5) ?></td>
                            <td class="text-end"><?= number_format($item['begin_price'] ?? 0, 5) ?></td>
                            <td class="text-end"><?= number_format($item['qty_in'] ?? 0, 5) ?></td>
                            <td class="text-end"><?= number_format($item['price_in'] ?? 0, 5) ?></td>
                            <td class="text-end"><?= number_format($item['total_qty_out'] ?? 0, 5) ?></td>
                            <td class="text-end"><?= number_format($item['total_price_out'] ?? 0, 5) ?></td>
                            <td class="text-end"><?= number_format($item['end_qty'] ?? 0, 5) ?></td>
                            <td class="text-end"><?= number_format($item['end_price'] ?? 0, 5) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="table-secondary" style="position: sticky; bottom: 0; z-index: 9;">
                    <tr>
                        <td colspan="4" class="text-center fw-medium">รวมทั้งหมด</td>
                        <td class="text-end fw-medium"><?= number_format($groupSummary['begin_qty'] ?? 0, 5) ?></td>
                        <td class="text-end fw-medium"><?= number_format($groupSummary['begin_price'] ?? 0, 5) ?></td>
                        <td class="text-end fw-medium"><?= number_format($groupSummary['qty_in'] ?? 0, 5) ?></td>
                        <td class="text-end fw-medium"><?= number_format($groupSummary['price_in'] ?? 0, 5) ?></td>
                        <td class="text-end fw-medium"><?= number_format($groupSummary['total_qty_out'] ?? 0, 5) ?></td>
                        <td class="text-end fw-medium"><?= number_format($groupSummary['total_price_out'] ?? 0, 5) ?></td>
                        <td class="text-end fw-medium"><?= number_format($groupSummary['end_qty'] ?? 0, 5) ?></td>
                        <td class="text-end fw-medium"><?= number_format($groupSummary['end_price'] ?? 0, 5) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

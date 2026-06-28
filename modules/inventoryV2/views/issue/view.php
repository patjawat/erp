<?php
use app\modules\inventoryV2\models\StockOrder;
use yii\helpers\Html;

/** @var StockOrder $model */

$mainWarehouse = $model->mainWarehouse->warehouse_name ?? '-';
$subWarehouse = $model->subWarehouse->warehouse_name ?? '-';
$issueReason = trim((string)$model->getIssueReason());
$orderDate = $model->order_date ?: $model->created_at;
$dateLabel = $orderDate ?: '-';

if ($orderDate && Yii::$app->has('thaiDate')) {
    $dateLabel = Yii::$app->thaiDate->toThaiDate($orderDate, true, false);
}

$statusMap = [
    StockOrder::STATUS_DRAFT => ['label' => 'ฉบับร่าง', 'class' => 'secondary'],
    StockOrder::STATUS_PENDING => ['label' => 'รออนุมัติ', 'class' => 'warning text-dark'],
    StockOrder::STATUS_APPROVED => ['label' => 'อนุมัติแล้ว', 'class' => 'info text-dark'],
    StockOrder::STATUS_CONFIRMED => ['label' => 'ตัดจ่ายแล้ว', 'class' => 'success'],
    StockOrder::STATUS_CANCELLED => ['label' => 'ยกเลิก', 'class' => 'secondary'],
];
$status = $statusMap[$model->status] ?? ['label' => (string)$model->status, 'class' => 'secondary'];
$details = $model->stockDetails;
$grandTotal = 0;
?>

<div class="issue-detail-view">
    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3 mb-3">
        <div>
            <div class="text-muted small mb-1">เลขที่เอกสาร</div>
            <h6 class="mb-0 fw-bold font-monospace"><?= Html::encode($model->order_no) ?></h6>
        </div>
        <span class="badge rounded-pill bg-<?= Html::encode($status['class']) ?> px-3 py-2">
            <?= Html::encode($status['label']) ?>
        </span>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-12 col-md-4">
            <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small mb-1">วันที่ตัดจ่าย</div>
                <div class="fw-semibold"><?= Html::encode($dateLabel) ?></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small mb-1">คลังที่จ่ายของ</div>
                <div class="fw-semibold"><?= Html::encode($mainWarehouse) ?></div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="border rounded-3 p-3 h-100">
                <div class="text-muted small mb-1">คลังที่รับของ</div>
                <div class="fw-semibold"><?= Html::encode($subWarehouse) ?></div>
            </div>
        </div>
    </div>

    <div class="border rounded-3 p-3 mb-3">
        <div class="text-muted small mb-1">เหตุผล/วัตถุประสงค์</div>
        <div><?= Html::encode($issueReason !== '' ? $issueReason : '-') ?></div>
    </div>

    <div class="table-responsive border rounded-3">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 48px;">#</th>
                    <th>รายการวัสดุ</th>
                    <th class="text-center" style="width: 110px;">Lot</th>
                    <th class="text-end" style="width: 100px;">จำนวน</th>
                    <th class="text-end" style="width: 110px;">ราคา/หน่วย</th>
                    <th class="text-end" style="width: 120px;">รวม</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($details)): ?>
                    <?php foreach ($details as $index => $detail): ?>
                        <?php
                        $item = $detail->item;
                        $itemName = $item->item_name ?? $detail->item_code;
                        $unitName = ($item && method_exists($item, 'getUnitName')) ? $item->getUnitName() : '';
                        $qty = (float)$detail->qty;
                        $unitPrice = (float)($detail->unit_price ?? 0);
                        $total = $qty * $unitPrice;
                        $grandTotal += $total;
                        ?>
                        <tr>
                            <td class="text-muted"><?= $index + 1 ?></td>
                            <td>
                                <div class="fw-semibold"><?= Html::encode($itemName) ?></div>
                                <div class="text-muted small">
                                    <?= Html::encode($detail->item_code) ?>
                                    <?php if ($unitName !== ''): ?>
                                        · <?= Html::encode($unitName) ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="text-center"><?= Html::encode($detail->lot_number ?: '-') ?></td>
                            <td class="text-end fw-semibold"><?= number_format($qty, 2) ?></td>
                            <td class="text-end"><?= number_format($unitPrice, 2) ?></td>
                            <td class="text-end fw-semibold"><?= number_format($total, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">ไม่มีรายการวัสดุ</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot class="table-light">
                <tr>
                    <th colspan="5" class="text-end">รวมมูลค่า</th>
                    <th class="text-end"><?= number_format($grandTotal, 2) ?></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

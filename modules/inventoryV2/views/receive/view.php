<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\StatusBadgeHelper;

$this->title = 'รายละเอียดใบรับเข้า';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'รับเข้าวัสดุ', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->order_no);

$statusLabel = StatusBadgeHelper::getLabel($model->status);
?>
<div class="stock-order-view">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-body"><?= Html::encode($model->order_no) ?></h4>
            <p class="text-muted small mb-0"><?= Html::encode($this->title) ?></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i> ย้อนกลับ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php if ($model->status !== 'CANCELLED'): ?>
                <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('<i class="bi bi-x-circle me-1"></i> ยกเลิกใบรับเข้า', ['cancel', 'id' => $model->id], [
                    'class' => 'btn btn-outline-danger',
                    'data' => [
                        'confirm' => 'ยืนยันยกเลิกใบรับเข้านี้หรือไม่? ระบบจะหักยอดสต็อกคืน',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-primary-gradient text-white py-2 px-3">
                    <h6 class="mb-0 text-white small fw-normal"><i class="bi bi-file-text me-1"></i> ข้อมูลหัวเอกสาร</h6>
                </div>
                <div class="card-body">
                    <?= DetailView::widget([
                        'model' => $model,
                        'options' => ['class' => 'table table-borderless table-sm mb-0'],
                        'template' => '<tr><th class="text-muted" style="width: 40%;">{label}</th><td>{value}</td></tr>',
                        'attributes' => [
                            [
                                'label' => 'เลขที่ใบรับเข้า',
                                'attribute' => 'order_no',
                            ],
                            [
                                'label' => 'วันที่รับเข้า',
                                'attribute' => 'order_date',
                                'format' => 'raw',
                                'value' => function ($model) {
                                    if (empty($model->order_date)) return '-';
                                    $ts = is_numeric($model->order_date) ? (int)$model->order_date : strtotime($model->order_date);
                                    $dateThai = \app\components\ThaiDateHelper::formatThaiDate($model->order_date);
                                    $time = date('H:i', $ts);
                                    return $dateThai . ' ' . $time;
                                },
                            ],
                            [
                                'label' => 'คลังที่รับเข้า',
                                'value' => $model->mainWarehouse ? Html::encode($model->mainWarehouse->warehouse_name) : '-',
                            ],
                            [
                                'label' => 'สถานะ',
                                'format' => 'raw',
                                'value' => StatusBadgeHelper::renderStatusBadge($model->status, ['tooltip' => $statusLabel]),
                            ],
                        ],
                    ]) ?>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary-gradient text-white py-2 px-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="mb-0 text-white small fw-normal"><i class="bi bi-box-seam me-1"></i> รายการพัสดุที่รับเข้า</h6>
                    <span class="badge text-bg-light text-dark"><?= count($model->stockDetails) ?> รายการ</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 3rem;">#</th>
                                    <th class="text-start">รหัสพัสดุ</th>
                                    <th class="text-start">ชื่อพัสดุ</th>
                                    <th class="text-center">เลข Lot</th>
                                    <th class="text-center">วันหมดอายุ</th>
                                    <th class="text-end">จำนวน</th>
                                    <th class="text-end">ราคา/หน่วย (บาท)</th>
                                    <th class="text-end">รวม (บาท)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $grandTotal = 0;
                                foreach ($model->stockDetails as $index => $item):
                                    $total = (float) $item->qty * (float) ($item->unit_price ?? 0);
                                    $grandTotal += $total;
                                    $itemName = $item->item ? Html::encode($item->item->item_name) : Html::encode($item->item_code);
                                ?>
                                <tr class="<?= $model->status === 'CANCELLED' ? 'text-decoration-line-through text-muted' : '' ?>">
                                    <td class="text-center"><?= $index + 1 ?></td>
                                    <td class="text-muted"><?= Html::encode($item->item_code) ?></td>
                                    <td><?= $itemName ?></td>
                                    <td class="text-center"><?= Html::encode($item->lot_number ?: '-') ?></td>
                                    <td class="text-center"><?= $item->expiry_date ? Html::encode($item->expiry_date) : '-' ?></td>
                                    <td class="text-end"><?= number_format($item->qty, 2) ?></td>
                                    <td class="text-end"><?= number_format($item->unit_price ?? 0, 2) ?></td>
                                    <td class="text-end fw-semibold"><?= number_format($total, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="7" class="text-end fw-bold">รวมยอดเงินทั้งหมด</td>
                                    <td class="text-end fw-bold text-primary" style="font-size: 1.1rem;"><?= number_format($grandTotal, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <?php $expenseItems = $model->getExpenseItems(); if (!empty($expenseItems)): ?>
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-light py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body"><i class="bi bi-cash-stack me-1"></i> รายการค่าใช้จ่าย / ใบเสร็จแนบ</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%;">#</th>
                                    <th style="width: 45%;">รายการ</th>
                                    <th style="width: 20%;" class="text-end">จำนวนเงิน (บาท)</th>
                                    <th style="width: 30%;">ใบเสร็จแนบ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($expenseItems as $ei => $row): ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $ei + 1 ?></td>
                                    <td><?= Html::encode($row['description'] ?? '-') ?></td>
                                    <td class="text-end"><?= number_format((float)($row['amount'] ?? 0), 2) ?></td>
                                    <td>
                                        <?php if (!empty($row['receipt_path'])): ?>
                                            <a href="<?= Html::encode($row['receipt_path']) ?>" target="_blank" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-pdf me-1"></i> ดูใบเสร็จ</a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
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
    </div>
</div>

<?php
use yii\helpers\Html;
use yii\widgets\DetailView;
use app\components\StatusBadgeHelper;

$this->title = 'รายละเอียดใบรับเข้า';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'รับเข้าวัสดุ', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->order_no);

$statusLabel = StatusBadgeHelper::getLabel($model->status);

$editTooltip = '<div class="rv-tip rv-tip--warning">'
    . '<div class="rv-tip__head"><i class="bi bi-pencil"></i> แก้ไขใบรับเข้า</div>'
    . '<div class="rv-tip__body">แก้ไขได้เฉพาะตอนที่ยังไม่มีการเบิกจ่ายวัสดุจาก Lot นี้เลย เพราะการแก้ไขจะย้อนยอดสต็อกเดิมออกก่อนแล้วรับเข้าใหม่ตามข้อมูลที่แก้ทั้งหมด</div>'
    . '</div>';
$cancelTooltip = '<div class="rv-tip rv-tip--secondary">'
    . '<div class="rv-tip__head"><i class="bi bi-x-circle"></i> ยกเลิกใบรับเข้า</div>'
    . '<div class="rv-tip__body">ยกเลิกได้เกือบทุกกรณี เอกสารยังอยู่ในระบบแต่ไม่มีผลอีกต่อไป เหมาะกับกรณีต้องการเก็บประวัติไว้ตรวจสอบย้อนหลัง</div>'
    . '</div>';
$deleteTooltip = '<div class="rv-tip rv-tip--danger">'
    . '<div class="rv-tip__head"><i class="bi bi-trash"></i> ลบใบรับเข้า</div>'
    . '<div class="rv-tip__body">ลบถาวร กู้คืนไม่ได้ กดได้เฉพาะตอนที่ยังไม่มีการเบิกจ่ายวัสดุจาก Lot นี้เลย เหมาะกับกรณีรับเข้าผิดทั้งใบ</div>'
    . '</div>';

$undeletableReason = $model->getUndeletableReason();
$canDelete = $undeletableReason === null;
?>
<div class="stock-order-view">

    <?php if ($model->status === 'DRAFT'): ?>
    <div class="alert alert-warning d-flex align-items-start gap-3 mb-4 border-0 shadow-sm" role="alert" style="background:#fff8e1">
        <i class="bi bi-exclamation-triangle-fill fs-4 text-warning flex-shrink-0 mt-1"></i>
        <div class="flex-grow-1">
            <h6 class="alert-heading mb-1 fw-bold">เอกสารนี้ยังเป็นฉบับร่าง — ยังไม่อัปเดตยอดคลัง</h6>
            <p class="mb-2 small">
                ยอดคงเหลือคลังยังไม่เปลี่ยน และใบเบิกที่ใช้วัสดุในเอกสารนี้จะ<strong>จ่ายไม่ได้</strong>
                จนกว่าจะเปิดและกด <em>"บันทึกและรับเข้าคลัง"</em>
            </p>
            <?= Html::a('<i class="bi bi-pencil-square me-1"></i> เปิดเพื่อยืนยันรับเข้าคลัง', ['update', 'id' => $model->id], [
                'class' => 'btn btn-warning btn-sm fw-semibold',
            ]) ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-body"><?= Html::encode($model->order_no) ?></h4>
            <p class="text-muted small mb-0"><?= Html::encode($this->title) ?></p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?= Html::a('<i class="bi bi-arrow-left me-1"></i> ย้อนกลับ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('<i class="bi bi-file-earmark-excel me-1"></i> ส่งออก Excel', ['export-excel', 'id' => $model->id], [
                'class' => 'btn btn-success',
                'target' => '_blank',
            ]) ?>
            <?php if ($canDelete): ?>
                <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไข', ['update', 'id' => $model->id], [
                    'class' => 'btn btn-warning',
                    'title' => $editTooltip,
                    'data' => [
                        'bs-toggle' => 'tooltip',
                        'bs-placement' => 'top',
                        'bs-html' => 'true',
                        'bs-custom-class' => 'rv-tip-pop',
                    ],
                ]) ?>
            <?php else: ?>
                <button type="button" class="btn btn-warning" disabled title="<?= Html::encode($undeletableReason) ?>">
                    <i class="bi bi-pencil me-1"></i> แก้ไข
                </button>
            <?php endif; ?>
            <?php if ($model->status !== 'CANCELLED'): ?>
                <?= Html::a('<i class="bi bi-x-circle me-1"></i> ยกเลิกใบรับเข้า', ['cancel', 'id' => $model->id], [
                    'class' => 'btn btn-secondary',
                    'title' => $cancelTooltip,
                    'data' => [
                        'confirm' => 'ยืนยันยกเลิกใบรับเข้านี้หรือไม่? ระบบจะหักยอดสต็อกคืน',
                        'method' => 'post',
                        'bs-toggle' => 'tooltip',
                        'bs-placement' => 'top',
                        'bs-html' => 'true',
                        'bs-custom-class' => 'rv-tip-pop',
                    ],
                ]) ?>
            <?php endif; ?>
            <?php if (\Yii::$app->user->can('admin')): ?>
                <?php if ($canDelete): ?>
                    <?= Html::a('<i class="bi bi-trash me-1"></i> ลบใบรับเข้า', ['delete', 'id' => $model->id], [
                        'class' => 'btn btn-danger',
                        'title' => $deleteTooltip,
                        'data' => [
                            'confirm' => 'ยืนยันลบใบรับเข้านี้หรือไม่? ระบบจะลบรายการและยอดคงเหลือที่เกี่ยวข้องทั้งหมด และไม่สามารถกู้คืนได้',
                            'method' => 'post',
                            'bs-toggle' => 'tooltip',
                            'bs-placement' => 'top',
                            'bs-html' => 'true',
                            'bs-custom-class' => 'rv-tip-pop',
                        ],
                    ]) ?>
                <?php else: ?>
                    <button type="button" class="btn btn-danger" disabled title="<?= Html::encode($undeletableReason) ?>">
                        <i class="bi bi-trash me-1"></i> ลบใบรับเข้า
                    </button>
                <?php endif; ?>
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
                            [
                                'label' => 'เลขที่สั่งซื้อ (PO)',
                                'format' => 'raw',
                                'visible' => trim((string) $model->ref) !== '',
                                'value' => function ($model) {
                                    $poOrderId = $model->getPoOrderId();
                                    $ref = Html::encode($model->ref);
                                    if (!$poOrderId) {
                                        return $ref;
                                    }
                                    return Html::a($ref . ' <i class="bi bi-box-arrow-up-right small"></i>', ['/purchase/order/view', 'id' => $poOrderId], [
                                        'target' => '_blank',
                                        'title' => 'ดูใบสั่งซื้อต้นทาง',
                                    ]);
                                },
                            ],
                            [
                                'label' => 'เลขที่ส่งสินค้า',
                                'value' => $model->getDeliveryNoteNo(),
                                'visible' => $model->getDeliveryNoteNo() !== '',
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
                                    <th class="text-center">หน่วยนับ</th>
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
                                    <td class="text-center text-muted"><?= $item->item && $item->item->unitName ? Html::encode($item->item->unitName) : '-' ?></td>
                                    <td class="text-end"><?= number_format($item->unit_price ?? 0, 2) ?></td>
                                    <td class="text-end fw-semibold"><?= number_format($total, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <td colspan="8" class="text-end fw-bold">รวมยอดเงินทั้งหมด</td>
                                    <td class="text-end fw-bold text-primary" style="font-size: 1.1rem;"><?= number_format($grandTotal, 2) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$this->registerJs(
    <<<'JS'
yii.confirm = function (message, ok, cancel) {
    Swal.fire({
        title: 'ยืนยันการทำรายการ',
        html: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#b91c1c',
        cancelButtonColor: '#6c757d',
        reverseButtons: false
    }).then(function (result) {
        if (result.isConfirmed) {
            ok && ok();
        } else {
            cancel && cancel();
        }
    });
    return false;
};
JS,
    \yii\web\View::POS_READY
);
?>

<?php $this->registerCss(<<<CSS
.tooltip.rv-tip-pop .tooltip-arrow {
    display: none;
}
.tooltip.rv-tip-pop .tooltip-inner {
    max-width: 260px;
    background: #ffffff;
    color: #1a202c;
    border: 1px solid rgba(15, 23, 42, .08);
    border-radius: 8px;
    box-shadow: 0 6px 18px rgba(15, 23, 42, .06), 0 2px 4px rgba(15, 23, 42, .04);
    padding: 0;
    text-align: left;
    overflow: hidden;
}
.rv-tip__head {
    display: flex;
    align-items: center;
    gap: .4rem;
    font-size: .82rem;
    font-weight: 600;
    padding: .5rem .7rem .35rem;
}
.rv-tip__body {
    font-size: .78rem;
    font-weight: 400;
    color: #4a5568;
    line-height: 1.45;
    padding: 0 .7rem .6rem;
}
.rv-tip--warning .rv-tip__head {
    color: #b45309;
    background: rgba(180, 83, 9, .08);
}
.rv-tip--secondary .rv-tip__head {
    color: #4a5568;
    background: rgba(15, 23, 42, .05);
}
.rv-tip--danger .rv-tip__head {
    color: #b91c1c;
    background: rgba(185, 28, 28, .08);
}
CSS
) ?>

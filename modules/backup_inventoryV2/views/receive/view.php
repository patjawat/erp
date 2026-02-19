<?php
use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = 'เลขที่บิล: ' . $model->order_no;
$this->params['breadcrumbs'][] = ['label' => 'รายการรับเข้า', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-order-view">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div>
            <?= Html::a('ย้อนกลับ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
            <?php if ($model->status !== 'CANCELLED'): ?>
                <?= Html::a('แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('ยกเลิกบิลนี้', ['cancel', 'id' => $model->id], [
                    'class' => 'btn btn-danger',
                    'data' => [
                        'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการยกเลิกบิลนี้? ยอดสต็อกจะถูกหักคืนทันที',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4 shadow-sm">
                <div class="card-header bg-light fw-bold">ข้อมูลหัวเอกสาร</div>
                <?= DetailView::widget([
                    'model' => $model,
                    'attributes' => [
                        'order_no',
                        'order_date:datetime',
                        'warehouse.warehouse_name',
                        'status',
                    ],
                ]) ?>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">รายการพัสดุที่รับเข้า</div>
                <div class="table-responsive">
                    <table class="table table-bordered mb-0">
                        <thead class="table-light text-center">
                            <tr>
                                <th>#</th>
                                <th class="text-start">พัสดุ</th>
                                <th>Lot</th>
                                <th>หมดอายุ</th>
                                <th>จำนวน</th>
                                <th>ราคา/หน่วย</th>
                                <th>รวม</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grandTotal = 0;
                            foreach ($model->stockDetails as $index => $item): 
                                $total = $item->qty * $item->unit_price;
                                $grandTotal += $total;
                            ?>
                            <tr class="<?= $model->status === 'CANCELLED' ? 'text-decoration-line-through text-muted' : '' ?>">
                                <td class="text-center"><?= $index + 1 ?></td>
                                <td><?= $item->item->item_name ?></td>
                                <td class="text-center"><?= $item->lot_number ?></td>
                                <td class="text-center"><?= $item->expiry_date ?: '-' ?></td>
                                <td class="text-center"><?= number_format($item->qty, 2) ?></td>
                                <td class="text-end"><?= number_format($item->unit_price, 2) ?></td>
                                <td class="text-end fw-bold"><?= number_format($total, 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <td colspan="6" class="text-end fw-bold">ยอดรวมสุทธิ</td>
                                <td class="text-end fw-bold text-primary" style="font-size: 1.2rem;">
                                    <?= number_format($grandTotal, 2) ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
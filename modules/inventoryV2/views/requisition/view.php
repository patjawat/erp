<?php
use yii\helpers\Html;
use yii\web\YiiAsset;

// ลงทะเบียน Asset เพื่อให้ data-confirm และ data-method ทำงาน
YiiAsset::register($this);

$this->title = 'รายละเอียดใบขอเบิก: ' . $model->order_no;
?>
<div class="requisition-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1><?= Html::encode($this->title) ?></h1>
        <div class="d-flex gap-2">
            <?php if ($model->canEdit()): ?>
            <?= Html::a('<i class="bi bi-pencil-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
            <?php endif; ?>
            <?= Html::a('ย้อนกลับ', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>
    
    <div class="alert <?= $model->status === 'CANCELLED' ? 'alert-danger' : 'alert-info' ?>">
        <div class="row">
            <div class="col-md-4">
                <strong>สถานะ:</strong>
                <?php
                    $statusLabel = [
                        'DRAFT' => '<span class="badge bg-warning text-dark">ฉบับร่าง / รออนุมัติ</span>',
                        'PENDING' => '<span class="badge bg-info">รอหัวหน้าอนุมัติ</span>',
                        'APPROVED' => '<span class="badge bg-primary">อนุมัติแล้ว — รอคลังจ่าย</span>',
                        'CONFIRMED' => '<span class="badge bg-success">จ่ายสินค้าแล้ว</span>',
                        'CANCELLED' => '<span class="badge bg-danger">ยกเลิกแล้ว</span>',
                    ];
                    echo $statusLabel[$model->status] ?? $model->status;
                ?>
            </div>
            <div class="col-md-4">
                <strong>คลังที่จ่ายของ:</strong> <?= $model->mainWarehouse ? Html::encode($model->mainWarehouse->warehouse_name) : '(ไม่ได้ระบุ)' ?>
            </div>
            <div class="col-md-4">
                <strong>หน่วยงานที่รับของ:</strong> <?= $model->subWarehouse ? Html::encode($model->subWarehouse->warehouse_name) : '(ไม่ได้ระบุ)' ?>
            </div>
        </div>
    </div>

    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>รายการวัสดุ</th>
                <th class="text-center" style="width: 100px;">หน่วยนับ</th>
                <th class="text-end" style="width: 150px;">จำนวนที่ขอเบิก</th>
                <th class="text-end" style="width: 200px;">ยอดคงเหลือในคลังที่จ่าย</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($model->stockDetails as $detail): ?>
            <tr>
                <td>
                    <strong>[<?= Html::encode($detail->item_code) ?>]</strong>
                    <?= Html::encode($detail->item->item_name ?? '') ?>
                </td>
                <td class="text-center text-muted"><?= Html::encode($detail->item ? ($detail->item->getUnitName() ?: '-') : '-') ?></td>
                <td class="text-end"><?= number_format($detail->qty, 2) ?></td>
                <td class="text-end">
                    <?php 
                        // ตรวจสอบยอดคงเหลือจริงในคลังหลัก
                        $balance = $detail->item->getStockBalance($model->main_warehouse_id);
                        
                        // ถ้ายกเลิกแล้ว หรือจ่ายแล้ว ยอดคงเหลือปัจจุบันอาจเปลี่ยนไป จึงใช้การแสดงผลเพื่อแจ้งเตือน
                        if (in_array($model->status, ['DRAFT', 'PENDING', 'APPROVED']) && $balance < $detail->qty) {
                            echo "<span class='text-danger fw-bold'>" . number_format($balance, 2) . " (ไม่พอจ่าย)</span>";
                        } else {
                            echo number_format($balance, 2);
                        }
                    ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <hr>

    <div class="form-group d-flex justify-content-between">
        <div>
            <?php if (in_array($model->status, ['DRAFT', 'PENDING'])): ?>
                <?= Html::a('<i class="bi bi-check-circle"></i> อนุมัติ', ['approve', 'id' => $model->id], [
                    'class' => 'btn btn-success btn-lg',
                    'data' => [
                        'confirm' => 'ยืนยันอนุมัติใบขอเบิก? (ยังไม่ตัดสต็อก — คลังจะจ่ายที่เมนู "รายการจ่ายพัสดุ")',
                        'method' => 'post'
                    ]
                ]) ?>
            <?php endif; ?>
            <?php if ($model->status === 'APPROVED'): ?>
                <?= Html::a('<i class="bi bi-box-seam"></i> ดำเนินการจ่าย', ['/inventory-v2/issue/process', 'id' => $model->id], [
                    'class' => 'btn btn-primary btn-lg',
                ]) ?>
            <?php endif; ?>
        </div>

        <div>
            <?php if ($model->status !== 'CANCELLED'): ?>
                <?= Html::a('ยกเลิกใบเบิกนี้', ['cancel', 'id' => $model->id], [
                    'class' => 'btn btn-outline-danger',
                    'data' => [
                        'confirm' => $model->status === 'CONFIRMED'
                            ? 'เอกสารนี้จ่ายของไปแล้ว การยกเลิกจะนำสินค้ากลับเข้าสต็อกคลังหลัก ยืนยันหรือไม่?'
                            : 'ยืนยันการยกเลิกใบขอเบิกนี้?',
                        'method' => 'post',
                    ],
                ]) ?>
            <?php endif; ?>
        </div>
    </div>
</div>
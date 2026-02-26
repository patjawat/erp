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
                    $st = \app\modules\inventoryV2\models\StockOrder::getStatusBadgeConfigFor($model->status);
                    $icon = !empty($st['icon']) ? '<i data-lucide="' . Html::encode($st['icon']) . '" class="me-1" style="width:14px;height:14px;vertical-align:-0.2em"></i>' : '';
                    echo '<span class="' . $st['class'] . '">' . $icon . Html::encode($st['label']) . '</span>';
                ?>
            </div>
            <div class="col-md-4">
                <strong>คลังที่จ่ายของ:</strong> <?= $model->mainWarehouse ? Html::encode($model->mainWarehouse->warehouse_name) : '(ไม่ได้ระบุ)' ?>
            </div>
            <div class="col-md-4">
                <strong>หน่วยงานที่รับของ:</strong> <?= $model->subWarehouse ? Html::encode($model->subWarehouse->warehouse_name) : '(ไม่ได้ระบุ)' ?>
            </div>
        </div>
        <?php if ($model->getIssueReason() !== ''): ?>
        <div class="row mt-2">
            <div class="col-12">
                <strong>เหตุผล/วัตถุประสงค์การเบิก:</strong> <?= nl2br(Html::encode($model->getIssueReason())) ?>
            </div>
        </div>
        <?php endif; ?>
        <?php
        $requester = $model->getIssueSignature('requester');
        $requesterEmp = $model->getRequesterEmployee();
        $requesterName = $requester['name'] ?: ($requesterEmp ? ($requesterEmp->fullname ?? '') : '');
        $requesterPosition = $requester['position'] ?: ($requesterEmp && method_exists($requesterEmp, 'positionName') ? ($requesterEmp->positionName() ?: '') : '');
        if ($requesterName !== '' || $requesterEmp): ?>
        <div class="row mt-2">
            <div class="col-12">
                <strong>ผู้ขอเบิก:</strong>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <?php if ($requesterEmp && method_exists($requesterEmp, 'ShowAvatar')): ?>
                        <?= Html::img($requesterEmp->ShowAvatar(), [
                            'class' => 'rounded-circle object-fit-cover',
                            'style' => 'width: 36px; height: 36px;',
                            'alt' => Html::encode($requesterName),
                        ]) ?>
                    <?php endif; ?>
                    <span><?= Html::encode($requesterName ?: '-') ?><?= $requesterPosition !== '' ? ' — ' . Html::encode($requesterPosition) : '' ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php
        $approver = $model->getIssueSignature('approver');
        if (!empty($approver['name']) || !empty($approver['position']) || !empty($approver['date'])): ?>
        <div class="row mt-2">
            <div class="col-12">
                <strong>ผู้เห็นชอบ (หัวหน้า):</strong>
                <?= Html::encode($approver['name']) ?><?= $approver['position'] ? ' — ' . Html::encode($approver['position']) : '' ?>
                <?php if (!empty($approver['date'])): ?>
                    <?php
                    $approvalDateFormatted = \app\components\ThaiDateHelper::formatThaiDate($approver['date']);
                    ?>
                    <span class="text-muted ms-1">(วันที่อนุมัติ: <?= Html::encode($approvalDateFormatted) ?>)</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
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

    <?php
    $approverEmpId = $model->getIssueSignatureEmpId('approver');
    $isCurrentUserApprover = false;
    if ($approverEmpId && !Yii::$app->user->isGuest) {
        $approverEmp = \app\modules\hr\models\Employees::findOne($approverEmpId);
        $isCurrentUserApprover = $approverEmp && (int)$approverEmp->user_id === (int)Yii::$app->user->id;
    }
    $hasInventoryPermission = !Yii::$app->user->isGuest && Yii::$app->user->can('inventory');
    $showApproveButton = in_array($model->status, ['DRAFT', 'PENDING']) && ($isCurrentUserApprover || $hasInventoryPermission);
    ?>
    <div class="form-group d-flex justify-content-between">
        <div>
            <?php if ($showApproveButton): ?>
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
<?php
use app\modules\inventoryV2\models\StockOrder;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\YiiAsset;

// ลงทะเบียน Asset เพื่อให้ data-confirm และ data-method ทำงาน
YiiAsset::register($this);

$this->title = 'รายละเอียดใบขอเบิก: ' . $model->order_no;

$userId = Yii::$app->user->isGuest ? null : (int) Yii::$app->user->id;
$canInventory = !Yii::$app->user->isGuest && Yii::$app->user->can('inventory');
$canCreateRequisition = !Yii::$app->user->isGuest && (
    Yii::$app->user->can('admin')
    || !empty(\app\modules\inventoryV2\models\Warehouse::findSubWarehousesForUser(true))
);
$approverEmpId = $model->getIssueSignatureEmpId('approver');
$isCurrentUserApprover = false;
if ($approverEmpId && $userId) {
    $approverEmp = \app\modules\hr\models\Employees::findOne($approverEmpId);
    $isCurrentUserApprover = $approverEmp && (int) $approverEmp->user_id === $userId;
}
$canApproverEdit = in_array($model->status, [StockOrder::STATUS_DRAFT, StockOrder::STATUS_PENDING], true)
    && $isCurrentUserApprover;
$revisions = $model->getApproverRevisions();
?>
<div class="requisition-view">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h4 mb-0 fw-semibold"><?= Html::encode($this->title) ?></h1>
        <div class="d-flex gap-2">
            <?php if ($model->canEdit() && $isCurrentUserApprover && !$canApproverEdit): ?>
                <?= Html::a('<i class="bi bi-pencil-square"></i> แก้ไข', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
            <?php endif; ?>
        </div>
    </div>

    <?= $this->render('@app/modules/inventoryV2/views/_partials/_migrated_v1_panel', ['model' => $model]) ?>

    <div class="alert <?= $model->status === 'CANCELLED' ? 'alert-danger' : 'alert-info' ?>">
        <div class="row">
            <div class="col-md-4">
                <strong>สถานะ:</strong>
                <?php
                    $st = StockOrder::getStatusBadgeConfigFor($model->status);
                    $icon = !empty($st['icon']) ? '<i data-lucide="' . Html::encode($st['icon']) . '" class="me-1" style="width:14px;height:14px;vertical-align:-0.2em"></i>' : '';
                    echo '<span class="' . $st['class'] . '">' . $icon . Html::encode($st['label']) . '</span>';
                ?>
            </div>
            <div class="col-md-4">
                <strong>คลังที่จ่ายของ:</strong> <?= $model->mainWarehouse ? Html::encode($model->mainWarehouse->warehouse_name) : '(ไม่ได้ระบุ)' ?>
            </div>
            <div class="col-md-4">
                <strong>คลังที่รับของ:</strong> <?= $model->subWarehouse ? Html::encode($model->subWarehouse->warehouse_name) : '(ไม่ได้ระบุ)' ?>
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

    <?php if ($canApproverEdit): ?>
        <?= $this->render('_approver_edit_panel', [
            'model' => $model,
            'isCurrentUserApprover' => $isCurrentUserApprover,
        ]) ?>
    <?php else: ?>
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
                    <td class="text-end" style="font-variant-numeric: tabular-nums"><?= number_format($detail->qty, 2) ?></td>
                    <td class="text-end" style="font-variant-numeric: tabular-nums">
                        <?php
                            $balance = $detail->item ? $detail->item->getStockBalance($model->main_warehouse_id) : 0;
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
    <?php endif; ?>

    <?php if (!empty($revisions)): ?>
        <?= $this->render('_approver_revisions', [
            'revisions' => $revisions,
            'model' => $model,
        ]) ?>
    <?php endif; ?>

    <?php if (!$canApproverEdit): ?>
        <hr>
        <?php $showApproveButton = in_array($model->status, [StockOrder::STATUS_DRAFT, StockOrder::STATUS_PENDING], true) && $isCurrentUserApprover; ?>
        <div class="form-group d-flex justify-content-between">
            <div class="d-flex gap-2">
                <?php if ($showApproveButton): ?>
                    <?= Html::a('<i class="bi bi-check-circle"></i> อนุมัติ', ['approve', 'id' => $model->id], [
                        'class' => 'btn btn-success btn-lg',
                        'data' => [
                            'confirm' => 'ยืนยันอนุมัติใบขอเบิก? (ยังไม่ตัดสต็อก — คลังจะจ่ายที่เมนู "รายการจ่ายพัสดุ")',
                            'method' => 'post'
                        ]
                    ]) ?>
                <?php endif; ?>
                <?php if ($model->canRestore($userId, $canInventory)): ?>
                    <?= Html::button('<i class="bi bi-arrow-clockwise"></i> คืนสถานะเป็นฉบับร่าง', [
                        'type' => 'button',
                        'class' => 'btn btn-outline-success js-restore',
                        'data' => [
                            'url' => Url::to(['restore', 'id' => $model->id]),
                            'order-no' => $model->order_no,
                            'bs-toggle' => 'tooltip',
                            'bs-title' => 'คืนใบที่ยกเลิกแล้วกลับมาเป็นฉบับร่าง เพื่อแก้ไขและส่งอนุมัติเบิกใหม่ (ยังไม่ตัดสต็อกจนกว่าคลังจะจ่าย)',
                        ],
                    ]) ?>
                <?php endif; ?>
                <?php if ($canCreateRequisition && $model->canCopy()): ?>
                    <?= Html::button('<i class="bi bi-files"></i> คัดลอกเป็นใบเบิกใหม่', [
                        'type' => 'button',
                        'class' => 'btn btn-outline-info js-copy',
                        'data' => [
                            'url' => Url::to(['copy', 'id' => $model->id]),
                            'order-no' => $model->order_no,
                            'bs-toggle' => 'tooltip',
                            'bs-title' => 'คัดลอกใบนี้เป็นใบเบิกใหม่ (ฉบับร่าง) พร้อมรายการเดิม เพื่อเบิกซ้ำได้สะดวก — จะไม่แก้ไขใบเดิม',
                        ],
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
    <?php endif; ?>
</div>

<?php
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$this->registerCss('.swal2-actions.req-swal-actions { flex-direction: row-reverse !important; justify-content: center; }');
$this->registerJs(<<<JS
(function () {
    if (document.body.dataset.reqConfirmBound) return;
    document.body.dataset.reqConfirmBound = '1';
    function submitPost(url) {
        var f = document.createElement('form');
        f.method = 'post'; f.action = url;
        var t = document.createElement('input');
        t.type = 'hidden'; t.name = '{$csrfParam}'; t.value = '{$csrfToken}';
        f.appendChild(t); document.body.appendChild(f); f.submit();
    }
    var CFG = {
        'js-restore': {
            title: 'คืนสถานะเป็นฉบับร่าง?',
            html: function (n) { return 'ใบ <strong>' + n + '</strong> ที่ยกเลิกแล้ว จะถูกคืนกลับมาเป็น<strong>ฉบับร่าง</strong> ให้แก้ไขและ<strong>ส่งอนุมัติเบิกใหม่</strong>ได้<br><span class="text-muted">ยังไม่ตัดสต็อกจนกว่าคลังจะจ่าย</span>'; },
            fallback: function (n) { return 'คืนใบ ' + n + ' ที่ยกเลิกแล้วกลับมาเป็นฉบับร่างเพื่อเบิกใหม่?'; },
            confirmText: 'คืนสถานะ', color: '#198754'
        },
        'js-copy': {
            title: 'คัดลอกเป็นใบเบิกใหม่?',
            html: function (n) { return 'สร้างใบเบิก<strong>ใหม่ (ฉบับร่าง)</strong> จากใบ <strong>' + n + '</strong> พร้อมรายการวัสดุเดิม<br><span class="text-muted">ใบเดิมไม่ถูกแก้ไข — ระบบจะพาไปหน้าแก้ไขให้ปรับจำนวนก่อนส่งอนุมัติ</span>'; },
            fallback: function (n) { return 'คัดลอกใบ ' + n + ' เป็นใบเบิกใหม่ (ฉบับร่าง)?'; },
            confirmText: 'คัดลอก', color: '#0dcaf0'
        }
    };
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.js-restore, .js-copy');
        if (!btn) return;
        var key = btn.classList.contains('js-restore') ? 'js-restore' : 'js-copy';
        var c = CFG[key], url = btn.getAttribute('data-url'), n = btn.getAttribute('data-order-no') || '';
        if (!url) return;
        e.preventDefault();
        var tip = window.bootstrap && bootstrap.Tooltip && bootstrap.Tooltip.getInstance(btn);
        if (tip) tip.hide();
        if (!window.Swal) { if (confirm(c.fallback(n))) submitPost(url); return; }
        Swal.fire({
            title: c.title, html: c.html(n), icon: 'question', iconColor: c.color,
            showCancelButton: true, confirmButtonText: c.confirmText, cancelButtonText: 'ยกเลิก',
            confirmButtonColor: c.color, reverseButtons: true, customClass: { actions: 'req-swal-actions' }
        }).then(function (r) { if (r.isConfirmed) submitPost(url); });
    });
    if (window.bootstrap && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
            if (!bootstrap.Tooltip.getInstance(el)) new bootstrap.Tooltip(el);
        });
    }
})();
JS, \yii\web\View::POS_END);
?>

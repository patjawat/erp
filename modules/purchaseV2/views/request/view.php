<?php

use yii\helpers\Html;
use app\components\UserHelper;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\purchaseV2\models\PurchaseRequest;
use app\modules\purchaseV2\models\PurchaseRequestApproval;

/** @var PurchaseRequest $model */

$this->title = 'รายละเอียดคำขอจัดซื้อ';
$this->params['breadcrumbs'][] = ['label' => 'จัดซื้อจัดจ้าง V2', 'url' => ['/purchase-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'รายการคำขอ', 'url' => ['/purchase-v2/request/index']];
$this->params['breadcrumbs'][] = $this->title;

$me = UserHelper::GetEmployee();
$currentApproval = $model->currentApproval;
$requesterSummary = $model->requesterSummary();
$departmentSummary = $model->departmentSummary();
$canManage = Yii::$app->user->can('admin') || Yii::$app->user->can('purchase');
$isOwner = (int) $model->created_by === (int) Yii::$app->user->id;
$approvalProgress = $model->approvalProgress();
$budgetUsage = $model->budgetUsagePercent();

$workflowTrail = [
    [
        'status' => PurchaseRequest::STATUS_DRAFT,
        'label' => 'แบบร่าง',
        'icon' => 'file-pen-line',
    ],
    [
        'status' => PurchaseRequest::STATUS_PENDING_APPROVAL,
        'label' => 'รออนุมัติ',
        'icon' => 'hourglass',
    ],
    [
        'status' => PurchaseRequest::STATUS_APPROVED,
        'label' => 'อนุมัติ',
        'icon' => 'badge-check',
    ],
    [
        'status' => PurchaseRequest::STATUS_ORDERED,
        'label' => 'จัดซื้อ',
        'icon' => 'file-signature',
    ],
    [
        'status' => PurchaseRequest::STATUS_RECEIVED,
        'label' => 'ตรวจรับ',
        'icon' => 'package-search',
    ],
    [
        'status' => PurchaseRequest::STATUS_STOCKED,
        'label' => 'เข้าคลัง',
        'icon' => 'warehouse',
    ],
    [
        'status' => PurchaseRequest::STATUS_COMPLETED,
        'label' => 'ปิดงาน',
        'icon' => 'circle-check-big',
    ],
];

$nextActionText = 'พร้อมดำเนินการต่อ';
if ($currentApproval && $currentApproval->status === PurchaseRequestApproval::STATUS_PENDING) {
    $nextActionText = 'ขั้นตอนถัดไป: ' . ($currentApproval->role_name ?: 'รออนุมัติ');
} elseif ((int) $model->status === PurchaseRequest::STATUS_DRAFT) {
    $nextActionText = 'ขั้นตอนถัดไป: ตรวจข้อมูลและส่งอนุมัติ';
} elseif ((int) $model->status === PurchaseRequest::STATUS_APPROVED) {
    $nextActionText = 'ขั้นตอนถัดไป: ดำเนินการจัดซื้อ';
} elseif ((int) $model->status === PurchaseRequest::STATUS_ORDERED) {
    $nextActionText = 'ขั้นตอนถัดไป: ตรวจรับพัสดุ';
} elseif ((int) $model->status === PurchaseRequest::STATUS_RECEIVED) {
    $nextActionText = 'ขั้นตอนถัดไป: เข้าคลังและปิดงาน';
}
?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/purchaseV2/menu', ['active' => 'request']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
    <div class="card-body p-4 p-lg-5">
        <div class="d-flex flex-column flex-lg-row justify-content-between gap-4">
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                        <?= Html::encode($model->getDisplayReference()) ?>
                    </span>
                    <?= $model->statusBadge() ?>
                    <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">
                        <?= Html::encode($model->requestTypeLabel()) ?>
                    </span>
                    <?php if (!empty($model->legacy_ref)): ?>
                        <span class="badge bg-light text-secondary border rounded-pill px-3 py-2">
                            นำเข้า: <?= Html::encode($model->legacy_ref) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h3 class="fw-bold mb-2"><?= Html::encode($model->request_title) ?></h3>
                <p class="text-muted mb-0"><?= Html::encode($model->summary ?: '-') ?></p>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <?php foreach ($workflowTrail as $step): ?>
                        <?php
                        $meta = PurchaseRequest::statusOptions()[$step['status']] ?? [
                            'label' => $step['label'],
                            'color' => 'secondary',
                            'icon' => $step['icon'],
                        ];
                        $isActive = (int) $model->status === (int) $step['status'];
                        $isComplete = (int) $model->status > (int) $step['status'] && (int) $model->status !== PurchaseRequest::STATUS_CANCELLED;
                        $pillClass = $isActive
                            ? 'bg-primary text-white border-primary'
                            : ($isComplete ? 'bg-success bg-opacity-10 text-success border-success-subtle' : 'bg-light text-muted border');
                        ?>
                        <span class="badge rounded-pill border fw-semibold px-3 py-2 <?= $pillClass ?>">
                            <i data-lucide="<?= Html::encode($meta['icon']) ?>" class="me-1"></i>
                            <?= Html::encode($meta['label']) ?>
                        </span>
                    <?php endforeach; ?>
                </div>

                <div class="mt-4">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>ความคืบหน้ากระบวนการ</span>
                        <span><?= number_format((int) $approvalProgress, 0) ?>%</span>
                    </div>
                    <div class="progress rounded-pill" style="height: .65rem;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= (int) $approvalProgress ?>%;" aria-valuenow="<?= (int) $approvalProgress ?>" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="alert alert-primary bg-primary bg-opacity-10 border-0 rounded-4 mt-4 mb-0">
                    <div class="fw-semibold mb-1">Next Action</div>
                    <div class="small text-body-secondary"><?= Html::encode($nextActionText) ?></div>
                </div>
            </div>

            <div class="text-lg-end">
                <div class="p-4 rounded-4 bg-body-tertiary">
                    <div class="text-muted small">ยอดรวมสุทธิ</div>
                    <div class="display-6 fw-bold text-primary mb-1"><?= number_format((float) $model->grand_total, 2) ?></div>
                    <div class="text-muted small">งบประมาณ <?= number_format((float) $model->budget_amount, 2) ?></div>
                </div>
                <div class="row g-2 mt-3">
                    <div class="col-12 col-sm-6 col-lg-12">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">ผู้ขอ</div>
                            <div class="fw-semibold"><?= Html::encode($requesterSummary['fullname']) ?></div>
                            <div class="text-muted small"><?= Html::encode($requesterSummary['position'] ?: '-') ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-sm-6 col-lg-12">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">หน่วยงาน</div>
                            <div class="fw-semibold"><?= Html::encode($departmentSummary['name']) ?></div>
                            <div class="text-muted small">ปีงบประมาณ <?= Html::encode($model->budget_year ?: '-') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-4">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="p-3 rounded-4 bg-body-tertiary h-100">
                    <div class="text-muted small">วันที่คำขอ</div>
                    <div class="fw-semibold"><?= Html::encode($model->request_date ?: '-') ?></div>
                    <div class="text-muted small">เลขที่เอกสาร: <?= Html::encode($model->request_no ?: '-') ?></div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="p-3 rounded-4 bg-body-tertiary h-100">
                    <div class="text-muted small">ประเภทงบ</div>
                    <div class="fw-semibold"><?= Html::encode($model->budgetTypeLabel()) ?></div>
                    <div class="text-muted small">VAT: <?= Html::encode($model->vatTypeLabel()) ?></div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="p-3 rounded-4 bg-body-tertiary h-100">
                    <div class="text-muted small">รายการ</div>
                    <div class="fw-semibold"><?= number_format(count($model->items), 0) ?> รายการ</div>
                    <div class="text-muted small">ผู้ขาย/ผู้รับจ้าง: <?= Html::encode($model->vendorLabel()) ?></div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-xl-3">
                <div class="p-3 rounded-4 bg-body-tertiary h-100">
                    <div class="text-muted small">การใช้งบ</div>
                    <div class="fw-semibold"><?= number_format((float) $model->grand_total, 2) ?> / <?= number_format((float) $model->budget_amount, 2) ?></div>
                    <div class="text-muted small"><?= number_format((int) $budgetUsage, 0) ?>% ของวงเงิน</div>
                </div>
            </div>
        </div>

        <?php if ((int) $model->status === PurchaseRequest::STATUS_CANCELLED): ?>
            <div class="alert alert-danger border-0 rounded-4 mt-4 mb-0">
                รายการนี้ถูกยกเลิกแล้ว
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3 p-lg-4">
        <div class="d-flex flex-wrap gap-2">
            <a href="#req-overview" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">ข้อมูลคำขอ</a>
            <a href="#req-items" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">รายการ</a>
            <a href="#req-files" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">เอกสารแนบ</a>
            <a href="#req-approval" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">อนุมัติ</a>
            <a href="#req-logs" class="btn btn-sm btn-outline-primary rounded-3 fw-semibold">ประวัติ</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4" id="req-overview">
            <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">ภาพรวมคำขอ</h5>
                    <div class="text-muted small">ข้อมูลสำคัญที่ต้องเห็นเร็วที่สุด</div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if (!empty($model->pr_number)): ?><span class="badge bg-light text-dark border rounded-pill">PR: <?= Html::encode($model->pr_number) ?></span><?php endif; ?>
                    <?php if (!empty($model->pq_number)): ?><span class="badge bg-light text-dark border rounded-pill">PQ: <?= Html::encode($model->pq_number) ?></span><?php endif; ?>
                    <?php if (!empty($model->po_number)): ?><span class="badge bg-light text-dark border rounded-pill">PO: <?= Html::encode($model->po_number) ?></span><?php endif; ?>
                    <?php if (!empty($model->gr_number)): ?><span class="badge bg-light text-dark border rounded-pill">GR: <?= Html::encode($model->gr_number) ?></span><?php endif; ?>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4 border h-100">
                            <div class="text-muted small mb-1">ผู้ขอ</div>
                            <div class="fw-semibold"><?= Html::encode($requesterSummary['fullname']) ?></div>
                            <div class="text-muted small"><?= Html::encode($requesterSummary['position'] ?: '-') ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-4 border h-100">
                            <div class="text-muted small mb-1">หน่วยงาน</div>
                            <div class="fw-semibold"><?= Html::encode($departmentSummary['name']) ?></div>
                            <div class="text-muted small">ปีงบประมาณ <?= Html::encode($model->budget_year ?: '-') ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-4 border h-100">
                            <div class="text-muted small mb-1">วันที่คำขอ</div>
                            <div class="fw-semibold"><?= Html::encode($model->request_date ?: '-') ?></div>
                            <div class="text-muted small">เลขที่คำขอ <?= Html::encode($model->request_no ?: '-') ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-4 border h-100">
                            <div class="text-muted small mb-1">ประเภทจัดซื้อ</div>
                            <div class="fw-semibold"><?= Html::encode($model->requestTypeLabel()) ?></div>
                            <div class="text-muted small">VAT <?= Html::encode($model->vatTypeLabel()) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 rounded-4 border h-100">
                            <div class="text-muted small mb-1">เลขอ้างอิงเดิม</div>
                            <div class="fw-semibold"><?= Html::encode($model->legacy_ref ?: '-') ?></div>
                            <div class="text-muted small">Trace กลับไปยังระบบเดิมได้</div>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="text-muted small mb-2">เรื่อง</div>
                    <div class="fw-semibold fs-5"><?= Html::encode($model->request_title) ?></div>
                </div>
                <div class="mt-3">
                    <div class="text-muted small mb-2">รายละเอียด / ความจำเป็น</div>
                    <div class="p-3 rounded-4 bg-body-tertiary">
                        <?= nl2br(Html::encode($model->summary ?: '-')) ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4" id="req-items">
            <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">รายการพัสดุ / ครุภัณฑ์</h5>
                    <div class="text-muted small">สแกนรายการและมูลค่าได้ทันทีโดยไม่ต้องอ่านตารางยาว</div>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                    <?= number_format(count($model->items), 0) ?> รายการ
                </span>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <?php foreach ($model->items as $item): ?>
                        <div class="card border rounded-4 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 mb-3">
                                    <div class="d-flex align-items-start gap-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                                            #<?= number_format((int) $item->line_no, 0) ?>
                                        </span>
                                        <div>
                                            <div class="fw-semibold"><?= Html::encode($item->item_name) ?></div>
                                            <div class="text-muted small"><?= Html::encode($item->detail ?: '-') ?></div>
                                        </div>
                                    </div>
                                    <span class="badge bg-light text-dark border rounded-pill align-self-start">
                                        <?= Html::encode($item->itemTypeLabel()) ?>
                                    </span>
                                </div>

                                <div class="row g-3">
                                    <div class="col-12 col-md-3">
                                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                                            <div class="text-muted small">หน่วย</div>
                                            <div class="fw-semibold"><?= Html::encode($item->unit_name ?: '-') ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                                            <div class="text-muted small">จำนวน</div>
                                            <div class="fw-semibold"><?= number_format((float) $item->qty, 2) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                                            <div class="text-muted small">ราคาต่อหน่วย</div>
                                            <div class="fw-semibold"><?= number_format((float) $item->unit_price, 2) ?></div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-3">
                                        <div class="p-3 rounded-4 bg-primary bg-opacity-10 h-100">
                                            <div class="text-muted small">รวม</div>
                                            <div class="fw-bold text-primary fs-5"><?= number_format((float) $item->amount, 2) ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($model->items)): ?>
                        <div class="text-center text-muted py-5">
                            <div class="bg-body-tertiary rounded-circle d-inline-flex align-items-center justify-content-center p-4 mb-3">
                                <i data-lucide="package-search" class="fs-3"></i>
                            </div>
                            <div class="fw-semibold">ยังไม่มีรายการพัสดุ / ครุภัณฑ์</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer bg-white border-top p-4">
                <div class="row g-3">
                    <div class="col-12 col-md-3">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">ยอดก่อนส่วนลด</div>
                            <div class="fw-bold text-primary fs-5"><?= number_format((float) $model->subtotal_amount, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">ส่วนลด</div>
                            <div class="fw-bold text-warning fs-5"><?= number_format((float) $model->discount_amount, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="p-3 rounded-4 bg-body-tertiary h-100">
                            <div class="text-muted small">VAT</div>
                            <div class="fw-bold text-info fs-5"><?= number_format((float) $model->vat_amount, 2) ?></div>
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="p-3 rounded-4 bg-primary bg-opacity-10 h-100">
                            <div class="text-muted small">ยอดรวมสุทธิ</div>
                            <div class="fw-bold text-primary fs-5"><?= number_format((float) $model->grand_total, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4" id="req-files">
            <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">เอกสารแนบ</h5>
                    <div class="text-muted small">รองรับไฟล์ปัจจุบันและไฟล์เดิมจากระบบเก่า</div>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="mb-4">
                    <div class="text-muted small mb-2">ไฟล์ปัจจุบัน</div>
                    <?= FileManagerHelper::FileUpload($model->ref, 'purchase_request', true) ?>
                </div>

                <?php if (!empty($model->legacy_ref)): ?>
                    <div class="border-top pt-4">
                        <div class="text-muted small mb-2">ไฟล์เดิมจากระบบเก่า</div>
                        <?= FileManagerHelper::FileUpload($model->legacy_ref, null, true) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm rounded-4 mb-4 sticky-top" id="req-approval">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">การดำเนินการ</h5>
                <div class="d-grid gap-2">
                    <?php if ($model->canEdit() || Yii::$app->user->can('admin')): ?>
                        <?= Html::a('<i data-lucide="file-pen-line" class="me-1"></i> แก้ไข', ['/purchase-v2/request/update', 'id' => $model->id], [
                            'class' => 'btn btn-outline-primary rounded-3 fw-semibold open-modal',
                            'data' => ['size' => 'modal-xl'],
                        ]) ?>
                    <?php endif; ?>

                    <?php if ($model->canSubmit() && ($isOwner || $canManage)): ?>
                        <?= Html::a('<i data-lucide="send" class="me-1"></i> ส่งอนุมัติ', ['/purchase-v2/request/submit', 'id' => $model->id], [
                            'class' => 'btn btn-primary rounded-3 fw-semibold',
                            'data' => ['method' => 'post', 'confirm' => 'ยืนยันการส่งคำขออนุมัติ?'],
                        ]) ?>
                    <?php endif; ?>

                    <?php if ($currentApproval && $me && (int) $currentApproval->approver_emp_id === (int) $me->id && $currentApproval->status === PurchaseRequestApproval::STATUS_PENDING): ?>
                        <?= Html::a('<i data-lucide="badge-check" class="me-1"></i> อนุมัติขั้นตอนนี้', ['/purchase-v2/request/approve', 'id' => $currentApproval->id], [
                            'class' => 'btn btn-success rounded-3 fw-semibold open-modal',
                            'data' => ['size' => 'modal-md'],
                        ]) ?>
                    <?php endif; ?>

                    <?php if ((int) $model->status >= PurchaseRequest::STATUS_APPROVED && (int) $model->status < PurchaseRequest::STATUS_ORDERED && $canManage): ?>
                        <?= Html::a('<i data-lucide="file-signature" class="me-1"></i> ออกใบสั่งซื้อ', ['/purchase-v2/request/mark-ordered', 'id' => $model->id], [
                            'class' => 'btn btn-outline-primary rounded-3 fw-semibold',
                            'data' => ['method' => 'post', 'confirm' => 'ยืนยันการเปลี่ยนสถานะเป็นออกใบสั่งซื้อ?'],
                        ]) ?>
                    <?php endif; ?>

                    <?php if ((int) $model->status >= PurchaseRequest::STATUS_ORDERED && (int) $model->status < PurchaseRequest::STATUS_RECEIVED && $canManage): ?>
                        <?= Html::a('<i data-lucide="package-search" class="me-1"></i> ตรวจรับ', ['/purchase-v2/request/mark-received', 'id' => $model->id], [
                            'class' => 'btn btn-outline-primary rounded-3 fw-semibold',
                            'data' => ['method' => 'post', 'confirm' => 'ยืนยันการเปลี่ยนสถานะเป็นตรวจรับ?'],
                        ]) ?>
                    <?php endif; ?>

                    <?php if ((int) $model->status >= PurchaseRequest::STATUS_RECEIVED && (int) $model->status < PurchaseRequest::STATUS_STOCKED && $canManage): ?>
                        <?= Html::a('<i data-lucide="warehouse" class="me-1"></i> เข้าคลัง', ['/purchase-v2/request/mark-stocked', 'id' => $model->id], [
                            'class' => 'btn btn-outline-primary rounded-3 fw-semibold',
                            'data' => ['method' => 'post', 'confirm' => 'ยืนยันการเปลี่ยนสถานะเป็นเข้าคลัง?'],
                        ]) ?>
                    <?php endif; ?>

                    <?php if ((int) $model->status >= PurchaseRequest::STATUS_STOCKED && (int) $model->status < PurchaseRequest::STATUS_COMPLETED && $canManage): ?>
                        <?= Html::a('<i data-lucide="circle-check-big" class="me-1"></i> ปิดงาน', ['/purchase-v2/request/mark-completed', 'id' => $model->id], [
                            'class' => 'btn btn-outline-success rounded-3 fw-semibold',
                            'data' => ['method' => 'post', 'confirm' => 'ยืนยันการปิดงาน?'],
                        ]) ?>
                    <?php endif; ?>

                    <?php if ($model->canCancel() && ($isOwner || $canManage)): ?>
                        <?= Html::a('<i data-lucide="ban" class="me-1"></i> ยกเลิก', ['/purchase-v2/request/cancel', 'id' => $model->id], [
                            'class' => 'btn btn-outline-danger rounded-3 fw-semibold',
                            'data' => ['method' => 'post', 'confirm' => 'ยืนยันการยกเลิกรายการ?'],
                        ]) ?>
                    <?php endif; ?>

                    <?= Html::a('<i data-lucide="arrow-left" class="me-1"></i> กลับรายการ', ['/purchase-v2/request/index'], [
                        'class' => 'btn btn-outline-secondary rounded-3 fw-semibold',
                    ]) ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <h5 class="mb-0 fw-bold">Approval Timeline</h5>
                <div class="text-muted small">แสดงลำดับการอนุมัติและผู้รับผิดชอบแต่ละขั้น</div>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <?php foreach ($model->approvals as $approval): ?>
                        <?php
                        $approvalMeta = $approval->statusMeta();
                        $approvalDotClass = match ($approval->status) {
                            PurchaseRequestApproval::STATUS_NONE => 'bg-secondary text-secondary',
                            PurchaseRequestApproval::STATUS_APPROVED => 'bg-success text-success',
                            PurchaseRequestApproval::STATUS_REJECTED => 'bg-danger text-danger',
                            PurchaseRequestApproval::STATUS_PENDING => 'bg-warning text-warning',
                            PurchaseRequestApproval::STATUS_INFO => 'bg-info text-info',
                            default => 'bg-secondary text-secondary',
                        };
                        ?>
                        <div class="d-flex gap-3 align-items-start">
                            <div class="rounded-circle bg-opacity-10 p-2 flex-shrink-0 <?= $approvalDotClass ?>">
                                <i data-lucide="<?= Html::encode($approvalMeta['icon'] ?? 'circle') ?>"></i>
                            </div>
                            <div class="flex-grow-1 border rounded-4 p-3 bg-body-tertiary">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div>
                                        <div class="fw-semibold">#<?= number_format((int) $approval->step_no, 0) ?> <?= Html::encode($approval->role_name ?: $approval->step_type) ?></div>
                                        <div class="text-muted small"><?= Html::encode($approval->approver_name ?: '-') ?></div>
                                        <div class="text-muted small"><?= Html::encode($approval->approver_position ?: '-') ?></div>
                                    </div>
                                    <?= $approval->statusBadge() ?>
                                </div>

                                <?php if (!empty($approval->comment)): ?>
                                    <div class="text-muted small mb-2">หมายเหตุ: <?= Html::encode($approval->comment) ?></div>
                                <?php endif; ?>

                                <?php $approvalDate = $approval->viewApproveDate(); ?>
                                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
                                    <div class="text-muted small">
                                        <?= Html::encode($approvalMeta['label'] ?? '-') ?>
                                        <?php if ($approvalDate): ?>
                                            | <?= Html::encode($approvalDate) ?>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($approval->status === PurchaseRequestApproval::STATUS_PENDING && $me && (int) $approval->approver_emp_id === (int) $me->id): ?>
                                        <?= Html::a('<i data-lucide="badge-check" class="me-1"></i> เปิดอนุมัติ', ['/purchase-v2/request/approve', 'id' => $approval->id], [
                                            'class' => 'btn btn-sm btn-outline-primary rounded-3 fw-semibold open-modal',
                                            'data' => ['size' => 'modal-md'],
                                        ]) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($model->approvals)): ?>
                        <div class="text-center text-muted py-4">ยังไม่มีขั้นตอนอนุมัติ</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4" id="req-logs">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <h5 class="mb-0 fw-bold">ประวัติการดำเนินการ</h5>
                <div class="text-muted small">บันทึกการเปลี่ยนสถานะและเหตุการณ์ที่เกิดขึ้นกับคำขอ</div>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <?php foreach ($model->logs as $log): ?>
                        <div class="border rounded-4 p-3 bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-semibold"><?= Html::encode($log->action) ?></div>
                                    <div class="text-muted small"><?= Html::encode($log->message) ?></div>
                                </div>
                                <span class="badge bg-light text-dark border rounded-pill"><?= Html::encode($log->created_at) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($model->logs)): ?>
                        <div class="text-center text-muted py-4">ยังไม่มีประวัติ</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

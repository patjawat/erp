<?php

use yii\helpers\Html;
use app\modules\purchaseV2\models\PurchaseRequest;

$this->title = 'จัดซื้อจัดจ้าง V2';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/purchaseV2/menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column flex-lg-row justify-content-between gap-3 mb-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-2">
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                Procurement Command Center
            </span>
            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">
                ภาพรวมงานจัดซื้อจัดจ้าง
            </span>
        </div>
        <h4 class="fw-bold mb-1"><?= Html::encode($this->title) ?></h4>
        <div class="text-muted">
            ภาพรวมงานค้าง อนุมัติ งบประมาณ และงานที่กำลังเดินอยู่ในหน้าจอเดียว
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-start align-items-lg-center">
        <?= Html::a('<i data-lucide="circle-plus" class="me-1"></i> สร้างคำขอ', ['/purchase-v2/request/create'], [
            'class' => 'btn btn-primary rounded-3 fw-semibold open-modal',
            'data' => ['size' => 'modal-xl'],
        ]) ?>
        <?= Html::a('<i data-lucide="clipboard-list" class="me-1"></i> Worklist', ['/purchase-v2/request/index'], [
            'class' => 'btn btn-outline-primary rounded-3 fw-semibold',
        ]) ?>
    </div>
</div>
<?php $this->endBlock(); ?>

<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">แบบร่าง</div>
                        <div class="fs-3 fw-bold text-secondary"><?= number_format($draftCount, 0) ?></div>
                    </div>
                    <div class="bg-secondary bg-opacity-10 text-secondary rounded-4 p-3">
                        <i data-lucide="file-pen-line"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">รออนุมัติ</div>
                        <div class="fs-3 fw-bold text-warning"><?= number_format($pendingApprovalCount, 0) ?></div>
                    </div>
                    <div class="bg-warning bg-opacity-10 text-warning rounded-4 p-3">
                        <i data-lucide="hourglass"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">กำลังดำเนินการ</div>
                        <div class="fs-3 fw-bold text-primary"><?= number_format($processingCount, 0) ?></div>
                    </div>
                    <div class="bg-primary bg-opacity-10 text-primary rounded-4 p-3">
                        <i data-lucide="refresh-cw"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">เสร็จสิ้น</div>
                        <div class="fs-3 fw-bold text-success"><?= number_format($completedCount, 0) ?></div>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success rounded-4 p-3">
                        <i data-lucide="circle-check-big"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">ยกเลิก</div>
                        <div class="fs-3 fw-bold text-danger"><?= number_format($cancelledCount, 0) ?></div>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger rounded-4 p-3">
                        <i data-lucide="ban"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-4 col-xxl-2">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between gap-3">
                    <div>
                        <div class="text-muted small">งบใช้ไป</div>
                        <div class="fs-4 fw-bold text-info"><?= number_format((float) $budgetUsed, 2) ?></div>
                        <div class="text-muted small">บาท</div>
                    </div>
                    <div class="bg-info bg-opacity-10 text-info rounded-4 p-3">
                        <i data-lucide="wallet"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-12 col-xxl-7">
        <div class="card border-0 shadow-sm rounded-4 h-100 overflow-hidden">
            <div class="card-header bg-white border-bottom px-4 py-3 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
                <div>
                    <h5 class="mb-0 fw-bold">คำขอจัดซื้อล่าสุด</h5>
                    <div class="text-muted small">เปิดรายละเอียดและทำงานต่อจากการ์ดแต่ละใบได้ทันที</div>
                </div>
                <?= Html::a('<i data-lucide="clipboard-list" class="me-1"></i> ดูรายการทั้งหมด', ['/purchase-v2/request/index'], [
                    'class' => 'btn btn-outline-primary rounded-3 fw-semibold',
                ]) ?>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <?php foreach ($recentRequests as $model): ?>
                        <?php
                        $requester = $model->requesterSummary();
                        $departmentSummary = $model->departmentSummary();
                        $currentApproval = $model->currentApproval;
                        ?>
                        <div class="card border rounded-4 shadow-sm">
                            <div class="card-body p-4">
                                <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                    <div class="flex-grow-1">
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2 fw-semibold">
                                                <?= Html::encode($model->getDisplayReference()) ?>
                                            </span>
                                            <?= $model->statusBadge() ?>
                                            <span class="badge bg-light text-dark border rounded-pill px-3 py-2 fw-semibold">
                                                <?= Html::encode($model->requestTypeLabel()) ?>
                                            </span>
                                        </div>

                                        <h6 class="fw-bold mb-1"><?= Html::encode($model->request_title) ?></h6>
                                        <div class="text-muted mb-0"><?= Html::encode($model->summary ?: '-') ?></div>

                                        <div class="row g-3 mt-3">
                                            <div class="col-12 col-md-4">
                                                <div class="p-3 rounded-4 bg-body-tertiary h-100">
                                                    <div class="text-muted small">ผู้ขอ</div>
                                                    <div class="fw-semibold"><?= Html::encode($requester['fullname']) ?></div>
                                                    <div class="text-muted small"><?= Html::encode($requester['department']) ?></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="p-3 rounded-4 bg-body-tertiary h-100">
                                                    <div class="text-muted small">หน่วยงาน</div>
                                                    <div class="fw-semibold"><?= Html::encode($departmentSummary['name']) ?></div>
                                                    <div class="text-muted small">ปีงบประมาณ <?= Html::encode($model->budget_year ?: '-') ?></div>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="p-3 rounded-4 bg-body-tertiary h-100">
                                                    <div class="text-muted small">ขั้นปัจจุบัน</div>
                                                    <div class="fw-semibold"><?= Html::encode($currentApproval?->role_name ?: 'ยังไม่มีงานค้าง') ?></div>
                                                    <div class="text-muted small">งบ <?= number_format((float) $model->grand_total, 2) ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-lg-end">
                                        <div class="text-muted small">ยอดรวมสุทธิ</div>
                                        <div class="display-6 fw-bold text-primary mb-1"><?= number_format((float) $model->grand_total, 2) ?></div>
                                        <div class="text-muted small">งบประมาณ <?= number_format((float) $model->budget_amount, 2) ?></div>
                                        <?php if (!empty($model->legacy_ref)): ?>
                                            <div class="mt-2">
                                                <span class="badge bg-light text-secondary border rounded-pill">
                                                    นำเข้า: <?= Html::encode($model->legacy_ref) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <?= Html::a('<i data-lucide="eye" class="me-1"></i> ดูรายละเอียด', ['/purchase-v2/request/view', 'id' => $model->id], [
                                        'class' => 'btn btn-outline-primary btn-sm rounded-3 fw-semibold open-modal',
                                        'data' => ['size' => 'modal-xl'],
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($recentRequests)): ?>
                        <div class="text-center text-muted py-5">
                            <div class="bg-body-tertiary rounded-circle d-inline-flex align-items-center justify-content-center p-4 mb-3">
                                <i data-lucide="clipboard-list" class="fs-3"></i>
                            </div>
                            <div class="fw-semibold">ยังไม่มีรายการล่าสุด</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xxl-5">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <h5 class="mb-0 fw-bold">รออนุมัติ</h5>
                <div class="text-muted small">รายการที่รอผู้เกี่ยวข้องดำเนินการต่อ</div>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <?php foreach ($currentApprovals as $approval): ?>
                        <?php $request = $approval->request; ?>
                        <div class="border rounded-4 p-3 bg-body-tertiary">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="fw-semibold">
                                        <?= Html::a(Html::encode($request?->getDisplayReference() ?? '-'), ['/purchase-v2/request/view', 'id' => $request?->id], [
                                            'class' => 'text-decoration-none open-modal',
                                            'data' => ['size' => 'modal-xl'],
                                        ]) ?>
                                    </div>
                                    <div class="text-muted small"><?= Html::encode($approval->role_name ?: 'ขั้นอนุมัติ') ?></div>
                                    <div class="text-muted small"><?= Html::encode($request?->request_title ?: '-') ?></div>
                                </div>
                                <?= $approval->statusBadge() ?>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($currentApprovals)): ?>
                        <div class="text-center text-muted py-4">ไม่มีรายการรออนุมัติ</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <h5 class="mb-0 fw-bold">สถานะระบบ</h5>
                <div class="text-muted small">ภาพรวมสัญญาณการทำงานของระบบจัดซื้อจัดจ้าง</div>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-3">
                    <div>
                        <div class="d-flex justify-content-between mb-1">
                            <span class="text-muted">รอดำเนินการทั้งหมด</span>
                            <span class="fw-semibold"><?= number_format($draftCount + $pendingApprovalCount, 0) ?></span>
                        </div>
                        <div class="progress rounded-pill" style="height: .65rem;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?= max(5, min(100, (int) (($pendingApprovalCount + $processingCount) / max(1, $draftCount + $pendingApprovalCount + $processingCount + $completedCount) * 100))) ?>%;" aria-valuenow="<?= max(5, min(100, (int) (($pendingApprovalCount + $processingCount) / max(1, $draftCount + $pendingApprovalCount + $processingCount + $completedCount) * 100))) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>ค้าง/รออนุมัติ</span>
                        <span>เสร็จสิ้น <?= number_format($completedCount, 0) ?> รายการ</span>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span>ยกเลิก</span>
                        <span><?= number_format($cancelledCount, 0) ?> รายการ</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-white border-bottom px-4 py-3">
                <h5 class="mb-0 fw-bold">Quick Actions</h5>
                <div class="text-muted small">เข้าสู่การทำงานที่ใช้บ่อยได้ทันที</div>
            </div>
            <div class="card-body p-4">
                <div class="d-grid gap-2">
                    <?= Html::a('<i data-lucide="circle-plus" class="me-1"></i> สร้างคำขอใหม่', ['/purchase-v2/request/create'], [
                        'class' => 'btn btn-primary rounded-3 fw-semibold open-modal',
                        'data' => ['size' => 'modal-xl'],
                    ]) ?>
                    <?= Html::a('<i data-lucide="clipboard-list" class="me-1"></i> เปิด Worklist', ['/purchase-v2/request/index'], [
                        'class' => 'btn btn-outline-primary rounded-3 fw-semibold',
                    ]) ?>
                    <?php if (Yii::$app->user->can('admin') || Yii::$app->user->can('purchase')): ?>
                        <?= Html::a('<i data-lucide="database-zap" class="me-1"></i> ย้ายข้อมูลเดิม', ['/purchase-v2/migration/index'], [
                            'class' => 'btn btn-outline-secondary rounded-3 fw-semibold',
                        ]) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

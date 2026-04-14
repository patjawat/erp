<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\approveV2\models\Approve[] $approvals */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle'] = 'อนุมัติใบลา';
$this->params['mobileSubtitle'] = 'ตรวจสอบรายการที่รอการพิจารณาจากคุณ';
?>

<div class="d-flex flex-column gap-3">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4 d-flex justify-content-between align-items-center gap-3">
            <div>
                <div class="small text-muted">รายการที่รออนุมัติ</div>
                <div class="fw-semibold text-dark"><?= count($approvals) ?> รายการ</div>
            </div>
            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill px-3 py-2">Pending</span>
        </div>
    </div>

    <?php if (empty($approvals)): ?>
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-5 text-center text-muted">
                <div class="fw-semibold text-dark mb-2">ไม่มีใบลาที่รออนุมัติ</div>
                <div class="small">เมื่อมีคำขอเข้ามา รายการจะปรากฏที่หน้านี้</div>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($approvals as $approve): ?>
            <?php
            $leave = $approve->leave;
            if (!$leave) {
                continue;
            }
            $detailUrl = Url::to(['/mobile/default/approve-leave', 'id' => $approve->id]);
            $requesterName = $leave->employee->fullname ?? '-';
            $leaveType = $leave->leaveType->title ?? 'ใบลา';
            $dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
            $reason = trim((string) ($leave->data_json['reason'] ?? ''));
            $levelLabel = $approve->data_json['label'] ?? $approve->title ?? 'ผู้อนุมัติ';
            ?>
            <a href="<?= Html::encode($detailUrl) ?>" class="card border-0 shadow-sm rounded-4 text-decoration-none text-body">
                <div class="card-body p-4 d-flex flex-column gap-3">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div class="min-w-0">
                            <div class="small text-muted">ผู้ขอ</div>
                            <div class="fw-semibold text-dark"><?= Html::encode($requesterName) ?></div>
                            <div class="small text-body-secondary"><?= Html::encode($levelLabel) ?></div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2">รออนุมัติ</span>
                    </div>

                    <div class="row g-3 small">
                        <div class="col-6">
                            <div class="text-muted">ประเภทการลา</div>
                            <div class="fw-semibold text-dark"><?= Html::encode($leaveType) ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted">จำนวนวัน</div>
                            <div class="fw-semibold text-dark"><?= (float) $leave->total_days ?> วัน</div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted">ช่วงเวลาที่ลา</div>
                            <div class="fw-semibold text-dark"><?= Html::encode($dateRange !== '' ? $dateRange : '-') ?></div>
                        </div>
                        <div class="col-12">
                            <div class="text-muted">เหตุผล</div>
                            <div class="fw-semibold text-dark"><?= Html::encode($reason !== '' ? $reason : '-') ?></div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-3 mt-1 border-top">
                        <span class="small text-primary fw-medium">เปิดรายละเอียดเพื่ออนุมัติ</span>
                        <i class="bi bi-chevron-right text-secondary"></i>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

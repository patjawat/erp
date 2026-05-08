<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\Leave $model */

$me = UserHelper::GetEmployee();
$author = $model->getAvatar($model->emp_id, '');
$authorAvatarUrl = $author['avatar_url'] ?? null;
$authorName = $author['fullname'] ?? ($model->employee->fullname ?? '');
$authorDept = $author['department'] ?? ($model->employee ? $model->employee->departmentName() : '');
$authorPosition = $author['position_name'] ?? ($model->employee ? $model->employee->positionName() : '');
$calDays = isset($model->data_json['summary_calendar_days']) ? (int) $model->data_json['summary_calendar_days'] : null;
$satSun = isset($model->data_json['summary_sat_sun']) ? (int) $model->data_json['summary_sat_sun'] : null;
$holiday = isset($model->data_json['summary_holiday']) ? (int) $model->data_json['summary_holiday'] : null;
$attachments = $model->getAttachmentList();
$signatureData = isset($model->data_json['signature_data']) ? trim((string) $model->data_json['signature_data']) : '';
$signatureType = $model->data_json['signature_type'] ?? 'canvas';
$substitute = $model->leaveWorkSend();
$leaveWrittenAt = null;
$leaveWrittenDaysAgo = null;
if (!empty($model->created_at)) {
    $leaveWrittenAt = ThaiDateHelper::formatThaiDate($model->created_at, 'long')
        . ' ' . date('H:i', strtotime((string) $model->created_at)) . ' น.';
    $createdDt = new \DateTime((string) $model->created_at);
    $leaveWrittenDaysAgo = (int) $createdDt->diff(new \DateTime())->days;
}
$leavePermission = $model->sumLeavePermission();
$leavePermissionSum = $leavePermission['sum'] ?? 0;
$leavePermissionTotal = $leavePermission['total'] ?? 0;

$this->registerCss(<<<CSS
.leave-detail-page {
    --leave-shell-border: #eef2f7;
    --leave-shell-shadow: 0 .35rem 1.25rem rgba(15, 23, 42, .04);
}

.leave-detail-card {
    border: 1px solid var(--leave-shell-border) !important;
    border-radius: 18px !important;
    box-shadow: var(--leave-shell-shadow) !important;
    overflow: hidden;
    background: #fff;
}

.leave-detail-card > .card-header {
    background: #fff !important;
    border-bottom: 1px solid var(--leave-shell-border) !important;
    padding: .9rem 1rem !important;
}

.leave-detail-card > .card-body {
    padding: 1rem !important;
}

.leave-detail-card .card-header h6 {
    font-size: .92rem;
    font-weight: 700;
    color: #1f2937;
}

.leave-detail-card .card-header h6 i {
    width: 30px;
    height: 30px;
    border-radius: 10px;
    background: rgba(13, 110, 253, .1);
    color: #0d6efd;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.leave-section-label {
    display: flex;
    align-items: flex-start;
    gap: .75rem;
}

.leave-section-label__icon {
    width: 36px;
    height: 36px;
    border-radius: 12px;
    background: rgba(13, 110, 253, .1);
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.leave-section-label__title {
    font-size: .95rem;
    font-weight: 700;
    line-height: 1.2;
    color: #111827;
}

.leave-section-label__subtitle {
    font-size: .8rem;
    color: #6c757d;
    margin-top: .15rem;
}

.leave-detail-hero {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1rem;
    flex-wrap: wrap;
}

.leave-detail-hero__identity {
    display: flex;
    align-items: center;
    gap: .9rem;
    min-width: 0;
    flex: 1 1 320px;
}

.leave-detail-hero__avatar {
    width: 58px;
    height: 58px;
    border-radius: 18px;
    overflow: hidden;
    border: 1px solid #dbe4f0;
    background: #f8fafc;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.leave-detail-hero__name {
    font-size: 1rem;
    font-weight: 800;
    line-height: 1.2;
    color: #111827;
}

.leave-detail-hero__meta {
    display: flex;
    flex-wrap: wrap;
    gap: .35rem .8rem;
    margin-top: .25rem;
    color: #6c757d;
    font-size: .875rem;
}

.leave-detail-hero__meta-item {
    display: inline-flex;
    align-items: center;
    gap: .35rem;
}

.leave-detail-hero__aside {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: .5rem;
    flex: 0 0 auto;
    text-align: right;
}

.leave-detail-note {
    margin-top: 1rem;
    padding: .85rem 1rem;
    border: 1px solid #eef2f7;
    border-radius: 14px;
    background: linear-gradient(180deg, rgba(248, 250, 252, .9), rgba(255, 255, 255, .95));
    display: flex;
    justify-content: space-between;
    gap: .75rem;
    align-items: center;
}

.leave-detail-note__label {
    font-size: .78rem;
    color: #6c757d;
}

.leave-detail-note__value {
    font-weight: 600;
    color: #111827;
    word-break: break-word;
}

.leave-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: .75rem;
}

.leave-summary-tile {
    border: 1px solid #eef2f7;
    border-radius: 14px;
    padding: .9rem .75rem;
    background: #f8fafc;
    min-height: 84px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
}

.leave-summary-tile--accent {
    background: rgba(13, 110, 253, .08);
    border-color: rgba(13, 110, 253, .16);
}

.leave-summary-tile__label {
    font-size: .78rem;
    color: #6c757d;
    line-height: 1.3;
}

.leave-summary-tile__value {
    font-size: 1rem;
    font-weight: 700;
    color: #111827;
    margin-top: .15rem;
}

.leave-attachment-list {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: .65rem;
}

.leave-attachment-link {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding: .82rem .95rem;
    border: 1px solid #eef2f7;
    border-radius: 14px;
    background: #fff;
    text-decoration: none;
    transition: all .2s ease;
}

.leave-attachment-link:hover {
    transform: translateY(-1px);
    border-color: rgba(13, 110, 253, .25);
    box-shadow: 0 .5rem 1rem rgba(15, 23, 42, .05);
}

.leave-attachment-link__left {
    display: flex;
    align-items: center;
    gap: .7rem;
    min-width: 0;
}

.leave-attachment-link__icon {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    background: rgba(13, 110, 253, .1);
    color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.leave-attachment-link__name {
    font-size: .92rem;
    font-weight: 600;
    color: #111827;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.leave-attachment-link__action {
    color: #94a3b8;
    flex-shrink: 0;
}

.leave-contact-row {
    display: grid;
    grid-template-columns: 110px minmax(0, 1fr);
    gap: .75rem;
    padding: .75rem 0;
    border-bottom: 1px dashed #eef2f7;
}

.leave-contact-row:last-child {
    border-bottom: 0;
}

.leave-contact-row__label {
    color: #6c757d;
    font-size: .8rem;
    display: flex;
    align-items: center;
    gap: .4rem;
}

.leave-contact-row__value {
    font-weight: 600;
    color: #111827;
    word-break: break-word;
}

.leave-detail-card.bg-primary {
    border-color: rgba(13, 110, 253, .2) !important;
}

@media (max-width: 991.98px) {
    .leave-summary-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .leave-detail-hero__aside {
        align-items: flex-start;
        text-align: left;
    }
}

@media (max-width: 575.98px) {
    .leave-detail-hero {
        flex-direction: column;
    }

    .leave-detail-hero__identity {
        flex-direction: row;
        align-items: flex-start;
    }

    .leave-detail-hero__aside {
        width: 100%;
    }

    .leave-detail-note {
        flex-direction: column;
        align-items: flex-start;
    }

    .leave-summary-grid {
        grid-template-columns: 1fr;
    }

    .leave-contact-row {
        grid-template-columns: 1fr;
    }

    .leave-attachment-link {
        align-items: flex-start;
    }

    .leave-attachment-link__name {
        white-space: normal;
    }
}
CSS);
?>
<div class="row g-4 align-items-start leave-detail-page">
    <!-- คอลัมน์ซ้าย: ข้อมูลหลัก -->
    <div class="col-lg-7">
        <div class="d-flex flex-column gap-4">

            <!-- บัตรผู้ขอลา + สถานะ -->
            <div class="card leave-detail-card">
                <div class="card-header">
                    <div class="leave-detail-hero">
                        <div>
                            <div class="leave-section-label">
                                <span class="leave-section-label__icon"><i class="bi bi-person-vcard"></i></span>
                                <div>
                                    <div class="leave-section-label__title">ผู้ขอลา</div>
                                    <div class="leave-section-label__subtitle">ข้อมูลผู้ยื่นคำขอและสถานะปัจจุบัน</div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap justify-content-end">
                            <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary border border-primary-subtle px-3 py-2">
                                <?= Html::encode($model->leaveType ? $model->leaveType->title : '-') ?>
                            </span>
                            <?= $model->viewStatus() ?>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="leave-detail-hero">
                        <div class="leave-detail-hero__identity">
                            <div class="leave-detail-hero__avatar">
                                <?php if (!empty($authorAvatarUrl)): ?>
                                    <?= Html::img($authorAvatarUrl, ['alt' => $authorName, 'class' => 'w-100 h-100 object-fit-cover']) ?>
                                <?php else: ?>
                                    <i class="bi bi-person fs-4 text-muted"></i>
                                <?php endif; ?>
                            </div>
                            <div style="min-width: 0;">
                                <div class="leave-detail-hero__name"><?= Html::encode($authorName) ?></div>
                                <div class="leave-detail-hero__meta">
                                    <?php if ($authorPosition !== ''): ?>
                                        <span class="leave-detail-hero__meta-item"><i class="bi bi-briefcase text-primary"></i> <?= Html::encode($authorPosition) ?></span>
                                    <?php endif; ?>
                                    <?php if ($authorDept !== ''): ?>
                                        <span class="leave-detail-hero__meta-item"><i class="bi bi-building text-primary"></i> <?= Html::encode($authorDept) ?></span>
                                    <?php endif; ?>
                                    <span class="leave-detail-hero__meta-item"><i class="bi bi-calendar2-event text-primary"></i> <?= (float) $model->total_days ?> วัน</span>
                                </div>
                            </div>
                        </div>
                        <div class="leave-detail-hero__aside">
                            <span class="badge rounded-pill bg-success bg-opacity-10 text-success border border-success-subtle px-3 py-2">
                                <?= (float) $model->total_days ?> วัน
                            </span>
                            <?php if ($leaveWrittenDaysAgo !== null): ?>
                                <div class="small text-muted">เขียนมาแล้ว <?= $leaveWrittenDaysAgo ?> วัน</div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($leaveWrittenAt !== null): ?>
                        <div class="leave-detail-note">
                            <div>
                                <div class="leave-detail-note__label">วันเวลาที่เขียนใบลา</div>
                                <div class="leave-detail-note__value"><?= Html::encode($leaveWrittenAt) ?></div>
                            </div>
                            <?php if ($leaveWrittenDaysAgo !== null): ?>
                                <span class="badge rounded-pill bg-light text-muted border border-light-subtle px-3 py-2">ผ่านมา <?= $leaveWrittenDaysAgo ?> วัน</span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- รายละเอียดการลา -->
            <div class="card leave-detail-card">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-calendar-check text-primary"></i>
                        รายละเอียดการลา
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">ประเภทการลา</div>
                            <div class="fw-semibold text-body"><?= $model->leaveType ? Html::encode($model->leaveType->title) : '-' ?></div>
                        </div>
                        <div class="col-sm-6">
                            <div class="small text-muted mb-1">จำนวนวัน</div>
                            <div class="fw-semibold text-body"><?= (float) $model->total_days ?> วัน</div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted mb-1">ช่วงเวลาที่ลา</div>
                            <div class="fw-medium text-body"><?= $model->showLeaveDate() ?></div>
                        </div>
                        <div class="col-12">
                            <div class="small text-muted mb-1">เหตุผลการลา</div>
                            <div class="fw-medium text-body"><?= Html::encode($model->data_json['reason'] ?? '-') ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- เอกสารแนบ / ใบรับรองแพทย์ -->
            <div class="card leave-detail-card">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-medical text-primary"></i>
                        เอกสารแนบ / ใบรับรองแพทย์
                    </h6>
                </div>
                <div class="card-body p-3">
                    <?php if (!empty($attachments)): ?>
                        <ul class="leave-attachment-list">
                            <?php foreach ($attachments as $att): ?>
                                <li>
                                    <a href="<?= Html::encode(Url::to(['/leave/leave/show-file', 'id' => $att->id])) ?>" class="leave-attachment-link" data-url="<?= Html::encode(Url::to(['/leave/leave/show-file', 'id' => $att->id])) ?>" data-open="new-tab" target="_blank" rel="noopener noreferrer" title="คลิกเพื่อเปิดแท็บใหม่">
                                        <span class="leave-attachment-link__left">
                                            <span class="leave-attachment-link__icon"><i class="bi bi-file-earmark-arrow-down"></i></span>
                                            <span class="leave-attachment-link__name"><?= Html::encode($att->file_name) ?></span>
                                        </span>
                                        <span class="leave-attachment-link__action"><i class="bi bi-box-arrow-up-right"></i></span>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-center text-muted py-4">
                            <i class="bi bi-inbox d-block fs-3 mb-2 opacity-50"></i>
                            <small>ไม่มีไฟล์แนบใบรับรองแพทย์</small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- สรุปวันลา -->
            <div class="card leave-detail-card">
                <div class="card-header bg-info bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-calendar3 text-info"></i>
                        สรุปวันลา
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="leave-summary-grid">
                        <div class="leave-summary-tile">
                            <div class="leave-summary-tile__label">รวมระยะเวลา</div>
                            <div class="leave-summary-tile__value"><?= $calDays !== null ? $calDays . ' วัน' : '-' ?></div>
                        </div>
                        <div class="leave-summary-tile">
                            <div class="leave-summary-tile__label">วันเสาร์-อาทิตย์</div>
                            <div class="leave-summary-tile__value"><?= $satSun !== null ? $satSun . ' วัน' : '-' ?></div>
                        </div>
                        <div class="leave-summary-tile">
                            <div class="leave-summary-tile__label">วันหยุดนักขัตฤกษ์</div>
                            <div class="leave-summary-tile__value"><?= $holiday !== null ? $holiday . ' วัน' : '-' ?></div>
                        </div>
                        <div class="leave-summary-tile leave-summary-tile--accent">
                            <div class="leave-summary-tile__label">รวมวันลา</div>
                            <div class="leave-summary-tile__value text-primary"><?= (float) $model->total_days ?> วัน</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ข้อมูลติดต่อ -->
            <div class="card leave-detail-card">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-telephone text-primary"></i>
                        ข้อมูลการติดต่อ
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="leave-contact-row">
                        <div class="leave-contact-row__label"><i class="bi bi-geo-alt text-primary"></i> ที่อยู่</div>
                        <div class="leave-contact-row__value"><?= Html::encode($model->data_json['address'] ?? '-') ?></div>
                    </div>
                    <div class="leave-contact-row">
                        <div class="leave-contact-row__label"><i class="bi bi-telephone text-primary"></i> โทรศัพท์</div>
                        <div class="leave-contact-row__value"><?= Html::encode($model->data_json['phone'] ?? $model->data_json['leave_contact_phone'] ?? '-') ?></div>
                    </div>
                </div>
            </div>

            <?php if ($signatureData !== ''): ?>
                <?php $signatureLabel = ($signatureType === 'system') ? 'ใช้ในระบบ' : 'เซ็นเอง'; ?>
                <div class="card leave-detail-card">
                    <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                        <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                            <i class="bi bi-pen text-primary"></i>
                            ลายเซ็นผู้ขอลา
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($signatureLabel) ?></span>
                        </h6>
                    </div>
                    <div class="card-body p-3 text-center">
                        <div class="bg-white border rounded-3 p-2 d-inline-block">
                            <img src="<?= Html::encode($signatureData) ?>" alt="ลายเซ็น" class="d-block img-fluid">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($substitute)): ?>
                <div class="card leave-detail-card">
                    <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                        <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                            <i class="bi bi-person-check text-primary"></i>
                            ผู้ปฏิบัติหน้าที่แทน
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center gap-3">
                            <?= $substitute->getAvatar(false, 'ผู้ปฏิบัติหน้าที่แทน') ?>
                            <div>
                                <div class="fw-bold text-body"><?= Html::encode($substitute->fullname) ?></div>
                                <div class="small text-muted"><?= Html::encode($substitute->positionName()) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- คอลัมน์ขวา: สิทธิคงเหลือ + สถิติการลา + สถานะการอนุมัติ -->
    <div class="col-lg-5">
        <div class="d-flex flex-column gap-4">
            <div class="card leave-detail-card bg-primary text-white">
                <div class="card-body p-4 text-white">
                    <div class="small text-white opacity-75 mb-1">สิทธิคงเหลือ</div>
                    <h2 class="fw-bold mb-0 display-6 text-white">
                        <?= $leavePermissionSum ?>
                        <span class="fs-6 fw-normal text-white opacity-75">วัน</span>
                    </h2>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-25 d-flex justify-content-between align-items-center small text-white">
                        <span class="text-white opacity-75">วันลาพักผ่อนสะสม</span>
                        <span class="text-white fw-bold"><?= $leavePermissionTotal ?> วัน</span>
                    </div>
                </div>
            </div>
            <div class="card leave-detail-card">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-file-text text-primary"></i>
                        สถิติการลาในปีงบประมาณนี้ <?= (int) $model->thai_year ?>
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <?= $this->render('view_summary', ['model' => $model, 'hideHeading' => true]) ?>
                    </div>
                </div>
            </div>
            <?= $this->render('_level_approve', [
                'model' => $model,
                'listApprove' => $model->listApprove(),
                'name' => 'leave',
            ]) ?>
        </div>
    </div>

    <!-- ประวัติการดำเนินการ -->
    <div class="col-12">
        <div class="card leave-detail-card">
            <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history text-primary"></i>
                    ประวัติการดำเนินการ
                </h6>
            </div>
            <div class="card-body p-3">
                <?= $this->render('history', ['model' => $model]) ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal แสดงเอกสารแนบใน iframe + ปุ่มย้อนกลับ -->
<div class="modal fade" id="leave-attachment-modal" tabindex="-1" aria-labelledby="leave-attachment-modal-label" aria-hidden="true" data-bs-backdrop="true" data-bs-keyboard="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content rounded-0">
            <div class="modal-header border-bottom bg-primary py-2 px-3 d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-light btn-sm rounded-pill d-inline-flex align-items-center gap-1" id="leave-attachment-back-btn" aria-label="ย้อนกลับ">
                    <i class="bi bi-arrow-left"></i> ย้อนกลับ
                </button>
                <h5 class="modal-title small fw-semibold mb-0 text-white" id="leave-attachment-modal-label">เอกสารแนบ / ใบรับรองแพทย์</h5>
            </div>
            <div class="modal-body p-0 bg-dark bg-opacity-10 position-relative" style="min-height: 70vh;">
                <iframe id="leave-attachment-iframe" class="border-0 w-100 h-100 position-absolute top-0 start-0" style="min-height: 75vh;" title="แสดงเอกสารแนบ"></iframe>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerJs(
    <<<'JS'
(function() {
    var modal = document.getElementById('leave-attachment-modal');
    var iframe = document.getElementById('leave-attachment-iframe');
    var backBtn = document.getElementById('leave-attachment-back-btn');
    if (!modal || !iframe || !backBtn) return;
    function closeModal() {
        if (typeof bootstrap !== 'undefined' && modal) {
            var bModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
            bModal.hide();
        }
        iframe.src = 'about:blank';
    }
    document.querySelectorAll('.leave-attachment-link').forEach(function(link) {
        link.addEventListener('click', function(e) {
            var url = this.getAttribute('data-url') || this.getAttribute('href');
            var openMode = (this.getAttribute('data-open') || '').toLowerCase();
            if (openMode === 'new-tab') {
                return;
            }
            e.preventDefault();
            if (url) {
                iframe.src = url;
                if (typeof bootstrap !== 'undefined') {
                    var bModal = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                    bModal.show();
                } else {
                    modal.classList.add('show');
                    modal.style.display = 'block';
                    modal.setAttribute('aria-hidden', 'false');
                }
            }
        });
    });
    backBtn.addEventListener('click', closeModal);
    if (modal) {
        modal.addEventListener('hidden.bs.modal', function() { iframe.src = 'about:blank'; });
    }
})();
JS
);
?>

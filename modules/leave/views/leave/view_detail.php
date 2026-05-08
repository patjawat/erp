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
?>
<div class="row g-4">
    <!-- คอลัมน์ซ้าย: ข้อมูลหลัก -->
    <div class="col-lg-7">
        <div class="d-flex flex-column gap-3">

            <!-- บัตรผู้ขอลา + สถานะ -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-person-vcard text-primary"></i>
                        ผู้ขอลา
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle overflow-hidden border border-2 border-primary bg-body-secondary flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <?php if (!empty($authorAvatarUrl)): ?>
                                    <?= Html::img($authorAvatarUrl, ['alt' => $authorName, 'class' => 'w-100 h-100 object-fit-cover']) ?>
                                <?php else: ?>
                                    <i class="bi bi-person fs-4 text-muted"></i>
                                <?php endif; ?>
                            </div>
                            <div>
                                <div class="fw-bold text-body"><?= Html::encode($authorName) ?></div>
                                <?php if ($authorPosition !== ''): ?>
                                    <div class="small text-muted"><?= Html::encode($authorPosition) ?></div>
                                <?php endif; ?>
                                <?php if ($authorDept !== ''): ?>
                                    <div class="small text-secondary"><?= Html::encode($authorDept) ?></div>
                                <?php endif; ?>

                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <?= $model->viewStatus() ?>
                        </div>
                    </div>
                    <?php if ($leaveWrittenAt !== null): ?>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top border-secondary border-opacity-25">
                            <div class="small text-muted">วันเวลาที่เขียนใบลา</div>
                            <div class="small fw-medium text-body"><?= Html::encode($leaveWrittenAt) ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- รายละเอียดการลา -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
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

            <!-- เอกสารแนบ / ใบรับรองแพทย์ (แสดงหลังรายละเอียดการลา) — คลิกแสดงใน iframe พร้อมปุ่มย้อนกลับ -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-file-earmark-medical text-primary"></i>
                        เอกสารแนบ / ใบรับรองแพทย์
                    </h6>
                </div>
                <div class="card-body p-3">
                    <?php if (!empty($attachments)): ?>
                        <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                            <?php foreach ($attachments as $att): ?>
                                <li>
                                    <a href="<?= Html::encode(Url::to(['/leave/leave/show-file', 'id' => $att->id])) ?>" class="leave-attachment-link d-inline-flex align-items-center gap-2 text-decoration-none text-body border rounded-3 px-3 py-2 bg-body-secondary bg-opacity-50" data-url="<?= Html::encode(Url::to(['/leave/leave/show-file', 'id' => $att->id])) ?>" data-open="new-tab" target="_blank" rel="noopener noreferrer" title="คลิกเพื่อเปิดแท็บใหม่">
                                        <i class="bi bi-file-earmark-arrow-down text-primary"></i>
                                        <span class="small"><?= Html::encode($att->file_name) ?></span>
                                        <i class="bi bi-eye small text-muted"></i>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="mb-0 text-muted small"><i class="bi bi-inbox me-1"></i> ไม่มีไฟล์แนบใบรับรองแพทย์</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- สรุปวันลา -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden border-start border-3 border-info">
                <div class="card-header bg-info bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-calendar3 text-info"></i>
                        สรุปวันลา
                    </h6>
                </div>
                <div class="card-body py-3 px-3">
                    <div class="row g-2 small">
                        <div class="col-6 col-md-3 text-center p-2 rounded-2 bg-body-secondary bg-opacity-50">
                            <div class="text-muted mb-0">รวมระยะเวลา</div>
                            <div class="fw-semibold text-body"><?= $calDays !== null ? $calDays . ' วัน' : '-' ?></div>
                        </div>
                        <div class="col-6 col-md-3 text-center p-2 rounded-2 bg-body-secondary bg-opacity-50">
                            <div class="text-muted mb-0">วันเสาร์-อาทิตย์</div>
                            <div class="fw-semibold text-body"><?= $satSun !== null ? $satSun . ' วัน' : '-' ?></div>
                        </div>
                        <div class="col-6 col-md-3 text-center p-2 rounded-2 bg-body-secondary bg-opacity-50">
                            <div class="text-muted mb-0">วันหยุดนักขัตฤกษ์</div>
                            <div class="fw-semibold text-body"><?= $holiday !== null ? $holiday . ' วัน' : '-' ?></div>
                        </div>
                        <div class="col-6 col-md-3 text-center p-2 rounded-2 bg-primary bg-opacity-10">
                            <div class="text-muted mb-0">รวมวันลา</div>
                            <div class="fw-bold text-primary"><?= (float) $model->total_days ?> วัน</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ข้อมูลติดต่อ -->
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-telephone text-primary"></i>
                        ข้อมูลการติดต่อ
                    </h6>
                </div>
                <div class="card-body p-3">
                    <div class="small text-muted mb-1">ที่อยู่</div>
                    <div class="fw-medium text-body mb-2"><?= Html::encode($model->data_json['address'] ?? '-') ?></div>
                    <div class="small text-muted mb-1">โทรศัพท์</div>
                    <div class="fw-medium text-body"><?= Html::encode($model->data_json['phone'] ?? $model->data_json['leave_contact_phone'] ?? '-') ?></div>
                </div>
            </div>

            <?php if ($signatureData !== ''): ?>
                <?php $signatureLabel = ($signatureType === 'system') ? 'ใช้ในระบบ' : 'เซ็นเอง'; ?>
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                    <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                        <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                            <i class="bi bi-pen text-primary"></i>
                            ลายเซ็นผู้ขอลา
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1"><?= Html::encode($signatureLabel) ?></span>
                        </h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="bg-white border rounded-3 p-2 d-inline-block">
                            <img src="<?= Html::encode($signatureData) ?>" alt="ลายเซ็น" class="d-block img-fluid">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($substitute)): ?>
                <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
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
        <div class="d-flex flex-column gap-3">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-primary text-white">
                <div class="card-body p-4 text-white">
                    <div class="small text-white opacity-75 mb-1">สิทธิคงเหลือ</div>
                    <h2 class="fw-bold mb-0 display-6 text-white">
                        <?= $model->sumLeavePermission()['sum'] ?? 0 ?>
                        <span class="fs-6 fw-normal text-white opacity-75">วัน</span>
                    </h2>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-25 d-flex justify-content-between align-items-center small text-white">
                        <span class="text-white opacity-75">วันลาพักผ่อนสะสม</span>
                        <span class="text-white fw-bold"><?= $model->sumLeavePermission()['total'] ?? 0 ?> วัน</span>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-file-text text-primary"></i>
                        สถิติการลาในปีงบประมาณนี้ <?= (int) $model->thai_year ?>
                    </h6>
                </div>
                <div class="card-body p-3">
                    <?= $this->render('view_summary', ['model' => $model, 'hideHeading' => true]) ?>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden flex-grow-1">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-clipboard-check text-primary"></i>
                        สถานะการอนุมัติ
                    </h6>
                </div>
                <div class="card-body p-3">
                    <?= $this->render('_level_approve', [
                        'model' => $model,
                        'listApprove' => $model->listApprove(),
                        'name' => 'leave',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ประวัติการดำเนินการ -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
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

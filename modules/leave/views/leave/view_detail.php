<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\Leave $model */
/** @var string|null $previewPdfUrl URL สำหรับแสดงตัวอย่าง PDF โดยตรง (ไม่มี toolbar หน้ารูปแบบพิมพ์) */

$me = UserHelper::GetEmployee();
$author = $model->getAvatar($model->emp_id, '');
$authorAvatar = $author['avatar'] ?? '';
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
                                <?php if ($authorAvatar): ?>
                                    <?= $authorAvatar ?>
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

            <?php if (!empty($attachments)): ?>
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
                <div class="card-header bg-primary bg-opacity-10 border-0 py-2 px-3">
                    <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                        <i class="bi bi-paperclip text-primary"></i>
                        เอกสารแนบ / ใบรับรองแพทย์
                    </h6>
                </div>
                <div class="card-body p-3">
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        <?php foreach ($attachments as $att): ?>
                        <li>
                            <a href="<?= Url::to(['/filemanager/uploads/show', 'id' => $att->id]) ?>" target="_blank" rel="noopener" class="d-inline-flex align-items-center gap-2 text-decoration-none text-body border rounded-3 px-3 py-2 bg-body-secondary bg-opacity-50">
                                <i class="bi bi-file-earmark-arrow-down text-primary"></i>
                                <span class="small"><?= Html::encode($att->file_name) ?></span>
                                <i class="bi bi-box-arrow-up-right small text-muted"></i>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <?php endif; ?>

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
                <div class="card-body p-4">
                    <div class="small opacity-75 mb-1">สิทธิคงเหลือ</div>
                    <h2 class="fw-bold mb-0 display-6">
                        <?= $model->sumLeavePermission()['sum'] ?? 0 ?>
                        <span class="fs-6 fw-normal opacity-75">วัน</span>
                    </h2>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-25 d-flex justify-content-between align-items-center small">
                        <span class="opacity-75">วันลาพักผ่อนสะสม</span>
                        <span class="fw-bold"><?= $model->sumLeavePermission()['total'] ?? 0 ?> วัน</span>
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

    <!-- ตัวอย่างใบลา (เต็มความกว้าง) -->
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-transparent border-0 py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h6 class="mb-0 fw-bold text-body small d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf text-primary"></i>
                    ตัวอย่างใบลา
                </h6>
                <?= Html::a('<i class="bi bi-printer me-1"></i> เปิดหน้ารูปแบบพิมพ์', ['/leave/leave/print', 'id' => $model->id], ['class' => 'btn btn-outline-primary btn-sm rounded-3', 'target' => '_blank', 'rel' => 'noopener']) ?>
            </div>
            <div class="card-body p-0 bg-body-secondary bg-opacity-25">
                <?php if (!empty($previewPdfUrl)): ?>
                    <div class="min-vh-100">
                        <iframe src="<?= Html::encode($previewPdfUrl) ?>#toolbar=0" title="ตัวอย่างใบลา (PDF)" class="w-100 border-0 d-block h-100 min-vh-100"></iframe>
                    </div>
                <?php else: ?>
                    <div class="text-center text-muted py-5 px-4">
                        <i class="bi bi-file-earmark-pdf display-4 opacity-25 d-block mb-2"></i>
                        <p class="mb-2">ยังไม่มีเทมเพลต PDF สำหรับใบลานี้</p>
                        <p class="small mb-0"><?= Html::a('เปิดหน้ารูปแบบพิมพ์ (แบบ HTML)', ['/leave/leave/print', 'id' => $model->id], ['class' => 'text-primary', 'target' => '_blank', 'rel' => 'noopener']) ?></p>
                    </div>
                <?php endif; ?>
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

<?php

use yii\helpers\Html;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\Leave $model */

$me = UserHelper::GetEmployee();
$this->registerCss('.leave-creator-avatar-wrap img { width: 100%; height: 100%; object-fit: cover; }');
?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <?php
                $author = $model->getAvatar($model->emp_id, '');
                $authorAvatar = $author['avatar'] ?? '';
                $authorName = $author['fullname'] ?? ($model->employee->fullname ?? '');
                $authorDept = $author['department'] ?? ($model->employee ? $model->employee->departmentName() : '');
                $authorPosition = $author['position_name'] ?? ($model->employee ? $model->employee->positionName() : '');
                ?>
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div class="d-flex align-items-center gap-3 flex-grow-1">
                        <div class="leave-creator-avatar-wrap rounded-circle overflow-hidden border border-2 border-primary bg-body-secondary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 56px; height: 56px;">
                            <?php if ($authorAvatar): ?>
                                <?= $authorAvatar ?>
                            <?php else: ?>
                                <i class="bi bi-person fs-2 text-muted"></i>
                            <?php endif; ?>
                        </div>
                        <div>
                            <div class="small text-muted mb-0">ผู้สร้างใบลา</div>
                            <div class="fw-bold text-body"><?= Html::encode($authorName) ?></div>
                            <?php if ($authorPosition !== ''): ?>
                                <div class="small text-muted"><?= Html::encode($authorPosition) ?></div>
                            <?php endif; ?>
                            <?php if ($authorDept !== ''): ?>
                                <div class="small text-secondary"><?= Html::encode($authorDept) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 text-muted small">
                        <?= $model->viewStatus() ?>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-sm-6">
                        <small class="text-muted d-block mb-1">ประเภทการลา</small>
                        <p class="fw-bold text-body mb-0 fs-5"><?= $model->leaveType ? Html::encode($model->leaveType->title) : '-' ?></p>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block mb-1">จำนวนวัน</small>
                        <p class="fw-bold text-body mb-0 fs-5"><?= (float) $model->total_days ?> วัน</p>
                    </div>
                    <?php
                    $calDays = isset($model->data_json['summary_calendar_days']) ? (int) $model->data_json['summary_calendar_days'] : null;
                    $satSun = isset($model->data_json['summary_sat_sun']) ? (int) $model->data_json['summary_sat_sun'] : null;
                    $holiday = isset($model->data_json['summary_holiday']) ? (int) $model->data_json['summary_holiday'] : null;
                    ?>
                    <div class="col-12">
                        <div class="card border-0 border-start border-3 border-info rounded-4 overflow-hidden">
                            <div class="card-body py-3 px-4">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <div class="p-2 bg-info bg-opacity-10 rounded-circle text-info"><i class="bi bi-calendar3 fs-5"></i></div>
                                    <h6 class="fw-bold mb-0 text-body">สรุปวันลา</h6>
                                </div>
                                <div class="row g-2 small">
                                    <div class="col-6 col-md-3 text-center">
                                        <div class="text-muted mb-1">รวมระยะเวลา</div>
                                        <div class="fw-semibold text-body"><?= $calDays !== null ? $calDays . ' วัน' : '-' ?></div>
                                    </div>
                                    <div class="col-6 col-md-3 text-center">
                                        <div class="text-muted mb-1">วันเสาร์-อาทิตย์</div>
                                        <div class="fw-semibold text-body"><?= $satSun !== null ? $satSun . ' วัน' : '-' ?></div>
                                    </div>
                                    <div class="col-6 col-md-3 text-center">
                                        <div class="text-muted mb-1">วันหยุดนักขัตฤกษ์</div>
                                        <div class="fw-semibold text-body"><?= $holiday !== null ? $holiday . ' วัน' : '-' ?></div>
                                    </div>
                                    <div class="col-6 col-md-3 text-center">
                                        <div class="text-muted mb-1">รวมวันลา</div>
                                        <div class="fw-semibold text-primary"><?= (float) $model->total_days ?> วัน</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 border border-secondary border-opacity-25">
                            <div class="d-flex gap-2 text-muted small">
                                <i class="fa-regular fa-calendar fa-xl me-1"></i> ช่วงเวลาที่ลา
                            </div>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <?= $model->showLeaveDate() ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 border border-secondary border-opacity-25">
                            <small class="text-muted d-block mb-1"><i class="fa-regular fa-circle-question fa-xl me-1"></i> เหตุผลการลา</small>
                            <?= Html::encode($model->data_json['reason'] ?? '-') ?>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border border-secondary border-opacity-25">
                            <small class="text-muted d-block mb-1"><i class="fa-regular fa-circle-question fa-xl me-1"></i> ข้อมูลการติดต่อ</small>
                            <div class="d-flex gap-2"><span><?= Html::encode($model->data_json['address'] ?? '-') ?></span></div>
                            <div class="d-flex gap-2 align-items-center">โทร. <span class="fw-medium text-body"><?= Html::encode($model->data_json['phone'] ?? $model->data_json['leave_contact_phone'] ?? '-') ?></span></div>
                        </div>
                    </div>
                    <?php
                    $attachments = $model->getAttachmentList();
                    if (!empty($attachments)):
                    ?>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border border-secondary border-opacity-25">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <i class="bi bi-paperclip text-primary"></i>
                                <small class="text-muted mb-0 fw-semibold">เอกสารแนบ / ใบรับรองแพทย์</small>
                            </div>
                            <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                                <?php foreach ($attachments as $att): ?>
                                <li>
                                    <a href="<?= \yii\helpers\Url::to(['/filemanager/uploads/show', 'id' => $att->id]) ?>" target="_blank" rel="noopener" class="d-inline-flex align-items-center gap-2 text-decoration-none text-body border border-secondary border-opacity-25 rounded-3 px-3 py-2 bg-body-secondary bg-opacity-25">
                                        <i class="bi bi-file-earmark-arrow-down text-primary"></i>
                                        <span class="small"><?= Html::encode($att->file_name) ?></span>
                                        <i class="bi bi-box-arrow-up-right small text-muted ms-1"></i>
                                    </a>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php
                    $signatureData = isset($model->data_json['signature_data']) ? trim((string) $model->data_json['signature_data']) : '';
                    $signatureType = $model->data_json['signature_type'] ?? 'canvas';
                    if ($signatureData !== ''):
                        $signatureLabel = ($signatureType === 'system') ? 'ใช้ในระบบ' : 'เซ็นเอง';
                    ?>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border border-secondary border-opacity-25">
                            <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                                <i class="bi bi-pen text-primary"></i>
                                <small class="text-muted mb-0 fw-semibold">ลายเซ็นผู้ขอลา</small>
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1 small"><?= Html::encode($signatureLabel) ?></span>
                            </div>
                            <div class="bg-white rounded-3 border border-secondary border-opacity-25 p-2 d-inline-block">
                                <img src="<?= Html::encode($signatureData) ?>" alt="ลายเซ็น" class="d-block img-fluid mw-100">
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php
                    $substitute = $model->leaveWorkSend();
                    if (!empty($substitute)):
                    ?>
                    <div class="col-12">
                        <div class="p-3 rounded-3 border border-secondary border-opacity-25">
                            <small class="text-muted d-block mb-1"><i class="fa-regular fa-user fa-xl me-1"></i> ผู้ปฏิบัติหน้าที่แทน</small>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="d-flex align-items-center gap-2">
                                    <?= $substitute->getAvatar(false, 'ผู้ปฏิบัติหน้าที่แทน') ?>
                                </div>
                                <div>
                                    <p class="fw-bold text-body mb-1"><?= Html::encode($substitute->fullname) ?></p>
                                    <p class="text-muted small mb-0"><?= Html::encode($substitute->positionName()) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <div class="col-12">
                        <?= $this->render('view_summary', ['model' => $model]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="d-flex flex-column gap-3 h-100">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white overflow-hidden position-relative">
                <div class="position-absolute top-0 end-0 opacity-10 p-2">
                    <i class="bi bi-graph-up-arrow display-4"></i>
                </div>
                <div class="card-body p-4 position-relative z-1">
                    <small class="opacity-75 d-block mb-1 text-white h6">สิทธิคงเหลือ</small>
                    <h2 class="display-6 fw-bold mb-0 text-white">
                        <?= $model->sumLeavePermission()['sum'] ?? 0 ?>
                        <span class="fs-6 fw-normal opacity-75 text-white">วัน</span>
                    </h2>
                    <div class="mt-3 pt-3 border-top border-white border-opacity-25 d-flex justify-content-between align-items-center small">
                        <span class="opacity-75 text-white h6">วันลาพักผ่อนสะสม</span>
                        <span class="fw-bold text-white"><?= $model->sumLeavePermission()['total'] ?? 0 ?> วัน</span>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm rounded-4 flex-grow-1">
                <div class="card-body p-4">
                    <?= $this->render('_level_approve', [
                        'model' => $model,
                        'listApprove' => $model->listApprove(),
                        'name' => 'leave',
                    ]) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="p-4">
            <?= $this->render('history', ['model' => $model]) ?>
        </div>
    </div>
</div>

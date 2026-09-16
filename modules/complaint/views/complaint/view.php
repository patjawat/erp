<?php

use app\components\AppHelper;
use app\modules\complaint\models\Complaint;
use app\modules\complaint\models\ComplaintAttachment;
use app\modules\complaint\models\ComplaintSurvey;
use app\widgets\datepicker\DatepickerThai;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Complaint $model */
/** @var bool $canManage */
/** @var array<int,string> $employees */
/** @var array<int,array{id:int,name:string}> $units */
/** @var array<string,array<int,string>> $masters */

$this->title = $model->complaint_no . ' — ' . $model->title;
$step = $model->step();
$today = AppHelper::DateFormDb(date('Y-m-d'));

// รายการ checklist ความครบถ้วน (ขั้นรับเรื่อง)
$checklistItems = [
    'reporter' => 'ข้อมูลผู้ร้องครบถ้วน',
    'event' => 'รายละเอียดเหตุการณ์ชัดเจน',
    'unit' => 'ระบุหน่วยงาน/บุคคลที่เกี่ยวข้อง',
    'evidence' => 'มีเอกสาร/หลักฐานประกอบ',
    'need' => 'ระบุความต้องการของผู้ร้อง',
];
$checkedList = [];
if ($model->intake_checklist) {
    $decoded = json_decode($model->intake_checklist, true);
    $checkedList = is_array($decoded) ? $decoded : [];
}

$empName = static fn (?int $id): string => $id && isset($employees[$id]) ? $employees[$id] : '—';
$thaiDate = static fn (?string $d): string => $d ? AppHelper::convertToThai($d) : '—';

// การแสดงผลขั้นตอน (stepper)
$steps = [
    1 => ['แจ้งเรื่อง', 'bi-megaphone'],
    2 => ['รับเรื่อง', 'bi-inbox'],
    3 => ['ประเมิน', 'bi-clipboard-check'],
    4 => ['ดำเนินงาน', 'bi-gear-wide-connected'],
    5 => ['ปิดเคส', 'bi-flag-fill'],
];
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($model->complaint_no) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>รหัสติดตาม <?= Html::encode($model->tracking_code) ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/complaint/menu', ['active' => 'registry']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $tone): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?>
            <div class="alert alert-<?= $tone ?> py-2 small"><?= Html::encode(Yii::$app->session->getFlash($key)) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <!-- หัวเรื่อง -->
    <div class="card border shadow-sm mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge text-bg-<?= Complaint::statusColor($model->status) ?>"><?= Html::encode($model->statusLabel()) ?></span>
                        <span class="fw-semibold"><?= Html::encode($model->complaint_no) ?></span>
                        <code class="text-body-secondary"><?= Html::encode($model->tracking_code) ?></code>
                    </div>
                    <h1 class="h5 fw-semibold mt-2 mb-1"><?= Html::encode($model->title) ?></h1>
                    <div class="small text-body-secondary">
                        <?= $thaiDate($model->complaint_date) ?><?= $model->complaint_time ? ' ' . substr($model->complaint_time, 0, 5) . ' น.' : '' ?>
                        <?php if ($model->channel): ?> · <i class="bi bi-signpost"></i> <?= Html::encode($model->channel->name) ?><?php endif; ?>
                        <?php if ($model->type): ?> · <?= Html::encode($model->type->name) ?><?php endif; ?>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($canManage): ?>
                        <?= Html::a('<i class="bi bi-pencil me-1"></i> แก้ไขข้อมูลเรื่อง', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary btn-sm']) ?>
                        <?php if ($model->status === Complaint::STATUS_CLOSED): ?>
                            <?= Html::beginForm(['reopen', 'id' => $model->id], 'post', ['class' => 'd-inline']) ?>
                                <?= Html::submitButton('<i class="bi bi-arrow-counterclockwise me-1"></i> เปิดเคสใหม่', ['class' => 'btn btn-outline-warning btn-sm']) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- stepper -->
            <div class="d-flex align-items-center gap-1 mt-3 flex-wrap">
                <?php foreach ($steps as $n => [$label, $icon]): ?>
                    <?php $done = $n <= $step && $model->status !== Complaint::STATUS_REJECTED || ($model->status === Complaint::STATUS_REJECTED && $n <= 3); ?>
                    <div class="d-flex align-items-center">
                        <span class="badge rounded-pill <?= $done ? 'text-bg-primary' : 'text-bg-light text-body-secondary' ?> d-inline-flex align-items-center gap-1">
                            <i class="bi <?= $icon ?>"></i> <?= $label ?>
                        </span>
                        <?php if ($n < 5): ?><i class="bi bi-chevron-right text-body-secondary mx-1 small"></i><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if ($model->status === Complaint::STATUS_REJECTED): ?>
                    <span class="badge text-bg-dark ms-2">ไม่รับพิจารณา</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-8">
            <!-- ขั้น 1: ข้อมูลเรื่อง -->
            <div class="card border shadow-sm mb-3">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-megaphone me-1"></i> 1. รายละเอียดเรื่องร้องเรียน</div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-sm-3 text-body-secondary">ผู้ร้อง</dt>
                        <dd class="col-sm-9"><?= $model->is_anonymous ? '<i class="bi bi-incognito"></i> ไม่ประสงค์ออกนาม' : Html::encode($model->reporter_name ?: '—') ?>
                            <?php if (!$model->is_anonymous && $model->reporter_phone): ?> · <?= Html::encode($model->reporter_phone) ?><?php endif; ?>
                            <?php if ($model->reporterRelation): ?> (<?= Html::encode($model->reporterRelation->name) ?>)<?php endif; ?>
                        </dd>
                        <dt class="col-sm-3 text-body-secondary">หน่วยงานรับผิดชอบ</dt>
                        <dd class="col-sm-9"><?= $model->assignedUnit ? Html::encode($model->assignedUnit->name) : '—' ?>
                            <?php if ($model->assigned_to): ?> · <?= Html::encode($empName((int) $model->assigned_to)) ?><?php endif; ?>
                        </dd>
                        <dt class="col-sm-3 text-body-secondary">รายละเอียด</dt>
                        <dd class="col-sm-9" style="white-space:pre-line"><?= nl2br(Html::encode($model->detail ?: '—')) ?></dd>
                    </dl>
                </div>
            </div>

            <!-- ขั้น 2: รับเรื่อง -->
            <div class="card border shadow-sm mb-3" id="step-intake">
                <div class="card-header bg-transparent fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-inbox me-1"></i> 2. รับเรื่อง</span>
                    <?php if ($model->intake_date): ?><span class="badge text-bg-success-subtle text-success-emphasis">รับเรื่องเมื่อ <?= $thaiDate($model->intake_date) ?></span><?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($canManage): ?>
                        <?= Html::beginForm(['intake', 'id' => $model->id], 'post') ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small">วันที่รับเรื่อง</label>
                                    <?= DatepickerThai::widget(['name' => 'intake_date_thai', 'value' => $model->intake_date ? AppHelper::DateFormDb($model->intake_date) : $today]) ?>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small">ที่อยู่ผู้ร้อง</label>
                                    <?= Html::textInput('Complaint[intake_address]', $model->intake_address, ['class' => 'form-control form-control-sm']) ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small d-block">Checklist ความครบถ้วน</label>
                                    <?php foreach ($checklistItems as $ck => $label): ?>
                                        <div class="form-check form-check-inline">
                                            <?= Html::checkbox('intake_checklist[]', in_array($ck, $checkedList, true), ['value' => $ck, 'class' => 'form-check-input', 'id' => 'ck-' . $ck]) ?>
                                            <label class="form-check-label small" for="ck-<?= $ck ?>"><?= Html::encode($label) ?></label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">ผลกระทบ</label>
                                    <?= Html::textarea('Complaint[intake_impact]', $model->intake_impact, ['class' => 'form-control form-control-sm', 'rows' => 2]) ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">ความต้องการของผู้ร้อง</label>
                                    <?= Html::textarea('Complaint[intake_need]', $model->intake_need, ['class' => 'form-control form-control-sm', 'rows' => 2]) ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">สิทธิผู้ป่วยที่เกี่ยวข้อง</label>
                                    <?= Html::dropDownList('Complaint[patient_right_id]', $model->patient_right_id, ['' => '— ไม่ระบุ —'] + $masters['patient_right'], ['class' => 'form-select form-select-sm']) ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">ผู้รับเรื่อง</label>
                                    <?= Html::dropDownList('Complaint[intake_by]', $model->intake_by, ['' => '— เลือก —'] + $employees, ['class' => 'form-select form-select-sm']) ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">บันทึกเพิ่มเติม</label>
                                    <?= Html::textarea('Complaint[intake_note]', $model->intake_note, ['class' => 'form-control form-control-sm', 'rows' => 2]) ?>
                                </div>
                            </div>
                            <div class="mt-2"><?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกการรับเรื่อง', ['class' => 'btn btn-primary btn-sm']) ?></div>
                        <?= Html::endForm() ?>
                    <?php else: ?>
                        <div class="small text-body-secondary">ที่อยู่: <?= Html::encode($model->intake_address ?: '—') ?><br>ผลกระทบ: <?= Html::encode($model->intake_impact ?: '—') ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ขั้น 3: ประเมิน -->
            <div class="card border shadow-sm mb-3" id="step-assess">
                <div class="card-header bg-transparent fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clipboard-check me-1"></i> 3. ประเมิน</span>
                    <?php if ($model->severity_level): ?><span class="badge text-bg-<?= $model->severity_level >= 3 ? 'danger' : 'primary' ?>">ระดับ <?= (int) $model->severity_level ?></span><?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($canManage): ?>
                        <?= Html::beginForm(['assess', 'id' => $model->id], 'post') ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small">วันที่ประเมิน</label>
                                    <?= DatepickerThai::widget(['name' => 'assess_date_thai', 'value' => $model->assess_date ? AppHelper::DateFormDb($model->assess_date) : $today]) ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">ระดับความรุนแรง</label>
                                    <?= Html::dropDownList('Complaint[severity_level]', $model->severity_level, ['' => '— เลือกระดับ —'] + Complaint::severityLabels(), ['class' => 'form-select form-select-sm']) ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">ระดับความเสี่ยง</label>
                                    <?= Html::textInput('Complaint[risk_level]', $model->risk_level, ['class' => 'form-control form-control-sm', 'placeholder' => 'เช่น High / เมทริกซ์']) ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">ผลการพิจารณา</label>
                                    <?= Html::dropDownList('Complaint[assess_decision]', $model->assess_decision, ['' => '— เลือก —'] + Complaint::decisionLabels(), ['class' => 'form-select form-select-sm', 'id' => 'assess-decision']) ?>
                                </div>
                                <div class="col-md-4" id="reject-reason-wrap">
                                    <label class="form-label small">เหตุผลไม่รับพิจารณา</label>
                                    <?= Html::dropDownList('Complaint[reject_reason_id]', $model->reject_reason_id, ['' => '— ไม่ระบุ —'] + $masters['reject_reason'], ['class' => 'form-select form-select-sm']) ?>
                                </div>
                                <div class="col-md-4 d-flex align-items-end">
                                    <div class="form-check mb-2">
                                        <?= Html::hiddenInput('Complaint[need_rca]', 0) ?>
                                        <?= Html::checkbox('Complaint[need_rca]', (bool) $model->need_rca, ['value' => 1, 'class' => 'form-check-input', 'id' => 'need-rca']) ?>
                                        <label class="form-check-label small" for="need-rca">ต้องทำ RCA</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">ผู้ประเมิน</label>
                                    <?= Html::dropDownList('Complaint[assess_by]', $model->assess_by, ['' => '— เลือก —'] + $employees, ['class' => 'form-select form-select-sm']) ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">บันทึกการประเมิน</label>
                                    <?= Html::textarea('Complaint[assess_note]', $model->assess_note, ['class' => 'form-control form-control-sm', 'rows' => 2]) ?>
                                </div>
                            </div>
                            <div class="mt-2"><?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกการประเมิน', ['class' => 'btn btn-primary btn-sm']) ?>
                                <span class="small text-body-secondary ms-2">ระบบจะคำนวณกำหนด SLA อัตโนมัติจากระดับ</span>
                            </div>
                        <?= Html::endForm() ?>
                    <?php endif; ?>

                    <?php if ($model->close_due || $model->respond_due): ?>
                        <div class="mt-3 border-top pt-2">
                            <div class="small fw-semibold text-body-secondary mb-1"><i class="bi bi-alarm me-1"></i> กำหนด SLA</div>
                            <div class="d-flex flex-wrap gap-2 small">
                                <span class="badge text-bg-light">ตอบสนอง: <?= $thaiDate($model->respond_due) ?></span>
                                <span class="badge text-bg-light">ตรวจสอบ/RCA: <?= $thaiDate($model->review_due) ?></span>
                                <span class="badge text-bg-light">ตอบกลับ: <?= $thaiDate($model->reply_due) ?></span>
                                <span class="badge text-bg-<?= !$model->isFinal() && $model->close_due && $model->close_due < date('Y-m-d') ? 'danger' : 'light' ?>">ปิดเคส: <?= $thaiDate($model->close_due) ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ขั้น 4: ดำเนินงาน -->
            <div class="card border shadow-sm mb-3" id="step-action">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-gear-wide-connected me-1"></i> 4. ดำเนินงาน (L1–L4)</div>
                <div class="card-body">
                    <?php if ($model->actions): ?>
                        <ul class="list-group list-group-flush mb-3">
                            <?php foreach ($model->actions as $a): ?>
                                <li class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start gap-2">
                                        <div>
                                            <span class="badge text-bg-secondary me-1"><?= $a->action_level ? 'L' . (int) $a->action_level : '—' ?></span>
                                            <span class="fw-semibold small"><?= Html::encode($a->kindLabel()) ?></span>
                                            <span class="text-body-secondary small ms-1"><?= $thaiDate($a->action_date) ?></span>
                                            <?php if ($a->title): ?><div class="small"><?= Html::encode($a->title) ?></div><?php endif; ?>
                                            <?php if ($a->detail): ?><div class="small text-body-secondary" style="white-space:pre-line"><?= nl2br(Html::encode($a->detail)) ?></div><?php endif; ?>
                                            <div class="small text-body-secondary"><i class="bi bi-person"></i> <?= Html::encode($empName($a->action_by ? (int) $a->action_by : null)) ?></div>
                                        </div>
                                        <?php if ($canManage): ?>
                                            <?= Html::beginForm(['delete-action', 'id' => $a->id], 'post', ['onsubmit' => 'return confirm("ลบรายการนี้?")']) ?>
                                                <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-outline-danger btn-sm']) ?>
                                            <?= Html::endForm() ?>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <div class="text-body-secondary small mb-3">— ยังไม่มีการบันทึกการดำเนินงาน —</div>
                    <?php endif; ?>

                    <?php if ($canManage && !$model->isFinal()): ?>
                        <?= Html::beginForm(['add-action', 'id' => $model->id], 'post', ['class' => 'border-top pt-3']) ?>
                            <div class="row g-2">
                                <div class="col-md-3">
                                    <label class="form-label small">วันที่</label>
                                    <?= DatepickerThai::widget(['name' => 'action_date_thai', 'value' => $today]) ?>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">ระดับ</label>
                                    <?= Html::dropDownList('action_level', null, ['' => '—', 1 => 'L1', 2 => 'L2', 3 => 'L3', 4 => 'L4'], ['class' => 'form-select form-select-sm']) ?>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small">ประเภทการดำเนินงาน</label>
                                    <?= Html::dropDownList('action_kind', null, ['' => '— เลือก —'] + \app\modules\complaint\models\ComplaintAction::KINDS, ['class' => 'form-select form-select-sm']) ?>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small">หัวข้อ</label>
                                    <?= Html::textInput('action_title', '', ['class' => 'form-control form-control-sm']) ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">รายละเอียด</label>
                                    <?= Html::textarea('action_detail', '', ['class' => 'form-control form-control-sm', 'rows' => 2]) ?>
                                </div>
                            </div>
                            <div class="mt-2"><?= Html::submitButton('<i class="bi bi-plus-lg me-1"></i> เพิ่มการดำเนินงาน', ['class' => 'btn btn-primary btn-sm']) ?></div>
                        <?= Html::endForm() ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ขั้น 5: ปิดเคส + แบบสำรวจ -->
            <div class="card border shadow-sm mb-3" id="step-close">
                <div class="card-header bg-transparent fw-semibold d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-flag-fill me-1"></i> 5. ปิดเคส / ถอดบทเรียน</span>
                    <?php if ($model->close_date): ?><span class="badge text-bg-success">ปิดเมื่อ <?= $thaiDate($model->close_date) ?></span><?php endif; ?>
                </div>
                <div class="card-body">
                    <?php if ($canManage): ?>
                        <?= Html::beginForm(['close', 'id' => $model->id], 'post') ?>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small">วันที่ปิดเคส</label>
                                    <?= DatepickerThai::widget(['name' => 'close_date_thai', 'value' => $model->close_date ? AppHelper::DateFormDb($model->close_date) : $today]) ?>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small">ผู้ปิดเคส</label>
                                    <?= Html::dropDownList('Complaint[close_by]', $model->close_by, ['' => '— เลือก —'] + $employees, ['class' => 'form-select form-select-sm']) ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">ถอดบทเรียน</label>
                                    <?= Html::textarea('Complaint[lesson_learned]', $model->lesson_learned, ['class' => 'form-control form-control-sm', 'rows' => 3]) ?>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">การเยียวยา</label>
                                    <?= Html::textarea('Complaint[remedy]', $model->remedy, ['class' => 'form-control form-control-sm', 'rows' => 3]) ?>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">ข้อเสนอแนะ/บันทึกปิดเคส</label>
                                    <?= Html::textarea('Complaint[close_note]', $model->close_note, ['class' => 'form-control form-control-sm', 'rows' => 2]) ?>
                                </div>
                            </div>
                            <div class="mt-2"><?= Html::submitButton('<i class="bi bi-flag me-1"></i> บันทึก/ปิดเคส', ['class' => 'btn btn-success btn-sm']) ?></div>
                        <?= Html::endForm() ?>

                        <!-- แบบสำรวจความพึงพอใจ -->
                        <div class="border-top mt-3 pt-3">
                            <div class="fw-semibold small mb-2"><i class="bi bi-emoji-smile me-1"></i> บันทึกแบบสำรวจความพึงพอใจ</div>
                            <?php if ($model->surveys): ?>
                                <?php foreach ($model->surveys as $s): ?>
                                    <div class="small text-body-secondary mb-1">
                                        <?= $thaiDate($s->survey_date) ?> · คะแนนเฉลี่ย <b><?= $s->avg_score !== null ? $s->avg_score : '—' ?></b>/5
                                        <?php if ($s->comment): ?> · “<?= Html::encode($s->comment) ?>”<?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                            <?= Html::beginForm(['save-survey', 'id' => $model->id], 'post', ['class' => 'row g-2 align-items-end mt-1']) ?>
                                <div class="col-md-3">
                                    <label class="form-label small">วันที่สำรวจ</label>
                                    <?= DatepickerThai::widget(['name' => 'survey_date_thai', 'value' => $today]) ?>
                                </div>
                                <?php foreach (ComplaintSurvey::ASPECTS as $field => $label): ?>
                                    <div class="col-6 col-md-auto">
                                        <label class="form-label small d-block" title="<?= Html::encode($label) ?>"><?= Html::encode(mb_substr($label, 0, 12)) ?></label>
                                        <?= Html::dropDownList("ComplaintSurvey[$field]", null, ['' => '—', 1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5], ['class' => 'form-select form-select-sm', 'style' => 'width:64px']) ?>
                                    </div>
                                <?php endforeach; ?>
                                <div class="col-12">
                                    <?= Html::textInput('ComplaintSurvey[comment]', '', ['class' => 'form-control form-control-sm', 'placeholder' => 'ข้อเสนอแนะ (ถ้ามี)']) ?>
                                </div>
                                <div class="col-12"><?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึกแบบสำรวจ', ['class' => 'btn btn-outline-primary btn-sm']) ?></div>
                            <?= Html::endForm() ?>
                        </div>
                    <?php else: ?>
                        <div class="small text-body-secondary">ถอดบทเรียน: <?= Html::encode($model->lesson_learned ?: '—') ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- คอลัมน์ขวา: ไฟล์แนบ + ประวัติ -->
        <div class="col-lg-4">
            <div class="card border shadow-sm mb-3" id="attachments">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-paperclip me-1"></i> ไฟล์แนบ</div>
                <div class="card-body">
                    <?php if ($canManage): ?>
                        <?= Html::beginForm(['upload-files', 'id' => $model->id], 'post', ['enctype' => 'multipart/form-data', 'class' => 'mb-3']) ?>
                            <div class="mb-2">
                                <?= Html::dropDownList('category', 'general', ComplaintAttachment::CATEGORIES, ['class' => 'form-select form-select-sm mb-2']) ?>
                                <?= Html::fileInput('files[]', null, ['class' => 'form-control form-control-sm', 'multiple' => true, 'accept' => 'image/*,.pdf,.doc,.docx,.xls,.xlsx']) ?>
                            </div>
                            <?= Html::submitButton('<i class="bi bi-upload me-1"></i> อัปโหลด', ['class' => 'btn btn-outline-primary btn-sm w-100']) ?>
                        <?= Html::endForm() ?>
                    <?php endif; ?>

                    <?php if (!$model->attachments): ?>
                        <div class="text-body-secondary small">— ยังไม่มีไฟล์แนบ —</div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach ($model->attachments as $att): ?>
                                <div class="d-flex align-items-center gap-2 border rounded p-1">
                                    <?php if ($att->isImage()): ?>
                                        <a href="<?= Url::to(['file', 'id' => $att->id]) ?>" target="_blank">
                                            <img src="<?= Url::to(['file', 'id' => $att->id, 'thumb' => 1]) ?>" alt="" style="width:44px;height:44px;object-fit:cover;border-radius:.3rem">
                                        </a>
                                    <?php else: ?>
                                        <a href="<?= Url::to(['file', 'id' => $att->id]) ?>" target="_blank" class="d-inline-flex align-items-center justify-content-center bg-light rounded" style="width:44px;height:44px"><i class="bi bi-file-earmark-text fs-5"></i></a>
                                    <?php endif; ?>
                                    <div class="flex-grow-1 min-w-0">
                                        <a href="<?= Url::to(['file', 'id' => $att->id]) ?>" target="_blank" class="small text-truncate d-block text-decoration-none"><?= Html::encode($att->file_name ?: 'ไฟล์') ?></a>
                                        <span class="badge text-bg-light"><?= Html::encode(ComplaintAttachment::CATEGORIES[$att->category] ?? $att->category) ?></span>
                                    </div>
                                    <?php if ($canManage): ?>
                                        <?= Html::beginForm(['delete-file', 'id' => $att->id], 'post', ['onsubmit' => 'return confirm("ลบไฟล์นี้?")']) ?>
                                            <?= Html::submitButton('<i class="bi bi-x-lg"></i>', ['class' => 'btn btn-sm btn-outline-danger border-0']) ?>
                                        <?= Html::endForm() ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border shadow-sm">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-clock-history me-1"></i> ประวัติดำเนินการ</div>
                <ul class="list-group list-group-flush small">
                    <?php if (!$model->logs): ?>
                        <li class="list-group-item text-body-secondary">—</li>
                    <?php endif; ?>
                    <?php foreach ($model->logs as $log): ?>
                        <li class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <span>
                                    <?= Html::encode($log->note ?: $log->action) ?>
                                    <?php if ($log->to_status): ?><span class="badge text-bg-<?= Complaint::statusColor($log->to_status) ?> ms-1"><?= Html::encode(Complaint::statusLabels()[$log->to_status] ?? $log->to_status) ?></span><?php endif; ?>
                                </span>
                            </div>
                            <div class="text-body-secondary" style="font-size:.75rem"><?= Html::encode($log->created_at) ?></div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php if ($canManage && \app\modules\complaint\services\ComplaintService::isManager()): ?>
                <div class="mt-3">
                    <?= Html::beginForm(['delete', 'id' => $model->id], 'post', ['onsubmit' => 'return confirm("ลบเรื่องนี้ทั้งหมด? ไม่สามารถกู้คืนได้")']) ?>
                        <?= Html::submitButton('<i class="bi bi-trash me-1"></i> ลบเรื่องนี้', ['class' => 'btn btn-outline-danger btn-sm w-100']) ?>
                    <?= Html::endForm() ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// ซ่อน/แสดงช่องเหตุผลไม่รับพิจารณา ตามผลการพิจารณา
$js = <<<'JS'
(function(){
  var dec=document.getElementById('assess-decision');
  var wrap=document.getElementById('reject-reason-wrap');
  if(!dec||!wrap) return;
  function sync(){ wrap.style.display = (dec.value==='reject') ? '' : 'none'; }
  dec.addEventListener('change', sync); sync();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>

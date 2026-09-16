<?php

use app\components\AppHelper;
use app\modules\complaint\models\Complaint;
use app\widgets\datepicker\DatepickerThai;
use kartik\select2\Select2;
use kartik\time\TimePicker;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Complaint $model */
/** @var array<int,string> $channels */
/** @var array<int,string> $types */
/** @var array<int,string> $relations */
/** @var array<int,array{id:int,name:string}> $units */
/** @var array<int,string> $employees */
/** @var int[] $years */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'รับเรื่องร้องเรียนใหม่' : ('แก้ไขเรื่อง ' . $model->complaint_no);
$dateThai = $model->complaint_date ? AppHelper::DateFormDb($model->complaint_date) : AppHelper::DateFormDb(date('Y-m-d'));
$err = $model->getErrors();
$feedback = static fn (array $err, string $attr): string => empty($err[$attr]) ? '' : '<div class="text-danger small mt-1">' . Html::encode(implode(' ', $err[$attr])) . '</div>';
$invalid = static fn (array $err, string $attr): string => empty($err[$attr]) ? '' : ' is-invalid';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>รับเรื่องร้องเรียน<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/complaint/menu', ['active' => 'registry']) ?></div>

    <div class="card border shadow-sm">
        <div class="card-body">
            <h1 class="h5 fw-semibold mb-3"><i class="bi bi-<?= $isNew ? 'plus-lg' : 'pencil' ?> me-1"></i> <?= Html::encode($this->title) ?></h1>

            <?php if ($err): ?>
                <div class="alert alert-danger py-2 small mb-3"><i class="bi bi-exclamation-triangle me-1"></i>กรุณาตรวจสอบข้อมูลที่กรอก</div>
            <?php endif; ?>

            <?= Html::beginForm('', 'post', ['id' => 'complaint-form']) ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">ชื่อเรื่องโดยย่อ <span class="text-danger">*</span></label>
                        <?= Html::textInput('Complaint[title]', $model->title, ['class' => 'form-control' . $invalid($err, 'title'), 'maxlength' => 500, 'autofocus' => $isNew]) ?>
                        <?= $feedback($err, 'title') ?>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">วันที่ร้องเรียน</label>
                        <?= DatepickerThai::widget(['name' => 'complaint_date_thai', 'value' => $dateThai]) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">เวลา</label>
                        <?= TimePicker::widget([
                            'name' => 'Complaint[complaint_time]',
                            'value' => $model->complaint_time ? substr($model->complaint_time, 0, 5) : '',
                            'pluginOptions' => ['showMeridian' => false],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ช่องทาง</label>
                        <?= Select2::widget([
                            'name' => 'Complaint[channel_id]',
                            'value' => $model->channel_id,
                            'data' => $channels,
                            'options' => ['placeholder' => '— เลือกช่องทาง —'],
                            'pluginOptions' => ['allowClear' => true],
                        ]) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ประเภทเรื่อง</label>
                        <?= Select2::widget([
                            'name' => 'Complaint[type_id]',
                            'value' => $model->type_id,
                            'data' => $types,
                            'options' => ['placeholder' => '— เลือกประเภท —'],
                            'pluginOptions' => ['allowClear' => true],
                        ]) ?>
                    </div>

                    <div class="col-12">
                        <label class="form-label">รายละเอียดเรื่องร้องเรียน</label>
                        <?= Html::textarea('Complaint[detail]', $model->detail, ['class' => 'form-control', 'rows' => 4]) ?>
                    </div>

                    <div class="col-12"><hr class="my-1"><div class="fw-semibold small text-body-secondary"><i class="bi bi-person me-1"></i> ข้อมูลผู้ร้อง</div></div>
                    <div class="col-md-4">
                        <label class="form-label">ชื่อผู้ร้อง</label>
                        <?= Html::textInput('Complaint[reporter_name]', $model->reporter_name, ['class' => 'form-control', 'maxlength' => 500]) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">เบอร์ติดต่อ</label>
                        <?= Html::textInput('Complaint[reporter_phone]', $model->reporter_phone, ['class' => 'form-control', 'maxlength' => 64]) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">ความสัมพันธ์</label>
                        <?= Select2::widget([
                            'name' => 'Complaint[reporter_relation_id]',
                            'value' => $model->reporter_relation_id,
                            'data' => $relations,
                            'options' => ['placeholder' => '— เลือก —'],
                            'pluginOptions' => ['allowClear' => true],
                        ]) ?>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check mb-2">
                            <?= Html::hiddenInput('Complaint[is_anonymous]', 0) ?>
                            <?= Html::checkbox('Complaint[is_anonymous]', (bool) $model->is_anonymous, ['value' => 1, 'class' => 'form-check-input', 'id' => 'anon']) ?>
                            <label class="form-check-label" for="anon">ไม่ประสงค์ออกนาม</label>
                        </div>
                    </div>

                    <div class="col-12"><hr class="my-1"><div class="fw-semibold small text-body-secondary"><i class="bi bi-diagram-3 me-1"></i> การมอบหมาย</div></div>
                    <div class="col-md-3">
                        <label class="form-label">ปีงบประมาณ <span class="text-danger">*</span></label>
                        <?= Html::dropDownList('Complaint[fiscal_year]', $model->fiscal_year, array_combine($years, $years), ['class' => 'form-select' . $invalid($err, 'fiscal_year')]) ?>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">หน่วยงานที่รับผิดชอบ</label>
                        <?= Select2::widget([
                            'name' => 'Complaint[assigned_unit_id]',
                            'value' => $model->assigned_unit_id,
                            'data' => ArrayHelper::map($units, 'id', 'name'),
                            'options' => ['placeholder' => '— เลือกหน่วยงาน —'],
                            'pluginOptions' => ['allowClear' => true],
                        ]) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ผู้รับผิดชอบ</label>
                        <?= Select2::widget([
                            'name' => 'Complaint[assigned_to]',
                            'value' => $model->assigned_to,
                            'data' => $employees,
                            'options' => ['placeholder' => '— เลือกผู้รับผิดชอบ —'],
                            'pluginOptions' => ['allowClear' => true],
                        ]) ?>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('ยกเลิก', $isNew ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
    <?php if ($isNew): ?>
        <div class="alert alert-light border mt-3 small text-body-secondary">
            <i class="bi bi-info-circle me-1"></i> เมื่อบันทึกแล้ว ระบบจะออก <b>เลขที่เรื่อง</b> และ <b>รหัสติดตาม</b> อัตโนมัติ จากนั้นดำเนินการรับเรื่อง/ประเมิน/ดำเนินงาน/ปิดเคส ได้ในหน้ารายละเอียด
        </div>
    <?php endif; ?>
</div>

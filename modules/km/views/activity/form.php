<?php

use app\components\AppHelper;
use app\widgets\datepicker\DatepickerThai;
use app\modules\km\models\KmActivity;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\km\models\KmActivity $model */
/** @var app\modules\km\models\KmCategory[] $categories */
/** @var array<int,array{id:int,name:string}> $units */
/** @var int[] $years */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'เพิ่มกิจกรรม' : 'แก้ไขกิจกรรม';
$dateThai = $model->activity_date ? AppHelper::DateFormDb($model->activity_date) : '';
$err = $model->getErrors();

/** ป้าย error ใต้ช่อง (ถ้ามี) */
$feedback = static function (array $err, string $attr): string {
    if (empty($err[$attr])) {
        return '';
    }
    return '<div class="text-danger small mt-1">' . Html::encode(implode(' ', $err[$attr])) . '</div>';
};
$invalid = static fn (array $err, string $attr): string => empty($err[$attr]) ? '' : ' is-invalid';
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>คลังกิจกรรม KM<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/km/menu', ['active' => 'activity']) ?></div>

    <div class="card border shadow-sm">
        <div class="card-body">
            <h1 class="h5 fw-semibold mb-3"><i class="bi bi-<?= $isNew ? 'plus-lg' : 'pencil' ?> me-1"></i> <?= Html::encode($this->title) ?></h1>

            <?php if ($err): ?>
                <div class="alert alert-danger py-2 small mb-3"><i class="bi bi-exclamation-triangle me-1"></i>กรุณาตรวจสอบข้อมูลที่กรอก</div>
            <?php endif; ?>

            <?= Html::beginForm('', 'post', ['id' => 'km-activity-form']) ?>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                        <?= Html::textInput('KmActivity[title]', $model->title, ['class' => 'form-control' . $invalid($err, 'title'), 'maxlength' => 500, 'autofocus' => $isNew]) ?>
                        <?= $feedback($err, 'title') ?>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">หมวดหมู่</label>
                        <?= Html::dropDownList('KmActivity[category_id]', $model->category_id, array_column($categories, 'name', 'id'), ['class' => 'form-select', 'prompt' => '— เลือกหมวด —']) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">ปีงบประมาณ <span class="text-danger">*</span></label>
                        <?= Html::dropDownList('KmActivity[fiscal_year]', $model->fiscal_year, array_combine($years, $years), ['class' => 'form-select' . $invalid($err, 'fiscal_year')]) ?>
                        <?= $feedback($err, 'fiscal_year') ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">หน่วยงานเจ้าภาพ</label>
                        <?= Html::dropDownList('KmActivity[owner_unit_id]', $model->owner_unit_id, array_column($units, 'name', 'id'), ['class' => 'form-select', 'prompt' => '— เลือกหน่วยงาน —']) ?>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">วันที่จัด</label>
                        <?= DatepickerThai::widget(['name' => 'activity_date_thai', 'value' => $dateThai]) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เวลาเริ่ม</label>
                        <?= Html::input('time', 'KmActivity[start_time]', $model->start_time, ['class' => 'form-control']) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">เวลาสิ้นสุด</label>
                        <?= Html::input('time', 'KmActivity[end_time]', $model->end_time, ['class' => 'form-control']) ?>
                    </div>

                    <div class="col-12">
                        <label class="form-label">สถานที่</label>
                        <?= Html::textInput('KmActivity[location]', $model->location, ['class' => 'form-control', 'maxlength' => 255]) ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label">สรุปย่อ <span class="text-body-secondary small">(แสดงบนการ์ด)</span></label>
                        <?= Html::textarea('KmActivity[summary]', $model->summary, ['class' => 'form-control', 'rows' => 2]) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">วัตถุประสงค์</label>
                        <?= Html::textarea('KmActivity[objective]', $model->objective, ['class' => 'form-control', 'rows' => 4]) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">รายละเอียด / ถอดบทเรียน</label>
                        <?= Html::textarea('KmActivity[detail]', $model->detail, ['class' => 'form-control', 'rows' => 4]) ?>
                    </div>

                    <div class="col-12">
                        <label class="form-label d-block">สถานะ</label>
                        <?php foreach (KmActivity::statusLabels() as $val => $label): ?>
                            <div class="form-check form-check-inline">
                                <?= Html::radio('KmActivity[status]', $model->status === $val, ['value' => $val, 'class' => 'form-check-input', 'id' => 'st-' . $val]) ?>
                                <label class="form-check-label" for="st-<?= $val ?>"><?= Html::encode($label) ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="mt-3 d-flex gap-2">
                    <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('ยกเลิก', $isNew ? ['index'] : ['view', 'id' => $model->id], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\ha12\models\Ha12Activity[] $activities */
/** @var int $activityId */
/** @var int $fiscalYear */
/** @var int[] $years */
/** @var array<int,array{id:int,name:string}> $units */

$this->title = 'HA12-PCT · นำเข้าข้อมูล';
$activityOptions = [];
foreach ($activities as $a) {
    $activityOptions[$a->id] = $a->no . '. ' . $a->name;
}
$unitOptions = [];
foreach ($units as $u) {
    $unitOptions[$u['id']] = $u['name'];
}
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode('HA12-PCT') ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>นำเข้าการทบทวน (กิจกรรมทั่วไป) จาก Excel<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/ha12/menu', ['active' => 'report']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $tone): ?>
        <?php if ($msg = Yii::$app->session->getFlash($flash)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h5 fw-semibold mb-0"><i class="bi bi-upload me-1"></i> นำเข้าการทบทวน</h1>
        <?= Html::a('<i class="bi bi-arrow-left"></i> กลับรายงาน', ['/ha12/report/index'], ['class' => 'btn btn-outline-secondary rounded-pill btn-sm']) ?>
    </div>

    <div class="alert alert-info-subtle border small">
        <i class="bi bi-info-circle me-1"></i>
        ใช้สำหรับย้ายข้อมูลเดิม/บันทึกทีละหลายรายการ — ดาวน์โหลดแม่แบบตามกิจกรรม กรอกข้อมูล แล้วอัปโหลด
        ระบบกันนำเข้าซ้ำด้วย “รหัสอ้างอิงเดิม” (ถ้าระบุ) · รองรับเฉพาะกิจกรรมทั่วไป (7/9/12 ใช้หน้ากรอกเฉพาะ)
    </div>

    <div class="card border shadow-sm">
        <div class="card-body">
            <?= Html::beginForm(['upload', 'activity_id' => $activityId], 'post', ['enctype' => 'multipart/form-data', 'id' => 'ha12-import-form']) ?>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label small fw-semibold">กิจกรรม</label>
                        <?= Html::dropDownList('activity_pick', $activityId, $activityOptions, ['class' => 'form-select', 'id' => 'imp-activity', 'onchange' => "window.location='" . \yii\helpers\Url::to(['index']) . "?activity_id='+this.value"]) ?>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">หน่วยงานเจ้าของ</label>
                        <?= Html::dropDownList('owner_unit_id', null, $unitOptions, ['class' => 'form-select', 'prompt' => '— เลือกหน่วยงาน —']) ?>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">ปีงบ</label>
                        <?= Html::dropDownList('fiscal_year', $fiscalYear, array_combine($years, $years), ['class' => 'form-select']) ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-semibold">ไฟล์ Excel (.xlsx)</label>
                        <?= Html::fileInput('file', null, ['class' => 'form-control', 'accept' => '.xlsx,.xls', 'required' => true]) ?>
                    </div>
                </div>
                <div class="d-flex flex-wrap justify-content-between gap-2 mt-3">
                    <?= Html::a('<i class="bi bi-download"></i> ดาวน์โหลดแม่แบบกิจกรรมนี้', ['template', 'activity_id' => $activityId], ['class' => 'btn btn-outline-secondary']) ?>
                    <?= Html::submitButton('<i class="bi bi-upload"></i> นำเข้า', ['class' => 'btn btn-primary']) ?>
                </div>
            <?= Html::endForm() ?>
        </div>
    </div>
</div>

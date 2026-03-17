<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AmAssetSequence $model */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'เพิ่มลำดับ (ต่อปีต่อหมวด)' : 'แก้ไขลำดับ';
$this->params['breadcrumbs'][] = ['label' => 'การกำหนดรูปแบบ FSN', 'url' => ['/am/setting/fsn-format']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h5 class="mb-0 fw-semibold"><?= Html::encode($this->title) ?></h5>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> กลับรายการลำดับ', ['/am/setting/fsn-format'], ['class' => 'btn btn-outline-secondary']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-2">
    <div class="card-body">
        <p class="text-muted small mb-3">
            กำหนดลำดับล่าสุดสำหรับรหัสหมวด FSN และปี พ.ศ. หมายเลขครุภัณฑ์ถัดไปจะใช้ค่าลำดับนี้ + 1<br>
            รหัสหมวดใช้รูปแบบเดียวกับรหัส FSN ในรายการครุภัณฑ์ (เช่น 7910-003-0003)
        </p>
        <?php $form = ActiveForm::begin(['id' => 'form-fsn-sequence']); ?>
        <div class="row g-3">
            <div class="col-md-12">
                <?= $form->field($model, 'category_id')->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'เช่น 7910-003-0003'])->label('รหัสหมวด (FSN)') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'year')->textInput(['type' => 'number', 'class' => 'form-control', 'placeholder' => 'เช่น 2568'])->label('ปี พ.ศ.') ?>
            </div>
            <div class="col-md-6">
                <?= $form->field($model, 'current_sequence')->textInput(['type' => 'number', 'min' => 0, 'class' => 'form-control', 'placeholder' => '0'])->label('ลำดับล่าสุด')->hint('หมายเลขถัดไปจะได้ลำดับนี้ + 1') ?>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <?= Html::submitButton('<i class="fa-solid fa-check me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('ยกเลิก', ['/am/setting/fsn-format'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AmAssetNumberFormat $model */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'เพิ่มรูปแบบหมายเลข FSN' : 'แก้ไขรูปแบบหมายเลข FSN';
$this->params['breadcrumbs'][] = ['label' => 'การกำหนดรูปแบบ FSN', 'url' => ['/am/setting/fsn-format']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<h5 class="mb-0 fw-semibold"><?= Html::encode($this->title) ?></h5>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> กลับรายการรูปแบบ', ['/am/setting/fsn-format'], ['class' => 'btn btn-outline-secondary']) ?>
<?php $this->endBlock(); ?>

<div class="card border-0 shadow-sm rounded-2">
    <div class="card-body">
        <p class="text-muted small mb-3">
            ใช้ตัวแปร: <code>{category}</code> รหัส FSN หมวด, <code>{year}</code> ปี พ.ศ. 2 หลัก, <code>{seq}</code> ลำดับในปี<br>
            ตัวอย่าง pattern: <code>{category}/{year}.{seq}</code> → 7910-003-0003/66.01
        </p>
        <?php $form = ActiveForm::begin(['id' => 'form-fsn-format']); ?>
        <div class="row g-3">
            <div class="col-md-12">
                <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => 'เช่น ลำดับ/ปี'])->label('ชื่อรูปแบบ') ?>
            </div>
            <div class="col-md-12">
                <?= $form->field($model, 'pattern')->textInput(['maxlength' => true, 'class' => 'form-control', 'placeholder' => '{category}/{seq}/{year}'])->label('รูปแบบ (Pattern)')->hint('ต้องมี {category} {year} {seq}') ?>
            </div>
            <div class="col-md-12">
                <div class="form-check">
                    <input type="hidden" name="set_active" value="0">
                    <input class="form-check-input" type="checkbox" name="set_active" value="1" id="set_active" <?= $model->is_active ? 'checked' : '' ?>>
                    <label class="form-check-label" for="set_active">ใช้รูปแบบนี้ทันที (ตั้งเป็นรูปแบบที่ใช้สร้างหมายเลข)</label>
                </div>
            </div>
        </div>
        <div class="mt-4 d-flex gap-2">
            <?= Html::submitButton('<i class="fa-solid fa-check me-1"></i> บันทึก', ['class' => 'btn btn-primary']) ?>
            <?= Html::a('ยกเลิก', ['/am/setting/fsn-format'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

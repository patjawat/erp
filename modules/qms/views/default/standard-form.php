<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\qms\models\Standard $model */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'เพิ่มมาตรฐาน' : 'แก้ไขมาตรฐาน: ' . $model->name;
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ทะเบียนมาตรฐาน<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับทะเบียนมาตรฐาน', ['standards'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="card border shadow-sm">
                <div class="card-header bg-body-tertiary">
                    <h1 class="h5 fw-semibold mb-0"><i class="bi bi-shield-check me-1"></i> <?= Html::encode($this->title) ?></h1>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>
                    <div class="row g-3">
                        <div class="col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'HA'])->hint('รหัสสั้นๆ ไม่ซ้ำ') ?></div>
                        <div class="col-md-8"><?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'Hospital Accreditation']) ?></div>
                        <div class="col-md-4"><?= $form->field($model, 'short_name')->textInput(['maxlength' => true, 'placeholder' => 'HA'])->hint('แสดงบนการ์ด') ?></div>
                        <div class="col-md-8"><?= $form->field($model, 'owner_label')->textInput(['maxlength' => true, 'placeholder' => 'คณะกรรมการ HA'])->hint('ชื่อเจ้าของ/ผู้รับผิดชอบมาตรฐาน') ?></div>
                        <div class="col-md-8"><?= $form->field($model, 'description')->textarea(['rows' => 2]) ?></div>
                        <div class="col-md-4"><?= $form->field($model, 'color')->input('color', ['value' => $model->color ?: '#1a508e'])->hint('สีธีมการ์ด') ?></div>
                        <div class="col-md-4"><?= $form->field($model, 'sort')->textInput(['type' => 'number']) ?></div>
                        <div class="col-md-4 d-flex align-items-center pt-3">
                            <?= $form->field($model, 'is_active')->checkbox() ?>
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>บันทึก', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('ยกเลิก', ['standards'], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

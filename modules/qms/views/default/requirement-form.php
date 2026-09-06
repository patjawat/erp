<?php

use app\widgets\TomSelectWidget;
use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\qms\models\Standard $standard */
/** @var app\modules\qms\models\Requirement $model */
/** @var app\modules\qms\models\Requirement[] $parentOptions */
/** @var array $employeeOptions  id => ชื่อ */

$isNew = $model->isNewRecord;
$this->title = $isNew ? 'เพิ่มข้อกำหนด' : 'แก้ไขข้อกำหนด';
$sid = (int) $standard->id;

$parentList = ArrayHelper::map($parentOptions, 'id', function ($r) {
    return ($r->code ? $r->code . ' ' : '') . $r->title;
});
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?><?= Html::encode($standard->name) ?><?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับรายการข้อกำหนด', ['requirements', 'standard_id' => $sid], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
    </div>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="card border shadow-sm">
                <div class="card-header bg-body-tertiary">
                    <h1 class="h5 fw-semibold mb-0"><i class="bi bi-list-check me-1"></i> <?= Html::encode($this->title) ?></h1>
                </div>
                <div class="card-body">
                    <?php $form = ActiveForm::begin(); ?>
                    <?= $form->field($model, 'parent_id')->dropDownList($parentList, [
                        'prompt' => '— เป็นหมวดบนสุด (ไม่มีข้อแม่) —',
                    ])->hint('เลือกหมวดแม่ ถ้าเว้นว่าง = เป็นหมวด/ข้อระดับบนสุด') ?>

                    <div class="row g-3">
                        <div class="col-md-3"><?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'IC-2.1']) ?></div>
                        <div class="col-md-9"><?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'ต้องมีคำสั่งแต่งตั้งคณะกรรมการ']) ?></div>
                    </div>
                    <?= $form->field($model, 'detail')->textarea(['rows' => 2]) ?>
                    <?= $form->field($model, 'default_assignee_emp_id')->widget(TomSelectWidget::class, [
                        'items' => ['' => '— ไม่ระบุ —'] + $employeeOptions,
                        'options' => ['class' => 'form-select'],
                        'clientOptions' => ['placeholder' => 'ค้นหาพนักงาน...'],
                    ])->hint('ผู้รับผิดชอบตั้งต้น จะติดไปกับ checklist ทุกปีที่เปิดรอบ') ?>
                    <div class="row g-3">
                        <div class="col-md-8"><?= $form->field($model, 'evidence_hint')->textInput(['maxlength' => true, 'placeholder' => 'คำสั่ง / รายงานประชุม / ภาพถ่าย'])->hint('ประเภทหลักฐานที่คาดหวัง') ?></div>
                        <div class="col-md-2"><?= $form->field($model, 'sort')->textInput(['type' => 'number']) ?></div>
                        <div class="col-md-2 d-flex align-items-center pt-3"><?= $form->field($model, 'is_active')->checkbox() ?></div>
                    </div>

                    <div class="d-flex gap-2 mt-2">
                        <?= Html::submitButton('<i class="bi bi-check-lg me-1"></i>บันทึก', ['class' => 'btn btn-primary']) ?>
                        <?= Html::a('ยกเลิก', ['requirements', 'standard_id' => $sid], ['class' => 'btn btn-outline-secondary']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>
</div>

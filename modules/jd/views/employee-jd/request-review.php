<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

$this->title = 'ขอทบทวน JD Revision ' . $jd->revision_no;
$sectionOptions = ArrayHelper::map($jd->sections, 'id', 'title');
?>
<div class="d-flex justify-content-between align-items-start gap-3 mb-3">
    <div><h5 class="mb-1 fw-semibold">ขอทบทวนคำอธิบายงาน</h5><div class="text-muted small">JD Revision <?= (int) $jd->revision_no ?> · <?= Html::encode($employee->fullname) ?></div></div>
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i>กลับไปอ่าน JD', ['/hr/employees/view', 'id' => $employee->id, 'name' => 'job_description_current'], ['class' => 'btn btn-outline-secondary']) ?>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <p class="text-muted">ระบุหัวข้อที่ไม่ตรงกับหน้าที่ปัจจุบัน พร้อมเหตุผลและข้อความที่เสนอให้ปรับ HR จะพิจารณาโดยไม่แก้ไข JD ฉบับที่ประกาศใช้แล้ว</p>
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($request, 'section_id')->dropDownList($sectionOptions, ['prompt' => 'เลือกหัวข้อที่ต้องการทบทวน', 'class' => 'form-select'])->label('หัวข้อ JD') ?>
        <?= $form->field($request, 'reason')->textarea(['rows' => 5, 'class' => 'form-control', 'placeholder' => 'อธิบายว่าส่วนใดไม่ตรงกับหน้าที่หรือบทบาทปัจจุบัน'])->label('เหตุผลที่ขอทบทวน') ?>
        <?= $form->field($request, 'proposed_change')->textarea(['rows' => 5, 'class' => 'form-control', 'placeholder' => 'ระบุข้อความหรือรายละเอียดที่เสนอให้แก้ไข'])->label('ข้อเสนอแนะในการแก้ไข') ?>
        <div class="d-flex justify-content-end gap-2 mt-3">
            <?= Html::a('ยกเลิก', ['/hr/employees/view', 'id' => $employee->id, 'name' => 'job_description_current'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::submitButton('<i class="bi bi-send me-1"></i>ส่งคำขอให้ HR', ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

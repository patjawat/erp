<?php

use yii\helpers\Html;
use yii\bootstrap5\ActiveForm;
use app\components\CategoriseHelper;
use app\modules\jd\models\JdTemplate;
use app\widgets\TomSelectWidget;

/** @var yii\web\View $this */
/** @var JdTemplate $model */

$positions = ['' => 'เลือกตำแหน่งงาน'] + CategoriseHelper::PositionName();
?>
<style>
#jd-template-library-form .form-label{font-size:.8rem;font-weight:600;color:#4a5568;margin-bottom:.4rem}#jd-template-library-form .form-control,#jd-template-library-form .form-select{min-height:42px;border-color:rgba(15,23,42,.14);border-radius:8px}#jd-template-library-form textarea.form-control{min-height:96px}#jd-template-library-form .form-control:focus,#jd-template-library-form .form-select:focus{border-color:#0d6efd;box-shadow:0 0 0 3px rgba(13,110,253,.08)}#jd-template-library-form .invalid-feedback{font-size:.78rem}
</style>
<?php $form = ActiveForm::begin(['id' => 'jd-template-library-form']); ?>
<div class="row g-3">
    <div class="col-md-8">
        <?= $form->field($model, 'name')->textInput(['maxlength' => true, 'placeholder' => 'เช่น พยาบาลวิชาชีพผู้ป่วยใน'])->label('ชื่อ Template') ?>
    </div>
    <div class="col-md-4">
        <?= $form->field($model, 'template_code')->textInput(['maxlength' => true, 'placeholder' => 'เช่น NUR-IPD'])->label('รหัส Template') ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'position_code')->widget(TomSelectWidget::class, [
            'items' => $positions,
            'options' => ['class' => 'form-select'],
            'clientOptions' => ['placeholder' => 'เลือกตำแหน่งงาน', 'allowEmptyOption' => true],
        ])->label('ตำแหน่งงาน') ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'template_type')->dropDownList(['base' => 'มาตรฐานของตำแหน่ง', 'variant' => 'เฉพาะลักษณะงาน'])->label('ประเภท Template') ?>
    </div>
    <div class="col-md-3">
        <?= $form->field($model, 'lifecycle_status')->dropDownList(['draft' => 'ฉบับร่าง', 'review' => 'รอตรวจสอบ', 'active' => 'พร้อมใช้งาน', 'retired' => 'ยกเลิกใช้งาน'])->label('สถานะ') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'อธิบายว่า Template นี้เหมาะกับงานลักษณะใด'])->label('คำอธิบายการใช้งาน') ?>
    </div>
</div>
<div class="d-flex justify-content-end gap-2 border-top pt-3 mt-4">
    <?= Html::a('ยกเลิก', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton($model->isNewRecord ? 'สร้างและจัดทำเนื้อหา' : 'บันทึกและกลับไปจัดทำเนื้อหา', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>

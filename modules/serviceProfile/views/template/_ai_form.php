<?php
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
$formId = 'sp-ai-template-form';
$form = ActiveForm::begin(['id' => $formId, 'options' => ['data-list-url' => Url::to(['index'])]]);
?>
<div class="alert alert-info d-flex gap-3" role="note">
    <i class="bi bi-stars fs-5" aria-hidden="true"></i>
    <div><div class="fw-semibold">AI จะสร้างเป็นฉบับร่างเท่านั้น</div><div class="small">โปรดตรวจชื่อหัวข้อ ประเภทข้อมูล และหัวข้อบังคับก่อนประกาศใช้ ห้ามระบุข้อมูลผู้ป่วยหรือข้อมูลส่วนบุคคล</div></div>
</div>
<div class="row g-3">
    <div class="col-12"><?= $form->field($model, 'owner_id')->dropDownList($ownerOptions, ['class'=>'form-select','prompt'=>'เลือกหน่วยงานหรือทีมประสาน']) ?></div>
    <div class="col-12"><?= $form->field($model, 'name')->textInput(['maxlength'=>true,'placeholder'=>'เช่น Service Profile งานผู้ป่วยนอก']) ?></div>
    <div class="col-12"><?= $form->field($model, 'mission')->textarea(['rows'=>4,'placeholder'=>'อธิบายบริการหลัก กลุ่มผู้รับบริการ กระบวนการสำคัญ และขอบเขตงาน']) ?><div class="form-text">ระบุรายละเอียดที่ทำให้ AI เข้าใจลักษณะเฉพาะของหน่วยงาน โดยไม่ใส่ข้อมูลส่วนบุคคล</div></div>
    <div class="col-12"><?= $form->field($model, 'focus')->textarea(['rows'=>3,'placeholder'=>'เช่น HA, 2P Safety, ความเสี่ยงสำคัญ หรือตัวชี้วัดที่ต้องการเน้น']) ?></div>
    <div class="col-6"><?= $form->field($model, 'section_count')->input('number',['min'=>6,'max'=>20]) ?></div>
    <div class="col-6"><?= $form->field($model, 'effective_fiscal_year')->input('number',['min'=>2500,'max'=>2700]) ?></div>
</div>
<div class="d-flex flex-wrap justify-content-end gap-2 mt-4 pt-3 border-top">
    <?= Html::button('ยกเลิก',['class'=>'btn btn-outline-secondary','data-bs-dismiss'=>'modal']) ?>
    <?= Html::submitButton('<i class="bi bi-stars me-1"></i> สร้างร่างด้วย AI',['class'=>'btn btn-primary','data-loading-text'=>'AI กำลังสร้าง Template...']) ?>
</div>
<?php ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){ if(r.redirect_url){window.location.href=r.redirect_url;return;} });");
?>

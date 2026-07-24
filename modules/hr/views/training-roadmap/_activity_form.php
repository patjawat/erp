<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\hr\models\TrainingRoadmapActivity;
$form = ActiveForm::begin(['id' => 'training-activity-form']);
?>
<div class="row g-3">
    <div class="col-md-2"><?= $form->field($model, 'sequence')->input('number', ['min' => 1])->label('ลำดับ') ?></div>
    <div class="col-md-10"><?= $form->field($model, 'title')->textInput()->label('กิจกรรมหรือหัวข้อพัฒนา') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'activity_type')->dropDownList(TrainingRoadmapActivity::typeOptions())->label('ประเภทกิจกรรม') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'competency_code')->textInput(['placeholder' => 'ถ้ามี'])->label('รหัส Competency') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'competency_level')->dropDownList(['' => 'ไม่กำหนด', 1 => '1 รับรู้และเข้าใจ', 2 => '2 ปฏิบัติภายใต้คำแนะนำ', 3 => '3 ปฏิบัติภายใต้การกำกับ', 4 => '4 ปฏิบัติได้ด้วยตนเอง', 5 => '5 สอนหรือกำกับผู้อื่นได้'])->label('ระดับเป้าหมาย') ?></div>
    <div class="col-md-5"><?= $form->field($model, 'development_method')->textInput(['placeholder' => 'เช่น Preceptor / Bedside Coaching'])->label('วิธีพัฒนา') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'requirement_type')->dropDownList(TrainingRoadmapActivity::requirementOptions())->label('วิธีวัดผล') ?></div>
    <div class="col-md-3"><?= $form->field($model, 'target_value')->input('number', ['min' => 0, 'step' => '0.01'])->label('ค่าเป้าหมาย') ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 3])->label('รายละเอียดและเกณฑ์ผ่าน') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'is_required')->checkbox()->label('เป็นกิจกรรมบังคับ') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'evidence_required')->checkbox()->label('ต้องแนบหลักฐาน') ?></div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?><?= Html::submitButton('บันทึกกิจกรรม', ['class' => 'btn btn-primary']) ?></div>
<?php ActiveForm::end(); ?>
<?php $this->registerJs("handleFormSubmit('#training-activity-form', null, function(){ window.location.reload(); });"); ?>

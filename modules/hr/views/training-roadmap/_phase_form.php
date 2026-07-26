<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\hr\models\TrainingRoadmap;
$form = ActiveForm::begin(['id' => 'training-phase-form']);
?>
<div class="row g-3">
    <div class="col-md-2"><?= $form->field($model, 'sequence')->input('number', ['min' => 1])->label('ลำดับ') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'title')->textInput()->label('ชื่อระยะพัฒนา') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'period_label')->textInput(['placeholder' => 'เช่น สัปดาห์ที่ 1–2'])->label('ข้อความช่วงเวลา') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'start_offset')->input('number', ['min' => 0])->label('เริ่มหลังมอบหมาย') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'end_offset')->input('number', ['min' => 0])->label('สิ้นสุดหลังมอบหมาย') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'offset_unit')->dropDownList(TrainingRoadmap::durationUnitOptions())->label('หน่วยเวลา') ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 3])->label('ผลลัพธ์ของระยะนี้') ?></div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?><?= Html::submitButton('บันทึกระยะพัฒนา', ['class' => 'btn btn-primary']) ?></div>
<?php ActiveForm::end(); ?>
<?php $this->registerJs("handleFormSubmit('#training-phase-form', null, function(){ window.location.reload(); });"); ?>

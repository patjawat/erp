<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\hr\models\TrainingRoadmap;

$formId = 'training-roadmap-form';
?>
<?php $form = ActiveForm::begin(['id' => $formId]); ?>
<div class="row g-3">
    <div class="col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true, 'placeholder' => 'เช่น NUR-ANE-TRM-001'])->label('รหัส Roadmap') ?></div>
    <div class="col-md-8"><?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อ Training Roadmap') ?></div>
    <div class="col-md-5"><?= $form->field($model, 'roadmap_type')->dropDownList(TrainingRoadmap::typeOptions())->label('ประเภท Roadmap') ?></div>
    <div class="col-md-2"><?= $form->field($model, 'version_no')->textInput(['type' => 'number', 'min' => 1])->label('เวอร์ชัน') ?></div>
    <div class="col-md-2"><?= $form->field($model, 'duration_value')->textInput(['type' => 'number', 'min' => 1])->label('ระยะเวลา') ?></div>
    <div class="col-md-3"><?= $form->field($model, 'duration_unit')->dropDownList(TrainingRoadmap::durationUnitOptions())->label('หน่วยเวลา') ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 3])->label('วัตถุประสงค์และผลลัพธ์ที่คาดหวัง') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'status')->dropDownList(TrainingRoadmap::statusOptions())->label('สถานะ') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'effective_from')->input('date')->label('เริ่มใช้วันที่') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'effective_to')->input('date')->label('สิ้นสุดการใช้') ?></div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('บันทึก Roadmap', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end(); ?>
<?php
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){ if(r && r.container && typeof erpReloadPjax === 'function' && erpReloadPjax(r.container)) return; window.location.reload(); });");
?>

<?php
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\hr\models\TrainingRoadmap;
$form = ActiveForm::begin(['id' => 'training-milestone-form']);
$phaseItems = ArrayHelper::map($model->roadmap->phases ?? [], 'id', 'title');
?>
<div class="row g-3">
    <div class="col-md-2"><?= $form->field($model, 'sequence')->input('number', ['min' => 1])->label('ลำดับ') ?></div>
    <div class="col-md-10"><?= $form->field($model, 'title')->textInput()->label('ชื่อจุดประเมิน') ?></div>
    <div class="col-md-5"><?= $form->field($model, 'phase_id')->dropDownList($phaseItems, ['prompt' => 'ไม่ผูกกับระยะใด'])->label('ระยะที่เกี่ยวข้อง') ?></div>
    <div class="col-md-3"><?= $form->field($model, 'due_offset')->input('number', ['min' => 0])->label('ครบกำหนดหลังเริ่ม') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'offset_unit')->dropDownList(TrainingRoadmap::durationUnitOptions())->label('หน่วยเวลา') ?></div>
    <div class="col-12"><?= $form->field($model, 'criteria_text')->textarea(['rows' => 4])->label('เกณฑ์ผ่าน') ?></div>
    <div class="col-md-5"><?= $form->field($model, 'minimum_score')->input('number', ['min' => 0, 'step' => '0.01'])->label('คะแนนขั้นต่ำ ถ้ามี') ?></div>
    <div class="col-md-7 d-flex align-items-end"><?= $form->field($model, 'requires_signoff')->checkbox()->label('ต้องมีผู้ประเมินรับรองผล') ?></div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?><?= Html::submitButton('บันทึกจุดประเมิน', ['class' => 'btn btn-primary']) ?></div>
<?php ActiveForm::end(); ?>
<?php $this->registerJs("handleFormSubmit('#training-milestone-form', null, function(){ window.location.reload(); });"); ?>

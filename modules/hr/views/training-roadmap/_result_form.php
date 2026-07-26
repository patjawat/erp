<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$form = ActiveForm::begin(['id' => 'training-result-form']);
?>
<div class="mb-3"><strong><?= Html::encode($model->activity->title) ?></strong><div class="text-muted small"><?= Html::encode($model->activity->description) ?></div></div>
<div class="row g-3">
    <div class="col-md-6"><?= $form->field($model, 'status')->dropDownList(['pending' => 'ยังไม่เริ่ม', 'in_progress' => 'กำลังดำเนินการ', 'passed' => 'ผ่าน', 'completed' => 'ทำครบแล้ว', 'failed' => 'ยังไม่ผ่าน'])->label('ผลการพัฒนา') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'result_value')->input('number', ['step' => '0.01'])->label('คะแนนหรือจำนวนที่ทำได้') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'competency_level')->dropDownList(['' => 'ไม่ระบุ', 1 => 'ระดับ 1', 2 => 'ระดับ 2', 3 => 'ระดับ 3', 4 => 'ระดับ 4', 5 => 'ระดับ 5'])->label('ระดับความสามารถที่ประเมินได้') ?></div>
    <div class="col-12"><?= $form->field($model, 'result_text')->textarea(['rows' => 4])->label('ผลการประเมินและข้อเสนอแนะ') ?></div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?><?= Html::submitButton('บันทึกผล', ['class' => 'btn btn-primary']) ?></div>
<?php ActiveForm::end(); ?>
<?php $this->registerJs("handleFormSubmit('#training-result-form', null, function(){ window.location.reload(); });"); ?>

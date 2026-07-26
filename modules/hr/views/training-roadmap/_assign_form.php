<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\hr\models\EmployeeTrainingPlan;
$form = ActiveForm::begin(['id' => 'training-assign-form']);
?>
<?php if ($roadmap): ?>
<div class="alert alert-light border mb-3"><strong><?= Html::encode($roadmap->title) ?></strong><br><span class="text-muted">ระบบจะคำนวณวันสิ้นสุดและสร้างรายการติดตามจากแม่แบบเวอร์ชัน <?= (int) $roadmap->version_no ?></span></div>
<?php endif ?>
<div class="row g-3">
    <div class="col-12"><?= $form->field($model, 'roadmap_id')->dropDownList($roadmapItems, ['prompt' => 'เลือก Training Roadmap', 'disabled' => $roadmap !== null])->label('Training Roadmap') ?><?php if ($roadmap): ?><?= Html::activeHiddenInput($model, 'roadmap_id') ?><?php endif ?></div>
    <div class="col-12"><?= $form->field($model, 'emp_id')->dropDownList($employeeItems, ['prompt' => 'เลือกบุคลากร'])->label('บุคลากร') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'start_date')->input('date')->label('วันที่เริ่มแผน') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'status')->dropDownList(EmployeeTrainingPlan::statusOptions())->label('สถานะเริ่มต้น') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'mentor_emp_id')->dropDownList($employeeItems, ['prompt' => 'ยังไม่กำหนด'])->label('พี่เลี้ยง') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'assessor_emp_id')->dropDownList($employeeItems, ['prompt' => 'ยังไม่กำหนด'])->label('ผู้ประเมินหลัก') ?></div>
    <div class="col-12"><?= $form->field($model, 'note')->textarea(['rows' => 3])->label('หมายเหตุ') ?></div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?><?= Html::submitButton('มอบหมาย Roadmap', ['class' => 'btn btn-primary']) ?></div>
<?php ActiveForm::end(); ?>
<?php $this->registerJs("handleFormSubmit('#training-assign-form', null, function(){ window.location.reload(); });"); ?>

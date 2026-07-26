<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\hr\models\IdpCycle;
$form = ActiveForm::begin(['id'=>'idp-cycle-form']);
?>
<div class="row g-3">
    <div class="col-md-8"><?= $form->field($model,'title')->textInput(['maxlength'=>true])->label('ชื่อรอบ IDP') ?></div>
    <div class="col-md-4"><?= $form->field($model,'fiscal_year')->input('number')->label('ปีงบประมาณ') ?></div>
    <div class="col-md-6"><?= $form->field($model,'start_date')->input('date')->label('วันที่เริ่มรอบ') ?></div>
    <div class="col-md-6"><?= $form->field($model,'end_date')->input('date')->label('วันที่สิ้นสุดรอบ') ?></div>
    <div class="col-md-6"><?= $form->field($model,'submission_due_date')->input('date')->label('กำหนดส่งแผน') ?></div>
    <div class="col-md-6"><?= $form->field($model,'review_due_date')->input('date')->label('กำหนดหัวหน้าพิจารณา') ?></div>
    <div class="col-md-4"><?= $form->field($model,'status')->dropDownList(IdpCycle::statusOptions())->label('สถานะ') ?></div>
    <div class="col-12"><?= $form->field($model,'description')->textarea(['rows'=>3])->label('คำอธิบายรอบ') ?></div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><?= Html::button('ยกเลิก',['class'=>'btn btn-light','data-bs-dismiss'=>'modal']) ?><?= Html::submitButton('บันทึกรอบ IDP',['class'=>'btn btn-primary']) ?></div>
<?php ActiveForm::end(); ?>
<?php $this->registerJs("handleFormSubmit('#idp-cycle-form', null, function(){ window.location.reload(); });"); ?>

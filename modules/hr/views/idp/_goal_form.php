<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\hr\models\IdpGoal;
$form=ActiveForm::begin(['id'=>'idp-goal-form']);
?>
<div class="row g-3">
    <div class="col-12"><?= $form->field($model,'title')->textInput(['maxlength'=>true])->label('เป้าหมายการพัฒนา') ?></div>
    <div class="col-md-6"><?= $form->field($model,'source_type')->dropDownList(IdpGoal::sourceOptions())->label('ที่มาของเป้าหมาย') ?></div>
    <div class="col-md-3"><?= $form->field($model,'due_date')->input('date')->label('กำหนดสำเร็จ') ?></div>
    <div class="col-md-3"><?= $form->field($model,'weight_percent')->input('number',['min'=>0,'max'=>100])->label('น้ำหนัก (%)') ?></div>
    <div class="col-12"><?= $form->field($model,'gap_reason')->textarea(['rows'=>2])->label('ช่องว่างหรือเหตุผลที่ต้องพัฒนา') ?></div>
    <div class="col-12"><?= $form->field($model,'expected_outcome')->textarea(['rows'=>2])->label('ผลลัพธ์ที่คาดหวัง') ?></div>
    <div class="col-12"><?= $form->field($model,'success_measure')->textarea(['rows'=>2])->label('วิธีวัดความสำเร็จ') ?></div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><?= Html::button('ยกเลิก',['class'=>'btn btn-light','data-bs-dismiss'=>'modal']) ?><?= Html::submitButton('บันทึกเป้าหมาย',['class'=>'btn btn-primary']) ?></div>
<?php ActiveForm::end(); ?>
<?php $this->registerJs("handleFormSubmit('#idp-goal-form', null, function(){ window.location.reload(); });"); ?>

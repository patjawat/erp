<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\modules\hr\models\IdpActivity;
$form=ActiveForm::begin(['id'=>'idp-activity-form']);
?>
<div class="row g-3">
    <div class="col-12"><?= $form->field($model,'title')->textInput(['maxlength'=>true])->label('กิจกรรมพัฒนา') ?></div>
    <div class="col-md-6"><?= $form->field($model,'method_type')->dropDownList(IdpActivity::methodOptions())->label('วิธีพัฒนา') ?></div>
    <div class="col-md-3"><?= $form->field($model,'due_date')->input('date')->label('กำหนดเสร็จ') ?></div>
    <div class="col-md-3"><?= $form->field($model,'progress_percent')->input('number',['min'=>0,'max'=>100])->label('ความก้าวหน้า (%)') ?></div>
    <div class="col-12"><?= $form->field($model,'evidence_note')->textarea(['rows'=>2])->label('หลักฐานหรือผลที่เกิดขึ้น') ?></div>
    <div class="col-12"><?= $form->field($model,'reflection')->textarea(['rows'=>2])->label('สิ่งที่ได้เรียนรู้') ?></div>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><?= Html::button('ยกเลิก',['class'=>'btn btn-light','data-bs-dismiss'=>'modal']) ?><?= Html::submitButton('บันทึกกิจกรรม',['class'=>'btn btn-primary']) ?></div>
<?php ActiveForm::end(); ?>
<?php $this->registerJs("handleFormSubmit('#idp-activity-form', null, function(){ window.location.reload(); });"); ?>

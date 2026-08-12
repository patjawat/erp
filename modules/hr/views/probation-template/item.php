<?php
use yii\helpers\Html; use yii\widgets\ActiveForm;
$this->title=$model->isNewRecord?'เพิ่มข้อประเมิน':'แก้ไขข้อประเมิน';
?>
<h1 class="h4 mb-1"><?= Html::encode($this->title) ?></h1><p class="text-body-secondary mb-3"><?= Html::encode($template->name) ?> · คะแนนสเกล 1–5</p><section class="card bg-body border shadow-sm"><div class="card-body p-3 p-md-4"><?php $form=ActiveForm::begin(); ?><div class="row g-3"><div class="col-md-9"><?= $form->field($model,'category')->textInput(['maxlength'=>150]) ?></div><div class="col-md-3"><?= $form->field($model,'sequence')->input('number',['min'=>1]) ?></div><div class="col-12"><?= $form->field($model,'question')->textarea(['rows'=>4]) ?></div></div><div class="d-flex justify-content-end gap-2"><?= Html::a('ยกเลิก',['view','id'=>$template->id],['class'=>'btn btn-outline-secondary']) ?><?= Html::submitButton('บันทึกข้อประเมิน',['class'=>'btn btn-primary']) ?></div><?php ActiveForm::end(); ?></div></section>

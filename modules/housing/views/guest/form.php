<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$this->title = 'แจ้งบุคคลภายนอกเข้าพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3"><div class="card border-0 shadow-sm"><div class="card-body"><?php $form = ActiveForm::begin(); ?><div class="row g-3"><div class="col-md-8"><?= $form->field($model, 'guest_name')->textInput()->label('ชื่อ-นามสกุล') ?></div><div class="col-md-4"><?= $form->field($model, 'relationship')->textInput()->label('ความสัมพันธ์') ?></div><div class="col-md-6"><?= $form->field($model, 'citizen_id')->textInput()->label('เลขบัตร/เอกสารระบุตัวตน') ?></div><div class="col-md-6"><?= $form->field($model, 'phone')->textInput()->label('โทรศัพท์') ?></div><div class="col-md-6"><?= $form->field($model, 'start_date')->input('date')->label('วันที่เริ่มพัก') ?></div><div class="col-md-6"><?= $form->field($model, 'end_date')->input('date')->label('วันที่สิ้นสุด') ?></div><div class="col-12"><?= $form->field($model, 'reason')->textarea(['rows' => 4])->label('เหตุผล') ?></div></div><div class="mt-3 d-flex justify-content-end"><?= Html::submitButton('ส่งคำขออนุญาต', ['class' => 'btn btn-primary']) ?></div><?php ActiveForm::end(); ?></div></div></div>

<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
$this->title='ตั้งค่าบัญชีรายปี';$this->params['breadcrumbs'][]=['label'=>'ระบบบัญชี','url'=>['/accounting/dashboard']];$this->params['breadcrumbs'][]=['label'=>'รายการบัญชีร่าง','url'=>['index']];$this->params['breadcrumbs'][]=$this->title;
$this->beginBlock('page-title'); ?><h4 class="mb-0"><?= Html::encode($this->title) ?></h4><?php $this->endBlock();$this->beginBlock('sub-title'); ?>กำหนดบัญชีควบคุมก่อนสร้างรายการตั้งเจ้าหนี้<?php $this->endBlock();$this->beginBlock('page-action'); ?><?= Html::a('กลับรายการบัญชีร่าง',['index'],['class'=>'btn btn-outline-secondary']) ?><?php $this->endBlock(); ?>
<section class="card border"><div class="card-body"><div class="row"><div class="col-xl-8"><?php $form=ActiveForm::begin(); ?><?= $form->errorSummary($model,['class'=>'alert alert-danger']) ?>
<?= $form->field($model,'fiscal_year')->textInput(['readonly'=>true])->label('ปีงบประมาณ') ?>
<?php if(!$version): ?><div class="alert alert-warning">ยังไม่มีผังบัญชีโรงพยาบาลที่เปิดใช้ในปีนี้ กรุณาเปิดใช้ผังก่อนตั้งค่า</div><?php else: ?><div class="alert alert-secondary">ใช้ผัง <?= Html::encode($version->version_code) ?> · <?= Html::encode($version->title) ?></div><?php endif; ?>
<?= Html::activeHiddenInput($model,'chart_version_id') ?>
<?= $form->field($model,'payable_account_id')->widget(Select2::class,['data'=>$payableOptions,'options'=>['placeholder'=>'ค้นหาบัญชีเจ้าหนี้หมวดหนี้สิน','disabled'=>!$version],'pluginOptions'=>['allowClear'=>true,'width'=>'100%']])->label('บัญชีเจ้าหนี้การค้า') ?>
<?= $form->field($model,'input_vat_account_id')->widget(Select2::class,['data'=>$vatOptions,'options'=>['placeholder'=>'ค้นหาบัญชีภาษีซื้อ (เว้นว่างได้ถ้าไม่มี VAT)','disabled'=>!$version],'pluginOptions'=>['allowClear'=>true,'width'=>'100%']])->label('บัญชีภาษีซื้อ') ?>
<?= $form->field($model,'note')->textarea(['rows'=>3,'maxlength'=>1000])->label('หมายเหตุ') ?>
<?= Html::submitButton('บันทึกค่าตั้งต้น',['class'=>'btn btn-primary','disabled'=>!$version]) ?><?php ActiveForm::end(); ?></div></div></div></section>

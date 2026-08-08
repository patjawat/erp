<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
app\assets\FormGuardAsset::register($this);
$labels = ['mission' => 'พันธกิจ', 'issue' => 'ประเด็นยุทธศาสตร์', 'goal' => 'เป้าประสงค์', 'tactic' => 'กลยุทธ์'];
$this->title = ($model->isNewRecord ? 'เพิ่ม' : 'แก้ไข') . $labels[$type];
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'strategy']) ?><?php $this->endBlock();
$form = ActiveForm::begin();
?>
<div class="mb-4"><h1 class="h3 mb-1"><?= Html::encode($this->title) ?></h1><p class="text-muted mb-0"><?= Html::encode($plan->name) ?></p></div>
<div class="card border-0 shadow-sm"><div class="card-body p-4"><div class="row g-3">
<div class="col-12 col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength'=>true]) ?></div>
<div class="col-12 col-md-2"><?= $form->field($model, 'sort_order')->textInput(['type'=>'number','min'=>0]) ?></div>
<div class="col-12"><?= $form->field($model, 'name')->textarea(['rows'=>4]) ?></div>
<div class="col-12"><?= $form->field($model, 'is_active')->checkbox() ?></div>
</div></div><div class="card-footer bg-body d-flex justify-content-between p-3">
<?= Html::a('ยกเลิก', ['/pm/strategy-plan/view','id'=>$plan->id], ['class'=>'btn btn-outline-secondary']) ?>
<?= Html::submitButton('บันทึก', ['class'=>'btn btn-primary']) ?>
</div></div><?php ActiveForm::end(); ?>

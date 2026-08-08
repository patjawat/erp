<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\db\ActiveRecord $model @var string $type @var \app\modules\pm\models\StrategyPlan $plan */
/** @var array $goals @var array $tactics @var array $measures */

app\assets\RichTextAsset::register($this);
$labels = ['factor' => 'ปัจจัยความสำเร็จ/RCA', 'measure' => 'มาตรการ', 'program' => 'แผนงานหลัก'];
$rich = ['rows' => 4, 'data-richtext' => 'name', 'data-rte-label' => 'รายละเอียด'];
$this->title = ($model->isNewRecord ? 'เพิ่ม' : 'แก้ไข') . $labels[$type];
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => $type === 'program' ? 'program' : 'indicator']) ?><?php $this->endBlock();
$form = ActiveForm::begin();
?>
<div class="mb-4"><h2 class="h5 mb-1"><?= Html::encode($this->title) ?></h2><p class="text-muted mb-0"><?= Html::encode($plan->name) ?> · รุ่น <?= (int) $plan->version ?></p></div>
<div class="card border-0 shadow-sm"><div class="card-body p-4"><div class="row g-3">
<?php if ($type === 'measure'): ?>
    <div class="col-12 col-md-4"><?= $form->field($model, 'code')->textInput() ?></div>
    <div class="col-12 col-md-4"><?= $form->field($model, 'fiscal_year')->textInput(['type' => 'number']) ?></div>
    <div class="col-12"><?= $form->field($model, 'tactic_id')->dropDownList($tactics, ['prompt' => 'ยังไม่จัดเข้ากลยุทธ์'])->hint('เป้าประสงค์จะยึดตามกลยุทธ์ที่เลือก') ?></div>
    <div class="col-12"><?= $form->field($model, 'name')->textarea($rich) ?></div>
<?php elseif ($type === 'factor'): ?>
    <div class="col-12 col-md-4"><?= $form->field($model, 'code')->textInput() ?></div>
    <div class="col-12 col-md-4"><?= $form->field($model, 'factor_type')->dropDownList(['success_factor' => 'ปัจจัยความสำเร็จ', 'rca' => 'RCA']) ?></div>
    <div class="col-12"><?= $form->field($model, 'name')->textarea($rich) ?></div>
<?php else: ?>
    <div class="col-12 col-md-4"><?= $form->field($model, 'code')->textInput() ?></div>
    <div class="col-12 col-md-4"><?= $form->field($model, 'fiscal_year')->textInput(['type' => 'number']) ?></div>
    <div class="col-12"><?= $form->field($model, 'measure_id')->dropDownList($measures, ['prompt' => 'ไม่ผูกมาตรการ']) ?></div>
    <div class="col-12"><?= $form->field($model, 'name')->textarea($rich) ?></div>
    <div class="col-12"><?= $form->field($model, 'owner_text')->textInput(['maxlength' => true]) ?></div>
<?php endif; ?>
    <div class="col-6 col-md-2"><?= $form->field($model, 'sort_order')->textInput(['type' => 'number', 'min' => 0]) ?></div>
    <div class="col-12"><?= $form->field($model, 'is_active')->checkbox() ?></div>
</div></div>
<div class="card-footer bg-body d-flex justify-content-between p-3">
    <?= Html::a('ยกเลิก', ['index', 'type' => $type, 'planId' => $plan->id], ['class' => 'btn btn-outline-secondary']) ?>
    <?= Html::submitButton('บันทึก', ['class' => 'btn btn-primary']) ?>
</div></div>
<?php ActiveForm::end(); ?>

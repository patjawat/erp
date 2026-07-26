<?php
use app\modules\housing\models\Unit;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
$formId = 'housing-unit-form';
$form = ActiveForm::begin(['id' => $formId, 'options' => ['data-list-url' => Url::to(['index'])]]);
?>
<div class="row g-3">
    <div class="col-md-6"><?= $form->field($model, 'building_id')->dropDownList($buildingOptions, ['prompt' => 'เลือกอาคาร']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'floor_id')->dropDownList($floorOptions, ['prompt' => 'ไม่ระบุชั้น']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-8"><?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'electric_account_no')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'occupancy_mode')->dropDownList(Unit::modeOptions()) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'capacity')->input('number', ['min' => 1]) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'status')->dropDownList(Unit::statusOptions()) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'monthly_base_fee')->input('number', ['step' => '.01']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'sort_order')->input('number') ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 2]) ?></div>
</div>
<div class="mt-3 d-flex justify-content-end gap-2"><?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?><?= Html::submitButton('บันทึกยูนิต', ['class' => 'btn btn-primary']) ?></div>
<?php ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){if(r&&r.container&&typeof erpReloadPjax==='function'&&erpReloadPjax(r.container)){return;}window.location.href=document.querySelector('#{$formId}').dataset.listUrl;});");
?>

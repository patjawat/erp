<?php
use app\modules\housing\models\Unit;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
$formId = 'housing-room-form';
$form = ActiveForm::begin(['id' => $formId]);
?>
<?= $form->field($model, 'unit_id')->hiddenInput()->label(false) ?>
<div class="bg-body-tertiary border rounded-3 p-3 mb-3">ห้อง <?= Html::encode($unit->code . ' · ' . $unit->name) ?></div>
<div class="row g-3">
    <div class="col-md-5"><?= $form->field($model, 'code')->textInput() ?></div>
    <div class="col-md-7"><?= $form->field($model, 'name')->textInput() ?></div>
    <div class="col-md-6"><?= $form->field($model, 'capacity')->input('number', ['min' => 1]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'status')->dropDownList(Unit::statusOptions()) ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 2]) ?></div>
</div>
<div class="mt-3 d-flex justify-content-end gap-2"><?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?><?= Html::submitButton($model->isNewRecord ? 'เพิ่มห้องย่อย' : 'บันทึกห้องย่อย', ['class' => 'btn btn-primary']) ?></div>
<?php ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){if(r&&r.container&&typeof erpReloadPjax==='function'&&erpReloadPjax(r.container)){return;}location.reload();});");
?>

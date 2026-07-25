<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$formId = 'housing-floor-form';
$form = ActiveForm::begin(['id' => $formId]);
?>
<?= $form->field($model, 'building_id')->hiddenInput()->label(false) ?>
<div class="alert alert-light border mb-3">
    อาคาร <?= Html::encode($building->code . ' · ' . $building->name) ?>
</div>
<div class="row g-3">
    <div class="col-md-4"><?= $form->field($model, 'floor_no')->input('number') ?></div>
    <div class="col-md-8"><?= $form->field($model, 'name')->textInput(['placeholder' => 'เช่น ชั้น 1']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'sort_order')->input('number') ?></div>
    <div class="col-md-8"><?= $form->field($model, 'description')->textarea(['rows' => 2]) ?></div>
</div>
<div class="mt-3 d-flex justify-content-end gap-2">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('เพิ่มชั้น', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){if(r&&r.container&&typeof erpReloadPjax==='function'){erpReloadPjax(r.container);}});");
?>

<?php

use app\modules\housing\models\Building;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;

$formId = 'housing-building-form';
$form = ActiveForm::begin(['id' => $formId, 'options' => ['data-list-url' => Url::to(['index'])]]);
?>
<div class="row g-3">
    <div class="col-md-4"><?= $form->field($model, 'code')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-8"><?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'building_type')->dropDownList(Building::typeOptions()) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'status')->dropDownList(Building::statusOptions()) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'sort_order')->input('number') ?></div>
    <div class="col-md-8"><?= $form->field($model, 'address')->textarea(['rows' => 2]) ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 3]) ?></div>
</div>
<div class="mt-3 d-flex justify-content-end gap-2">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('บันทึกข้อมูล', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){if(r&&r.container&&typeof erpReloadPjax==='function'&&erpReloadPjax(r.container)){return;}window.location.href=document.querySelector('#{$formId}').dataset.listUrl;});");
?>

<?php
use app\modules\housing\models\AssetAssignment;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$formId = 'housing-asset-form';
$form = ActiveForm::begin(['id' => $formId, 'options' => ['enctype' => 'multipart/form-data']]);
?>
<div class="alert alert-light border mb-3">
    <strong><?= Html::encode($room ? $room->code . ' · ' . $room->name : $unit->code . ' · ' . $unit->name) ?></strong>
</div>
<div class="row g-3">
    <div class="col-md-8"><?= $form->field($model, 'item_name')->textInput(['maxlength' => true, 'placeholder' => 'เช่น เตียง 5 ฟุต']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'category')->textInput(['maxlength' => true, 'placeholder' => 'เช่น เฟอร์นิเจอร์']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'quantity')->input('number', ['min' => 0, 'step' => '.01']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'unit_name')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'unit_price')->input('number', ['min' => 0, 'step' => '.01']) ?></div>
    <div class="col-md-3"><?= $form->field($model, 'monthly_rent')->input('number', ['min' => 0, 'step' => '.01']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'condition_status')->dropDownList(AssetAssignment::conditionOptions()) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'assigned_at')->input('date') ?></div>
    <div class="col-12"><?= $form->field($model, 'image_file')->fileInput(['accept' => 'image/jpeg,image/png,image/webp'])->hint('รองรับ JPG, PNG และ WebP ขนาดไม่เกิน 10 MB') ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 2]) ?></div>
</div>
<div class="mt-3 d-flex justify-content-end gap-2">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('บันทึกรายการ', ['class' => 'btn btn-primary']) ?>
</div>
<?php
ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){if(r&&r.redirect){window.location.href=r.redirect;}});");
?>

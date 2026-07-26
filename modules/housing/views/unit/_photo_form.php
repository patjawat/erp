<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$formId = 'housing-location-photo-form';
$form = ActiveForm::begin(['id' => $formId, 'options' => ['enctype' => 'multipart/form-data']]);
?>
<div class="alert alert-light border mb-3">
    เพิ่มรูปภาพสำหรับ <strong><?= Html::encode($room ? $room->code . ' · ' . $room->name : $unit->code . ' · ' . $unit->name) ?></strong>
</div>
<?= $form->field($model, 'photo_file')->fileInput(['accept' => 'image/jpeg,image/png,image/webp'])->hint('รองรับ JPG, PNG และ WebP ขนาดไม่เกิน 10 MB และความละเอียดไม่เกิน 50 ล้านพิกเซล') ?>
<?= $form->field($model, 'caption')->textInput(['maxlength' => true, 'placeholder' => 'เช่น ภาพภายในห้อง มุมด้านหน้าต่าง']) ?>
<?= $form->field($model, 'is_primary')->checkbox() ?>
<div class="mt-3 d-flex justify-content-end gap-2">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('เพิ่มรูปภาพ', ['class' => 'btn btn-primary']) ?>
</div>
<?php
ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){if(r&&r.redirect){window.location.href=r.redirect;}});");
?>

<?php

use app\modules\housing\models\MaintenanceRequest;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin([
    'id' => 'my-housing-maintenance-form',
    'options' => ['enctype' => 'multipart/form-data'],
]);
$locationName = implode(' / ', array_filter([
    $occupancy->unit?->building?->name,
    $occupancy->unit?->floor?->name,
    $occupancy->unit?->name,
    $occupancy->room?->name,
]));
$scopeOptions = array_intersect_key(MaintenanceRequest::scopeOptions(), array_flip([
    MaintenanceRequest::SCOPE_HOUSE,
    MaintenanceRequest::SCOPE_UNIT,
    MaintenanceRequest::SCOPE_ROOM,
]));
?>
<div class="alert alert-light border"><div class="small text-body-secondary">สถานที่แจ้งปัญหา</div><strong><?= Html::encode($locationName) ?></strong></div>
<div class="row g-3">
    <div class="col-md-8"><?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'เช่น น้ำรั่วบริเวณห้องน้ำ']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'priority')->dropDownList(MaintenanceRequest::priorityOptions()) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'problem_scope')->dropDownList($scopeOptions) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'location_note')->textInput(['maxlength' => true, 'placeholder' => 'ระบุจุดที่พบปัญหา']) ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 4, 'placeholder' => 'อธิบายปัญหาและผลกระทบที่พบ']) ?></div>
    <div class="col-12"><?= $form->field($model, 'before_photos')->fileInput(['multiple' => true, 'accept' => 'image/jpeg,image/png,image/webp'])->hint('เพิ่มภาพประกอบได้สูงสุด 10 ภาพ ขนาดไม่เกิน 10 MB ต่อไฟล์') ?></div>
</div>
<div class="mt-3 d-flex justify-content-end gap-2">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-outline-secondary', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('ส่งรายการแจ้งปัญหา', ['class' => 'btn btn-primary']) ?>
</div>
<?php ActiveForm::end();
$this->registerJs(<<<'JS'
handleFormSubmit('#my-housing-maintenance-form', null, function(response){
    if (response && response.redirect) window.location.href = response.redirect;
});
JS);
?>

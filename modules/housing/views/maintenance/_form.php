<?php
use app\modules\housing\models\MaintenanceRequest;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$formId = 'housing-maintenance-form';
$form = ActiveForm::begin(['id' => $formId, 'options' => ['enctype' => 'multipart/form-data']]);
?>
<div class="row g-3">
    <div class="col-md-7"><?= $form->field($model, 'building_id')->dropDownList($buildingOptions, ['prompt' => 'เลือกบ้านพักหรือแฟลต']) ?></div>
    <div class="col-md-5"><?= $form->field($model, 'location_note')->textInput(['maxlength' => true, 'placeholder' => 'เช่น ห้อง 101, ชั้น 2 หรือบริเวณส่วนกลาง']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'reporter_type')->dropDownList(MaintenanceRequest::reporterTypeOptions(), ['id' => 'maintenance-reporter-type']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'problem_scope')->dropDownList(MaintenanceRequest::scopeOptions()) ?></div>
    <div class="col-12" id="maintenance-occupancy-field"><?= $form->field($model, 'occupancy_id')->dropDownList($occupancyOptions, ['prompt' => 'เลือกผู้พักอาศัยและที่พัก']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'reported_at')->input('datetime-local') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'reporter_name')->textInput(['maxlength' => true]) ?></div>
    <div class="col-md-8"><?= $form->field($model, 'title')->textInput(['maxlength' => true, 'placeholder' => 'เช่น น้ำรั่วบริเวณห้องน้ำ']) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'priority')->dropDownList(MaintenanceRequest::priorityOptions()) ?></div>
    <div class="col-12"><?= $form->field($model, 'description')->textarea(['rows' => 3, 'placeholder' => 'อธิบายปัญหาที่พบและผลกระทบ']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'assigned_employee_id')->dropDownList($employeeOptions, ['prompt' => 'ยังไม่มอบหมาย']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'status')->dropDownList(MaintenanceRequest::statusOptions()) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'acknowledgement_status')->dropDownList(MaintenanceRequest::acknowledgementOptions()) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'repaired_at')->input('datetime-local') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'expense_amount')->input('number', ['min' => 0, 'step' => '.01']) ?></div>
    <div class="col-12"><?= $form->field($model, 'resolution')->textarea(['rows' => 3, 'placeholder' => 'บันทึกวิธีแก้ไขหรือเหตุผลที่ไม่สามารถดำเนินการ']) ?></div>
    <div class="col-md-6"><?= $form->field($model, 'before_photos')->fileInput(['multiple' => true, 'accept' => 'image/jpeg,image/png,image/webp'])->hint('รวมได้ไม่เกิน 10 ภาพ ขนาดไม่เกิน 10 MB และความละเอียดไม่เกิน 50 ล้านพิกเซลต่อไฟล์') ?></div>
    <div class="col-md-6"><?= $form->field($model, 'after_photos')->fileInput(['multiple' => true, 'accept' => 'image/jpeg,image/png,image/webp'])->hint('เพิ่มภายหลังได้ รวมไม่เกิน 10 ภาพ ขนาดไม่เกิน 10 MB และความละเอียดไม่เกิน 50 ล้านพิกเซลต่อไฟล์') ?></div>
</div>
<div class="mt-3 d-flex justify-content-end gap-2">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('บันทึกแจ้งซ่อม', ['class' => 'btn btn-primary']) ?>
</div>
<?php
ActiveForm::end();
$this->registerJs("handleFormSubmit('#{$formId}', null, function(r){if(r&&r.redirect){window.location.href=r.redirect;}});");
$this->registerJs("
function toggleMaintenanceReporterFields(){
    var isResident = $('#maintenance-reporter-type').val() === 'resident';
    $('#maintenance-occupancy-field').toggle(isResident);
    if (!isResident) {
        $('#maintenancerequest-occupancy_id').val('');
        $('#maintenancerequest-acknowledgement_status').val('not_required');
    } else if ($('#maintenancerequest-acknowledgement_status').val() === 'not_required') {
        $('#maintenancerequest-acknowledgement_status').val('pending');
    }
}
$('#maintenance-reporter-type').on('change', toggleMaintenanceReporterFields);
toggleMaintenanceReporterFields();
");
?>

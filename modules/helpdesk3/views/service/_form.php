<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;


/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);

?>

<?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/me/repair-v2/create-validator']
    ]); ?>
<?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'name')->hiddenInput(['value' => 'repair'])->label(false) ?>

<div class="row g-3">
    <div class="col-12">
        <div class="alert alert-primary">
            <i class="bi bi-info-circle me-2"></i>
            กรุณากรอกข้อมูลให้ครบถ้วนเพื่อความรวดเร็วในการดำเนินการ
        </div>
    </div>

    <div class="col-12 col-md-6">
        <?=$form->field($model, 'device_type_id')->widget(Select2::classname(), [
                    'data' => $model->listDeviceType(),
                    'options' => ['placeholder' => 'เลือกประเภทอุปกรณ์ ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('ประเภทอุปกรณ์');
                ?>
    </div>

    <div class="col-12 col-md-6">
            <?= $form->field($model, 'asset_number')->widget(Select2::classname(), [
                'data' => $model->listAsset(),
                'options' => ['placeholder' => 'เลือกครุภัณฑ์ ...'],
                'pluginOptions' => [
                    'allowClear' => true,
                    'dropdownParent' => '#main-modal',
                ],
                'pluginEvents' => [
                    "select2:select" => "function(e) { getRepairGroup(e.params.data.id); }",
                ],
            ])->label('รหัสครุภัณฑ์'); ?>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'title')->textArea(['rows' => 3, 'placeholder' => 'กรุณาอธิบายปัญหาที่พบโดยละเอียด...'])->label('รายละเอียดปัญหา') ?>
    </div>

    <div class="col-12 col-md-6">
        <?= $form->field($model, 'data_json[location]')->textInput(['placeholder'=>'เช่น ห้อง 301, แผนกบัญชี'])->label('สถานที่') ?>
    </div>
     <div class="col-12 col-md-6">
        <?= $form->field($model, 'data_json[phone]')->textInput(['placeholder'=>'เบอร์โทรศัพท์ติดต่อ'])->label('โทร') ?>
    </div>

    <div class="col-12 col-md-6">
        <?=$form->field($model, 'repair_group')->widget(Select2::classname(), [
                    'data' => $model->listRepairGroup(),
                    'options' => ['placeholder' => 'เลือกแผนกช่าง ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('แผนกช่าง');
                ?>

    </div>

    <div class="col-12 col-md-6">
        <?=$form->field($model, 'data_json[urgency]')->widget(Select2::classname(), [
                    'data' => $model->listUrgency(),
                    'options' => ['placeholder' => 'เลือกความเร่งด่วน ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('ความเร่งด่วน');
                ?>

    </div>

    <div class="col-12 col-md-12">
        <?= $form->field($model, 'request_repair_date')->textInput(['placeholder' => 'เลือกวันที่ต้องการให้ซ่อม'])->label('วันที่ต้องการให้ซ่อม'); ?>
    </div>

    <div class="col-12">
        <?=$model->upload('repair_request')?>

    </div>

    <div class="col-12" id="imagePreviewContainer" style="display: none;">
        <div class="d-flex flex-wrap gap-2 mt-2" id="imagePreview"></div>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 5, 'placeholder' => 'ข้อมูลเพิ่มเติมที่อาจเป็นประโยชน์ต่อการซ่อม...'])->label('หมายเหตุเพิ่มเติม') ?>

    </div>

    <div class="col-12 d-flex justify-content-center mt-4 gap-3">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-send me-1"></i>
            บันทึก
        </button>
        <button type="button" class="btn btn-outline-secondary me-2">ยกเลิก</button>
    </div>
</div>
</div>

<?php ActiveForm::end(); ?>
<?php
$getRepairGroupUrl = Url::to(['/me/repair-v2/get-repair-group']);
$assetNumber = json_encode($model->asset_number);
$js = <<< JS

     if ($assetNumber) {
        getRepairGroup($assetNumber);
    }
    
    thaiDatepicker('#helpdesk-request_repair_date');


    handleFormSubmit('#form', null, async function(response) {
        await location.reload();
    });
    function getRepairGroup(id)
    {
        $.ajax({
            type: "get",
            url: "$getRepairGroupUrl",
            data: {id:id},
            dataType: "json",
            success: function (response) {
                console.log(response);
               $('#helpdesk-repair_group').val(response).trigger('change');
                
            }
        });
    }

JS;
$this->registerJS($js,View::POS_END)
?>
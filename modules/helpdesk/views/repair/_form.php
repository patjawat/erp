<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
use softark\duallistbox\DualListbox;
use yii\helpers\ArrayHelper;
use kartik\date\DatePicker;
use kartik\widgets\DateTimePicker;
use app\modules\hr\models\Employees;
use kartik\datecontrol\DateControl;
/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */

$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);


?>
<div class="repair-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-repair',
        'enableAjaxValidation'      => true,//เปิดการใช้งาน AjaxValidation
        'validationUrl' =>['/helpdesk/repair/create-validator']
    ]); ?>

    <!-- เอาเก็บข้อมูล auto -->
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'code')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[technician_name]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[status_name]')->hiddenInput(['value' => 'ร้องขอ'])->label(false) ?>
    <!-- ## End ## -->

    <!-- ถ้าเป็นการสร้างใหม่ -->
    <?php if($model->isNewRecord):?>
    <?= $form->field($model, 'data_json[create_name]')->hiddenInput(['value' => $emp->fullname])->label(false) ?>
    <?= $form->field($model, 'status')->hiddenInput(['value' => 1])->label(false) ?>
    <?= $form->field($model, 'data_json[send_type]')->hiddenInput(['general' => 'ทั่วไป','asset' => 'ครุภัณฑ์'],['inline'=>false,'custom' => true])->label(false) ?>
    <?= $form->field($model, 'data_json[title]')->textInput(['placeholder' => 'ระบุอาการเสีย...'])->label('อาการเสีย') ?>
    <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 5,'placeholder' => 'ระบุรายละเอียดเพิ่มเติมของอาการเสีย...'])->label('เพิ่มเติม') ?>
    <?= $form->field($model, 'data_json[location]')->textInput(['placeholder' => 'ระบุสถานที่เกิดเหตุ...'])->label('สถานที่') ?>
    <?= $form->field($model, 'data_json[urgency]')->radioList($model->listUrgency(),['inline'=>true,'custom' => true])->label('ความเร่งด่วน') ?>
    <?=$model->upload('req_repair')?>
    <!-- ## End ## -->

    <?php else:?>
    <!-- ถ้าเป็นการแก้ไข -->
    <?= $form->field($model, 'data_json[urgency]')->hiddenInput($model->listUrgency(),['inline'=>true,'custom' => true])->label(false) ?>
    <?= $form->field($model, 'data_json[location]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[send_type]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'status')->hiddenInput()->label(false) ?>
    <?php
        //  echo $form->field($model, 'status')->widget(Select2::classname(), [
        //             'data' => $model->listRepairStatus(),
        //             'options' => ['placeholder' => 'ระบุสถานะการซ่อม ...'],
        //             'pluginEvents' => [
        //                 "select2:select" => "function() {
        //                    $('#helpdesk-data_json-status_name').val($(this).select2('data')[0].text)
        //                 }"
        //             ],
        //             'pluginOptions' => [
        //                 'allowClear' => true
        //             ],
        //             ])->label('สถานะ') 
                    ?>



    <div class="d-flex bg-primary justify-content-between bg-opacity-10 p-3 rounded mb-3">
        <div><i class="bi bi-check2-circle fs-5"></i> <span class="text-primary">การประเมินงานซ่อมและมอบหมายงาน</span></div>
        <div>ขั้นตอนที่ <span class="badge rounded-pill bg-primary text-white">1</span> </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="border border-1 border-primary p-3 rounded">
                <?=$form->field($model, 'data_json[start_job]')->checkbox(['custom' => true, 'switch' => true])->label('ดำเนินการวันที่');?>
                <?php
                  echo $form->field($model, 'data_json[start_job_date]')->widget(DateTimePicker::classname(), [
                      'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                      'language' => 'th',
                      'pluginOptions' => [
                          'autoclose' => true,
                          'format' => 'mm/dd/yyyy H:i:s'
                      ],
                        ])->label(false)?>
            </div>
        </div>
        <div class="col-6">
            <div class="border border-1 border-primary p-3 rounded" style="height: 127px;">
                <?= $form->field($model, 'data_json[repair_type]')->radioList(['ซ่อมภายใน' => 'ซ่อมภายใน','ซ่อมภายนอก' => 'ซ่อมภายนอก'],['inline'=>true,'custom' => true])->label('ประเภทารซ่อม') ?>
            </div>
        </div>
ิ
        <div class="col-12">
            <div class="my-3">
            <?php
            $items = ArrayHelper::map(Employees::find()->limit(10)->all(),'fullname',function($model){
                return $model->fullname;
            });
            echo DualListbox::widget([
                'model' => $model,
                'attribute' => 'data_json[join]',
                'items' => $model->listTecName(),
                'options' => [
                    'multiple' => true,
                    'size' => 8,
                ],
                'clientOptions' => [
                    'moveOnSelect' => false,
                    'selectedListLabel' => 'ผู้รับมอบหมาย',
                    'nonSelectedListLabel' => 'ช่างเทคนิค',
                ],
            ]);
        ?>
                 </div>
        </div>
    </div>


    <div class="d-flex bg-primary justify-content-between bg-opacity-10 p-3 rounded mb-3">
        <div><i class="bi bi-check2-circle fs-5"></i> <span class="text-primary">สรุปผลดำเนินงานและวิธีแก้ไข</span></div>
        <div>ขั้นตอนที่ <span class="badge rounded-pill bg-primary text-white">2</span> </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="border border-1 border-primary p-3 rounded">
                <?=$form->field($model, 'data_json[end_job]')->checkbox(['custom' => true, 'switch' => true])->label('เสร็จวันที่');?>
                <?php
                  echo $form->field($model, 'data_json[end_job_date]')->widget(DateTimePicker::classname(), [
                      'options' => ['placeholder' => 'ระบุวันที่แล้วเสร็จ ...'],
                      'language' => 'th',
                      'pluginOptions' => [
                          'autoclose' => true,
                          'format' => 'mm/dd/yyyy H:i:s'
                      ],
                      ])->label(false)?>
            </div>
            <?= $form->field($model, 'data_json[price]')->textInput(['placeholder' => 'ระบุมูลค่าการซ่อมถ้ามี','type' => 'number'])->label('มูลค่าการซ่อม') ?>
        </div>

        <div class="col-6">
            <?= $form->field($model, 'data_json[repair_note]')->textArea(['style' => 'height: 127px;','placeholder' => 'ระบุวิธีการแก้ไข/แนวทางแก้ไข/อื่นๆ...'])->label(false) ?>
        </div>

        <?=$model->Upload('repair')?>

        <?= $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[note]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[accept_time]')->hiddenInput()->label(false) ?>
        <?php endif;?>

       

        <div class="form-group mt-3 d-flex justify-content-center">
            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary','id' => "summit"]) ?>
        </div>
        <?php ActiveForm::end(); ?>

    </div>

    <?php
    $urlDateNow = Url::to(['/helpdesk/default/datetime-now']);
$js = <<<JS

 
 $("#helpdesk-data_json-start_job").change(function() {
    if(this.checked) {
        $.get("$urlDateNow",function (data, textStatus, jqXHR) 
        {
            $('#helpdesk-data_json-start_job_date').val(data).trigger('change')
        },"json");
        $('#helpdesk-status').val(3)
    }else{
        $('#helpdesk-data_json-start_job_date').val('');
        $('#helpdesk-status').val(2)
        $('#helpdesk-data_json-end_job').prop('checked', false)
        // $('#helpdesk-data_json-end_job_date').val('');
    }
});

$("#helpdesk-data_json-end_job").change(function() {
    if(this.checked) {
        $.get("$urlDateNow",function (data, textStatus, jqXHR) 
        {
            $('#helpdesk-data_json-end_job_date').val(data).trigger('change')
        },"json");
        $('#helpdesk-status').val(4)

        if($("#helpdesk-data_json-start_job").is(":checked")) {

        }else{
alert('ระุบดำเนินการก่อน');
$(this).prop('checked', false)
        }
    }else{
        $('#helpdesk-data_json-end_job_date').val('');
        // $('#helpdesk-status').val(3).trigger('change')
        $('#helpdesk-status').val(3)
    }
});



$('#form-repair').on('beforeSubmit', function (e) {
    var form = $(this);
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                closeModal()
                success()
                try {
                    loadRepairHostory()
                } catch (error) {
                    
                }
                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
            }
        }
    });
    return false;
});

JS;
$this->registerJS($js)
?>
<?php

use app\modules\hr\models\Employees;
use kartik\date\DatePicker;
use kartik\datecontrol\DateControl;
use kartik\form\ActiveField;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use kartik\widgets\DateTimePicker;
use softark\duallistbox\DualListbox;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);

?>
<div class="repair-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form-repair',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/helpdesk/repair/create-validator']
    ]); ?>

    <!-- เอาเก็บข้อมูล auto -->
    <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?php //  $form->field($model, 'code')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[technician_name]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[status_name]')->hiddenInput(['value' => 'ร้องขอ'])->label(false) ?>
    <!-- ## End ## -->

    <!-- ถ้าเป็นการสร้างใหม่ -->
    <?php if ($model->isNewRecord): ?>
    <?= $form->field($model, 'data_json[create_name]')->hiddenInput(['value' => $emp->fullname])->label(false) ?>
    <?= $form->field($model, 'status')->hiddenInput(['value' => 1])->label(false) ?>
    <?= $form->field($model, 'data_json[location_other]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[location]')->hiddenInput(['value' => $model->data_json['location']])->label(false) ?>
    <?= $form->field($model, 'data_json[send_type]')->hiddenInput(['general' => 'ทั่วไป', 'asset' => 'ครุภัณฑ์'], ['inline' => false, 'custom' => true])->label(false) ?>

    <div class="row">
        <div class="col-8">
            <?= $form->field($model, 'data_json[title]')->textInput(['placeholder' => 'ระบุอาการเสีย...'])->label('<i class="fa-solid fa-exclamation"></i> ระบุอาการเสีย/ความต้องการ') ?>
        </div>
        <div class="col-4">
            <div class="mb-3 highlight-addon field-helpdesk-data_json-location has-success">
                <label class="form-label has-star" for="helpdesk-data_json-location">หน่วยงานผู้แจ้ง</label>
                <input type="text" class="form-control" name="Helpdesk[data_json][location]" value="<?= $model->data_json['location'] ?>" disabled="true">
            </div>
        </div>
        <div class="col-8">
            <?= $form->field($model, 'data_json[urgency]')->radioList($model->listUrgency(), ['inline' => true, 'custom' => true])->label('ความเร่งด่วน') ?>
        </div>
        <div class="col-4">
            <?= $form
                ->field($model, 'data_json[location_other]', [
                    'hintType' => ActiveField::HINT_SPECIAL,
                    'hintSettings' => ['placement' => 'right', 'onLabelClick' => true, 'onLabelHover' => true]
                ])
                ->textInput(['placeholder' => 'ระบุสถานที่ิอื่นๆ...'])
                ->hint('ถ้าหากไม่ได้เกิดเหตุบริเวร หน่วยงานผู้แจ้ง ให้สารถระบุบถสานที่อื่ๆ ได้')
                ->label('สถานที่อื่นๆ') ?>
        </div>
        <div class="col-12">
            <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 5, 'placeholder' => 'ระบุรายละเอียดเพิ่มเติมของอาการเสีย...'])->label('เพิ่มเติม') ?>
        </div>
        <div class="col-6">
    <?= $form->field($model, 'data_json[phone]')->textInput()->label('เบอร์โทร') ?>

            <div class="border border-1 border-primary p-3 rounded">
                <?php if ($model->code): ?>
                <?php if ($model->repair_group == 1): ?>
                <div class="d-flex flex-column align-items-center justify-content-center text-bg-light p-5 rounded-2">
                    <div class="d-flex justify-content-between gap-5 mb-3">
                        <i class="fa-solid fa-right-left fs-2"></i> <i
                            class="fa-solid fa-screwdriver-wrench fs-2 text-primary"></i>
                    </div>
                    <div class="h5">ส่งงานซ่อมบำรุง</div>
                </div>
                <?php endif; ?>

                <?php if ($model->repair_group == 2): ?>
                <div class="d-flex flex-column align-items-center justify-content-center text-bg-light p-5 rounded-2">
                    <div class="d-flex justify-content-between gap-5 mb-3">
                        <i class="fa-solid fa-right-left fs-2"></i> <i
                            class="fa-solid fa-computer fs-2 text-primary"></i>

                    </div>
                    <div class="h5">ส่งศูนย์คอมพิวเตอร์</div>
                </div>
                <?php endif; ?>
                <?php if ($model->repair_group == 3): ?>
                <div class="d-flex flex-column align-items-center justify-content-center text-bg-light p-5 rounded-2">
                    <div class="d-flex justify-content-between gap-5 mb-3">
                        <i class="fa-solid fa-right-left fs-2"></i> <i
                            class="fa-solid fa-briefcase-medical fs-2 text-primary"></i>
                    </div>
                    <div class="h5">ส่งศูนย์เครื่องมือแพทย์</div>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <?= $form->field($model, 'repair_group')->radioList($model->listRepairGroup(), ['inline' => false, 'custom' => true])->label('<i class="fa-regular fa-paper-plane"></i> แจ้งไปยัง') ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-6">
            <a href="#" class="select-img">
                <?= Html::img($model->showImg(), ['class' => 'repair-photo object-fit-cover rounded m-auto border border-2 border-secondary-subtle', 'style' => 'max-width:100%;min-width: 320px;']) ?>
            </a>
            <input type="file" id="req_file" style="display: none;" />
            <a href="#" class="select-photo-req"></a>
        </div>
    </div>



    <?php // $model->upload('req_repair') ?>
    <!-- ## End ## -->
    <?php else: ?>
    <!-- ถ้าเป็นการแก้ไข -->
    <?= $form->field($model, 'data_json[create_name]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[urgency]')->hiddenInput($model->listUrgency(), ['inline' => true, 'custom' => true])->label(false) ?>
    <?= $form->field($model, 'data_json[location]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[send_type]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[accept_name]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'data_json[accept_time]')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'status')->hiddenInput()->label(false) ?>

    <div class="d-flex bg-primary justify-content-between bg-opacity-10 p-3 rounded mb-3">
        <div><i class="bi bi-check2-circle fs-5"></i> <span class="text-primary">การประเมินงานซ่อมและมอบหมายงาน</span>
        </div>
        <div>ขั้นตอนที่ <span class="badge rounded-pill bg-primary text-white">1</span> </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="border border-1 border-primary p-3 rounded">
                <?= $form->field($model, 'data_json[start_job]')->checkbox(['custom' => true, 'switch' => true])->label('ดำเนินการวันที่'); ?>
                <?php
                echo $form
                    ->field($model, 'data_json[start_job_date]')
                    ->widget(DateControl::classname(), [
                        'type' => DateControl::FORMAT_DATETIME,
                        'language' => 'th',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                            'pluginOptions' => [
                                'autoclose' => true
                            ]
                        ]
                    ])
                    ->label(false)
                ?>
            </div>
        </div>
        <div class="col-6">
            <div class="border border-1 border-primary p-3 rounded" style="height: 127px;">
                <?=
                $form
                    ->field($model, 'data_json[repair_type]')
                    ->radioList(['ซ่อมภายใน' => 'ซ่อมภายใน', 'ซ่อมภายนอก' => 'ซ่อมภายนอก'], ['inline' => true, 'custom' => true])
                    ->label(false)
                // ->label('ประเภทารซ่อม')
                ?>
                <?php
                echo $form
                    ->field($model, 'data_json[repair_type_date]')
                    ->widget(DateControl::classname(), [
                        'type' => DateControl::FORMAT_DATE,
                        'language' => 'th',
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'ระบุวันที่ส่งซ่อมภายนอก ...'],
                            'pluginOptions' => [
                                'autoclose' => true
                            ]
                        ]
                    ])
                    ->label(false)
                ?>
            </div>
        </div>
        ิ
        <div class="col-12">
            <div class="my-3">
                <?php
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
        <div><i class="bi bi-check2-circle fs-5"></i> <span class="text-primary">สรุปผลดำเนินงานและวิธีแก้ไข</span>
        </div>
        <div>ขั้นตอนที่ <span class="badge rounded-pill bg-primary text-white">2</span> </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="border border-1 border-primary p-3 rounded">
                <?= $form->field($model, 'data_json[end_job]')->checkbox(['custom' => true, 'switch' => true])->label('เสร็จวันที่'); ?>
                <?php
                echo $form
                    ->field($model, 'data_json[end_job_date]')
                    ->widget(DateControl::classname(), [
                        'type' => DateControl::FORMAT_DATETIME,
                        'widgetOptions' => [
                            'options' => ['placeholder' => 'ระบุวันที่แล้วเสร็จ ...'],
                            'pluginOptions' => [
                                'autoclose' => true,
                            ]
                        ]
                    ])
                    ->label(false)
                ?>
            </div>
            <?= $form->field($model, 'data_json[price]')->textInput(['placeholder' => 'ระบุมูลค่าการซ่อมถ้ามี', 'type' => 'number'])->label('มูลค่าการซ่อม') ?>
        </div>

        <div class="col-6">
            <?= $form->field($model, 'data_json[repair_note]')->textArea(['style' => 'height: 127px;', 'placeholder' => 'ระบุวิธีการแก้ไข/แนวทางแก้ไข/อื่นๆ...'])->label(false) ?>
        </div>
        <div class="d-flex bg-primary justify-content-between bg-opacity-10 p-3 rounded mb-3">
        <div><i class="bi bi-check2-circle fs-5"></i> <span class="text-primary">เอกสารต่างๆ เช่น ใบส่งซ่อมจากร้านซ่อม(กรณีแจ้งซ่อมภายนอก)</span>
        </div>
        <div>ขั้นตอนที่ <span class="badge rounded-pill bg-primary text-white">3</span> </div>
    </div>
        
        <?= $model->Upload('repair_success') ?>

        <?= $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[note]')->hiddenInput()->label(false) ?>
        <?= $form->field($model, 'data_json[accept_time]')->hiddenInput()->label(false) ?>
        <?php endif; ?>



        <div class="form-group mt-3 d-flex justify-content-center">
            <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary', 'id' => 'summit']) ?>
        </div>
        <?php ActiveForm::end(); ?>

    </div>

    <?php
        $urlDateNow = Url::to(['/helpdesk/default/datetime-now']);
        $js = <<<JS

            if(\$("#helpdesk-data_json-repair_type").val() == "ซ่อมภายนอก"){
                \$('#helpdesk-data_json-repair_type_date-disp').prop('disabled', false)
                    }else{
                        \$('#helpdesk-data_json-repair_type_date-disp').prop('disabled', true)

                }           

             \$("#helpdesk-data_json-start_job").change(function() {
                if(this.checked) {
                    \$.get("$urlDateNow",function (data, textStatus, jqXHR) 
                    {
                        \$('#helpdesk-data_json-start_job_date-disp').val(data).trigger('change')
                    },"json");
                    \$('#helpdesk-status').val(3)
                }else{
                    \$('#helpdesk-data_json-start_job_date').val('');
                    \$('#helpdesk-status').val(2)
                    \$('#helpdesk-data_json-end_job').prop('checked', false)
                    // \$('#helpdesk-data_json-end_job_date').val('');
                }
            });

            \$("#helpdesk-data_json-end_job").change(function() {
                if(this.checked) {
                    \$.get("$urlDateNow",function (data, textStatus, jqXHR) 
                    {
                        \$('#helpdesk-data_json-end_job_date-disp').val(data).trigger('change')
                    },"json");
                    \$('#helpdesk-status').val(4)

                    if(\$("#helpdesk-data_json-start_job").is(":checked")) {

                    }else{
            alert('ระุบดำเนินการก่อน');
            \$(this).prop('checked', false)
                    }
                }else{
                    \$('#helpdesk-data_json-end_job_date').val('');
                    // \$('#helpdesk-status').val(3).trigger('change')
                    \$('#helpdesk-status').val(3)
                }
            });

            \$("#helpdesk-data_json-repair_type").change(function() {
                value = \$("input[name='Helpdesk[data_json][repair_type]']:checked").val();
                if(value == "ซ่อมภายนอก") {
                        \$('#helpdesk-data_json-repair_type_date-disp').prop('disabled', false)
                    }else{
                        \$('#helpdesk-data_json-repair_type_date-disp').prop('disabled', true)

                }
            });


            \$('#form-repair').on('beforeSubmit', function (e) {
                var form = \$(this);
                \$.ajax({
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
                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                        }
                    }
                });
                return false;
            });


            \$(".select-img").click(function() {
                \$("input[id='req_file']").click();
            });

            \$("input[id='req_file']").on("change", function() {
                var fileInput = \$(this)[0];
                if (fileInput.files && fileInput.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                    \$(".repair-photo").attr("src", e.target.result);
                    };
                    reader.readAsDataURL(fileInput.files[0]);
                    uploadImg()
                }

            });

            function uploadImg()
            {
                formdata = new FormData();
                if(\$("input[id='req_file']").prop('files').length > 0)
                {
            \t\tfile = \$("input[id='req_file']").prop('files')[0];
                    formdata.append("repair", file);
                    formdata.append("id", 1);
                    formdata.append("ref", '$model->ref');
                    formdata.append("name", 'repair');

                    console.log(file);
            \t\t\$.ajax({
            \t\t\turl: '/filemanager/uploads/single',
            \t\t\ttype: "POST",
            \t\t\tdata: formdata,
            \t\t\tprocessData: false,
            \t\t\tcontentType: false,
            \t\t\tsuccess: function (res) {
                            success('แก้ไขภาพสำเร็จ')
                            console.log(res)
            \t\t\t}
            \t\t});
                }
            }

            // \$("button[id='summit']").on('click', function() {
            //     formdata = new FormData();
            //     if(\$("input[id='req_file']").prop('files').length > 0)
            //     {
            // \t\tfile = \$("input[id='req_file']").prop('files')[0];
            //         formdata.append("avatar", file);
            //         formdata.append("id", 1);
            //         formdata.append("ref", '$model->ref');
            //         formdata.append("name", 'req_repair');

            //         console.log(file);
            // \t\t\$.ajax({
            // \t\t\turl: '/filemanager/uploads/single',
            // \t\t\ttype: "POST",
            // \t\t\tdata: formdata,
            // \t\t\tprocessData: false,
            // \t\t\tcontentType: false,
            // \t\t\tsuccess: function (res) {
            //                 // success('แก้ไขภาพ')
            //                 console.log(res)
            // \t\t\t}
            // \t\t});
            //     }
            // })


            JS;
        $this->registerJS($js)
    ?>
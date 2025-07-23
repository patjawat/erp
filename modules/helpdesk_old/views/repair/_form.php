<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\date\DatePicker;
use kartik\select2\Select2;
// use kartik\widgets\DateTimePicker;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use kartik\datecontrol\DateControl;
use app\modules\hr\models\Employees;
use softark\duallistbox\DualListbox;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);
$ref = $model->ref;


$formatJs = <<< 'JS'
    var formatRepo = function (repo) {
        if (repo.loading) {
            return repo.avatar;
        }
        // console.log(repo);
        var markup =
    '<div class="row">' +
        '<div class="col-12">' +
            '<span>' + repo.avatar + '</span>' +
        '</div>' +
    '</div>';
        if (repo.description) {
          markup += '<p>' + repo.avatar + '</p>';
        }
        return '<div style="overflow:hidden;">' + markup + '</div>';
    };
    var formatRepoSelection = function (repo) {
        return repo.avatar || repo.avatar;
    }
    JS;

// Register the formatting script
$this->registerJs($formatJs, View::POS_HEAD);

// script to parse the results into the format expected by Select2
$resultsJs = <<< JS
    function (data, params) {
        params.page = params.page || 1;
        return {
            results: data.results,
            pagination: {
                more: (params.page * 30) < data.total_count
            }
        };
    }
    JS;

?>

<style>
.col-form-label {
    text-align: end;
}
    .select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #eaecee !important;
    color: #fff;
}
:not(.form-floating) > .input-lg.select2-container--krajee-bs5 .select2-selection--single, :not(.form-floating) > .input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 12px) !important;
}
.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #eaecee !important;
    color: #3F51B5;
}
</style>
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
            <?= $form->field($model, 'title')->textInput(['placeholder' => 'ระบุอาการเสีย...'])->label('<i class="fa-solid fa-exclamation"></i> ระบุอาการเสีย/ความต้องการ') ?>
            <di class="d-flex gap-3">

                <div class="mb-3 highlight-addon field-helpdesk-data_json-location has-success w-50">
                    <label class="form-label has-star" for="helpdesk-data_json-location">หน่วยงานผู้แจ้ง</label>
                    <input type="text" class="form-control" name="Helpdesk[data_json][location]"
                        value="<?= $model->data_json['location'] ?>" disabled="true">
                </div>
                <div class="w-50">
                    <?= $form
                ->field($model, 'data_json[location_other]', [
                    'hintType' => ActiveField::HINT_SPECIAL,
                    'hintSettings' => ['placement' => 'right', 'onLabelClick' => true, 'onLabelHover' => true]
                    ])
                    ->textInput(['placeholder' => 'ระบุสถานที่ิอื่นๆ...'])
                    ->hint('ถ้าหากไม่ได้เกิดเหตุบริเวร หน่วยงานผู้แจ้ง ให้สารถระบุบถสานที่อื่ๆ ได้')
                    ->label('สถานที่อื่นๆ') ?>
            </di>
        </div>

    </div>
    <div class="col-4">
        <div>

            <a href="#" class="select-img">
                <?= Html::img($model->showImg(), ['class' => 'repair-photo object-fit-cover rounded m-auto border border-2 border-secondary-subtle', 'style' => 'max-width:100%;min-width: 230px;']) ?>
            </a>
            <input type="file" id="my_file" style="display: none;" />
            <a href="#" class="select-photo-req"></a>
        </div>


      

    </div>
    </div>
    <div class="row">
    <div class="col-6">
        <?= $form->field($model, 'data_json[urgency]')->radioList($model->listUrgency(), ['inline' => true, 'custom' => true])->label('ความเร่งด่วน') ?>
    </div>
    <div class="col-6">
    <?= $form->field($model, 'data_json[phone]')->textInput()->label('เบอร์โทร') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'data_json[note]')->textArea(['rows' => 5, 'placeholder' => 'ระบุรายละเอียดเพิ่มเติมของอาการเสีย...'])->label('เพิ่มเติม') ?>
    </div>
    <div class="col-6">
    
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
                    <i class="fa-solid fa-right-left fs-2"></i> <i class="fa-solid fa-computer fs-2 text-primary"></i>

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


    <?php
try {
    //code...
    $initEmployee =  Employees::find()->where(['id' => $model->data_json['technician_req']])->one()->getAvatar(false);
} catch (\Throwable $th) {
    $initEmployee = '';
}
        echo $form->field($model, 'data_json[technician_req]')->widget(Select2::classname(), [
            'initValueText' => $initEmployee,
            'options' => ['placeholder' => 'เลือก ...'],
            'size' => Select2::LARGE,
            'pluginEvents' => [
                'select2:unselect' => 'function() {
                $("#order-data_json-board_fullname").val("")

         }',
                'select2:select' => 'function() {
                var fullname = $(this).select2("data")[0].fullname;
               
         }',
            ],
            'pluginOptions' => [
                'dropdownParent' => '#main-modal',
                'allowClear' => true,
                'minimumInputLength' => 1,
                'ajax' => [
                    'url' => Url::to(['/helpdesk/repair/technician-list']),
                    'dataType' => 'json',
                    'delay' => 250,
                   'data' => new JsExpression('function(params) { 
                            return {
                                q: params.term,
                                repair_group: $("input[name=\'Helpdesk[repair_group]\']:checked").val(), 
                                page: params.page
                            }; 
                        }'),
                    'processResults' => new JsExpression($resultsJs),
                    'cache' => true,
                ],
                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                'templateResult' => new JsExpression('formatRepo'),
            ],
        ])->label('เลือกช่างที่ต้องการ')
    ?>

            
    <div id="show_technician"></div>
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


            <?=$form->field($model, 'data_json[start_job_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label(false);
                ?>
            <?php
                // echo $form
                //     ->field($model, 'data_json[start_job_date]')
                //     ->widget(DateControl::classname(), [
                //         'type' => DateControl::FORMAT_DATETIME,
                //         'language' => 'th',
                //         'widgetOptions' => [
                //             'options' => ['placeholder' => 'ระบุวันที่ดำเนินการ ...'],
                //             'pluginOptions' => [
                //                 'autoclose' => true
                //             ]
                //         ]
                //     ])
                //     ->label(false)
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
            <?=$form->field($model, 'data_json[repair_type_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label(false);
                ?>
            <?php
                // echo $form
                //     ->field($model, 'data_json[repair_type_date]')
                //     ->widget(DateControl::classname(), [
                //         'type' => DateControl::FORMAT_DATE,
                //         'language' => 'th',
                //         'widgetOptions' => [
                //             'options' => ['placeholder' => 'ระบุวันที่ส่งซ่อมภายนอก ...'],
                //             'pluginOptions' => [
                //                 'autoclose' => true
                //             ]
                //         ]
                //     ])
                //     ->label(false)
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
            <?=$form->field($model, 'data_json[end_job_date]')->widget(Datetimepicker::className(),[
                    'options' => [
                        'timepicker' => false,
                        'datepicker' => true,
                        'mask' => '99/99/9999',
                        'lang' => 'th',
                        'yearOffset' => 543,
                        'format' => 'd/m/Y', 
                    ],
                    ])->label(false);
                ?>
            <?php
                // echo $form
                    // ->field($model, 'data_json[end_job_date]')
                    // ->widget(DateControl::classname(), [
                    //     'type' => DateControl::FORMAT_DATETIME,
                    //     'widgetOptions' => [
                    //         'options' => ['placeholder' => 'ระบุวันที่แล้วเสร็จ ...'],
                    //         'pluginOptions' => [
                    //             'autoclose' => true,
                    //         ]
                    //     ]
                    // ])
                    // ->label(false)
                ?>
        </div>
        <?= $form->field($model, 'data_json[price]')->textInput(['placeholder' => 'ระบุมูลค่าการซ่อมถ้ามี', 'type' => 'number'])->label('มูลค่าการซ่อม') ?>
    </div>

    <div class="col-6">
        <?= $form->field($model, 'data_json[repair_note]')->textArea(['style' => 'height: 127px;', 'placeholder' => 'ระบุวิธีการแก้ไข/แนวทางแก้ไข/อื่นๆ...'])->label(false) ?>
    </div>
    <div class="d-flex bg-primary justify-content-between bg-opacity-10 p-3 rounded mb-3">
        <div><i class="bi bi-check2-circle fs-5"></i> <span class="text-primary">เอกสารต่างๆ เช่น
                ใบส่งซ่อมจากร้านซ่อม(กรณีแจ้งซ่อมภายนอก)</span>
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
$urlTechnicianList = Url::to(['/helpdesk/repair/technician-list']);
$ref = isset($ref) ? Html::encode($ref) : '';
$js = <<< JS

    $(document).ready(function () {
        $('input[name="Helpdesk[repair_group]"]').on("change", function () {
            console.log($(this).val());
            $.ajax({
                type: "get",
                url: "$urlTechnicianList",
                data:{
                    repair_group:$(this).val()
                },
                dataType: "json",
                success: function (res) {
                    $('#show_technician').html(res.content)
                }
            });
        });
    });



    $('#form-repair').on('beforeSubmit', function (e) {
                                var form = $(this);

                                Swal.fire({
                                    title: "ยืนยัน?",
                                    text: $('#helpdesk-data_json-title').val() + " !",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonColor: "#3085d6",
                                    cancelButtonColor: "#d33",
                                    confirmButtonText: "ใช่!",
                                    cancelButtonText: "ยกเลิก",
                                        }).then((result) => {
                                        if (result.isConfirmed) {
                                            $.ajax({
                                                    url: form.attr('action'),
                                                    type: 'post',
                                                    data: form.serialize(),
                                                    dataType: 'json',
                                                    beforeSend: async function () {
                                                        await uploadFile();
                                                    },
                                                    success: async function (response) {
                                                        form.yiiActiveForm('updateMessages', response, true);
                                                        if(response.status == 'success') {
                                                            closeModal()
                                                            success()
                                                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                                        }
                                                    }
                                                });
                                                return false;
                                        } else if (result.isDenied) {
                                            Swal.fire("Changes are not saved", "", "info");
                                        }
                                        });

                                        return false;
});
                            
// $('#form-repair').on('beforeSubmit', function (e) {
//                 var form = $(this);
//                 \$.ajax({
//                     url: form.attr('action'),
//                     type: 'post',
//                     data: form.serialize(),
//                     dataType: 'json',
//                     success: async function (response) {
//                         form.yiiActiveForm('updateMessages', response, true);
//                         if(response.status == 'success') {
//                             closeModal()
//                             success()
//                             try {
//                                 loadRepairHostory()
//                             } catch (error) {
                                
//                             }
//                             await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
//                         }
//                     }
//                 });
//                 return false;
//             });


        var thaiYear = function (ct) {
            var leap=3;  
            var dayWeek=["พฤ.", "ศ.", "ส.", "อา.","จ.", "อ.", "พ."];  
            if(ct){  
                var yearL=new Date(ct).getFullYear()-543;  
                leap=(((yearL % 4 == 0) && (yearL % 100 != 0)) || (yearL % 400 == 0))?2:3;  
                if(leap==2){  
                    dayWeek=["ศ.", "ส.", "อา.", "จ.","อ.", "พ.", "พฤ."];  
                }  
            }              
            this.setOptions({  
                i18n:{ th:{dayOfWeek:dayWeek}},dayOfWeekStart:leap,  
            })                
        };    
        
    
        $("#helpdesk-data_json-start_job_date").datetimepicker({
            timepicker:false,
            format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
            lang:'th',  // แสดงภาษาไทย
            onChangeMonth:thaiYear,          
            onShow:thaiYear,                  
            yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
            closeOnDateSelect:true,
        }); 

        


        $("#helpdesk-data_json-repair_type_date").datetimepicker({
            timepicker:false,
            format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
            lang:'th',  // แสดงภาษาไทย
            onChangeMonth:thaiYear,          
            onShow:thaiYear,                  
            yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
            closeOnDateSelect:true,
        }); 

        $("#helpdesk-data_json-end_job_date").datetimepicker({
            timepicker:false,
            format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000            
            lang:'th',  // แสดงภาษาไทย
            onChangeMonth:thaiYear,          
            onShow:thaiYear,                  
            yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
            closeOnDateSelect:true,
        }); 



            if($("#helpdesk-data_json-repair_type").val() == "ซ่อมภายนอก"){
                $('#helpdesk-data_json-repair_type_date-disp').prop('disabled', false)
                    }else{
                        $('#helpdesk-data_json-repair_type_date-disp').prop('disabled', true)

                }           

             $("#helpdesk-data_json-start_job").change(function() {
                if(this.checked) {
                    \$.get("$urlDateNow",function (data, textStatus, jqXHR) 
                    {
                        $('#helpdesk-data_json-start_job_date-disp').val(data).trigger('change')
                    },"json");
                    $('#helpdesk-status').val(3)
                }else{
                    $('#helpdesk-data_json-start_job_date').val('');
                    $('#helpdesk-status').val(2)
                    $('#helpdesk-data_json-end_job').prop('checked', false)
                    // $('#helpdesk-data_json-end_job_date').val('');
                }
            });

            $("#helpdesk-data_json-ด").change(function() {
                if(this.checked) {
                    \$.get("$urlDateNow",function (data, textStatus, jqXHR) 
                    {
                        $('#helpdesk-data_json-end_job_date-disp').val(data).trigger('change')
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

            $("#helpdesk-data_json-repair_type").change(function() {
                value = $("input[name='Helpdesk[data_json][repair_type]']:checked").val();
                if(value == "ซ่อมภายนอก") {
                        $('#helpdesk-data_json-repair_type_date-disp').prop('disabled', false)
                    }else{
                        $('#helpdesk-data_json-repair_type_date-disp').prop('disabled', true)

                }
            });


           

            // $(".select-img").click(function() {
            //     $("input[id='my_file']").click();
            // });

            // $("input[id='my_file']").on("change", function() {
            //     var fileInput = $(this)[0];
            //     if (fileInput.files && fileInput.files[0]) {
            //         var reader = new FileReader();
            //         reader.onload = function(e) {
            //         $(".repair-photo").attr("src", e.target.result);
            //         };
            //         reader.readAsDataURL(fileInput.files[0]);
            //         uploadImg()
            //     }

            // });


            //###########

            $("#my_file").on("change", function() {
                var fileInput = $(this)[0];
                if (fileInput.files && fileInput.files[0]) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $(".repair-photo").attr("src", e.target.result);
                    };
                    reader.readAsDataURL(fileInput.files[0]);
                }
            });
            //###########
            
            $(".select-img").click(function() {
                $("#my_file").click();
                console.log('click');
                
            });

            // $("input[id='my_file']").on("change", function() {
            //     var fileInput = $(this)[0];
            //     if (fileInput.files && fileInput.files[0]) {
            //         var reader = new FileReader();
            //         reader.onload = function(e) {
            //         $(".repair-photo").attr("src", e.target.result);
            //         };
            //         reader.readAsDataURL(fileInput.files[0]);
            //         uploadImg()
            //     }

            // });
            


 function uploadFile(){
    formdata = new FormData();
    var checkFile = $("input[id='my_file']").prop('files').length;
    console.log(checkFile);
    
    
    if($("input[id='my_file']").prop('files').length > 0)
    {
		file = $("input[id='my_file']").prop('files')[0];
        formdata.append("repair", file);
        formdata.append("id", 1);
        formdata.append("ref", '$ref');
        formdata.append("name", 'repair');
		$.ajax({
			url: '/filemanager/uploads/single',
			type: "POST",
			data: formdata,
			processData: false,
			contentType: false,
			success: function (res) {
                return true;
			}
		});
    }
}



JS;
$this->registerJS($js)
?>
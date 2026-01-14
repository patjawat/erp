<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use app\components\SiteHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;

$director = SiteHelper::viewDirector();

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
/** @var yii\widgets\ActiveForm $form */
$formatJs = <<<'JS'
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
$resultsJs = <<<JS
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
    :not(.form-floating)>.input-lg.select2-container--krajee-bs5 .select2-selection--single,
    :not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
        height: calc(2.875rem + 2px);
        padding: 4px;
        font-size: 1.0rem;
        line-height: 1.5;
        border-radius: .3rem;
    }

    .select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
        background-color: #e5e5e5;
        color: #000;
    }
</style>

<?php $form = ActiveForm::begin([
    'id' => 'form-elave',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/hr/leave/create-validator', 'id' => $model->id],
]); ?>

<?php if (!$model->isNewRecord): ?>
    <?= $form->field($model, 'id')->hiddenInput()->label(false); ?>
<?php endif ?>
<div class="row d-flex justify-content-center">
    <div class="col-lg-12 col-md-12">
        <!-- Row -->
        <div class="row">
            <div class="col-6">

                <div class="d-flex justify-content-between gap-3">
                    <div class="w-50">
                        <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่']); ?>
                        <?= $form->field($model, 'date_end')->textInput(['placeholder' => 'เลือกวันที่']); ?>
                    </div>
                    <div class="w-50">
                        <?= $form->field($model, 'date_start_type')->widget(Select2::classname(), [
                            'data' => [
                                '0' => 'เต็มวัน',
                                '0.5' => 'ครึงวัน',
                            ],
                            // 'options' => ['placeholder' => 'เลือกประเภทการลา ...'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'dropdownParent' => '#main-modal',
                                'width' => '100%',
                            ],
                            'pluginEvents' => [
                                'select2:unselect' => 'function() {
                                    calDays();
                                    }',
                                'select2:select' => 'function() {
                                        calDays();
                                        }',
                            ],
                        ])->label('ประเภท');
                        ?>

                        <?= $form->field($model, 'date_end_type')->widget(Select2::classname(), [
                            'data' => [
                                '0' => 'เต็มวัน',
                                '0.5' => 'ครึงวัน',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'dropdownParent' => '#main-modal',
                                'width' => '100%',
                            ],
                            'pluginEvents' => [
                                'select2:unselect' => 'function() {
                                    calDays();
                                    }',
                                'select2:select' => 'function() {
                                        calDays();
                                    }',
                            ],
                        ])->label('ประเภท');
                        ?>


                    </div>
                </div>
                <?php
                echo $form->field($model, 'leave_type_id')->widget(Select2::classname(), [
                    'data' => $model->listLeavetype(),
                    'options' => ['placeholder' => 'เลือกประเภทการลา ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                    'pluginEvents' => [
                        'select2:unselect' => 'function() {
                                    calDays();
                                    }',
                        'select2:select' => 'function() {
                                        calDays();
                                    }',
                    ],
                ])->label('ประเภท');
                ?>
            </div>
            <div class="col-6">
                <?php
                echo $form->field($model, 'data_json[work_shift]')->widget(Select2::classname(), [
                    'data' => ['normal' => 'ปกติ', 'shift' => 'เวร 8 ชั่วโมง'],
                    'options' => [
                        'id' => 'work_shift',
                        'placeholder' => '--- ประเภทของเวร ---',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true
                    ],
                    // เพิ่มการดักจับ Event ตรงนี้
                    'pluginEvents' => [
                        "change" => "function() { calDays(); }",
                    ]
                ])->label('(หากเป็นเวร 8 จะไม่นับวันหยุดและเสาร์-อาทิตย์) *');
                ?>
                <div class="d-flex justify-content-between gap-3">
                    <div class="w-50">
                        <?php echo $form->field($model, 'data_json[sat_sun_days]')->textInput([
                            'id' => 'satsunDays'
                        ])->label('วันเสาร์-อาทิตย์') ?>
                    </div>

                    <div class="w-50">
                        <?php echo $form->field($model, 'data_json[holidays]')->textInput(['id' => 'holiday'])->label('วันหยุดนักขัตฤกษ์') ?>
                    </div>
                </div>
                <?php echo $form->field($model, 'total_days')->textInput(['id' => 'summaryDay'])->label('สรุปวันลา') ?>
            </div>
        </div>

        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home"
                    type="button" role="tab" aria-controls="pills-home"
                    aria-selected="true">รายละเอียดเพิ่มเติม</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile"
                    type="button" role="tab" aria-controls="pills-profile"
                    aria-selected="false">เอกสารแนบ/ใบรับรองแพทย์</button>
            </li>

        </ul>
        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab"
                tabindex="0">
                <!-- Start row -->
                <div class="row">
                    <div class="col-6">
                        <div class="d-flex gap-3">
                            <div class="w-50">

                                <?php echo $form->field($model, 'data_json[phone]')->textInput()->label('เบอร์โทรติดต่อ') ?>
                            </div>
                            <div class="w-50">
                                <?php
                                echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
                                    'data' => [
                                        'ภายในจังหวัด' => 'ภายในจังหวัด',
                                        'ต่างจังหวัด' => 'ต่างจังหวัด',
                                        'ต่างประเทศ' => 'ต่างประเทศ',
                                    ],
                                    // 'options' => ['placeholder' => 'เลือกสถานที่ไป ...'],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'dropdownParent' => '#main-modal',
                                        'width' => '100%',
                                    ],
                                ])->label('สถานที่ไป');
                                ?>
                            </div>
                        </div>
                        <?php echo $form->field($model, 'data_json[address]')->textArea(['style' => 'height:117px;'])->label('ระหว่างลาติดต่อ') ?>
                    </div>
                    <div class="col-6">

                        <?php
                        try {
                            $initEmployee = Employees::find()->where(['id' => $model->data_json['leave_work_send_id']])->one()->getAvatar(false);
                        } catch (\Throwable $th) {
                            $initEmployee = '';
                        }
                        echo $form->field($model, 'data_json[leave_work_send_id]')->widget(Select2::classname(), [
                            'initValueText' => $initEmployee,
                            'options' => ['placeholder' => 'เลือกรายการ...'],
                            'size' => Select2::LARGE,
                            'pluginEvents' => [
                                'select2:unselect' => 'function() {
                                    $("#leave-data_json-leave_work_send").val("")
                                    }',
                                'select2:select' => 'function() {
                                            var fullname = $(this).select2("data")[0].fullname;
                                            $("#leave-data_json-leave_work_send").val(fullname)
                                    }',
                            ],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'dropdownParent' => '#main-modal',
                                'minimumInputLength' => 1,
                                'ajax' => [
                                    'url' => Url::to(['/depdrop/employee-by-id']),
                                    'dataType' => 'json',
                                    'delay' => 250,
                                    'data' => new JsExpression('function(params) { return {q:params.term, page: params.page}; }'),
                                    'processResults' => new JsExpression($resultsJs),
                                    'cache' => true,
                                ],
                                'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
                                'templateSelection' => new JsExpression('function (item) { return item.text; }'),
                                'templateResult' => new JsExpression('formatRepo'),
                            ],
                        ])->label('มอบหมายงานให้')
                        ?>
                        <?php echo $this->render('@app/modules/hr/views/leave/approve', ['form' => $form, 'model' => $model]) ?>


                    </div>
                </div>
                <?php echo $form->field($model, 'data_json[reason]')->textArea(['style' => 'height:130px;'])->label('เหตุผล/เนื่องจาก') ?>
                <!-- End Row -->
            </div>
            <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab"
                tabindex="0">
                <?php echo $model->Upload('leave_file') ?>
            </div>

        </div>

    </div>
</div>


<?php // echo $this->render('summary', ['model' => $model]) 
?>

<?php echo $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[leave_work_send]')->hiddenInput()->label(false) ?>




<?php echo $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[director]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[director_fullname]')->hiddenInput()->label(false) ?>


<div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>


<?php ActiveForm::end(); ?>
<?php
$calDaysUrl = Url::to(['/hr/leave/cal-days', 'emp_id' => $model->emp_id]);
$js = <<<JS

        calDays()
        // ตรวจสอบเบื้องต้นตอนโหลดหน้าเว็บ
        thaiDatepicker('#leave-date_start,#leave-date_end')

        function toggleDateEndType() {
            let dateStart = \$('#leave-date_start').val();
            let dateEnd = \$('#leave-date_end').val();

            if (dateStart && dateEnd && dateStart === dateEnd) {
                \$('#leave-data_json-date_end_type').prop('disabled', true);
            } else {
                \$('#leave-data_json-date_end_type').prop('disabled', false);
            }
        }

        // เรียกใช้เมื่อค่าเปลี่ยนแปลง
        \$('#leave-date_start, #leave-date_end').on('change', function () {
            toggleDateEndType();
        });

        // เรียกใช้เมื่อหน้าโหลด
        toggleDateEndType();
        

      \$('#form-elave').on('beforeSubmit', function (e) {
        var form = \$(this);
        
        
        // let totalDays = parseInt(\$('#summaryDay').val(), 10);
        let totalDays = parseFloat($('#summaryDay').val());
        console.log(totalDays);

        if (isNaN(totalDays) || totalDays <= 0) {
            Swal.fire({
                icon: 'error',
                title: 'ข้อผิดพลาด',
                text: 'วันลาต้องมากกว่า 0!',
                confirmButtonText: 'ตกลง'
            });
            return false;
        }
        
        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกขออนุมัติการลา!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        cancelButtonText: "ยกเลิก!",
        confirmButtonText: "ใช่, ยืนยัน!"
        }).then((result) => {
        if (result.isConfirmed) {
            beforLoadModal()
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    // form.yiiActiveForm('updateMessages', response, true);
                    if(response.status == 'success') {
                        closeModal()
                        location.reload();
                        // success()
                        // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });

        }
        });
        return false;
    });

        \$('#leave-date_start').on('change', function() {
            var selectedDate = \$(this).val();
            calDays(selectedDate);
        });

        \$('#leave-date_end').on('change', function() {
            var selectedDate = \$(this).val();
            calDays(selectedDate);
        });

        \$("#leave-data_json-auto").change(function() {
            //ไม่รวมวันหยุด Auto
            if(this.checked) {
                calDays()
            }else{
                calDays()
            }
        });
        

    function calDays()
    {
            \$.ajax({
                type: "get",
                url: "$calDaysUrl",
                data:{
                    date_start:\$('#leave-date_start').val(),
                    date_end:\$('#leave-date_end').val(),
                    date_start_type:\$('#leave-date_start_type').val(),
                    date_end_type:\$('#leave-date_end_type').val(),
                    on_holidays:\$('#leave-on_holidays').val(),
                    leave_type_id:\$('#leave-leave_type_id').val()
                    
                },
                dataType: "json",
                success: function (res) {
                    console.log(res.shift);
                    if(res.status == 'error'){
                        Swal.fire({
                            icon: 'error',
                            title: 'ข้อผิดพลาด',
                            text: res.message,
                            confirmButtonText: 'ตกลง'
                        });

                        \$('#satsunDays').val(0)
                        
    
                        
                        \$('#holiday').val(0)
                       
                       \$('#summaryDay').val(0)
                       \$('#summaryDay').val(0)
                       return false;

                    }

                    
                         var workShift = $('#work_shift');
                          var holiday = $('#holiday');

                        var satSunDays = $('#satsunDays'); // ปกติ Yii2 จะใช้ class 'field-[ID]' คลุมฟิลด์ไว้
                                    
                        var totalDays = $('#summaryDay');
                    
                    // ตรวจสอบค่า (ระวังคำสะกด: mormal -> normal หรือตามที่คุณตั้งใน data)
                    if (workShift.val() == 'normal') {
                        // ถ้าเป็น 'ปกติ' ให้ซ่อน
                         holiday.prop('readOnly', true)
                        totalDays.prop('readOnly', true)
                        satSunDays.prop('readOnly', true)
                    } else if (workShift.val() == 'shift') {
                        // ถ้าเป็น 'เวร 8 ชั่วโมง' ให้แสดง
                        holiday.prop('readOnly', false)
                        totalDays.prop('readOnly', false)
                        satSunDays.prop('readOnly', false)
                    }
                    
                    \$('#satsunDays').val(res.satsunDays)
                    satSunDays.val(res.satsunDays)
                    satSunDays.val(res.satsunDays)
                    
                    \$('#holiday').val(res.holiday)
                   
                   \$('#summaryDay').val(res.total)

                   if(res.isDayOff >= 1){
                    \$('.day_normal').hide()
                    \$('.day_off').show()
                }
                
                if(res.isDayOff == 0){
                    \$('.day_off').hide()
                    \$('.day_normal').show()
                   }
                    
                }
            });
        }


    \$("input[name='Leave[data_json][leave_type]']").on('change', function() {
            // ดึงค่าที่ถูกเลือก
            var selectedValue = \$("input[name='Leave[data_json][leave_type]']:checked").val();
            console.log(selectedValue); // แสดงค่าใน console
            if(selectedValue == "เต็มวัน"){
                \$('#leave-leave_time_type').val(1);
            }else{
                \$('#leave-leave_time_type').val(0.5);
            }
        });

    $("body").on("change", "#work_shift", function () {
        var value = $(this).val();
        var id = $('#leave-emp_id').val();
        if(value){   
            $.ajax({
                url: "/hr/work-shift/update-shift", // <-- route ไปยัง action
                type: "POST",
                data: {
                    id: id,
                    work_shift: value,
                },
                success: async function (response) {
                    await calDays();
                },
                error: function () {
                    error("เกิดข้อผิดพลาดในการบันทึก");
                }
            });
        }
    });

    JS;
$this->registerJS($js, View::POS_END);

?>
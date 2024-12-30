<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\web\JsExpression;
use app\models\Categorise;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;
use kartik\sortable\Sortable;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
/** @var yii\widgets\ActiveForm $form */
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
    color: #fff;
}
</style>
<!-- <div class="row d-flex justify-content-center">
<div class="col-8">
<div class="card">
    <div class="card-body"> -->

<?php $form = ActiveForm::begin([
    'id' => 'form-elave',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/hr/leave/create-validator']
]); ?>


<div class="row d-flex justify-content-center">
    <div class="col-lg-12 col-md-12">
        <!-- Row -->
        <div class="row">
            <div class="col-6">
                <?php
                echo $form->field($model, 'leave_type_id')->widget(Select2::classname(), [
                    'data' => $model->listLeavetype(),
                    'options' => ['placeholder' => 'เลือกประเภทการลา ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('ประเภท');
                ?>
                <?php echo $form->field($model, 'data_json[reason]')->textArea(['style' => 'height:118px;'])->label('เหตุผล/เนื่องจาก') ?>

                <?php echo $form->field($model, 'data_json[phone]')->textInput()->label('เบอร์โทรติดต่อ') ?>

                <?php echo $form->field($model, 'data_json[address]')->textArea(['style' => 'height:220px;'])->label('ระหว่างลาติดต่อ') ?>

            </div>

            <div class="col-6">
                <div class="d-flex justify-content-between gap-3">
                    <div>
                        <?php echo $form->field($model, 'date_start')->widget(Datetimepicker::className(), [
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y',
                            ],
                        ])->label('ตั้งแต่วันที่') ?>
                        <?php echo $form->field($model, 'date_end')->widget(Datetimepicker::className(), [
                            'options' => [
                                'timepicker' => false,
                                'datepicker' => true,
                                'mask' => '99/99/9999',
                                'lang' => 'th',
                                'yearOffset' => 543,
                                'format' => 'd/m/Y',
                            ],
                        ]) ?>
                    </div>
                    <div>
                        <?php
                    echo $form->field($model, 'data_json[date_start_type]')->widget(Select2::classname(), [
                        'data' => [
                            '0' => 'เต็มวัน',
                            '0.5' => 'ครึงวัน',
                        ],
                        // 'options' => ['placeholder' => 'เลือกประเภทการลา ...'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                            'width' => '150px',
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

                        <?php
                        echo $form->field($model, 'data_json[date_end_type]')->widget(Select2::classname(), [
                            'data' => [
                                '0' => 'เต็มวัน',
                                '0.5' => 'ครึงวัน',
                            ],
                            // 'options' => ['placeholder' => 'เลือกประเภทการลา ...'],
                            'pluginOptions' => [
                                'allowClear' => true,
                                'dropdownParent' => '#main-modal',
                                'width' => '150px',
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

                <div
                    class="d-flex justify-content-between  align-middle align-items-center bg-primary bg-opacity-10  pt-3 px-3 rounded mb-3">

                    <h6>เป็นเวลา <span class="cal-days text-black bg-danger-subtle badge rounded-pill fw-ligh fs-13">
                            <?php echo $model->total_days ?></span> วัน</h6>
                    <?php echo $form->field($model, 'on_holidays', [
                            'options' => ['class' => 'mb-2']
                        ])->checkbox(['custom' => true, 'switch' => true, 'checked' => ($model->on_holidays == 1 ? true : false)])->label('ไม่รวมวันหยุด'); ?>


                </div>
                <?php
                echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
                    'data' => [
                        'ภายในจังหวัด' => 'ภายในจังหวัด',
                        'ต่างจังหวัด' => 'ต่างจังหวัด',
                        'ต่างประเทศ' => 'ต่างประเทศ',
                    ],
                    'options' => ['placeholder' => 'เลือกสถานที่ไป ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('สถานที่ไป');
                ?>
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
                <?php echo $this->render('approve', ['form' => $form, 'model' => $model]) ?>




            </div>


        </div>
    </div>
</div>
<?php // echo $this->render('summary', ['model' => $model]) ?>

<?php echo $form->field($model, 'ref')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[leave_work_send]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'total_days')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[director]')->hiddenInput()->label(false) ?>
<?php echo $form->field($model, 'data_json[director_fullname]')->hiddenInput()->label(false) ?>


<div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
</div>
</div>
</div>



<?php ActiveForm::end(); ?>

<!-- </div>
</div>
</div>
</div> -->

<?php
$calDaysUrl = Url::to(['/hr/leave/cal-days']);
$js = <<< JS

      \$('#form-elave').on('beforeSubmit', function (e) {
        var form = \$(this);
        console.log('Submit');

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
            
            \$.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: async function (response) {
                    form.yiiActiveForm('updateMessages', response, true);
                    if(response.status == 'success') {
                        closeModal()
                        // success()
                        await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
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

        \$("#leave-on_holidays").change(function() {
            //ไม่รวมวันหยุด Auto
            if(this.checked) {
                calDays()
            }else{
                calDays()
            }
        });



        function formatDate(date) {
        if (!date) {
            return 'n/a'; // Handle case where date might be null or undefined
        }
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const day = String(date.getDate()).padStart(2, '0');
        return year+'-'+month+'-'+day; // Format: YYYY-MM-DD
    }

    // แปลงเป็น พ.ศ.
    function formatDateThai(date) {
        if (!date) {
            return 'n/a'; // Handle case where date might be null or undefined
        }
        const year = date.getFullYear() + 543;
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const day = String(date.getDate()).padStart(2, '0');
        return (day-1)+'/'+month+'/'+year; // Format: YYYY-MM-DD
    }


    function dateToForm(dateStart,dateEnd)
    {
    console.log(dateStart);

    }


      function calDays()
        {
            console.log(\$('#leave-date_end').val())
            \$.ajax({
                type: "get",
                url: "$calDaysUrl",
                data:{
                    date_start:\$('#leave-date_start').val(),
                    date_end:\$('#leave-date_end').val(),
                    date_start_type:\$('#leave-data_json-date_start_type').val(),
                    date_end_type:\$('#leave-data_json-date_end_type').val(),
                    on_holidays:\$('#leave-on_holidays').is(':checked') ? 1 : 0,
                },
                dataType: "json",
                success: function (res) {
                    console.log(\$('#leave-data_json-date_start_type').val());
                   \$('.cal-days').html(res.total)
                   \$('#leave-total_days').val(res.total)
                    
                    
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

        \$("#leave-date_start").datetimepicker({
            timepicker:false,
            format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
            lang:'th',  // แสดงภาษาไทย
            onChangeMonth:thaiYear,
            onShow:thaiYear,
            yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
            closeOnDateSelect:true,
        });

        \$("#leave-date_end").datetimepicker({
            timepicker:false,
            format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
            lang:'th',  // แสดงภาษาไทย
            onChangeMonth:thaiYear,
            onShow:thaiYear,
            yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
            closeOnDateSelect:true,
        });

    JS;
$this->registerJS($js, View::POS_END);

?>
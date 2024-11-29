<?php

use app\models\Categorise;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\web\JsExpression;
use yii\widgets\Pjax;
use app\modules\hr\models\Employees;
use yii\helpers\ArrayHelper;
use kartik\sortable\Sortable;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
/** @var yii\widgets\ActiveForm $form */
$this->registerJsFile('https://code.jquery.com/ui/1.14.0/jquery-ui.js', ['depends' => [\yii\web\JqueryAsset::className()]]);


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

:not(.form-floating)>.input-lg.select2-container--krajee-bs5 .select2-selection--single,
:not(.form-floating)>.input-group-lg .select2-container--krajee-bs5 .select2-selection--single {
    height: calc(2.875rem + 12px) !important;
}

.select2-container--krajee-bs5 .select2-results__option--highlighted[aria-selected] {
    background-color: #eaecee !important;
    color: #3F51B5;
}
</style>

<?php $form = ActiveForm::begin([
    'id' => 'form',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/lm/leave/create-validator']
]); ?>
<div class="row d-flex justify-content-center">
    <div class="col-lg-3 col-md-5">

        <div class="card">
            <div class="card-body">

                <div id='external-events'>
                    <h4>เลือกเหตุหารณ์</h4>
                    <div class='fc-event p-3'>วัน OFF</div>

                    <p>
                        <input type='checkbox' id='drop-remove' />
                        <label for='drop-remove'>remove after drop</label>
                    </p>
                </div>

                <div style='clear:both'></div>

            </div>
        </div>

    </div>
    <div class="col-lg-6 col-md-7">
        <div class="card text-start">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h2><i class="fa-solid fa-file-pen"></i> บันทึกขอ<?= $model->leaveType->title ?></h2>
                    <h6><span class="cal-days">0</span> / วัน</h6>
                </div>

                <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'leave_type_id')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'days_off')->hiddenInput()->label(false) ?>


                <div class="row">
                    <div class="col-6">
                        <div class="d-flex justify-content=between gap-2">
                            <div class="w-50">
                                <?= $form->field($model, 'date_start')->widget(Datetimepicker::className(), [
                                    'options' => [
                                        'timepicker' => false,
                                        'datepicker' => true,
                                        'mask' => '99/99/9999',
                                        'lang' => 'th',
                                        'yearOffset' => 543,
                                        'format' => 'd/m/Y',
                                    ],
                                ])->label('ตั้งแต่วันที่') ?>

                            </div>
                            <div class="w-50">

                                <?php
                                echo $form->field($model, 'data_json[date_start_type]')->widget(Select2::classname(), [
                                    'data' => [
                                        'เต็มวัน' => 'เต็มวัน',
                                        'ครึงวันบ่าย' => 'ครึงวันบ่าย',
                                    ],
                                    'options' => ['placeholder' => 'เลือก ...'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ])->label('เลือก');
                                ?>
                            </div>
                        </div>

                    </div>
                    <div class="col-6">
                        <div class="d-flex justify-content=between gap-2">
                            <div class="w-50">
                                <?= $form->field($model, 'date_end')->widget(Datetimepicker::className(), [
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
                            <div class="w-50">
                                <?php
                                echo $form->field($model, 'data_json[date_end_type]')->widget(Select2::classname(), [
                                    'data' => [
                                        'เต็มวัน' => 'เต็มวัน',
                                        'ครึงวันเช้า' => 'ครึงวันเช้า',
                                    ],
                                    'options' => ['placeholder' => 'เลือก ...'],
                                    'pluginOptions' => [
                                        'allowClear' => true
                                    ],
                                ])->label('เลือก');
                                ?>
                            </div>

                        </div>


                    </div>
                    <div class="col-12">
                        <?=$this->render('calendar')?>
                    </div>
                 
                </div>

                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[note]')->textArea(['style' =>'height:115px;'])->label('เหตุผล') ?>
                        <?= $form->field($model, 'data_json[address]')->textArea(['style' => 'height:58px;'])->label('ระหว่างลาติดต่อ') ?>
                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[phone]')->textInput()->label('เบอร์โทรติดต่อ') ?>
                        <?=$form->field($model, 'data_json[location]')->widget(Select2::classname(), [
    'data' => [
        'ภายในจังหวัด' => 'ภายในจังหวัด',
        'ต่างจังหวัด' => 'ต่างจังหวัด',
        'ต่างประเทศ' => 'ต่างประเทศ',
    ],
    'options' => ['placeholder' => 'เลือกสถานที่ไป ...'],
    'pluginOptions' => [
        'allowClear' => true
    ],
])->label('สถานที่ไป');
?>

                        <?php



try {
    //code...
    $initEmployee =  Employees::find()->where(['id' => $model->data_json['delegate']])->one()->getAvatar(false);
} catch (\Throwable $th) {
    $initEmployee = '';
}
echo $form->field($model, 'data_json[delegate]')->widget(Select2::classname(), [
    'initValueText' => $initEmployee,
    'options' => ['placeholder' => 'เลือกรายการ...'],
    'size' => Select2::LARGE,
    'pluginEvents' => [
        'select2:unselect' => 'function() {
            $("#order-data_json-board_fullname").val("")
            }',
        'select2:select' => 'function() {
                    var fullname = $(this).select2("data")[0].fullname;
                    var position_name = $(this).select2("data")[0].position_name;
                    $("#order-data_json-board_fullname").val(fullname)
                    $("#order-data_json-position_name").val(position_name)
                
            }',
    ],
    'pluginOptions' => [
        'allowClear' => true,
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



                    </div>
                </div>


                <?= $this->render('leader', ['form' => $form, 'model' => $model]) ?>

                <div class="row">
                    <div class="col-2">

                    </div>
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[leave_type]')->radioList(['เต็มวัน' => 'เต็มวัน', 'ลาครึ่งเช้า' => 'ลาครึ่งเช้า', 'ลาครึ่งบ่าย' => 'ลาครึ่งบ่าย'], ['custom' => true, 'inline' => true])->label('ประเภท') ?>
                    </div>
                </div>






                <div class="form-group mt-3 d-flex justify-content-center">
                    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$calDaysUrl = Url::to(['/lm/leave/cal-days']);
$js = <<< JS

\$('#form').on('beforeSubmit', function (e) {
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
                                            await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                        }
                                    }
                                });
                                return false;
                            });

$("input[name='Leave[data_json][leave_type]']").on('change', function() {
        // ดึงค่าที่ถูกเลือก
        var selectedValue = $("input[name='Leave[data_json][leave_type]']:checked").val();
        console.log(selectedValue); // แสดงค่าใน console
        if(selectedValue == "เต็มวัน"){
            $('#leave-leave_time_type').val(1);
        }else{
            $('#leave-leave_time_type').val(0.5);
        }
    });

    $('#leave-date_start').on('change', function() {
        var selectedDate = $(this).val();
        // Perform any action you want when the date is changed
        calDays(selectedDate);
        // You can add further logic here
    });

    $('#leave-date_end').on('change', function() {
        var selectedDate = $(this).val();
        // Perform any action you want when the date is changed
        // console.log('Selected Date: ' + selectedDate);
        calDays(selectedDate);
        // You can add further logic here
    });

    function calDays()
    {
        $.ajax({
            type: "get",
            url: "$calDaysUrl",
            data:{
                date_start:$('#leave-date_start').val(),
                date_end:$('#leave-date_end').val(),
            },
            dataType: "json",
            success: function (res) {
               $('.cal-days').html(res.summaryDay)
               console.log(res);
               
            //    $.pjax.reload({ container:'#calendar-container', history:false,replace: false,timeout: false});                               
               
                
            }
        });
    }

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

    $("#leave-date_start").datetimepicker({
        timepicker:false,
        format:'d/m/Y',  // กำหนดรูปแบบวันที่ ที่ใช้ เป็น 00-00-0000
        lang:'th',  // แสดงภาษาไทย
        onChangeMonth:thaiYear,
        onShow:thaiYear,
        yearOffset:543,  // ใช้ปี พ.ศ. บวก 543 เพิ่มเข้าไปในปี ค.ศ
        closeOnDateSelect:true,
    });

    $("#leave-date_end").datetimepicker({
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
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
    'id' => 'form-elave',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/lm/leave/create-validator']
]); ?>

<div class="row d-flex justify-content-center">
    <div class="col-lg-4 col-md-5">

        <div class="card">
            <div class="card-body">
                <?= $this->render('calendar') ?>
            </div>
        </div>

    </div>
    <div class="col-lg-8 col-md-8">
        <div class="card text-start">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h2><i class="fa-solid fa-file-pen"></i> บันทึกขอ<?= $model->leaveType->title ?></h2>
                    <div class="d-flex gap-3">
                        <?= $form->field($model, 'data_json[auto]')->checkbox(['custom' => true, 'switch' => true, 'checked' => true])->label('ไม่รวมวันหยุด'); ?>
                        <h6><span class="cal-days">0</span> / วัน</h6>

                    </div>
                </div>

                <?= $form->field($model, 'ref')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'leave_type_id')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'total_days')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[title]')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[director]')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'data_json[director_fullname]')->hiddenInput()->label(false) ?>
                
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

                    </div>

                </div>

                <div class="row">
                    <div class="col-6">
                        <?= $form->field($model, 'data_json[note]')->textArea(['style' => 'height:115px;'])->label('เหตุผล') ?>
                        <?= $form->field($model, 'data_json[address]')->textArea(['style' => 'height:58px;'])->label('ระหว่างลาติดต่อ') ?>
                    </div>
                    <div class="col-6">
                        <div>

                        </div>
                        <?= $form->field($model, 'data_json[phone]')->textInput()->label('เบอร์โทรติดต่อ') ?>
                        <?= $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
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

                <div class="form-group mt-3 d-flex justify-content-center gap-3">
                    <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
                    <?=Html::a('<i class="bi bi-arrow-left-circle"></i> ย้อนกลับ',['/lm/leave'],['class' => 'btn btn-warning rounded-pill shadow'])?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ActiveForm::end(); ?>
<?php
$calDaysUrl = Url::to(['/lm/leave/cal-days']);
$js = <<< JS




document.addEventListener('DOMContentLoaded', function() {

  var calendarEl = document.getElementById('calendar');
  var Calendar = FullCalendar.Calendar;
  var Draggable = FullCalendar.Draggable;
  var Draggable2 = FullCalendar.addEvent;
  var containerEl = document.getElementById('external-events');
  var checkbox = document.getElementById('drop-remove');

  // initialize the external events
  // -----------------------------------------------------------------

  new Draggable(containerEl, {
    itemSelector: '.fc-event',
    eventData: function(eventEl) {
      return {
        title: eventEl.innerText
      };
    }
  });


  var calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'th',
    initialView: 'dayGridMonth',
    headerToolbar: {
    left: 'addEventButton',

    },
    editable: true,
    selectable: true,
    selectHelper: true,
    droppable: true,
    drop: function(info) {
        // console.log('drop');
        console.log('drop: ' + info.dateStr);
      if (checkbox.checked) {
        info.draggedEl.parentNode.removeChild(info.draggedEl);
      }
    },
    eventDrop: function(info) {
            // console.log('Event dropped:');
            // console.log('Title: ' + info.event.title);
            // console.log('New Start: ' + formatDate(info.event.start));
            // console.log('New End: ' + formatDate(info.event.end));
            if(info.event.title !='วัน OFF'){
            var dateStart = formatDateThai(info.event.start);
            var dateEnd = formatDateThai(info.event.end);
            $('#leave-date_start').val(dateStart);
            $('#leave-date_end').val(dateEnd)
            console.log(dateStart,' ถึง '+ dateEnd);
            
            }
        },

        eventResize: function(info) {
            // console.log('resized: ' + info.dateStr);
            // console.log('Event resized:');
            // console.log('Title: ' + info.event.title);
            console.log('New Start: ' + formatDate(info.event.start));
            console.log('New End: ' + formatDate(info.event.end));

            // var dateStart = formatDate(info.event.start);
            // var dateEnd = formatDate(info.event.end);
            // if(info.event.title !='วัน OFF'){
            //     var dateStart = formatDateThai(info.event.start);
            //     var dateEnd = formatDateThai(info.event.end);
            //     $('#leave-date_start').val(dateStart);
            //     $('#leave-date_end').val(dateEnd)
            // console.log(dateStart,' ถึง '+ dateEnd);

            // }
        },
    dateClick: function(info) {
        // console.log('dateClick');
        var dateEnd = formatDateThai(info.date);
        console.log('Date: ' + dateEnd);
        // console.log('Resource ID: ' + info.resource);
        
      // Open modal for event input
      // Handle form submission


    },

  });


  
  calendar.render();


  $('#form-elave').on('beforeSubmit', function (e) {
    var form = $(this);
    console.log('Submit');

    Swal.fire({
  title: "บันทึกหรือไม่?",
  text: "ยืนยันบันทึกการลา!",
  icon: "warning",
  showCancelButton: true,
  confirmButtonColor: "#3085d6",
  cancelButtonColor: "#d33",
  cancelButtonText: "ยกเลิก!",
  confirmButtonText: "ใช่, ยืนยัน!"
}).then((result) => {
  if (result.isConfirmed) {
    
    $.ajax({
        url: form.attr('action'),
        type: 'post',
        data: form.serialize(),
        dataType: 'json',
        success: async function (response) {
            form.yiiActiveForm('updateMessages', response, true);
            if(response.status == 'success') {
                closeModal()
                // success()
                await  $.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
            }
        }
    });

  }
});


    return false;
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
        $.ajax({
            type: "get",
            url: "$calDaysUrl",
            data:{
                date_start:$('#leave-date_start').val(),
                date_end:$('#leave-date_end').val(),
            },
            dataType: "json",
            success: function (res) {
               $('.cal-days').html(res[0].summaryDay)
               console.log(res);

               var newStart = res.start; // New event start date
                var newEnd = res.end;   // New event end date
                var newTitle = $('#leave-data_json-title').val();
 // Get all events
            var events = calendar.getEvents();

            // Find if there's an event with the same title
            var existingEvent = events.find(function(event) {
            return event.title === newTitle;
            });

            if (existingEvent) {
            // Update existing event if title matches
            existingEvent.setProp('title', newTitle); // Update title
            existingEvent.setDates(newStart, newEnd); // Update start and end dates
            } else {
            // Add new event if no matching event is found
            calendar.addEvent({
                title: newTitle,
                start: newStart,
                end: newEnd
            });
            } 
                
            }
        });
    }


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

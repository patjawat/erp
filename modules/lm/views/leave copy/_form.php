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

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
/** @var yii\widgets\ActiveForm $form */


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
<div class="row">
    <div class="col-8">



        <div class="card text-start">
            <div class="card-body">



                <div class="d-flex justify-content-between">
                    <h2><i class="fa-solid fa-file-pen"></i> บันทึกขอ<?= $model->leaveType->title ?></h2>
                    <?php //$form->field($model, 'id')->checkbox(['custom' => true, 'switch' => true, 'checked' => true])->label('ไม่รวม'); ?>
                </div>

                <?= $form->field($model, 'leave_type_id')->hiddenInput()->label(false) ?>
                <?= $form->field($model, 'days_off')->hiddenInput()->label(false) ?>


                <div class="row">
                    <div class="col-5">
               
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
                   

                        <?php
echo $form->field($model, 'data_json[location]')->widget(Select2::classname(), [
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
                    </div>
                    <div class="col-5">
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


                        <?= $form->field($model, 'data_json[phone]')->textInput()->label('เบอร์โทรติดต่อ') ?>

                    </div>
                    <div class="col-2">
                        <div class="card border-1 mt-3">
                            <div class="card-body">
                                <h1 class="text-center cal-days">0</h1>
                            </div>
                            <div class="card-footer">
                                จำนวน/วัน
                            </div>
                        </div>

                    </div>
                    <div class="col-6">
                        <?php //  $form->field($model, 'data_json[note]')->textArea(['rows' => 6])->label('เหตุผลการลา')
                        ?>
                        <?= $form->field($model, 'data_json[note]')->textArea(['style' =>'height:55px;'])->label('เหตุผล') ?>
                        <?= $form->field($model, 'data_json[address]')->textArea(['style' =>'height:155px;'])->label('ระหว่างลาติดต่อ') ?>
                    </div>
                    <div class="col-6">
                        <?=$this->render('leader',['form' => $form,'model' => $model])?>
                    </div>
                </div>

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
    <div class="col-4">


        <div class="card text-start">
            <div class="card-body">
            <?php //  Pjax::begin(['id' => 'calendar-container']); ?>
                <?php 
    //              \edofre\fullcalendar\Fullcalendar::widget([
    //     'options'       => [
    //         'id'       => 'calendar',
    //         'language' => 'th',
    //         'themeSystem'=> 'bootstrap5',
    //     ],
    //     'clientOptions' => [
    //         'weekNumbers' => true,
    //         'selectable'  => true,
    //         'defaultView' => 'month',
    //         'eventResize' => new JsExpression("
    //             function(event, delta, revertFunc, jsEvent, ui, view) {
    //                 console.log(event);
    //             }
    //         "),

    //         'eventClick' => new JsExpression("
    //          function(event, delta, revertFunc, jsEvent, ui, view) {
    //                 console.log(event);
    //             }
    //               "),

    //     ],
    //     'events'        => Url::to(['calendar/events', 'id' => 1]),
    // ]);
?>


<?= moreamazingnick\fullcalendar\Fullcalendar::widget([
     'options'       => [
        'id'       => 'calendar',
    ],
    // 'clientOptions' => [
    //     'locale' => 'th',
    //     'timeZone'=> 'local',

    //     //'initialView'=>'timeGridWeek',
    //     'firstDay' => 1,
    //     'weekNumbers' => true,
    //     'selectable'  => true,
    //     'eventTimeFormat' => [
    //         'hour' => '2-digit',
    //         'minute' => '2-digit',
    //         'omitZeroMinute' => false,
    //     ],
    //     'editable' => true,
        
    // ],

    'clientOptions' => [
        //modify eventcontainer
        'eventDidMount' => new JsExpression("
            function (info) {
                    let p = document.createElement('span');
                    p.innerHTML='mytest';
                    p.className = 'mytest';
                    info.el.append(p)
            }
        "),
        'eventAdd'=>new JsExpression("
            function (info) {
                console.log(info);
            }
        "),
        'loading' =>new JsExpression("
            function(bool) {
                 if(bool){
                    //   doSomething();
                 }else{
                    //  doSomethingElse();
                 }
            }
        "),
        // select an empty time slot and do something, like create an event
        'select' => new JsExpression("
             function(arg) {
                 Event = {
                     title: 'Slot',
                     start: arg.start,
                     end: arg.end,
                     editable: true,
                     startEditable: true,
                     durationEditable: true,
                     allDay: arg.allDay,
                 };
                 postevent=JSON.parse(JSON.stringify({Event}));
                     jQuery.ajax({
                         type: 'POST',
                         url: '" . Url::to(['yourcontroller/create-event']) . "',
                         data: postevent,
                         success: function(response){
                             console.log('create event select');
                             if(response){
                                 calendar.addEvent(response)
                             }else{
                                 alert('Could not create event!');
                             }
                             calendar.unselect() 
                        },
                        error: function(){
                            alert('Could not create event!');
                            calendar.unselect()
                        },
                    });
             }
        "),
        // moves event from one timeslot to another
        'eventDrop' => new JsExpression(" 
            function(eventDropInfo) {
           

        }"),
        // You can use the same implementation as in eventDrop
        'eventResize' => new JsExpression("
            function(eventResizeInfo) {
         console.log('re')
            }
        "),
        //OnClick event, for example you can open a modal window or fetch more details
        'eventClick'=>new JsExpression("
            function (e) {
            }
        "),       
    ],
   'events'        => new JsExpression('[
            {
                "id":null,
                "title":"Appointment #776",
                "allDay":false,
                "start":"2024-09-18T14:00:00",
                "end":null,
                "url":null,
                "className":null,
                "editable":false,
                "startEditable":false,
                "durationEditable":false,
                "rendering":null,
                "overlap":true,
                "constraint":null,
                "source":null,
                "color":null,
                "backgroundColor":"grey",
                "borderColor":"black",
                "textColor":null
            },
            {
                "id":"56e74da126014",
                "title":"Appointment #928",
                "allDay":false,
                "start":"2024-09-17T12:30:00",
                "end":"2024-09-17T13:30:00",
                "url":null,
                "className":null,
                "editable":true,
                "startEditable":true,
                "durationEditable":true,
                "rendering":null,
                "overlap":true,
                "constraint":null,
                "source":null,
                "color":null,
                "backgroundColor":"grey",
                "borderColor":"black",
                "textColor":null
            },
            {
                "id":"56e74da126050",
                "title":"Appointment #197",
                "allDay":false,
                "start":"2024-09-17T15:30:00",
                "end":"2024-09-17T19:30:00",
                "url":null,
                "className":null,
                "editable":true,
                "startEditable":true,
                "durationEditable":true,
                "rendering":null,
                "overlap":false,
                "constraint":null,
                "source":null,
                "color":null,
                "backgroundColor":"grey",
                "borderColor":"black",
                "textColor":null
            },
            {
                "id":"56e74da126080",
                "title":"Appointment #537",
                "allDay":false,
                "start":"2024-09-16T11:00:00",
                "end":"2024-09-16T11:30:00",
                "url":null,
                "className":null,
                "editable":false,
                "startEditable":false,
                "durationEditable":true,
                "rendering":null,
                "overlap":true,
                "constraint":null,
                "source":null,
                "color":null,
                "backgroundColor":"grey",
                "borderColor":"black",
                "textColor":null
            },
            {
                "id":"56e74da1260a7",
                "title":"Appointment #465",
                "allDay":false,
                "start":"2024-09-15T14:00:00",
                "end":"2024-09-15T15:30:00",
                "url":null,
                "className":null,
                "editable":false,
                "startEditable":true,
                "durationEditable":false,
                "rendering":null,
                "overlap":true,
                "constraint":null,
                "source":null,
                "color":null,
                "backgroundColor":"grey",
                "borderColor":"black",
                "textColor":null
            },
        ]'),
    ]);
?>



    <?php // Pjax::end(); ?>

            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h4><i class="fa-solid fa-circle-info"></i> สรุปการลา </h4>
                <hr>
                <!-- <h6><?= $model->leaveType->icon ?> <?= $model->leaveType->title ?></h6> -->
                <div class="d-flex justify-content-between">

                    <h6>ประเภท : <?= $model->leaveType->title ?> <?= $model->leaveType->icon ?></h6>
                </div>
                <div class="d-flex justify-content-between">
                    <p class="card-text">รวมวันลา</p>
                    <p class="card-text">จำนวน <span id="calDays" class="fs-1 cal-days">0</span> วัน</p>

                </div>
            </div>
        </div>

        <div class="card text-start">
            <div class="card-body">





            </div>
        </div>

        <?php
        // echo "<pre>";
        // print_r($model->Approve());
        // echo "</pre>";
        ?>

<button id="update-event-btn">Update Event Date</button>
    </div>
</div>

<?php ActiveForm::end(); ?>

<?php
$calDaysUrl = Url::to(['/lm/leave/cal-days']);
$js = <<< JS

$('#update-event-btn').click(function (e) { 
      // ดึง FullCalendar instance
      var calendar = $('#calendar').fullCalendar();
        
        // ดึง event ที่ต้องการอัปเดต โดยใช้ event ID
        var event = calendar.getEventById(); // ตัวอย่าง ID event

        // ถ้าเจอ event ทำการอัปเดตวันที่ใหม่
        if (event) {
            event.setDates('2024-09-25', '2024-09-26'); // อัปเดตวันที่
        } else {
            console.log('Event not found');
        }
    
});



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
               
               $.pjax.reload({ container:'#calendar-container', history:false,replace: false,timeout: false});                               
               
                
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
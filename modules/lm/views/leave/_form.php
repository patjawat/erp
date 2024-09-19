<?php

use app\models\Categorise;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;
use kartik\widgets\Select2;
use yii\helpers\ArrayHelper;

/** @var yii\web\View $this */
/** @var app\modules\lm\models\Leave $model */
/** @var yii\widgets\ActiveForm $form */
?>


<div class="leave-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/lm/leave/create-validator']
    ]); ?>

<div class="row">
    <div class="col-4">
    <?php
  
  echo $form->field($model, 'leave_type_id')->widget(Select2::classname(), [
      'data' => ArrayHelper::map(Categorise::find()->where(['name' => 'leave_type'])->all(),'code','title'),
      'options' => ['placeholder' => '',
  ],
      'pluginOptions' => [
          'allowClear' => true,
          'dropdownParent' => '#main-modal',
      ],
      'pluginEvents' => [
          'select2:select' => "function(result) { 

                  }",
      ]
  ])->label('ประเภท');
  ?>
    <?php //  $form->field($model, 'leave_time_type')->textInput()->label('จำนวนวันที่ลา') ?>
</div>
<div class="col-6">
    <?= $form->field($model, 'data_json[leave_type]')->radioList(['เต็มวัน' => 'เต็มวัน', 'ลาครึ่งเช้า' => 'ลาครึ่งเช้า', 'ลาครึ่งบ่าย' => 'ลาครึ่งบ่าย'], ['custom' => true, 'inline' => true])->label('ประเภท') ?>
</div>
</div>
    <div class="row">
        <div class="col-6">
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
        <div class="col-6">
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
    </div>


<div
    class="alert alert-primary"
    role="alert"
>
    <h4 class="alert-heading">คำนวนได้ <span id="calDays">0</span></h4>
    <p>Alert Content</p>
    <hr />
    <p class="mb-0">Alert Description</p>
</div>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

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
               $('#calDays').html(res.summaryDay)
               console.log(res);
               
                
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
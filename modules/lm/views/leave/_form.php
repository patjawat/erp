<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;
use kartik\widgets\ActiveForm;
use iamsaint\datetimepicker\Datetimepicker;

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

    <?php //  $form->field($model, 'leave_type_id')->textInput(['maxlength' => true]) 
    ?>
<div class="row">
<div class="col-4">
    <?= $form->field($model, 'leave_time_type')->textInput()->label('จำนวนวันที่ลา') ?>
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




    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$calDaysUrl = Url::to(['/lm/leave/cal-days']);
$js = <<< JS

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
        console.log('Selected Date: ' + selectedDate);
        calDays(selectedDate);
        // You can add further logic here
    });

    $('#leave-date_end').on('change', function() {
        var selectedDate = $(this).val();
        // Perform any action you want when the date is changed
        console.log('Selected Date: ' + selectedDate);
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
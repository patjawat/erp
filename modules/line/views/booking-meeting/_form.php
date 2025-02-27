<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use yii\widgets\MaskedInput;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\modules\booking\models\Room;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentsDetail;

$me = UserHelper::GetEmployee();
$room = Room::findOne(['name' => 'meeting_room','code' => $model->room_id]);

try {
    $mappedDataAccessory = ArrayHelper::map(
        array_map(fn($v) => ['name' => $v], $room->data_json['room_accessory']), 
        'name', 
        'name'
    );
    
} catch (\Throwable $th) {
    $mappedDataAccessory = [];
}


?>

<style>
    .form-label {
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.custom-radio-list label {
    display: inline-block;
    padding: 8px 16px;
    border: 2px solid #ccc;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: white;
    color: black;
}

.custom-radio-list input[type="radio"] {
    display: none;
}

.custom-radio-list input[type="radio"]:checked + label {
    background: #007bff;
    color: white;
    border-color: #007bff;
}

.custom-radio-list label:hover {
    background: #f0f0f0;
}

</style>
<div style="margin-top:70px" class="pb-5">


    <?php $form = ActiveForm::begin([
            'id' => 'booking-form',
            'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
            'validationUrl' => ['/me/booking-meeting/validator']
        ]); ?>

    <?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'room_id')->hiddenInput()->label(false) ?>
    <?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>


    <div class="flex-shrink-0 rounded p-5 mb-4 mt-5"
        style="margin-top:20px;background-image:url(<?php echo $room->showImg()?>);background-size:cover;background-repeat:no-repeat;background-position:center;height:200px;">

    </div>

    <div class="card">
        <div class="card-body">
        <div class="d-flex justify-content-center align-items-center mb-2">
    <h4 class="text-center"><?php echo $model->room->title; ?></h4>
</div>
            <div class="row">
                
                <div class="col-12">
                    <div class="custom-radio-list mb-2 d-flex gap-3 justify-content-center">
    <?php

        $options = [
            'เต็มวัน' => 'เต็มวัน',
            'ครึ่งวันเช้า' => 'ครึ่งวันเช้า',
            'ครึ่งวันบ่าย' => 'ครึ่งวันบ่าย',
        ];
    
    foreach ($options as $key => $label) {
        echo Html::radio('Booking[data_json][period_time]', false, [
            'value' => $key,
            'class' => 'hidden',
            'id' => 'period-' . $key
        ]);
        echo Html::label($label, 'period-' . $key);
    }
    ?>
</div>
                    <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่ต้องการประชุม','class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10'])->label('วันที่ขอใช้ห้องประชุม') ?>
                </div>

                <div class="col-6">
                    <?= $form->field($model, 'time_start')->widget(MaskedInput::class, ['mask' => '99:99','options' => ['class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10']])->label('เวลาเริ่ม') ?>
                </div>
                <div class="col-6">
                    <?= $form->field($model, 'time_end')->widget(MaskedInput::class, ['mask' => '99:99','options' => ['class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10']])->label('ถึงเวลา') ?>
                </div>

                <div class="col-12">
                    <?= $form->field($model, 'reason')->textArea(['class' => 'form-control form-control-lg rounded-2 border-0 bg-secondary text-opacity-100 bg-opacity-10'])->label('เรื่องการประชุม') ?>
                </div>
                <div class="col-8">
                    <?= $form->field($model, 'data_json[employee_point]')->textInput(['class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10'])->label('กลุ่มบุคคลเป้าหมาย') ?>
                </div>

                <div class="col-4">
                    <?= $form->field($model, 'data_json[employee_total]')->textInput(['type' => 'number','class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10'])->label('จำนวน') ?>
                </div>

                <div class="col-12">
                    <?= $form->field($model, 'data_json[phone]')->textInput(['placeholder' => 'เบอร์โทรศัพท์ติดต่อ','class' => 'form-control form-control-lg rounded-pill border-0 bg-secondary text-opacity-100 bg-opacity-10'])->label('เบอร์ติดต่อ') ?>
                </div>


            </div>







            <?= $form->field($model, 'data_json[accessory]')->checkboxList($mappedDataAccessory)->label('รายการอุปกรณ์ที่ต้องการ') ?>



        </div>
    </div>

    <div class="form-group mt-3 d-flex justify-content-center gap-3 mb-5">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary border border-white rounded-pill shadow', 'id' => 'summit']) ?>
        <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
                class="fa-regular fa-circle-xmark"></i> ปิด</button>
    </div>

    <?php ActiveForm::end(); ?>
</div>
<?php

$js = <<<JS


      thaiDatepicker('#booking-date_start,#booking-date_end')


      $('#listEmployee').click(function (e) { 
        e.preventDefault();
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            dataType: "json",
            success: function (res) {
                $('#showListEmployee').html(res.content)
            }
        });
        
      });
      \$('#booking-date_start').on('change', function() {
            var dateStart = \$('#booking-date_start').val();
            var dateEnd = \$('#booking-date_end').val();
            listCars(dateStart,dateEnd)
        });

        

      \$('#booking-form').on('beforeSubmit', function (e) {
        var form = \$(this);

        Swal.fire({
        title: "ยืนยัน?",
        text: "ขอใช้ห้องประชุม!",
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
                boforeSubmit: function(){
                    beforLoadModal()
                },
                success: function (response) {
                    // form.yiiActiveForm('updateMessages', response, true);
                    if(response.status == 'success') {
                        closeModal()
                        location.reload(true)
                        // success()
                        // await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });
        }
        });
        return false;
    });

    $("body").on("click", ".select-car", function (e) {
        e.preventDefault();
        let licensePlate = $(this).data("license_plate"); // ดึงค่าจาก data-license_plate
        $('#req_license_plate').val(licensePlate)
        $('#booking-license_plate').val(licensePlate)
        $('#booking-data_json-req_license_plate').val(licensePlate)
        
        // $("#car-container .card").removeClass("border-2 border-primary");

    
        $(this).find(".card").addClass("border-2 border-primary");
        $(this).find(".checked").html('<i class="fa-solid fa-circle-check text-success fs-4"></i>')
        $("#offcanvasRight").offcanvas("hide"); // ปิด Offcanvas
        success('เลือกรถที่ต้องการใช้งานเรียบร้อยแล้ว')

        let cloned = $(this).clone(); // Clone ตัวเอง
        // ลบคลาส select-car
        cloned.removeClass("select-car hover-card");
        cloned.addClass("border-2 border-primary");

        // เพิ่ม attributes ที่ต้องการ
        cloned.attr({
            "data-bs-toggle": "offcanvas",
            "data-bs-target": "#offcanvasRight",
            "aria-controls": "offcanvasRight"
        });
        $("#selectCar").html(cloned); // ใส่ใน container

    });

    $('input[name="Booking[data_json][period_time]"]').change(function () {
        setTime();
    });

    function setTime() {
        var period_time = $('input[name="Booking[data_json][period_time]"]:checked').val();
        if (period_time == 'เต็มวัน') {
            $('#booking-time_start').val('08:00');
            $('#booking-time_end').val('16:00');
        } else if (period_time == 'ครึ่งวันเช้า') {
            $('#booking-time_start').val('08:00');
            $('#booking-time_end').val('12:00');
        } else if (period_time == 'ครึ่งวันบ่าย') {
            $('#booking-time_start').val('13:30');
            $('#booking-time_end').val('16:00');
        }
    }

    
    JS;
$this->registerJS($js, View::POS_END);

?>
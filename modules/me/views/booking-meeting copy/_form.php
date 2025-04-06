<?php
use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\JsExpression;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\components\UserHelper;
use kartik\widgets\ActiveForm;
use app\modules\booking\models\Room;
use app\modules\hr\models\Employees;
use app\modules\hr\models\Organization;
use app\modules\dms\models\DocumentsDetail;

$me = UserHelper::GetEmployee();
$room = Room::findOne(['name' => 'meeting_room', 'code' => $model->room_id]);

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


<?php $form = ActiveForm::begin([
    'id' => 'booking-form',
    'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
    'validationUrl' => ['/me/booking-meeting/validator']
]); ?>

<?php
echo $form->field($model, 'room_id')->widget(Select2::classname(), [
    'data' => $model->listRooms(),
    'options' => [
        'class' => 'bg-danger',  // เพิ่ม class ตรงนี้
        'placeholder' => 'เลือกช่วงเวลา...',
    ],
    'pluginOptions' => [
        'allowClear' => true,
        'dropdownParent' => '#main-modal',
        // 'width' => '150px',
    ],
    'pluginEvents' => [
        'select2:unselect' => 'function() {
                                    setTime();
                                    }',
        'select2:select' => 'function() {
                                        setTime();
                                    }',
    ],
])->label('ห้องประชุม');
?>

<div class="row">
    <div class="col-6">
        <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่ต้องการประชุม', 'class' => ''])->label('ตั้งแต่วันที่') ?>
        <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลาเริ่ม') ?>
    </div>
    <div class="col-6">

        <?php
        echo $form->field($model, 'data_json[period_time]')->widget(Select2::classname(), [
            'data' => [
                'เต็มวัน' => 'เต็มวัน',
                'ครึ่งวันเช้า' => 'ครึ่งวันเช้า',
                'ครึ่งวันบ่าย' => 'ครึ่งวันบ่าย',
            ],
            'options' => [
                'class' => 'bg-danger',  // เพิ่ม class ตรงนี้
                'placeholder' => 'เลือกช่วงเวลา...',
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
                // 'width' => '150px',
            ],
            'pluginEvents' => [
                'select2:unselect' => 'function() {
                                    setTime();
                                    }',
                'select2:select' => 'function() {
                                        setTime();
                                        }',
            ],
        ])->label('ช่วงเวลา');
        ?>
        <?= $form->field($model, 'time_end')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('ถึงเวลา') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'reason')->textInput(['class' => ''])->label('เรื่องการประชุม') ?>
    </div>

</div>



<div class="mt-1">
    <h6>🎤 อุปกรณ์เพิ่มเติม</h6>

    <div class="row">

        <?php
        $equipmentList = [
            'projector' => '📽️ โปรเจคเตอร์',
            'microphone' => '🎙️ ไมโครโฟน',
            'whiteboard' => '📝 กระดานไวท์บอร์ด',
        ];

        foreach ($equipmentList as $key => $label):
            ?>
        <div class="col-4">

            <div class="form-check">
                <?= Html::checkbox("MeetingRoomForm[equipment][$key]", false, [
                    'class' => 'form-check-input equipment-checkbox',
                    'id' => $key
                ]) ?>
                <?= Html::label($label, $key, ['class' => 'form-check-label']) ?>

                <?= Html::input('number', "MeetingRoomForm[equipment_quantity][$key]", '', [
                    'class' => 'form-control mt-1 equipment-quantity',
                    'min' => 1,
                    'max' => ($key === 'projector' ? 5 : ($key === 'microphone' ? 10 : 3)),
                    'placeholder' => 'จำนวน',
                    'disabled' => true
                ]) ?>
            </div>
        </div>
        <?php endforeach; ?>

    </div>
</div>

<div class="mt-3">
    <label class="form-label">📝 หมายเหตุเพิ่มเติม (ถ้ามี)</label>
    <?= $form->field($model, 'data_json[note]')->textArea(['placeholder' => 'เพิ่มรายละเอียดเพิ่มเติม', 'rows' => 3, 'class' => ''])->label(false) ?>
</div>
    <?= $form->field($model, 'data_json[phone]')->textInput(['placeholder' => 'เบอร์โทรศัพท์ติดต่อ', 'class' => ''])->label('เบอร์ติดต่อ') ?>

<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>

<?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>

<div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
      thaiDatepicker('#booking-date_start')
      $('#listEmployee').click(function (e) { 
        e.preventDefault();
        \$.ajax({
            type: "get",
            url: $(this).attr('href'),
            dataType: "json",
            success: function (res) {
                $('#showListEmployee').html(res.content)
            }
        });
        
      });
      $('#booking-date_start').on('change', function() {
            var dateStart = $('#booking-date_start').val();
            var dateEnd = $('#booking-date_end').val();
            listCars(dateStart,dateEnd)
        });

        

      $('#booking-form').on('beforeSubmit', function (e) {
        var form = $(this);

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

    function setTime()
    {
        var period_time = $('#booking-data_json-period_time').val();
        var dateStart = $('#booking-date_start').val();
        var dateEnd = $('#booking-date_end').val();
        if(period_time == 'เต็มวัน'){
            $('#booking-time_start').val('08:00')
            $('#booking-time_end').val('16:00')
        }else if(period_time == 'ครึ่งวันเช้า'){
            $('#booking-time_start').val('08:00')
            $('#booking-time_end').val('12:00')
        }else if(period_time == 'ครึ่งวันบ่าย'){
            $('#booking-time_start').val('13:30')
            $('#booking-time_end').val('16:00')
        }
    }


JS;
$this->registerJS($js, View::POS_END);
?>
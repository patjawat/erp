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
        .layout-option {
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .layout-option:hover, .layout-option.active {
            border-color: #0d6efd;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
        }
    </style>
    
                <form>
                    <!-- เลือกห้องประชุม -->
                    <div class="mb-3">
                        <label class="form-label">🏢 เลือกห้องประชุม</label>
                        <select class="form-select" required>
                            <option value="">-- เลือกห้อง --</option>
                            <option value="A">ห้อง A</option>
                            <option value="B">ห้อง B</option>
                            <option value="C">ห้อง C</option>
                        </select>
                    </div>

                    <!-- วันที่ -->
                    <div class="mb-3">
                        <label class="form-label">📅 วันที่</label>
                        <input type="date" class="form-control" required>
                    </div>

                    <!-- เวลาเริ่ม - เวลาเลิก -->
                    <div class="row">
                        <div class="col-md-6">
                            <label class="form-label">⏰ เวลาเริ่ม</label>
                            <input type="time" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">⏳ เวลาสิ้นสุด</label>
                            <input type="time" class="form-control" required>
                        </div>
                    </div>


                    <div class="mt-4">
                        <h5>🎤 อุปกรณ์เพิ่มเติม</h5>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="projector">
                            <label class="form-check-label" for="projector">📽️ โปรเจคเตอร์</label>
                            <input type="number" class="form-control mt-1" min="1" max="5" placeholder="จำนวน" disabled>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="microphone">
                            <label class="form-check-label" for="microphone">🎙️ ไมโครโฟน</label>
                            <input type="number" class="form-control mt-1" min="1" max="10" placeholder="จำนวน" disabled>
                        </div>
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="whiteboard">
                            <label class="form-check-label" for="whiteboard">📝 กระดานไวท์บอร์ด</label>
                            <input type="number" class="form-control mt-1" min="1" max="3" placeholder="จำนวน" disabled>
                        </div>
                    </div>

                    
                    <!-- รูปแบบการจัดห้อง -->
                    <div class="mt-4">
                        <h5>📐 รูปแบบการจัดห้อง</h5>
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <img src="https://via.placeholder.com/100?text=Theater" class="img-fluid layout-option" data-layout="Theater">
                                <p>🏛 Theater</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <img src="https://via.placeholder.com/100?text=Classroom" class="img-fluid layout-option" data-layout="Classroom">
                                <p>🏢 Classroom</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <img src="https://via.placeholder.com/100?text=U-Shape" class="img-fluid layout-option" data-layout="U-Shape">
                                <p>👥 U-Shape</p>
                            </div>
                            <div class="col-md-3 text-center">
                                <img src="https://via.placeholder.com/100?text=Boardroom" class="img-fluid layout-option" data-layout="Boardroom">
                                <p>🏫 Boardroom</p>
                            </div>
                        </div>
                        <input type="hidden" id="selectedLayout" name="layout" required>
                    </div>


<div class="mt-4">
    <h5>🎤 อุปกรณ์เพิ่มเติม</h5>

    <?php
    $equipmentList = [
        'projector' => '📽️ โปรเจคเตอร์',
        'microphone' => '🎙️ ไมโครโฟน',
        'whiteboard' => '📝 กระดานไวท์บอร์ด',
    ];

    foreach ($equipmentList as $key => $label): ?>
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
    <?php endforeach; ?>
</div>

                    <!-- หมายเหตุเพิ่มเติม -->
                    <div class="mt-3">
                        <label class="form-label">📝 หมายเหตุเพิ่มเติม (ถ้ามี)</label>
                        <textarea class="form-control" rows="3" placeholder="เพิ่มรายละเอียดเพิ่มเติม"></textarea>
                    </div>

                    <!-- ปุ่มยืนยัน -->
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success btn-lg">✅ ยืนยันการจอง</button>
                    </div>
                </form>
                
<?php $form = ActiveForm::begin([
            'id' => 'booking-form',
            'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
            'validationUrl' => ['/me/booking-meeting/validator']
        ]); ?>

<div class="flex-shrink-0 rounded p-5 mb-4"
    style="background-image:url(<?php echo $room->showImg()?>);background-size:cover;background-repeat:no-repeat;background-position:center;height:200px;">

</div>

<ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">ระบุบรายละเอียดการขอใช้ห้องประชุม</button>
  </li>

  <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-accessory-tab" data-bs-toggle="pill" data-bs-target="#pills-accessory" type="button" role="tab" aria-controls="pills-accessory" aria-selected="false">รายการอุปกรณ์</button>
  </li>
  <!-- <li class="nav-item" role="presentation">
    <button class="nav-link" id="pills-layout_room-tab" data-bs-toggle="pill" data-bs-target="#pills-layout_room" type="button" role="tab" aria-controls="pills-layout_room" aria-selected="false">รูปแบบการจัดโต๊ะ</button>
  </li> -->
</ul>
<div class="tab-content" id="pills-tabContent">
  <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
    

  <div class="row">


  <div class="col-4">
        <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่ต้องการประชุม','class' => ''])->label('ตั้งแต่วันที่') ?>
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
                                    'class' => 'bg-danger', // เพิ่ม class ตรงนี้
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
    </div>

    
    <div class="col-12">
        <?= $form->field($model, 'reason')->textInput(['class' => ''])->label('เรื่องการประชุม') ?>
    </div>
    <div class="col-8">
        <?= $form->field($model, 'data_json[employee_point]')->textInput(['class' => ''])->label('กลุ่มบุคคลเป้าหมาย') ?>
    </div>

    <div class="col-4">
        <?= $form->field($model, 'data_json[employee_total]')->textInput(['type' => 'number','class' => ''])->label('จำนวน') ?>
    </div>

    <div class="col-6">
        <?= $form->field($model, 'data_json[phone]')->textInput(['placeholder' => 'เบอร์โทรศัพท์ติดต่อ','class' => ''])->label('เบอร์ติดต่อ') ?>
    </div>
    <div class="col-2">
        <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', [
    'mask' => '99:99'])->label('เวลาเริ่ม') ?>
    </div>
    <div class="col-2">
        <?= $form->field($model, 'time_end')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('ถึงเวลา') ?>
    </div>


</div>

<?= $form->field($model, 'name')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'room_id')->hiddenInput()->label(false) ?>
<?= $form->field($model, 'emp_id')->hiddenInput()->label(false) ?>




  </div>

  <div class="tab-pane fade" id="pills-accessory" role="tabpanel" aria-labelledby="pills-accessory-tab" tabindex="0">

  <?= $form->field($model, 'data_json[accessory]')->checkboxList($mappedDataAccessory)->label('รายการอุปกรณ์ที่ต้องการ') ?>
  </div>
  <!-- <div class="tab-pane fade" id="pills-layout_room" role="tabpanel" aria-labelledby="pills-layout_room-tab" tabindex="0">...</div> -->
</div>


<div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>

<?php ActiveForm::end(); ?>

<?php

$js = <<<JS


      thaiDatepicker('#booking-date_start,#booking-date_end')


        // เมื่อเลือกการจัดห้องประชุม
        document.querySelectorAll(".layout-option").forEach(img => {
            img.addEventListener("click", function() {
                document.querySelectorAll(".layout-option").forEach(i => i.classList.remove("active"));
                this.classList.add("active");
                document.getElementById("selectedLayout").value = this.dataset.layout;
            });
        });
        

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
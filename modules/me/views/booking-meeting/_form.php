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
<style>
    .room-img{
        object-fit: cover;max-width: 100%;height: auto;
    }
</style>
<div class="container-xx">
    <?php // $this->render('navbar')?>

    <?php $form = ActiveForm::begin([
        'id' => 'meeting-form',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/me/booking-meeting/validator']
    ]); ?>


    <div class="row">
        <div class="col-7">
            <div class="card text-start">
                <div class="card-body">
                    <h4 class="fw-medium mb-2">จองห้องประชุม</h4>
                    <p class="card-text">กรอกข้อมูลเพื่อจองห้องประชุม</p>
                    <?php
                    echo $form->field($model, 'room_id')->widget(Select2::classname(), [
                        'data' => $model->listRooms(),
                        'options' => [
                            'class' => 'bg-danger',  // เพิ่ม class ตรงนี้
                            'placeholder' => 'เลือกห้องประชุม...',
                        ],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                            // 'width' => '150px',
                        ],
                        'pluginEvents' => [
                            'select2:unselect' => 'function() {
                                                            
                                                            }',
                            'select2:select' => 'function() {
                                                                setTime();
                                                            }',
                        ],
                    ])->label('เลือกห้องประชุม');
                    ?>


    <div class="row">
        <div class="col-6">
                            <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่ต้องการประชุม', 'class' => ''])->label('ตั้งแต่วันที่') ?>
                            
                            <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลาเริ่มต้น') ?>
                        </div>
                        <div class="col-6">
                        <?=$form->field($model, 'data_json[period_time]')->widget(Select2::classname(), [
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
                            'select2:unselect' => 'function() {setTime();}',
                            'select2:select' => 'function() {setTime();}',
                        ],
                    ])->label('ช่วงเวลา');
                    ?>
                            <?= $form->field($model, 'time_end')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลาสิ้นสุด') ?>
                        </div>
                    </div>
<div class="row">
<div class="col-6">
    <?= $form->field($model, 'emp_number')->textInput(['class' => ''])->label('จำนวนผู้เข้าร่วม') ?>
    </div>
    <dv class="col-6">
    <?= $form->field($model, 'urgent')->widget(Select2::classname(), [
                        'data' => $model->listUrgent(),
                        'options' => ['placeholder' => 'เลือกระดับความแร้งด่วน'],
                        'pluginOptions' => [
                            'allowClear' => true,
                            'dropdownParent' => '#main-modal',
                            // 'width' => '370px',
                        ],
                        'pluginEvents' => [
                            'select2:select' => 'function(result) {}',
                            'select2:unselecting' => 'function() {}',
                        ]
                    ]) ?>
</dv>
</div>
                    <?= $form->field($model, 'title')->textInput(['class' => ''])->label('หัวข้อการประชุม') ?>
                    <?= $form->field($model, 'data_json[meeting_details]')->textArea(['rows' => 3, 'class' => ''])->label('รายละเอียดการประชุม') ?>
                    <?= $form->field($model, 'data_json[equipment]')->textInput(['class' => ''])->label('อุปกรณ์ที่ต้องการ') ?>
                    <?= $form->field($model, 'data_json[phone]')->textInput(['placeholder' => 'เบอร์โทรศัพท์ติดต่อ', 'class' => ''])->label('เบอร์ติดต่อ') ?>

                 

                    <div class="d-flex justify-content-between">
                        <div class="mt-3">
                            <?= Html::a('<i class="fa-solid fa-arrow-left"></i> ยกเลิก', ['index'], ['class' => 'btn btn-secondary shadow rounded-pill']) ?>
                        </div>
                        <div class="mt-3">
                            <?= Html::submitButton('<i class="fa-solid fa-calendar-plus"></i> ส่งคำขอจอง', ['class' => 'btn btn-primary shadow rounded-pill']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-5">

            <div class="card">
                <div class="card-body">
                    <h4 class="fw-medium mb-2">ข้อมูลห้องประชุม</h4>
                    <p class="card-text">รายละเอียดห้องประชุมที่เลือก</p>
                    <div class="rounded-md d-flex align-items-center justify-content-center mb-3">
                        <?php if($room && $room->showImg()):?>
                        <?=Html::img($room->showImg(), ['class' => 'room-img'])?>
                        <?php else:?>
                        <?= Html::img('@web/img/placeholder.svg', ['class' => 'room-img']) ?>
                        <?php endif?>
                    </div>

                    <div>
                        <h3 class="h5 fw-semibold room-title">ห้องประชุมใหญ่</h3>
                        <div class="mt-2">
                            <div class="d-flex align-items-center gap-2 small mb-2">
                                <i class="bi bi-person-add fs-5"></i>
                                <span>ความจุ: <span class="seat">0</span> คน</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="bi bi-calendar-event-fill"></i>
                                <span>เวลาทำการ: 09:00 - 17:00 น.</span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <h4 class="fw-medium mb-2">อุปกรณ์ที่มีให้บริการ</h4>
                        <div>
                            <div class="d-flex align-items-center gap-2 small mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="text-secondary" viewBox="0 0 24 24">
                                    <path d="M5 7 3 5"></path>
                                    <path d="M9 6V3"></path>
                                    <path d="m13 7 2-2"></path>
                                    <circle cx="9" cy="13" r="3"></circle>
                                    <path
                                        d="M11.83 12H20a2 2 0 0 1 2 2v4a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-4a2 2 0 0 1 2-2h2.17">
                                    </path>
                                    <path d="M16 16h2"></path>
                                </svg>
                                <span>โปรเจคเตอร์</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small mb-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="text-secondary" viewBox="0 0 24 24">
                                    <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                    <line x1="12" x2="12" y1="19" y2="22"></line>
                                </svg>
                                <span>ไมโครโฟนและเครื่องเสียง</span>
                            </div>
                            <div class="d-flex align-items-center gap-2 small">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="text-secondary" viewBox="0 0 24 24">
                                    <path d="M12 20h.01"></path>
                                    <path d="M2 8.82a15 15 0 0 1 20 0"></path>
                                    <path d="M5 12.859a10 10 0 0 1 14 0"></path>
                                    <path d="M8.5 16.429a5 5 0 0 1 7 0"></path>
                                </svg>
                                <span>Wi-Fi ความเร็วสูง</span>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div>
                        <h4 class="fw-medium mb-2">กฎระเบียบการใช้ห้องประชุม</h4>
                        <ul class="small ps-3 mb-0">
                            <li class="mb-1">ห้ามนำอาหารและเครื่องดื่มเข้าห้องประชุม</li>
                            <li class="mb-1">กรุณาจองล่วงหน้าอย่างน้อย 1 วัน</li>
                            <li class="mb-1">หากต้องการยกเลิก กรุณาแจ้งล่วงหน้าอย่างน้อย 3 ชั่วโมง</li>
                            <li>ผู้จองต้องเป็นผู้รับผิดชอบความเสียหายที่เกิดขึ้น</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <?php ActiveForm::end(); ?>
</div>

<?php
$js = <<<JS
          thaiDatepicker('#meeting-date_start')

            \$('#meeting-room_id').on('change', function() {
              \$.ajax({
                type: "get",
                url: "/me/booking-meeting/get-room",
                data: {
                  id: \$(this).val()
                },
                dataType: "json",
                success: function (res) {
                    \$('.room-title').text(res.title)
                    \$('.seat').text(res.seat)
                    $('.room-img').attr('src',res.img)
                    log(res)
                }
              });
            });

            \$('#meeting-date_end').on('change', function() {
                var dateStart = \$('#meeting-date_start').val();
                var dateEnd = \$('#meeting-date_end').val();
                listCars(dateStart,dateEnd)
            });

          \$('#meeting-date_start').on('change', function() {
                var dateStart = \$('#meeting-date_start').val();
                var dateEnd = \$('#meeting-date_end').val();
                listCars(dateStart,dateEnd)
            });

            

          \$('#meeting-form').on('beforeSubmit', function (e) {
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
                            
                        }
                    }
                });
            }
            });
            return false;
        });


        function setTime()
        {
            var period_time = \$('#meeting-data_json-period_time').val();
            var dateStart = \$('#meeting-date_start').val();
            var dateEnd = \$('#meeting-date_end').val();
            if(period_time == 'เต็มวัน'){
                \$('#meeting-time_start').val('08:00')
                \$('#meeting-time_end').val('16:00')
            }else if(period_time == 'ครึ่งวันเช้า'){
                \$('#meeting-time_start').val('08:00')
                \$('#meeting-time_end').val('12:00')
            }else if(period_time == 'ครึ่งวันบ่าย'){
                \$('#meeting-time_start').val('13:30')
                \$('#meeting-time_end').val('16:00')
            }
        }


    JS;
$this->registerJS($js, View::POS_END);
?>
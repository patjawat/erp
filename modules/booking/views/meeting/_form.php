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
use app\modules\booking\models\RoomLayout;
use app\modules\dms\models\DocumentsDetail;

$me = UserHelper::GetEmployee();
$room = Room::findOne(['name' => 'meeting_room', 'code' => $model->room_id]);
$roomLayout = RoomLayout::findOne(['name' => 'room_layout', 'code' => $model->room_layout_id]);

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
    .room-img,.room-layout-img{
        object-fit: cover;max-width: 100%;height: auto;
    }
</style>
<div class="container-xx">
    <?php // $this->render('navbar')?>

<?php $form = ActiveForm::begin([
    'id' => 'meeting-form',
    'validateOnChange' => true,
    'validateOnBlur' => true,
    'validateOnType' => false,
    'enableAjaxValidation' => true,
    'validationUrl' => $model->isNewRecord 
        ? ['/me/booking-meeting/validator']   // create
        : ['/me/booking-meeting/validator', 'id' => $model->id], // update
]); ?>



    <div class="row">
        <div class="col-7">
            <div class="card text-start">
                <div class="card-body">
                    <h4 class="fw-medium mb-2">จองห้องประชุม</h4>
                    <p class="card-text">กรอกข้อมูลเพื่อจองห้องประชุม</p>
                  

    <div class="row">
        <div class="col-6">
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


                            <?= $form->field($model, 'date_start')->textInput(['placeholder' => 'เลือกวันที่ต้องการประชุม', 'class' => ''])->label('วันที่') ?>
                            
                            <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลาเริ่มต้น') ?>
                        </div>
                        <div class="col-6">
                             <?php
                    echo $form->field($model, 'room_layout_id')->widget(Select2::classname(), [
                        'data' => $model->listRoomLayout(),
                        'options' => [
                            'placeholder' => 'เลือกรูปแบบห้องประชุม...',
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

                                                            }',
                        ],
                    ])->label('รูปแบบห้องประชุม');
                    ?>

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
                        'options' => ['placeholder' => 'เลือกระดับความเร่งด่วน'],
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
                            <?= Html::submitButton('<i class="fa-solid fa-calendar-plus"></i> บันทึก', ['class' => 'btn btn-primary shadow rounded-pill']) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-5">

            <div class="card">
                <div class="card-body">
                    <h4 class="fw-medium mb-2">ข้อมูลห้องประชุม</h4>
                    <div class="d-flex justify-content-between">
                        <p class="card-text  room-title">รายละเอียดห้องประชุมที่เลือก</p>
                        <span>ความจุ: <span class="seat"><?= $room->data_json['seat_capacity'] ?? 0?></span> คน</span>
                    </div>
                    <div class="rounded-md d-flex align-items-center justify-content-center mb-3">
                        <?php if($room && $room->showImg()):?>
                        <?=Html::img($room->showImg(), ['class' => 'room-img'])?>
                        <?php else:?>
                        <?= Html::img('@web/img/placeholder.svg', ['class' => 'room-img']) ?>
                        <?php endif?>
                    </div>

                        <div class="d-flex justify-content-between">
                        <p class="card-text ">รูปแบบการจัดห้องประชุม</p>
                        <p class="room-layout-title"><?= $roomLayout->title ?? ''?></p>

                    </div>
                    <div class="rounded-md d-flex align-items-center justify-content-center mb-3">
                        <?php if($roomLayout && $roomLayout->showImg()['isFile']):?>
                        <?=Html::img($roomLayout->showImg()['image'], ['class' => 'room-layout-img'])?>
                        <?php else:?>
                        <?= Html::img('@web/img/placeholder.svg', ['class' => 'room-layout-img']) ?>
                        <?php endif?>
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


        $('#meeting-date_start').on('change', function () {
            $('#meeting-form').yiiActiveForm('validateAttribute', 'meeting-room_id');
        });
          thaiDatepicker('#meeting-date_start,#meeting-date_end')

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
                }
            });
        });
        
        
        \$('#meeting-room_layout_id').on('change', function() {
            \$.ajax({
                type: "get",
                url: "/me/booking-meeting/get-room-layout",
                data: {
                    id: \$(this).val()
                },
                dataType: "json",
                success: function (res) {
                    $('.room-layout-img').attr('src',res.img)
                    \$('.room-layout-title').text(res.title)
                }
              });
            });


    handleFormSubmit('#meeting-form', null, async function(response) {
        await location.reload();
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
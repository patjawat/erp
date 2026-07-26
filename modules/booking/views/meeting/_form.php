<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use app\modules\booking\models\Room;
use app\modules\booking\models\RoomLayout;

$room = Room::findOne(['name' => 'meeting_room', 'code' => $model->room_id]);
$roomLayout = RoomLayout::findOne(['name' => 'room_layout', 'code' => $model->room_layout_id]);

$roomSeat = 0;
if ($room && is_array($room->data_json ?? null)) {
    $roomSeat = (int) ($room->data_json['seat_capacity'] ?? 0);
}

$roomImage = null;
if ($room && method_exists($room, 'showImg')) {
    $roomImage = $room->showImg();
}

$roomLayoutImage = null;
$roomLayoutTitle = $roomLayout->title ?? '';
if ($roomLayout && method_exists($roomLayout, 'showImg')) {
    $roomLayoutImage = $roomLayout->showImg();
}

$equipmentItemRenderer = function ($index, $label, $name, $checked, $value) {
    $safeValue = preg_replace('/[^A-Za-z0-9\-_:.]/', '-', (string) $value);
    $id = Html::getInputIdByName($name) . '-' . $safeValue;
    $encodedLabel = Html::encode($label);
    $encodedValue = Html::encode((string) $value);
    $checkedAttr = $checked ? ' checked' : '';

    return <<<HTML
<div class="col">
    <label class="equipment-item d-flex align-items-start gap-2 w-100 h-100 m-0 p-3 border rounded-3 bg-body-tertiary" for="{$id}">
        <input class="equipment-input form-check-input flex-shrink-0 mt-1" type="checkbox" name="{$name}" id="{$id}" value="{$encodedValue}"{$checkedAttr}>
        <span class="equipment-label text-body lh-sm text-break">{$encodedLabel}</span>
    </label>
</div>
HTML;
};

?>
<style>
    .meeting-form-page .meeting-preview-frame {
        aspect-ratio: 16 / 9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .meeting-form-page .room-img,
    .meeting-form-page .room-layout-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .meeting-form-page .meeting-actions {
        position: sticky;
        bottom: 0;
        background-color: var(--bs-card-bg);
        z-index: 1;
    }

    .meeting-form-page .equipment-item {
        cursor: pointer;
    }

    .meeting-form-page .equipment-item:hover {
        border-color: rgba(var(--bs-primary-rgb), 0.35) !important;
        background-color: var(--bs-primary-bg-subtle) !important;
    }

    @media (max-width: 991.98px) {
        .meeting-form-page .meeting-actions {
            position: static;
        }
    }
</style>

<div class="container-fluid px-2 px-lg-3 meeting-form-page">
    <div class="meeting-shell bg-body-tertiary rounded-4 p-2 p-lg-3">
        <?php $form = ActiveForm::begin([
            'id' => 'meeting-form',
            'validateOnChange' => true,
            'validateOnBlur' => true,
            'validateOnType' => false,
            'enableAjaxValidation' => true,
            'validationUrl' => $model->isNewRecord
                ? ['/me/booking-meeting/validator']
                : ['/me/booking-meeting/validator', 'id' => $model->id],
        ]); ?>



    <div class="row g-3 align-items-stretch">
        <div class="col-12 col-xl-7">
            <div class="card border shadow-sm rounded-4 h-100 text-start">
                <div class="card-body">
                    <h4 class="fw-semibold text-body mb-2">จองห้องประชุม</h4>
                    <p class="card-text text-body-secondary">กรอกข้อมูลเพื่อจองห้องประชุม</p>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <?php
                                echo $form->field($model, 'room_id')->widget(Select2::classname(), [
                                    'data' => $model->listRooms(),
                                    'options' => [
                                        'placeholder' => 'เลือกห้องประชุม...',
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'dropdownParent' => '#main-modal',
                                    ],
                                    'pluginEvents' => [
                                        'select2:unselect' => 'function() {}',
                                        'select2:select' => 'function() { setTime(); }',
                                    ],
                                ])->label('เลือกห้องประชุม');
                                ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?php
                                echo $form->field($model, 'room_layout_id')->widget(Select2::classname(), [
                                    'data' => $model->listRoomLayout(),
                                    'options' => [
                                        'placeholder' => 'เลือกรูปแบบห้องประชุม...',
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'dropdownParent' => '#main-modal',
                                    ],
                                    'pluginEvents' => [
                                        'select2:unselect' => 'function() {}',
                                        'select2:select' => 'function() {}',
                                    ],
                                ])->label('รูปแบบห้องประชุม');
                                ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?= $form->field($model, 'date_start')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                                    'options' => ['placeholder' => 'เลือกวันที่ต้องการประชุม'],
                                ])->label('เริ่มวันที่') ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?= $form->field($model, 'date_end')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                                    'options' => ['placeholder' => 'เลือกวันที่สิ้นสุดการประชุม'],
                                ])->label('ถึงวันที่') ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?= $form->field($model, 'data_json[period_time]')->widget(Select2::classname(), [
                                    'data' => [
                                        'เต็มวัน' => 'เต็มวัน',
                                        'ครึ่งวันเช้า' => 'ครึ่งวันเช้า',
                                        'ครึ่งวันบ่าย' => 'ครึ่งวันบ่าย',
                                    ],
                                    'options' => [
                                        'placeholder' => 'เลือกช่วงเวลา...',
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'dropdownParent' => '#main-modal',
                                    ],
                                    'pluginEvents' => [
                                        'select2:unselect' => 'function() { setTime(); }',
                                        'select2:select' => 'function() { setTime(); }',
                                    ],
                                ])->label('ช่วงเวลา') ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', [
                                    'mask' => '99:99',
                                ])->label('เวลาเริ่มต้น') ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?= $form->field($model, 'time_end')->widget('yii\widgets\MaskedInput', [
                                    'mask' => '99:99',
                                ])->label('เวลาสิ้นสุด') ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?= $form->field($model, 'emp_number')->textInput([
                                    'type' => 'number',
                                    'min' => 1,
                                    'placeholder' => 'ระบุจำนวนผู้เข้าร่วม',
                                ])->label('จำนวนผู้เข้าร่วม') ?>
                            </div>
                            <div class="col-12 col-md-6">
                                <?php
                                echo $form->field($model, 'urgent')->widget(Select2::classname(), [
                                    'data' => $model->listUrgent(),
                                    'options' => [
                                        'placeholder' => 'เลือกระดับความเร่งด่วน',
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => true,
                                        'dropdownParent' => '#main-modal',
                                    ],
                                    'pluginEvents' => [
                                        'select2:select' => 'function(result) {}',
                                        'select2:unselecting' => 'function() {}',
                                    ],
                                ])->label('ระดับความเร่งด่วน');
                                ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'title')->textInput([
                                    'placeholder' => 'ระบุหัวข้อการประชุม',
                                ])->label('หัวข้อการประชุม') ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'data_json[meeting_details]')->textArea([
                                    'rows' => 4,
                                    'placeholder' => 'ระบุรายละเอียดการประชุม',
                                ])->label('รายละเอียดการประชุม') ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'data_json[equipment]')->checkboxList(
                                    $model->equipmentItems(),
                                    [
                                        'item' => $equipmentItemRenderer,
                                        'class' => 'equipment-list row row-cols-1 row-cols-sm-2 row-cols-xl-3 g-2',
                                    ]
                                )->label('รายการอุปกรณ์') ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'data_json[phone]')->textInput([
                                    'placeholder' => 'เบอร์โทรศัพท์ติดต่อ',
                                ])->label('เบอร์ติดต่อ') ?>
                            </div>
                        </div>

                        <div class="meeting-actions pt-3 mt-3 border-top">
                            <div class="d-grid gap-2 d-sm-flex justify-content-between">
                                <div>
                                    <?= Html::a('<i class="fa-solid fa-arrow-left me-1"></i> ยกเลิก', ['index'], [
                                        'class' => 'btn btn-outline-secondary rounded-pill px-4',
                                    ]) ?>
                                </div>
                                <div>
                                    <?= Html::submitButton('<i class="fa-solid fa-calendar-plus me-1"></i> บันทึก', [
                                        'class' => 'btn btn-primary rounded-pill px-4',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-5">
                <div class="card border shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="mb-3">
                            <h4 class="fw-semibold text-body mb-2">ข้อมูลห้องประชุม</h4>
                            <p class="text-body-secondary mb-0">ดูรายละเอียดห้องและรูปแบบที่เลือกแบบเรียลไทม์</p>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <p class="card-text room-title fw-semibold text-body mb-0"><?= Html::encode($room->title ?? 'รายละเอียดห้องประชุมที่เลือก') ?></p>
                            <span class="badge rounded-pill bg-primary-subtle text-primary-emphasis fw-semibold px-3 py-2">
                                ความจุ: <span class="seat"><?= $roomSeat ?></span> คน
                            </span>
                        </div>

                        <div class="meeting-preview mb-3 border rounded-4 overflow-hidden bg-body-tertiary">
                            <div class="meeting-preview-frame">
                                <?php if ($room && $roomImage): ?>
                                    <?= Html::img($roomImage, ['class' => 'room-img', 'alt' => 'รูปห้องประชุม']) ?>
                                <?php else: ?>
                                    <?= Html::img('@web/img/placeholder.svg', ['class' => 'room-img', 'alt' => 'ไม่มีรูปห้องประชุม']) ?>
                                <?php endif ?>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <p class="card-text text-body mb-0">รูปแบบการจัดห้องประชุม</p>
                            <p class="room-layout-title text-body-secondary mb-0 fw-semibold"><?= Html::encode($roomLayoutTitle) ?></p>
                        </div>

                        <div class="meeting-preview mb-3 border rounded-4 overflow-hidden bg-body-tertiary">
                            <div class="meeting-preview-frame">
                                <?php if ($roomLayoutImage && ($roomLayoutImage['isFile'] ?? false)): ?>
                                    <?= Html::img($roomLayoutImage['image'], ['class' => 'room-layout-img', 'alt' => 'รูปแบบห้องประชุม']) ?>
                                <?php else: ?>
                                    <?= Html::img('@web/img/placeholder.svg', ['class' => 'room-layout-img', 'alt' => 'ไม่มีรูปแบบห้องประชุม']) ?>
                                <?php endif ?>
                            </div>
                        </div>

                        <hr class="my-3 border-secondary-subtle opacity-100">

                        <div class="mb-3">
                            <h4 class="fw-semibold text-body mb-2">กฎระเบียบการใช้ห้องประชุม</h4>
                            <ul class="small text-body-secondary ps-3 mb-0">
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
</div>

<?php
$js = <<<JS

        $('#meeting-date_start').on('change', function () {
            $('#meeting-form').yiiActiveForm('validateAttribute', 'meeting-room_id');
        });

        $('#meeting-room_id').on('change', function() {
          $.ajax({
            type: "get",
            url: "/me/booking-meeting/get-room",
            data: {
              id: $(this).val()
            },
            dataType: "json",
            success: function (res) {
                $('.room-title').text(res.title)
                $('.seat').text(res.seat)
                $('.room-img').attr('src', res.img)
            }
        });
    });
        
        $('#meeting-room_layout_id').on('change', function() {
            $.ajax({
                type: "get",
                url: "/me/booking-meeting/get-room-layout",
                data: {
                    id: $(this).val()
                },
                dataType: "json",
                success: function (res) {
                    $('.room-layout-img').attr('src', res.img)
                    $('.room-layout-title').text(res.title)
                }
              });
            });

    handleFormSubmit('#meeting-form', null, async function(response) {
        await location.reload();
    });

        function setTime()
        {
            var period_time = $('#meeting-data_json-period_time').val();
            var dateStart = $('#meeting-date_start').val();
            var dateEnd = $('#meeting-date_end').val();
            if(period_time == 'เต็มวัน'){
                $('#meeting-time_start').val('08:00')
                $('#meeting-time_end').val('16:00')
            }else if(period_time == 'ครึ่งวันเช้า'){
                $('#meeting-time_start').val('08:00')
                $('#meeting-time_end').val('12:00')
            }else if(period_time == 'ครึ่งวันบ่าย'){
                $('#meeting-time_start').val('13:30')
                $('#meeting-time_end').val('16:00')
            }
        }

    JS;
$this->registerJS($js, View::POS_END);
?>

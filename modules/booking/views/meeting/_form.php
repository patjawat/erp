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
<label class="equipment-item" for="{$id}">
    <input class="equipment-input" type="checkbox" name="{$name}" id="{$id}" value="{$encodedValue}"{$checkedAttr}>
    <span class="equipment-label">{$encodedLabel}</span>
</label>
HTML;
};

?>
<style>
    .meeting-form-page {
        --meeting-bg: linear-gradient(180deg, #f8fbff 0%, #f5f7fb 100%);
        --meeting-card-border: rgba(15, 23, 42, 0.08);
        --meeting-surface: #ffffff;
        --meeting-muted: #64748b;
    }

    .meeting-form-page .meeting-shell {
        background: var(--meeting-bg);
        border-radius: 24px;
        padding: 1rem;
    }

    .meeting-form-page .meeting-card {
        border: 1px solid var(--meeting-card-border);
        border-radius: 20px;
        box-shadow: 0 14px 34px rgba(15, 23, 42, 0.06);
        background: var(--meeting-surface);
    }

    .meeting-form-page .meeting-card .card-body {
        padding: 1.25rem;
    }

    .meeting-form-page .meeting-hero {
        padding: 1rem 1rem 0.25rem;
        margin-bottom: 0.5rem;
    }

    .meeting-form-page .meeting-kicker {
        color: var(--meeting-muted);
        font-size: 0.95rem;
    }

    .meeting-form-page .meeting-section-title {
        font-size: 1rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .meeting-form-page .meeting-section-title::before {
        content: "";
        width: 0.4rem;
        height: 1.2rem;
        border-radius: 999px;
        background: linear-gradient(180deg, #0d6efd 0%, #4ea1ff 100%);
        flex: 0 0 auto;
    }

    .meeting-form-page .meeting-preview {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 18px;
        overflow: hidden;
        background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
    }

    .meeting-form-page .meeting-preview-frame {
        aspect-ratio: 16 / 9;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background: #f8fafc;
    }

    .meeting-form-page .room-img,
    .meeting-form-page .room-layout-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .meeting-form-page .meeting-meta {
        color: var(--meeting-muted);
        font-size: 0.92rem;
    }

    .meeting-form-page .meeting-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.45rem 0.75rem;
        border-radius: 999px;
        background: #eef4ff;
        color: #1d4ed8;
        font-weight: 600;
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .meeting-form-page .meeting-actions {
        position: sticky;
        bottom: 0;
        padding-top: 0.75rem;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.1), #fff 35%);
        z-index: 1;
    }

    .meeting-form-page .equipment-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.6rem;
    }

    .meeting-form-page .equipment-item {
        display: flex;
        align-items: flex-start;
        gap: 0.65rem;
        margin: 0;
        padding: 0.8rem 0.9rem;
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 14px;
        background: #fbfdff;
        min-height: 58px;
        cursor: pointer;
    }

    .meeting-form-page .equipment-input {
        margin-top: 0.2rem;
        flex: 0 0 auto;
    }

    .meeting-form-page .equipment-label {
        margin: 0;
        line-height: 1.35;
        word-break: break-word;
        color: #0f172a;
    }

    .meeting-form-page .equipment-item:hover {
        border-color: rgba(13, 110, 253, 0.28);
        background: #f8fbff;
    }

    @media (min-width: 576px) {
        .meeting-form-page .equipment-list {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 1200px) {
        .meeting-form-page .equipment-list {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }

    @media (max-width: 991.98px) {
        .meeting-form-page .meeting-shell {
            padding: 0.75rem;
            border-radius: 18px;
        }

        .meeting-form-page .meeting-card .card-body {
            padding: 1rem;
        }

        .meeting-form-page .meeting-actions {
            position: static;
            background: transparent;
        }
    }
</style>

<div class="container-fluid px-2 px-lg-3 meeting-form-page">
    <div class="meeting-shell">
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

<<<<<<< HEAD


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


                            <?= $form->field($model, 'date_start')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                                'options' => ['placeholder' => 'เลือกวันที่ต้องการประชุม', 'class' => ''],
                            ])->label('เริ่มวันที่') ?>

                            <?= $form->field($model, 'date_end')->widget(\app\widgets\datepicker\DatepickerThai::class, [
                                'options' => ['placeholder' => 'เลือกวันที่สิ้นสุดการประชุม', 'class' => ''],
                            ])->label('ถึงวันที่') ?>
                            
                            <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลาเริ่มต้น') ?>
=======
        <div class="row g-4 align-items-start">
            <div class="col-12 col-xl-7">
                <div class="card meeting-card">
                    <div class="card-body">
                        <div class="meeting-hero">
                            <h4 class="fw-bold mb-2">จองห้องประชุม</h4>
                            <p class="meeting-kicker mb-0">กรอกข้อมูลให้ครบถ้วน ระบบจะช่วยจัดเวลาและข้อมูลห้องประชุมแบบเหมาะกับทุกขนาดหน้าจอ</p>
>>>>>>> c3ea37043ea67faa0b4c0acf5ca7b1a7925a24e8
                        </div>

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
                                ])->label('วันที่') ?>
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
                                        'class' => 'equipment-list',
                                    ]
                                )->label('รายการอุปกรณ์') ?>
                            </div>
                            <div class="col-12">
                                <?= $form->field($model, 'data_json[phone]')->textInput([
                                    'placeholder' => 'เบอร์โทรศัพท์ติดต่อ',
                                ])->label('เบอร์ติดต่อ') ?>
                            </div>
                        </div>

                        <div class="meeting-actions">
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
                <div class="card meeting-card h-100">
                    <div class="card-body">
                        <div class="meeting-hero">
                            <h4 class="fw-bold mb-2">ข้อมูลห้องประชุม</h4>
                            <p class="meeting-kicker mb-0">ดูรายละเอียดห้องและรูปแบบที่เลือกแบบเรียลไทม์</p>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <p class="card-text room-title mb-0"><?= Html::encode($room->title ?? 'รายละเอียดห้องประชุมที่เลือก') ?></p>
                            <span class="meeting-chip">ความจุ: <span class="seat"><?= $roomSeat ?></span> คน</span>
                        </div>

                        <div class="meeting-preview mb-3">
                            <div class="meeting-preview-frame">
                                <?php if ($room && $roomImage): ?>
                                    <?= Html::img($roomImage, ['class' => 'room-img', 'alt' => 'รูปห้องประชุม']) ?>
                                <?php else: ?>
                                    <?= Html::img('@web/img/placeholder.svg', ['class' => 'room-img', 'alt' => 'ไม่มีรูปห้องประชุม']) ?>
                                <?php endif ?>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <p class="card-text mb-0">รูปแบบการจัดห้องประชุม</p>
                            <p class="room-layout-title mb-0 fw-semibold"><?= Html::encode($roomLayoutTitle) ?></p>
                        </div>

                        <div class="meeting-preview mb-3">
                            <div class="meeting-preview-frame">
                                <?php if ($roomLayoutImage && ($roomLayoutImage['isFile'] ?? false)): ?>
                                    <?= Html::img($roomLayoutImage['image'], ['class' => 'room-layout-img', 'alt' => 'รูปแบบห้องประชุม']) ?>
                                <?php else: ?>
                                    <?= Html::img('@web/img/placeholder.svg', ['class' => 'room-layout-img', 'alt' => 'ไม่มีรูปแบบห้องประชุม']) ?>
                                <?php endif ?>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="mb-3">
                            <h4 class="fw-bold mb-2">กฎระเบียบการใช้ห้องประชุม</h4>
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

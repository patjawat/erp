<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** บันทึกภาระกิจการใช้รถยนต์ */
/** @var yii\web\View $this */
/** @var app\modules\booking\models\Vehicle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="mt-4 p-3 border rounded-3 bg-white shadow-sm">

    <div class="row g-3">
        <div class="col-sm-6">
            <label class="small text-muted d-block">หมายเลขการจอง</label>
            <span class="fw-medium"> <?= $model->vehicle->code ?></span>
        </div>
        <div class="col-sm-6">
            <div class="d-flex">
                            <?php
                                try {
                                    echo $model->car ? Html::img($model->car?->ShowImg()['image'], ['class' => 'avatar rounded border-secondary']) : '';
                                } catch (\Throwable $th) {
                                    //throw $th;
                                }
                                ?>
                            <div class="avatar-detail">
                                <div class="d-flex flex-column">
                                    <p class="mb-0"><?= $model->car?->data_json['brand']; ?></p>
                                    <p class="mb-0 text-primary"><?= $model->license_plate ?></p>
                                </div>
                            </div>
                        </div>
        </div>
        <div class="col-12">
            <hr class="my-1 opacity-5">
        </div>
        <div class="col-sm-6">
            <label class="small text-muted d-block">วัตถุประสงค์</label>
            <span class="fw-medium"><?= $model->vehicle->reason ?></span>
        </div>
        <div class="col-sm-6">
            <label class="small text-muted d-block">สถานที่ไป</label>
            <span class="fw-medium"><?= $model->vehicle->locationOrg?->title ?? '-' ?></span>
        </div>
        <div class="col-sm-6">
            <label class="small text-muted d-block">วัน/เวลา เดินทาง</label>
            <span class="fw-medium"><?php echo $model->vehicle->showDateRange() ?> (<?= $model->vehicle->viewTime()['full'] ?>)</span>
        </div>
        <div class="col-sm-6">
            <label class="small text-muted d-block">ความเร่งด่วน</label>
            <span class="text-primary fw-medium"><?= $model->vehicle->viewUrgent() ?></span>
        </div>
    </div>

</div>
<div class="vehicle-form">
    <?php $form = ActiveForm::begin([
        'id' => 'booking-form',
    ]); ?>
    <div class="mb-3 p-3">
        <div class="row">
            <div class="col-6">
                <div class="d-flex gap-3">
                      <?= $form->field($model, 'date_start')->textInput([
                    'class' => 'form-control',
                    'placeholder' => 'วันที่/เดือน/พ.ศ.',
                ])->label('วันออกเดินทาง') ?>
                    <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลาออกเดินทาง') ?>
                </div>
                <div class="d-flex gap-3">
                      <?= $form->field($model, 'date_end')->textInput([
                    'class' => 'form-control',
                    'placeholder' => 'วันที่/เดือน/พ.ศ.',
                ])->label('วันเดินทางกลับ') ?>
                    <?= $form->field($model, 'time_end')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลากลับ') ?>
                </div>

                <?= $form->field($model, 'oil_price')->textInput(['maxlength' => true, ['type' => 'number']])->label('ราคาน้ํามัน/บาท') ?>
                <?= $form->field($model, 'oil_liter')->textInput(['maxlength' => true, ['type' => 'number']])->label('ปริมาณน้ํามัน/ลิตร') ?>

            </div>
            <div class="col-6">
                <?= $form->field($model, 'mileage_start')->textInput(['maxlength' => true, ['type' => 'number']]) ?>
                <?= $form->field($model, 'mileage_end')->textInput(['maxlength' => true, ['type' => 'number']]) ?>
                <?= $form->field($model, 'distance_km')->textInput(['maxlength' => true, ['type' => 'number']]) ?>
                <?= $form->field($model, 'status')->widget(Select2::classname(), [
                    'data' => [
                        'Pass' => ' จัดสรร',
                        'Success' => 'เสร็จสิ้นภาระกิจ',
                        'Cancel' => 'ยกเลิก',
                    ],
                    'options' => ['placeholder' => 'เลือกสถานะ'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        // 'width' => '370px',
                    ],
                    'pluginEvents' => [
                        'select2:select' => 'function(result) { 
                                            }',
                        'select2:unselecting' => 'function() {}',
                    ]
                ]) ?>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-12">
                        <?php

        echo $form->field($model, 'license_plate', [
            'addon' => [
                'append' => [
                    'content' => Html::button('<i class="bi bi-ui-checks"></i>', [
                        'class' => 'btn btn-primary list-car',
                        'title' => 'Mark on map',
                        'data-toggle' => 'tooltip',
                        'data-driver_id' => $model->id,
                        'data-driver_fullname' => isset($model->data_json['req_driver_fullname']) ? $model->data_json['req_driver_fullname'] : '',
                        'data-bs-toggle' => 'offcanvas',
                        'data-bs-target' => '#offcanvasRight',
                        'aria-controls' => 'offcanvasRight'
                    ]),
                    'asButton' => true
                ]
            ]
        ])->widget(Select2::classname(), [
            'data' => $model->vehicle->ListCarItems(),
            'options' => ['placeholder' => 'เลือก...'],
            'pluginOptions' => [
                'tags' => true,  // เปิดให้เพิ่มค่าใหม่ได้
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ]
        ])->label('ทะเบียนยานพาหนะ (<code>รถยนต์ส่วนตัวกรอกทะเบียนรถ</code>) ' . $model->license_plate);
        ?>
                </div>
            <div class="col-lg-6 col-md-6 col-sm-12">
                <?= $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $model, 'label' => 'พขร.ที่ได้รับการจัดสรร', 'modal' => true, 'fieldName' => 'driver_id']) ?>
            </div>
        </div>
    </div>

    บิลล์ค่าใช้จ่ายเอกสารต่างๆ
    <?= $model->upload() ?>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true, ['type' => 'number']])->label(false) ?>

    <div class="form-group mt-3 d-flex justify-content-center gap-3">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
        <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
                class="fa-regular fa-circle-xmark"></i> ปิด</button>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<<JS
//  thaiDatepicker('#vehicledetail-date_start,#vehicledetail-date_end')
    handleFormSubmit('#booking-form', null, async function(response) {
        await location.reload();
    });

    // คำนวนระยะทาง
$('body').ready(function() {
    function calculateDistance() {
        let start = parseFloat($("#vehicledetail-mileage_start").val()) || 0;
        let end = parseFloat($("#vehicledetail-mileage_end").val()) || 0;
        let distance = end - start;

        // ป้องกันค่าติดลบ
        if (distance < 0) distance = 0;

        $("#vehicledetail-distance_km").val(distance);
    }

    // คำนวณทุกครั้งที่กรอก
    $("#vehicledetail-mileage_start, #vehicledetail-mileage_end").on("input", calculateDistance);
});


JS;
$this->registerJs($js);
?>
<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** บันทึกภาระกิจการใช้รถยนต์ */
/** @var yii\web\View $this */
/** @var app\modules\booking\models\Vehicle $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="vehicle-form">
    <?php $form = ActiveForm::begin([
        'id' => 'booking-form',
    ]); ?>
    <div>
        <p class="text-center mb-0">เลขที่ <?= $model->vehicle->code ?></p>    
    </div>
<div class="mb-3 p-3">
    <div class="row">
        <div class="col-6">
            <div class="d-flex gap-3">
                <?=$form->field($model,'date_start')->textInput()->label('วันออกเดินทาง')?>
                <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลาออกเดินทาง') ?>
            </div>
            <div class="d-flex gap-3">
                <?=$form->field($model,'date_end')->textInput()->label('วันเดินทางกลับ')?>
                <?= $form->field($model, 'time_end')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลากลับ') ?>
            </div>

            <?= $form->field($model, 'oil_price')->textInput(['maxlength' => true,['type' => 'number']])->label('ราคาน้ํามัน/บาท') ?>
            <?= $form->field($model, 'oil_liter')->textInput(['maxlength' => true,['type' => 'number']])->label('ปริมาณน้ํามัน/ลิตร') ?>
            
        </div>
        <div class="col-6">
            <?= $form->field($model, 'mileage_start')->textInput(['maxlength' => true,['type' => 'number']]) ?>
            <?= $form->field($model, 'mileage_end')->textInput(['maxlength' => true,['type' => 'number']]) ?>
            <?= $form->field($model, 'distance_km')->textInput(['maxlength' => true,['type' => 'number']]) ?>
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

        <div class="col-lg-12 col-md-12 col-sm-12">
                 <?= $this->render('@app/components/ui/input_emp', ['form' => $form, 'model' => $model, 'label' => 'พขร.ที่ได้รับการจัดสรร','modal' => true,'fieldName' => 'driver_id']) ?>
        </div>
    </div>
</div>

บิลล์ค่าใช้จ่ายเอกสารต่างๆ
<?=$model->upload()?>

<?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true,['type' => 'number']])->label(false) ?>

    <div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<<JS
 thaiDatepicker('#vehicledetail-date_start,#vehicledetail-date_end')
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
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
    <?php $form = ActiveForm::begin(); ?>
    <div>
        <h4 class="text-center">บันทึกภาระกิจการใช้รถยนต์</h4>
        <p class="text-center mb-0">เลขที่ <?= $model->vehicle->code ?></p>    
        <p class="text-center mb-0">วันที่ <?= Yii::$app->thaiDate->toThaiDate($model->date_start, true, true) ?></p>
    </div>
<div class="mb-3 p-3">
    <h6>ข้อมูลเวลา</h6>
    <div class="row">
        <div class="col-6">
            <div class="d-flex gap-3">
            <?= $form->field($model, 'time_start')->widget('yii\widgets\MaskedInput', ['mask' => '99:99'])->label('เวลาออกเดินทาง') ?>
            <?= $form->field($model, 'time_end')->textInput(['type' => 'time'])->label('เวลากลับ') ?>
            </div>
            <?= $form->field($model, 'oil_price')->textInput(['maxlength' => true,['type' => 'number']]) ?>
            <?= $form->field($model, 'oil_liter')->textInput(['maxlength' => true,['type' => 'number']]) ?>
            <?= $form->field($model, 'status')->widget(Select2::classname(), [
            'data' => [
                'Pass' => ' จัดสรร',
                'Success' => 'เสร็จสิ้นภาระกิจ',
            ],
            'options' => ['placeholder' => 'เลือกระดับความแร้งด่วน'],
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
        <div class="col-6">
            <?= $form->field($model, 'mileage_start')->textInput(['maxlength' => true,['type' => 'number']]) ?>
            <?= $form->field($model, 'mileage_end')->textInput(['maxlength' => true,['type' => 'number']]) ?>
            <?= $form->field($model, 'distance_km')->textInput(['maxlength' => true,['type' => 'number']]) ?>
        </div>
    </div>
</div>


    <div class="form-group mt-3 d-flex justify-content-center gap-3">
    <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"><i
            class="fa-regular fa-circle-xmark"></i> ปิด</button>
</div>

    <?php ActiveForm::end(); ?>

</div>

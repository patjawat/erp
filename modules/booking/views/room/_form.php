<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Room $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="room-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

    <div class="row">
<div class="col-3">
    <?= $form->field($model, 'code')->textInput(['maxlength' => true])->label('รหัสห้องประชุม') ?>
    
</div>
<div class="col-9">
    <?= $form->field($model, 'title')->textInput(['maxlength' => true])->label('ชื่อห้องประชุม') ?>
</div>
    </div>
    <div class="row">
        <div class="col-6">
        <?= $form->field($model, 'data_json[location]')->textInput()->label('สถานที่ตั้ง') ?>
        <?= $form->field($model, 'data_json[advance_booking]')->textInput()->label('จองล่วงหน้า (วัน)') ?>
    </div>
    <div class="col-6">
        <?= $form->field($model, 'data_json[owner]')->textInput()->label('ผู้รับผิดชอบ') ?>
        <?= $form->field($model, 'data_json[seat_capacity]')->textInput()->label('ความจุ (คน)') ?>
    </div>
    </div>

    <?= $form->field($model, 'description')->textArea(['maxlength' => true])->label('หมายเหตุ'); ?>

    <?= $form->field($model, 'active')->checkbox(['custom' => true,'switch' => true, 'checked' => $model->active == 1 ? true : false])->label('เปิดใช้งาน') ?>
    <?=$model->upload()?>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>
    <?= $form->field($model, 'name')->hiddenInput(['maxlength' => true])->label(false) ?>
    <div class="form-group mt-3 d-flex justify-content-center gap-3">
                <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
                <button type="button" class="btn btn-secondary  rounded-pill shadow" data-bs-dismiss="modal"> <i class="fa-regular fa-circle-xmark"></i> ปิด</button>
            </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS
      $('#form').on('beforeSubmit', function (e) {
        var form = \$(this);
        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกข้อมูล!",
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
                success: async function (response) {
                    form.yiiActiveForm('updateMessages', response, true);
                    if(response.status == 'success') {
                        closeModal()
                        // success()
                        await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                    }
                }
            });

        }
        });
        return false;
    });

JS;
$this->registerJS($js, View::POS_END);
?>
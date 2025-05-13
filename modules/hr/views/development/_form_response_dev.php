 <?php
use yii\web\View;
use yii\helpers\Html;
use kartik\widgets\ActiveForm;
?>


<div class="row mb-0 align-items-center">
    <label class="col-sm-3 col-form-label text-end fw-medium">เรื่อง:</label>
    <div class="col-sm-8"><?=$model->topic;?></div>
</div>
<div class="row mb-0 align-items-center">
    <label class="col-sm-3 col-form-label text-end fw-medium">สถานที่</label>
    <div class="col-sm-8">  <?= $model->data_json['location']?> </div>
</div>
<div class="row mb-0 align-items-center">
    <label class="col-sm-3 col-form-label text-end fw-medium">วัน/เวลา</label>
    <div class="col-sm-8"> <?= $model->showDateRange() ?></div>
</div>

<div class="row mb-0 align-items-center">
    <label class="col-sm-3 col-form-label text-end fw-medium">จำนวนผู้เข้าร่วม:</label>
    <div class="col-sm-8">20 คน</div>
</div>
<div class="row mb-0 align-items-center">
    <label class="col-sm-3 col-form-label text-end fw-medium">สถานะ:</label>
    <div class="col-sm-8">
    <?=$model->viewStatus()['view']?>
    </div>
</div>

<h6><i class="bi bi-ui-checks"></i> การตอบรับ</h6>
 <?php $form = ActiveForm::begin(['id' => 'form-development']); ?>

 <?= $form->field($model, 'response_status')->radioList([
    'Accept' => 'ยินดีเป็นวิทยากรตามวันเวลา และสถานที่ ที่กำหนดไว้',
    'Reject' => 'ไม่สามารถไปเป็นวิทยากรตามวันเวลา และสถานที่ ที่กำหนดไว้',
    'Other' => 'อื่นๆ',
])->label(false); ?>

 <?= $form->field($model, 'data_json[response_status_other]')->textArea(['rows' => 3, 'placeholder' => 'ระบุหมายเหตุ...'])->label('หมายเหตุ'); ?>
         <div class="form-group text-center mt-4">
            <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึกข้อมูล', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
            <button type="button" class="btn btn-secondary rounded-pill shadow" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i> ปิด
            </button>
        </div>

 <?php ActiveForm::end(); ?>


 <?php

$js = <<<JS

    thaiDatepicker('#development-date_start,#development-date_end,#development-vehicle_date_start,#development-vehicle_date_end');

      \$('#form-development').on('beforeSubmit', function (e) {
        var form = \$(this);

        Swal.fire({
        title: "ยืนยัน?",
        text: "บันทึกการตอบรับ!",
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
                    if(response.status == 'success') {
                        closeModal()
                        Swal.fire({
                            title: "สำเร็จ!",
                            text: "บันทึกข้อมูลเรียบร้อยแล้ว",
                            icon: "success",
                            timer: 1000,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
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
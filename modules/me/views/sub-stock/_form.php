<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
/** @var yii\widgets\ActiveForm $form */
$formWarehouse = Yii::$app->session->get('selectMainWarehouse');
$toWarehouse = Yii::$app->session->get('warehouse');

?>

<div class="stock-in-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/inventory/sub-stock/create-validator'],
    ]); ?>

<?= $form->field($model, 'data_json[note]')->textArea(['rows' => 5])->label('เหตุผล');?>
    <?php echo $form->field($model, 'name')->hiddenInput()->label(false); ?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?php echo Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']); ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS

$('#form').on('beforeSubmit', function (e) {
    e.preventDefault();

    Swal.fire({
        title: 'ยืนยัน',
        text: 'เบิกวัสดุ',
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "ใช่, ยืนยัน!",
        cancelButtonText: "ยกเลิก",
    }).then((result) => {
        if (result.isConfirmed) {
            $("#main-modal").modal("hide");
      

            var form = $('#form'); // ดึงฟอร์มมาใช้งาน
            $.ajax({
                url: form.attr('action'),
                type: 'post',
                data: form.serialize(),
                dataType: 'json',
                success: function (response) {
                    if (response.status == 'success') {

                              // แสดง SweetAlert เป็น loading พร้อม timer
                    Swal.fire({
                        title: 'กำลังดำเนินการ...',
                        text: 'กรุณารอสักครู่',
                        allowOutsideClick: false,
                        timer: 1000, // 5 วินาที
                        timerProgressBar: true,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    })
                    .then(() => {
                        Swal.fire({
                            title: 'สำเร็จ!',
                            text: 'ดำเนินการเรียบร้อยแล้ว',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload(); // รีโหลดหน้า
                        });   
                        });
                        
                    } else {
                        Swal.fire({
                            title: 'ผิดพลาด!',
                            text: response.message || 'ไม่สามารถดำเนินการได้',
                            icon: 'error'
                        });
                    }
                },
                error: function () {
                    Swal.fire({
                        title: 'ผิดพลาด!',
                        text: 'เกิดข้อผิดพลาดในการส่งข้อมูล',
                        icon: 'error'
                    });
                }
            });
        }
    });

    return false;
});

JS;
$this->registerJS($js);
?>
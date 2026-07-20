<?php

use yii\helpers\Url;
use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use iamsaint\datetimepicker\Datetimepicker;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */
/** @var yii\widgets\ActiveForm $form */

?>

<div class="stock-in-form">

    <?php $form = ActiveForm::begin([
        'id' => 'form',
        'enableAjaxValidation' => true,  // เปิดการใช้งาน AjaxValidation
        'validationUrl' => ['/inventory/stock-in/create-validator']
    ]); ?>

    <?php if ($model->name == 'order'): ?>
        <?= $this->render('_form_order', ['form' => $form, 'model' => $model]) ?>
    <?php endif; ?>

    <?php if ($model->name == 'order_item'): ?>
        <?= $this->render('_form_order_item', ['form' => $form, 'model' => $model]) ?>
    <?php endif; ?>

    <?= $form->field($model, 'data_json[po_number]')->hiddenInput()->label(false);?>
    <?= $form->field($model, 'data_json[pq_number]')->hiddenInput()->label(false);?>
    <?php //  $form->field($model, 'data_json[asset_type]')->hiddenInput()->label(false);?>
    <?= $form->field($model, 'data_json[asset_type_name]')->hiddenInput()->label(false);?>
    <?= $form->field($model, 'data_json[vendor_name]')->hiddenInput()->label(false);?>
    <?= $form->field($model, 'name')->hiddenInput()->label(false);?>
    <?= $form->field($model, 'transaction_type')->hiddenInput()->label(false);?>
    <?= $model->isNewRecord ? $form->field($model, 'category_id')->hiddenInput()->label(false) : null;?>

    <div class="form-group mt-3 d-flex justify-content-center">
        <?= Html::submitButton('<i class="bi bi-check2-circle"></i> บันทึก', ['class' => 'btn btn-primary rounded-pill shadow', 'id' => 'summit']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php
$js = <<< JS

                            \$('#form').on('beforeSubmit', function (e) {
                                var form = \$(this);

                                Swal.fire({
                                    title: "ยืนยัน?",
                                    text: "ยืนยันการบันทึกอีกครั้ง!",
                                    icon: "warning",
                                    showCancelButton: true,
                                    confirmButtonColor: "#3085d6",
                                    cancelButtonColor: "#d33",
                                    confirmButtonText: "ใช่!",
                                    cancelButtonText: "ยกเลิก",
                                        }).then((result) => {
                                        /* Read more about isConfirmed, isDenied below */
                                        if (result.isConfirmed) {
                                            if (typeof erpHideModal === "function") {
                                                erpHideModal("#main-modal");
                                            }
                                            $.ajax({
                                                    url: form.attr('action'),
                                                    type: 'post',
                                                    data: form.serialize(),
                                                    dataType: 'json',
                                                    success: async function (response) {
                                                        form.yiiActiveForm('updateMessages', response, true);
                                                        // if(response.status == 'success') {
                                                        //     closeModal()
                                                        //     success()
                                                        //     await  \$.pjax.reload({ container:response.container, history:false,replace: false,timeout: false});                               
                                                        // }

                                                        if (response.status == "success") {
                                                            Swal.fire({
                                                                title: 'สำเร็จ!',
                                                                text: 'บันทึกข้อมูลสำเร็จ!',
                                                                icon: 'success',
                                                                showConfirmButton: false,
                                                                timer: 1000 // ✅ ปิด Swal อัตโนมัติใน 1 วินาที
                                                            });

                                                            setTimeout(() => {
                                                                if (response.url) {
                                                                    window.location.href = response.url;
                                                                } else {
                                                                    location.reload(); // ✅ รีโหลดหน้าเว็บหลังจาก Swal ปิด
                                                                }
                                                            }, 1000);
                                                            } else {
                                                            Swal.fire(
                                                                'ผิดพลาด!',
                                                                'ไม่สามารถบันทึกข้อมูลได้',
                                                                'error'
                                                            );
                                                            }
                                                                                                    },
                                                    error: function () {
                                                        Swal.fire(
                                                            'ผิดพลาด!',
                                                            'เกิดข้อผิดพลาดในการบันทึกข้อมูล กรุณาลองใหม่อีกครั้ง',
                                                            'error'
                                                        );
                                                    }
                                                });
                                                return false;
                                        } else if (result.isDenied) {
                                            Swal.fire("Changes are not saved", "", "info");
                                        }
                                        });

                                        return false;
                            });
            JS;
$this->registerJS($js)
?>
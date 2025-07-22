<?php

use yii\helpers\Html;
use kartik\widgets\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\HelpdeskDetail $model */
/** @var yii\widgets\ActiveForm $form */
?>

<div class="helpdesk-detail-form">

    <?php $form = ActiveForm::begin(['id' => 'form']); ?>

    <?= $form->field($model, 'ref')->hiddenInput(['maxlength' => true])->label(false) ?>

    <?= $form->field($model, 'helpdesk_id')->hiddenInput()->label(false) ?>

    <?= $form->field($model, 'name')->hiddenInput(['value' => 'service_record'])->label(false) ?>
    <div class="row">
<div class="col-3">
   <?=$form->field($model, 'status')->widget(Select2::classname(), [
                    'data' => ['เริ่มตรวจสอบ' => 'เริ่มตรวจสอบ','สั่งอะไหล่' => 'สั่งอะไหล่'],
                    'options' => ['placeholder' => 'สถานะดำเนินการ'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ])->label(false);
                ?>
</div>
<div class="col-9">
    <?php
    echo $form->field($model, 'title', [
    'addon' => [
        'append' => [
            'content' => Html::submitButton('<i class="fa-solid fa-floppy-disk me-1"></i>บันทึก', ['class' => 'btn btn-primary']),
            'asButton' => true
        ],
    ],
])->textInput(['placeholder' => 'อธิบายวิธีการดำเนินการ...'])->label(false);

 ?>
</div>

    </div>
    <?php ActiveForm::end(); ?>

</div>

<?php
$js = <<< JS
   
   $(document).on('beforeSubmit', '#form', function (e) {
    e.preventDefault();
    const form = $(this);
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
        Swal.fire({
          title: 'กำลังบันทึก...',
          text: 'กรุณารอสักครู่',
          allowOutsideClick: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        $.ajax({
          url:  form.attr('action'),
          type: 'POST',
          data: form.serialize(),
          dataType: 'json',
          success: function (response) {
            Swal.close();
            if (response.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: 'สำเร็จ!',
                text: 'บันทึกข้อมูลเรียบร้อยแล้ว',
                timer: 1000,
                showConfirmButton: false
              }).then(() => {
                loadFormServiceRecord()
                loadTimeline()
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: response.message || 'ไม่สามารถบันทึกข้อมูลได้'
              });
            }
          },
          error: function () {
            Swal.close();
            Swal.fire({
              icon: 'error',
              title: 'เกิดข้อผิดพลาด',
              text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้'
            });
          }
        });
      }
    });
    return false;
  });

JS;
$this->registerJs($js);
?>
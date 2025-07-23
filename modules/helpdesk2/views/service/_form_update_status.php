<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\form\ActiveField;
use yii\helpers\ArrayHelper;
use kartik\widgets\ActiveForm;
use app\modules\hr\models\Employees;


/** @var yii\web\View $this */
/** @var app\modules\helpdesk\models\Repair $model */
/** @var yii\widgets\ActiveForm $form */
$emp = Employees::findOne(['user_id' => Yii::$app->user->id]);

?>

<?php $form = ActiveForm::begin([
        'id' => 'form-status',
    ]); ?>
<div class="row">
    <div class="col-6">
        <?= $form->field($model, 'repair_type')->dropDownList($model::getRepairTypeList(), ['prompt' => 'เลือกประเภทการซ่อม'])->label('ประเภทการซ่อม');?>
    </div>
    <div class="col-6">

        <?= $form->field($model, 'status')->widget(Select2::classname(), [
    'data' => $model->listStatus(),
    'options' => ['placeholder' => 'เลือกประเภทอุปกรณ์ ...'],
    'pluginOptions' => [
      'allowClear' => true,
    ],
    ])->label('สถานะงานซ่อม'); ?>

    </div>
    <div class="col-12">
        <?= $form->field($model, 'repair_result')->widget(Select2::classname(), [
          'data' => $model->getRepairResultList(),
            'options' => ['placeholder' => 'เลือกผลการซ่อม ...'],
            'pluginOptions' => [
                'allowClear' => true,
            ],
        ])->label('ผลการซ่อม'); ?>
    </div>
</div>

<div class="col-12 d-flex justify-content-end mt-4">
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-circle-check me-1"></i>
        บันทึก
    </button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<< JS
   
   $(document).on('beforeSubmit', '#form-status', function (e) {
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
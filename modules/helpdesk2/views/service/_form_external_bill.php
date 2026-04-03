<?php

use yii\helpers\Html;
use yii\web\View;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\Helpdesk $model */
?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3">
        <?php $form = ActiveForm::begin(['id' => 'external-bill-form']); ?>
        <div class="small text-muted mb-3">ปรับโหมดงานซ่อมและแนบไฟล์ใบเสร็จ/บิลค่าใช้จ่ายได้ แม้งานถูกปิดแล้ว</div>
        <?= $form->field($model, 'data_json[repair_channel]')->widget(Select2::class, [
            'data' => $model->listRepairChannel(),
            'options' => ['placeholder' => 'เลือกโหมดงานซ่อม ...'],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
        ])->label('โหมดงานซ่อม') ?>

        <?= $model->Upload('external_repair_bill') ?>
        <div class="small text-muted mt-2">บิลจะถูกแนบอัตโนมัติเมื่ออัปโหลดสำเร็จ และกดบันทึกเพื่อยืนยันโหมดงานซ่อม</div>
        <div class="mt-3 d-flex gap-2">
            <?= Html::submitButton('<i class="fa-solid fa-circle-check me-1"></i> บันทึก', ['class' => 'btn btn-sm btn-primary']) ?>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">ปิด</button>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<<JS
$(document).off('beforeSubmit.externalBillForm').on('beforeSubmit.externalBillForm', '#external-bill-form', function (e) {
  e.preventDefault();
  var form = $(this);
  $.ajax({
    type: 'POST',
    url: form.attr('action'),
    data: form.serialize(),
    dataType: 'json',
    success: function (response) {
      if (response.status === 'success') {
        Swal.fire({
          icon: 'success',
          title: 'บันทึกเรียบร้อย',
          timer: 900,
          showConfirmButton: false
        }).then(function () {
          $('#main-modal').modal('hide');
          window.location.reload();
        });
      } else {
        Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: response.message || 'ไม่สามารถบันทึกข้อมูลได้' });
      }
    },
    error: function () {
      Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้' });
    }
  });
  return false;
});
JS;
$this->registerJs($js, View::POS_END);
?>


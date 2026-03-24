<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\Helpdesk $model */
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-root-cause',
]); ?>

<div class="row g-3">
    <div class="col-12">
        <?= $form->field($model, 'data_json[root_cause]')
            ->textArea([
                'rows' => 4,
                'placeholder' => 'ระบุสาเหตุของปัญหา',
            ])
            ->label('สาเหตุของปัญหา') ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'data_json[diagnosis]')
            ->textArea([
                'rows' => 4,
                'placeholder' => 'ระบุรายละเอียดการวินิจฉัย',
            ])
            ->label('รายละเอียดการวินิจฉัย') ?>
    </div>
</div>

<div class="d-flex justify-content-end mt-3">
    <button type="submit" class="btn btn-primary">
        <i class="fa-solid fa-circle-check me-1"></i> บันทึก
    </button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$(document).off('beforeSubmit.rootCause', '#form-root-cause').on('beforeSubmit.rootCause', '#form-root-cause', function (e) {
  e.preventDefault();
  const form = $(this);
  $.ajax({
    url: form.attr('action'),
    type: 'POST',
    data: form.serialize(),
    dataType: 'json',
    success: function (response) {
      if (response.status === 'success') {
        if ($('#main-modal').length) {
          $('#main-modal').modal('hide');
        }
        if (typeof success === 'function') {
          success('บันทึกข้อมูลเรียบร้อย');
        }
        window.location.reload();
      } else {
        Swal.fire({
          icon: 'error',
          title: 'ไม่สำเร็จ',
          text: response.message || 'ไม่สามารถบันทึกข้อมูลได้',
        });
      }
    },
    error: function () {
      Swal.fire({
        icon: 'error',
        title: 'ไม่สำเร็จ',
        text: 'ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้',
      });
    },
  });
  return false;
});
JS;
$this->registerJs($js);
?>

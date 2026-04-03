<?php

use kartik\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\Helpdesk $model */
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-root-cause',
]); ?>

<div class="row g-3">
    <div class="col-12">
        <?= $form->field($model, 'data_json[repair_channel]')->widget(Select2::class, [
            'data' => $model->listRepairChannel(),
            'options' => ['placeholder' => 'เลือกโหมดงานซ่อม ...'],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
        ])->label('โหมดงานซ่อม') ?>
    </div>
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
    <div class="col-12" id="root-cause-external-bill-wrap" style="display:none;">
        <div class="border rounded-3 p-3">
            <div class="small text-muted mb-2">กรณีซ่อมภายนอก ให้แนบใบส่งซ่อม/บิลจากร้านที่ส่งซ่อม</div>
            <?= $model->Upload('external_repair_bill') ?>
        </div>
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
function toggleRootCauseExternalBill() {
  var val = $('#helpdesk-data_json-repair_channel').val() || '';
  var show = (val === 'external' || val === 'hybrid');
  $('#root-cause-external-bill-wrap').toggle(show);
}

$(document).off('change.rootCauseRepairChannel').on('change.rootCauseRepairChannel', '#helpdesk-data_json-repair_channel', toggleRootCauseExternalBill);
toggleRootCauseExternalBill();

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

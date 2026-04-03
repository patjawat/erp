<?php

use yii\web\View;
use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\Helpdesk $model */
/** @var array $technicianList */
/** @var int $currentRepairTeamEmpId */
?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-3">
        <?php $form = ActiveForm::begin(['id' => 'edit-ticket-lite-form']); ?>

        <div class="row g-3">
            <div class="col-12">
                <?= $form->field($model, 'device_type_id')->widget(Select2::classname(), [
                    'data' => $model->listDeviceType(),
                    'options' => ['placeholder' => 'เลือกประเภทอุปกรณ์ ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('ประเภทอุปกรณ์') ?>
            </div>

            <div class="col-12">
                <?= $form->field($model, 'asset_number')->widget(Select2::classname(), [
                    'data' => $model->listAsset(),
                    'options' => ['placeholder' => 'เลือกรหัสครุภัณฑ์ ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('รหัสครุภัณฑ์') ?>
            </div>

            <div class="col-12">
                <?= Html::label('ช่างผู้รับผิดชอบ', 'repair-team-emp-id', ['class' => 'form-label']) ?>
                <?= Select2::widget([
                    'name' => 'repair_team_emp_id',
                    'value' => (int) ($currentRepairTeamEmpId ?? 0) ?: null,
                    'data' => $technicianList,
                    'options' => [
                        'id' => 'repair-team-emp-id',
                        'placeholder' => 'เลือกช่างผู้รับผิดชอบ ...',
                    ],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ]) ?>
            </div>

            <div class="col-12">
                <?= $form->field($model, 'repair_group')->widget(Select2::classname(), [
                    'data' => $model->listRepairGroup(),
                    'options' => ['placeholder' => 'เลือกแผนกช่าง ...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                        'dropdownParent' => '#main-modal',
                    ],
                ])->label('แผนกช่าง') ?>
            </div>

            <div class="col-12 d-grid">
                <?= Html::submitButton('<i class="fa-solid fa-circle-check me-1"></i> บันทึกการแก้ไข', ['class' => 'btn btn-primary']) ?>
            </div>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<?php
$js = <<<JS
$(document).off('beforeSubmit.editTicketLite').on('beforeSubmit.editTicketLite', '#edit-ticket-lite-form', function (e) {
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


<?php

use kartik\widgets\ActiveForm;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\Helpdesk $model */

$initialRepairChannel = (string) ($model->data_json['repair_channel'] ?? '');
?>

<?php $form = ActiveForm::begin([
    'id' => 'form-root-cause',
    'options' => [
        'data-confirm-title' => 'ยืนยันการบันทึกสาเหตุและการวินิจฉัย?',
        'data-confirm-text' => 'ตรวจสอบข้อมูลให้ครบถ้วนก่อนยืนยันการบันทึก',
        'data-confirm-button' => 'ยืนยันบันทึก',
        'data-loading-title' => 'กำลังบันทึกข้อมูล',
        'data-loading-text' => 'กรุณารอสักครู่ ระบบกำลังบันทึกข้อมูลการตรวจสอบ',
    ],
]); ?>

<p class="text-body-secondary mb-3">
    บันทึกผลจากการตรวจสอบเพื่อให้ทีมช่างเห็นสาเหตุและดำเนินงานต่อได้ถูกต้อง
</p>

<?= $form->errorSummary($model, [
    'class' => 'alert alert-danger',
    'role' => 'alert',
]) ?>

<div class="row g-3">
    <div class="col-12">
        <?= $form->field($model, 'data_json[repair_channel]')
            ->radioList($model->listRepairChannel(), [
                'class' => 'd-grid d-sm-flex gap-2',
                'role' => 'radiogroup',
                'item' => static function ($index, $label, $name, $checked, $value) use ($model) {
                    $id = Html::getInputId($model, 'data_json[repair_channel]') . '-' . $index;
                    return Html::radio($name, $checked, [
                        'id' => $id,
                        'value' => $value,
                        'class' => 'btn-check',
                        'autocomplete' => 'off',
                    ]) . Html::label(Html::encode($label), $id, [
                        'class' => 'btn btn-outline-primary flex-fill py-2',
                    ]);
                },
            ])
            ->label('รูปแบบงานซ่อม', ['class' => 'form-label fw-semibold'])
            ->hint('เลือกวิธีดำเนินงานที่ใช้กับรายการซ่อมนี้', ['class' => 'form-text text-body-secondary']) ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'data_json[root_cause]')
            ->textArea([
                'rows' => 4,
                'placeholder' => 'ระบุสาเหตุที่ตรวจพบ เช่น อุปกรณ์เสื่อมสภาพหรือสายไฟชำรุด',
            ])
            ->label('สาเหตุของปัญหา', ['class' => 'form-label fw-semibold'])
            ->hint('ระบุสาเหตุหลักที่ทำให้เกิดปัญหา', ['class' => 'form-text text-body-secondary']) ?>
    </div>
    <div class="col-12">
        <?= $form->field($model, 'data_json[diagnosis]')
            ->textArea([
                'rows' => 4,
                'placeholder' => 'ระบุอาการที่ตรวจพบ วิธีตรวจสอบ และข้อสรุป',
            ])
            ->label('รายละเอียดการวินิจฉัย', ['class' => 'form-label fw-semibold'])
            ->hint('บันทึกลำดับการตรวจสอบหรือข้อมูลที่ทีมช่างต้องใช้ต่อ', ['class' => 'form-text text-body-secondary']) ?>
    </div>
    <div
        class="col-12 d-none"
        id="root-cause-external-bill-wrap"
        data-initial-channel="<?= Html::encode($initialRepairChannel) ?>"
        aria-hidden="true"
    >
        <div class="border rounded-3 p-3">
            <h3 class="h6 fw-semibold mb-1">เอกสารส่งซ่อมภายนอก</h3>
            <p class="small text-body-secondary mb-3">แนบใบส่งซ่อมหรือบิลจากร้านที่รับซ่อม</p>
            <?= $model->Upload('external_repair_bill') ?>
        </div>
    </div>
</div>

<div class="d-grid d-sm-flex justify-content-sm-end gap-2 border-top mt-4 pt-3">
    <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">ยกเลิก</button>
    <?= Html::submitButton(
        '<i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i> บันทึกสาเหตุและการวินิจฉัย',
        ['class' => 'btn btn-primary px-4 py-2']
    ) ?>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
function toggleRootCauseExternalBill() {
  var selectedChannel = $('#form-root-cause input[type="radio"]:checked').filter(function () {
    return (this.name || '').indexOf('[repair_channel]') !== -1;
  }).val();
  var val = selectedChannel || $('#root-cause-external-bill-wrap').attr('data-initial-channel') || '';
  var show = (val === 'external' || val === 'hybrid');
  $('#root-cause-external-bill-wrap')
    .toggleClass('d-none', !show)
    .attr('aria-hidden', show ? 'false' : 'true');
}

$(document)
  .off('change.rootCauseRepairChannel', '#form-root-cause input[type="radio"]')
  .on('change.rootCauseRepairChannel', '#form-root-cause input[type="radio"]', toggleRootCauseExternalBill);
toggleRootCauseExternalBill();

if (typeof handleFormSubmit === 'function') {
  handleFormSubmit('#form-root-cause', null, async function () {
    if (typeof window.refreshRepairView === 'function') {
      await window.refreshRepairView();
      return;
    }
    window.location.reload();
  });
}
JS;
$this->registerJs($js);
?>

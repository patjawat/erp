<?php

use yii\helpers\Html;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\helpdesk2\models\Helpdesk $model */
/** @var array $technicianList */
/** @var int $currentRepairTeamEmpId */
?>

<?php $form = ActiveForm::begin([
    'id' => 'edit-ticket-lite-form',
    'options' => [
        'data-confirm-title' => 'ยืนยันการแก้ไขใบแจ้งซ่อม?',
        'data-confirm-text' => 'ตรวจสอบข้อมูลอุปกรณ์และผู้รับผิดชอบก่อนยืนยัน',
        'data-confirm-button' => 'ยืนยันการแก้ไข',
        'data-loading-title' => 'กำลังบันทึกการแก้ไข',
        'data-loading-text' => 'กรุณารอสักครู่ ระบบกำลังปรับปรุงข้อมูลใบแจ้งซ่อม',
    ],
]); ?>

<p class="text-body-secondary mb-3">
    แก้ไขข้อมูลอุปกรณ์ ช่างผู้รับผิดชอบ และแผนกที่ดำเนินงาน
</p>

<?= $form->errorSummary($model, [
    'class' => 'alert alert-danger',
    'role' => 'alert',
]) ?>

<div class="row g-3">
    <div class="col-12">
        <?= $form->field($model, 'device_type_id')->widget(Select2::class, [
            'data' => $model->listDeviceType(),
            'options' => ['placeholder' => 'เลือกประเภทอุปกรณ์'],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
        ])
            ->label('ประเภทอุปกรณ์', ['class' => 'form-label fw-semibold'])
            ->hint('เลือกประเภทที่ตรงกับอุปกรณ์ซึ่งแจ้งซ่อม', ['class' => 'form-text text-body-secondary']) ?>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'asset_number')->widget(Select2::class, [
            'data' => $model->listAsset(),
            'options' => ['placeholder' => 'เลือกรหัสครุภัณฑ์'],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
        ])
            ->label('รหัสครุภัณฑ์', ['class' => 'form-label fw-semibold'])
            ->hint('เว้นว่างได้เมื่อรายการนี้ไม่ผูกกับครุภัณฑ์', ['class' => 'form-text text-body-secondary']) ?>
    </div>

    <div class="col-12">
        <?= Html::label('ช่างผู้รับผิดชอบ', 'repair-team-emp-id', ['class' => 'form-label fw-semibold']) ?>
        <?= Select2::widget([
            'name' => 'repair_team_emp_id',
            'value' => (int) ($currentRepairTeamEmpId ?? 0) ?: null,
            'data' => $technicianList,
            'options' => [
                'id' => 'repair-team-emp-id',
                'placeholder' => 'เลือกช่างผู้รับผิดชอบ',
                'aria-describedby' => 'repair-team-emp-hint',
            ],
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => '#main-modal',
            ],
        ]) ?>
        <div id="repair-team-emp-hint" class="form-text text-body-secondary">
            แสดงเฉพาะผู้มีสิทธิ์ในระบบซ่อมของแผนกช่างปัจจุบัน หากไม่เลือก ระบบจะนำช่างที่มอบหมายทั้งหมดออกจากงานนี้
        </div>
    </div>

    <div class="col-12">
        <?= $form->field($model, 'repair_group')
            ->radioList(['' => 'ยังไม่ระบุ'] + $model->listRepairGroup(), [
                'class' => 'd-grid d-md-flex gap-2',
                'role' => 'radiogroup',
                'item' => static function ($index, $label, $name, $checked, $value) use ($model) {
                    $id = Html::getInputId($model, 'repair_group') . '-' . $index;
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
            ->label('แผนกช่าง', ['class' => 'form-label fw-semibold'])
            ->hint('เลือกหน่วยงานที่รับผิดชอบดำเนินงานซ่อม', ['class' => 'form-text text-body-secondary']) ?>
    </div>
</div>

<div class="d-grid d-sm-flex justify-content-sm-end gap-2 border-top mt-4 pt-3">
    <?= Html::submitButton(
        '<i class="fa-solid fa-circle-check me-1" aria-hidden="true"></i> บันทึกการแก้ไข',
        ['class' => 'btn btn-primary px-4 py-2']
    ) ?>
    <button type="button" class="btn btn-outline-secondary px-4 py-2" data-bs-dismiss="modal">ยกเลิก</button>
</div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
if (typeof handleFormSubmit === 'function') {
  handleFormSubmit('#edit-ticket-lite-form', null, async function () {
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

<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/** @var yii\web\View $this */
/** @var app\modules\leave\models\LeaveSearch $model */
/** @var array $listLeaveType */
/** @var array $listLeaveStatus */
?>
<?php $form = ActiveForm::begin([
    'action' => ['/leave/approver/index'],
    'method' => 'get',
    'options' => ['class' => 'leave-approver-search', 'data-pjax' => 1],
    'fieldConfig' => ['options' => ['class' => 'form-group mb-2']],
]); ?>
<div class="row g-3">
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <label class="form-label small text-muted mb-1">จากวันที่</label>
        <?= $form->field($model, 'date_start', ['options' => ['class' => 'mb-0']])->widget(\app\widgets\datepicker\DatepickerThai::class, [
            'options' => ['class' => 'form-control', 'placeholder' => 'วว/ดด/พพพพ'],
        ])->label(false) ?>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <label class="form-label small text-muted mb-1">ถึงวันที่</label>
        <?= $form->field($model, 'date_end', ['options' => ['class' => 'mb-0']])->widget(\app\widgets\datepicker\DatepickerThai::class, [
            'options' => ['class' => 'form-control', 'placeholder' => 'วว/ดด/พพพพ'],
        ])->label(false) ?>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <label class="form-label small text-muted mb-1">สถานะการลา</label>
        <?= $form->field($model, 'status', ['options' => ['class' => 'mb-0']])->dropDownList($listLeaveStatus ?? [], [
            'prompt' => 'ทุกสถานะ',
            'class' => 'form-select',
        ])->label(false) ?>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <label class="form-label small text-muted mb-1">ประเภทการลา</label>
        <?= $form->field($model, 'leave_type_id', ['options' => ['class' => 'mb-0']])->dropDownList($listLeaveType ?? [], [
            'prompt' => 'ทุกประเภท',
            'class' => 'form-select',
        ])->label(false) ?>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <label class="form-label small text-muted mb-1">ผู้ขอลา</label>
        <?= $this->render('@app/components/ui/input_emp', [
            'form' => $form,
            'model' => $model,
            'label' => false,
            'placeholder' => 'ผู้ขอลา',
        ]) ?>
    </div>
    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
        <label class="form-label small text-muted mb-1">ค้นหาเหตุผล</label>
        <?= $form->field($model, 'q', ['options' => ['class' => 'mb-0']])->textInput([
            'class' => 'form-control',
            'placeholder' => 'คำค้น...',
        ])->label(false) ?>
    </div>
</div>
<div class="d-flex flex-wrap gap-2 pt-3">
    <?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
    <?= Html::a('<i class="bi bi-arrow-clockwise me-1"></i> ล้างตัวกรอง', ['/leave/approver/index'], ['class' => 'btn btn-outline-secondary']) ?>
</div>
<?php ActiveForm::end(); ?>

<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use app\modules\hr\models\AppraisalRound;

/** @var yii\web\View $this */
/** @var AppraisalRound $model */

$form = ActiveForm::begin(['id' => 'appraisal-round-form']);
?>
<div class="row g-3">
    <div class="col-md-5">
        <?= $form->field($model, 'fiscal_year')->input('number', [
            'readonly' => !$model->isNewRecord,
        ])->hint('ปีงบประมาณ พ.ศ. เช่น 2569') ?>
    </div>
    <div class="col-md-7">
        <?= $form->field($model, 'round_no')->dropDownList(AppraisalRound::roundLabels(), [
            'disabled' => !$model->isNewRecord,
        ]) ?>
    </div>
    <div class="col-md-4"><?= $form->field($model, 'start_date')->input('date') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'end_date')->input('date') ?></div>
    <div class="col-md-4"><?= $form->field($model, 'due_date')->input('date') ?></div>

    <div class="col-12">
        <div class="alert alert-light border small mb-0">
            <i class="bi bi-percent"></i>
            น้ำหนักองค์ประกอบของรอบนี้ — ตามแบบฟอร์มเดิมคือ ผลสัมฤทธิ์ 50 : Core 30 : Functional 20
        </div>
    </div>
    <div class="col-md-4"><?= $form->field($model, 'weight_kpi')->input('number', ['step' => '0.01', 'min' => 0, 'max' => 100]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'weight_core')->input('number', ['step' => '0.01', 'min' => 0, 'max' => 100]) ?></div>
    <div class="col-md-4"><?= $form->field($model, 'weight_functional')->input('number', ['step' => '0.01', 'min' => 0, 'max' => 100]) ?></div>

    <div class="col-12">
        <?= $form->field($model, 'note')->textarea(['rows' => 2, 'placeholder' => 'บันทึกภายในของ HR']) ?>
    </div>
</div>

<div class="d-flex justify-content-end gap-2 mt-4">
    <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
    <?= Html::submitButton('บันทึกรอบประเมิน', ['class' => 'btn btn-primary']) ?>
</div>
<?php
ActiveForm::end();

$this->registerJs(<<<JS
(function () {
    // เปลี่ยนรอบ/ปี แล้วเติมช่วงวันที่ตามปฏิทินงบประมาณให้อัตโนมัติ (เฉพาะตอนสร้างใหม่)
    var isNew = \$('#appraisalround-fiscal_year').prop('readonly') !== true;
    if (!isNew) { return; }

    \$(document).off('change.cpRoundDates').on('change.cpRoundDates',
        '#appraisalround-fiscal_year, #appraisalround-round_no', function () {
        var fy = parseInt(\$('#appraisalround-fiscal_year').val() || 0, 10);
        var no = parseInt(\$('#appraisalround-round_no').val() || 0, 10);
        if (!fy || !no) { return; }
        var ce = fy - 543;
        var dates = no === 1
            ? [(ce - 1) + '-10-01', ce + '-03-31', ce + '-04-30']
            : [ce + '-04-01', ce + '-09-30', ce + '-10-31'];
        \$('#appraisalround-start_date').val(dates[0]);
        \$('#appraisalround-end_date').val(dates[1]);
        \$('#appraisalround-due_date').val(dates[2]);
    });
})();
handleFormSubmit('#appraisal-round-form', null, function () { window.location.reload(); });
JS);

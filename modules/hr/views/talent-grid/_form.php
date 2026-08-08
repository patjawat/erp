<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\widgets\Select2;
use app\modules\hr\models\TalentGrid;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\TalentGrid $model */
/** @var array $employeeItems */

$levels = TalentGrid::levelOptions();
$boxMeta = TalentGrid::boxMeta();
$form = ActiveForm::begin(['id' => 'talent-grid-form']);
?>
<div class="row g-3">
    <div class="col-12">
        <?= $form->field($model, 'emp_id')->widget(Select2::classname(), [
            'data' => $employeeItems,
            'options' => ['placeholder' => 'พิมพ์ชื่อเพื่อค้นหาจากทะเบียนบุคลากร'],
            // ต้องผูก dropdown ไว้กับ modal ไม่งั้น bootstrap ดึง focus คืน ทำให้พิมพ์ค้นหาไม่ได้
            'pluginOptions' => [
                'allowClear' => true,
                'dropdownParent' => new JsExpression("$('#main-modal')"),
            ],
            'disabled' => !$model->isNewRecord,
        ])->hint('แสดงเฉพาะผู้ที่ยังปฏิบัติงานและยังไม่ถูกจัดวางในปีงบประมาณนี้') ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'performance')->radioList($levels, [
            'class' => 'd-flex gap-3 pt-1',
            'item' => static function ($index, $label, $name, $checked, $value) {
                return '<div class="form-check">'
                    . Html::radio($name, $checked, ['value' => $value, 'class' => 'form-check-input', 'id' => "perf-$value"])
                    . Html::label($label, "perf-$value", ['class' => 'form-check-label']) . '</div>';
            },
        ]) ?>
    </div>
    <div class="col-md-6">
        <?= $form->field($model, 'potential')->radioList($levels, [
            'class' => 'd-flex gap-3 pt-1',
            'item' => static function ($index, $label, $name, $checked, $value) {
                return '<div class="form-check">'
                    . Html::radio($name, $checked, ['value' => $value, 'class' => 'form-check-input', 'id' => "pot-$value"])
                    . Html::label($label, "pot-$value", ['class' => 'form-check-label']) . '</div>';
            },
        ]) ?>
    </div>
    <div class="col-12">
        <div class="alert alert-light border mb-0 py-2" id="talent-box-preview"></div>
    </div>
    <div class="col-md-5"><?= $form->field($model, 'assessed_at')->input('date') ?></div>
    <div class="col-md-7"><?= $form->field($model, 'fiscal_year')->input('number', ['readonly' => true]) ?></div>
    <div class="col-12"><?= $form->field($model, 'note')->textarea(['rows' => 2]) ?></div>
</div>
<div class="d-flex justify-content-between gap-2 mt-4">
    <div>
        <?php if (!$model->isNewRecord): ?>
            <?php // ห้ามใช้ data-method ของ yii.js ตรงนี้ เพราะปุ่มอยู่ในฟอร์ม yii.js จะยึด action ของฟอร์มไปแทน ?>
            <?= Html::button('<i class="bi bi-trash"></i> นำออกจากตาราง', [
                'type' => 'button',
                'class' => 'btn btn-outline-danger',
                'id' => 'talent-grid-remove',
                'data-url' => Url::to(['/hr/talent-grid/delete', 'id' => $model->id]),
            ]) ?>
        <?php endif ?>
    </div>
    <div class="d-flex gap-2">
        <?= Html::button('ยกเลิก', ['class' => 'btn btn-light', 'data-bs-dismiss' => 'modal']) ?>
        <?= Html::submitButton('บันทึกการจัดวาง', ['class' => 'btn btn-primary']) ?>
    </div>
</div>
<?php
ActiveForm::end();

$boxJson = json_encode($boxMeta, JSON_UNESCAPED_UNICODE);
$fiscalYear = (int) $model->fiscal_year;
$this->registerJs(<<<JS
(function () {
    var meta = {$boxJson};
    function refresh() {
        var perf = parseInt(\$('#talent-grid-form input[name="TalentGrid[performance]"]:checked').val() || 0, 10);
        var pot = parseInt(\$('#talent-grid-form input[name="TalentGrid[potential]"]:checked').val() || 0, 10);
        var box = document.getElementById('talent-box-preview');
        if (!box) { return; }
        if (!perf || !pot) { box.innerHTML = '<span class="text-muted">เลือกผลการปฏิบัติงานและศักยภาพ เพื่อแสดง Box ที่จะจัดวาง</span>'; return; }
        var no = (pot - 1) * 3 + perf;
        var info = meta[no];
        box.innerHTML = '<strong>Box ' + no + ' · ' + info.name + '</strong> — ' + info.criteria
            + '<div class="small text-muted mt-1">แนวทางดำเนินการ: ' + info.action + '</div>';
    }
    \$(document).off('change.talentBox').on('change.talentBox', '#talent-grid-form input[type=radio]', refresh);
    refresh();

    \$(document).off('click.talentRemove').on('click.talentRemove', '#talent-grid-remove', function () {
        var url = \$(this).data('url');
        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการนำออก',
            text: 'ต้องการนำบุคลากรรายนี้ออกจากตาราง 9 Box ปีงบประมาณ {$fiscalYear} หรือไม่',
            showCancelButton: true,
            confirmButtonText: 'ยืนยันนำออก',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#b42318'
        }).then(function (result) {
            if (!result.isConfirmed) { return; }
            var data = {};
            data[yii.getCsrfParam()] = yii.getCsrfToken();
            \$.post(url, data, null, 'json')
                .done(async function (response) {
                    await erpHideModal('#main-modal');
                    success((response && response.message) || 'นำบุคลากรออกแล้ว');
                    setTimeout(function () { window.location.reload(); }, 800);
                })
                .fail(function () { warning('นำบุคลากรออกไม่สำเร็จ'); });
        });
    });
})();
handleFormSubmit('#talent-grid-form', null, function () { window.location.reload(); });
JS);

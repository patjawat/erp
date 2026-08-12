<?php

use app\modules\roster\models\Period;
use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Period $model */
/** @var array $groups หน่วยงานจัดกลุ่มตามผังโครงสร้าง [ราก => [id => "กลุ่มงาน › งาน"]] */
/** @var array $units แบนราบ [id => label] */
/** @var array $shiftsByUnit [unitId => [['id','name','short','time','standby'], ...]] */

$thisYear = (int) date('Y') + 543;
$years = [];
for ($y = $thisYear - 1; $y <= $thisYear + 2; $y++) {
    $years[$y - 543] = $y;
}
?>
<?php $form = ActiveForm::begin(['id' => 'form', 'options' => ['data-pjax' => false]]); ?>

<?= $form->field($model, 'unit_id')->dropDownList($groups, [
    'prompt' => '— เลือกหน่วยงาน —',
    'class' => 'form-select',
    'id' => 'period-unit',
])->hint('จัดกลุ่มตามผังโครงสร้างองค์กร') ?>

<div class="row g-3">
    <div class="col-12">
        <?= $form->field($model, 'title')->textInput([
            'maxlength' => 255,
            'id' => 'period-title',
            'placeholder' => 'เช่น ตารางเวรหลัก / ตารางเวร Refer / ตารางเวร On call',
        ])->label('ชื่อแผ่น')->hint('เดือนหนึ่งมีได้หลายแผ่น ชื่อต้องไม่ซ้ำกันภายในเดือนเดียวกัน') ?>
    </div>
    <div class="col-7">
        <?= $form->field($model, 'month')->dropDownList(Period::monthNames(), ['class' => 'form-select']) ?>
    </div>
    <div class="col-5">
        <?= $form->field($model, 'year_ce')->dropDownList($years, ['class' => 'form-select'])->label('ปี (พ.ศ.)') ?>
    </div>
</div>

<div class="mb-3">
    <label class="form-label fw-semibold">แผ่นนี้ครอบเวรอะไรบ้าง</label>
    <div id="shift-scope" class="border rounded p-2 bg-body-tertiary">
        <div class="text-body-secondary small">เลือกหน่วยงานก่อน</div>
    </div>
    <div class="form-text">
        ไม่ติ๊กเลย = ครอบทุกเวรของหน่วยงาน · กริดและตัวนับจะแสดงเฉพาะเวรที่เลือก
    </div>
</div>

<div class="alert alert-warning border-0 d-none" id="unit-not-configured">
    <i class="bi bi-exclamation-triangle"></i>
    หน่วยงานนี้ยังไม่ได้ตั้งเวร — <a href="#" class="alert-link" id="go-setup" target="_blank">ไปตั้งค่าเวรก่อน</a>
    แล้วค่อยกลับมาสร้างแผ่น
</div>

<div class="text-body-secondary small">
    <i class="bi bi-info-circle"></i>
    คำขอหยุด/ขออยู่ของเจ้าหน้าที่จะแสดงบนทุกแผ่นของเดือนนั้นโดยอัตโนมัติ
</div>

<?php ActiveForm::end(); ?>

<?php
$shiftsJson = json_encode($shiftsByUnit, JSON_UNESCAPED_UNICODE);
$setupUrl = Url::to(['/roster/setting/unit']);
$js = <<<JS
window.rosterCreateInit = function () {
    var shiftsByUnit = {$shiftsJson};
    var \$unit = jQuery('#period-unit');
    var \$scope = jQuery('#shift-scope');

    function render() {
        var unitId = \$unit.val();
        var list = shiftsByUnit[unitId] || [];
        jQuery('#unit-not-configured').toggleClass('d-none', !unitId || list.length > 0);
        jQuery('#go-setup').attr('href', '{$setupUrl}?unit_id=' + (unitId || ''));

        if (!unitId) {
            \$scope.html('<div class="text-body-secondary small">เลือกหน่วยงานก่อน</div>');
            return;
        }
        if (!list.length) {
            \$scope.html('<div class="text-body-secondary small">หน่วยงานนี้ยังไม่ได้ตั้งเวร</div>');
            return;
        }
        var html = '<div class="d-flex flex-wrap gap-2">';
        list.forEach(function (s) {
            html += '<div class="form-check me-3">' +
                '<input class="form-check-input scope-shift" type="checkbox" name="unit_shift_ids[]" ' +
                'value="' + s.id + '" id="scope-' + s.id + '">' +
                '<label class="form-check-label" for="scope-' + s.id + '">' +
                '<strong>' + s.short + '</strong> ' + s.name +
                ' <span class="text-body-secondary small">' + s.time + '</span>' +
                (s.standby ? ' <i class="bi bi-telephone text-info"></i>' : '') +
                '</label></div>';
        });
        html += '</div>';
        html += '<div class="mt-2 d-flex gap-2">' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" id="scope-all">เลือกทั้งหมด</button>' +
            '<button type="button" class="btn btn-sm btn-outline-secondary" id="scope-none">ล้าง</button></div>';
        \$scope.html(html);
    }

    \$unit.off('change.rosterCreate').on('change.rosterCreate', render);
    \$scope.off('click.rosterScope').on('click.rosterScope', '#scope-all', function () {
        \$scope.find('.scope-shift').prop('checked', true);
    }).on('click.rosterScope', '#scope-none', function () {
        \$scope.find('.scope-shift').prop('checked', false);
    });
    render();
};
window.rosterCreateInit();
JS;
$this->registerJs($js);
?>

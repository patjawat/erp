<?php

use kartik\select2\Select2;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\JsExpression;

/** @var yii\web\View $this */
/** @var app\modules\roster\models\Period $period */
/** @var array $employees */

$options = [];
foreach ($employees as $employee) {
    $department = $employee['department_name'] ?: 'ไม่ระบุหน่วยงาน';
    $options[$department][(int) $employee['id']] = trim(
        ($employee['prefix'] ?? '') . $employee['fname'] . ' ' . $employee['lname']
    );
}
?>
<form id="form" data-pjax="false">
    <div class="alert alert-info border-0 d-flex gap-2" role="note">
        <i class="bi bi-info-circle flex-shrink-0"></i>
        <div>เลือกบุคลากรที่มาช่วยขึ้นเวรใน <strong><?= Html::encode($period->unitName()) ?></strong> รายชื่อจะเพิ่มเฉพาะแผ่นเวรนี้</div>
    </div>

    <label class="form-label fw-semibold" for="external-employee-picker">บุคลากรจากหน่วยงานอื่น</label>
    <?= Select2::widget([
        'name' => 'emp_id',
        'data' => $options,
        'options' => [
            'id' => 'external-employee-picker',
            'placeholder' => 'พิมพ์ชื่อหรือเลือกจากหน่วยงาน...',
            'required' => true,
            'aria-label' => 'ค้นหาและเลือกบุคลากรจากหน่วยงานอื่น',
        ],
        'pluginOptions' => [
            'allowClear' => true,
            'width' => '100%',
            // Select2 สร้างช่องค้นหาไว้ใต้ body โดยปริยาย ซึ่งถูก Bootstrap modal
            // focus trap กันไม่ให้พิมพ์ ต้องย้าย dropdown มาอยู่ใน modal เดียวกัน
            'dropdownParent' => new JsExpression("jQuery('#main-modal')"),
        ],
    ]) ?>
    <?php if (empty($employees)): ?>
        <div class="form-text text-body-secondary">ไม่มีบุคลากรต่างหน่วยที่สามารถเพิ่มได้</div>
    <?php else: ?>
        <div class="form-text">ค้นหาได้จากชื่อ และรายชื่อจัดกลุ่มตามหน่วยงานต้นสังกัด</div>
    <?php endif; ?>
</form>

<?php
$saveUrl = Url::to(['add-employee', 'id' => $period->id]);
$js = <<<JS
jQuery('#form').off('submit.rosterExternal').on('submit.rosterExternal', function (e) {
    e.preventDefault();
    var \$form = jQuery(this);
    var \$submit = jQuery('.form-submit');
    if (!\$form.find('[name="emp_id"]').val()) { return; }
    \$submit.prop('disabled', true);
    jQuery.post('{$saveUrl}', \$form.serialize()).done(function (res) {
        if (res.status === 'success') {
            if (typeof success === 'function') { success(res.message); }
            window.location.reload();
        } else if (typeof warning === 'function') {
            warning(res.message || 'เพิ่มบุคลากรไม่สำเร็จ');
        }
    }).fail(function () {
        if (typeof warning === 'function') { warning('เชื่อมต่อระบบไม่สำเร็จ กรุณาลองใหม่'); }
    }).always(function () { \$submit.prop('disabled', false); });
});
JS;
$this->registerJs($js);
?>

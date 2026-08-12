<?php

use yii\bootstrap5\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\roster\models\Item $item */
/** @var app\modules\roster\models\Period $period */
/** @var array $candidates */

$employee = $item->employee;
?>
<form id="form" data-pjax="false">
    <?= Html::hiddenInput('item_id', $item->id) ?>

    <div class="alert alert-warning border-0">
        <i class="bi bi-exclamation-triangle"></i>
        ตารางเวรนี้ผู้อำนวยการอนุมัติแล้ว การเปลี่ยนตัวจะถูกบันทึกเป็นหลักฐานพร้อมเหตุผล
    </div>

    <dl class="row mb-3 small">
        <dt class="col-4 text-body-secondary">วันที่</dt>
        <dd class="col-8"><?= Html::encode(date('d/m/', strtotime($item->work_date)) . (date('Y', strtotime($item->work_date)) + 543)) ?></dd>
        <dt class="col-4 text-body-secondary">เวร</dt>
        <dd class="col-8">
            <span class="badge rounded-pill px-3 <?= $item->shiftCellClass() ?>"><?= Html::encode($item->shiftShort()) ?></span>
            <?= Html::encode($item->shiftName()) ?>
            <?php if ($item->unitShift): ?>
                <span class="text-body-secondary"><?= Html::encode($item->unitShift->timeRangeLabel()) ?></span>
            <?php endif; ?>
        </dd>
        <dt class="col-4 text-body-secondary">คนเดิม</dt>
        <dd class="col-8 fw-semibold"><?= Html::encode($employee ? $employee->fullname : '-') ?></dd>
    </dl>

    <div class="mb-3">
        <label class="form-label fw-semibold">เปลี่ยนเป็น</label>
        <select name="to_emp_id" class="form-select" id="replace-to" required>
            <option value="">— เลือกเจ้าหน้าที่ —</option>
            <?php foreach ($candidates as $id => $name): ?>
                <option value="<?= (int) $id ?>"><?= Html::encode($name) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="alert alert-danger border-0 d-none" id="replace-warnings"></div>

    <div class="mb-2">
        <label class="form-label fw-semibold">เหตุผล <span class="text-danger">*</span></label>
        <textarea name="reason" class="form-control" rows="2" required
                  placeholder="เช่น ลาป่วยกะทันหัน"></textarea>
        <div class="form-text">จำเป็นต้องระบุ เพราะเป็นการแก้เอกสารที่อนุมัติแล้ว</div>
    </div>
</form>

<?php
$previewUrl = Url::to(['swap-preview']);
$saveUrl = Url::to(['replace']);
$itemId = (int) $item->id;
$js = <<<JS
window.rosterReplaceInit = function () {
    var \$sel = jQuery('#replace-to');
    var \$warn = jQuery('#replace-warnings');

    \$sel.off('change.replace').on('change.replace', function () {
        var to = jQuery(this).val();
        if (!to) { \$warn.addClass('d-none').empty(); return; }
        jQuery.get('{$previewUrl}', { item_id: {$itemId}, to_emp_id: to }, function (res) {
            if (res.warnings && res.warnings.length) {
                \$warn.removeClass('d-none').html(
                    '<i class="bi bi-exclamation-triangle"></i> คนนี้จะผิดกฎ: ' + res.warnings.join(' · ') +
                    '<div class="small mt-1">ยังบันทึกได้ แต่ระบบจะเก็บคำเตือนนี้ไว้เป็นหลักฐาน</div>'
                );
            } else {
                \$warn.addClass('d-none').empty();
            }
        });
    });

    jQuery('#form').off('submit.replace').on('submit.replace', function (e) {
        e.preventDefault();
        jQuery.post('{$saveUrl}', jQuery(this).serialize(), function (res) {
            if (res.status === 'success') {
                if (typeof success === 'function') { success(res.message); }
                if (typeof erpHideModal === 'function') { erpHideModal('#main-modal'); }
                window.location.reload();
            } else if (typeof warning === 'function') {
                warning(res.message);
            }
        });
    });

    // ปุ่มบันทึกใน modal footer เป็น .form-submit ที่ยิง submit ให้ฟอร์มนี้อยู่แล้ว
};
window.rosterReplaceInit();
JS;
$this->registerJs($js);
?>

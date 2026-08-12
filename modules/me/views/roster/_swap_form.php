<?php

use app\modules\roster\models\Swap;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\roster\models\Item $item */
/** @var app\modules\roster\models\Period $period */
/** @var array $colleagues */
/** @var app\modules\roster\models\Item[] $theirItems */

// เวรของเพื่อนแต่ละคน ใช้เติมดรอปดาวน์ "เวรที่จะแลกมา" เมื่อเลือกคนแล้ว
$itemsByEmp = [];
foreach ($theirItems as $their) {
    $itemsByEmp[(int) $their->emp_id][] = [
        'id' => (int) $their->id,
        'label' => date('d/m', strtotime($their->work_date)) . ' ' . $their->shiftShort(),
    ];
}
?>
<form id="form" data-pjax="false">
    <?= Html::hiddenInput('item_id', $item->id) ?>

    <dl class="row mb-3 small">
        <dt class="col-4 text-body-secondary">เวรของคุณ</dt>
        <dd class="col-8">
            <?= Html::encode(date('d/m/', strtotime($item->work_date)) . (date('Y', strtotime($item->work_date)) + 543)) ?>
            <span class="badge rounded-pill px-3 <?= $item->shiftCellClass() ?>"><?= Html::encode($item->shiftShort()) ?></span>
            <?= Html::encode($item->shiftName()) ?>
        </dd>
    </dl>

    <div class="mb-3">
        <label class="form-label fw-semibold">รูปแบบ</label>
        <div class="d-flex gap-2">
            <input type="radio" class="btn-check" name="type" id="swap-type-swap"
                   value="<?= Swap::TYPE_SWAP ?>" checked autocomplete="off">
            <label class="btn btn-outline-primary flex-fill" for="swap-type-swap">
                <i class="bi bi-arrow-left-right"></i> แลกเวรกัน
            </label>
            <input type="radio" class="btn-check" name="type" id="swap-type-give"
                   value="<?= Swap::TYPE_GIVE ?>" autocomplete="off">
            <label class="btn btn-outline-primary flex-fill" for="swap-type-give">
                <i class="bi bi-arrow-right"></i> ยกเวรให้
            </label>
        </div>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold">แลกกับ</label>
        <select name="to_emp_id" class="form-select" id="swap-to" required>
            <option value="">— เลือกเพื่อนร่วมงาน —</option>
            <?php foreach ($colleagues as $c): ?>
                <option value="<?= (int) $c['id'] ?>">
                    <?= Html::encode(trim(($c['prefix'] ?? '') . $c['fname'] . ' ' . $c['lname'])) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3" id="counter-wrap">
        <label class="form-label fw-semibold">เวรของเขาที่จะแลกมา</label>
        <select name="counter_item_id" class="form-select" id="swap-counter">
            <option value="">— เลือกคนก่อน —</option>
        </select>
        <div class="form-text">เลือกเวรที่คุณจะไปทำแทนเขา</div>
    </div>

    <div class="alert alert-warning border-0 d-none" id="swap-warnings"></div>

    <div class="mb-2">
        <label class="form-label fw-semibold">เหตุผล</label>
        <input type="text" name="reason" class="form-control" maxlength="255" placeholder="ไม่บังคับ">
    </div>

    <div class="text-body-secondary small">
        <i class="bi bi-info-circle"></i>
        เมื่อยื่นแล้ว เพื่อนต้องกดรับก่อน จากนั้นหัวหน้าหน่วยจึงอนุมัติ ตารางเวรจะเปลี่ยนเมื่ออนุมัติแล้วเท่านั้น
    </div>
</form>

<?php
$itemsJson = json_encode($itemsByEmp, JSON_UNESCAPED_UNICODE);
$previewUrl = Url::to(['/roster/period/swap-preview']);
$saveUrl = Url::to(['swap-request']);
$itemId = (int) $item->id;
$js = <<<JS
window.rosterSwapInit = function () {
    var itemsByEmp = {$itemsJson};
    var \$to = jQuery('#swap-to');
    var \$counter = jQuery('#swap-counter');
    var \$warn = jQuery('#swap-warnings');

    function isSwapMode() {
        return jQuery('input[name="type"]:checked').val() === 'swap';
    }

    function refreshCounter() {
        var empId = \$to.val();
        \$counter.empty();
        if (!empId || !itemsByEmp[empId]) {
            \$counter.append('<option value="">— ไม่มีเวรให้แลก —</option>');
            return;
        }
        \$counter.append('<option value="">— เลือกเวร —</option>');
        itemsByEmp[empId].forEach(function (it) {
            \$counter.append('<option value="' + it.id + '">' + it.label + '</option>');
        });
    }

    function refreshMode() {
        jQuery('#counter-wrap').toggleClass('d-none', !isSwapMode());
        \$counter.prop('required', isSwapMode());
    }

    function checkWarnings() {
        var to = \$to.val();
        if (!to) { \$warn.addClass('d-none').empty(); return; }
        jQuery.get('{$previewUrl}', { item_id: {$itemId}, to_emp_id: to }, function (res) {
            if (res.warnings && res.warnings.length) {
                \$warn.removeClass('d-none').html(
                    '<i class="bi bi-exclamation-triangle"></i> ถ้าเขามารับเวรนี้จะผิดกฎ: ' + res.warnings.join(' · ') +
                    '<div class="small mt-1">ยื่นได้ แต่หัวหน้าจะเห็นคำเตือนนี้ตอนพิจารณา</div>'
                );
            } else {
                \$warn.addClass('d-none').empty();
            }
        });
    }

    \$to.off('change.swap').on('change.swap', function () { refreshCounter(); checkWarnings(); });
    jQuery('input[name="type"]').off('change.swap').on('change.swap', refreshMode);
    refreshMode();

    jQuery('#form').off('submit.swap').on('submit.swap', function (e) {
        e.preventDefault();
        if (isSwapMode() && !\$counter.val()) {
            if (typeof warning === 'function') { warning('เลือกเวรของเขาที่จะแลกมาด้วย'); }
            return;
        }
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
};
window.rosterSwapInit();
JS;
$this->registerJs($js);
?>

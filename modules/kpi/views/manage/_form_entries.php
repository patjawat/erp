<?php

use app\modules\kpi\models\KpiItem;
use app\modules\kpi\services\KpiService;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var KpiItem $item */
/** @var array<int, app\modules\kpi\models\KpiEntry> $entryMap */

$fmt = static fn($n): string => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
$isNum = $item->value_type === KpiItem::TYPE_NUMERIC;
?>
<form id="kpi-entries-form" method="post" action="<?= Url::to(['record-item', 'id' => $item->id]) ?>">
    <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>
    <p class="text-body-secondary small mb-3">
        <i class="bi bi-bullseye me-1"></i>เป้า <?= Html::encode($item->target_text ?: ($item->target_value !== null ? $fmt($item->target_value) : '—')) ?>
        <span class="mx-1">·</span>น้ำหนัก <?= $item->weight > 0 ? $fmt($item->weight) . '%' : '—' ?>
        <span class="mx-1">·</span>ปีงบประมาณ ต.ค.–ก.ย.
    </p>
    <div class="row row-cols-3 row-cols-sm-4 g-2">
        <?php foreach (KpiService::FISCAL_MONTHS as $idx => $cm): ?>
            <?php $fi = $idx + 1; $e = $entryMap[$fi] ?? null; ?>
            <div class="col">
                <label class="form-label small text-body-secondary mb-1"><?= KpiService::MONTH_LABELS_TH[$cm] ?></label>
                <?php if ($isNum): ?>
                    <input type="number" step="any" name="m[<?= $fi ?>]" value="<?= $e && $e->value_num !== null ? Html::encode($fmt($e->value_num)) : '' ?>" class="form-control form-control-sm text-center">
                <?php else: ?>
                    <input type="text" name="mt[<?= $fi ?>]" value="<?= $e ? Html::encode($e->value_text) : '' ?>" class="form-control form-control-sm">
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="text-end mt-3">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
        <button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
    </div>
</form>
<?php
$this->registerJs(<<<JS
$(document).off('keydown.kpiEntries').on('keydown.kpiEntries', '#kpi-entries-form input', function(e){ if(e.key==='Enter'){ e.preventDefault(); } });
$(document).off('submit.kpiEntries').on('submit.kpiEntries', '#kpi-entries-form', function(e){
    e.preventDefault();
    var f = $(this), btn = f.find('button[type=submit]');
    btn.prop('disabled', true);
    $.ajax({ url: f.attr('action'), type: 'POST', data: f.serialize(), dataType: 'json' })
     .done(function(res){
         if (res && res.status === 'success') {
             if (typeof erpHideModal === 'function') { erpHideModal('#main-modal').then(function(){ location.reload(); }); }
             else { location.reload(); }
         } else {
             btn.prop('disabled', false);
             Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: (res && res.message) || 'บันทึกไม่สำเร็จ' });
         }
     })
     .fail(function(){ btn.prop('disabled', false); Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: 'บันทึกไม่สำเร็จ' }); });
});
JS);
?>

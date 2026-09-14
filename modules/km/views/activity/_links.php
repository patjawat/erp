<?php

use app\modules\km\models\KmActivityLink;
use app\modules\km\services\KmLinkService;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\km\models\KmActivity $activity */
/** @var bool $canManage */
?>
<div class="card border shadow-sm">
    <div class="card-body">
        <h2 class="h6 fw-semibold"><i class="bi bi-link-45deg me-1"></i> หลักฐานที่ผูก <span class="text-body-secondary fw-normal">(<?= count($activity->links) ?>)</span></h2>

        <?php if (!$activity->links): ?>
            <div class="text-body-secondary small mb-2">ยังไม่ได้ผูกหลักฐาน</div>
        <?php else: ?>
            <ul class="list-group list-group-flush mb-2">
                <?php foreach ($activity->links as $lk): ?>
                    <?php $out = KmLinkService::outUrl($lk->item_type, $lk->ref_id); ?>
                    <li class="list-group-item px-0 d-flex align-items-start gap-2">
                        <i class="bi <?= KmLinkService::icon($lk->item_type) ?> mt-1 text-body-secondary"></i>
                        <div class="flex-grow-1 min-w-0">
                            <div class="small text-body-secondary"><?= Html::encode($lk->typeLabel()) ?></div>
                            <div>
                                <?php if ($out): ?>
                                    <?= Html::a(Html::encode($lk->ref_label ?: ('#' . $lk->ref_id)), $out, ['target' => '_blank', 'rel' => 'noopener']) ?>
                                <?php else: ?>
                                    <?= Html::encode($lk->ref_label ?: ('#' . $lk->ref_id)) ?>
                                <?php endif; ?>
                            </div>
                            <?php if ($lk->note): ?><div class="small text-body-secondary"><?= Html::encode($lk->note) ?></div><?php endif; ?>
                        </div>
                        <?php if ($canManage): ?>
                            <?= Html::beginForm(['delete-link', 'id' => $lk->id], 'post', ['class' => 'd-inline', 'onsubmit' => "return confirm('ยกเลิกการผูกรายการนี้?');"]) ?>
                                <?= Html::submitButton('<i class="bi bi-x-lg"></i>', ['class' => 'btn btn-sm btn-link text-danger p-0', 'title' => 'ยกเลิกการผูก']) ?>
                            <?= Html::endForm() ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <?php if ($canManage): ?>
            <hr class="my-2">
            <?= Html::beginForm(['add-link', 'id' => $activity->id], 'post', ['id' => 'km-link-form']) ?>
                <div class="mb-2">
                    <label class="form-label small mb-1">ประเภทหลักฐาน</label>
                    <?php
                    $typeOpts = [];
                    foreach (KmLinkService::enabledTypes() as $t) {
                        $typeOpts[$t] = KmActivityLink::typeLabels()[$t];
                    }
                    ?>
                    <?= Html::dropDownList('item_type', null, $typeOpts, ['class' => 'form-select form-select-sm', 'id' => 'km-link-type']) ?>
                </div>
                <div class="mb-2">
                    <label class="form-label small mb-1">ค้นหารายการ</label>
                    <input type="text" class="form-control form-control-sm" id="km-link-search" placeholder="พิมพ์เพื่อค้นหา...">
                </div>
                <div class="mb-2">
                    <?= Html::dropDownList('ref_id', null, [], ['class' => 'form-select form-select-sm', 'id' => 'km-link-target', 'size' => 6]) ?>
                </div>
                <div class="mb-2">
                    <?= Html::textInput('note', '', ['class' => 'form-control form-control-sm', 'placeholder' => 'หมายเหตุ (ถ้ามี)', 'maxlength' => 255]) ?>
                </div>
                <?= Html::submitButton('<i class="bi bi-link-45deg me-1"></i> ผูกหลักฐาน', ['class' => 'btn btn-sm btn-primary w-100', 'id' => 'km-link-submit']) ?>
            <?= Html::endForm() ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($canManage): ?>
<?php
$optUrl = Url::to(['link-options']);
$js = <<<JS
(function(){
    var typeEl = document.getElementById('km-link-type');
    var searchEl = document.getElementById('km-link-search');
    var targetEl = document.getElementById('km-link-target');
    if (!typeEl || !targetEl) return;
    var timer = null;
    function load(){
        var url = '$optUrl' + (('$optUrl'.indexOf('?')>=0)?'&':'?') + 'type=' + encodeURIComponent(typeEl.value) + '&q=' + encodeURIComponent(searchEl.value || '');
        targetEl.innerHTML = '<option>กำลังโหลด...</option>';
        fetch(url, {headers: {'X-Requested-With':'XMLHttpRequest'}})
            .then(function(r){ return r.json(); })
            .then(function(d){
                targetEl.innerHTML = '';
                var items = (d && d.items) || [];
                if (!items.length){ targetEl.innerHTML = '<option value="">— ไม่พบรายการ —</option>'; return; }
                items.forEach(function(it){
                    var o = document.createElement('option');
                    o.value = it.id; o.textContent = it.label;
                    targetEl.appendChild(o);
                });
            })
            .catch(function(){ targetEl.innerHTML = '<option value="">— โหลดไม่สำเร็จ —</option>'; });
    }
    typeEl.addEventListener('change', function(){ searchEl.value=''; load(); });
    searchEl.addEventListener('input', function(){ clearTimeout(timer); timer = setTimeout(load, 300); });
    load();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>
<?php endif; ?>

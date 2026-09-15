<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\km\models\KmCategory[] $categories */
/** @var app\modules\km\models\KmCategory[] $topCategories */

$this->title = 'หมวดหมู่กิจกรรม KM';

// จัดกลุ่มตามหมวดแม่ (parent_id null -> key 0 = หมวดหลัก)
$byParent = [];
foreach ($categories as $c) {
    $byParent[(int) $c->parent_id][] = $c;
}
$tops = $byParent[0] ?? [];

$parentOptions = ArrayHelper::map($topCategories, 'id', 'name');

/** แถวหนึ่งในตาราง — $child=true จะเยื้องเข้าไป */
$row = function ($c, bool $child) use ($byParent) {
    $hasChildren = !empty($byParent[(int) $c->id]);
    $attrs = 'data-cat-row data-name="' . Html::encode(mb_strtolower($c->name)) . '" ';
    $attrs .= $child ? ('data-child-of="' . (int) $c->parent_id . '"') : ('data-top-id="' . (int) $c->id . '"');
    ob_start();
    ?>
    <tr <?= $attrs ?>>
        <td class="text-body-secondary"><?= (int) $c->sort ?></td>
        <td>
            <?php if ($child): ?>
                <span class="text-body-secondary ms-3 me-1">└</span>
            <?php elseif ($hasChildren): ?>
                <button type="button" class="btn btn-sm btn-link p-0 me-1 text-body-secondary km-cat-toggle" data-toggle-id="<?= (int) $c->id ?>" aria-label="ย่อ/ขยายหมวดย่อย"><i class="bi bi-chevron-down"></i></button>
            <?php else: ?>
                <span class="d-inline-block me-1" style="width:1rem"></span>
            <?php endif; ?>
            <?php if ($c->icon): ?><i class="bi <?= Html::encode($c->icon) ?> me-1" <?= $c->color ? 'style="color:' . Html::encode($c->color) . '"' : '' ?>></i><?php endif; ?>
            <?= Html::encode($c->name) ?>
            <?php if (!$child && $hasChildren): ?><span class="badge text-bg-light border ms-1"><?= count($byParent[(int) $c->id]) ?> ย่อย</span><?php endif; ?>
        </td>
        <td><?= $c->is_active ? '<span class="badge text-bg-success">เปิด</span>' : '<span class="badge text-bg-secondary">ปิด</span>' ?></td>
        <td class="text-end">
            <?php if (!$child): ?>
                <button type="button" class="btn btn-sm btn-outline-primary km-cat-addsub"
                    data-parent-id="<?= $c->id ?>" data-parent-name="<?= Html::encode($c->name) ?>"
                    title="เพิ่มหมวดย่อยใน <?= Html::encode($c->name) ?>">
                    <i class="bi bi-plus-lg"></i><span class="d-none d-xl-inline"> ย่อย</span>
                </button>
            <?php endif; ?>
            <button type="button" class="btn btn-sm btn-outline-secondary km-cat-edit"
                data-id="<?= $c->id ?>" data-name="<?= Html::encode($c->name) ?>"
                data-icon="<?= Html::encode($c->icon) ?>" data-color="<?= Html::encode($c->color) ?>"
                data-sort="<?= (int) $c->sort ?>" data-active="<?= (int) $c->is_active ?>"
                data-parent="<?= (int) $c->parent_id ?>" data-haschildren="<?= $hasChildren ? '1' : '0' ?>">
                <i class="bi bi-pencil"></i>
            </button>
            <?= Html::beginForm(['delete', 'id' => $c->id], 'post', ['class' => 'd-inline', 'onsubmit' => "return confirm('ลบหมวดนี้? หมวดย่อย/กิจกรรมที่อยู่ในหมวดจะกลายเป็นไม่มีหมวด');"]) ?>
                <?= Html::submitButton('<i class="bi bi-trash"></i>', ['class' => 'btn btn-sm btn-outline-danger']) ?>
            <?= Html::endForm() ?>
        </td>
    </tr>
    <?php
    return ob_get_clean();
};
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>คลัง KM<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="mb-3"><?= $this->render('@app/modules/km/menu', ['active' => 'category']) ?></div>

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $tone): ?>
        <?php if (Yii::$app->session->hasFlash($key)): ?>
            <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash($key)) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <h2 class="h6 fw-semibold mb-0">รายการหมวด <span class="text-body-secondary fw-normal small">(หมวดหลัก → หมวดย่อย)</span></h2>
                        <div class="input-group input-group-sm" style="width:auto">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="km-cat-search" placeholder="ค้นหาหมวด..." style="min-width:180px" autocomplete="off">
                        </div>
                    </div>
                    <?php if (!$categories): ?>
                        <div class="text-body-secondary small">ยังไม่มีหมวด — เพิ่มได้จากแบบฟอร์มด้านขวา</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" id="km-cat-table">
                                <thead><tr>
                                    <th style="width:60px">ลำดับ</th><th>ชื่อหมวด</th><th style="width:90px">สถานะ</th><th style="width:170px"></th>
                                </tr></thead>
                                <tbody>
                                <?php foreach ($tops as $top): ?>
                                    <?= $row($top, false) ?>
                                    <?php foreach ($byParent[(int) $top->id] ?? [] as $child): ?>
                                        <?= $row($child, true) ?>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="text-body-secondary small mt-2" id="km-cat-empty" hidden><i class="bi bi-inbox me-1"></i>ไม่พบหมวดที่ตรงกับคำค้น</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border shadow-sm">
                <div class="card-body">
                    <h2 class="h6 fw-semibold mb-3" id="km-cat-form-title">เพิ่มหมวดใหม่</h2>
                    <?= Html::beginForm(['save'], 'post', ['id' => 'km-cat-form']) ?>
                        <?= Html::hiddenInput('id', '', ['id' => 'km-cat-id']) ?>
                        <div class="mb-2">
                            <label class="form-label">ชื่อหมวด <span class="text-danger">*</span></label>
                            <?= Html::textInput('name', '', ['class' => 'form-control', 'id' => 'km-cat-name', 'required' => true, 'maxlength' => 255]) ?>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">หมวดแม่ <span class="text-body-secondary small">(ว่าง = เป็นหมวดหลัก)</span></label>
                            <?= Html::dropDownList('parent_id', '', $parentOptions, ['class' => 'form-select', 'id' => 'km-cat-parent', 'prompt' => '— ไม่มี (เป็นหมวดหลัก) —']) ?>
                            <div class="form-text" id="km-cat-parent-hint" hidden>หมวดนี้มีหมวดย่อยอยู่ จึงตั้งเป็นหมวดย่อยของหมวดอื่นไม่ได้</div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">ไอคอน <span class="text-body-secondary small">(เช่น bi-people)</span></label>
                            <?= Html::textInput('icon', '', ['class' => 'form-control', 'id' => 'km-cat-icon', 'placeholder' => 'bi-...']) ?>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label">สี</label>
                                <?= Html::textInput('color', '', ['class' => 'form-control', 'id' => 'km-cat-color', 'placeholder' => '#0d6efd']) ?>
                            </div>
                            <div class="col-6">
                                <label class="form-label">ลำดับ</label>
                                <?= Html::input('number', 'sort', '0', ['class' => 'form-control', 'id' => 'km-cat-sort']) ?>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <?= Html::checkbox('is_active', true, ['class' => 'form-check-input', 'id' => 'km-cat-active']) ?>
                            <label class="form-check-label" for="km-cat-active">เปิดใช้งาน</label>
                        </div>
                        <div class="d-flex gap-2">
                            <?= Html::submitButton('<i class="bi bi-save me-1"></i> บันทึก', ['class' => 'btn btn-primary btn-sm']) ?>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="km-cat-reset">ล้างฟอร์ม</button>
                        </div>
                    <?= Html::endForm() ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$js = <<<'JS'
(function(){
    var f = document.getElementById('km-cat-form');
    if (!f) return;
    function setVal(id, v){ var el = document.getElementById(id); if (el) el.value = v; }
    var parentSel = document.getElementById('km-cat-parent');
    var parentHint = document.getElementById('km-cat-parent-hint');

    // แก้ไข: เติมค่าเข้าฟอร์ม
    document.querySelectorAll('.km-cat-edit').forEach(function(btn){
        btn.addEventListener('click', function(){
            var d = btn.dataset;
            setVal('km-cat-id', d.id); setVal('km-cat-name', d.name);
            setVal('km-cat-icon', d.icon || ''); setVal('km-cat-color', d.color || '');
            setVal('km-cat-sort', d.sort || '0');
            setVal('km-cat-parent', d.parent && d.parent !== '0' ? d.parent : '');
            document.getElementById('km-cat-active').checked = (d.active === '1');
            var lockParent = (d.haschildren === '1');
            if (parentSel) { parentSel.disabled = lockParent; if (lockParent) parentSel.value = ''; }
            if (parentHint) parentHint.hidden = !lockParent;
            document.getElementById('km-cat-form-title').textContent = 'แก้ไขหมวด: ' + d.name;
            f.scrollIntoView({behavior:'smooth', block:'center'});
        });
    });

    // เพิ่มหมวดย่อย: เปิดฟอร์มใหม่ พร้อมเลือกหมวดแม่ให้
    document.querySelectorAll('.km-cat-addsub').forEach(function(btn){
        btn.addEventListener('click', function(){
            f.reset(); setVal('km-cat-id','');
            document.getElementById('km-cat-active').checked = true;
            if (parentSel) { parentSel.disabled = false; parentSel.value = btn.dataset.parentId; }
            if (parentHint) parentHint.hidden = true;
            document.getElementById('km-cat-form-title').textContent = 'เพิ่มหมวดย่อยใน: ' + btn.dataset.parentName;
            f.scrollIntoView({behavior:'smooth', block:'center'});
            var nameEl = document.getElementById('km-cat-name'); if (nameEl) nameEl.focus();
        });
    });

    var reset = document.getElementById('km-cat-reset');
    if (reset) reset.addEventListener('click', function(){
        f.reset(); setVal('km-cat-id','');
        if (parentSel) parentSel.disabled = false;
        if (parentHint) parentHint.hidden = true;
        document.getElementById('km-cat-active').checked = true;
        document.getElementById('km-cat-form-title').textContent = 'เพิ่มหมวดใหม่';
    });

    // พับ/ขยายหมวดย่อยของหมวดหลัก
    var collapsed = {};
    function childrenOf(id){ return document.querySelectorAll('[data-child-of="' + id + '"]'); }
    document.querySelectorAll('.km-cat-toggle').forEach(function(btn){
        btn.addEventListener('click', function(){
            var id = btn.dataset.toggleId;
            collapsed[id] = !collapsed[id];
            childrenOf(id).forEach(function(tr){ tr.hidden = collapsed[id]; });
            var icon = btn.querySelector('i');
            if (icon) icon.className = collapsed[id] ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
        });
    });

    // ค้นหา: กรองแถวตามชื่อ (เจอลูก→โชว์แม่ด้วย, เจอแม่→โชว์ลูกด้วย)
    var search = document.getElementById('km-cat-search');
    var emptyMsg = document.getElementById('km-cat-empty');
    if (search) search.addEventListener('input', function(){
        var q = (search.value || '').trim().toLowerCase();
        var rows = document.querySelectorAll('[data-cat-row]');
        if (q === ''){
            rows.forEach(function(tr){
                var of = tr.getAttribute('data-child-of');
                tr.hidden = of ? !!collapsed[of] : false; // คืนสถานะพับ
            });
            if (emptyMsg) emptyMsg.hidden = true;
            return;
        }
        var showTop = {};
        rows.forEach(function(tr){
            var match = (tr.getAttribute('data-name') || '').indexOf(q) !== -1;
            if (match){
                var of = tr.getAttribute('data-child-of');
                if (of) showTop[of] = true;                 // ลูกเจอ → จำว่าแม่ต้องโชว์
                var top = tr.getAttribute('data-top-id');
                if (top) showTop[top] = true;               // แม่เจอ → โชว์แม่ (ลูกจะถูกโชว์ผ่านลูป)
            }
        });
        var any = false;
        rows.forEach(function(tr){
            var name = tr.getAttribute('data-name') || '';
            var of = tr.getAttribute('data-child-of');
            var top = tr.getAttribute('data-top-id');
            var match = name.indexOf(q) !== -1;
            var show = match || (of && showTop[of]) || (top && showTop[top]);
            tr.hidden = !show;
            if (show) any = true;
        });
        if (emptyMsg) emptyMsg.hidden = any;
    });
})();
JS;
$this->registerJs($js, \yii\web\View::POS_END);

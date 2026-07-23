<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\widgets\LinkPager;

$this->registerCss('.dp-bulk-bar{position:sticky;top:.5rem;z-index:3;}');

/** @var yii\web\View $this */
/** @var string $level */
/** @var array $levels */
/** @var string|null $q */
/** @var array $rows */
/** @var yii\data\Pagination $pages */
/** @var int $count */
/** @var app\modules\am\models\DepreciationProfile[] $profiles */

$this->title = 'ผูกเกณฑ์ค่าเสื่อมกับลำดับชั้นทรัพย์สิน';
$this->params['breadcrumbs'][] = ['label' => 'ค่าเสื่อมราคา', 'url' => ['/am/asset-depreciation/overview']];
$this->params['breadcrumbs'][] = $this->title;

$this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
    <span class="text-primary"><i data-lucide="link"></i></span>
    <?= Html::encode($this->title) ?>
</h4>
<?php $this->endBlock();

$this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'depreciation']) ?>
<?php $this->endBlock(); ?>
<div class="container-fluid py-3">

    <?php foreach (['success' => 'success', 'error' => 'danger'] as $flash => $cls): ?>
        <?php if (Yii::$app->session->hasFlash($flash)): ?>
            <div class="alert alert-<?= $cls ?>"><?= Yii::$app->session->getFlash($flash) ?></div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="alert alert-info small">
        <i data-lucide="info"></i> ทรัพย์สินจะใช้เกณฑ์จากระดับที่เฉพาะเจาะจงที่สุด: <b>รายชิ้น → รายการ → หมวด → ประเภทหลัก</b> — ตั้งที่ระดับกลุ่มครั้งเดียว ครอบคลุมทุกชิ้นใต้กลุ่มนั้นอัตโนมัติ (ตั้งค่ารายชิ้นได้ที่เมนู "เปลี่ยนเกณฑ์ทรัพย์สิน")<br>
        <i data-lucide="check-check"></i> <b>ติ๊กเลือกหลายรายการ</b> แล้วเลือกเกณฑ์ที่แถบด้านบน กด "กำหนดให้ที่เลือก" เพื่อผูกทีเดียวทั้งกลุ่ม
    </div>

    <ul class="nav nav-pills mb-3">
        <?php foreach ($levels as $lv => $label): ?>
            <li class="nav-item">
                <?= Html::a($label, ['index', 'level' => $lv], ['class' => 'nav-link ' . ($level === $lv ? 'active' : '')]) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="card mb-3"><div class="card-body">
        <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <?= Html::hiddenInput('level', $level) ?>
            <?php if ($level === 'asset_category' && !empty($typeOptions)): ?>
            <div class="col-md-3">
                <label class="form-label">ประเภทหลัก</label>
                <select name="type" class="form-select" onchange="this.form.submit()">
                    <option value="">— ทุกประเภท —</option>
                    <?php foreach ($typeOptions as $tcode => $ttitle): ?>
                        <option value="<?= Html::encode($tcode) ?>" <?= (string) ($type ?? '') === (string) $tcode ? 'selected' : '' ?>><?= Html::encode($ttitle) ?> (<?= Html::encode($tcode) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="col-md-4">
                <label class="form-label">ค้นหา (ชื่อ/รหัส)</label>
                <input type="text" name="q" class="form-control" value="<?= Html::encode($q) ?>">
            </div>
            <div class="col-md-2"><?= Html::submitButton('<i data-lucide="search"></i> ค้นหา', ['class' => 'btn btn-outline-primary']) ?></div>
            <div class="col text-end text-muted small">พบ <?= $count ?> รายการ (<?= $levels[$level] ?>)</div>
        <?= Html::endForm() ?>
    </div></div>

    <?php Pjax::begin(['id' => 'dp-binding-container', 'enablePushState' => false]); ?>
    <?php $showParentType = ($level === 'asset_category'); ?>
    <?= Html::beginForm(['bulk-set'], 'post', [
        'id' => 'dp-bulk-form',
        'data-confirm-title' => 'ยืนยันกำหนดเกณฑ์กับรายการที่เลือก?',
        'data-confirm-text' => 'ระบบจะตั้งเกณฑ์ให้ทุกรายการที่ติ๊กเลือกไว้ (มีผลกับทรัพย์สินที่ขึ้นทะเบียนใหม่หลังจากนี้)',
    ]) ?>
    <?= Html::hiddenInput('level', $level) ?>
    <?= Html::hiddenInput('q', $q) ?>
    <?= Html::hiddenInput('type', $type ?? '') ?>

    <?php // แถบกำหนดเกณฑ์เป็นกลุ่ม — sticky ด้านบนของตาราง ?>
    <div class="card mb-2 dp-bulk-bar">
        <div class="card-body py-2 d-flex flex-wrap align-items-center gap-2">
            <span class="small text-muted">เลือกแล้ว <b id="dp-sel-count">0</b> จาก <?= (int) $count ?> รายการ</span>
            <div class="ms-auto d-flex flex-wrap align-items-center gap-2">
                <label class="small text-muted mb-0" for="dp-bulk-profile">กำหนดเป็น</label>
                <select name="profile_id" id="dp-bulk-profile" class="form-select form-select-sm" style="min-width:15rem;">
                    <option value="">— เลือกเกณฑ์ —</option>
                    <?php foreach ($profiles as $p): ?>
                        <option value="<?= $p->id ?>"><?= Html::encode($p->code . ' — ' . $p->name) ?></option>
                    <?php endforeach; ?>
                    <option value="0">— ล้างการผูก (ไม่ผูกเกณฑ์) —</option>
                </select>
                <button type="submit" id="dp-apply-btn" class="btn btn-sm btn-primary" disabled>
                    <i data-lucide="check-check"></i> กำหนดให้ที่เลือก
                </button>
            </div>
        </div>
    </div>

    <div class="card"><div class="card-body">
        <?php if (empty($rows)): ?>
            <div class="text-center text-muted py-4">ไม่พบข้อมูล</div>
        <?php else: ?>
            <?php // ---------- Mobile ---------- ?>
            <ul class="list-group list-group-flush d-lg-none mb-3" role="list" aria-label="รายการผูกเกณฑ์ค่าเสื่อม">
                <?php foreach ($rows as $r): ?>
                    <li class="list-group-item px-0">
                        <label class="d-flex gap-2 align-items-start mb-0" style="cursor:pointer;">
                            <input type="checkbox" class="form-check-input dp-check mt-1" name="ids[]" value="<?= (int) $r['id'] ?>"
                                aria-label="เลือก <?= Html::encode($r['title']) ?>">
                            <span class="flex-grow-1 min-w-0">
                                <span class="d-flex justify-content-between gap-2">
                                    <span class="min-w-0">
                                        <span class="fw-semibold text-dark d-block"><?= Html::encode($r['title']) ?></span>
                                        <span class="small text-muted"><?= Html::encode($r['code']) ?></span>
                                        <?php if (!empty($r['parent_type_name'])): ?>
                                            <span class="small text-muted d-block mt-1">ประเภทหลัก: <?= Html::encode($r['parent_type_name']) ?></span>
                                        <?php elseif (!empty($r['parent_type_code'])): ?>
                                            <span class="small text-muted d-block mt-1">ประเภทหลัก: <?= Html::encode($r['parent_type_code']) ?></span>
                                        <?php endif; ?>
                                    </span>
                                    <span class="badge <?= $r['bound_profile_name'] ? 'bg-success' : 'bg-light text-dark border' ?> align-self-start">
                                        <?= $r['bound_profile_name'] ? Html::encode($r['bound_profile_name']) : 'ยังไม่ผูก' ?>
                                    </span>
                                </span>
                            </span>
                        </label>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php // ---------- Desktop ---------- ?>
            <div class="table-responsive d-none d-lg-block">
                <table class="table table-sm table-hover align-middle mb-0">
                    <caption class="visually-hidden">รายการผูกเกณฑ์ค่าเสื่อมตามลำดับชั้นทรัพย์สิน</caption>
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width:2.5rem;">
                                <input type="checkbox" class="form-check-input" id="dp-check-all" aria-label="เลือกทั้งหมดในหน้านี้">
                            </th>
                            <th scope="col">รหัส</th>
                            <th scope="col">ชื่อ</th>
                            <?php if ($showParentType): ?>
                                <th scope="col">ประเภทหลัก</th>
                            <?php endif; ?>
                            <th scope="col">เกณฑ์ที่ผูก</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input dp-check" name="ids[]" value="<?= (int) $r['id'] ?>"
                                        aria-label="เลือก <?= Html::encode($r['title']) ?>">
                                </td>
                                <td><?= Html::encode($r['code']) ?></td>
                                <td><?= Html::encode($r['title']) ?></td>
                                <?php if ($showParentType): ?>
                                    <td>
                                        <?php if (!empty($r['parent_type_name'])): ?>
                                            <span class="badge bg-light text-dark border"><i data-lucide="corner-down-right" style="width:.8rem;height:.8rem;"></i> <?= Html::encode($r['parent_type_name']) ?></span>
                                        <?php elseif (!empty($r['parent_type_code'])): ?>
                                            <span class="text-muted small"><?= Html::encode($r['parent_type_code']) ?></span>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <?php if ($r['bound_profile_id']): ?>
                                        <span class="badge bg-success"><?= Html::encode($r['bound_profile_name']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">— ไม่ได้ผูก —</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <div class="mt-3"><?= LinkPager::widget(['pagination' => $pages]) ?></div>
    </div></div>
    <?= Html::endForm() ?>
    <?php Pjax::end(); ?>
</div>

<?php
$js = <<<'JS'
(function () {
    var CT = '#dp-binding-container';
    function boxes() { return Array.prototype.slice.call(document.querySelectorAll(CT + ' .dp-check')); }
    function recalc() {
        var all = boxes();
        var checked = new Set(), total = new Set();
        all.forEach(function (c) { total.add(c.value); if (c.checked) checked.add(c.value); });
        var n = checked.size, t = total.size;
        var cnt = document.getElementById('dp-sel-count');
        if (cnt) cnt.textContent = n;
        var sel = document.getElementById('dp-bulk-profile');
        var btn = document.getElementById('dp-apply-btn');
        if (btn) btn.disabled = !(n > 0 && sel && sel.value !== '');
        var master = document.getElementById('dp-check-all');
        if (master) { master.checked = (n > 0 && n === t); master.indeterminate = (n > 0 && n < t); }
    }
    $(document).on('change', CT + ' .dp-check', recalc);
    $(document).on('change', '#dp-bulk-profile', recalc);
    $(document).on('change', '#dp-check-all', function () {
        var on = this.checked;
        boxes().forEach(function (c) { c.checked = on; });
        recalc();
    });
    $(document).on('pjax:end', recalc);
    // ปุ่ม "กำหนดให้ที่เลือก" → confirm → ajax POST → reload เฉพาะตารางผ่าน pjax
    handleFormSubmit('#dp-bulk-form', null, async function (r) {
        var c = r && r.container;
        if (c && document.querySelector(c) && typeof erpReloadPjax === 'function' && erpReloadPjax(c)) return;
        location.reload();
    });
    recalc();
})();
JS;
$this->registerJs($js);
?>

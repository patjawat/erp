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
/** @var string|null $bind */
/** @var bool $canEdit */
/** @var array $recentLogs */
/** @var app\modules\am\models\DepreciationProfile[] $profiles */

$levelLabels = [
    'asset_type' => 'ประเภทหลัก',
    'asset_category' => 'หมวด',
    'asset_item' => 'รายการ',
];

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

    <?php if (empty($profiles)): ?>
        <div class="alert alert-warning">
            <div class="fw-semibold mb-1"><i data-lucide="alert-triangle"></i> ยังไม่มีเกณฑ์ค่าเสื่อมในระบบ จึงยังผูกอะไรไม่ได้</div>
            <div class="small mb-2">ต้องสร้างเกณฑ์ (อายุการใช้งาน / มูลค่าซาก / วิธีคำนวณ) ก่อน แล้วจึงกลับมาผูกเข้ากับประเภททรัพย์สินในหน้านี้</div>
            <?= Html::a('<i data-lucide="percent"></i> ไปหน้าเกณฑ์ค่าเสื่อม แล้วกด “นำเข้าข้อมูลตั้งต้น”', ['/am/depreciation-profile/index'], ['class' => 'btn btn-sm btn-warning']) ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info small">
            <i data-lucide="info"></i> ทรัพย์สินจะใช้เกณฑ์จากระดับที่เฉพาะเจาะจงที่สุด: <b>รายชิ้น → รายการ → หมวด → ประเภทหลัก</b> — ตั้งที่ระดับกลุ่มครั้งเดียว ครอบคลุมทุกชิ้นใต้กลุ่มนั้นอัตโนมัติ (ตั้งค่ารายชิ้นได้ที่เมนู "เปลี่ยนเกณฑ์ทรัพย์สิน")<br>
            <i data-lucide="mouse-pointer-click"></i> <b>ผูกทีละรายการ:</b> เลือกเกณฑ์จากช่องในคอลัมน์ "เกณฑ์ที่ผูก" ของแถวนั้น — บันทึกทันที<br>
            <i data-lucide="check-check"></i> <b>ผูกทีเดียวหลายรายการ:</b> ติ๊กเลือกแถวที่ต้องการ เลือกเกณฑ์ที่แถบด้านบน แล้วกด "กำหนดให้ที่เลือก"
        </div>
    <?php endif; ?>

    <?php if (!$canEdit): ?>
        <div class="alert alert-secondary small">
            <i data-lucide="eye"></i> คุณมีสิทธิ์<b>ดูอย่างเดียว</b> — การผูกเกณฑ์ต้องใช้สิทธิ์ <code>depreciationSetup</code> (พัสดุ / บัญชีผู้ดูแลระบบ)
        </div>
    <?php endif; ?>

    <ul class="nav nav-pills mb-3">
        <?php foreach ($levels as $lv => $label): ?>
            <li class="nav-item">
                <?= Html::a($label, ['index', 'level' => $lv, 'bind' => $bind], ['class' => 'nav-link ' . ($level === $lv ? 'active' : '')]) ?>
            </li>
        <?php endforeach; ?>
    </ul>

    <div class="card mb-3"><div class="card-body">
        <?= Html::beginForm(['index'], 'get', ['class' => 'row g-2 align-items-end']) ?>
            <?= Html::hiddenInput('level', $level) ?>
            <?php if ($level !== 'asset_type' && !empty($typeOptions)): ?>
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
            <div class="col-md-3">
                <label class="form-label">สถานะการผูก</label>
                <select name="bind" class="form-select" onchange="this.form.submit()">
                    <option value="">— ทั้งหมด —</option>
                    <option value="no" <?= $bind === 'no' ? 'selected' : '' ?>>ยังไม่ผูก</option>
                    <option value="yes" <?= $bind === 'yes' ? 'selected' : '' ?>>ผูกแล้ว</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">ค้นหา (ชื่อ/รหัส)</label>
                <input type="text" name="q" class="form-control" value="<?= Html::encode($q) ?>">
            </div>
            <div class="col-md-2"><?= Html::submitButton('<i data-lucide="search"></i> ค้นหา', ['class' => 'btn btn-outline-primary']) ?></div>
            <div class="col text-end text-muted small">พบ <?= $count ?> รายการ (<?= $levels[$level] ?>)</div>
        <?= Html::endForm() ?>
    </div></div>

    <?php Pjax::begin(['id' => 'dp-binding-container', 'enablePushState' => false]); ?>
    <?php $showParentType = ($level !== 'asset_type'); ?>
    <?= Html::beginForm(['bulk-set'], 'post', [
        'id' => 'dp-bulk-form',
        'data-confirm-title' => 'ยืนยันกำหนดเกณฑ์กับรายการที่เลือก?',
        'data-confirm-text' => 'ระบบจะตั้งเกณฑ์ให้ทุกรายการที่ติ๊กเลือกไว้ (มีผลกับทรัพย์สินที่ขึ้นทะเบียนใหม่หลังจากนี้)',
    ]) ?>
    <?= Html::hiddenInput('level', $level) ?>
    <?= Html::hiddenInput('q', $q) ?>
    <?= Html::hiddenInput('type', $type ?? '') ?>
    <?= Html::hiddenInput('bind', $bind ?? '') ?>

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
                <span class="small text-muted" id="dp-sel-assets"></span>
                <button type="submit" id="dp-apply-btn" class="btn btn-sm btn-primary" <?= $canEdit ? 'disabled' : 'disabled title="ไม่มีสิทธิ์แก้ไข"' ?>>
                    <i data-lucide="check-check"></i> กำหนดให้ที่เลือก
                </button>
            </div>
        </div>
    </div>

    <div class="card"><div class="card-body">
        <?php if (empty($rows)): ?>
            <div class="text-center text-muted py-4">ไม่พบข้อมูล</div>
        <?php else: ?>
            <?php
            /**
             * ช่องเลือกเกณฑ์ประจำแถว — บันทึกทันทีเมื่อเปลี่ยนค่า (โพสต์ไป action set ผ่าน ajax)
             * ไม่ใส่ name เพื่อไม่ให้ถูกส่งไปพร้อมฟอร์ม bulk ที่ครอบตารางอยู่
             */
            $rowSelect = static function (array $r) use ($profiles, $canEdit): string {
                $inheritLabel = !empty($r['inherited_profile_name'])
                    ? 'สืบทอด: ' . $r['inherited_profile_name']
                    : 'ไม่ผูกเกณฑ์';
                $opts = '<option value="0">— ' . Html::encode($inheritLabel) . ' —</option>';
                foreach ($profiles as $p) {
                    $sel = ((int) $r['bound_profile_id'] === (int) $p->id) ? ' selected' : '';
                    $opts .= '<option value="' . (int) $p->id . '"' . $sel . '>'
                        . Html::encode($p->code . ' — ' . $p->name) . '</option>';
                }
                $cls = $r['bound_profile_id'] ? 'border-success' : (!empty($r['inherited_profile_id']) ? 'border-info' : '');

                return '<select class="form-select form-select-sm dp-row-profile ' . $cls . '"'
                    . ' data-id="' . (int) $r['id'] . '"'
                    . ' data-current="' . (int) $r['bound_profile_id'] . '"'
                    . ' data-assets="' . (int) $r['asset_count'] . '"'
                    . ' style="min-width:14rem;"'
                    . ($canEdit ? '' : ' disabled')
                    . ' aria-label="เกณฑ์ค่าเสื่อมของ ' . Html::encode($r['title']) . '">'
                    . $opts . '</select>';
            };

            /** ป้ายบอกว่าเกณฑ์ที่ใช้จริงมาจากไหน — กันผูกซ้ำทั้งที่รับมาจากประเภทแม่อยู่แล้ว */
            $effectiveBadge = static function (array $r): string {
                if (!empty($r['bound_profile_id'])) {
                    return '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle">ผูกที่ระดับนี้</span>';
                }
                if (!empty($r['inherited_profile_name'])) {
                    return '<span class="badge bg-info-subtle text-info-emphasis border border-info-subtle" title="'
                        . Html::encode($r['inherited_profile_name']) . '">สืบทอดจากประเภทหลัก</span>';
                }

                return '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">ยังไม่มีเกณฑ์</span>';
            };
            ?>
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
                                    <span class="text-end flex-shrink-0">
                                        <?= $effectiveBadge($r) ?>
                                        <span class="small text-muted d-block mt-1">
                                            <?= $r['asset_count'] > 0 ? 'กระทบ ' . number_format($r['asset_count']) . ' ชิ้น' : 'ไม่มีทรัพย์สิน' ?>
                                        </span>
                                    </span>
                                </span>
                                <?php if (!empty($profiles)): ?>
                                    <span class="d-block mt-2"><?= $rowSelect($r) ?></span>
                                <?php endif; ?>
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
                            <th scope="col" class="text-end" title="จำนวนทรัพย์สินที่จะได้รับผลจากการผูกแถวนี้">ทรัพย์สิน</th>
                            <th scope="col">สถานะ</th>
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
                                <td class="text-end">
                                    <?php if ($r['asset_count'] > 0): ?>
                                        <span class="fw-semibold"><?= number_format($r['asset_count']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= $effectiveBadge($r) ?></td>
                                <td>
                                    <?php if (!empty($profiles)): ?>
                                        <?= $rowSelect($r) ?>
                                    <?php elseif ($r['bound_profile_id']): ?>
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

    <?php // ---------- ประวัติการผูก ---------- ?>
    <?php if (!empty($recentLogs)): ?>
        <div class="card mt-3">
            <div class="card-body">
                <h6 class="fw-semibold mb-3"><i data-lucide="history"></i> ประวัติการผูกเกณฑ์ล่าสุด</h6>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <caption class="visually-hidden">ประวัติการเปลี่ยนการผูกเกณฑ์ค่าเสื่อม</caption>
                        <thead class="table-light">
                            <tr>
                                <th scope="col">เมื่อ</th>
                                <th scope="col">ระดับ</th>
                                <th scope="col">รายการ</th>
                                <th scope="col">จาก</th>
                                <th scope="col">เป็น</th>
                                <th scope="col">โดย</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentLogs as $l): ?>
                                <tr>
                                    <td class="small text-muted text-nowrap"><?= Html::encode((string) $l['created_at']) ?></td>
                                    <td class="small"><?= Html::encode($levelLabels[(string) $l['level']] ?? (string) $l['level']) ?></td>
                                    <td>
                                        <?= Html::encode((string) $l['title']) ?>
                                        <span class="text-muted small">(<?= Html::encode((string) $l['code']) ?>)</span>
                                    </td>
                                    <td class="small"><?= $l['old_profile_name'] ? Html::encode($l['old_profile_name']) : '<span class="text-muted">— ไม่ผูก —</span>' ?></td>
                                    <td class="small"><?= $l['new_profile_name'] ? Html::encode($l['new_profile_name']) : '<span class="text-muted">— ล้างการผูก —</span>' ?></td>
                                    <td class="small text-muted"><?= $l['created_by_name'] ? Html::encode($l['created_by_name']) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$cfg = \yii\helpers\Json::htmlEncode([
    'setUrl' => Url::to(['set']),
    'level' => $level,
    'q' => (string) $q,
    'type' => (string) ($type ?? ''),
    'bind' => (string) ($bind ?? ''),
]);
$js = <<<'JS'
(function () {
    var CT = '#dp-binding-container';
    var CFG = window.dpBindCfg || {};
    function boxes() { return Array.prototype.slice.call(document.querySelectorAll(CT + ' .dp-check')); }
    // จำนวนทรัพย์สินของแต่ละแถว อ่านจาก data-assets ของช่องเลือกเกณฑ์ในแถวเดียวกัน
    function assetsOf(box) {
        var row = box.closest('tr') || box.closest('li');
        var sel = row ? row.querySelector('.dp-row-profile') : null;
        return sel ? (parseInt(sel.getAttribute('data-assets'), 10) || 0) : 0;
    }
    function recalc() {
        var all = boxes();
        var checked = new Set(), total = new Set(), assets = 0;
        all.forEach(function (c) {
            total.add(c.value);
            if (c.checked && !checked.has(c.value)) { checked.add(c.value); assets += assetsOf(c); }
        });
        var n = checked.size, t = total.size;
        var cnt = document.getElementById('dp-sel-count');
        if (cnt) cnt.textContent = n;
        // บอกผลกระทบก่อนกด — ผูกกลุ่มเดียวอาจกระทบทรัพย์สินหลักพันชิ้น
        var ac = document.getElementById('dp-sel-assets');
        if (ac) { ac.textContent = n > 0 ? ('· กระทบทรัพย์สิน ' + assets.toLocaleString() + ' ชิ้น') : ''; }
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

    // ผูกทีละรายการ: เลือกเกณฑ์ในแถว → บันทึกทันที
    // (select อยู่ใน label ของฝั่งมือถือ — กันไม่ให้คลิกไปโดนช่องติ๊กเลือก)
    $(document).on('click', CT + ' .dp-row-profile', function (e) { e.stopPropagation(); });
    $(document).on('change', CT + ' .dp-row-profile', function () {
        var el = this;
        var prev = el.getAttribute('data-current') || '0';
        if (el.value === prev) { return; }
        el.disabled = true;
        $.post(CFG.setUrl, {
            id: el.getAttribute('data-id'),
            level: CFG.level,
            q: CFG.q,
            type: CFG.type,
            bind: CFG.bind,
            profile_id: el.value,
            _csrf: yii.getCsrfToken()
        }).done(function (r) {
            if (r && r.status === 'error') {
                el.value = prev;
                el.disabled = false;
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', text: r.message, timer: 2500, showConfirmButton: false });
                } else {
                    alert(r.message);
                }
                return;
            }
            el.setAttribute('data-current', el.value);
            if (typeof erpReloadPjax === 'function' && erpReloadPjax(CT)) { return; }
            location.reload();
        }).fail(function () {
            el.value = prev;
            el.disabled = false;
            alert('บันทึกไม่สำเร็จ');
        });
    });

    // ปุ่ม "กำหนดให้ที่เลือก" → confirm → ajax POST → reload เฉพาะตารางผ่าน pjax
    handleFormSubmit('#dp-bulk-form', null, async function (r) {
        var c = r && r.container;
        if (c && document.querySelector(c) && typeof erpReloadPjax === 'function' && erpReloadPjax(c)) return;
        location.reload();
    });
    recalc();
})();
JS;
$this->registerJs('window.dpBindCfg = ' . $cfg . ';');
$this->registerJs($js);
?>

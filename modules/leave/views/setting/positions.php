<?php
use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var array $config */
/** @var array $items */
/** @var array $fieldLabels */
/** @var string $templateUrl */
/** @var app\modules\leave\models\Leave[] $recentLeaves */

$this->title = 'กำหนดตำแหน่งข้อมูลบน PDF';
$this->params['breadcrumbs'][] = ['label' => 'การลางาน', 'url' => ['/leave/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'แบบฟอร์มใบลา', 'url' => ['/leave/setting/leave-template']];
$this->params['breadcrumbs'][] = $this->title;

// ใช้ฟอนต์ THSarabunNew ให้ตรงกับตอนพิมพ์ PDF
$this->registerCssFile(Url::to('@web/css/thsarabunnew.css'), ['depends' => [\yii\web\YiiAsset::class]]);
$this->registerCss('.leave-field-chip { font-family: "THSarabunNew", sans-serif; }');

$scale = 3; // 3 px = 1 mm (A4 210×297 mm → 630×891 px) — ขนาดใหญ่ขึ้นเพื่อลากจัดตำแหน่งได้ง่าย
$pageW = 210;
$pageH = 297;
$canvasW = $pageW * $scale;
$canvasH = $pageH * $scale;
// ขนาดตัวอักษรบน canvas ให้เล็กกว่าหรือเท่ากับ template (แบบฟอร์มราชการมักใช้ตัวเล็ก) — ตอนพิมพ์ใช้ขนาดจริง
$pageWpt = 595.276; // 210mm in pt (72pt/inch)
$fontDisplayScale = ($canvasW / $pageWpt) * 0.55;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-geo-alt"></i> <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/leave/views/menu', ['active' => 'setting']) ?>
<?php $this->endBlock(); ?>

<div class="alert alert-info border-0 rounded-3 mb-3 d-flex align-items-start gap-2" role="status">
    <i class="bi bi-info-circle fs-5 text-info flex-shrink-0 mt-1"></i>
    <div class="small">
        <strong>การพิมพ์ใบลา</strong> — หลังบันทึกตำแหน่งแล้ว ไปที่ <strong>ขอลา / รายการของฉัน</strong> แล้วกดปุ่ม «พิมพ์ใบลา» ที่รายการที่ต้องการ เพื่อเปิดหน้ารูปแบบพิมพ์ หรือใช้บล็อก <strong>ทดสอบพิมพ์ใบลา</strong> ด้านซ้ายเพื่อตรวจสอบทันที
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-primary bg-opacity-10 text-primary border-0 py-3 px-4 rounded-top-3">
        <h6 class="mb-0 small fw-semibold d-flex align-items-center gap-2">
            <i class="bi bi-hand-index-thumb"></i> ลากฟิลด์ไปวางบนพื้นที่เทมเพลต (A4) ให้ตรงตำแหน่งที่ต้องการ
        </h6>
    </div>
    <div class="card-body p-4">
        <div id="positions-alert" class="alert d-none mb-3"></div>
        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-body">ตำแหน่งข้อมูลบนเทมเพลต</label>
                    <p class="small text-muted mb-2">เพิ่มได้หลายจุดต่อฟิลด์ (เช่น ชื่อผู้ลา ไว้ที่หัวฟอร์มและที่ช่องเซ็น) เลือกประเภทฟิลด์ ปรับขนาด/ความหนา แล้วลากชิปไปวางบนเทมเพลต</p>
                </div>
                <form id="positions-form">
                    <div id="positions-rows" class="d-flex flex-column gap-2 mb-3">
                        <?php foreach ($items as $item): ?>
                        <?php
                            $itemId = $item['id'];
                            $key = $item['key'];
                            $x = (float) ($item['x'] ?? 0);
                            $y = (float) ($item['y'] ?? 0);
                            $fontSize = (int) ($item['fontSize'] ?? 15);
                            $bold = !empty($item['bold']);
                            $enabled = isset($item['enabled']) ? (int) $item['enabled'] : 1;
                            $label = $item['label'] ?? $key;
                        ?>
                        <div class="position-row d-flex align-items-center gap-2 p-2 rounded-3 border border-1 border-secondary border-opacity-25 flex-wrap <?= !$enabled ? 'opacity-75' : '' ?>" data-item-id="<?= Html::encode($itemId) ?>">
                            <div class="d-flex align-items-center gap-1 flex-grow-1" style="min-width: 0;">
                                <select name="positions[<?= Html::encode($itemId) ?>][key]" class="form-select position-key-select" style="width: auto; min-width: 10rem;" aria-label="ประเภทฟิลด์" data-item-id="<?= Html::encode($itemId) ?>">
                                    <?php foreach ($fieldLabels as $fk => $fl): ?>
                                    <option value="<?= Html::encode($fk) ?>" <?= $fk === $key ? 'selected' : '' ?>><?= Html::encode($fl['label'] ?? $fk) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="positions[<?= Html::encode($itemId) ?>][enabled]" value="0">
                                <div class="form-check mb-0 flex-shrink-0">
                                    <input type="checkbox" name="positions[<?= Html::encode($itemId) ?>][enabled]" value="1" class="form-check-input field-enabled-cb" data-item-id="<?= Html::encode($itemId) ?>" <?= $enabled ? 'checked' : '' ?> aria-label="แสดงบนเทมเพลต">
                                    <label class="form-check-label small text-muted mb-0">แสดง</label>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <label class="small text-muted mb-0 me-1">ขนาด</label>
                                <input type="number" min="6" max="24" name="positions[<?= Html::encode($itemId) ?>][fontSize]" value="<?= (int) $fontSize ?>" class="form-control position-font-size" style="width: 3.5rem;" data-item-id="<?= Html::encode($itemId) ?>" aria-label="ขนาดตัวอักษร">
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <input type="hidden" name="positions[<?= Html::encode($itemId) ?>][bold]" value="0">
                                <div class="form-check mb-0">
                                    <input type="checkbox" name="positions[<?= Html::encode($itemId) ?>][bold]" value="1" class="form-check-input position-bold" data-item-id="<?= Html::encode($itemId) ?>" <?= $bold ? 'checked' : '' ?> aria-label="ตัวหนา">
                                    <label class="form-check-label small text-muted mb-0">หนา</label>
                                </div>
                            </div>
                            <input type="hidden" name="positions[<?= Html::encode($itemId) ?>][x]" value="<?= Html::encode($x) ?>" data-pos-x data-item-id="<?= Html::encode($itemId) ?>">
                            <input type="hidden" name="positions[<?= Html::encode($itemId) ?>][y]" value="<?= Html::encode($y) ?>" data-pos-y data-item-id="<?= Html::encode($itemId) ?>">
                            <button type="button" class="btn btn-outline-danger btn-sm position-remove" data-item-id="<?= Html::encode($itemId) ?>" aria-label="ลบตำแหน่ง"><i class="bi bi-trash"></i></button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary rounded-3" id="btn-add-position"><i class="bi bi-plus-lg me-1"></i> เพิ่มตำแหน่ง</button>
                    </div>
                    <template id="position-row-tpl">
                        <div class="position-row d-flex align-items-center gap-2 p-2 rounded-3 border border-1 border-secondary border-opacity-25 flex-wrap" data-item-id="__ITEM_ID__">
                            <div class="d-flex align-items-center gap-1 flex-grow-1" style="min-width: 0;">
                                <select name="positions[__ITEM_ID__][key]" class="form-select position-key-select" style="width: auto; min-width: 10rem;" aria-label="ประเภทฟิลด์" data-item-id="__ITEM_ID__"></select>
                                <input type="hidden" name="positions[__ITEM_ID__][enabled]" value="0">
                                <div class="form-check mb-0 flex-shrink-0">
                                    <input type="checkbox" name="positions[__ITEM_ID__][enabled]" value="1" class="form-check-input field-enabled-cb" data-item-id="__ITEM_ID__" aria-label="แสดงบนเทมเพลต">
                                    <label class="form-check-label small text-muted mb-0">แสดง</label>
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <label class="small text-muted mb-0 me-1">ขนาด</label>
                                <input type="number" min="6" max="24" name="positions[__ITEM_ID__][fontSize]" value="15" class="form-control position-font-size" style="width: 3.5rem;" data-item-id="__ITEM_ID__" aria-label="ขนาดตัวอักษร">
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                <input type="hidden" name="positions[__ITEM_ID__][bold]" value="0">
                                <div class="form-check mb-0">
                                    <input type="checkbox" name="positions[__ITEM_ID__][bold]" value="1" class="form-check-input position-bold" data-item-id="__ITEM_ID__" aria-label="ตัวหนา">
                                    <label class="form-check-label small text-muted mb-0">หนา</label>
                                </div>
                            </div>
                            <input type="hidden" name="positions[__ITEM_ID__][x]" value="0" data-pos-x data-item-id="__ITEM_ID__">
                            <input type="hidden" name="positions[__ITEM_ID__][y]" value="0" data-pos-y data-item-id="__ITEM_ID__">
                            <button type="button" class="btn btn-outline-danger btn-sm position-remove" data-item-id="__ITEM_ID__" aria-label="ลบตำแหน่ง"><i class="bi bi-trash"></i></button>
                        </div>
                    </template>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary rounded-3 px-3" id="btn-save-positions">
                            <i class="bi bi-check-lg me-1"></i> บันทึกตำแหน่ง
                        </button>
                        <?= Html::a('ย้อนกลับ', ['/leave/setting/leave-template'], ['class' => 'btn btn-outline-secondary rounded-3']) ?>
                    </div>

                    <?php if (!empty($recentLeaves)): ?>
                    <div class="card border-0 border-top mt-4 pt-3">
                        <label class="form-label small fw-semibold text-body d-flex align-items-center gap-2">
                            <i class="bi bi-printer text-primary"></i>
                            ทดสอบพิมพ์ใบลา
                        </label>
                        <p class="small text-muted mb-2">เลือกใบลาด้านล่าง แล้วกดปุ่มเพื่อเปิดหน้ารูปแบบพิมพ์ (ใช้ตรวจสอบหลังกำหนดตำแหน่ง)</p>
                        <div class="d-flex flex-wrap align-items-end gap-2">
                            <div class="flex-grow-1" style="min-width: 200px;">
                                <select id="leave-print-test-select" class="form-select">
                                    <?php foreach ($recentLeaves as $lv): ?>
                                    <option value="<?= (int) $lv->id ?>"><?= Html::encode($lv->leaveType ? $lv->leaveType->title : '-') ?> — <?= $lv->showLeaveDate() ?> (<?= (float) $lv->total_days ?> วัน)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?= Html::a(
                                '<i class="bi bi-printer me-1"></i> เปิดหน้ารูปแบบพิมพ์',
                                ['/leave/leave/print', 'id' => $recentLeaves[0]->id],
                                ['class' => 'btn btn-outline-primary rounded-3', 'target' => '_blank', 'rel' => 'noopener', 'id' => 'leave-print-test-btn', 'data-print-base' => Url::to(['/leave/leave/print'])]
                            ) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </form>
            </div>
            <div class="col-12 col-lg-8">
                <p class="small text-muted mb-2">พื้นที่เทมเพลต (A4) — พื้นหลังเป็น PDF ที่อัปโหลด ลากชิ้นงานไปวางให้ตรงตำแหน่ง</p>
                <div class="border rounded-3 overflow-auto bg-secondary bg-opacity-10 d-flex justify-content-center p-3" style="max-height: min(920px, 85vh);">
                    <div id="pdf-canvas-wrapper" class="position-relative shadow-sm" style="width: <?= (int) $canvasW ?>px; height: <?= (int) $canvasH ?>px;">
                        <iframe src="<?= Html::encode($templateUrl) ?>#toolbar=0" class="position-absolute top-0 start-0 w-100 h-100 border-0" style="pointer-events: none; z-index: 0;" title="เทมเพลต PDF (พื้นหลัง)"></iframe>
                        <div id="pdf-canvas" class="position-absolute top-0 start-0 w-100 h-100" style="z-index: 1; background: transparent;" data-font-display-scale="<?= Html::encode($fontDisplayScale) ?>">
                        <?php foreach ($items as $item): ?>
                        <?php
                            $itemId = $item['id'];
                            $key = $item['key'];
                            $enabled = isset($item['enabled']) ? (int) $item['enabled'] : 1;
                            $x = (float) ($item['x'] ?? 0);
                            $y = (float) ($item['y'] ?? 0);
                            $fontSize = (int) ($item['fontSize'] ?? 15);
                            $bold = !empty($item['bold']);
                            $label = $item['label'] ?? $key;
                            $left = round($x * $scale);
                            $top = round($y * $scale);
                            $chipFontSizePt = round($fontSize * $fontDisplayScale, 1);
                            $chipFontWeight = $bold ? 'bold' : 'normal';
                        ?>
                        <div class="position-absolute leave-field-chip text-primary small fw-medium user-select-none <?= !$enabled ? 'd-none' : '' ?>"
                             data-item-id="<?= Html::encode($itemId) ?>"
                             data-field-key="<?= Html::encode($key) ?>"
                             style="left: <?= (int) $left ?>px; top: <?= (int) $top ?>px; min-width: 2rem; cursor: grab; font-family: 'THSarabunNew', sans-serif; font-size: <?= Html::encode($chipFontSizePt) ?>pt; font-weight: <?= Html::encode($chipFontWeight) ?>;"
                             title="<?= Html::encode($label) ?>"
                             draggable="false">
                            <span class="chip-label text-truncate d-inline-block" style="max-width: 140px;"><?= Html::encode($label) ?></span>
                        </div>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$saveUrl = Url::to(['/leave/setting/save-positions']);
$csrfParam = Yii::$app->request->csrfParam;
$csrfToken = Yii::$app->request->csrfToken;
$scaleJs = $scale;
$pageWJs = $pageW;
$pageHJs = $pageH;
$fieldLabelsJson = json_encode(array_map(function ($v) { return $v['label'] ?? ''; }, $fieldLabels));
$fieldKeysJson = json_encode(array_keys($fieldLabels));
$this->registerJs(<<<JS
(function() {
    var scale = {$scaleJs};
    var pageW = {$pageWJs};
    var pageH = {$pageHJs};
    var fieldLabels = {$fieldLabelsJson};
    var fieldKeys = {$fieldKeysJson};
    var canvas = document.getElementById('pdf-canvas');
    var fontDisplayScale = parseFloat(canvas.getAttribute('data-font-display-scale')) || (420 / 595.276);
    var form = document.getElementById('positions-form');
    var btn = document.getElementById('btn-save-positions');
    var alertEl = document.getElementById('positions-alert');
    var rowsContainer = document.getElementById('positions-rows');
    var rowTpl = document.getElementById('position-row-tpl');
    var addBtn = document.getElementById('btn-add-position');
    if (!canvas || !form || !btn || !rowsContainer || !rowTpl) return;

    function nextItemId() {
        return 'item_' + Date.now() + '_' + Math.random().toString(36).slice(2, 9);
    }

    function pxToMm(px, isY) {
        var v = px / scale;
        var max = isY ? pageH : pageW;
        return Math.round(Math.max(0, Math.min(max, v)) * 10) / 10;
    }

    function updateHiddenInput(itemId, x, y) {
        var xInput = form.querySelector('input[name="positions[' + itemId + '][x]"]');
        var yInput = form.querySelector('input[name="positions[' + itemId + '][y]"]');
        if (xInput) xInput.value = x;
        if (yInput) yInput.value = y;
    }

    function getLabelForKey(key) {
        return fieldLabels[key] !== undefined ? fieldLabels[key] : key;
    }

    function updateChipLabel(chip, key) {
        var span = chip.querySelector('.chip-label');
        if (span) span.textContent = getLabelForKey(key);
        chip.setAttribute('data-field-key', key);
        chip.title = getLabelForKey(key);
    }

    function updateChipFont(itemId, fontSize, bold) {
        var chip = canvas.querySelector('.leave-field-chip[data-item-id="' + itemId + '"]');
        if (!chip) return;
        var sizePt = (fontSize || 15) * fontDisplayScale;
        chip.style.fontSize = sizePt.toFixed(1) + 'pt';
        chip.style.fontWeight = bold ? 'bold' : 'normal';
    }

    function attachChipDrag(chip) {
        var itemId = chip.getAttribute('data-item-id');
        var dragging = false;
        var startX, startY, startLeft, startTop;

        chip.addEventListener('mousedown', function(e) {
            if (e.button !== 0) return;
            e.preventDefault();
            dragging = true;
            startX = e.clientX;
            startY = e.clientY;
            startLeft = parseInt(chip.style.left, 10) || 0;
            startTop = parseInt(chip.style.top, 10) || 0;
            chip.style.cursor = 'grabbing';
        });

        document.addEventListener('mousemove', function(e) {
            if (!dragging || !chip.parentNode) return;
            var dx = e.clientX - startX;
            var dy = e.clientY - startY;
            var newLeft = Math.max(0, Math.min(canvas.offsetWidth - chip.offsetWidth, startLeft + dx));
            var newTop = Math.max(0, Math.min(canvas.offsetHeight - chip.offsetHeight, startTop + dy));
            chip.style.left = newLeft + 'px';
            chip.style.top = newTop + 'px';
            updateHiddenInput(itemId, pxToMm(newLeft, false), pxToMm(newTop, true));
            startX = e.clientX;
            startY = e.clientY;
            startLeft = newLeft;
            startTop = newTop;
        });

        document.addEventListener('mouseup', function() {
            if (dragging) {
                dragging = false;
                chip.style.cursor = 'grab';
            }
        });
    }

    canvas.querySelectorAll('.leave-field-chip').forEach(attachChipDrag);

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        var positions = {};
        rowsContainer.querySelectorAll('.position-row').forEach(function(row) {
            var itemId = row.getAttribute('data-item-id');
            if (!itemId) return;
            var keyInput = form.querySelector('select[name="positions[' + itemId + '][key]"]');
            var xInput = form.querySelector('input[name="positions[' + itemId + '][x]"]');
            var yInput = form.querySelector('input[name="positions[' + itemId + '][y]"]');
            var fsInput = form.querySelector('input[name="positions[' + itemId + '][fontSize]"]');
            var boldCb = form.querySelector('input[name="positions[' + itemId + '][bold]"][type="checkbox"]');
            var enabledCb = form.querySelector('input[name="positions[' + itemId + '][enabled]"][type="checkbox"]');
            if (!keyInput || !xInput || !yInput) return;
            var key = keyInput.value;
            if (!key) return;
            positions[itemId] = {
                key: key,
                x: parseFloat(xInput.value) || 0,
                y: parseFloat(yInput.value) || 0,
                fontSize: parseInt(fsInput ? fsInput.value : 15, 10) || 15,
                bold: (boldCb && boldCb.checked) ? 1 : 0,
                enabled: (enabledCb && enabledCb.checked) ? 1 : 0
            };
        });
        var data = {};
        data.positions = positions;
        data['{$csrfParam}'] = '{$csrfToken}';

        btn.disabled = true;
        alertEl.classList.add('d-none');
        fetch('{$saveUrl}', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        })
        .then(function(r) { return r.json(); })
        .then(function(res) {
            if (res.success) {
                alertEl.className = 'alert alert-success alert-dismissible fade show mb-3';
                alertEl.innerHTML = 'บันทึกตำแหน่งเรียบร้อย <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>';
                alertEl.classList.remove('d-none');
            } else {
                alertEl.className = 'alert alert-danger alert-dismissible fade show mb-3';
                alertEl.innerHTML = (res.message || 'บันทึกไม่สำเร็จ') + ' <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>';
                alertEl.classList.remove('d-none');
            }
        })
        .catch(function() {
            alertEl.className = 'alert alert-danger alert-dismissible fade show mb-3';
            alertEl.innerHTML = 'เกิดข้อผิดพลาด <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="ปิด"></button>';
            alertEl.classList.remove('d-none');
        })
        .finally(function() { btn.disabled = false; });
    });

    rowsContainer.addEventListener('input', function(e) {
        var itemId = e.target.getAttribute('data-item-id');
        if (!itemId) return;
        if (e.target.classList.contains('position-font-size') || e.target.classList.contains('position-bold')) {
            var fs = form.querySelector('input[name="positions[' + itemId + '][fontSize]"]');
            var boldCb = form.querySelector('input[name="positions[' + itemId + '][bold]"][type="checkbox"]');
            updateChipFont(itemId, fs ? parseInt(fs.value, 10) : 15, boldCb ? boldCb.checked : false);
        }
    });
    rowsContainer.addEventListener('change', function(e) {
        var itemId = e.target.getAttribute('data-item-id');
        if (!itemId) return;
        if (e.target.classList.contains('position-key-select')) {
            var chip = canvas.querySelector('.leave-field-chip[data-item-id="' + itemId + '"]');
            if (chip) updateChipLabel(chip, e.target.value);
        } else if (e.target.classList.contains('position-font-size') || e.target.classList.contains('position-bold')) {
            var fs = form.querySelector('input[name="positions[' + itemId + '][fontSize]"]');
            var boldCb = form.querySelector('input[name="positions[' + itemId + '][bold]"][type="checkbox"]');
            updateChipFont(itemId, fs ? parseInt(fs.value, 10) : 15, boldCb ? boldCb.checked : false);
        } else if (e.target.classList.contains('field-enabled-cb')) {
            var chip = canvas.querySelector('.leave-field-chip[data-item-id="' + itemId + '"]');
            var row = e.target.closest('.position-row');
            if (chip) chip.classList.toggle('d-none', !e.target.checked);
            if (row) row.classList.toggle('opacity-75', !e.target.checked);
        }
    });

    addBtn.addEventListener('click', function() {
        var itemId = nextItemId();
        var html = rowTpl.innerHTML.replace(/__ITEM_ID__/g, itemId);
        var wrap = document.createElement('div');
        wrap.innerHTML = html;
        var row = wrap.firstElementChild;
        var keySelect = row.querySelector('.position-key-select');
        fieldKeys.forEach(function(k) {
            var opt = document.createElement('option');
            opt.value = k;
            opt.textContent = getLabelForKey(k);
            keySelect.appendChild(opt);
        });
        rowsContainer.appendChild(row);

        var chip = document.createElement('div');
        chip.className = 'position-absolute leave-field-chip text-primary small fw-medium user-select-none';
        chip.setAttribute('data-item-id', itemId);
        chip.setAttribute('data-field-key', fieldKeys[0] || '');
        chip.style.left = '0px';
        chip.style.top = '0px';
        chip.style.minWidth = '2rem';
        chip.style.cursor = 'grab';
        chip.style.fontSize = (15 * fontDisplayScale).toFixed(1) + 'pt';
        chip.style.fontWeight = 'normal';
        chip.style.fontFamily = "'THSarabunNew', sans-serif";
        chip.title = getLabelForKey(fieldKeys[0] || '');
        chip.draggable = false;
        var span = document.createElement('span');
        span.className = 'chip-label text-truncate d-inline-block';
        span.style.maxWidth = '140px';
        span.textContent = getLabelForKey(fieldKeys[0] || '');
        chip.appendChild(span);
        canvas.appendChild(chip);
        attachChipDrag(chip);

        row.querySelector('.position-remove').addEventListener('click', function() {
            row.remove();
            chip.remove();
        });
    });

    rowsContainer.querySelectorAll('.position-remove').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var itemId = btn.getAttribute('data-item-id');
            var row = btn.closest('.position-row');
            var chip = canvas.querySelector('.leave-field-chip[data-item-id="' + itemId + '"]');
            if (row) row.remove();
            if (chip) chip.remove();
        });
    });

    var printTestSelect = document.getElementById('leave-print-test-select');
    var printTestBtn = document.getElementById('leave-print-test-btn');
    if (printTestSelect && printTestBtn) {
        var printBase = printTestBtn.getAttribute('data-print-base') || '';
        printTestSelect.addEventListener('change', function() {
            var id = this.value;
            printTestBtn.setAttribute('href', printBase + (printBase.indexOf('?') >= 0 ? '&' : '?') + 'id=' + id);
        });
    }
})();
JS
);
?>

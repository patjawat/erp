<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\bootstrap5\LinkPager;

/** @var yii\web\View $this */
/** @var app\modules\inventoryV2\models\Warehouse $warehouse */
/** @var array $rows */
/** @var \yii\data\Pagination $pagination */
/** @var array|null $totals */
/** @var string $q */
/** @var string $status */
/** @var string|null $categoryId */
/** @var app\modules\inventoryV2\models\Warehouse[] $accessibleWarehouses */
/** @var bool $hasInventoryRole */

$totals = $totals ?: ['total' => 0, 'configured' => 0];
$totalItems = (int) ($totals['total'] ?? 0);
$configuredItems = (int) ($totals['configured'] ?? 0);
$unconfiguredItems = max(0, $totalItems - $configuredItems);

$this->title = 'ตั้ง min/max วัสดุ: ' . $warehouse->warehouse_name;
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าคลัง', 'url' => ['/inventory-v2/default/setting']];
$this->params['breadcrumbs'][] = $warehouse->warehouse_name;

$saveUrl = Url::to(['/inventory-v2/warehouse/save-setting']);
$deleteUrl = Url::to(['/inventory-v2/warehouse/delete-setting']);
$csrf = Yii::$app->request->csrfToken;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-sliders2"></i>
        ตั้ง min/max วัสดุ
        <span class="text-muted fw-normal fs-6"><?= Html::encode($warehouse->warehouse_name) ?></span>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex flex-wrap align-items-center gap-2">
    <?php
    $groups = ['MAIN' => [], 'SUB' => [], 'BRANCH' => [], 'OTHER' => []];
    foreach ($accessibleWarehouses as $w) {
        $key = in_array($w->warehouse_type, ['MAIN', 'SUB', 'BRANCH'], true) ? $w->warehouse_type : 'OTHER';
        $groups[$key][] = $w;
    }
    $groupLabels = ['MAIN' => 'คลังหลัก', 'SUB' => 'คลังย่อย', 'BRANCH' => 'รพ.สต.', 'OTHER' => 'อื่นๆ'];
    $hasAny = false;
    foreach ($groups as $g) { if (!empty($g)) { $hasAny = true; break; } }
    ?>
    <?php if ($hasAny): ?>
        <label for="smm-warehouse-switcher" class="form-label small text-muted mb-0 d-none d-md-inline">
            <i class="bi bi-shuffle me-1"></i>เปลี่ยนคลัง
        </label>
        <select id="smm-warehouse-switcher" class="form-select form-select-sm" style="max-width: 280px;">
            <?php foreach ($groups as $type => $list): ?>
                <?php if (empty($list)) continue; ?>
                <optgroup label="<?= Html::encode($groupLabels[$type]) ?> (<?= count($list) ?>)">
                    <?php foreach ($list as $w): ?>
                        <option
                            value="<?= Url::to(['/inventory-v2/warehouse/stock-min-max', 'id' => $w->id]) ?>"
                            <?= $w->id === $warehouse->id ? 'selected' : '' ?>>
                            <?= Html::encode($w->warehouse_name) ?>
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            <?php endforeach; ?>
        </select>
        <?php if ($hasInventoryRole): ?>
            <span class="badge text-bg-primary-subtle text-primary border border-primary-subtle d-none d-lg-inline" title="คุณมีสิทธิ์ระดับ inventory จึงเห็นทุกคลัง">
                <i class="bi bi-shield-check me-1"></i>inventory
            </span>
        <?php endif; ?>
    <?php endif; ?>
    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['/inventory-v2/default/setting'], [
        'class' => 'btn btn-outline-secondary btn-sm',
    ]) ?>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid px-3 px-md-4 py-3 py-md-4" style="font-family: 'Sarabun', sans-serif;">

    <div class="row g-3 mb-3 stock-min-max-summary">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3 p-md-3">
                    <div class="text-muted small mb-1">วัสดุทั้งหมดในคลังนี้</div>
                    <div class="fs-3 fw-semibold text-body"><?= number_format($totalItems) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">ตั้งค่าแล้ว</div>
                    <div class="fs-3 fw-semibold text-success"><?= number_format($configuredItems) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">ยังไม่ตั้ง</div>
                    <div class="fs-3 fw-semibold text-secondary"><?= number_format($unconfiguredItems) ?></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <div class="text-muted small mb-1">ความครอบคลุม</div>
                    <div class="fs-3 fw-semibold text-primary">
                        <?= $totalItems > 0 ? round(($configuredItems / $totalItems) * 100) : 0 ?>%
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white py-2 px-3">
                    <h6 class="mb-0 text-white"><i class="bi bi-funnel me-2"></i>ค้นหา / กรอง</h6>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= Url::to(['/inventory-v2/warehouse/stock-min-max', 'id' => $warehouse->id]) ?>" class="row g-2 align-items-end">
                        <div class="col-12 col-md-5">
                            <label class="form-label small mb-1">ค้นหารหัส/ชื่อวัสดุ</label>
                            <input type="text" class="form-control" name="q" value="<?= Html::encode($q) ?>" placeholder="พิมพ์รหัสหรือชื่อ">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small mb-1">สถานะ</label>
                            <select class="form-select" name="status">
                                <option value="all" <?= $status === 'all' ? 'selected' : '' ?>>ทั้งหมด</option>
                                <option value="configured" <?= $status === 'configured' ? 'selected' : '' ?>>ตั้งค่าแล้ว</option>
                                <option value="unconfigured" <?= $status === 'unconfigured' ? 'selected' : '' ?>>ยังไม่ตั้ง</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3 d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-search me-1"></i>ค้นหา
                            </button>
                            <?php if ($q !== '' || $status !== 'all'): ?>
                                <a href="<?= Url::to(['/inventory-v2/warehouse/stock-min-max', 'id' => $warehouse->id]) ?>" class="btn btn-outline-secondary" title="ล้างตัวกรอง">
                                    <i class="bi bi-x-lg"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-3">
                    <h6 class="mb-0 text-white">
                        <i class="bi bi-ui-checks me-2"></i>รายการวัสดุ
                        <span class="badge text-bg-light text-dark ms-2"><?= number_format($pagination->totalCount) ?> รายการ</span>
                    </h6>
                    <small class="text-white-50 d-none d-md-inline">
                        <i class="bi bi-info-circle me-1"></i>
                        กรอกแล้วเลื่อนไปช่องถัดไป ระบบบันทึกอัตโนมัติ
                    </small>
                </div>

                <div class="card-body p-0">
                    <?php if (empty($rows)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบรายการวัสดุที่ตรงกับเงื่อนไข
                        </div>
                    <?php else: ?>
                        <div class="d-none d-md-block table-responsive">
                            <table class="table table-hover align-middle mb-0 stock-min-max-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>รหัส / ชื่อวัสดุ</th>
                                        <th style="width: 90px;">หน่วย</th>
                                        <th class="text-end" style="width: 140px;">min</th>
                                        <th class="text-end" style="width: 140px;">max</th>
                                        <th class="text-center" style="width: 110px;">สถานะ</th>
                                        <th style="width: 50px;"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rows as $i => $r):
                                        $itemDataJson = $r['item_data_json'] ?? null;
                                        if (is_string($itemDataJson)) {
                                            $itemDataJson = json_decode($itemDataJson, true);
                                        }
                                        $unitName = $itemDataJson['unit_name'] ?? '-';
                                        $isConfigured = !empty($r['setting_id']);
                                        $minQty = $isConfigured ? (float) $r['setting_min_qty'] : '';
                                        $maxQty = $isConfigured ? (float) $r['setting_max_qty'] : '';
                                        $rowNumber = $pagination->offset + $i + 1;
                                    ?>
                                        <tr data-item-code="<?= Html::encode($r['item_code']) ?>" class="<?= $isConfigured ? 'is-configured' : 'is-unconfigured' ?>">
                                            <td class="text-muted small"><?= $rowNumber ?></td>
                                            <td>
                                                <div class="fw-semibold"><?= Html::encode($r['item_name']) ?></div>
                                                <code class="text-muted small"><?= Html::encode($r['item_code']) ?></code>
                                            </td>
                                            <td class="text-muted small"><?= Html::encode($unitName) ?></td>
                                            <td class="text-end">
                                                <input
                                                    type="number"
                                                    inputmode="decimal"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control form-control-sm text-end js-min-input"
                                                    value="<?= $minQty === '' ? '' : htmlspecialchars((string) $minQty) ?>"
                                                    placeholder="—"
                                                >
                                            </td>
                                            <td class="text-end">
                                                <input
                                                    type="number"
                                                    inputmode="decimal"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control form-control-sm text-end js-max-input"
                                                    value="<?= $maxQty === '' ? '' : htmlspecialchars((string) $maxQty) ?>"
                                                    placeholder="—"
                                                >
                                            </td>
                                            <td class="text-center">
                                                <span class="badge status-badge <?= $isConfigured ? 'text-bg-success-subtle text-success border border-success-subtle' : 'text-bg-light text-secondary border' ?>">
                                                    <?= $isConfigured ? 'ตั้งแล้ว' : 'ยังไม่ตั้ง' ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-link text-danger js-delete <?= $isConfigured ? '' : 'd-none' ?>" title="ลบการตั้งค่า">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <div class="d-block d-md-none">
                            <div class="list-group list-group-flush stock-min-max-mobile">
                                <?php foreach ($rows as $i => $r):
                                    $itemDataJson = $r['item_data_json'] ?? null;
                                    if (is_string($itemDataJson)) {
                                        $itemDataJson = json_decode($itemDataJson, true);
                                    }
                                    $unitName = $itemDataJson['unit_name'] ?? '-';
                                    $isConfigured = !empty($r['setting_id']);
                                    $minQty = $isConfigured ? (float) $r['setting_min_qty'] : '';
                                    $maxQty = $isConfigured ? (float) $r['setting_max_qty'] : '';
                                ?>
                                    <div class="list-group-item py-3" data-item-code="<?= Html::encode($r['item_code']) ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1 pe-2">
                                                <div class="fw-semibold"><?= Html::encode($r['item_name']) ?></div>
                                                <code class="text-muted small"><?= Html::encode($r['item_code']) ?></code>
                                                <span class="text-muted small ms-2"><?= Html::encode($unitName) ?></span>
                                            </div>
                                            <span class="badge status-badge <?= $isConfigured ? 'text-bg-success-subtle text-success border border-success-subtle' : 'text-bg-light text-secondary border' ?>">
                                                <?= $isConfigured ? 'ตั้งแล้ว' : 'ยังไม่ตั้ง' ?>
                                            </span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small mb-1 text-muted">min</label>
                                                <input
                                                    type="number"
                                                    inputmode="decimal"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control js-min-input text-end"
                                                    value="<?= $minQty === '' ? '' : htmlspecialchars((string) $minQty) ?>"
                                                    placeholder="—"
                                                >
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small mb-1 text-muted">max</label>
                                                <input
                                                    type="number"
                                                    inputmode="decimal"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control js-max-input text-end"
                                                    value="<?= $maxQty === '' ? '' : htmlspecialchars((string) $maxQty) ?>"
                                                    placeholder="—"
                                                >
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-danger w-100 mt-2 js-delete <?= $isConfigured ? '' : 'd-none' ?>">
                                            <i class="bi bi-trash me-1"></i>ลบการตั้งค่า
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($pagination->pageCount > 1): ?>
                    <div class="card-footer bg-white border-0 d-flex justify-content-center py-3">
                        <?= LinkPager::widget([
                            'pagination' => $pagination,
                            'firstPageLabel' => 'หน้าแรก',
                            'lastPageLabel' => 'หน้าสุดท้าย',
                            'options' => ['class' => 'pagination pagination-sm mb-0'],
                        ]) ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="smm-toast-container" class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1100;"></div>

<style>
.stock-min-max-table tbody tr.row-saved {
    animation: smmRowPulse 600ms ease-out;
}
@keyframes smmRowPulse {
    0%   { background-color: rgba(25, 135, 84, 0.18); }
    100% { background-color: transparent; }
}
.stock-min-max-table input.is-saving,
.stock-min-max-mobile input.is-saving {
    background-image: linear-gradient(90deg, rgba(13,110,253,0.08), rgba(13,110,253,0.18), rgba(13,110,253,0.08));
    background-size: 200% 100%;
    animation: smmInputSaving 900ms linear infinite;
}
@keyframes smmInputSaving {
    0%   { background-position: 0% 50%; }
    100% { background-position: -200% 50%; }
}
.stock-min-max-table input:focus,
.stock-min-max-mobile input:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.18);
    border-color: rgba(13, 110, 253, 0.6);
}
.stock-min-max-table input,
.stock-min-max-mobile input {
    transition: border-color 0.15s, box-shadow 0.15s, background-color 0.2s;
    min-height: 38px;
}
.stock-min-max-mobile input { min-height: 44px; font-size: 1rem; }
.status-badge { font-weight: 500; padding: 0.35em 0.6em; }
.stock-min-max-table .form-control-sm {
    padding: 0.3rem 0.5rem;
    font-variant-numeric: tabular-nums;
}
@media (prefers-reduced-motion: reduce) {
    .stock-min-max-table tbody tr.row-saved { animation: none; background-color: rgba(25, 135, 84, 0.12); }
    .stock-min-max-table input.is-saving,
    .stock-min-max-mobile input.is-saving { animation: none; background: rgba(13,110,253,0.08); }
}
</style>

<?php
$js = <<<JS
(function () {
    const SAVE_URL   = '{$saveUrl}';
    const DELETE_URL = '{$deleteUrl}';
    const CSRF       = '{$csrf}';
    const WAREHOUSE_ID = {$warehouse->id};

    function showToast(message, type) {
        const container = document.getElementById('smm-toast-container');
        if (!container) return;
        const bg = type === 'error' ? 'text-bg-danger' : 'text-bg-success';
        const icon = type === 'error' ? 'bi-x-circle' : 'bi-check-circle';
        const el = document.createElement('div');
        el.className = 'toast ' + bg + ' border-0 shadow';
        el.setAttribute('role', 'status');
        el.setAttribute('aria-live', 'polite');
        el.innerHTML = '<div class="d-flex"><div class="toast-body"><i class="bi ' + icon + ' me-2"></i>' + message + '</div><button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="ปิด"></button></div>';
        container.appendChild(el);
        const t = new bootstrap.Toast(el, { delay: 2000 });
        t.show();
        el.addEventListener('hidden.bs.toast', function () { el.remove(); });
    }

    function getRow(input) {
        return input.closest('tr') || input.closest('.list-group-item');
    }

    function getInputs(row) {
        return {
            min: row.querySelector('.js-min-input'),
            max: row.querySelector('.js-max-input'),
        };
    }

    function markSaving(row, on) {
        const { min, max } = getInputs(row);
        [min, max].forEach(i => i && i.classList.toggle('is-saving', !!on));
    }

    function setConfiguredVisual(row, configured) {
        const badge = row.querySelector('.status-badge');
        const del = row.querySelector('.js-delete');
        if (badge) {
            if (configured) {
                badge.textContent = 'ตั้งแล้ว';
                badge.classList.remove('text-bg-light', 'text-secondary');
                badge.classList.add('text-bg-success-subtle', 'text-success', 'border-success-subtle');
            } else {
                badge.textContent = 'ยังไม่ตั้ง';
                badge.classList.add('text-bg-light', 'text-secondary');
                badge.classList.remove('text-bg-success-subtle', 'text-success', 'border-success-subtle');
            }
        }
        if (del) del.classList.toggle('d-none', !configured);
        row.classList.toggle('is-configured', configured);
        row.classList.toggle('is-unconfigured', !configured);
    }

    function flashSaved(row) {
        if (row.classList.contains('row-saved')) {
            row.classList.remove('row-saved');
            void row.offsetWidth;
        }
        row.classList.add('row-saved');
    }

    function trySave(row) {
        const { min, max } = getInputs(row);
        if (!min || !max) return;
        const minVal = min.value.trim();
        const maxVal = max.value.trim();
        const itemCode = row.getAttribute('data-item-code');

        if (minVal === '' || maxVal === '') return;
        if (Number(minVal) < 0 || Number(maxVal) < 0) {
            showToast('ค่าต้องไม่ติดลบ', 'error');
            return;
        }
        if (Number(maxVal) < Number(minVal)) {
            showToast('max ต้องไม่น้อยกว่า min', 'error');
            return;
        }

        markSaving(row, true);
        const form = new FormData();
        form.append('warehouse_id', String(WAREHOUSE_ID));
        form.append('item_code', itemCode);
        form.append('min_qty', minVal);
        form.append('max_qty', maxVal);
        form.append('_csrf', CSRF);

        fetch(SAVE_URL, {
            method: 'POST',
            body: form,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.json())
            .then(data => {
                markSaving(row, false);
                if (data.status === 'success') {
                    setConfiguredVisual(row, true);
                    flashSaved(row);
                } else {
                    showToast(data.message || 'บันทึกไม่สำเร็จ', 'error');
                }
            })
            .catch(() => {
                markSaving(row, false);
                showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            });
    }

    function tryDelete(row) {
        const itemCode = row.getAttribute('data-item-code');
        if (!itemCode) return;
        if (!confirm('ยืนยันการลบการตั้งค่า min/max ของวัสดุนี้?')) return;

        const form = new FormData();
        form.append('warehouse_id', String(WAREHOUSE_ID));
        form.append('item_code', itemCode);
        form.append('_csrf', CSRF);

        fetch(DELETE_URL, {
            method: 'POST',
            body: form,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    const { min, max } = getInputs(row);
                    if (min) min.value = '';
                    if (max) max.value = '';
                    setConfiguredVisual(row, false);
                    showToast('ลบการตั้งค่าแล้ว', 'success');
                } else {
                    showToast(data.message || 'ลบไม่สำเร็จ', 'error');
                }
            })
            .catch(() => showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error'));
    }

    document.addEventListener('change', function (e) {
        const t = e.target;
        if (!t.classList || (!t.classList.contains('js-min-input') && !t.classList.contains('js-max-input'))) return;
        const row = getRow(t);
        if (row) trySave(row);
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        const t = e.target;
        if (!t.classList || (!t.classList.contains('js-min-input') && !t.classList.contains('js-max-input'))) return;
        e.preventDefault();
        const row = getRow(t);
        if (row) trySave(row);
    });

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.js-delete');
        if (!btn) return;
        const row = getRow(btn);
        if (row) tryDelete(row);
    });

    const switcher = document.getElementById('smm-warehouse-switcher');
    if (switcher) {
        switcher.addEventListener('change', function () {
            const target = this.value;
            if (target) window.location.href = target;
        });
    }
})();
JS;
$this->registerJs($js, View::POS_END);
?>

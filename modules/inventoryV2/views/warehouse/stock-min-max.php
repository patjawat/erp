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
/** @var string $categoryId */
/** @var array $categoryOptions [['code' => '...', 'title' => '...'], ...] */
/** @var app\modules\inventoryV2\models\Warehouse[] $accessibleWarehouses */
/** @var bool $hasInventoryRole */

$totals = $totals ?: ['total' => 0, 'configured' => 0, 'below_min' => 0, 'above_max' => 0];
$totalItems = (int) ($totals['total'] ?? 0);
$configuredItems = (int) ($totals['configured'] ?? 0);
$unconfiguredItems = max(0, $totalItems - $configuredItems);
$belowMinItems = (int) ($totals['below_min'] ?? 0);
$aboveMaxItems = (int) ($totals['above_max'] ?? 0);
$coveragePct = $totalItems > 0 ? round(($configuredItems / $totalItems) * 100) : 0;
$categoryOptions = $categoryOptions ?? [];
$baseUrl = ['/inventory-v2/warehouse/stock-min-max', 'id' => $warehouse->id];

$this->title = 'ตั้ง min/max วัสดุ: ' . $warehouse->warehouse_name;
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าคลัง', 'url' => ['/inventory-v2/default/setting']];
$this->params['breadcrumbs'][] = $warehouse->warehouse_name;

$saveUrl = Url::to(['/inventory-v2/warehouse/save-setting']);
$deleteUrl = Url::to(['/inventory-v2/warehouse/delete-setting']);
$csrf = Yii::$app->request->csrfToken;
?>

<?php
$groups = ['MAIN' => [], 'SUB' => [], 'BRANCH' => [], 'OTHER' => []];
foreach ($accessibleWarehouses as $w) {
    $key = in_array($w->warehouse_type, ['MAIN', 'SUB', 'BRANCH'], true) ? $w->warehouse_type : 'OTHER';
    $groups[$key][] = $w;
}
$groupLabels = ['MAIN' => 'คลังหลัก', 'SUB' => 'คลังย่อย', 'BRANCH' => 'รพ.สต.', 'OTHER' => 'อื่นๆ'];
$hasSwitcher = false;
foreach ($groups as $g) { if (!empty($g)) { $hasSwitcher = true; break; } }
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-sliders2 text-primary"></i>
        ตั้งจุดสั่งซื้อขั้นต่ำ / ขั้นสูง
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['/inventory-v2/default/setting'], [
    'class' => 'btn btn-outline-secondary btn-sm',
]) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid px-3 px-md-4 py-3 py-md-4 stock-min-max-page" style="font-family: 'Sarabun', sans-serif;">

    <!-- Warehouse context bar — เห็นชัดว่ากำลังตั้งค่า "คลังไหน" + switch ได้สะดวก -->
    <div class="smm-context-bar d-flex flex-wrap align-items-center gap-2 mb-3 p-3 bg-light border rounded-3">
        <div class="d-flex align-items-center gap-2 flex-grow-1">
            <span class="smm-context-icon d-inline-flex align-items-center justify-content-center rounded-circle bg-primary-subtle text-primary" style="width:42px;height:42px;">
                <i class="bi bi-shop fs-5"></i>
            </span>
            <div class="lh-sm">
                <div class="text-muted small">กำลังตั้งค่าคลัง</div>
                <div class="fw-semibold fs-6"><?= Html::encode($warehouse->warehouse_name) ?></div>
            </div>
        </div>
        <?php if ($hasSwitcher): ?>
            <div class="d-flex align-items-center gap-2">
                <label for="smm-warehouse-switcher" class="form-label small text-muted mb-0 d-none d-sm-inline">
                    <i class="bi bi-shuffle me-1"></i>เปลี่ยนคลัง
                </label>
                <select id="smm-warehouse-switcher" class="form-select form-select-sm" style="min-width: 220px; max-width: 320px;" aria-label="เปลี่ยนคลังที่กำลังตั้งค่า">
                    <?php foreach ($groups as $type => $list): ?>
                        <?php if (empty($list)) continue; ?>
                        <optgroup label="<?= Html::encode($groupLabels[$type]) ?> (<?= count($list) ?>)">
                            <?php foreach ($list as $w): ?>
                                <option value="<?= Url::to(['/inventory-v2/warehouse/stock-min-max', 'id' => $w->id]) ?>" <?= $w->id === $warehouse->id ? 'selected' : '' ?>>
                                    <?= Html::encode($w->warehouse_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
                <?php if ($hasInventoryRole): ?>
                    <span class="badge text-bg-primary-subtle text-primary border border-primary-subtle d-none d-lg-inline-flex align-items-center" title="คุณมีสิทธิ์ระดับ inventory จึงเห็นทุกคลัง">
                        <i class="bi bi-shield-check me-1"></i>inventory
                    </span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Summary tiles — clickable filter -->
    <div class="row g-2 g-md-3 mb-3 stock-min-max-summary">
        <?php
        $tiles = [
            ['key' => 'all', 'label' => 'วัสดุทั้งหมด', 'value' => $totalItems, 'tone' => 'body', 'icon' => 'bi-box-seam'],
            ['key' => 'configured', 'label' => 'ตั้งค่าแล้ว', 'value' => $configuredItems, 'tone' => 'success', 'icon' => 'bi-check2-circle'],
            ['key' => 'unconfigured', 'label' => 'ยังไม่ตั้ง', 'value' => $unconfiguredItems, 'tone' => 'secondary', 'icon' => 'bi-circle'],
            ['key' => 'below_min', 'label' => 'ต่ำกว่า Min ตอนนี้', 'value' => $belowMinItems, 'tone' => 'danger', 'icon' => 'bi-exclamation-triangle'],
            ['key' => 'coverage', 'label' => 'ความครอบคลุม', 'value' => $coveragePct . '%', 'tone' => 'primary', 'icon' => 'bi-graph-up', 'static' => true],
        ];
        foreach ($tiles as $t):
            $isActive = !empty($t['static']) ? false : ($status === $t['key']);
            $url = !empty($t['static']) ? null : Url::to(array_merge($baseUrl, ['status' => $t['key'], 'q' => $q, 'category_id' => $categoryId]));
            $tagOpen = $url ? '<a href="' . $url . '" class="smm-tile-link"' . ($isActive ? ' aria-current="true"' : '') . '>' : '<div class="smm-tile-link smm-tile-static">';
            $tagClose = $url ? '</a>' : '</div>';
        ?>
            <div class="col-6 col-md">
                <?= $tagOpen ?>
                    <div class="smm-tile smm-tile-<?= $t['tone'] ?><?= $isActive ? ' is-active' : '' ?>">
                        <div class="d-flex align-items-start justify-content-between mb-1">
                            <div class="text-muted small"><?= Html::encode($t['label']) ?></div>
                            <i class="bi <?= $t['icon'] ?> smm-tile-icon" aria-hidden="true"></i>
                        </div>
                        <div class="fs-3 fw-semibold smm-tile-value"><?= is_numeric($t['value']) ? number_format($t['value']) : $t['value'] ?></div>
                    </div>
                <?= $tagClose ?>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Toolbar inline (ไม่ใช่ card แยก)
         หมายเหตุ: form method=get จะ strip query string ของ action URL ตาม HTML5 spec
         จึงต้องส่ง id ผ่าน hidden input ไม่งั้น controller จะหา warehouse ไม่เจอ -->
    <form method="get" action="<?= Url::to(['/inventory-v2/warehouse/stock-min-max']) ?>" class="smm-toolbar row g-2 align-items-end mb-3">
        <input type="hidden" name="id" value="<?= (int) $warehouse->id ?>">
        <div class="col-12 col-md-5 col-lg-4">
            <label class="form-label small mb-1 text-muted">ค้นหารหัส / ชื่อวัสดุ</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                <input type="text" class="form-control" name="q" value="<?= Html::encode($q) ?>" placeholder="พิมพ์รหัสหรือชื่อ">
            </div>
        </div>
        <div class="col-6 col-md-3 col-lg-3">
            <label class="form-label small mb-1 text-muted">สถานะ</label>
            <select class="form-select form-select-sm" name="status">
                <option value="all"          <?= $status === 'all' ? 'selected' : '' ?>>ทั้งหมด</option>
                <option value="configured"   <?= $status === 'configured' ? 'selected' : '' ?>>ตั้งค่าแล้ว</option>
                <option value="unconfigured" <?= $status === 'unconfigured' ? 'selected' : '' ?>>ยังไม่ตั้ง</option>
                <option value="below_min"    <?= $status === 'below_min' ? 'selected' : '' ?>>ต่ำกว่า Min</option>
                <option value="above_max"    <?= $status === 'above_max' ? 'selected' : '' ?>>เกิน Max</option>
            </select>
        </div>
        <?php if (!empty($categoryOptions)): ?>
        <div class="col-6 col-md-2 col-lg-3">
            <label class="form-label small mb-1 text-muted">หมวดวัสดุ</label>
            <select class="form-select form-select-sm" name="category_id">
                <option value="">ทุกหมวด</option>
                <?php foreach ($categoryOptions as $c): ?>
                    <option value="<?= Html::encode($c['code']) ?>" <?= $categoryId === (string) $c['code'] ? 'selected' : '' ?>>
                        <?= Html::encode($c['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                <i class="bi bi-funnel me-1"></i>กรอง
            </button>
            <?php if ($q !== '' || $status !== 'all' || $categoryId !== ''): ?>
                <a href="<?= Url::to($baseUrl) ?>" class="btn btn-outline-secondary btn-sm" title="ล้างตัวกรอง" aria-label="ล้างตัวกรอง">
                    <i class="bi bi-x-lg"></i>
                </a>
            <?php endif; ?>
        </div>
    </form>

    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="smm-table-header d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2 border-bottom">
                    <h6 class="mb-0 text-body fw-semibold d-flex align-items-center gap-2">
                        <i class="bi bi-list-ul text-primary"></i>
                        รายการวัสดุ
                        <span class="badge rounded-pill text-bg-secondary fw-normal"><?= number_format($pagination->totalCount) ?></span>
                    </h6>
                    <small class="text-muted d-none d-md-inline">
                        <i class="bi bi-info-circle me-1"></i>
                        กรอกแล้วกด Tab/Enter → บันทึกอัตโนมัติ
                    </small>
                </div>

                <div class="p-0">
                    <?php if (empty($rows)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            ไม่พบรายการวัสดุที่ตรงกับเงื่อนไข
                        </div>
                    <?php else: ?>
                        <?php
                        // ตัวช่วยตัดสิน status semantic + badge class
                        $resolveStatus = function ($isConfigured, $balance, $min, $max) {
                            if (!$isConfigured) {
                                return ['key' => 'unset', 'label' => 'ยังไม่ตั้ง', 'badge' => 'text-bg-light text-secondary border'];
                            }
                            if ($balance < $min) {
                                return ['key' => 'below', 'label' => 'ต่ำกว่า Min', 'badge' => 'text-bg-danger-subtle text-danger border border-danger-subtle'];
                            }
                            if ($balance > $max) {
                                return ['key' => 'above', 'label' => 'เกิน Max', 'badge' => 'text-bg-warning-subtle text-warning border border-warning-subtle'];
                            }
                            return ['key' => 'ok', 'label' => 'ปกติ', 'badge' => 'text-bg-success-subtle text-success border border-success-subtle'];
                        };
                        $renderBalance = function ($balance, $isConfigured, $min, $max) {
                            $tone = 'text-body';
                            if ($isConfigured) {
                                if ($balance < $min)      $tone = 'text-danger fw-semibold';
                                elseif ($balance > $max)  $tone = 'text-warning fw-semibold';
                                else                      $tone = 'text-success';
                            }
                            return '<span class="' . $tone . '">' . number_format((float) $balance, ((float) $balance == (int) $balance) ? 0 : 2) . '</span>';
                        };
                        ?>
                        <div class="d-none d-md-block table-responsive">
                            <table class="table table-hover align-middle mb-0 stock-min-max-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;">#</th>
                                        <th>รหัส / ชื่อวัสดุ</th>
                                        <th style="width: 80px;">หน่วย</th>
                                        <th class="text-end" style="width: 110px;">คงเหลือ</th>
                                        <th class="text-end" style="width: 130px;">Min</th>
                                        <th class="text-end" style="width: 130px;">Max</th>
                                        <th class="text-center" style="width: 120px;">สถานะ</th>
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
                                        $balance = (float) ($r['balance_qty'] ?? 0);
                                        $st = $resolveStatus($isConfigured, $balance, $isConfigured ? (float) $minQty : 0, $isConfigured ? (float) $maxQty : 0);
                                        $rowNumber = $pagination->offset + $i + 1;
                                    ?>
                                        <tr data-item-code="<?= Html::encode($r['item_code']) ?>" data-balance="<?= htmlspecialchars((string) (float) ($r['balance_qty'] ?? 0)) ?>" class="<?= $isConfigured ? 'is-configured' : 'is-unconfigured' ?> smm-row-<?= $st['key'] ?>">
                                            <td class="text-muted small"><?= $rowNumber ?></td>
                                            <td>
                                                <div class="fw-semibold"><?= Html::encode($r['item_name']) ?></div>
                                                <code class="text-muted small"><?= Html::encode($r['item_code']) ?></code>
                                            </td>
                                            <td class="text-muted small"><?= Html::encode($unitName) ?></td>
                                            <td class="text-end font-monospace">
                                                <?= $renderBalance($balance, $isConfigured, $isConfigured ? (float) $minQty : 0, $isConfigured ? (float) $maxQty : 0) ?>
                                            </td>
                                            <td class="text-end">
                                                <input
                                                    type="number"
                                                    inputmode="decimal"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control form-control-sm text-end js-min-input"
                                                    value="<?= $minQty === '' ? '' : htmlspecialchars((string) $minQty) ?>"
                                                    placeholder="—"
                                                    aria-label="Min ของ <?= Html::encode($r['item_name']) ?>"
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
                                                    aria-label="Max ของ <?= Html::encode($r['item_name']) ?>"
                                                >
                                            </td>
                                            <td class="text-center">
                                                <span class="badge status-badge <?= $st['badge'] ?>">
                                                    <?= $st['label'] ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <button type="button" class="btn btn-sm btn-link text-danger js-delete <?= $isConfigured ? '' : 'd-none' ?>" title="ลบการตั้งค่า" aria-label="ลบการตั้งค่า Min/Max ของ <?= Html::encode($r['item_name']) ?>">
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
                                    $balance = (float) ($r['balance_qty'] ?? 0);
                                    $st = $resolveStatus($isConfigured, $balance, $isConfigured ? (float) $minQty : 0, $isConfigured ? (float) $maxQty : 0);
                                ?>
                                    <div class="list-group-item py-3 smm-row-<?= $st['key'] ?>" data-item-code="<?= Html::encode($r['item_code']) ?>" data-balance="<?= htmlspecialchars((string) (float) ($r['balance_qty'] ?? 0)) ?>">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1 pe-2">
                                                <div class="fw-semibold"><?= Html::encode($r['item_name']) ?></div>
                                                <code class="text-muted small"><?= Html::encode($r['item_code']) ?></code>
                                                <span class="text-muted small ms-2"><?= Html::encode($unitName) ?></span>
                                            </div>
                                            <span class="badge status-badge <?= $st['badge'] ?>">
                                                <?= $st['label'] ?>
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-2 small">
                                            <span class="text-muted">คงเหลือตอนนี้:</span>
                                            <span class="font-monospace">
                                                <?= $renderBalance($balance, $isConfigured, $isConfigured ? (float) $minQty : 0, $isConfigured ? (float) $maxQty : 0) ?>
                                                <span class="text-muted ms-1"><?= Html::encode($unitName) ?></span>
                                            </span>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small mb-1 text-muted">Min</label>
                                                <input
                                                    type="number"
                                                    inputmode="decimal"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control js-min-input text-end"
                                                    value="<?= $minQty === '' ? '' : htmlspecialchars((string) $minQty) ?>"
                                                    placeholder="—"
                                                    aria-label="Min ของ <?= Html::encode($r['item_name']) ?>"
                                                >
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small mb-1 text-muted">Max</label>
                                                <input
                                                    type="number"
                                                    inputmode="decimal"
                                                    step="0.01"
                                                    min="0"
                                                    class="form-control js-max-input text-end"
                                                    value="<?= $maxQty === '' ? '' : htmlspecialchars((string) $maxQty) ?>"
                                                    placeholder="—"
                                                    aria-label="Max ของ <?= Html::encode($r['item_name']) ?>"
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
/* ===== Context bar ===== */
.stock-min-max-page .smm-context-bar { background-color: #f8f9fa; }

/* ===== Summary tiles ===== */
.stock-min-max-page .smm-tile-link {
    display: block;
    text-decoration: none;
    color: inherit;
}
.stock-min-max-page .smm-tile {
    background: #fff;
    border: 1px solid rgba(0,0,0,0.06);
    border-radius: 10px;
    padding: 0.85rem 1rem;
    height: 100%;
    transition: box-shadow 160ms ease-out, border-color 160ms ease-out, transform 160ms ease-out;
}
.stock-min-max-page .smm-tile-link:hover .smm-tile {
    border-color: rgba(13, 110, 253, 0.35);
    box-shadow: 0 0.4rem 0.9rem rgba(0,0,0,0.06);
    transform: translateY(-1px);
}
.stock-min-max-page .smm-tile-link:focus-visible .smm-tile {
    outline: 2px solid rgba(13, 110, 253, 0.45);
    outline-offset: 2px;
}
.stock-min-max-page .smm-tile.is-active {
    border-color: rgba(13, 110, 253, 0.6);
    box-shadow: inset 0 0 0 1px rgba(13, 110, 253, 0.3);
}
.stock-min-max-page .smm-tile-icon { color: rgba(0,0,0,0.18); font-size: 1rem; }
.stock-min-max-page .smm-tile-static { cursor: default; }
.stock-min-max-page .smm-tile-success .smm-tile-value { color: var(--bs-success); }
.stock-min-max-page .smm-tile-secondary .smm-tile-value { color: var(--bs-secondary); }
.stock-min-max-page .smm-tile-danger .smm-tile-value { color: var(--bs-danger); }
.stock-min-max-page .smm-tile-primary .smm-tile-value { color: var(--bs-primary); }
.stock-min-max-page .smm-tile-danger.is-active,
.stock-min-max-page .smm-tile-link:hover .smm-tile-danger {
    border-color: rgba(220, 53, 69, 0.45);
}

/* ===== Toolbar ===== */
.stock-min-max-page .smm-toolbar .input-group-text { border-right: 0; }
.stock-min-max-page .smm-toolbar .form-control,
.stock-min-max-page .smm-toolbar .form-select { font-size: 0.875rem; }

/* ===== Table ===== */
.stock-min-max-page .smm-table-header { background: #fff; border-radius: 0; }
.stock-min-max-table tbody tr.row-saved { animation: smmRowPulse 600ms ease-out; }
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
.stock-min-max-table .font-monospace,
.stock-min-max-mobile .font-monospace {
    font-variant-numeric: tabular-nums;
    font-family: 'SFMono-Regular', 'Menlo', 'Monaco', 'Consolas', monospace;
    font-size: 0.9em;
}

/* Row-state tint — เบามาก ไม่รบกวน input */
.stock-min-max-table tbody tr.smm-row-below > td { box-shadow: inset 3px 0 0 var(--bs-danger); }
.stock-min-max-table tbody tr.smm-row-above > td { box-shadow: inset 3px 0 0 var(--bs-warning); }
.stock-min-max-mobile .list-group-item.smm-row-below { border-left: 3px solid var(--bs-danger); }
.stock-min-max-mobile .list-group-item.smm-row-above { border-left: 3px solid var(--bs-warning); }

/* ===== Touch targets (mobile a11y) ===== */
@media (max-width: 575.98px) {
    .stock-min-max-page .smm-tile { padding: 0.75rem; }
    .stock-min-max-page .smm-tile-value { font-size: 1.5rem !important; }
    .stock-min-max-mobile .js-delete { min-height: 44px; }
    #smm-warehouse-switcher { min-height: 38px; }
}

@media (prefers-reduced-motion: reduce) {
    .stock-min-max-page .smm-tile,
    .stock-min-max-page .smm-tile-link:hover .smm-tile { transition: none; transform: none; }
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

    const STATUS_CLASSES = [
        'text-bg-light', 'text-secondary',
        'text-bg-success-subtle', 'text-success', 'border-success-subtle',
        'text-bg-danger-subtle', 'text-danger', 'border-danger-subtle',
        'text-bg-warning-subtle', 'text-warning', 'border-warning-subtle',
        'border',
    ];
    const STATUS_MAP = {
        unset: { label: 'ยังไม่ตั้ง', add: ['text-bg-light', 'text-secondary', 'border'] },
        ok:    { label: 'ปกติ',       add: ['text-bg-success-subtle', 'text-success', 'border', 'border-success-subtle'] },
        below: { label: 'ต่ำกว่า Min', add: ['text-bg-danger-subtle', 'text-danger', 'border', 'border-danger-subtle'] },
        above: { label: 'เกิน Max',   add: ['text-bg-warning-subtle', 'text-warning', 'border', 'border-warning-subtle'] },
    };

    function resolveStatusKey(configured, balance, min, max) {
        if (!configured) return 'unset';
        if (balance < min) return 'below';
        if (balance > max) return 'above';
        return 'ok';
    }

    function setConfiguredVisual(row, configured, min, max) {
        const balance = parseFloat(row.getAttribute('data-balance') || '0') || 0;
        const key = resolveStatusKey(configured, balance, Number(min) || 0, Number(max) || 0);
        const badge = row.querySelector('.status-badge');
        const del = row.querySelector('.js-delete');
        if (badge) {
            badge.classList.remove(...STATUS_CLASSES);
            badge.classList.add(...STATUS_MAP[key].add);
            badge.textContent = STATUS_MAP[key].label;
        }
        if (del) del.classList.toggle('d-none', !configured);
        row.classList.toggle('is-configured', configured);
        row.classList.toggle('is-unconfigured', !configured);
        row.classList.remove('smm-row-unset', 'smm-row-ok', 'smm-row-below', 'smm-row-above');
        row.classList.add('smm-row-' + key);
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
                    const m = data.data && data.data.min_qty != null ? data.data.min_qty : minVal;
                    const x = data.data && data.data.max_qty != null ? data.data.max_qty : maxVal;
                    setConfiguredVisual(row, true, m, x);
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
                    setConfiguredVisual(row, false, 0, 0);
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

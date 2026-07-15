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

$totals = $totals ?: ['total' => 0, 'configured' => 0, 'below_min' => 0, 'above_max' => 0];
$totalItems = (int) ($totals['total'] ?? 0);
$configuredItems = (int) ($totals['configured'] ?? 0);
$belowMinItems = (int) ($totals['below_min'] ?? 0);
$aboveMaxItems = (int) ($totals['above_max'] ?? 0);
$coveragePct = $totalItems > 0 ? round(($configuredItems / $totalItems) * 100) : 0;
$categoryOptions = $categoryOptions ?? [];
$baseUrl = ['/inventory-v2/warehouse/stock-min-max', 'id' => $warehouse->id];

// ตัวกรองที่ active — แนบไปกับลิงก์ export เพื่อให้ Excel ส่งออกตรงกับที่เห็นบนหน้าจอ
$exportFilterParams = array_filter([
    'q' => $q,
    'status' => $status,
    'category_id' => $categoryId,
], static fn($v) => $v !== '' && $v !== null);
$hasExportFilter = !empty($exportFilterParams);

$this->title = 'ตั้ง min/max วัสดุ: ' . $warehouse->warehouse_name;
$this->params['breadcrumbs'][] = ['label' => 'ตั้งค่าคลัง', 'url' => ['/inventory-v2/default/setting']];
$this->params['breadcrumbs'][] = $warehouse->warehouse_name;

$saveUrl = Url::to(['/inventory-v2/warehouse/save-setting']);
$deleteUrl = Url::to(['/inventory-v2/warehouse/delete-setting']);
$saveBatchUrl = Url::to(['/inventory-v2/warehouse/save-setting-batch']);
$copyPreviewUrl = Url::to(['/inventory-v2/warehouse/copy-from-preview']);
$copyUrl = Url::to(['/inventory-v2/warehouse/copy-from']);
$importPreviewUrl = Url::to(['/inventory-v2/warehouse/import-preview', 'id' => $warehouse->id]);
$historyPreviewUrl = Url::to(['/inventory-v2/warehouse/history-min-max-preview', 'id' => $warehouse->id]);
$historyApplyUrl = Url::to(['/inventory-v2/warehouse/history-min-max-apply', 'id' => $warehouse->id]);
$candidateItemsUrl = Url::to(['/inventory-v2/warehouse/candidate-items', 'id' => $warehouse->id]);
$addItemsUrl = Url::to(['/inventory-v2/warehouse/add-items']);
$deleteBatchUrl = Url::to(['/inventory-v2/warehouse/delete-settings-batch']);
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
            </div>
        <?php endif; ?>
    </div>

    <!-- Summary tiles — clickable filter -->
    <div class="row g-2 g-md-3 mb-3 stock-min-max-summary">
        <?php
        $tiles = [
            ['key' => 'configured', 'label' => 'ตั้งค่าแล้ว', 'value' => $configuredItems, 'tone' => 'success', 'icon' => 'bi-check2-circle'],
            ['key' => 'below_min', 'label' => 'ต่ำกว่า Min ตอนนี้', 'value' => $belowMinItems, 'tone' => 'danger', 'icon' => 'bi-exclamation-triangle'],
            ['key' => 'above_max', 'label' => 'เกิน Max ตอนนี้', 'value' => $aboveMaxItems, 'tone' => 'warning', 'icon' => 'bi-arrow-up-circle'],
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
                <option value="configured"   <?= $status === 'configured' ? 'selected' : '' ?>>ตั้งค่าแล้วทั้งหมด</option>
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
            <?php if ($q !== '' || $status !== 'configured' || $categoryId !== ''): ?>
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
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <h6 class="mb-0 text-body fw-semibold d-flex align-items-center gap-2">
                            <i class="bi bi-list-ul text-primary"></i>
                            รายการวัสดุ
                            <span class="badge rounded-pill text-bg-secondary fw-normal"><?= number_format($pagination->totalCount) ?></span>
                        </h6>
                        <button type="button" class="btn btn-outline-danger btn-sm d-none" id="smm-header-delete" title="ลบการตั้งค่า Min/Max ของรายการที่เลือก">
                            <i class="bi bi-trash me-1"></i>ลบที่เลือก (<span class="js-sel-count">0</span>)
                        </button>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <!-- Mode toggle: ทีละช่อง autosave vs เลือกหลายรายการ manual -->
                        <div class="btn-group btn-group-sm smm-mode-toggle" role="group" aria-label="โหมดบันทึก">
                            <input type="radio" class="btn-check" name="smm-mode" id="smm-mode-auto" value="auto" checked>
                            <label class="btn btn-outline-primary" for="smm-mode-auto" title="กรอกแล้ว Tab/Enter บันทึกทันที">
                                <i class="bi bi-lightning-charge me-1"></i>ทีละช่อง · autosave
                            </label>
                            <input type="radio" class="btn-check" name="smm-mode" id="smm-mode-batch" value="batch">
                            <label class="btn btn-outline-primary" for="smm-mode-batch" title="ติ๊กเลือกแล้วบันทึกพร้อมกัน">
                                <i class="bi bi-check2-square me-1"></i>เลือกหลายรายการ
                            </label>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#smm-add-items-modal" title="เลือกหมวด → ติ๊กรายการที่ต้องการ → เพิ่มเข้าตาราง (Min/Max เริ่มต้น 0 — ค่อยกรอกในตาราง)">
                            <i class="bi bi-plus-lg me-1"></i>เพิ่มวัสดุเข้าตาราง
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#smm-history-modal" title="โหลดวัสดุจากประวัติการเบิกและคำนวณ Min/Max ตั้งต้น">
                            <i class="bi bi-magic me-1"></i>คำนวณจากประวัติเบิก
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#smm-copy-from-modal" title="คัดลอกค่า Min/Max จากคลังอื่น">
                            <i class="bi bi-clipboard-plus me-1"></i>คัดลอกจากคลัง
                        </button>
                        <!-- Excel dropdown -->
                        <div class="dropdown">
                            <button type="button" class="btn btn-outline-success btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-file-earmark-spreadsheet me-1"></i>Excel
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <?php if ($hasExportFilter): ?>
                                    <li>
                                        <h6 class="dropdown-header d-flex align-items-center gap-1">
                                            <i class="bi bi-funnel-fill text-primary"></i>ส่งออกเฉพาะรายการที่กรองอยู่
                                        </h6>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <a class="dropdown-item" href="<?= Url::to(array_merge(['/inventory-v2/warehouse/export-settings', 'id' => $warehouse->id, 'mode' => 'template'], $exportFilterParams)) ?>">
                                        <i class="bi bi-download me-2 text-muted"></i>ดาวน์โหลด Template (ว่าง)
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="<?= Url::to(array_merge(['/inventory-v2/warehouse/export-settings', 'id' => $warehouse->id, 'mode' => 'snapshot'], $exportFilterParams)) ?>">
                                        <i class="bi bi-download me-2 text-muted"></i>ดาวน์โหลด Snapshot (ค่าปัจจุบัน)
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#smm-import-modal">
                                        <i class="bi bi-upload me-2 text-primary"></i>นำเข้า Excel...
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="p-0">
                    <?php if (empty($rows)): ?>
                        <div class="text-center text-muted py-5">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            <?php if ($q === '' && $status === 'configured' && $categoryId === ''): ?>
                                <div class="mb-3">ยังไม่มีวัสดุที่ตั้งค่า Min/Max ในคลังนี้</div>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#smm-add-items-modal">
                                    <i class="bi bi-plus-lg me-1"></i>เพิ่มวัสดุเข้าตาราง
                                </button>
                            <?php else: ?>
                                ไม่พบรายการวัสดุที่ตรงกับเงื่อนไข
                            <?php endif; ?>
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
                                        <th class="text-center" style="width: 40px;">
                                            <input type="checkbox" class="form-check-input js-select-all" aria-label="เลือกทั้งหน้า">
                                        </th>
                                        <th style="width: 50px;">#</th>
                                        <th>รหัส / ชื่อวัสดุ</th>
                                        <th style="width: 150px;">หมวดหมู่</th>
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
                                        $unitName = $itemDataJson['unit'] ?? '-';
                                        $isConfigured = !empty($r['setting_id']);
                                        $minQty = $isConfigured ? (float) $r['setting_min_qty'] : '';
                                        $maxQty = $isConfigured ? (float) $r['setting_max_qty'] : '';
                                        $balance = (float) ($r['balance_qty'] ?? 0);
                                        $st = $resolveStatus($isConfigured, $balance, $isConfigured ? (float) $minQty : 0, $isConfigured ? (float) $maxQty : 0);
                                        $rowNumber = $pagination->offset + $i + 1;
                                    ?>
                                        <tr data-item-code="<?= Html::encode($r['item_code']) ?>" data-balance="<?= htmlspecialchars((string) (float) ($r['balance_qty'] ?? 0)) ?>" class="<?= $isConfigured ? 'is-configured' : 'is-unconfigured' ?> smm-row-<?= $st['key'] ?>">
                                            <td class="text-center">
                                                <input type="checkbox" class="form-check-input js-row-select" aria-label="เลือกรายการ">
                                            </td>
                                            <td class="text-muted small"><?= $rowNumber ?></td>
                                            <td>
                                                <div class="fw-semibold"><?= Html::encode($r['item_name']) ?></div>
                                                <code class="text-muted small"><?= Html::encode($r['item_code']) ?></code>
                                            </td>
                                            <td class="text-muted small"><?= Html::encode($r['category_title'] ?? '-') ?></td>
                                            <td class="text-muted small"><?= Html::encode($unitName) ?></td>
                                            <td class="text-end font-monospace">
                                                <?= $renderBalance($balance, $isConfigured, $isConfigured ? (float) $minQty : 0, $isConfigured ? (float) $maxQty : 0) ?>
                                            </td>
                                            <td class="text-end">
                                                <input
                                                    type="number"
                                                    inputmode="numeric"
                                                    step="1"
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
                                                    inputmode="numeric"
                                                    step="1"
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
                                    $unitName = $itemDataJson['unit'] ?? $itemDataJson['unit_name'] ?? '-';
                                    $isConfigured = !empty($r['setting_id']);
                                    $minQty = $isConfigured ? (float) $r['setting_min_qty'] : '';
                                    $maxQty = $isConfigured ? (float) $r['setting_max_qty'] : '';
                                    $balance = (float) ($r['balance_qty'] ?? 0);
                                    $st = $resolveStatus($isConfigured, $balance, $isConfigured ? (float) $minQty : 0, $isConfigured ? (float) $maxQty : 0);
                                ?>
                                    <div class="list-group-item py-3 smm-row-<?= $st['key'] ?>" data-item-code="<?= Html::encode($r['item_code']) ?>" data-balance="<?= htmlspecialchars((string) (float) ($r['balance_qty'] ?? 0)) ?>">
                                        <div class="mb-2">
                                            <label class="d-flex align-items-center gap-2 small text-muted">
                                                <input type="checkbox" class="form-check-input js-row-select">
                                                เลือกรายการนี้
                                            </label>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div class="flex-grow-1 pe-2">
                                                <div class="fw-semibold"><?= Html::encode($r['item_name']) ?></div>
                                                <code class="text-muted small"><?= Html::encode($r['item_code']) ?></code>
                                                <span class="text-muted small ms-2"><?= Html::encode($unitName) ?></span>
                                                <?php if (!empty($r['category_title'])): ?>
                                                    <div class="text-muted small"><?= Html::encode($r['category_title']) ?></div>
                                                <?php endif; ?>
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
                                                    inputmode="numeric"
                                                    step="1"
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
                                                    inputmode="numeric"
                                                    step="1"
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

<!-- Sticky bulk toolbar — โผล่เมื่อมี row ที่เลือก หรือ row dirty (batch mode) -->
<div id="smm-bulk-toolbar" class="smm-bulk-toolbar d-none" role="region" aria-label="จัดการหลายรายการพร้อมกัน">
    <div class="container-fluid">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 py-2 px-2 px-md-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge text-bg-primary rounded-pill" id="smm-selected-count">0</span>
                <span class="text-muted small">รายการที่เลือก</span>
                <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 ms-2" id="smm-clear-selection">
                    ยกเลิกเลือก
                </button>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="d-flex align-items-center gap-1 smm-batch-only">
                    <label class="small text-muted mb-0" for="smm-bulk-min">Min</label>
                    <input type="number" inputmode="numeric" step="1" min="0" id="smm-bulk-min"
                        class="form-control form-control-sm text-end" style="width: 90px;" placeholder="—">
                    <label class="small text-muted mb-0 ms-2" for="smm-bulk-max">Max</label>
                    <input type="number" inputmode="numeric" step="1" min="0" id="smm-bulk-max"
                        class="form-control form-control-sm text-end" style="width: 90px;" placeholder="—">
                    <button type="button" class="btn btn-outline-secondary btn-sm ms-1" id="smm-bulk-apply" title="ใส่ค่า Min/Max ที่กรอกให้ทุกรายการที่เลือก">
                        <i class="bi bi-arrow-down-square"></i> ใส่
                    </button>
                </div>
                <button type="button" class="btn btn-primary btn-sm fw-semibold smm-batch-only" id="smm-bulk-save">
                    <i class="bi bi-check2-circle me-1"></i>บันทึก (<span class="js-count">0</span>)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Copy from warehouse modal -->
<div class="modal fade" id="smm-copy-from-modal" tabindex="-1" aria-labelledby="smm-copy-from-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="smm-copy-from-title">
                    <i class="bi bi-clipboard-plus text-primary me-1"></i>
                    คัดลอกค่า Min/Max จากคลังอื่น
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="smm-copy-source" class="form-label small fw-semibold">คลังต้นทาง</label>
                    <select id="smm-copy-source" class="form-select">
                        <option value="">-- เลือกคลังต้นทาง --</option>
                        <?php foreach ($groups as $type => $list): ?>
                            <?php if (empty($list)) continue; ?>
                            <optgroup label="<?= Html::encode($groupLabels[$type]) ?>">
                                <?php foreach ($list as $w): ?>
                                    <?php if ($w->id === $warehouse->id) continue; // ไม่ให้เลือกตัวเอง ?>
                                    <option value="<?= (int) $w->id ?>"><?= Html::encode($w->warehouse_name) ?></option>
                                <?php endforeach; ?>
                            </optgroup>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div id="smm-copy-preview" class="d-none alert alert-light border small mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">มีในคลังต้นทาง (ที่ตรงประเภทคลังนี้):</span>
                        <strong id="smm-copy-source-total">0</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">ยังไม่ตั้งในคลังนี้:</span>
                        <strong id="smm-copy-target-new" class="text-success">0</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">ตั้งแล้วในคลังนี้:</span>
                        <strong id="smm-copy-target-existing" class="text-warning">0</strong>
                    </div>
                </div>
                <div id="smm-copy-empty" class="d-none alert alert-warning small mb-3">
                    คลังต้นทางไม่มีรายการที่จะคัดลอกได้
                </div>

                <fieldset id="smm-copy-mode-group" class="mb-2">
                    <legend class="form-label small fw-semibold">วิธีคัดลอก</legend>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="smm-copy-mode" id="smm-copy-mode-skip" value="skip" checked>
                        <label class="form-check-label small" for="smm-copy-mode-skip">
                            คัดลอกเฉพาะที่ยังไม่ตั้งในคลังนี้ <span class="text-muted">(แนะนำ)</span>
                        </label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="smm-copy-mode" id="smm-copy-mode-overwrite" value="overwrite">
                        <label class="form-check-label small" for="smm-copy-mode-overwrite">
                            คัดลอกทั้งหมด ทับค่าเดิม
                        </label>
                    </div>
                </fieldset>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="smm-copy-confirm" disabled>
                    <i class="bi bi-clipboard-check me-1"></i>คัดลอก
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add items modal — เลือกหมวด → ติ๊กรายการ → เพิ่มเข้าตาราง (default 0/0) -->
<div class="modal fade" id="smm-add-items-modal" tabindex="-1" aria-labelledby="smm-add-items-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="smm-add-items-title">
                    <i class="bi bi-plus-lg text-primary me-1"></i>
                    เพิ่มวัสดุเข้าตาราง Min/Max
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-12 col-md-5">
                        <label for="smm-add-category" class="form-label small mb-1 text-muted">หมวดวัสดุ</label>
                        <select id="smm-add-category" class="form-select form-select-sm">
                            <option value="">ทุกหมวดที่คลังนี้รับเข้า</option>
                            <?php foreach ($categoryOptions as $c): ?>
                                <option value="<?= Html::encode($c['code']) ?>"><?= Html::encode($c['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-7">
                        <label for="smm-add-q" class="form-label small mb-1 text-muted">ค้นหารหัส / ชื่อวัสดุ (เสริม)</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="smm-add-q" class="form-control" placeholder="พิมพ์เพื่อค้นเฉพาะรายการที่ต้องการ">
                        </div>
                    </div>
                </div>

                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                    <div class="small text-muted">
                        <span id="smm-add-status">เลือกหมวดเพื่อโหลดรายการ</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge text-bg-primary rounded-pill" id="smm-add-selected-count">0</span>
                        <span class="small text-muted">เลือกแล้ว</span>
                    </div>
                </div>

                <div class="table-responsive border rounded-3" style="max-height: 420px;">
                    <table class="table table-sm table-hover align-middle mb-0 smm-add-table">
                        <thead class="table-light position-sticky top-0">
                            <tr>
                                <th class="text-center" style="width:40px;">
                                    <input type="checkbox" class="form-check-input" id="smm-add-select-all" aria-label="เลือกทั้งหมด">
                                </th>
                                <th style="width:140px;">รหัส</th>
                                <th>ชื่อวัสดุ</th>
                                <th style="width:80px;">หน่วย</th>
                                <th style="width:160px;">หมวด</th>
                            </tr>
                        </thead>
                        <tbody id="smm-add-tbody">
                            <tr><td colspan="5" class="text-center text-muted py-4">ยังไม่มีข้อมูล</td></tr>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted d-block mt-2">
                    <i class="bi bi-info-circle me-1"></i>
                    Min/Max เริ่มต้นจะถูกตั้งเป็น 0 — กรอกค่าจริงในตารางหน้าหลักหลังเพิ่มแล้ว
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="smm-add-confirm" disabled>
                    <i class="bi bi-plus-lg me-1"></i>
                    เพิ่ม <span class="js-add-count">0</span> รายการ
                </button>
            </div>
        </div>
    </div>
</div>

<!-- History auto setup modal -->
<div class="modal fade" id="smm-history-modal" tabindex="-1" aria-labelledby="smm-history-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="smm-history-title">
                    <i class="bi bi-magic text-primary me-1"></i>
                    คำนวณ Min/Max จากประวัติเบิก
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3 align-items-end mb-3">
                    <div class="col-6 col-md-3">
                        <label for="smm-history-months" class="form-label small fw-semibold">ย้อนหลัง</label>
                        <select id="smm-history-months" class="form-select form-select-sm">
                            <option value="3">3 เดือน</option>
                            <option value="6" selected>6 เดือน</option>
                            <option value="12">12 เดือน</option>
                            <option value="24">24 เดือน</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="smm-history-min-factor" class="form-label small fw-semibold">Min = เฉลี่ย x</label>
                        <input type="number" id="smm-history-min-factor" class="form-control form-control-sm" min="0.1" max="12" step="0.1" value="1">
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="smm-history-max-factor" class="form-label small fw-semibold">Max = เฉลี่ย x</label>
                        <input type="number" id="smm-history-max-factor" class="form-control form-control-sm" min="0.1" max="24" step="0.1" value="2">
                    </div>
                    <div class="col-6 col-md-3 d-grid">
                        <button type="button" class="btn btn-outline-primary btn-sm" id="smm-history-preview">
                            <i class="bi bi-search me-1"></i>ดูตัวอย่าง
                        </button>
                    </div>
                </div>

                <div id="smm-history-summary" class="alert alert-light border d-none mb-3"></div>
                <div id="smm-history-empty" class="text-center text-muted py-4">
                    <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                    กดดูตัวอย่างเพื่อโหลดวัสดุจากประวัติเบิก
                </div>
                <div class="table-responsive d-none" id="smm-history-table-wrap">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>รหัส</th>
                                <th>รายการวัสดุ</th>
                                <th class="text-end">รวมเบิก</th>
                                <th class="text-end">เฉลี่ย/เดือน</th>
                                <th class="text-end">Min</th>
                                <th class="text-end">Max</th>
                                <th class="text-end">เอกสาร</th>
                            </tr>
                        </thead>
                        <tbody id="smm-history-preview-body"></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="smm-history-apply" disabled>
                    <i class="bi bi-check2-circle me-1"></i>บันทึกเข้าตาราง
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Excel Import modal -->
<div class="modal fade" id="smm-import-modal" tabindex="-1" aria-labelledby="smm-import-title" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="smm-import-title">
                    <i class="bi bi-upload text-primary me-1"></i>
                    นำเข้า Min/Max จาก Excel
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <!-- Step 1: Upload -->
                <div id="smm-import-step1" class="mb-3">
                    <label for="smm-import-file" class="form-label small fw-semibold">
                        <span class="badge text-bg-primary me-1">1</span>เลือกไฟล์ Excel/CSV
                    </label>
                    <div class="d-flex gap-2 align-items-center">
                        <input type="file" id="smm-import-file" class="form-control" accept=".xlsx,.xls,.csv">
                        <button type="button" class="btn btn-primary" id="smm-import-upload">
                            <i class="bi bi-search me-1"></i>ตรวจสอบ
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-info-circle me-1"></i>
                        แนะนำให้ดาวน์โหลด Template หรือ Snapshot ก่อน แล้วแก้ค่า Min/Max ในคอลัมน์ที่กำหนด — รองรับไฟล์ขนาดไม่เกิน 5MB / 2000 รายการ
                    </small>
                </div>

                <!-- Step 2: Preview -->
                <div id="smm-import-step2" class="d-none">
                    <div class="mb-2">
                        <span class="badge text-bg-primary me-1">2</span>
                        <strong>ตรวจรายการก่อนบันทึก</strong>
                    </div>
                    <div class="row g-2 mb-3" id="smm-import-summary">
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 text-center">
                                <div class="text-muted small">รายการใหม่</div>
                                <div class="fs-5 fw-semibold text-success" id="smm-cnt-new">0</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 text-center">
                                <div class="text-muted small">จะอัปเดต</div>
                                <div class="fs-5 fw-semibold text-primary" id="smm-cnt-update">0</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 text-center">
                                <div class="text-muted small">ไม่เปลี่ยน</div>
                                <div class="fs-5 fw-semibold text-secondary" id="smm-cnt-unchanged">0</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 text-center">
                                <div class="text-muted small">ข้อผิดพลาด</div>
                                <div class="fs-5 fw-semibold text-danger" id="smm-cnt-error">0</div>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 380px;">
                        <table class="table table-sm table-hover align-middle mb-0 smm-import-table">
                            <thead class="table-light position-sticky top-0">
                                <tr>
                                    <th style="width:60px;">แถว</th>
                                    <th>รหัส / ชื่อ</th>
                                    <th class="text-end" style="width:90px;">Min</th>
                                    <th class="text-end" style="width:90px;">Max</th>
                                    <th class="text-center" style="width:110px;">สถานะ</th>
                                    <th>หมายเหตุ / ปัญหา</th>
                                </tr>
                            </thead>
                            <tbody id="smm-import-preview-body"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer flex-wrap gap-2">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-outline-secondary d-none" id="smm-import-reset">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>เลือกไฟล์ใหม่
                </button>
                <button type="button" class="btn btn-primary d-none" id="smm-import-apply">
                    <i class="bi bi-check2-circle me-1"></i>
                    บันทึก <span class="js-apply-count">0</span> รายการ
                </button>
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
.stock-min-max-table input[type="number"]:focus,
.stock-min-max-mobile input[type="number"]:focus {
    box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.18);
    border-color: rgba(13, 110, 253, 0.6);
}
.stock-min-max-table input[type="number"],
.stock-min-max-mobile input[type="number"] {
    transition: border-color 0.15s, box-shadow 0.15s, background-color 0.2s;
    min-height: 38px;
}
.stock-min-max-mobile input[type="number"] { min-height: 44px; font-size: 1rem; }
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

/* ===== Mode toggle + batch UI ===== */
.smm-mode-toggle .btn { font-size: 0.8rem; }
.smm-mode-toggle .btn-check:checked + .btn { font-weight: 600; }
/* Batch-only UI ซ่อนใน auto mode (default) */
body:not(.smm-mode-batch) .smm-batch-only { display: none !important; }
.stock-min-max-page.smm-mode-batch .stock-min-max-table tr.is-selected,
.stock-min-max-page.smm-mode-batch .stock-min-max-mobile .list-group-item.is-selected {
    background-color: rgba(13, 110, 253, 0.04);
}
/* Dirty (มีการเปลี่ยน ยังไม่ save) — เห็นทั้งสอง mode */
.stock-min-max-table tr.is-dirty input[type="number"],
.stock-min-max-mobile .list-group-item.is-dirty input[type="number"] {
    background-color: rgba(255, 193, 7, 0.06);
    border-color: rgba(255, 193, 7, 0.5);
}

/* ===== Sticky bulk toolbar ===== */
.smm-bulk-toolbar {
    position: fixed;
    left: 0; right: 0; bottom: 0;
    background: #fff;
    border-top: 1px solid rgba(0,0,0,0.08);
    box-shadow: 0 -0.4rem 1rem rgba(0,0,0,0.06);
    z-index: 1040;
    animation: smmBulkSlide 200ms ease-out;
}
@keyframes smmBulkSlide {
    from { transform: translateY(100%); }
    to   { transform: translateY(0); }
}
.smm-bulk-toolbar .form-control { font-variant-numeric: tabular-nums; }
/* Spacing เพิ่มเมื่อ toolbar แสดงผล กันไม่ให้ทับ content ล่าง */
body.smm-bulk-visible { padding-bottom: 90px; }

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
    const SAVE_URL         = '{$saveUrl}';
    const DELETE_URL       = '{$deleteUrl}';
    const SAVE_BATCH_URL   = '{$saveBatchUrl}';
    const COPY_PREVIEW_URL = '{$copyPreviewUrl}';
    const COPY_URL         = '{$copyUrl}';
    const IMPORT_PREVIEW_URL = '{$importPreviewUrl}';
    const HISTORY_PREVIEW_URL = '{$historyPreviewUrl}';
    const HISTORY_APPLY_URL = '{$historyApplyUrl}';
    const CANDIDATE_ITEMS_URL = '{$candidateItemsUrl}';
    const ADD_ITEMS_URL    = '{$addItemsUrl}';
    const DELETE_BATCH_URL = '{$deleteBatchUrl}';
    const CSRF             = '{$csrf}';
    const WAREHOUSE_ID     = {$warehouse->id};
    const MODE_PREF_KEY    = 'inv2:smm:mode';

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

    // ===== Mode state =====
    function currentMode() {
        const checked = document.querySelector('input[name="smm-mode"]:checked');
        return checked ? checked.value : 'auto';
    }

    function applyModeUI(mode) {
        document.body.classList.toggle('smm-mode-batch', mode === 'batch');
        const page = document.querySelector('.stock-min-max-page');
        if (page) page.classList.toggle('smm-mode-batch', mode === 'batch');
        if (mode !== 'batch') {
            clearSelection();
        }
        try { localStorage.setItem(MODE_PREF_KEY, mode); } catch (e) {}
    }

    // restore mode pref
    try {
        const saved = localStorage.getItem(MODE_PREF_KEY);
        if (saved === 'batch') {
            const r = document.getElementById('smm-mode-batch');
            if (r) { r.checked = true; }
        }
    } catch (e) {}
    applyModeUI(currentMode());

    document.querySelectorAll('input[name="smm-mode"]').forEach(function (r) {
        r.addEventListener('change', function () {
            const next = this.value;
            if (next !== 'batch' && hasDirtyRows()) {
                if (!confirm('มีการเปลี่ยนแปลงที่ยังไม่บันทึก จะออกจากโหมดเลือกหลายรายการหรือไม่?')) {
                    document.getElementById('smm-mode-batch').checked = true;
                    return;
                }
                clearDirty();
            }
            applyModeUI(next);
        });
    });

    // ===== Input change (autosave or mark dirty) =====
    document.addEventListener('change', function (e) {
        const t = e.target;
        if (!t.classList || (!t.classList.contains('js-min-input') && !t.classList.contains('js-max-input'))) return;
        const row = getRow(t);
        if (!row) return;
        if (currentMode() === 'batch') {
            row.classList.add('is-dirty');
            updateBulkSaveCount();
        } else {
            trySave(row);
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        const t = e.target;
        if (!t.classList || (!t.classList.contains('js-min-input') && !t.classList.contains('js-max-input'))) return;
        e.preventDefault();
        const row = getRow(t);
        if (!row) return;
        if (currentMode() === 'batch') {
            row.classList.add('is-dirty');
            updateBulkSaveCount();
        } else {
            trySave(row);
        }
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
            if (hasDirtyRows() && !confirm('มีการเปลี่ยนแปลงที่ยังไม่บันทึก จะเปลี่ยนคลังหรือไม่?')) {
                this.value = window.location.pathname + window.location.search;
                return;
            }
            const target = this.value;
            if (target) window.location.href = target;
        });
    }

    // ===== Row selection (batch mode) =====
    function allRows() {
        return Array.from(document.querySelectorAll('tr[data-item-code], .list-group-item[data-item-code]'));
    }
    function selectedRows() {
        return allRows().filter(function (r) {
            const cb = r.querySelector('.js-row-select');
            return cb && cb.checked;
        });
    }
    // หน้านี้ render row ซ้ำ 2 view (table md+ / mobile <md) → unique โดย item_code
    function selectedCodes() {
        const set = new Set();
        selectedRows().forEach(function (r) {
            const c = r.getAttribute('data-item-code');
            if (c) set.add(c);
        });
        return Array.from(set);
    }
    function allCodes() {
        const set = new Set();
        allRows().forEach(function (r) {
            const c = r.getAttribute('data-item-code');
            if (c) set.add(c);
        });
        return Array.from(set);
    }
    function setCodeSelected(code, checked) {
        allRows().forEach(function (r) {
            if (r.getAttribute('data-item-code') !== code) return;
            const cb = r.querySelector('.js-row-select');
            if (cb) cb.checked = checked;
            r.classList.toggle('is-selected', checked);
        });
    }
    function updateSelectAllState() {
        const total = allCodes().length;
        const selected = selectedCodes().length;
        document.querySelectorAll('.js-select-all').forEach(function (cb) {
            cb.checked = total > 0 && selected === total;
            cb.indeterminate = selected > 0 && selected < total;
        });
    }
    function dirtyRows() {
        return allRows().filter(function (r) { return r.classList.contains('is-dirty'); });
    }
    function hasDirtyRows() { return dirtyRows().length > 0; }
    function clearDirty() {
        dirtyRows().forEach(function (r) { r.classList.remove('is-dirty'); });
    }
    function clearSelection() {
        document.querySelectorAll('.js-row-select, .js-select-all').forEach(function (cb) { cb.checked = false; });
        allRows().forEach(function (r) { r.classList.remove('is-selected'); });
        updateBulkUI();
    }
    function updateBulkUI() {
        const sel = selectedCodes().length;
        const dirty = dirtyRows().length;
        // sticky toolbar โผล่เฉพาะ batch mode (bulk apply Min/Max + บันทึก)
        const showToolbar = currentMode() === 'batch' && (sel > 0 || dirty > 0);
        const toolbar = document.getElementById('smm-bulk-toolbar');
        if (toolbar) toolbar.classList.toggle('d-none', !showToolbar);
        document.body.classList.toggle('smm-bulk-visible', showToolbar);
        const countEl = document.getElementById('smm-selected-count');
        if (countEl) countEl.textContent = sel;
        // ปุ่ม "ลบที่เลือก" ใน table header — โผล่ทุก mode เมื่อมี selected
        const headerDel = document.getElementById('smm-header-delete');
        if (headerDel) {
            headerDel.classList.toggle('d-none', sel === 0);
            headerDel.querySelectorAll('.js-sel-count').forEach(function (el) { el.textContent = sel; });
        }
        updateSelectAllState();
        updateBulkSaveCount();
    }
    function updateBulkSaveCount() {
        // "บันทึก (N)" นับเฉพาะ row ที่ dirty (มีการเปลี่ยน) — ตรงเจตนาผู้ใช้
        const n = dirtyRows().length;
        document.querySelectorAll('#smm-bulk-save .js-count').forEach(function (el) { el.textContent = n; });
        const btn = document.getElementById('smm-bulk-save');
        if (btn) btn.disabled = (n === 0);
        const sel = selectedCodes().length;
        const showToolbar = currentMode() === 'batch' && (sel > 0 || n > 0);
        const toolbar = document.getElementById('smm-bulk-toolbar');
        if (toolbar) toolbar.classList.toggle('d-none', !showToolbar);
        document.body.classList.toggle('smm-bulk-visible', showToolbar);
    }

    document.addEventListener('change', function (e) {
        const t = e.target;
        if (t.classList && t.classList.contains('js-row-select')) {
            const row = getRow(t);
            if (row) {
                const code = row.getAttribute('data-item-code');
                if (code) {
                    setCodeSelected(code, t.checked);
                } else {
                    row.classList.toggle('is-selected', t.checked);
                }
            }
            updateBulkUI();
        } else if (t.classList && t.classList.contains('js-select-all')) {
            const checked = t.checked;
            document.querySelectorAll('.js-row-select').forEach(function (cb) {
                cb.checked = checked;
                const row = getRow(cb);
                if (row) row.classList.toggle('is-selected', checked);
            });
            updateBulkUI();
        }
    });

    // ===== Bulk apply (set Min/Max ให้ทุกรายการที่เลือก) =====
    const bulkApplyBtn = document.getElementById('smm-bulk-apply');
    if (bulkApplyBtn) {
        bulkApplyBtn.addEventListener('click', function () {
            const min = document.getElementById('smm-bulk-min').value.trim();
            const max = document.getElementById('smm-bulk-max').value.trim();
            if (min === '' && max === '') {
                showToast('กรอก Min หรือ Max ก่อน', 'error');
                return;
            }
            const targets = selectedRows();
            if (targets.length === 0) {
                showToast('ยังไม่ได้เลือกรายการ', 'error');
                return;
            }
            targets.forEach(function (row) {
                const { min: minI, max: maxI } = getInputs(row);
                if (min !== '' && minI) minI.value = min;
                if (max !== '' && maxI) maxI.value = max;
                row.classList.add('is-dirty');
            });
            updateBulkSaveCount();
        });
    }

    // ===== Bulk delete (ลบ settings หลายรายการ) — ปุ่มอยู่ใน table header =====
    const bulkDeleteBtn = document.getElementById('smm-header-delete');
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function () {
            const codes = selectedCodes();
            if (codes.length === 0) return;
            if (!confirm('ยืนยันลบการตั้งค่า Min/Max ของ ' + codes.length + ' รายการที่เลือก?')) return;

            bulkDeleteBtn.disabled = true;
            const form = new FormData();
            form.append('warehouse_id', String(WAREHOUSE_ID));
            form.append('_csrf', CSRF);
            codes.forEach(function (c) { form.append('item_codes[]', c); });

            fetch(DELETE_BATCH_URL, {
                method: 'POST', body: form, credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(r => r.json())
                .then(data => {
                    bulkDeleteBtn.disabled = false;
                    if (data.status === 'success') {
                        showToast('ลบแล้ว ' + (data.deleted || codes.length) + ' รายการ', 'success');
                        setTimeout(function () { window.location.reload(); }, 500);
                    } else {
                        showToast(data.message || 'ลบไม่สำเร็จ', 'error');
                    }
                })
                .catch(() => {
                    bulkDeleteBtn.disabled = false;
                    showToast('เชื่อมต่อไม่ได้', 'error');
                });
        });
    }

    // ===== Bulk save (POST batch) =====
    const bulkSaveBtn = document.getElementById('smm-bulk-save');
    if (bulkSaveBtn) {
        bulkSaveBtn.addEventListener('click', function () {
            const rows = dirtyRows();
            if (rows.length === 0) return;
            const items = [];
            for (const row of rows) {
                const { min, max } = getInputs(row);
                if (!min || !max) continue;
                const minVal = min.value.trim(), maxVal = max.value.trim();
                if (minVal === '' || maxVal === '') {
                    showToast('มีค่า Min/Max ที่ว่าง — บันทึกไม่ได้', 'error');
                    return;
                }
                if (Number(maxVal) < Number(minVal)) {
                    showToast('max ต้องไม่น้อยกว่า min', 'error');
                    return;
                }
                items.push({
                    item_code: row.getAttribute('data-item-code'),
                    min_qty: minVal,
                    max_qty: maxVal,
                });
            }
            if (items.length === 0) return;

            bulkSaveBtn.disabled = true;
            const form = new FormData();
            form.append('warehouse_id', String(WAREHOUSE_ID));
            form.append('_csrf', CSRF);
            // ส่งเป็น items[i][item_code] ฯลฯ ให้ Yii parse เป็น array ได้
            items.forEach(function (it, i) {
                form.append('items[' + i + '][item_code]', it.item_code);
                form.append('items[' + i + '][min_qty]',  it.min_qty);
                form.append('items[' + i + '][max_qty]',  it.max_qty);
            });
            fetch(SAVE_BATCH_URL, {
                method: 'POST', body: form, credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(r => r.json())
                .then(data => {
                    bulkSaveBtn.disabled = false;
                    if (data.status === 'success') {
                        showToast('บันทึกแล้ว ' + (data.saved || items.length) + ' รายการ', 'success');
                        // refresh page เพื่อให้ตาราง + summary + balance อัปเดต
                        setTimeout(function () { window.location.reload(); }, 600);
                    } else {
                        showToast(data.message || 'บันทึกไม่สำเร็จ', 'error');
                        if (data.failed && Array.isArray(data.failed)) {
                            data.failed.slice(0, 3).forEach(function (f) {
                                showToast(f.item_code + ': ' + f.message, 'error');
                            });
                        }
                    }
                })
                .catch(() => {
                    bulkSaveBtn.disabled = false;
                    showToast('เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                });
        });
    }

    const clearBtn = document.getElementById('smm-clear-selection');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            if (hasDirtyRows() && !confirm('ยังมีการเปลี่ยนแปลงที่ยังไม่บันทึก ยกเลิกการเลือก?')) return;
            clearSelection();
            clearDirty();
            allRows().forEach(function (r) {
                // restore values from data attribute? — ทำง่ายๆ: refresh page
            });
            updateBulkUI();
        });
    }

    // ก่อน leave page ที่มี dirty rows
    window.addEventListener('beforeunload', function (e) {
        if (currentMode() === 'batch' && hasDirtyRows()) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // ===== Copy from warehouse modal =====
    const copySourceSelect = document.getElementById('smm-copy-source');
    const copyConfirmBtn = document.getElementById('smm-copy-confirm');
    const copyPreviewBox = document.getElementById('smm-copy-preview');
    const copyEmptyBox = document.getElementById('smm-copy-empty');

    function loadCopyPreview(sourceId) {
        copyPreviewBox.classList.add('d-none');
        copyEmptyBox.classList.add('d-none');
        copyConfirmBtn.disabled = true;
        if (!sourceId) return;
        const u = COPY_PREVIEW_URL + '?source_warehouse_id=' + encodeURIComponent(sourceId)
            + '&target_warehouse_id=' + WAREHOUSE_ID;
        fetch(u, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (data.status !== 'success') {
                    showToast(data.message || 'โหลด preview ไม่สำเร็จ', 'error');
                    return;
                }
                if ((data.source_total || 0) === 0) {
                    copyEmptyBox.classList.remove('d-none');
                    return;
                }
                document.getElementById('smm-copy-source-total').textContent = data.source_total;
                document.getElementById('smm-copy-target-new').textContent = data.target_new;
                document.getElementById('smm-copy-target-existing').textContent = data.target_existing;
                copyPreviewBox.classList.remove('d-none');
                copyConfirmBtn.disabled = false;
            })
            .catch(() => showToast('เชื่อมต่อไม่ได้', 'error'));
    }

    if (copySourceSelect) {
        copySourceSelect.addEventListener('change', function () { loadCopyPreview(this.value); });
    }

    // ===== Excel Import =====
    const importFile = document.getElementById('smm-import-file');
    const importUpload = document.getElementById('smm-import-upload');
    const importStep1 = document.getElementById('smm-import-step1');
    const importStep2 = document.getElementById('smm-import-step2');
    const importReset = document.getElementById('smm-import-reset');
    const importApply = document.getElementById('smm-import-apply');
    const importPreviewBody = document.getElementById('smm-import-preview-body');
    let importPreviewRows = [];

    const STATUS_PILL = {
        new:       { label: 'ใหม่',         cls: 'text-bg-success-subtle text-success border border-success-subtle' },
        update:    { label: 'จะอัปเดต',     cls: 'text-bg-primary-subtle text-primary border border-primary-subtle' },
        unchanged: { label: 'ไม่เปลี่ยน',   cls: 'text-bg-light text-secondary border' },
        error:     { label: 'ข้อผิดพลาด',   cls: 'text-bg-danger-subtle text-danger border border-danger-subtle' },
    };

    function renderImportPreview(data) {
        const rows = data.rows || [];
        const sum = data.summary || {};
        document.getElementById('smm-cnt-new').textContent       = sum.new || 0;
        document.getElementById('smm-cnt-update').textContent    = sum.update || 0;
        document.getElementById('smm-cnt-unchanged').textContent = sum.unchanged || 0;
        document.getElementById('smm-cnt-error').textContent     = sum.error || 0;

        const html = rows.map(function (r) {
            const pill = STATUS_PILL[r.status] || STATUS_PILL.error;
            const issue = (r.errors && r.errors.length) ? r.errors.join(', ')
                        : (r.status === 'update' ? 'เดิม Min=' + (r.current_min ?? '-') + ' Max=' + (r.current_max ?? '-') : '');
            const escape = function (s) {
                return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
                    return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
                });
            };
            return '<tr>' +
                '<td class="text-muted small">' + r.row_index + '</td>' +
                '<td><div class="fw-semibold">' + escape(r.item_name) + '</div>' +
                    '<code class="text-muted small">' + escape(r.item_code) + '</code></td>' +
                '<td class="text-end font-monospace">' + (r.min_qty != null ? Number(r.min_qty).toLocaleString() : '—') + '</td>' +
                '<td class="text-end font-monospace">' + (r.max_qty != null ? Number(r.max_qty).toLocaleString() : '—') + '</td>' +
                '<td class="text-center"><span class="badge ' + pill.cls + '">' + pill.label + '</span></td>' +
                '<td class="small text-muted">' + escape(issue) + '</td>' +
            '</tr>';
        }).join('');
        importPreviewBody.innerHTML = html || '<tr><td colspan="6" class="text-center text-muted py-3">ไม่มีรายการ</td></tr>';

        const applyCount = rows.filter(r => r.status === 'new' || r.status === 'update').length;
        importApply.querySelector('.js-apply-count').textContent = applyCount;
        importApply.classList.toggle('d-none', applyCount === 0);
        importReset.classList.remove('d-none');
        importPreviewRows = rows;
    }

    function resetImportModal() {
        importStep1.classList.remove('d-none');
        importStep2.classList.add('d-none');
        importReset.classList.add('d-none');
        importApply.classList.add('d-none');
        importPreviewBody.innerHTML = '';
        importFile.value = '';
        importPreviewRows = [];
    }

    if (importUpload) {
        importUpload.addEventListener('click', function () {
            const f = importFile.files && importFile.files[0];
            if (!f) {
                showToast('กรุณาเลือกไฟล์ก่อน', 'error');
                return;
            }
            importUpload.disabled = true;
            const form = new FormData();
            form.append('file', f);
            form.append('_csrf', CSRF);
            fetch(IMPORT_PREVIEW_URL, {
                method: 'POST', body: form, credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(r => r.json())
                .then(data => {
                    importUpload.disabled = false;
                    if (data.status !== 'success') {
                        showToast(data.message || 'อ่านไฟล์ไม่สำเร็จ', 'error');
                        return;
                    }
                    importStep1.classList.add('d-none');
                    importStep2.classList.remove('d-none');
                    renderImportPreview(data);
                })
                .catch(() => {
                    importUpload.disabled = false;
                    showToast('เชื่อมต่อไม่ได้', 'error');
                });
        });
    }

    if (importReset) importReset.addEventListener('click', resetImportModal);

    // เคลียร์ state ทุกครั้งที่ปิด modal
    const importModalEl = document.getElementById('smm-import-modal');
    if (importModalEl) importModalEl.addEventListener('hidden.bs.modal', resetImportModal);

    if (importApply) {
        importApply.addEventListener('click', function () {
            const valid = importPreviewRows.filter(r => r.status === 'new' || r.status === 'update');
            if (valid.length === 0) return;
            if (!confirm('ยืนยันบันทึก ' + valid.length + ' รายการ?')) return;

            importApply.disabled = true;
            const form = new FormData();
            form.append('warehouse_id', String(WAREHOUSE_ID));
            form.append('_csrf', CSRF);
            valid.forEach(function (r, i) {
                form.append('items[' + i + '][item_code]', r.item_code);
                form.append('items[' + i + '][min_qty]',  r.min_qty);
                form.append('items[' + i + '][max_qty]',  r.max_qty);
            });
            fetch(SAVE_BATCH_URL, {
                method: 'POST', body: form, credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(r => r.json())
                .then(data => {
                    importApply.disabled = false;
                    if (data.status === 'success') {
                        showToast('นำเข้าแล้ว ' + (data.saved || valid.length) + ' รายการ', 'success');
                        const modal = bootstrap.Modal.getInstance(importModalEl);
                        if (modal) modal.hide();
                        setTimeout(function () { window.location.reload(); }, 700);
                    } else {
                        showToast(data.message || 'บันทึกไม่สำเร็จ', 'error');
                    }
                })
                .catch(() => {
                    importApply.disabled = false;
                    showToast('เชื่อมต่อไม่ได้', 'error');
                });
        });
    }

    // ===== Add items modal =====
    const addModalEl = document.getElementById('smm-add-items-modal');
    const addCategorySel = document.getElementById('smm-add-category');
    const addQInput = document.getElementById('smm-add-q');
    const addTbody = document.getElementById('smm-add-tbody');
    const addStatusEl = document.getElementById('smm-add-status');
    const addSelectAll = document.getElementById('smm-add-select-all');
    const addCountEl = document.getElementById('smm-add-selected-count');
    const addConfirmBtn = document.getElementById('smm-add-confirm');

    let addLoadSeq = 0;     // ป้องกัน race ระหว่าง fetch ซ้อน
    let addQTimer = null;

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c];
        });
    }

    function renderAddRows(rows, total, limited) {
        if (!rows || rows.length === 0) {
            addTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">ไม่มีรายการที่ยังไม่ตั้งค่าในเงื่อนไขนี้</td></tr>';
            addStatusEl.textContent = 'ไม่พบรายการ';
        } else {
            addTbody.innerHTML = rows.map(function (r) {
                return '<tr>' +
                    '<td class="text-center"><input type="checkbox" class="form-check-input js-add-row" value="' + escapeHtml(r.item_code) + '"></td>' +
                    '<td><code class="text-muted small">' + escapeHtml(r.item_code) + '</code></td>' +
                    '<td>' + escapeHtml(r.item_name) + '</td>' +
                    '<td class="text-muted small">' + escapeHtml(r.unit_name || '-') + '</td>' +
                    '<td class="text-muted small">' + escapeHtml(r.category_title || '-') + '</td>' +
                '</tr>';
            }).join('');
            addStatusEl.textContent = 'พบ ' + total + ' รายการ' + (limited ? ' (แสดง 500 รายการแรก — ค้นหาเพื่อแคบลง)' : '');
        }
        addSelectAll.checked = false;
        updateAddCount();
    }

    function loadAddCandidates() {
        const seq = ++addLoadSeq;
        const cat = addCategorySel.value;
        const q = addQInput.value.trim();
        addTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4"><span class="spinner-border spinner-border-sm me-1"></span>กำลังโหลด...</td></tr>';
        addStatusEl.textContent = 'กำลังโหลด...';
        const sep = CANDIDATE_ITEMS_URL.indexOf('?') >= 0 ? '&' : '?';
        const u = CANDIDATE_ITEMS_URL + sep + 'category_id=' + encodeURIComponent(cat) + '&q=' + encodeURIComponent(q);
        fetch(u, { credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (seq !== addLoadSeq) return; // มี request ใหม่กว่าแล้ว
                if (data.status !== 'success') {
                    addTbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">' + escapeHtml(data.message || 'โหลดไม่สำเร็จ') + '</td></tr>';
                    addStatusEl.textContent = '';
                    return;
                }
                renderAddRows(data.rows || [], data.total || 0, !!data.limited);
            })
            .catch(() => {
                if (seq !== addLoadSeq) return;
                addTbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4">เชื่อมต่อไม่ได้</td></tr>';
                addStatusEl.textContent = '';
            });
    }

    function updateAddCount() {
        const n = addTbody.querySelectorAll('.js-add-row:checked').length;
        addCountEl.textContent = n;
        addConfirmBtn.querySelector('.js-add-count').textContent = n;
        addConfirmBtn.disabled = (n === 0);
    }

    function resetAddModal() {
        addCategorySel.value = '';
        addQInput.value = '';
        addTbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-4">เลือกหมวดเพื่อโหลดรายการ</td></tr>';
        addStatusEl.textContent = 'เลือกหมวดเพื่อโหลดรายการ';
        addSelectAll.checked = false;
        addLoadSeq++; // cancel any pending fetch
        updateAddCount();
    }

    if (addModalEl) {
        // เปิด modal → load รายการรอบแรก (default = ทุกหมวด)
        addModalEl.addEventListener('shown.bs.modal', function () {
            if (addTbody.children.length <= 1) {
                loadAddCandidates();
            }
        });
        addModalEl.addEventListener('hidden.bs.modal', resetAddModal);
    }

    if (addCategorySel) {
        addCategorySel.addEventListener('change', loadAddCandidates);
    }
    if (addQInput) {
        addQInput.addEventListener('input', function () {
            clearTimeout(addQTimer);
            addQTimer = setTimeout(loadAddCandidates, 350);
        });
    }
    if (addSelectAll) {
        addSelectAll.addEventListener('change', function () {
            const on = this.checked;
            addTbody.querySelectorAll('.js-add-row').forEach(function (cb) { cb.checked = on; });
            updateAddCount();
        });
    }
    if (addTbody) {
        addTbody.addEventListener('change', function (e) {
            if (e.target && e.target.classList && e.target.classList.contains('js-add-row')) {
                updateAddCount();
            }
        });
    }

    if (addConfirmBtn) {
        addConfirmBtn.addEventListener('click', function () {
            const codes = Array.from(addTbody.querySelectorAll('.js-add-row:checked')).map(function (cb) { return cb.value; });
            if (codes.length === 0) return;

            addConfirmBtn.disabled = true;
            const form = new FormData();
            form.append('warehouse_id', String(WAREHOUSE_ID));
            form.append('_csrf', CSRF);
            codes.forEach(function (c) { form.append('item_codes[]', c); });

            fetch(ADD_ITEMS_URL, {
                method: 'POST', body: form, credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(r => r.json())
                .then(data => {
                    addConfirmBtn.disabled = false;
                    if (data.status === 'success') {
                        const msg = 'เพิ่มแล้ว ' + (data.added || 0) + ' รายการ'
                            + ((data.skipped || 0) > 0 ? ' (ข้าม ' + data.skipped + ' ที่ตั้งไว้แล้ว)' : '');
                        showToast(msg, 'success');
                        const modal = bootstrap.Modal.getInstance(addModalEl);
                        if (modal) modal.hide();
                        setTimeout(function () { window.location.reload(); }, 600);
                    } else {
                        showToast(data.message || 'เพิ่มไม่สำเร็จ', 'error');
                    }
                })
                .catch(() => {
                    addConfirmBtn.disabled = false;
                    showToast('เชื่อมต่อไม่ได้', 'error');
                });
        });
    }

    if (copyConfirmBtn) {
        copyConfirmBtn.addEventListener('click', function () {
            const sourceId = copySourceSelect.value;
            if (!sourceId) return;
            const mode = (document.querySelector('input[name="smm-copy-mode"]:checked') || {}).value || 'skip';
            copyConfirmBtn.disabled = true;
            const form = new FormData();
            form.append('source_warehouse_id', sourceId);
            form.append('target_warehouse_id', String(WAREHOUSE_ID));
            form.append('mode', mode);
            form.append('_csrf', CSRF);
            fetch(COPY_URL, {
                method: 'POST', body: form, credentials: 'same-origin',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(r => r.json())
                .then(data => {
                    copyConfirmBtn.disabled = false;
                    if (data.status === 'success') {
                        showToast('คัดลอกแล้ว ' + (data.copied || 0) + ' รายการ (ข้าม ' + (data.skipped || 0) + ')', 'success');
                        const modal = bootstrap.Modal.getInstance(document.getElementById('smm-copy-from-modal'));
                        if (modal) modal.hide();
                        setTimeout(function () { window.location.reload(); }, 700);
                    } else {
                        showToast(data.message || 'คัดลอกไม่สำเร็จ', 'error');
                    }
                })
                .catch(() => {
                    copyConfirmBtn.disabled = false;
                    showToast('เชื่อมต่อไม่ได้', 'error');
                });
        });
    }
    const historyPreviewBtn = document.getElementById('smm-history-preview');
    const historyApplyBtn = document.getElementById('smm-history-apply');
    const historyBody = document.getElementById('smm-history-preview-body');
    const historyEmpty = document.getElementById('smm-history-empty');
    const historyTableWrap = document.getElementById('smm-history-table-wrap');
    const historySummary = document.getElementById('smm-history-summary');

    function historyParams() {
        const fd = new FormData();
        fd.append('_csrf', CSRF);
        fd.append('months', document.getElementById('smm-history-months')?.value || '6');
        fd.append('min_factor', document.getElementById('smm-history-min-factor')?.value || '1');
        fd.append('max_factor', document.getElementById('smm-history-max-factor')?.value || '2');
        return fd;
    }

    function textCell(value, className) {
        const td = document.createElement('td');
        if (className) td.className = className;
        td.textContent = value === null || value === undefined || value === '' ? '-' : String(value);
        return td;
    }

    function numberText(value) {
        const n = Number(value || 0);
        return n.toLocaleString(undefined, { maximumFractionDigits: 2 });
    }

    function renderHistoryPreview(data) {
        const rows = Array.isArray(data.rows) ? data.rows : [];
        historyBody.innerHTML = '';
        historyApplyBtn.disabled = rows.length === 0;
        historyTableWrap.classList.toggle('d-none', rows.length === 0);
        historyEmpty.classList.toggle('d-none', rows.length > 0);
        historySummary.classList.toggle('d-none', false);
        historySummary.textContent = rows.length > 0
            ? 'พบ ' + rows.length + ' รายการที่ยังไม่มี min/max จากประวัติย้อนหลัง ' + (data.options?.months || '-') + ' เดือน'
            : 'ไม่พบวัสดุจากประวัติเบิกที่ยังไม่มีการตั้งค่า';

        rows.forEach(function(row) {
            const tr = document.createElement('tr');
            tr.appendChild(textCell(row.item_code, 'font-monospace'));
            tr.appendChild(textCell(row.item_name));
            tr.appendChild(textCell(numberText(row.total_qty), 'text-end'));
            tr.appendChild(textCell(numberText(row.avg_monthly), 'text-end'));
            tr.appendChild(textCell(numberText(row.min_qty), 'text-end fw-semibold'));
            tr.appendChild(textCell(numberText(row.max_qty), 'text-end fw-semibold'));
            tr.appendChild(textCell(numberText(row.issue_count), 'text-end'));
            historyBody.appendChild(tr);
        });
    }

    ['smm-history-months', 'smm-history-min-factor', 'smm-history-max-factor'].forEach(function(id) {
        const el = document.getElementById(id);
        if (!el) return;
        el.addEventListener('input', function() {
            historyApplyBtn.disabled = true;
        });
        el.addEventListener('change', function() {
            historyApplyBtn.disabled = true;
        });
    });

    if (historyPreviewBtn) {
        historyPreviewBtn.addEventListener('click', function() {
            historyPreviewBtn.disabled = true;
            historyPreviewBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>กำลังคำนวณ';
            fetch(HISTORY_PREVIEW_URL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: historyParams()
            })
                .then(r => r.json())
                .then(function(data) {
                    if (!data || data.status !== 'success') {
                        showToast((data && data.message) || 'คำนวณไม่สำเร็จ', 'error');
                        return;
                    }
                    renderHistoryPreview(data);
                })
                .catch(() => showToast('เชื่อมต่อไม่ได้', 'error'))
                .finally(function() {
                    historyPreviewBtn.disabled = false;
                    historyPreviewBtn.innerHTML = '<i class="bi bi-search me-1"></i>ดูตัวอย่าง';
                });
        });
    }

    if (historyApplyBtn) {
        historyApplyBtn.addEventListener('click', function() {
            historyApplyBtn.disabled = true;
            historyApplyBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>กำลังบันทึก';
            fetch(HISTORY_APPLY_URL, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: historyParams()
            })
                .then(r => r.json())
                .then(function(data) {
                    if (!data || data.status !== 'success') {
                        showToast((data && data.message) || 'บันทึกไม่สำเร็จ', 'error');
                        historyApplyBtn.disabled = false;
                        historyApplyBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>บันทึกเข้าตาราง';
                        return;
                    }
                    showToast('เพิ่มจากประวัติเบิก ' + (data.added || 0) + ' รายการ');
                    setTimeout(function() { window.location.reload(); }, 700);
                })
                .catch(function() {
                    historyApplyBtn.disabled = false;
                    historyApplyBtn.innerHTML = '<i class="bi bi-check2-circle me-1"></i>บันทึกเข้าตาราง';
                    showToast('เชื่อมต่อไม่ได้', 'error');
                });
        });
    }
})();
JS;
$this->registerJs($js, View::POS_END);
?>

<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\web\View;

$this->title = 'สรุปยอดคงเหลือตามคลัง';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$historyUrl = Url::to(['/inventory-v2/report/item-history']);
$exportHistoryUrl = Url::to(['/inventory-v2/report/export-item-history']);
?>

<?php $this->beginBlock('page-title'); ?>
<?= $this->render('@app/modules/inventoryV2/views/sub-stock/_page_head', [
    'icon'    => 'bi-boxes',
    'title'   => $this->title,
    'caption' => 'คลังหลักดูสถานะวัสดุคงเหลือของคลังย่อยได้ — คลังย่อยดูสถานะของตัวเองได้ คลิก <span class="inline-badge">ดูประวัติ</span> ที่แต่ละแถวเพื่อเปิดบัตรเคลื่อนไหว',
]) ?>
<?php $this->endBlock(); ?>

<?= $this->render('@app/modules/inventoryV2/views/sub-stock/_menu_sub_stock', [
    'active' => 'balance',
    'currentWarehouseId' => $warehouseId ?: null,
]) ?>

<div class="container-fluid px-3 px-md-4">
    <form method="get"
          action="<?= Url::to(['/inventory-v2/report/balance-by-warehouse']) ?>"
          id="balance-filter-form"
          class="balance-toolbar"
          role="search"
          aria-label="ตัวกรองยอดคงเหลือ">
        <div class="balance-toolbar__fields">
            <label class="balance-toolbar__field balance-toolbar__field--search" title="ค้นหา">
                <span class="visually-hidden">ค้นหา</span>
                <i class="bi bi-search balance-toolbar__icon" aria-hidden="true"></i>
                <?= Html::input('search', 'search', $search, [
                    'id' => 'balance-search',
                    'class' => 'balance-toolbar__input',
                    'placeholder' => 'ค้นหาชื่อ / รหัส / จำนวน / มูลค่า',
                    'aria-label' => 'ค้นหา',
                    'autocomplete' => 'off',
                ]) ?>
            </label>
            <label class="balance-toolbar__field" title="คลัง">
                <span class="visually-hidden">คลัง</span>
                <i class="bi bi-shop balance-toolbar__icon" aria-hidden="true"></i>
                <?= Html::dropDownList('warehouse_id', $warehouseId, $warehouses, [
                    'id' => 'balance-warehouse',
                    'class' => 'balance-toolbar__select',
                    'aria-label' => 'คลัง',
                ]) ?>
            </label>
            <label class="balance-toolbar__field" title="ประเภทวัสดุ">
                <span class="visually-hidden">ประเภทวัสดุ</span>
                <i class="bi bi-tag balance-toolbar__icon" aria-hidden="true"></i>
                <?= Html::dropDownList('category_id', $categoryId, $categories, [
                    'id' => 'balance-category',
                    'prompt' => 'ทุกประเภท',
                    'class' => 'balance-toolbar__select',
                    'aria-label' => 'ประเภทวัสดุ',
                ]) ?>
            </label>
            <label class="balance-toolbar__field" title="สถานะ">
                <span class="visually-hidden">สถานะ</span>
                <i class="bi bi-flag balance-toolbar__icon" aria-hidden="true"></i>
                <?= Html::dropDownList('status', $status, [
                    'below_min' => 'ต่ำกว่า Min',
                    'below_max' => 'ต่ำกว่า Max',
                    'normal' => 'ปกติ (พอดี)',
                ], [
                    'id' => 'balance-status',
                    'prompt' => 'ทุกสถานะ',
                    'class' => 'balance-toolbar__select',
                    'aria-label' => 'สถานะ',
                ]) ?>
            </label>
        </div>
        <div class="balance-toolbar__actions">
            <?php $hasActiveFilter = !empty($warehouseId) || !empty($categoryId) || !empty($status) || !empty($search); ?>
            <?php if ($hasActiveFilter): ?>
                <a href="<?= Url::to(['/inventory-v2/report/balance-by-warehouse']) ?>"
                   class="balance-toolbar__btn"
                   title="ล้างค่าตัวกรอง"
                   aria-label="ล้างค่าตัวกรอง">
                    <i class="bi bi-arrow-counterclockwise" aria-hidden="true"></i>
                    <span class="d-none d-md-inline">ล้างค่า</span>
                </a>
            <?php endif; ?>
            <?= Html::a('<i class="bi bi-file-earmark-excel" aria-hidden="true"></i><span class="d-none d-md-inline">Excel</span>',
                array_merge(['/inventory-v2/report/export-balance-by-warehouse'], Yii::$app->request->getQueryParams()),
                [
                    'class' => 'balance-toolbar__btn balance-toolbar__btn--accent',
                    'title' => 'ดาวน์โหลด Excel',
                    'aria-label' => 'ดาวน์โหลด Excel',
                ]
            ) ?>
            <noscript>
                <button type="submit" class="balance-toolbar__btn balance-toolbar__btn--accent">
                    <i class="bi bi-search" aria-hidden="true"></i>
                    <span>แสดงผล</span>
                </button>
            </noscript>
        </div>
    </form>
</div>

<style>
.balance-toolbar {
    --bt-ink-1: #1a202c;
    --bt-ink-2: #4a5568;
    --bt-ink-3: #718096;
    --bt-surface: #ffffff;
    --bt-surface-hover: #f1f5f9;
    --bt-line: rgba(15, 23, 42, 0.08);
    --bt-line-strong: rgba(15, 23, 42, 0.14);
    --bt-primary: #0d6efd;
    --bt-primary-ink: #0a58ca;
    --bt-primary-soft: rgba(13, 110, 253, 0.08);
    --bt-success-ink: #15803d;
    --bt-success-soft: rgba(21, 128, 61, 0.10);
    --bt-radius-sm: 8px;
    --bt-ease: cubic-bezier(0.16, 1, 0.3, 1);

    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
    padding: 0.5rem 0;
    margin-bottom: 0.85rem;
    border-bottom: 1px solid var(--bt-line);
}

.balance-toolbar__fields {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    flex: 1 1 auto;
    min-width: 0;
    flex-wrap: wrap;
}

.balance-toolbar__field {
    position: relative;
    display: inline-flex;
    align-items: center;
    min-width: 0;
    flex: 0 1 auto;
    margin: 0;
}
.balance-toolbar__icon {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--bt-ink-3);
    font-size: 0.95rem;
    pointer-events: none;
}
.balance-toolbar__field--search { flex: 1 1 240px; min-width: 200px; }
.balance-toolbar__input {
    width: 100%;
    min-height: 38px;
    padding: 0 0.85rem 0 2.2rem;
    background-color: var(--bt-surface);
    border: 1px solid var(--bt-line-strong);
    border-radius: var(--bt-radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--bt-ink-1);
    transition: border-color 120ms var(--bt-ease), background-color 120ms var(--bt-ease), box-shadow 120ms var(--bt-ease);
}
.balance-toolbar__input::placeholder { color: var(--bt-ink-3); font-weight: 400; }
.balance-toolbar__input:hover { border-color: var(--bt-ink-3); }
.balance-toolbar__input:focus-visible {
    outline: none;
    border-color: var(--bt-primary);
    box-shadow: 0 0 0 3px var(--bt-primary-soft);
}
.balance-toolbar__select {
    appearance: none;
    -webkit-appearance: none;
    min-height: 38px;
    padding: 0 2.1rem 0 2.2rem;
    background-color: var(--bt-surface);
    background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='none' stroke='%23718096' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpath d='m4 6 4 4 4-4'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.7rem center;
    background-size: 14px;
    border: 1px solid var(--bt-line-strong);
    border-radius: var(--bt-radius-sm);
    font-size: 0.875rem;
    font-weight: 500;
    color: var(--bt-ink-1);
    cursor: pointer;
    max-width: 100%;
    transition: border-color 120ms var(--bt-ease), background-color 120ms var(--bt-ease), box-shadow 120ms var(--bt-ease);
}
.balance-toolbar__select:hover {
    border-color: var(--bt-ink-3);
    background-color: var(--bt-surface-hover);
}
.balance-toolbar__select:focus-visible {
    outline: none;
    border-color: var(--bt-primary);
    box-shadow: 0 0 0 3px var(--bt-primary-soft);
}
/* Selected (non-default) state */
.balance-toolbar__select:not([value=""]):not([data-default="1"]) {
    color: var(--bt-ink-1);
}

.balance-toolbar__actions {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    flex-shrink: 0;
}

.balance-toolbar__btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0 0.85rem;
    min-height: 38px;
    background: var(--bt-surface);
    border: 1px solid var(--bt-line-strong);
    border-radius: var(--bt-radius-sm);
    color: var(--bt-ink-2);
    font-size: 0.85rem;
    font-weight: 500;
    text-decoration: none;
    cursor: pointer;
    white-space: nowrap;
    transition: background-color 120ms var(--bt-ease), border-color 120ms var(--bt-ease), color 120ms var(--bt-ease);
}
.balance-toolbar__btn:hover {
    background: var(--bt-surface-hover);
    color: var(--bt-ink-1);
    border-color: var(--bt-ink-3);
}
.balance-toolbar__btn:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--bt-primary-soft);
    color: var(--bt-ink-1);
}
.balance-toolbar__btn i { font-size: 0.95rem; line-height: 1; }

.balance-toolbar__btn--accent {
    color: var(--bt-success-ink);
    border-color: rgba(21, 128, 61, 0.22);
}
.balance-toolbar__btn--accent:hover {
    background: var(--bt-success-soft);
    color: var(--bt-success-ink);
    border-color: rgba(21, 128, 61, 0.35);
}

@media (max-width: 767.98px) {
    .balance-toolbar { gap: 0.5rem; padding: 0.5rem 0 0.6rem; }
    .balance-toolbar__fields { flex: 1 1 100%; gap: 0.35rem; }
    .balance-toolbar__field { flex: 1 1 auto; min-width: 130px; }
    .balance-toolbar__field .balance-toolbar__select { width: 100%; }
    .balance-toolbar__actions {
        flex: 1 1 100%;
        justify-content: flex-end;
    }
    .balance-toolbar__btn { padding: 0 0.7rem; }
}

@media (prefers-reduced-motion: reduce) {
    .balance-toolbar__select,
    .balance-toolbar__btn { transition: none; }
}
</style>

<style>
/* Balance-by-warehouse: layout + motion */
.balance-table tbody tr {
    transition: background-color .18s cubic-bezier(.215,.61,.355,1);
}
.balance-table tbody tr:hover {
    background-color: rgba(13,110,253,.04);
}
.balance-table .target-cell {
    font-variant-numeric: tabular-nums;
    line-height: 1.25;
}
.balance-table .target-cell .sep { color: var(--bs-secondary-color); padding: 0 .25rem; }
.item-cell {
    display: flex;
    align-items: center;
    gap: .65rem;
    min-width: 14rem;
}
.item-thumb {
    width: 40px; height: 40px;
    flex-shrink: 0;
    border-radius: .5rem;
    object-fit: cover;
    background: var(--bs-tertiary-bg);
    border: 1px solid var(--bs-border-color);
    transition: transform .18s ease-out;
}
.balance-table tbody tr:hover .item-thumb { transform: scale(1.06); }
.item-cell .item-name {
    line-height: 1.25;
    word-break: break-word;
}
.item-cell .item-code-mini {
    display: block;
    font-size: .72rem;
    color: var(--bs-secondary-color);
    font-variant-numeric: tabular-nums;
}
.btn-history {
    --bs-btn-padding-y: .25rem;
    --bs-btn-padding-x: .55rem;
    --bs-btn-font-size: .8125rem;
    border-color: rgba(13,110,253,.25);
    color: var(--bs-primary);
    background: rgba(13,110,253,.04);
    font-weight: 500;
    transition: transform .15s ease-out, background-color .15s ease-out, border-color .15s ease-out;
}
.btn-history:hover {
    background: var(--bs-primary);
    color: #fff;
    border-color: var(--bs-primary);
    transform: translateY(-1px);
}
.btn-history:focus-visible {
    outline: 2px solid var(--bs-primary);
    outline-offset: 2px;
}

/* Modal: scale + fade entrance (Bootstrap class .show triggers transition) */
#itemHistoryModal .modal-dialog {
    transition: transform .22s cubic-bezier(.215,.61,.355,1), opacity .18s ease-out;
}
#itemHistoryModal:not(.show) .modal-dialog {
    transform: translateY(8px) scale(.985);
    opacity: 0;
}
#itemHistoryModal.show .modal-dialog {
    transform: translateY(0) scale(1);
    opacity: 1;
}

/* Force readable header — override any global modal-header theme */
#itemHistoryModal .modal-header {
    background: #fff !important;
    background-image: none !important;
    color: #1f2937 !important;
    border-bottom: 1px solid var(--bs-border-color) !important;
    padding: 1rem 1.25rem;
}
#itemHistoryModal .modal-title {
    color: #0f172a !important;
    font-weight: 700;
}
#itemHistoryModal .modal-header .header-meta {
    color: #475569 !important;
}
#itemHistoryModal .modal-header .header-meta strong {
    color: #0f172a;
    font-weight: 600;
}
#itemHistoryModal .modal-header .btn-close {
    filter: none !important;
    opacity: .65;
}
#itemHistoryModal .modal-header .btn-close:hover { opacity: 1; }
#itemHistoryModal .title-icon {
    background: rgba(13,110,253,.1);
    color: var(--bs-primary);
    width: 2rem; height: 2rem;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: .5rem;
    flex-shrink: 0;
}
#itemHistoryModal .modal-thumb {
    width: 64px; height: 64px;
    border-radius: .75rem;
    object-fit: cover;
    background: var(--bs-tertiary-bg);
    border: 1px solid var(--bs-border-color);
    flex-shrink: 0;
    box-shadow: 0 1px 2px rgba(0,0,0,.04);
}

/* Stat tiles inside modal */
.history-stat {
    border: 1px solid var(--bs-border-color);
    border-radius: .75rem;
    padding: .85rem 1rem;
    background: #fff;
    display: flex;
    flex-direction: column;
    gap: .15rem;
}
.history-stat .label {
    font-size: .75rem;
    color: var(--bs-secondary-color);
    font-weight: 600;
    letter-spacing: .01em;
}
.history-stat .value {
    font-size: 1.5rem;
    font-weight: 700;
    line-height: 1.1;
    font-variant-numeric: tabular-nums;
}
.history-stat.brought-forward .value { color: var(--bs-body-color); }
.history-stat.in-out .value { color: var(--bs-body-color); display: flex; gap: .35rem; align-items: baseline; }
.history-stat.in-out .in { color: #157347; }
.history-stat.in-out .out { color: #b02a37; }
.history-stat.balance { background: rgba(13,110,253,.06); border-color: rgba(13,110,253,.25); }
.history-stat.balance .value { color: var(--bs-primary); }
.history-stat .sub { font-size: .75rem; color: var(--bs-secondary-color); font-variant-numeric: tabular-nums; }

/* History table */
.history-table {
    font-size: .9rem;
}
.history-table thead th {
    font-weight: 600;
    font-size: .78rem;
    color: var(--bs-body-color);
    background: var(--bs-tertiary-bg);
    border-bottom-width: 1px;
    text-transform: none;
    letter-spacing: 0;
    white-space: nowrap;
}
.history-table tbody td { font-variant-numeric: tabular-nums; }
.history-table .direction-pill {
    display: inline-flex; align-items: center; gap: .25rem;
    padding: .15rem .5rem; border-radius: 999px;
    font-size: .72rem; font-weight: 600;
}
.history-table .direction-pill.in  { background: rgba(21,115,71,.12); color: #0f5132; }
.history-table .direction-pill.out { background: rgba(176,42,55,.12); color: #842029; }
.history-table tr.bf-row {
    background: var(--bs-tertiary-bg);
    font-weight: 600;
}
.history-table tr.bf-row td { color: var(--bs-body-color); }

/* Skeleton */
@keyframes hist-skel {
    0%   { background-position: 0% 50%; }
    100% { background-position: 100% 50%; }
}
.history-skeleton {
    height: 12px; border-radius: 4px;
    background: linear-gradient(90deg, rgba(0,0,0,.06) 0%, rgba(0,0,0,.12) 40%, rgba(0,0,0,.06) 80%);
    background-size: 200% 100%;
    animation: hist-skel 1.1s ease-in-out infinite;
}

@media (prefers-reduced-motion: reduce) {
    .balance-table tbody tr,
    .btn-history,
    #itemHistoryModal .modal-dialog { transition: none !important; }
    .history-skeleton { animation: none; background: rgba(0,0,0,.08); }
}
</style>

<div class="container-fluid py-4">

    <?php if (empty($rows)): ?>
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>
            ไม่มียอดคงเหลือในคลังที่เลือก หรือยังไม่มีข้อมูลรับเข้าในคลังหลัก
        </div>
    <?php else: ?>
        <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <p class="text-muted small mb-1 fw-bold">มูลค่ารวม (บาท)</p>
                        <p class="fs-4 fw-bold text-primary mb-0"><?= number_format($summary['total_value'], 2) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body py-3">
                        <p class="text-muted small mb-1 fw-bold">จำนวนรายการวัสดุ</p>
                        <p class="fs-4 fw-bold mb-0"><?= number_format($summary['items_count']) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100 border border-danger border-opacity-25">
                    <div class="card-body py-3">
                        <p class="text-muted small mb-1 fw-bold">ต่ำกว่าจุดสั่งซื้อขั้นต่ำ (Min)</p>
                        <p class="fs-4 fw-bold text-danger mb-0"><?= number_format($summary['below_min_count']) ?> รายการ</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="card border-0 shadow-sm h-100 border border-warning border-opacity-25">
                    <div class="card-body py-3">
                        <p class="text-muted small mb-1 fw-bold">ต่ำกว่าจุดสั่งซื้อขั้นสูง (Max)</p>
                        <p class="fs-4 fw-bold text-warning mb-0"><?= number_format($summary['below_max_count']) ?> รายการ</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-secondary bg-opacity-10 py-2 px-3">
                <h6 class="mb-0 fw-normal">รายการวัสดุคงเหลือตามคลัง</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 balance-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-nowrap text-center" style="width: 4rem;">ลำดับ</th>
                                <th class="text-nowrap">คลัง</th>
                                <th>วัสดุ</th>
                                <th class="text-nowrap text-center">ประเภท</th>
                                <th class="text-nowrap text-center">หน่วย</th>
                                <th class="text-end fw-bold">จำนวนคงเหลือ</th>
                                <th class="text-end fw-bold">มูลค่า (บาท)</th>
                                <th class="text-center text-nowrap">Min / Max</th>
                                <th class="text-center">สถานะ</th>
                                <th class="text-center text-nowrap" style="width: 7rem;">ประวัติ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $i => $r): ?>
                                <tr class="<?= $r['below_min'] ? 'table-danger' : ($r['below_max'] ? 'table-warning' : '') ?>">
                                    <td class="text-center text-muted"><?= $i + 1 ?></td>
                                    <td class="text-nowrap"><?= Html::encode($r['warehouse_name']) ?></td>
                                    <td>
                                        <div class="item-cell">
                                            <img src="<?= Html::encode($r['image_url']) ?>"
                                                 alt="<?= Html::encode($r['item_name']) ?>"
                                                 class="item-thumb"
                                                 loading="lazy"
                                                 onerror="this.onerror=null;this.src='<?= Yii::getAlias('@web') ?>/img/placeholder-img.jpg';">
                                            <div>
                                                <div class="item-name"><?= Html::encode($r['item_name']) ?></div>
                                                <span class="item-code-mini"><?= Html::encode($r['item_code']) ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center text-muted small"><?= Html::encode($r['category_title']) ?></td>
                                    <td class="text-center text-muted small"><?= Html::encode($r['unit_name']) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($r['balance_qty'], 2) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($r['value'], 2) ?></td>
                                    <td class="text-center text-muted small target-cell">
                                        <?= $r['min_qty'] !== null ? number_format($r['min_qty'], 0) : '-' ?>
                                        <span class="sep">/</span>
                                        <?= $r['max_qty'] !== null ? number_format($r['max_qty'], 0) : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($r['below_min']): ?>
                                            <span class="badge text-bg-danger">ต่ำกว่า Min</span>
                                        <?php elseif ($r['below_max']): ?>
                                            <span class="badge text-bg-warning text-dark">ต่ำกว่า Max</span>
                                        <?php else: ?>
                                            <span class="badge text-bg-success">พอดี</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-history"
                                                data-bs-toggle="modal"
                                                data-bs-target="#itemHistoryModal"
                                                data-item-code="<?= Html::encode($r['item_code']) ?>"
                                                data-item-name="<?= Html::encode($r['item_name']) ?>"
                                                data-unit-name="<?= Html::encode($r['unit_name']) ?>"
                                                data-item-image="<?= Html::encode($r['image_url']) ?>"
                                                data-warehouse-id="<?= (int) $r['warehouse_id'] ?>"
                                                data-warehouse-name="<?= Html::encode($r['warehouse_name']) ?>"
                                                aria-label="ดูประวัติเคลื่อนไหวของ <?= Html::encode($r['item_name']) ?> ที่ <?= Html::encode($r['warehouse_name']) ?>">
                                            <i class="bi bi-clock-history me-1"></i>ดูประวัติ
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: ประวัติการเคลื่อนไหววัสดุ -->
<div class="modal fade" id="itemHistoryModal" tabindex="-1" aria-labelledby="itemHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header align-items-start">
                <div class="flex-grow-1 pe-3 d-flex gap-3">
                    <img id="hist-thumb" src="<?= Yii::getAlias('@web') ?>/img/placeholder-img.jpg"
                         alt="" class="modal-thumb"
                         onerror="this.onerror=null;this.src='<?= Yii::getAlias('@web') ?>/img/placeholder-img.jpg';">
                    <div class="flex-grow-1">
                        <h5 class="modal-title mb-1 d-flex align-items-center gap-2" id="itemHistoryModalLabel">
                            <i class="bi bi-clock-history text-primary"></i>
                            ประวัติการเคลื่อนไหววัสดุ
                        </h5>
                        <div class="header-meta small d-flex flex-wrap" style="gap: .15rem 1.25rem;">
                            <span><i class="bi bi-upc-scan me-1 text-primary"></i><strong id="hist-item-code">-</strong></span>
                            <span><i class="bi bi-box me-1 text-primary"></i><strong id="hist-item-name">-</strong></span>
                            <span><i class="bi bi-building me-1 text-primary"></i><strong id="hist-warehouse">-</strong></span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>

            <div class="modal-body">
                <form id="historyFilterForm" class="row g-2 align-items-end mb-3">
                    <div class="col-sm-4">
                        <label class="form-label small fw-semibold mb-1" for="hist-start-date">เริ่ม</label>
                        <input type="date" id="hist-start-date" name="start_date" class="form-control form-control-sm" value="<?= date('Y-m-01') ?>">
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label small fw-semibold mb-1" for="hist-end-date">ถึง</label>
                        <input type="date" id="hist-end-date" name="end_date" class="form-control form-control-sm" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-sm-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-arrow-clockwise me-1"></i>โหลดประวัติ
                        </button>
                    </div>
                </form>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <div class="history-stat brought-forward">
                            <span class="label">ยอดยกมา</span>
                            <span class="value" id="hist-bf-qty">0</span>
                            <span class="sub">มูลค่า <span id="hist-bf-value">0.00</span> บาท</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="history-stat in-out">
                            <span class="label">เคลื่อนไหวในช่วงเวลา</span>
                            <span class="value">
                                <span class="in" id="hist-total-in">+0</span>
                                <span class="text-muted fs-6 fw-normal">/</span>
                                <span class="out" id="hist-total-out">-0</span>
                            </span>
                            <span class="sub"><span id="hist-tx-count">0</span> รายการ</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="history-stat balance">
                            <span class="label">ยอดคงเหลือปัจจุบัน</span>
                            <span class="value" id="hist-current-qty">0</span>
                            <span class="sub"><span id="hist-unit-name">หน่วย</span></span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle history-table mb-0">
                        <thead>
                            <tr>
                                <th class="text-nowrap">วัน/เวลา</th>
                                <th class="text-nowrap">เลขที่เอกสาร</th>
                                <th>รายการ</th>
                                <th class="text-center text-nowrap">ทิศทาง</th>
                                <th class="text-end text-nowrap">จำนวน</th>
                                <th class="text-end text-nowrap">ราคา/หน่วย</th>
                                <th class="text-end text-nowrap">ยอดสะสม</th>
                                <th class="text-end text-nowrap d-none d-md-table-cell">มูลค่าสะสม</th>
                                <th class="text-nowrap small d-none d-md-table-cell">Lot</th>
                            </tr>
                        </thead>
                        <tbody id="hist-tbody">
                            <tr><td colspan="9" class="text-center text-muted py-4">เลือกช่วงเวลาแล้วกด "โหลดประวัติ"</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer border-top">
                <span class="text-muted small me-auto"><i class="bi bi-info-circle me-1"></i>นับเฉพาะเอกสารที่ยืนยันแล้ว (CONFIRMED)</span>
                <button type="button" class="btn btn-success" id="hist-export-btn" disabled>
                    <i class="bi bi-file-earmark-excel me-1"></i>Export Excel
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<?php
$jsHistoryUrl = Json::encode($historyUrl);
$jsExportHistoryUrl = Json::encode($exportHistoryUrl);
$js = <<<JS
(function () {
    var modalEl = document.getElementById('itemHistoryModal');
    if (!modalEl) return;

    var ctx = { item_code: null, warehouse_id: null, unit_name: '-' };
    var exportBtn = document.getElementById('hist-export-btn');
    var fmt = new Intl.NumberFormat('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    var fmtInt = new Intl.NumberFormat('th-TH', { maximumFractionDigits: 0 });

    function setText(id, val) {
        var el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function skeletonRows() {
        var tbody = document.getElementById('hist-tbody');
        if (!tbody) return;
        var rows = '';
        for (var i = 0; i < 5; i++) {
            rows += '<tr>' +
                '<td><div class="history-skeleton" style="width:80%"></div></td>' +
                '<td><div class="history-skeleton" style="width:60%"></div></td>' +
                '<td><div class="history-skeleton" style="width:90%"></div></td>' +
                '<td class="text-center"><div class="history-skeleton mx-auto" style="width:50%"></div></td>' +
                '<td class="text-end"><div class="history-skeleton ms-auto" style="width:50%"></div></td>' +
                '<td class="text-end"><div class="history-skeleton ms-auto" style="width:55%"></div></td>' +
                '<td class="text-end"><div class="history-skeleton ms-auto" style="width:55%"></div></td>' +
                '<td class="text-end d-none d-md-table-cell"><div class="history-skeleton ms-auto" style="width:60%"></div></td>' +
                '<td class="d-none d-md-table-cell"><div class="history-skeleton" style="width:40%"></div></td>' +
                '</tr>';
        }
        tbody.innerHTML = rows;
    }

    function escHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&':'&amp;', '<':'&lt;', '>':'&gt;', '"':'&quot;', "'":'&#39;' }[c];
        });
    }

    function loadHistory() {
        if (!ctx.item_code || !ctx.warehouse_id) return;
        var sd = document.getElementById('hist-start-date').value;
        var ed = document.getElementById('hist-end-date').value;
        skeletonRows();
        if (exportBtn) exportBtn.disabled = true;

        var params = new URLSearchParams({
            item_code: ctx.item_code,
            warehouse_id: ctx.warehouse_id,
            start_date: sd,
            end_date: ed
        });

        fetch($jsHistoryUrl + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            credentials: 'same-origin'
        })
            .then(function (r) { if (!r.ok) throw new Error(r.status); return r.json(); })
            .then(function (res) {
                render(res);
                if (exportBtn) exportBtn.disabled = false;
            })
            .catch(function () {
                var tbody = document.getElementById('hist-tbody');
                tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-4">' +
                    '<i class="bi bi-exclamation-triangle me-1"></i>โหลดข้อมูลไม่สำเร็จ ลองอีกครั้ง</td></tr>';
            });
    }

    function exportExcel() {
        if (!ctx.item_code || !ctx.warehouse_id) return;
        var sd = document.getElementById('hist-start-date').value;
        var ed = document.getElementById('hist-end-date').value;
        var params = new URLSearchParams({
            item_code: ctx.item_code,
            warehouse_id: ctx.warehouse_id,
            start_date: sd,
            end_date: ed
        });
        window.location.href = $jsExportHistoryUrl + '?' + params.toString();
    }

    function render(res) {
        setText('hist-bf-qty', fmt.format(res.summary.qty_bf));
        setText('hist-bf-value', fmt.format(res.summary.value_bf));
        setText('hist-total-in', '+' + fmt.format(res.summary.total_in));
        setText('hist-total-out', '-' + fmt.format(res.summary.total_out));
        setText('hist-current-qty', fmt.format(res.summary.current_qty));
        setText('hist-tx-count', fmtInt.format(res.summary.tx_count));

        var unit = res.meta.unit_name || ctx.unit_name || '-';
        setText('hist-unit-name', 'หน่วย: ' + unit);

        if (res.meta.image_url) {
            var thumb = document.getElementById('hist-thumb');
            if (thumb && thumb.src !== res.meta.image_url) {
                thumb.src = res.meta.image_url;
            }
        }

        var tbody = document.getElementById('hist-tbody');
        var html = '';

        html += '<tr class="bf-row">' +
            '<td colspan="3" class="text-end small">' +
                '<i class="bi bi-arrow-bar-right me-1"></i>ยอดยกมา ณ ' + escHtml(res.meta.start_date) +
            '</td>' +
            '<td class="text-center small text-muted">—</td>' +
            '<td class="text-end">—</td>' +
            '<td class="text-end">—</td>' +
            '<td class="text-end">' + fmt.format(res.summary.qty_bf) + '</td>' +
            '<td class="text-end d-none d-md-table-cell">' + fmt.format(res.summary.value_bf) + '</td>' +
            '<td class="d-none d-md-table-cell text-muted small">—</td>' +
        '</tr>';

        if (!res.transactions.length) {
            html += '<tr><td colspan="9" class="text-center text-muted py-4">' +
                '<i class="bi bi-inbox me-1"></i>ไม่พบการเคลื่อนไหวในช่วงเวลานี้</td></tr>';
        } else {
            res.transactions.forEach(function (t) {
                var pillCls = t.direction === 'in' ? 'in' : 'out';
                var pillLabel = t.direction === 'in' ? 'รับเข้า' : 'จ่ายออก';
                var pillIcon = t.direction === 'in' ? 'arrow-down-left' : 'arrow-up-right';
                html += '<tr>' +
                    '<td class="text-nowrap small">' + escHtml(t.date) + ' <span class="text-muted">' + escHtml(t.time) + '</span></td>' +
                    '<td class="text-nowrap"><span class="badge text-bg-light border text-body">' + escHtml(t.order_no) + '</span></td>' +
                    '<td class="small">' + escHtml(t.source_label) + '</td>' +
                    '<td class="text-center"><span class="direction-pill ' + pillCls + '">' +
                        '<i class="bi bi-' + pillIcon + '"></i>' + pillLabel + '</span></td>' +
                    '<td class="text-end fw-semibold ' + (t.direction === 'in' ? 'text-success' : 'text-danger') + '">' +
                        (t.direction === 'in' ? '+' : '-') + fmt.format(t.qty) + '</td>' +
                    '<td class="text-end text-muted small">' + fmt.format(t.unit_price) + '</td>' +
                    '<td class="text-end fw-semibold">' + fmt.format(t.balance_qty) + '</td>' +
                    '<td class="text-end text-primary d-none d-md-table-cell">' + fmt.format(t.balance_value) + '</td>' +
                    '<td class="d-none d-md-table-cell text-muted small">' + escHtml(t.lot) + '</td>' +
                '</tr>';
            });
        }

        tbody.innerHTML = html;
    }

    modalEl.addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        if (!btn) return;
        ctx.item_code = btn.getAttribute('data-item-code');
        ctx.warehouse_id = btn.getAttribute('data-warehouse-id');
        ctx.unit_name = btn.getAttribute('data-unit-name') || '-';

        setText('hist-item-code', ctx.item_code);
        setText('hist-item-name', btn.getAttribute('data-item-name') || '-');
        setText('hist-warehouse', btn.getAttribute('data-warehouse-name') || '-');
        setText('hist-unit-name', 'หน่วย: ' + ctx.unit_name);

        var thumb = document.getElementById('hist-thumb');
        var img = btn.getAttribute('data-item-image');
        if (thumb && img) {
            thumb.src = img;
            thumb.alt = btn.getAttribute('data-item-name') || '';
        }

        // reset stats
        ['hist-bf-qty','hist-bf-value','hist-current-qty'].forEach(function (id) { setText(id, '0.00'); });
        setText('hist-total-in', '+0.00');
        setText('hist-total-out', '-0.00');
        setText('hist-tx-count', '0');
    });

    modalEl.addEventListener('shown.bs.modal', function () {
        loadHistory();
    });

    document.getElementById('historyFilterForm').addEventListener('submit', function (e) {
        e.preventDefault();
        loadHistory();
    });

    if (exportBtn) {
        exportBtn.addEventListener('click', exportExcel);
    }
})();
JS;
$this->registerJs($js, View::POS_END);

$this->registerJs(<<<JS
(function () {
    var form = document.getElementById('balance-filter-form');
    if (!form) return;
    form.addEventListener('change', function (e) {
        if (e.target && e.target.tagName === 'SELECT') {
            form.submit();
        }
    });

    var searchInput = document.getElementById('balance-search');
    if (searchInput) {
        var initial = searchInput.value;
        var timer = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(function () {
                if (searchInput.value !== initial) form.submit();
            }, 450);
        });
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(timer);
                form.submit();
            }
        });
    }
})();
JS, View::POS_READY);
?>

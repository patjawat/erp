<?php
/**
 * Sub-stock module navigation — แสดงบนทุกหน้าของ sub-stock
 * Layout: tabs (left, horizontal scroll on mobile) + optional warehouse filter (right)
 *
 * @var string $active 'dashboard' | 'issue' | 'requisition' | 'balance' | 'history'
 * @var int|null $currentWarehouseId  ส่งผ่าน warehouse_id เพื่อคง context
 * @var array $warehouses             optional — ถ้ามีจะ render filter dropdown
 * @var string|array $filterAction    optional — submit URL (default: dashboard)
 */
use yii\helpers\Html;
use yii\helpers\Url;

$active = $active ?? '';
$currentWarehouseId = $currentWarehouseId ?? null;
$warehouses = $warehouses ?? [];
$filterAction = $filterAction ?? ['/inventory-v2/sub-stock/dashboard'];

$wParam = $currentWarehouseId ? ['warehouse_id' => (int) $currentWarehouseId] : [];

$items = [
    [
        'key'   => 'dashboard',
        'label' => 'ภาพรวม',
        'icon'  => 'bi-grid-1x2',
        'url'   => array_merge(['/inventory-v2/sub-stock/dashboard'], $wParam),
    ],
    [
        'key'   => 'issue',
        'label' => 'บันทึกจ่าย',
        'icon'  => 'bi-box-arrow-up-right',
        'url'   => array_merge(['/inventory-v2/sub-stock/issue'], $wParam),
    ],
    [
        'key'   => 'requisition',
        'label' => 'ใบขอเบิก',
        'icon'  => 'bi-file-earmark-plus',
        'url'   => ['/inventory-v2/requisition'],
    ],
    [
        'key'   => 'balance',
        'label' => 'สถานะคงคลัง',
        'icon'  => 'bi-boxes',
        'url'   => array_merge(['/inventory-v2/report/balance-by-warehouse'], $wParam),
    ],
    [
        'key'   => 'history',
        'label' => 'ประวัติการใช้',
        'icon'  => 'bi-clock-history',
        'url'   => array_merge(['/inventory-v2/sub-stock/dashboard'], $wParam, ['#' => 'section-history']),
    ],
];

$hasFilter = !empty($warehouses);
?>
<nav class="sub-nav" aria-label="เมนูคลังย่อย">
    <div class="container-fluid sub-nav__container">
        <div class="sub-nav__scroll">
            <ul class="sub-nav__list" role="tablist">
                <?php foreach ($items as $item):
                    $isActive = $active === $item['key'];
                ?>
                    <li role="presentation">
                        <a href="<?= Url::to($item['url']) ?>"
                           role="tab"
                           aria-current="<?= $isActive ? 'page' : 'false' ?>"
                           aria-selected="<?= $isActive ? 'true' : 'false' ?>"
                           class="sub-nav__item<?= $isActive ? ' is-active' : '' ?>">
                            <i class="bi <?= Html::encode($item['icon']) ?>" aria-hidden="true"></i>
                            <span><?= Html::encode($item['label']) ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php if ($hasFilter): ?>
            <form method="get"
                  action="<?= Url::to($filterAction) ?>"
                  id="form-sub-warehouse"
                  class="sub-nav__filter">
                <label for="subWarehouseFilter" class="sub-nav__filter-label">คลัง</label>
                <div class="sub-nav__filter-field">
                    <i class="bi bi-shop" aria-hidden="true"></i>
                    <select name="warehouse_id" id="subWarehouseFilter" aria-label="เลือกคลังย่อย">
                        <option value="all" <?= $currentWarehouseId === null ? 'selected' : '' ?>>คลังย่อยทั้งหมด</option>
                        <?php foreach ($warehouses as $w): ?>
                            <option value="<?= (int)$w->id ?>" <?= (int)$w->id === (int)$currentWarehouseId ? 'selected' : '' ?>>
                                <?= Html::encode($w->warehouse_name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <i class="bi bi-chevron-down sub-nav__filter-caret" aria-hidden="true"></i>
                </div>
            </form>
        <?php endif; ?>
    </div>
</nav>

<style>
.sub-nav {
    --sn-ink-1: #1a202c;
    --sn-ink-2: #4a5568;
    --sn-ink-3: #718096;
    --sn-surface: #ffffff;
    --sn-surface-2: #f7f9fc;
    --sn-line: rgba(15, 23, 42, 0.08);
    --sn-line-strong: rgba(15, 23, 42, 0.14);
    --sn-primary: #0d6efd;
    --sn-primary-ink: #0a58ca;
    --sn-primary-soft: rgba(13, 110, 253, 0.08);
    --sn-primary-line: rgba(13, 110, 253, 0.22);
    --sn-radius-sm: 8px;
    --sn-ease: cubic-bezier(0.16, 1, 0.3, 1);

    background: var(--sn-surface);
    border-bottom: 1px solid var(--sn-line);
    margin-bottom: 1rem;
}
.sub-nav__container {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding-top: 0.4rem;
    padding-bottom: 0.4rem;
}
.sub-nav__scroll {
    flex: 1;
    min-width: 0;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.sub-nav__scroll::-webkit-scrollbar { display: none; }

.sub-nav__list {
    display: flex;
    gap: 0.25rem;
    list-style: none;
    margin: 0;
    padding: 0;
    min-width: max-content;
}

.sub-nav__item {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.5rem 0.85rem;
    min-height: 38px;
    color: var(--sn-ink-2);
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: var(--sn-radius-sm);
    transition: background-color 120ms var(--sn-ease), color 120ms var(--sn-ease);
    white-space: nowrap;
}
.sub-nav__item:hover {
    background: var(--sn-surface-2);
    color: var(--sn-ink-1);
}
.sub-nav__item:focus-visible {
    outline: none;
    box-shadow: 0 0 0 3px var(--sn-primary-soft);
    color: var(--sn-ink-1);
}
.sub-nav__item.is-active {
    color: var(--sn-primary);
    background: var(--sn-primary-soft);
    font-weight: 600;
    box-shadow: inset 0 0 0 1px var(--sn-primary-line);
}
.sub-nav__item i {
    font-size: 1rem;
    line-height: 1;
}

/* Warehouse filter (right of tabs) */
.sub-nav__filter {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}
.sub-nav__filter-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--sn-ink-2);
    margin: 0;
    white-space: nowrap;
}
.sub-nav__filter-field {
    position: relative;
    min-width: 220px;
}
.sub-nav__filter-field > i:first-child {
    position: absolute;
    left: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sn-ink-3);
    font-size: 0.95rem;
    pointer-events: none;
}
.sub-nav__filter-caret {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    color: var(--sn-ink-3);
    font-size: 0.8rem;
    pointer-events: none;
}
.sub-nav__filter select {
    appearance: none;
    -webkit-appearance: none;
    width: 100%;
    min-height: 38px;
    padding: 0 2.25rem 0 2.25rem;
    background: var(--sn-surface);
    border: 1px solid var(--sn-line-strong);
    border-radius: var(--sn-radius-sm);
    font-size: 0.875rem;
    color: var(--sn-ink-1);
    transition: border-color 120ms var(--sn-ease), box-shadow 120ms var(--sn-ease);
    cursor: pointer;
}
.sub-nav__filter select:hover { border-color: var(--sn-ink-3); }
.sub-nav__filter select:focus-visible {
    outline: none;
    border-color: var(--sn-primary);
    box-shadow: 0 0 0 3px var(--sn-primary-soft);
}

@media (max-width: 767.98px) {
    .sub-nav__container {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .sub-nav__scroll { width: 100%; flex: 1 1 100%; }
    .sub-nav__filter {
        width: 100%;
        flex: 1 1 100%;
        padding-top: 0.25rem;
        border-top: 1px solid var(--sn-line);
    }
    .sub-nav__filter-field { flex: 1; min-width: 0; }
    .sub-nav__filter-label { display: none; }
}

@media (max-width: 575.98px) {
    .sub-nav__item { padding: 0.45rem 0.75rem; font-size: 0.85rem; }
    .sub-nav__item i { font-size: 0.95rem; }
}

@media (prefers-reduced-motion: reduce) {
    .sub-nav__item,
    .sub-nav__filter select { transition: none; }
}
</style>

<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'ตัดจ่ายคลังย่อย';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'Dashboard คลังย่อย', 'url' => ['/inventory-v2/sub-stock/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$subWarehouses = $subWarehouses ?? [];
$usageHistory = $usageHistory ?? [];
$currentWarehouseId = $currentWarehouseId ?? null;
$canCreateRequisition = (bool) ($canCreateRequisition ?? false);
$getLotsUrl = Url::to(['/inventory-v2/sub-stock/get-available-lots']);
$saveUrl = Url::to(['/inventory-v2/sub-stock/save-usage']);
$repairPickerUrl = Url::to(['/inventory-v2/sub-stock/repair-picker', 'title' => '<i class="bi bi-tools me-1"></i> เลือกใบแจ้งซ่อม']);
$requisitionUrl = Url::to(['/inventory-v2/requisition']);
$dashboardUrl = Url::to(['/inventory-v2/sub-stock/dashboard']);

$recentItemCounts = [];
foreach ($usageHistory as $o) {
    $details = is_array($o->stockDetails) ? $o->stockDetails : [];
    foreach ($details as $d) {
        $code = (string) ($d->item_code ?? '');
        if ($code !== '') {
            $recentItemCounts[$code] = ($recentItemCounts[$code] ?? 0) + 1;
        }
    }
}
arsort($recentItemCounts);
$recentItemCodes = array_slice(array_keys($recentItemCounts), 0, 5);

$jobOptions = [
    ['value' => 'patient',     'label' => 'งานคลินิก',  'caption' => 'รายคนไข้',   'icon' => 'bi-person-vcard'],
    ['value' => 'maintenance', 'label' => 'ซ่อมบำรุง',  'caption' => 'งานช่าง/ไอที', 'icon' => 'bi-tools'],
    ['value' => 'office',      'label' => 'บริหาร',     'caption' => 'บัญชี/ธุรการ', 'icon' => 'bi-briefcase'],
    ['value' => 'emergency',   'label' => 'ฉุกเฉิน',    'caption' => 'อุบัติเหตุ',    'icon' => 'bi-exclamation-octagon'],
];
?>

<?php $this->beginBlock('page-title'); ?>
<?= $this->render('_page_head', [
    'icon'  => 'bi-box-arrow-up-right',
    'title' => $this->title,
]) ?>
<?php $this->endBlock(); ?>

<?php
$subStockActionMenu = $this->render('_menu_sub_stock', [
    'active' => 'issue',
    'currentWarehouseId' => $currentWarehouseId,
]);
foreach (['action', 'page-action'] as $actionBlock) {
    $this->beginBlock($actionBlock);
    echo $subStockActionMenu;
    $this->endBlock();
}
?>

<div class="container-fluid py-3 py-md-4 px-3 px-md-4 sub-stock-issue">

<?php if (empty($subWarehouses)): ?>
    <div class="empty-block">
        <div class="empty-block__icon">
            <i class="bi bi-exclamation-triangle"></i>
        </div>
        <h5 class="fw-semibold mb-2">ไม่พบคลังย่อยที่คุณรับผิดชอบ</h5>
        <p class="text-muted mb-4">ติดต่อผู้ดูแลระบบเพื่อกำหนดสิทธิ์ดูแลคลังย่อย ก่อนเริ่มตัดจ่ายพัสดุ</p>
        <a href="<?= $dashboardUrl ?>" class="btn btn-outline-primary px-3">
            <i class="bi bi-arrow-left me-1"></i>กลับ Dashboard
        </a>
    </div>
<?php else: ?>

<header class="ctx-bar" aria-label="บริบทการเบิก">
    <div class="ctx-bar__field ctx-bar__field--wh">
        <label for="warehouseSelect" class="ctx-bar__label">คลังย่อย</label>
        <div class="ctx-bar__control">
            <i class="bi bi-house-door ctx-bar__icon" aria-hidden="true"></i>
            <select class="form-select ctx-bar__select" id="warehouseSelect" required>
                <option value="">เลือกคลังย่อย</option>
                <?php foreach ($subWarehouses as $w): ?>
                    <option value="<?= (int)$w->id ?>" <?= $currentWarehouseId && (int)$w->id === (int)$currentWarehouseId ? 'selected' : '' ?>>
                        <?= Html::encode($w->warehouse_name) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="ctx-bar__field ctx-bar__field--job">
        <span class="ctx-bar__label">ประเภทงาน</span>
        <div class="seg-control ctx-bar__seg" role="radiogroup" aria-label="ประเภทงาน">
            <?php foreach ($jobOptions as $i => $opt): ?>
                <button type="button"
                        class="seg-control__item <?= $i === 0 ? 'is-active' : '' ?>"
                        data-value="<?= $opt['value'] ?>"
                        role="radio"
                        aria-checked="<?= $i === 0 ? 'true' : 'false' ?>">
                    <i class="bi <?= $opt['icon'] ?> seg-control__icon"></i>
                    <span class="seg-control__title"><?= $opt['label'] ?></span>
                </button>
            <?php endforeach; ?>
        </div>
        <select id="jobType" hidden aria-hidden="true">
            <?php foreach ($jobOptions as $opt): ?>
                <option value="<?= $opt['value'] ?>" data-caption="<?= Html::encode($opt['caption']) ?>"><?= $opt['label'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="ctx-bar__field ctx-bar__field--ref">
        <label for="referenceInput" class="ctx-bar__label" id="dynamicLabel">HN / ชื่อคนไข้</label>
        <div class="ctx-bar__control" id="referenceControl">
            <i class="bi bi-person-vcard ctx-bar__icon" id="referenceIcon" aria-hidden="true"></i>
            <input type="text" class="form-control ctx-bar__input" id="referenceInput"
                   placeholder="ระบุอ้างอิง (ถ้ามี)">
            <input type="hidden" id="repairHelpdeskId" value="">
            <a href="<?= $repairPickerUrl ?>"
               class="ctx-bar__addon open-modal"
               id="btnPickRepair"
               data-size="modal-xl"
               hidden
               aria-label="เลือกใบแจ้งซ่อม">
                <i class="bi bi-search" aria-hidden="true"></i>
                <span>เลือก</span>
            </a>
        </div>
    </div>
</header>

<div class="pos-shell">

    <main class="pos-items" aria-labelledby="hdrItems">
        <div class="pos-items__head">
            <h2 id="hdrItems" class="pos-items__title">
                <i class="bi bi-boxes"></i>
                รายการพัสดุ
                <span class="count-pill pos-items__pill" id="itemsCount" hidden>0</span>
            </h2>
            <div class="search-input-wrap pos-items__search">
                <i class="bi bi-search search-input__icon" aria-hidden="true"></i>
                <input type="search"
                       id="itemSearchInput"
                       class="form-control form-control-input search-input"
                       placeholder="ค้นหาด้วยชื่อ หรือ รหัสพัสดุ"
                       autocomplete="off"
                       inputmode="search"
                       aria-controls="itemGrid">
                <button type="button" id="itemSearchClear" class="search-input__clear" hidden aria-label="ล้างคำค้น">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
            </div>
        </div>

        <div class="recent-chips pos-items__recent" id="recentChips" hidden>
            <div class="recent-chips__label">
                <i class="bi bi-clock-history" aria-hidden="true"></i>เบิกล่าสุด
            </div>
            <div class="recent-chips__list" id="recentChipsList"></div>
        </div>

        <div class="pos-state" id="stateNoWarehouse">
            <div class="pos-state__icon"><i class="bi bi-arrow-up-circle"></i></div>
            <div class="pos-state__title">เลือกคลังย่อยจากแถบด้านบน</div>
            <div class="pos-state__caption">เพื่อโหลดรายการพัสดุที่มีในสต็อก</div>
        </div>

        <div class="pos-state d-none" id="stateLoading" aria-live="polite">
            <div class="pos-state__icon"><i class="bi bi-arrow-clockwise spin"></i></div>
            <div class="pos-state__title">กำลังโหลดรายการพัสดุ</div>
        </div>

        <div class="pos-state d-none" id="stateNoItems">
            <div class="pos-state__icon pos-state__icon--warn"><i class="bi bi-inbox"></i></div>
            <div class="pos-state__title">คลังนี้ยังไม่มีพัสดุในสต็อก</div>
            <div class="pos-state__caption mb-3">ต้องรับของจากคลังหลักก่อนจึงตัดจ่ายได้</div>
            <?php if ($canCreateRequisition): ?>
            <a href="<?= $requisitionUrl ?>" class="btn btn-primary px-3">
                <i class="bi bi-file-earmark-plus me-1"></i>สร้างใบขอเบิก
            </a>
            <?php endif; ?>
        </div>

        <div class="pos-state d-none" id="stateNoMatch">
            <div class="pos-state__icon"><i class="bi bi-search"></i></div>
            <div class="pos-state__title">ไม่พบ "<span id="noMatchQuery"></span>"</div>
            <div class="pos-state__caption">ลองค้นด้วยคำอื่นหรือรหัสพัสดุ</div>
        </div>

        <ul class="pos-grid d-none" id="itemGrid" role="list" aria-label="รายการพัสดุที่เบิกได้"></ul>
    </main>

    <aside class="pos-cart" aria-labelledby="hdrCart" id="cartPanel">
        <header class="pos-cart__head">
            <h2 id="hdrCart" class="pos-cart__title">
                <i class="bi bi-journal-check" aria-hidden="true"></i>
                รายการจ่าย
            </h2>
            <span class="count-pill" id="cartCount">0 รายการ</span>
            <button type="button" class="icon-btn pos-cart__close d-lg-none" id="btnCloseCart" aria-label="ปิดรายการจ่าย">
                <i class="bi bi-x-lg"></i>
            </button>
        </header>

        <div class="pos-cart__body">
            <div id="cartEmpty" class="cart-empty">
                <i class="bi bi-cart3 cart-empty__icon" aria-hidden="true"></i>
                <div class="cart-empty__title">ยังไม่มีรายการ</div>
                <div class="cart-empty__caption">คลิกพัสดุด้านซ้ายเพื่อเพิ่มเข้ารายการจ่าย</div>
            </div>

            <ul class="cart-list d-none" id="cartList" role="list"></ul>
        </div>

        <footer class="pos-cart__foot">
            <div class="pos-cart__totals" id="summaryTotals" hidden>
                <div class="pos-cart__total-row">
                    <span>รายการ</span>
                    <span class="pos-cart__total-value" id="totalCount">0</span>
                </div>
                <div class="pos-cart__total-row pos-cart__total-row--strong">
                    <span>จำนวนรวม</span>
                    <span class="pos-cart__total-value" id="totalQty">0</span>
                </div>
            </div>
            <button type="button" class="btn btn-primary btn-save pos-cart__save" id="btnSaveFinal" disabled>
                <span class="btn-save__label"><i class="bi bi-check2"></i> ยืนยันการบันทึก</span>
                <span class="btn-save__progress" aria-hidden="true"></span>
            </button>
            <a href="<?= $dashboardUrl ?>" class="btn btn-light pos-cart__cancel">ยกเลิก</a>
        </footer>
    </aside>

    <div class="pos-cart__backdrop" id="cartBackdrop" hidden aria-hidden="true"></div>
</div>

<button type="button" class="pos-cart-fab d-lg-none" id="btnOpenCart" hidden aria-label="ดูรายการจ่าย">
    <span class="pos-cart-fab__icon" aria-hidden="true">
        <i class="bi bi-journal-check"></i>
        <span class="pos-cart-fab__badge" id="fabBadge">0</span>
    </span>
    <span class="pos-cart-fab__meta">
        <span class="pos-cart-fab__count" id="fabCount">0 รายการ</span>
        <span class="pos-cart-fab__qty" id="fabQty">รวม 0</span>
    </span>
    <span class="pos-cart-fab__cta">เปิดดู <i class="bi bi-chevron-up" aria-hidden="true"></i></span>
</button>

<div class="undo-toast" id="undoToast" hidden role="status" aria-live="polite">
    <i class="bi bi-arrow-counterclockwise undo-toast__icon"></i>
    <span class="undo-toast__text" id="undoToastText">ลบรายการแล้ว</span>
    <button type="button" class="undo-toast__btn" id="btnUndo">เลิกทำ</button>
</div>

<div class="mt-4">
    <?= $this->render('use_history', ['usageHistory' => $usageHistory, 'currentWarehouseId' => $currentWarehouseId]) ?>
</div>

<?php endif; ?>
</div>

<style>
.sub-stock-issue {
    --ink-1: #1a202c;
    --ink-2: #4a5568;
    --ink-3: #718096;
    --ink-4: #a0aec0;

    --surface: #ffffff;
    --surface-2: #f7f9fc;
    --surface-3: #eef2f7;
    --surface-hover: #f1f5f9;

    --line: rgba(15, 23, 42, 0.08);
    --line-strong: rgba(15, 23, 42, 0.14);

    --primary: #0d6efd;
    --primary-ink: #0a58ca;
    --primary-soft: rgba(13, 110, 253, 0.08);
    --primary-line: rgba(13, 110, 253, 0.22);

    --success: #15803d;
    --success-soft: rgba(21, 128, 61, 0.10);
    --warning: #b45309;
    --warning-soft: rgba(180, 83, 9, 0.10);
    --danger: #b91c1c;
    --danger-soft: rgba(185, 28, 28, 0.10);

    --radius: 12px;
    --radius-sm: 8px;
    --radius-xs: 6px;
    --shadow-1: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 1px rgba(15, 23, 42, 0.03);
    --shadow-2: 0 6px 18px rgba(15, 23, 42, 0.06), 0 2px 4px rgba(15, 23, 42, 0.04);
    --shadow-3: 0 12px 32px rgba(15, 23, 42, 0.10), 0 4px 8px rgba(15, 23, 42, 0.05);

    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    --ease-in: cubic-bezier(0.7, 0, 0.84, 0);
    --t-fast: 120ms var(--ease);
    --t-mid: 180ms var(--ease);
    --t-slow: 240ms var(--ease);

    color: var(--ink-1);
}

/* ─── Empty (no warehouses permission) ─── */
.sub-stock-issue .empty-block {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    padding: 2.5rem 1.5rem;
    text-align: center;
    box-shadow: var(--shadow-1);
}
.sub-stock-issue .empty-block__icon {
    width: 64px; height: 64px;
    margin: 0 auto 1rem;
    border-radius: 16px;
    background: var(--warning-soft);
    color: var(--warning);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.7rem;
}

/* ─── Context bar (compact, top of page) ─── */
.sub-stock-issue .ctx-bar {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    gap: 0.85rem 1.1rem;
    padding: 0.85rem 1rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    margin-bottom: 0.85rem;
}
.sub-stock-issue .ctx-bar__field {
    display: flex; flex-direction: column;
    gap: 0.35rem;
    min-width: 0;
}
.sub-stock-issue .ctx-bar__field--wh { flex: 1 1 220px; }
.sub-stock-issue .ctx-bar__field--job { flex: 2 1 380px; }
.sub-stock-issue .ctx-bar__field--ref { flex: 1 1 220px; }
.sub-stock-issue .ctx-bar__label {
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ink-2);
    line-height: 1.2;
    margin: 0;
}
.sub-stock-issue .ctx-bar__control {
    position: relative;
    display: flex;
    align-items: center;
}
.sub-stock-issue .ctx-bar__icon {
    position: absolute;
    left: 0.8rem;
    color: var(--ink-3);
    font-size: 0.95rem;
    pointer-events: none;
}
.sub-stock-issue .ctx-bar__select,
.sub-stock-issue .ctx-bar__input {
    width: 100%;
    min-height: 42px;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    padding: 0.5rem 0.85rem 0.5rem 2.4rem;
    font-size: 0.95rem;
    color: var(--ink-1);
    background: var(--surface);
    transition: border-color var(--t-fast), box-shadow var(--t-fast);
}
.sub-stock-issue .ctx-bar__select {
    appearance: none; -webkit-appearance: none;
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><path fill='%23718096' d='M3.5 5.5l4.5 4.5 4.5-4.5'/></svg>");
    background-repeat: no-repeat;
    background-position: right 0.7rem center;
    padding-right: 2.2rem;
}
.sub-stock-issue .ctx-bar__select:hover,
.sub-stock-issue .ctx-bar__input:hover { border-color: rgba(15, 23, 42, 0.22); }
.sub-stock-issue .ctx-bar__control.has-addon .ctx-bar__input { padding-right: 5.25rem; }
.sub-stock-issue .ctx-bar__addon {
    position: absolute;
    right: 0.35rem;
    top: 50%;
    transform: translateY(-50%);
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    min-height: 32px;
    padding: 0 0.55rem;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-xs);
    background: var(--surface-2);
    color: var(--primary-ink);
    font-size: 0.82rem;
    font-weight: 700;
    text-decoration: none;
    transition: background var(--t-fast), border-color var(--t-fast), box-shadow var(--t-fast);
}
.sub-stock-issue .ctx-bar__addon[hidden] { display: none; }
.sub-stock-issue .ctx-bar__addon:hover {
    background: var(--surface-hover);
    border-color: var(--primary-line);
    color: var(--primary-ink);
}
.sub-stock-issue .ctx-bar__addon:focus-visible {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.sub-stock-issue .ctx-bar__select:focus,
.sub-stock-issue .ctx-bar__select:focus-visible,
.sub-stock-issue .ctx-bar__input:focus,
.sub-stock-issue .ctx-bar__input:focus-visible {
    border-color: var(--primary);
    outline: 0;
    box-shadow: 0 0 0 3px var(--primary-soft);
}

/* segmented control inside ctx bar */
.sub-stock-issue .ctx-bar__seg {
    display: flex;
    gap: 0.25rem;
    padding: 0.25rem;
    background: var(--surface-2);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
}
.sub-stock-issue .seg-control__item {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    padding: 0.45rem 0.5rem;
    background: transparent;
    border: 1px solid transparent;
    border-radius: var(--radius-xs);
    color: var(--ink-2);
    font-size: 0.85rem;
    min-height: 40px;
    cursor: pointer;
    transition: background-color var(--t-fast), color var(--t-fast), border-color var(--t-fast), box-shadow var(--t-fast);
    line-height: 1.2;
}
.sub-stock-issue .seg-control__item:hover {
    background: var(--surface);
    color: var(--ink-1);
}
.sub-stock-issue .seg-control__icon { font-size: 1rem; }
.sub-stock-issue .seg-control__title { font-weight: 600; font-size: 0.84rem; }
.sub-stock-issue .seg-control__item.is-active {
    background: var(--surface);
    border-color: var(--primary);
    color: var(--primary);
    box-shadow: 0 0 0 2px var(--primary-soft);
}
.sub-stock-issue .seg-control__item:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

@media (max-width: 575.98px) {
    .sub-stock-issue .ctx-bar__field--job { flex: 1 1 100%; }
    .sub-stock-issue .seg-control__item .seg-control__title { display: none; }
    .sub-stock-issue .seg-control__item.is-active .seg-control__title { display: inline; }
}

/* ─── POS shell ─── */
.sub-stock-issue .pos-shell {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 380px;
    gap: 1rem;
    align-items: start;
}
@media (max-width: 1199.98px) {
    .sub-stock-issue .pos-shell { grid-template-columns: minmax(0, 1fr) 340px; }
}
@media (max-width: 991.98px) {
    .sub-stock-issue .pos-shell { grid-template-columns: minmax(0, 1fr); }
}

/* ─── Items panel ─── */
.sub-stock-issue .pos-items {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    padding: 1rem 1.1rem 1.25rem;
    min-height: 60vh;
}
.sub-stock-issue .pos-items__head {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.85rem;
    flex-wrap: wrap;
}
.sub-stock-issue .pos-items__title {
    font-size: 1.02rem; font-weight: 600;
    color: var(--ink-1);
    margin: 0;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    flex-shrink: 0;
}
.sub-stock-issue .pos-items__title i { color: var(--primary); font-size: 1.1rem; }
.sub-stock-issue .pos-items__search { flex: 1 1 280px; min-width: 0; }
.sub-stock-issue .pos-items__recent { margin-bottom: 0.85rem; }

/* ─── Form control ─── */
.sub-stock-issue .form-control-input {
    min-height: 42px;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    padding: 0.5rem 0.85rem;
    font-size: 0.95rem;
    color: var(--ink-1);
    background: var(--surface);
    transition: border-color var(--t-fast), box-shadow var(--t-fast);
}
.sub-stock-issue .form-control-input:hover { border-color: rgba(15, 23, 42, 0.22); }
.sub-stock-issue .form-control-input:focus,
.sub-stock-issue .form-control-input:focus-visible {
    border-color: var(--primary);
    outline: 0;
    box-shadow: 0 0 0 3px var(--primary-soft);
}

/* ─── Search input ─── */
.sub-stock-issue .search-input-wrap { position: relative; }
.sub-stock-issue .search-input { padding-left: 2.5rem; padding-right: 2.5rem; }
.sub-stock-issue .search-input__icon {
    position: absolute; left: 0.9rem; top: 50%;
    transform: translateY(-50%);
    color: var(--ink-3); font-size: 1rem;
    pointer-events: none;
}
.sub-stock-issue .search-input__clear {
    position: absolute; right: 0.5rem; top: 50%;
    transform: translateY(-50%);
    background: none; border: 0;
    color: var(--ink-4); padding: 0.3rem; line-height: 1;
    border-radius: 50%;
    cursor: pointer;
    transition: color var(--t-fast), background-color var(--t-fast);
}
.sub-stock-issue .search-input__clear:hover { color: var(--ink-1); background: var(--surface-hover); }

/* ─── Recent chips ─── */
.sub-stock-issue .recent-chips__label {
    font-size: 0.78rem; color: var(--ink-3); font-weight: 500;
    margin-bottom: 0.45rem;
    display: inline-flex; align-items: center; gap: 0.35rem;
}
.sub-stock-issue .recent-chips__list { display: flex; flex-wrap: wrap; gap: 0.35rem; }
.sub-stock-issue .recent-chip {
    display: inline-flex; align-items: center; gap: 0.35rem;
    padding: 0.35rem 0.7rem;
    background: var(--surface-2);
    border: 1px solid var(--line);
    color: var(--ink-1);
    border-radius: 999px;
    font-size: 0.82rem; font-weight: 500;
    cursor: pointer;
    transition: background-color var(--t-fast), border-color var(--t-fast), color var(--t-fast);
    text-align: left;
    max-width: 100%;
}
.sub-stock-issue .recent-chip:hover {
    background: var(--primary-soft);
    border-color: var(--primary-line);
    color: var(--primary-ink);
}
.sub-stock-issue .recent-chip i { color: var(--ink-3); font-size: 0.82rem; }
.sub-stock-issue .recent-chip:hover i { color: var(--primary); }
.sub-stock-issue .recent-chip__name {
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    max-width: 12rem;
}

/* ─── Item grid (POS tiles) ─── */
.sub-stock-issue .pos-grid {
    list-style: none;
    margin: 0;
    padding: 0;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 0.7rem;
}
.sub-stock-issue .pos-tile {
    position: relative;
    display: flex; flex-direction: column;
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    padding: 0.75rem 0.8rem;
    cursor: pointer;
    text-align: left;
    transition: border-color var(--t-fast), box-shadow var(--t-fast), transform 100ms var(--ease), background-color var(--t-fast);
    min-height: 132px;
    overflow: hidden;
    font: inherit;
    color: inherit;
}
.sub-stock-issue .pos-tile:hover {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft), var(--shadow-1);
}
.sub-stock-issue .pos-tile:focus-visible {
    outline: 0;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft), var(--shadow-1);
}
.sub-stock-issue .pos-tile:active { transform: translateY(1px); }
.sub-stock-issue .pos-tile.is-out {
    cursor: not-allowed;
    opacity: 0.55;
}
.sub-stock-issue .pos-tile.is-out:hover {
    border-color: var(--line-strong);
    box-shadow: none;
    transform: none;
}
.sub-stock-issue .pos-tile.is-in-cart {
    border-color: var(--primary-line);
    background: var(--primary-soft);
}
.sub-stock-issue .pos-tile__top {
    display: flex; gap: 0.55rem;
    align-items: flex-start;
    margin-bottom: 0.55rem;
    min-width: 0;
}
.sub-stock-issue .pos-tile__avatar {
    flex-shrink: 0;
    width: 32px; height: 32px;
    border-radius: 8px;
    background: var(--surface-3);
    color: var(--ink-2);
    display: inline-flex; align-items: center; justify-content: center;
    font-weight: 700;
    font-size: 0.85rem;
    text-transform: uppercase;
}
.sub-stock-issue .pos-tile.is-in-cart .pos-tile__avatar {
    background: var(--primary);
    color: #fff;
}
.sub-stock-issue .pos-tile__body { min-width: 0; flex-grow: 1; }
.sub-stock-issue .pos-tile__name {
    font-weight: 600;
    color: var(--ink-1);
    line-height: 1.3;
    font-size: 0.92rem;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.sub-stock-issue .pos-tile__code {
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 0.7rem;
    color: var(--ink-3);
    margin-top: 0.15rem;
}
.sub-stock-issue .pos-tile__qty-badge {
    position: absolute;
    top: 0.45rem;
    right: 0.45rem;
    min-width: 24px;
    height: 24px;
    border-radius: 999px;
    background: var(--primary);
    color: #fff;
    padding: 0 0.45rem;
    font-size: 0.78rem; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center;
    box-shadow: var(--shadow-1);
    font-variant-numeric: tabular-nums;
}
.sub-stock-issue .pos-tile__foot {
    display: flex; align-items: baseline;
    justify-content: space-between;
    gap: 0.5rem;
    margin-top: auto;
    padding-top: 0.55rem;
    border-top: 1px solid var(--line);
}
.sub-stock-issue .pos-tile__lot {
    font-size: 0.7rem;
    color: var(--ink-3);
    display: inline-flex; align-items: center; gap: 0.2rem;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.sub-stock-issue .pos-tile__balance-wrap {
    display: inline-flex; align-items: baseline; gap: 0.15rem;
    flex-shrink: 0;
}
.sub-stock-issue .pos-tile__balance {
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--success);
    font-variant-numeric: tabular-nums;
    line-height: 1.1;
}
.sub-stock-issue .pos-tile.is-low .pos-tile__balance { color: var(--warning); }
.sub-stock-issue .pos-tile.is-out .pos-tile__balance { color: var(--danger); }
.sub-stock-issue .pos-tile__unit {
    font-size: 0.7rem;
    color: var(--ink-3);
    font-weight: 500;
}
.sub-stock-issue .pos-tile mark {
    background: rgba(180, 83, 9, 0.18);
    color: inherit; padding: 0 2px;
    border-radius: 2px;
}

@media (max-width: 575.98px) {
    .sub-stock-issue .pos-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.55rem; }
    .sub-stock-issue .pos-tile { min-height: 118px; padding: 0.6rem 0.65rem; }
}

/* ─── State (empty / loading / no-match) ─── */
.sub-stock-issue .pos-state {
    text-align: center;
    padding: 3rem 1rem;
}
.sub-stock-issue .pos-state__icon {
    width: 64px; height: 64px;
    margin: 0 auto 0.85rem;
    border-radius: 16px;
    background: var(--surface-3);
    color: var(--ink-3);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.7rem;
}
.sub-stock-issue .pos-state__icon--warn {
    background: var(--warning-soft);
    color: var(--warning);
}
.sub-stock-issue .pos-state__title {
    font-weight: 600; color: var(--ink-1);
    font-size: 1.02rem;
    margin-bottom: 0.25rem;
}
.sub-stock-issue .pos-state__caption {
    color: var(--ink-3); font-size: 0.88rem;
}

/* ─── Cart panel ─── */
.sub-stock-issue .pos-cart {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    display: flex; flex-direction: column;
    max-height: calc(100vh - 8rem);
    position: sticky;
    top: 1rem;
}
.sub-stock-issue .pos-cart__head {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.85rem 1rem;
    border-bottom: 1px solid var(--line);
}
.sub-stock-issue .pos-cart__title {
    font-size: 0.98rem; font-weight: 600;
    color: var(--ink-1);
    margin: 0;
    display: inline-flex; align-items: center; gap: 0.5rem;
    flex-grow: 1;
    min-width: 0;
}
.sub-stock-issue .pos-cart__title i { color: var(--primary); font-size: 1.05rem; }
.sub-stock-issue .pos-cart__body {
    flex-grow: 1;
    overflow-y: auto;
    padding: 0.75rem;
    min-height: 0;
}
.sub-stock-issue .pos-cart__foot {
    padding: 0.85rem 1rem 1rem;
    border-top: 1px solid var(--line);
    background: var(--surface-2);
    border-radius: 0 0 var(--radius) var(--radius);
}
.sub-stock-issue .pos-cart__totals {
    margin-bottom: 0.7rem;
    display: flex; flex-direction: column;
    gap: 0.25rem;
}
.sub-stock-issue .pos-cart__total-row {
    display: flex; justify-content: space-between;
    align-items: baseline;
    font-size: 0.85rem;
    color: var(--ink-2);
}
.sub-stock-issue .pos-cart__total-row--strong { color: var(--ink-1); font-weight: 600; font-size: 0.95rem; }
.sub-stock-issue .pos-cart__total-value {
    font-variant-numeric: tabular-nums;
    font-weight: 700;
    color: var(--ink-1);
}
.sub-stock-issue .pos-cart__save { width: 100%; min-height: 48px; }
.sub-stock-issue .pos-cart__cancel { width: 100%; margin-top: 0.5rem; }

/* ─── Cart rows ─── */
.sub-stock-issue .cart-empty {
    text-align: center;
    padding: 2.5rem 0.5rem 1.5rem;
}
.sub-stock-issue .cart-empty__icon {
    color: var(--ink-4);
    font-size: 2.2rem;
    display: block;
    margin-bottom: 0.5rem;
}
.sub-stock-issue .cart-empty__title { font-weight: 600; color: var(--ink-1); margin-bottom: 0.15rem; }
.sub-stock-issue .cart-empty__caption { font-size: 0.82rem; color: var(--ink-3); }

.sub-stock-issue .cart-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex; flex-direction: column;
    gap: 0.5rem;
}
.sub-stock-issue .cart-row {
    padding: 0.65rem 0.7rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    transition: border-color var(--t-fast), background-color var(--t-fast), opacity var(--t-fast), transform var(--t-fast);
}
.sub-stock-issue .cart-row:hover { border-color: var(--line-strong); }
.sub-stock-issue .cart-row.is-new {
    animation: cart-row-pop 280ms var(--ease);
    border-color: var(--primary-line);
    background: var(--primary-soft);
}
@keyframes cart-row-pop {
    0% { opacity: 0; transform: scale(0.95); }
    60% { opacity: 1; transform: scale(1.02); }
    100% { transform: scale(1); }
}
.sub-stock-issue .cart-row--removing { opacity: 0; transform: translateX(20px); pointer-events: none; }
.sub-stock-issue .cart-row__head {
    display: flex; gap: 0.5rem;
    align-items: flex-start;
    margin-bottom: 0.45rem;
}
.sub-stock-issue .cart-row__body { flex-grow: 1; min-width: 0; }
.sub-stock-issue .cart-row__name {
    font-weight: 600; color: var(--ink-1);
    line-height: 1.3;
    font-size: 0.9rem;
    word-break: break-word;
}
.sub-stock-issue .cart-row__meta {
    font-size: 0.72rem;
    color: var(--ink-3);
    display: flex; gap: 0.5rem;
    flex-wrap: wrap;
    margin-top: 0.1rem;
}
.sub-stock-issue .cart-row__meta-code {
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
}
.sub-stock-issue .cart-row__remove {
    background: transparent; border: 0;
    color: var(--ink-4);
    border-radius: 6px;
    width: 28px; height: 28px;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
    transition: color var(--t-fast), background-color var(--t-fast);
}
.sub-stock-issue .cart-row__remove:hover { background: var(--danger-soft); color: var(--danger); }
.sub-stock-issue .cart-row__qty {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}
.sub-stock-issue .cart-row__stepper {
    display: flex;
    align-items: center;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    background: var(--surface);
    overflow: hidden;
    transition: border-color var(--t-fast), box-shadow var(--t-fast);
}
.sub-stock-issue .cart-row__stepper:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.sub-stock-issue .cart-row__btn {
    border: 0; background: transparent;
    color: var(--ink-2);
    min-width: 32px; min-height: 32px;
    font-size: 0.95rem;
    cursor: pointer;
    transition: background-color var(--t-fast), color var(--t-fast);
}
.sub-stock-issue .cart-row__btn:hover:not(:disabled) { background: var(--surface-hover); color: var(--ink-1); }
.sub-stock-issue .cart-row__btn:disabled { cursor: not-allowed; opacity: 0.4; }
.sub-stock-issue .cart-row__input {
    border: 0;
    background: transparent;
    width: 3rem;
    text-align: center;
    font-weight: 700;
    color: var(--ink-1);
    font-size: 0.95rem;
    font-variant-numeric: tabular-nums;
    outline: none;
    min-height: 32px;
}
.sub-stock-issue .cart-row__input::-webkit-outer-spin-button,
.sub-stock-issue .cart-row__input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.sub-stock-issue .cart-row__balance {
    font-size: 0.74rem;
    color: var(--ink-3);
    font-variant-numeric: tabular-nums;
}
.sub-stock-issue .cart-row__balance.is-warn { color: var(--warning); font-weight: 500; }

/* ─── Icon button ─── */
.sub-stock-issue .icon-btn {
    background: transparent; border: 0;
    color: var(--ink-4);
    border-radius: 8px;
    width: 32px; height: 32px;
    display: inline-flex; align-items: center; justify-content: center;
    transition: color var(--t-fast), background-color var(--t-fast);
    flex-shrink: 0;
    cursor: pointer;
}
.sub-stock-issue .icon-btn:hover { background: var(--surface-hover); color: var(--ink-1); }

/* ─── Count pill ─── */
.sub-stock-issue .count-pill {
    background: var(--surface-3);
    color: var(--ink-2);
    font-size: 0.78rem; font-weight: 600;
    padding: 0.2rem 0.6rem;
    border-radius: 999px;
    transition: background-color var(--t-fast), color var(--t-fast);
    line-height: 1.3;
    font-variant-numeric: tabular-nums;
}
.sub-stock-issue .count-pill.is-active {
    background: var(--primary);
    color: #fff;
}

/* ─── Buttons ─── */
.sub-stock-issue .btn { border-radius: var(--radius-sm); font-weight: 600; transition: background-color var(--t-fast), border-color var(--t-fast), color var(--t-fast), box-shadow var(--t-fast), transform 80ms; }
.sub-stock-issue .btn-primary { background: var(--primary); border-color: var(--primary); }
.sub-stock-issue .btn-primary:hover:not(:disabled) { background: var(--primary-ink); border-color: var(--primary-ink); }
.sub-stock-issue .btn-primary:active:not(:disabled) { transform: translateY(1px); }
.sub-stock-issue .btn-primary:focus-visible { box-shadow: 0 0 0 3px var(--primary-soft); }
.sub-stock-issue .btn-primary:disabled { opacity: 0.55; cursor: not-allowed; }
.sub-stock-issue .btn-light {
    background: var(--surface-2);
    border-color: var(--line-strong);
    color: var(--ink-2);
}
.sub-stock-issue .btn-light:hover { background: var(--surface-hover); color: var(--ink-1); }

/* ─── Save button progress ─── */
.sub-stock-issue .btn-save { position: relative; overflow: hidden; min-height: 48px; }
.sub-stock-issue .btn-save__label { position: relative; z-index: 2; display: inline-flex; align-items: center; gap: 0.35rem; }
.sub-stock-issue .btn-save__progress {
    position: absolute; left: 0; top: 0; bottom: 0;
    width: 0%;
    background: rgba(255, 255, 255, 0.2);
    transition: width 200ms linear;
    z-index: 1;
}
.sub-stock-issue .btn-save.is-saving .btn-save__progress { animation: save-progress 1.2s ease-out forwards; }
.sub-stock-issue .btn-save.is-success { background: var(--success); border-color: var(--success); }
@keyframes save-progress {
    from { width: 0%; }
    to { width: 92%; }
}

/* ─── Mobile cart FAB ─── */
.sub-stock-issue .pos-cart-fab {
    position: fixed;
    left: 50%;
    bottom: calc(0.85rem + env(safe-area-inset-bottom));
    transform: translateX(-50%) translateY(0);
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    padding: 0.55rem 1rem 0.55rem 0.55rem;
    background: var(--primary);
    color: #fff;
    border: 0;
    border-radius: 999px;
    box-shadow: var(--shadow-3);
    z-index: 1040;
    max-width: calc(100vw - 1.5rem);
    cursor: pointer;
    opacity: 0;
    transition: background-color var(--t-fast), transform var(--t-mid), opacity var(--t-mid);
    font: inherit;
}
.sub-stock-issue .pos-cart-fab[hidden] { display: none !important; }
.sub-stock-issue .pos-cart-fab.is-show { opacity: 1; }
.sub-stock-issue .pos-cart-fab:hover { background: var(--primary-ink); }
.sub-stock-issue .pos-cart-fab:active { transform: translateX(-50%) translateY(1px); }
.sub-stock-issue .pos-cart-fab__icon {
    position: relative;
    width: 36px; height: 36px;
    background: rgba(255, 255, 255, 0.18);
    border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1rem;
    flex-shrink: 0;
}
.sub-stock-issue .pos-cart-fab__badge {
    position: absolute;
    top: -2px; right: -4px;
    min-width: 18px; height: 18px;
    background: #fff;
    color: var(--primary);
    font-size: 0.7rem; font-weight: 700;
    border-radius: 999px;
    padding: 0 0.3rem;
    display: inline-flex; align-items: center; justify-content: center;
    font-variant-numeric: tabular-nums;
    border: 2px solid var(--primary);
}
.sub-stock-issue .pos-cart-fab__meta {
    display: flex; flex-direction: column;
    line-height: 1.15;
    text-align: left;
}
.sub-stock-issue .pos-cart-fab__count { font-size: 0.85rem; font-weight: 600; }
.sub-stock-issue .pos-cart-fab__qty { font-size: 0.72rem; opacity: 0.85; font-variant-numeric: tabular-nums; }
.sub-stock-issue .pos-cart-fab__cta {
    margin-left: 0.5rem;
    font-size: 0.85rem; font-weight: 600;
    display: inline-flex; align-items: center; gap: 0.25rem;
    border-left: 1px solid rgba(255, 255, 255, 0.28);
    padding-left: 0.75rem;
}

/* ─── Mobile cart drawer ─── */
@media (max-width: 991.98px) {
    .sub-stock-issue .pos-cart {
        position: fixed;
        left: 0; right: 0;
        bottom: 0;
        top: auto;
        max-height: 88vh;
        margin: 0;
        border-radius: var(--radius) var(--radius) 0 0;
        box-shadow: var(--shadow-3);
        transform: translateY(100%);
        visibility: hidden;
        transition: transform 240ms var(--ease), visibility 0s linear 240ms;
        z-index: 1050;
    }
    .sub-stock-issue .pos-cart.is-open {
        transform: translateY(0);
        visibility: visible;
        transition: transform 240ms var(--ease), visibility 0s linear 0s;
    }
}
.sub-stock-issue .pos-cart__backdrop {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.32);
    z-index: 1045;
    opacity: 0;
    transition: opacity var(--t-mid);
    pointer-events: none;
}
.sub-stock-issue .pos-cart__backdrop.is-show {
    opacity: 1;
    pointer-events: auto;
}
.sub-stock-issue .pos-cart__close { color: var(--ink-3); }

@media (min-width: 992px) {
    .sub-stock-issue .pos-cart__backdrop { display: none !important; }
}

/* ─── Undo toast ─── */
.undo-toast {
    position: fixed;
    left: 50%;
    bottom: calc(1rem + env(safe-area-inset-bottom));
    transform: translateX(-50%) translateY(8px);
    background: #1a202c;
    color: #fff;
    border-radius: 999px;
    padding: 0.55rem 0.65rem 0.55rem 1rem;
    display: flex; align-items: center; gap: 0.65rem;
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.24);
    z-index: 1060;
    opacity: 0;
    transition: opacity 180ms cubic-bezier(0.16, 1, 0.3, 1), transform 180ms cubic-bezier(0.16, 1, 0.3, 1);
    pointer-events: none;
    font-size: 0.88rem;
    max-width: calc(100vw - 1.5rem);
}
.undo-toast.is-show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
    pointer-events: auto;
}
.undo-toast__icon { color: #a0aec0; }
.undo-toast__text { font-weight: 500; }
.undo-toast__btn {
    background: rgba(255,255,255,0.12);
    border: 0;
    color: #fff;
    padding: 0.3rem 0.85rem;
    border-radius: 999px;
    font-weight: 600; font-size: 0.85rem;
    cursor: pointer;
    transition: background-color 120ms ease;
}
.undo-toast__btn:hover { background: rgba(255,255,255,0.2); }
@media (min-width: 992px) {
    .undo-toast { bottom: 1.25rem; left: auto; right: 1.5rem; transform: translateY(8px); }
    .undo-toast.is-show { transform: translateY(0); }
}

/* ─── FLIP fly clone ─── */
.sub-stock-issue .flip-fly {
    position: fixed;
    z-index: 1080;
    pointer-events: none;
    will-change: transform, opacity;
    background: var(--primary-soft);
    border: 1px solid var(--primary-line);
    border-radius: var(--radius-sm);
    padding: 0.5rem 0.65rem;
    display: flex; align-items: center; gap: 0.55rem;
    color: var(--primary-ink);
    box-shadow: var(--shadow-2);
    font-weight: 600; font-size: 0.84rem;
    max-width: 280px;
}

/* ─── Spin ─── */
.sub-stock-issue .spin { animation: spin 0.8s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ─── Body padding for mobile FAB ─── */
@media (max-width: 991.98px) {
    body.has-cart-fab { padding-bottom: 92px; }
}

/* ─── Reduced motion ─── */
@media (prefers-reduced-motion: reduce) {
    .sub-stock-issue *,
    .sub-stock-issue *::before,
    .sub-stock-issue *::after,
    .undo-toast { animation: none !important; transition: opacity 80ms linear !important; }
    .sub-stock-issue .btn-primary:active:not(:disabled),
    .sub-stock-issue .pos-tile:active { transform: none; }
    .undo-toast { transform: translateX(-50%) translateY(0); }
    .sub-stock-issue .pos-cart { transition: none; }
}
</style>

<?php if (!empty($subWarehouses)): ?>
    <?php
    $getLotsUrlJson = json_encode($getLotsUrl);
    $saveUrlJson = json_encode($saveUrl);
    $recentJson = json_encode(array_values($recentItemCodes));
    $this->registerJs(
        <<<JS
(function(){
    var getLotsUrl = {$getLotsUrlJson};
    var saveUrl = {$saveUrlJson};
    var recentItemCodes = {$recentJson};
    var lotsData = [];
    var rows = [];
    var lastWarehouseValue = $('#warehouseSelect').val() || '';
    var isInitialWarehouseChange = true;
    var currentQuery = '';
    var lastRemoved = null;
    var undoTimer = null;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isDesktop = window.matchMedia('(min-width: 992px)').matches;

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c];
        });
    }
    function highlight(text, query) {
        if (!query) return esc(text);
        var safe = String(text || '');
        var idx = safe.toLowerCase().indexOf(query.toLowerCase());
        if (idx < 0) return esc(safe);
        return esc(safe.substring(0, idx)) + '<mark>' + esc(safe.substring(idx, idx + query.length)) + '</mark>' + esc(safe.substring(idx + query.length));
    }
    function notify(type, message, title) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type,
                title: title || (type === 'success' ? 'สำเร็จ' : type === 'error' ? 'ไม่สำเร็จ' : ''),
                text: message,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'ตกลง'
            });
        } else { alert(message); }
    }
    function findCartIndex(item) {
        if (!item) return -1;
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].item_code === item.item_code &&
                String(rows[i].lot_number || '') === String(item.lot_number || '')) {
                return i;
            }
        }
        return -1;
    }
    function lookupLot(item_code, lot_number) {
        for (var i = 0; i < lotsData.length; i++) {
            if (lotsData[i].item_code === item_code &&
                String(lotsData[i].lot_number || '') === String(lot_number || '')) return lotsData[i];
        }
        return null;
    }
    function initial(s) {
        var t = String(s || '').trim();
        return t ? t.charAt(0) : '?';
    }

    /* =========================
       View state toggle
       ========================= */
    function showState(stateId) {
        ['stateNoWarehouse', 'stateLoading', 'stateNoItems', 'stateNoMatch'].forEach(function(s) {
            var el = document.getElementById(s);
            if (el) el.classList.toggle('d-none', s !== stateId);
        });
        var grid = document.getElementById('itemGrid');
        if (grid) grid.classList.toggle('d-none', stateId !== '_grid');
    }

    /* =========================
       Item grid (POS tiles)
       ========================= */
    function renderItemGrid() {
        var q = (currentQuery || '').trim().toLowerCase();
        var grid = $('#itemGrid').empty();
        var filtered = lotsData.filter(function(o) {
            if (!q) return true;
            return (String(o.item_name || '').toLowerCase().indexOf(q) >= 0
                 || String(o.item_code || '').toLowerCase().indexOf(q) >= 0
                 || String(o.lot_number || '').toLowerCase().indexOf(q) >= 0);
        });
        $('#itemsCount').text(filtered.length).prop('hidden', filtered.length === 0);
        if (filtered.length === 0) {
            if (q) { $('#noMatchQuery').text(q); showState('stateNoMatch'); }
            else { showState('stateNoItems'); }
            return;
        }
        showState('_grid');
        filtered.forEach(function(o) {
            var cartIdx = findCartIndex(o);
            var balance = parseFloat(o.balance_qty || 0);
            var inCart = cartIdx >= 0 ? parseFloat(rows[cartIdx].qty) || 0 : 0;
            var remaining = balance - inCart;
            var isOut = remaining <= 0;
            var isLow = !isOut && remaining > 0 && remaining < Math.max(5, balance * 0.1);

            var html = '<li role="listitem">' +
                '<button type="button" class="pos-tile' +
                    (isOut ? ' is-out' : '') +
                    (isLow ? ' is-low' : '') +
                    (cartIdx >= 0 ? ' is-in-cart' : '') +
                '" data-code="' + esc(o.item_code) + '" data-lot="' + esc(o.lot_number || '') +
                '" aria-label="' + esc(o.item_name || o.item_code) + ' คงเหลือ ' + esc(remaining) + (o.unit ? ' ' + esc(o.unit) : '') + '">' +
                  (cartIdx >= 0 ? '<span class="pos-tile__qty-badge" aria-label="ในรายการจ่าย ' + esc(inCart) + '">' + esc(inCart) + '</span>' : '') +
                  '<div class="pos-tile__top">' +
                    '<div class="pos-tile__avatar" aria-hidden="true">' + esc(initial(o.item_name)) + '</div>' +
                    '<div class="pos-tile__body">' +
                      '<div class="pos-tile__name">' + highlight(o.item_name || o.item_code, q) + '</div>' +
                      (o.item_code ? '<div class="pos-tile__code">' + esc(o.item_code) + '</div>' : '') +
                    '</div>' +
                  '</div>' +
                  '<div class="pos-tile__foot">' +
                    '<span class="pos-tile__lot"><i class="bi bi-bookmark" aria-hidden="true"></i> Lot ' + esc(o.lot_number || '-') + '</span>' +
                    '<span class="pos-tile__balance-wrap">' +
                      '<span class="pos-tile__balance">' + esc(Math.max(0, remaining)) + '</span>' +
                      (o.unit ? '<span class="pos-tile__unit">' + esc(o.unit) + '</span>' : '') +
                    '</span>' +
                  '</div>' +
                '</button>' +
              '</li>';
            var tile = $(html);
            tile.find('.pos-tile').on('click', function() { addToCart(o, this); });
            grid.append(tile);
        });
    }

    /* =========================
       Search input → filter grid in place
       ========================= */
    $('#itemSearchInput').on('input', function() {
        currentQuery = this.value;
        $('#itemSearchClear').prop('hidden', this.value.length === 0);
        renderItemGrid();
    }).on('keydown', function(e) {
        if (e.key === 'Escape') {
            if (this.value) { this.value = ''; currentQuery = ''; $('#itemSearchInput').trigger('input'); }
            else this.blur();
        }
    });
    $('#itemSearchClear').on('click', function() {
        $('#itemSearchInput').val('').focus();
        currentQuery = '';
        $('#itemSearchClear').prop('hidden', true);
        renderItemGrid();
    });

    /* =========================
       Recent chips
       ========================= */
    function renderRecentChips() {
        var wrap = $('#recentChips');
        var list = $('#recentChipsList').empty();
        var matched = [];
        recentItemCodes.forEach(function(code) {
            for (var i = 0; i < lotsData.length; i++) {
                if (lotsData[i].item_code === code) { matched.push(lotsData[i]); return; }
            }
        });
        if (matched.length === 0) { wrap.prop('hidden', true); return; }
        matched.forEach(function(o) {
            var chip = $('<button type="button" class="recent-chip"></button>');
            chip.append('<i class="bi bi-arrow-up-right-circle" aria-hidden="true"></i>');
            chip.append($('<span class="recent-chip__name"></span>').text(o.item_name || o.item_code));
            chip.on('click', function() { addToCart(o, this); });
            list.append(chip);
        });
        wrap.prop('hidden', false);
    }

    /* =========================
       Add / increment cart
       ========================= */
    function addToCart(item, sourceEl) {
        var balance = parseFloat(item.balance_qty || 0);
        var unit = item.unit || '';
        var unitText = unit ? ' ' + unit : '';
        var existingIdx = findCartIndex(item);
        var existingQty = existingIdx >= 0 ? (parseFloat(rows[existingIdx].qty) || 0) : 0;

        if (existingQty + 1 > balance) {
            notify('warning', 'เกินยอดคงเหลือใน Lot นี้ (คงเหลือ ' + balance + unitText + ')');
            return;
        }

        var targetIndex;
        if (existingIdx >= 0) {
            rows[existingIdx].qty = +(existingQty + 1).toFixed(2);
            targetIndex = existingIdx;
        } else {
            rows.push({
                item_code: item.item_code,
                item_name: item.item_name,
                lot_number: item.lot_number,
                unit: unit,
                qty: 1
            });
            targetIndex = rows.length - 1;
        }

        if (sourceEl && !reducedMotion) flyToCart(item, sourceEl);
        var delay = (sourceEl && !reducedMotion) ? 280 : 0;
        setTimeout(function() {
            renderCart({ markIndex: targetIndex });
            renderItemGrid();
        }, delay);
    }

    function setRowQty(idx, newQty) {
        var row = rows[idx];
        if (!row) return;
        var lot = lookupLot(row.item_code, row.lot_number);
        var balance = lot ? parseFloat(lot.balance_qty || 0) : Infinity;
        var qty = parseFloat(newQty);
        if (isNaN(qty) || qty < 0) qty = 0;
        if (qty > balance) {
            notify('warning', 'เกินยอดคงเหลือ (มี ' + balance + (row.unit ? ' ' + row.unit : '') + ')');
            qty = balance;
        }
        if (qty === 0) { removeRow(idx); return; }
        row.qty = +qty.toFixed(2);
        renderCart();
        renderItemGrid();
    }

    function removeRow(idx) {
        var row = rows[idx];
        if (!row) return;
        lastRemoved = { index: idx, item: row };
        rows.splice(idx, 1);
        renderCart();
        renderItemGrid();
        showUndoToast('ลบ "' + (row.item_name || row.item_code) + '" แล้ว');
    }

    /* =========================
       FLIP fly to cart
       ========================= */
    function flyToCart(item, sourceEl) {
        if (reducedMotion) return;
        var target = isDesktop
            ? document.getElementById('cartPanel')
            : document.getElementById('btnOpenCart');
        var source = sourceEl;
        if (!target || !source) return;
        var srcRect = source.getBoundingClientRect();
        var dstRect = target.getBoundingClientRect();
        var clone = document.createElement('div');
        clone.className = 'flip-fly';
        clone.innerHTML = '<i class="bi bi-box-seam" aria-hidden="true"></i><span>' + esc(item.item_name || item.item_code || 'พัสดุ') + '</span>';
        document.body.appendChild(clone);
        var cloneRect = clone.getBoundingClientRect();
        var startX = srcRect.left + (srcRect.width - cloneRect.width) / 2;
        var startY = srcRect.top + srcRect.height / 2 - cloneRect.height / 2;
        clone.style.left = startX + 'px';
        clone.style.top = startY + 'px';
        var endX = dstRect.left + dstRect.width / 2 - cloneRect.width / 2;
        var endY = dstRect.top + dstRect.height / 2 - cloneRect.height / 2;
        var tx = endX - startX;
        var ty = endY - startY;
        var anim = clone.animate([
            { transform: 'translate(0,0) scale(1)', opacity: 1, offset: 0 },
            { transform: 'translate(' + (tx * 0.4) + 'px,' + (ty * 0.3 - 30) + 'px) scale(0.95)', opacity: 1, offset: 0.5 },
            { transform: 'translate(' + tx + 'px,' + ty + 'px) scale(0.55)', opacity: 0, offset: 1 }
        ], { duration: 420, easing: 'cubic-bezier(0.5, 0, 0.75, 0)' });
        anim.onfinish = function() { clone.remove(); };
    }

    /* =========================
       Undo toast
       ========================= */
    function showUndoToast(text) {
        var toast = document.getElementById('undoToast');
        if (!toast) return;
        var label = document.getElementById('undoToastText');
        if (label) label.textContent = text || 'ลบรายการแล้ว';
        toast.hidden = false;
        requestAnimationFrame(function() { toast.classList.add('is-show'); });
        if (undoTimer) clearTimeout(undoTimer);
        undoTimer = setTimeout(hideUndoToast, 5000);
    }
    function hideUndoToast() {
        var toast = document.getElementById('undoToast');
        if (!toast) return;
        toast.classList.remove('is-show');
        setTimeout(function() { toast.hidden = true; }, 200);
        if (undoTimer) { clearTimeout(undoTimer); undoTimer = null; }
        lastRemoved = null;
    }
    $('#btnUndo').on('click', function() {
        if (lastRemoved) {
            var i = Math.min(lastRemoved.index, rows.length);
            rows.splice(i, 0, lastRemoved.item);
            renderCart({ markIndex: i });
            renderItemGrid();
        }
        hideUndoToast();
    });

    /* =========================
       Cart render + totals + FAB
       ========================= */
    function renderCart(opts) {
        opts = opts || {};
        var list = $('#cartList').empty();
        var count = rows.length;
        var totalQty = rows.reduce(function(s, r) { return s + (parseFloat(r.qty) || 0); }, 0);
        var roundedQty = Math.round(totalQty * 100) / 100;

        $('#cartCount').text(count + ' รายการ').toggleClass('is-active', count > 0);
        $('#totalCount').text(count);
        $('#totalQty').text(roundedQty);
        $('#summaryTotals').prop('hidden', count === 0);
        $('#btnSaveFinal').prop('disabled', count === 0);

        $('#fabCount').text(count + ' รายการ');
        $('#fabQty').text('รวม ' + roundedQty);
        $('#fabBadge').text(count);

        var fab = document.getElementById('btnOpenCart');
        if (fab) {
            if (count > 0) {
                fab.hidden = false;
                requestAnimationFrame(function() { fab.classList.add('is-show'); });
                document.body.classList.add('has-cart-fab');
            } else {
                fab.classList.remove('is-show');
                setTimeout(function() { fab.hidden = true; }, 200);
                document.body.classList.remove('has-cart-fab');
            }
        }

        if (count === 0) {
            $('#cartEmpty').show();
            list.addClass('d-none');
            return;
        }
        $('#cartEmpty').hide();
        list.removeClass('d-none');

        rows.forEach(function(r, i) {
            var lot = lookupLot(r.item_code, r.lot_number);
            var balance = lot ? parseFloat(lot.balance_qty || 0) : null;
            var atMax = balance !== null && parseFloat(r.qty) >= balance;
            var unitText = r.unit ? ' ' + r.unit : '';
            var balanceText = balance !== null ? 'คงเหลือ ' + balance + unitText : '';

            var li = $('<li class="cart-row" role="listitem"></li>');
            if (i === opts.markIndex) li.addClass('is-new');

            var head = $('<div class="cart-row__head"></div>');
            var body = $('<div class="cart-row__body"></div>');
            body.append($('<div class="cart-row__name"></div>').text(r.item_name || r.item_code));
            var meta = $('<div class="cart-row__meta"></div>');
            meta.append($('<span></span>').html('<i class="bi bi-bookmark" aria-hidden="true"></i> Lot ' + esc(r.lot_number || '-')));
            if (r.item_code) meta.append($('<span class="cart-row__meta-code"></span>').text(r.item_code));
            body.append(meta);
            head.append(body);

            var removeBtn = $('<button type="button" class="cart-row__remove" aria-label="ลบ"><i class="bi bi-x-lg" aria-hidden="true"></i></button>');
            removeBtn.on('click', function() {
                li.addClass('cart-row--removing');
                setTimeout(function() { removeRow(i); }, 160);
            });
            head.append(removeBtn);
            li.append(head);

            var qtyRow = $('<div class="cart-row__qty"></div>');
            var stepper = $('<div class="cart-row__stepper"></div>');
            var minus = $('<button type="button" class="cart-row__btn" aria-label="ลดจำนวน"><i class="bi bi-dash-lg" aria-hidden="true"></i></button>');
            var input = $('<input type="number" class="cart-row__input" min="0" step="1" inputmode="decimal" aria-label="จำนวน">').val(r.qty);
            var plus = $('<button type="button" class="cart-row__btn" aria-label="เพิ่มจำนวน"><i class="bi bi-plus-lg" aria-hidden="true"></i></button>');
            if (atMax) plus.prop('disabled', true);
            minus.on('click', function() {
                var v = parseFloat(input.val()) || 0;
                setRowQty(i, +(v - 1).toFixed(2));
            });
            plus.on('click', function() {
                var v = parseFloat(input.val()) || 0;
                setRowQty(i, +(v + 1).toFixed(2));
            });
            input.on('change', function() { setRowQty(i, parseFloat(this.value) || 0); })
                 .on('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); this.blur(); } });
            stepper.append(minus, input, plus);

            var balanceLabel = $('<span class="cart-row__balance"></span>').text(balanceText);
            if (atMax) balanceLabel.addClass('is-warn');

            qtyRow.append(stepper, balanceLabel);
            li.append(qtyRow);
            list.append(li);
        });
    }

    /* =========================
       Mobile cart drawer
       ========================= */
    function openCart() {
        if (isDesktop) return;
        var panel = document.getElementById('cartPanel');
        var backdrop = document.getElementById('cartBackdrop');
        if (!panel) return;
        panel.classList.add('is-open');
        if (backdrop) {
            backdrop.hidden = false;
            requestAnimationFrame(function() { backdrop.classList.add('is-show'); });
        }
        document.body.style.overflow = 'hidden';
    }
    function closeCart() {
        var panel = document.getElementById('cartPanel');
        var backdrop = document.getElementById('cartBackdrop');
        if (panel) panel.classList.remove('is-open');
        if (backdrop) {
            backdrop.classList.remove('is-show');
            setTimeout(function() { backdrop.hidden = true; }, 180);
        }
        document.body.style.overflow = '';
    }
    $('#btnOpenCart').on('click', openCart);
    $('#btnCloseCart').on('click', closeCart);
    $('#cartBackdrop').on('click', closeCart);

    /* =========================
       Warehouse change
       ========================= */
    function loadLots(wh) {
        currentQuery = '';
        $('#itemSearchInput').val('');
        $('#itemSearchClear').prop('hidden', true);
        if (!wh) { showState('stateNoWarehouse'); return; }
        showState('stateLoading');
        \$.get(getLotsUrl, { warehouse_id: wh }).done(function(data) {
            lotsData = data || [];
            renderRecentChips();
            if (lotsData.length === 0) { showState('stateNoItems'); return; }
            renderItemGrid();
            if (isDesktop) $('#itemSearchInput').focus();
        }).fail(function() {
            showState('stateNoWarehouse');
            notify('error', 'โหลดรายการพัสดุไม่สำเร็จ ลองอีกครั้ง');
        });
    }

    $('#warehouseSelect').on('change', function() {
        var wh = $(this).val();
        var sel = $(this);
        var doSwitch = function() {
            lastWarehouseValue = wh;
            isInitialWarehouseChange = false;
            if (rows.length === 0 && wh) {
                window.location.href = window.location.pathname + '?warehouse_id=' + wh;
                return;
            }
            loadLots(wh);
        };
        if (isInitialWarehouseChange) { isInitialWarehouseChange = false; lastWarehouseValue = wh; loadLots(wh); return; }
        if (rows.length > 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'มีรายการที่ยังไม่ได้บันทึก',
                    text: 'เปลี่ยนคลังจะล้างรายการที่เพิ่มไว้ ดำเนินการต่อ?',
                    showCancelButton: true,
                    confirmButtonText: 'ล้างและเปลี่ยนคลัง',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#dc3545'
                }).then(function(result) {
                    if (result.isConfirmed) { rows = []; renderCart(); doSwitch(); }
                    else { sel.val(lastWarehouseValue); }
                });
                return;
            } else if (!confirm('มีรายการที่ยังไม่ได้บันทึก เปลี่ยนคลังจะล้างรายการ ดำเนินการต่อ?')) {
                sel.val(lastWarehouseValue);
                return;
            }
            rows = []; renderCart();
        }
        doSwitch();
    });

    /* =========================
       Segmented job type
       ========================= */
    $('.seg-control__item').on('click', function() {
        var v = $(this).data('value');
        $('.seg-control__item').removeClass('is-active').attr('aria-checked', 'false');
        $(this).addClass('is-active').attr('aria-checked', 'true');
        $('#jobType').val(v).trigger('change');
    }).on('keydown', function(e) {
        var items = $('.seg-control__item').toArray();
        var idx = items.indexOf(this);
        if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
            e.preventDefault();
            $(items[(idx + 1) % items.length]).trigger('click').focus();
        } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
            e.preventDefault();
            $(items[(idx - 1 + items.length) % items.length]).trigger('click').focus();
        }
    }).attr('tabindex', '0');

    /* =========================
       Job type / reference label
       ========================= */
    var selectedRepairNumber = '';
    var refConfig = {
        patient:     { label: 'HN / ชื่อคนไข้',          placeholder: 'HN12345 หรือ ชื่อคนไข้', icon: 'bi-person-vcard' },
        maintenance: { label: 'เลขที่ส่งซ่อม',             placeholder: 'เลือกจากรายการแจ้งซ่อม หรือระบุเลขที่ส่งซ่อม', icon: 'bi-tools' },
        office:      { label: 'รหัสผู้เบิก / โครงการ',     placeholder: 'หน่วยงาน หรือ โครงการ', icon: 'bi-briefcase' },
        emergency:   { label: 'อ้างอิงการเบิก',           placeholder: 'เหตุการณ์ หรือ ห้อง', icon: 'bi-exclamation-octagon' }
    };
    $('#jobType').on('change', function() {
        var jobType = $(this).val();
        var isMaintenance = jobType === 'maintenance';
        var cfg = refConfig[jobType] || { label: 'อ้างอิง', placeholder: 'ระบุอ้างอิง (ถ้ามี)', icon: 'bi-tag' };
        $('#dynamicLabel').text(cfg.label);
        $('#referenceInput').attr('placeholder', cfg.placeholder);
        $('#referenceIcon').attr('class', 'bi ' + cfg.icon + ' ctx-bar__icon');
        $('#referenceControl').toggleClass('has-addon', isMaintenance);
        $('#btnPickRepair').prop('hidden', !isMaintenance);
        if (!isMaintenance) {
            selectedRepairNumber = '';
            $('#repairHelpdeskId').val('');
        }
    });
    $('#referenceInput').on('input', function() {
        if ($('#jobType').val() === 'maintenance' && this.value !== selectedRepairNumber) {
            $('#repairHelpdeskId').val('');
        }
    });
    $(document).on('click', '.js-select-repair', function(e) {
        e.preventDefault();
        var repairNumber = String($(this).data('repair-number') || '');
        var helpdeskId = String($(this).data('helpdesk-id') || '');
        selectedRepairNumber = repairNumber;
        $('#referenceInput').val(repairNumber).trigger('input').focus();
        $('#repairHelpdeskId').val(helpdeskId);
        notify('success', 'เลือกเลขที่ส่งซ่อม ' + repairNumber);

        var modalEl = $(this).closest('.modal')[0];
        if (modalEl && window.bootstrap && window.bootstrap.Modal) {
            var modal = window.bootstrap.Modal.getInstance(modalEl) || new window.bootstrap.Modal(modalEl);
            modal.hide();
        } else if ($.fn.modal) {
            $(this).closest('.modal').modal('hide');
        }
    });
    $(document).on('input', '[data-repair-filter]', function() {
        var term = String(this.value || '').toLowerCase();
        var root = $(this).closest('[data-repair-picker]');
        var visible = 0;
        root.find('.repair-picker__item').each(function() {
            var text = String($(this).data('repair-search') || '').toLowerCase();
            var matched = term === '' || text.indexOf(term) !== -1;
            $(this).toggle(matched);
            if (matched) visible += 1;
        });
        root.find('[data-repair-count]').text(visible);
    });

    /* =========================
       Keyboard shortcuts
       ========================= */
    document.addEventListener('keydown', function(e) {
        var tag = e.target.tagName;
        var inField = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
        if (e.key === '/' && !inField) {
            var searchInput = document.getElementById('itemSearchInput');
            if (searchInput) { e.preventDefault(); searchInput.focus(); }
        }
        if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
            if (rows.length > 0 && !$('#btnSaveFinal').is(':disabled')) {
                e.preventDefault();
                doSave();
            }
        }
    });

    /* =========================
       Save
       ========================= */
    function doSave() {
        if (rows.length === 0) { notify('warning', 'เพิ่มรายการพัสดุก่อน'); return; }
        var wh = $('#warehouseSelect').val();
        if (!wh) { notify('warning', 'เลือกคลังย่อยก่อน'); return; }
        var jobType = $('#jobType').val();
        var reference = ($('#referenceInput').val() || '').trim() || 'ไม่ได้ระบุ';
        var items = rows.map(function(r) { return { item_code: r.item_code, lot_number: r.lot_number, qty: r.qty }; });

        var btn = $('#btnSaveFinal').prop('disabled', true).addClass('is-saving');
        btn.find('.btn-save__label i').removeClass('bi-check2').addClass('bi-arrow-clockwise spin');

        var payload = { warehouse_id: wh, job_type: jobType, reference: reference, items: items };
        if (jobType === 'maintenance') {
            payload.helpdesk_id = $('#repairHelpdeskId').val();
        }

        \$.post(saveUrl, payload)
            .done(function(res) {
                if (res.success) {
                    btn.removeClass('is-saving').addClass('is-success')
                        .find('.btn-save__label i').removeClass('bi-arrow-clockwise spin').addClass('bi-check-lg');
                    setTimeout(function() {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'บันทึกเรียบร้อย',
                                text: (res.order_no ? 'เลขที่ ' + res.order_no : '') + (res.message ? ' · ' + res.message : ''),
                                confirmButtonColor: '#0d6efd',
                                confirmButtonText: 'ตกลง'
                            }).then(function() {
                                location.href = window.location.pathname + '?warehouse_id=' + wh;
                            });
                        } else {
                            alert(res.message || 'บันทึกเรียบร้อย');
                            location.reload();
                        }
                    }, 300);
                } else {
                    notify('error', res.message || 'เกิดข้อผิดพลาด');
                    btn.prop('disabled', false).removeClass('is-saving is-success')
                        .find('.btn-save__label i').removeClass('bi-arrow-clockwise spin').addClass('bi-check2');
                }
            })
            .fail(function() {
                notify('error', 'เชื่อมต่อ server ไม่สำเร็จ');
                btn.prop('disabled', false).removeClass('is-saving is-success')
                    .find('.btn-save__label i').removeClass('bi-arrow-clockwise spin').addClass('bi-check2');
            });
    }
    $('#btnSaveFinal').on('click', doSave);

    /* =========================
       Initial load
       ========================= */
    renderCart();
    var initialWh = $('#warehouseSelect').val();
    if (initialWh) {
        lastWarehouseValue = initialWh;
        loadLots(initialWh);
        isInitialWarehouseChange = false;
    } else {
        showState('stateNoWarehouse');
    }
})();
JS,
        View::POS_READY
    );
    ?>
<?php endif; ?>

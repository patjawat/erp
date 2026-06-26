<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;

$this->title = 'บันทึกการจ่ายพัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = ['label' => 'Dashboard คลังย่อย', 'url' => ['/inventory-v2/sub-stock/dashboard']];
$this->params['breadcrumbs'][] = $this->title;

$subWarehouses = $subWarehouses ?? [];
$usageHistory = $usageHistory ?? [];
$currentWarehouseId = $currentWarehouseId ?? null;
$getLotsUrl = Url::to(['/inventory-v2/sub-stock/get-available-lots']);
$saveUrl = Url::to(['/inventory-v2/sub-stock/save-usage']);
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

<div class="sticky-context-bar" id="stickyContextBar" hidden role="region" aria-label="บริบทการเบิก">
    <div class="container-fluid px-3 px-md-4">
        <div class="d-flex align-items-center gap-2">
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="ctx-chip" id="stickyCtxWarehouse">
                        <i class="bi bi-house-door"></i><span data-text>—</span>
                    </span>
                    <span class="ctx-chip" id="stickyCtxJob">
                        <i class="bi bi-briefcase"></i><span data-text>—</span>
                    </span>
                    <span class="ctx-chip" id="stickyCtxRef" hidden>
                        <i class="bi bi-tag"></i><span data-text>—</span>
                    </span>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-light border rounded-2 px-3" id="stickyCtxEdit" aria-label="กลับไปแก้บริบทด้านบน">
                <i class="bi bi-pencil"></i>
                <span class="d-none d-sm-inline ms-1">แก้บริบท</span>
            </button>
        </div>
    </div>
</div>

<?= $this->render('_menu_sub_stock', [
    'active' => 'issue',
    'currentWarehouseId' => $currentWarehouseId,
]) ?>

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

<ol class="issue-stepper" id="issueStepper" aria-label="ขั้นตอนการบันทึก">
    <li class="issue-step is-active" data-step="1">
        <span class="issue-step__indicator" aria-hidden="true">
            <span class="issue-step__num">1</span>
            <i class="bi bi-check2 issue-step__check"></i>
        </span>
        <div class="issue-step__body">
            <div class="issue-step__title">บริบทการเบิก</div>
            <div class="issue-step__caption">คลัง · ประเภทงาน · อ้างอิง</div>
        </div>
    </li>
    <li class="issue-step" data-step="2">
        <span class="issue-step__indicator" aria-hidden="true">
            <span class="issue-step__num">2</span>
            <i class="bi bi-check2 issue-step__check"></i>
        </span>
        <div class="issue-step__body">
            <div class="issue-step__title">เลือกพัสดุ</div>
            <div class="issue-step__caption">ค้นหาและเพิ่มเข้ารายการ</div>
        </div>
    </li>
    <li class="issue-step" data-step="3">
        <span class="issue-step__indicator" aria-hidden="true">
            <span class="issue-step__num">3</span>
            <i class="bi bi-check2 issue-step__check"></i>
        </span>
        <div class="issue-step__body">
            <div class="issue-step__title">ยืนยันบันทึก</div>
            <div class="issue-step__caption">ตรวจสอบและส่ง</div>
        </div>
    </li>
</ol>

<div class="row g-3 g-lg-4">
    <div class="col-12 col-lg-7">

        <section class="surface-card mb-3" id="contextCard" aria-labelledby="hdrContext">
            <header class="surface-card__head">
                <h2 id="hdrContext" class="surface-card__title">
                    <i class="bi bi-clipboard-check"></i> บริบทการเบิก
                </h2>
            </header>
            <div class="surface-card__body">
                <div class="form-grid">
                    <div class="form-grid__row">
                        <label for="warehouseSelect" class="form-grid__label">คลังย่อย</label>
                        <select class="form-select form-control-input" id="warehouseSelect" required>
                            <option value="">เลือกคลังย่อย</option>
                            <?php foreach ($subWarehouses as $w): ?>
                                <option value="<?= (int)$w->id ?>" <?= $currentWarehouseId && (int)$w->id === (int)$currentWarehouseId ? 'selected' : '' ?>>
                                    <?= Html::encode($w->warehouse_name) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-grid__row">
                        <span class="form-grid__label">ประเภทงาน</span>
                        <div class="seg-control" role="radiogroup" aria-label="ประเภทงาน">
                            <?php foreach ($jobOptions as $i => $opt): ?>
                                <button type="button"
                                        class="seg-control__item <?= $i === 0 ? 'is-active' : '' ?>"
                                        data-value="<?= $opt['value'] ?>"
                                        role="radio"
                                        aria-checked="<?= $i === 0 ? 'true' : 'false' ?>">
                                    <i class="bi <?= $opt['icon'] ?> seg-control__icon"></i>
                                    <span class="seg-control__label">
                                        <span class="seg-control__title"><?= $opt['label'] ?></span>
                                        <span class="seg-control__caption"><?= $opt['caption'] ?></span>
                                    </span>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <select id="jobType" hidden aria-hidden="true">
                            <?php foreach ($jobOptions as $opt): ?>
                                <option value="<?= $opt['value'] ?>"><?= $opt['label'] ?> (<?= $opt['caption'] ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-grid__row">
                        <label for="referenceInput" class="form-grid__label" id="dynamicLabel">HN / ชื่อคนไข้</label>
                        <input type="text" class="form-control form-control-input" id="referenceInput"
                               placeholder="ระบุอ้างอิง (ถ้ามี)">
                    </div>
                </div>
            </div>
        </section>

        <section class="surface-card" aria-labelledby="hdrPicker">
            <header class="surface-card__head">
                <h2 id="hdrPicker" class="surface-card__title">
                    <i class="bi bi-box-seam"></i> เลือกพัสดุ
                </h2>
                <span class="kbd-hints d-none d-lg-flex" aria-hidden="true">
                    <span><kbd>/</kbd> ค้นหา</span>
                    <span><kbd>↑↓</kbd> เลื่อน</span>
                    <span><kbd>Enter</kbd> เพิ่ม</span>
                </span>
            </header>
            <div class="surface-card__body">

                <div id="pickerStateInitial" class="picker-state">
                    <div class="picker-state__icon">
                        <i class="bi bi-arrow-up-circle"></i>
                    </div>
                    <div class="picker-state__title">เลือกคลังย่อยก่อน</div>
                    <div class="picker-state__caption">เพื่อโหลดรายการพัสดุที่มีในสต็อก</div>
                </div>

                <div id="pickerStateLoading" class="picker-state d-none picker-state--loading">
                    <div class="skeleton-row"><div class="skeleton-block skeleton-block--icon"></div><div class="skeleton-lines"><div class="skeleton-line"></div><div class="skeleton-line skeleton-line--sm"></div></div><div class="skeleton-block skeleton-block--num"></div></div>
                    <div class="skeleton-row"><div class="skeleton-block skeleton-block--icon"></div><div class="skeleton-lines"><div class="skeleton-line"></div><div class="skeleton-line skeleton-line--sm"></div></div><div class="skeleton-block skeleton-block--num"></div></div>
                    <div class="skeleton-row"><div class="skeleton-block skeleton-block--icon"></div><div class="skeleton-lines"><div class="skeleton-line"></div><div class="skeleton-line skeleton-line--sm"></div></div><div class="skeleton-block skeleton-block--num"></div></div>
                </div>

                <div id="pickerStateEmpty" class="picker-state d-none">
                    <div class="picker-state__icon picker-state__icon--warn">
                        <i class="bi bi-inbox"></i>
                    </div>
                    <div class="picker-state__title">คลังนี้ยังไม่มีพัสดุในสต็อก</div>
                    <div class="picker-state__caption mb-3">ต้องรับของจากคลังหลักก่อนจึงตัดจ่ายได้</div>
                    <a href="<?= $requisitionUrl ?>" class="btn btn-primary px-3">
                        <i class="bi bi-file-earmark-plus me-1"></i>สร้างใบขอเบิก
                    </a>
                </div>

                <div id="pickerStateReady" class="picker-state d-none picker-state--ready">

                    <div class="recent-chips mb-3" id="recentChips" hidden>
                        <div class="recent-chips__label">
                            <i class="bi bi-clock-history"></i>เบิกล่าสุด · แตะเพื่อเลือกเร็ว
                        </div>
                        <div class="recent-chips__list" id="recentChipsList"></div>
                    </div>

                    <label for="itemSearchInput" class="form-grid__label">ค้นหาพัสดุ</label>
                    <div class="search-input-wrap">
                        <i class="bi bi-search search-input__icon"></i>
                        <input type="search"
                               id="itemSearchInput"
                               class="form-control form-control-input search-input"
                               placeholder="พิมพ์ชื่อ หรือ รหัสพัสดุ"
                               autocomplete="off"
                               inputmode="search"
                               aria-controls="searchResults"
                               aria-autocomplete="list">
                        <button type="button" id="itemSearchClear" class="search-input__clear" hidden aria-label="ล้างคำค้น">
                            <i class="bi bi-x-circle-fill"></i>
                        </button>
                    </div>

                    <div class="search-results-wrap">
                        <ul class="search-results d-none" id="searchResults" role="listbox" aria-label="ผลค้นหาพัสดุ"></ul>
                        <div class="search-empty d-none" id="searchEmpty">
                            <i class="bi bi-search me-1"></i>ไม่พบพัสดุที่ค้น
                        </div>
                    </div>

                    <div id="selectedItemPanel" class="selected-panel mt-3 d-none" aria-live="polite">
                        <div class="selected-panel__head">
                            <div class="selected-panel__icon">
                                <i class="bi bi-box-seam"></i>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="selected-panel__name" id="selectedItemName"></div>
                                <div class="selected-panel__meta" id="selectedItemMeta"></div>
                            </div>
                            <button type="button" id="selectedItemClear" class="icon-btn" aria-label="ยกเลิกที่เลือก">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>

                        <div class="selected-panel__qty">
                            <div>
                                <label for="inputQty" class="form-grid__label">จำนวนที่ใช้</label>
                                <div class="qty-stepper">
                                    <button type="button" class="qty-stepper__btn" id="qtyMinus" aria-label="ลดจำนวน">
                                        <i class="bi bi-dash-lg"></i>
                                    </button>
                                    <input type="number" class="qty-stepper__input" id="inputQty"
                                           value="1" min="0.01" step="1" inputmode="decimal" aria-label="จำนวน">
                                    <button type="button" class="qty-stepper__btn" id="qtyPlus" aria-label="เพิ่มจำนวน">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                                <div class="balance-hint" id="balanceHint">
                                    <i class="bi bi-info-circle me-1"></i>เลือกพัสดุเพื่อดูยอดคงเหลือ
                                </div>
                            </div>
                            <div class="selected-panel__action">
                                <button type="button" class="btn btn-primary btn-block" id="btnAddToList" disabled>
                                    <i class="bi bi-plus-lg me-1"></i><span class="btn-add-mode">เพิ่มเข้ารายการ</span>
                                    <kbd class="ms-2 d-none d-sm-inline-flex kbd-inline">Enter</kbd>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="col-12 col-lg-5">
        <aside class="surface-card order-summary cart-card" aria-labelledby="hdrSummary">
            <header class="surface-card__head order-summary__head">
                <h2 id="hdrSummary" class="surface-card__title">
                    <i class="bi bi-receipt"></i> สรุปและยืนยัน
                </h2>
                <span class="count-pill" id="cartCount">0 รายการ</span>
            </header>
            <div class="surface-card__body">

                <dl class="summary-recap" id="summaryRecap" hidden>
                    <div class="summary-recap__row">
                        <dt><i class="bi bi-house-door"></i>คลัง</dt>
                        <dd id="recapWh">—</dd>
                    </div>
                    <div class="summary-recap__row">
                        <dt><i class="bi bi-briefcase"></i>ประเภทงาน</dt>
                        <dd id="recapJob">—</dd>
                    </div>
                    <div class="summary-recap__row" id="recapRefRow" hidden>
                        <dt><i class="bi bi-tag"></i><span id="recapRefLabel">อ้างอิง</span></dt>
                        <dd id="recapRef">—</dd>
                    </div>
                </dl>

                <div class="summary-divider" id="summaryDivider" hidden></div>

                <div id="cartEmpty" class="cart-empty">
                    <i class="bi bi-cart3 cart-empty__icon"></i>
                    <div class="cart-empty__title">ยังไม่มีรายการ</div>
                    <div class="cart-empty__caption">ค้นหาและเลือกพัสดุจากแผงซ้าย</div>
                </div>

                <ul class="cart-list d-none" id="cartList"></ul>

                <div class="summary-totals" id="summaryTotals" hidden>
                    <div class="summary-totals__row">
                        <span>จำนวนรายการ</span>
                        <span class="summary-totals__value" id="totalCount">0</span>
                    </div>
                    <div class="summary-totals__row">
                        <span>รวมจำนวนตัดจ่าย</span>
                        <span class="summary-totals__value" id="totalQty">0</span>
                    </div>
                </div>

                <div class="d-none d-lg-block order-summary__actions">
                    <button type="button" class="btn btn-primary btn-block btn-save" id="btnSaveFinal" disabled>
                        <span class="btn-save__label"><i class="bi bi-check2"></i> ยืนยันการบันทึก</span>
                        <span class="btn-save__progress" aria-hidden="true"></span>
                    </button>
                    <a href="<?= $dashboardUrl ?>" class="btn btn-light btn-block mt-2">ยกเลิก</a>
                    <div class="order-summary__hint d-none d-lg-block" id="saveHint">
                        เพิ่มพัสดุอย่างน้อย 1 รายการเพื่อบันทึก
                        <kbd class="kbd-inline ms-1">Ctrl</kbd>+<kbd class="kbd-inline">S</kbd>
                    </div>
                </div>
            </div>
        </aside>
    </div>
</div>

<div class="sticky-savebar d-lg-none" id="stickySavebar">
    <div class="sticky-savebar__meta">
        <span id="savebarCount">0 รายการ</span>
        <span class="sticky-savebar__dot" aria-hidden="true">·</span>
        <span id="savebarQty">รวม 0</span>
    </div>
    <button type="button" class="btn btn-primary btn-save sticky-savebar__btn" id="btnSaveFinalMobile" disabled>
        <span class="btn-save__label"><i class="bi bi-check2"></i> ยืนยันการบันทึก</span>
        <span class="btn-save__progress" aria-hidden="true"></span>
    </button>
</div>

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

    --radius: 10px;
    --radius-sm: 8px;
    --radius-xs: 6px;
    --shadow-1: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 1px rgba(15, 23, 42, 0.03);
    --shadow-2: 0 6px 18px rgba(15, 23, 42, 0.06), 0 2px 4px rgba(15, 23, 42, 0.04);

    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    --ease-in: cubic-bezier(0.7, 0, 0.84, 0);
    --t-fast: 120ms var(--ease);
    --t-mid: 180ms var(--ease);
    --t-slow: 240ms var(--ease);
}

.sub-stock-issue { color: var(--ink-1); }

/* ---------- Surface Card ---------- */
.sub-stock-issue .surface-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    overflow: hidden;
}
.sub-stock-issue .surface-card__head {
    display: flex; align-items: center; justify-content: space-between;
    gap: 0.75rem;
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid var(--line);
    background: linear-gradient(180deg, #fafbfd 0%, #ffffff 100%);
}
.sub-stock-issue .surface-card__title {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--ink-1);
    margin: 0;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    line-height: 1.2;
}
.sub-stock-issue .surface-card__title i { color: var(--primary); font-size: 1.05rem; }
.sub-stock-issue .surface-card__body { padding: 1rem 1.1rem 1.1rem; }

/* ---------- Stepper ---------- */
.sub-stock-issue .issue-stepper {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    list-style: none;
    margin: 0 0 1rem;
    padding: 0.4rem 0.5rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    position: relative;
}
.sub-stock-issue .issue-step {
    display: flex; align-items: center; gap: 0.65rem;
    padding: 0.55rem 0.75rem;
    position: relative;
    min-width: 0;
}
.sub-stock-issue .issue-step + .issue-step::before {
    content: '';
    position: absolute;
    left: -1px; top: 50%;
    width: 2px; height: 18px;
    background: var(--line);
    transform: translateY(-50%);
    border-radius: 1px;
}
.sub-stock-issue .issue-step__indicator {
    flex-shrink: 0;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--surface-3);
    color: var(--ink-3);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 0.82rem; font-weight: 600;
    position: relative;
    transition: background-color var(--t-mid), color var(--t-mid), box-shadow var(--t-mid);
}
.sub-stock-issue .issue-step__num,
.sub-stock-issue .issue-step__check {
    position: absolute; inset: 0;
    display: inline-flex; align-items: center; justify-content: center;
    transition: opacity var(--t-fast), transform var(--t-fast);
}
.sub-stock-issue .issue-step__check { opacity: 0; transform: scale(0.6); font-size: 0.95rem; }
.sub-stock-issue .issue-step__body { min-width: 0; }
.sub-stock-issue .issue-step__title {
    font-size: 0.83rem; font-weight: 600; color: var(--ink-2); line-height: 1.2;
    transition: color var(--t-mid);
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.sub-stock-issue .issue-step__caption {
    font-size: 0.72rem; color: var(--ink-3); line-height: 1.3;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}

.sub-stock-issue .issue-step.is-active .issue-step__indicator {
    background: var(--primary);
    color: #fff;
    box-shadow: 0 0 0 4px var(--primary-soft);
}
.sub-stock-issue .issue-step.is-active .issue-step__title { color: var(--ink-1); }
.sub-stock-issue .issue-step.is-done .issue-step__indicator {
    background: var(--success);
    color: #fff;
}
.sub-stock-issue .issue-step.is-done .issue-step__num { opacity: 0; transform: scale(0.6); }
.sub-stock-issue .issue-step.is-done .issue-step__check { opacity: 1; transform: scale(1); }
.sub-stock-issue .issue-step.is-done .issue-step__title { color: var(--ink-1); }

@media (max-width: 575.98px) {
    .sub-stock-issue .issue-step { padding: 0.5rem 0.4rem; }
    .sub-stock-issue .issue-step__body { display: none; }
    .sub-stock-issue .issue-step.is-active .issue-step__body {
        display: block;
        position: absolute;
        top: 100%; left: 50%;
        transform: translateX(-50%);
        white-space: nowrap;
        background: var(--ink-1);
        color: #fff;
        padding: 0.3rem 0.6rem;
        border-radius: var(--radius-xs);
        margin-top: 4px;
        z-index: 2;
        pointer-events: none;
    }
    .sub-stock-issue .issue-step.is-active .issue-step__title { color: #fff; font-size: 0.78rem; }
    .sub-stock-issue .issue-step.is-active .issue-step__caption { color: rgba(255,255,255,0.7); font-size: 0.7rem; }
    .sub-stock-issue .issue-stepper { padding: 0.4rem 0.3rem; margin-bottom: 1.75rem; }
}

/* ---------- Form controls ---------- */
.sub-stock-issue .form-grid { display: grid; gap: 0.85rem; }
.sub-stock-issue .form-grid__row { display: flex; flex-direction: column; gap: 0.4rem; min-width: 0; }
.sub-stock-issue .form-grid__label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--ink-2);
    margin: 0;
}
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

/* ---------- Segmented control (job type) ---------- */
.sub-stock-issue .seg-control {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 0.4rem;
    padding: 0.3rem;
    background: var(--surface-2);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
}
@media (max-width: 575.98px) {
    .sub-stock-issue .seg-control { grid-template-columns: repeat(2, 1fr); }
}
.sub-stock-issue .seg-control__item {
    display: flex; flex-direction: column; align-items: center;
    gap: 0.25rem;
    padding: 0.55rem 0.4rem;
    background: transparent;
    border: 1px solid transparent;
    border-radius: var(--radius-xs);
    color: var(--ink-2);
    font-size: 0.85rem;
    min-height: 64px;
    cursor: pointer;
    transition: background-color var(--t-fast), color var(--t-fast), border-color var(--t-fast), box-shadow var(--t-fast);
    text-align: center;
    line-height: 1.15;
}
.sub-stock-issue .seg-control__item:hover {
    background: var(--surface);
    color: var(--ink-1);
}
.sub-stock-issue .seg-control__icon { font-size: 1.1rem; }
.sub-stock-issue .seg-control__label {
    display: flex; flex-direction: column; gap: 0.05rem;
    width: 100%;
    min-width: 0;
}
.sub-stock-issue .seg-control__title { font-weight: 600; font-size: 0.8rem; }
.sub-stock-issue .seg-control__caption { font-size: 0.68rem; color: var(--ink-3); }
.sub-stock-issue .seg-control__item.is-active {
    background: var(--surface);
    border-color: var(--primary);
    color: var(--primary);
    box-shadow: 0 0 0 2px var(--primary-soft);
}
.sub-stock-issue .seg-control__item.is-active .seg-control__caption { color: var(--primary); opacity: 0.75; }
.sub-stock-issue .seg-control__item:focus-visible {
    outline: 2px solid var(--primary);
    outline-offset: 2px;
}

/* ---------- Picker states ---------- */
.sub-stock-issue .picker-state {
    min-height: 120px;
    text-align: center;
    padding: 1.25rem 0;
}
.sub-stock-issue .picker-state__icon {
    width: 56px; height: 56px;
    margin: 0 auto 0.75rem;
    border-radius: 14px;
    background: var(--surface-3);
    color: var(--ink-3);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
}
.sub-stock-issue .picker-state__icon--warn {
    background: var(--warning-soft);
    color: var(--warning);
}
.sub-stock-issue .picker-state__title { font-weight: 600; color: var(--ink-1); margin-bottom: 0.2rem; }
.sub-stock-issue .picker-state__caption { font-size: 0.85rem; color: var(--ink-3); }

.sub-stock-issue .picker-state--loading { text-align: left; padding: 0.5rem 0; display: flex; flex-direction: column; gap: 0.5rem; }
.sub-stock-issue .picker-state--ready { text-align: left; padding: 0; }

/* Skeleton */
.sub-stock-issue .skeleton-row {
    display: flex; align-items: center; gap: 0.75rem;
    padding: 0.6rem 0.25rem;
}
.sub-stock-issue .skeleton-block,
.sub-stock-issue .skeleton-line {
    background: linear-gradient(90deg, var(--surface-3) 0%, var(--surface-hover) 50%, var(--surface-3) 100%);
    background-size: 200% 100%;
    animation: shimmer 1.4s ease-in-out infinite;
    border-radius: var(--radius-xs);
}
.sub-stock-issue .skeleton-block--icon { width: 36px; height: 36px; border-radius: 8px; flex-shrink: 0; }
.sub-stock-issue .skeleton-block--num { width: 52px; height: 18px; margin-left: auto; flex-shrink: 0; }
.sub-stock-issue .skeleton-lines { flex-grow: 1; display: flex; flex-direction: column; gap: 0.4rem; min-width: 0; }
.sub-stock-issue .skeleton-line { height: 12px; }
.sub-stock-issue .skeleton-line--sm { height: 9px; width: 55%; }
@keyframes shimmer {
    0% { background-position: 200% 50%; }
    100% { background-position: -200% 50%; }
}

/* ---------- Recent chips ---------- */
.sub-stock-issue .recent-chips__label {
    font-size: 0.8rem; color: var(--ink-3); font-weight: 500;
    margin-bottom: 0.5rem;
    display: inline-flex; align-items: center; gap: 0.4rem;
}
.sub-stock-issue .recent-chips__list { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.sub-stock-issue .recent-chip {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.4rem 0.75rem;
    background: var(--surface-2);
    border: 1px solid var(--line);
    color: var(--ink-1);
    border-radius: 999px;
    font-size: 0.85rem; font-weight: 500;
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
.sub-stock-issue .recent-chip i { color: var(--ink-3); font-size: 0.85rem; }
.sub-stock-issue .recent-chip:hover i { color: var(--primary); }
.sub-stock-issue .recent-chip__name {
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    max-width: 14rem;
}

/* ---------- Search ---------- */
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

.sub-stock-issue .search-results-wrap { position: relative; }
.sub-stock-issue .search-results {
    list-style: none; margin: 0.4rem 0 0; padding: 0;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    max-height: 340px;
    overflow-y: auto;
    box-shadow: var(--shadow-2);
}
.sub-stock-issue .search-result {
    padding: 0.65rem 0.85rem;
    cursor: pointer;
    border-bottom: 1px solid var(--line);
    transition: background-color var(--t-fast);
    display: flex; align-items: center; gap: 0.7rem;
}
.sub-stock-issue .search-result:last-child { border-bottom: 0; }
.sub-stock-issue .search-result:hover,
.sub-stock-issue .search-result.is-active { background: var(--surface-2); }
.sub-stock-issue .search-result.is-active { box-shadow: inset 3px 0 0 var(--primary); }
.sub-stock-issue .search-result__icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    background: var(--surface-3);
    color: var(--ink-2);
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.sub-stock-issue .search-result__name {
    font-weight: 600; color: var(--ink-1);
    line-height: 1.3;
    word-break: break-word;
    font-size: 0.92rem;
}
.sub-stock-issue .search-result__meta {
    font-size: 0.75rem; color: var(--ink-3);
    display: flex; gap: 0.5rem; flex-wrap: wrap;
    margin-top: 0.1rem;
}
.sub-stock-issue .search-result__balance {
    margin-left: auto;
    text-align: right;
    flex-shrink: 0;
    font-weight: 600; font-size: 0.9rem;
    color: var(--success);
    white-space: nowrap;
}
.sub-stock-issue .search-result mark {
    background: rgba(180, 83, 9, 0.18);
    color: inherit; padding: 0 2px;
    border-radius: 2px;
}
.sub-stock-issue .search-empty {
    margin-top: 0.5rem;
    padding: 0.9rem;
    background: var(--surface-2);
    border: 1px dashed var(--line);
    border-radius: var(--radius-sm);
    color: var(--ink-3);
    font-size: 0.85rem;
    text-align: center;
}

/* ---------- Selected item panel ---------- */
.sub-stock-issue .selected-panel {
    background: var(--surface-2);
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    padding: 0.9rem;
    animation: selected-enter 180ms var(--ease);
}
@keyframes selected-enter {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
.sub-stock-issue .selected-panel__head { display: flex; align-items: start; gap: 0.6rem; }
.sub-stock-issue .selected-panel__icon {
    flex-shrink: 0;
    width: 38px; height: 38px;
    border-radius: 8px;
    background: var(--primary-soft);
    color: var(--primary);
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 1rem;
}
.sub-stock-issue .selected-panel__name {
    font-weight: 600; color: var(--ink-1); font-size: 0.95rem;
    line-height: 1.3;
    word-break: break-word;
}
.sub-stock-issue .selected-panel__meta {
    font-size: 0.78rem; color: var(--ink-3);
    display: flex; gap: 0.5rem; flex-wrap: wrap;
    margin-top: 0.2rem;
}
.sub-stock-issue .selected-panel__qty {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr);
    gap: 0.75rem;
    align-items: end;
    margin-top: 0.85rem;
}
.sub-stock-issue .selected-panel__action { align-self: end; }
@media (max-width: 575.98px) {
    .sub-stock-issue .selected-panel__qty { grid-template-columns: 1fr; }
}

.sub-stock-issue .icon-btn {
    background: transparent; border: 0;
    color: var(--ink-4);
    border-radius: 8px;
    width: 30px; height: 30px;
    display: inline-flex; align-items: center; justify-content: center;
    transition: color var(--t-fast), background-color var(--t-fast);
    flex-shrink: 0;
    cursor: pointer;
}
.sub-stock-issue .icon-btn:hover { background: var(--surface-hover); color: var(--ink-1); }

/* ---------- Qty stepper ---------- */
.sub-stock-issue .qty-stepper {
    display: flex;
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    overflow: hidden;
    background: var(--surface);
    transition: border-color var(--t-fast), box-shadow var(--t-fast);
}
.sub-stock-issue .qty-stepper:focus-within {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.sub-stock-issue .qty-stepper__btn {
    border: 0; background: transparent; color: var(--ink-2);
    min-width: 42px; min-height: 42px;
    font-size: 1rem;
    cursor: pointer;
    transition: background-color var(--t-fast), color var(--t-fast);
}
.sub-stock-issue .qty-stepper__btn:hover { background: var(--surface-hover); color: var(--ink-1); }
.sub-stock-issue .qty-stepper__btn:active { background: var(--surface-3); }
.sub-stock-issue .qty-stepper__input {
    border: 0; outline: 0; background: transparent;
    flex: 1; min-width: 0;
    text-align: center;
    font-size: 1.05rem; font-weight: 600;
    color: var(--ink-1);
    padding: 0.3rem 0.5rem;
    min-height: 42px;
}
.sub-stock-issue .qty-stepper__input::-webkit-outer-spin-button,
.sub-stock-issue .qty-stepper__input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }

/* ---------- Balance hint ---------- */
.sub-stock-issue .balance-hint {
    margin-top: 0.4rem;
    font-size: 0.8rem;
    color: var(--ink-3);
    min-height: 1.2rem;
    display: flex; align-items: center;
    transition: color var(--t-fast);
}
.sub-stock-issue .balance-hint.is-ok { color: var(--success); }
.sub-stock-issue .balance-hint.is-warn { color: var(--warning); font-weight: 500; }

/* ---------- Buttons ---------- */
.sub-stock-issue .btn { border-radius: var(--radius-sm); font-weight: 600; transition: background-color var(--t-fast), border-color var(--t-fast), color var(--t-fast), box-shadow var(--t-fast), transform 80ms; }
.sub-stock-issue .btn-block { display: flex; align-items: center; justify-content: center; width: 100%; min-height: 44px; }
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

.sub-stock-issue .kbd-inline,
.sub-stock-issue kbd {
    display: inline-flex; align-items: center;
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 0.72rem; font-weight: 600;
    line-height: 1;
    padding: 2px 5px;
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.28);
    border-radius: 4px;
    color: inherit;
}
.sub-stock-issue .surface-card__head kbd,
.sub-stock-issue .order-summary__hint kbd {
    background: var(--surface-3);
    border-color: var(--line-strong);
    color: var(--ink-2);
}
.sub-stock-issue .kbd-hints {
    display: flex; align-items: center; gap: 0.65rem;
    font-size: 0.72rem;
    color: var(--ink-3);
}
.sub-stock-issue .kbd-hints span { display: inline-flex; align-items: center; gap: 0.25rem; }

/* ---------- Save button progress ---------- */
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

/* ---------- Order summary ---------- */
.sub-stock-issue .order-summary__head {
    align-items: center;
}
.sub-stock-issue .count-pill {
    background: var(--surface-3);
    color: var(--ink-2);
    font-size: 0.78rem; font-weight: 600;
    padding: 0.25rem 0.6rem;
    border-radius: 999px;
    transition: background-color var(--t-fast), color var(--t-fast);
}
.sub-stock-issue .count-pill.is-active {
    background: var(--primary);
    color: #fff;
}
.sub-stock-issue .summary-recap {
    margin: 0 0 0.85rem;
    padding: 0.7rem 0.85rem;
    background: var(--surface-2);
    border-radius: var(--radius-sm);
    display: grid;
    gap: 0.45rem;
    animation: recap-enter 160ms var(--ease);
}
@keyframes recap-enter {
    from { opacity: 0; transform: translateY(-3px); }
    to { opacity: 1; transform: translateY(0); }
}
.sub-stock-issue .summary-recap__row { display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; }
.sub-stock-issue .summary-recap__row dt {
    display: inline-flex; align-items: center; gap: 0.35rem;
    color: var(--ink-3); font-weight: 500;
    margin: 0; min-width: 5.5rem;
}
.sub-stock-issue .summary-recap__row dt i { color: var(--ink-4); font-size: 0.85rem; }
.sub-stock-issue .summary-recap__row dd {
    margin: 0; color: var(--ink-1); font-weight: 600;
    flex-grow: 1; min-width: 0;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.sub-stock-issue .summary-divider {
    height: 1px; background: var(--line); margin: 0.5rem 0 0.9rem;
}

/* ---------- Cart empty ---------- */
.sub-stock-issue .cart-empty {
    text-align: center;
    padding: 1.5rem 0 1.25rem;
}
.sub-stock-issue .cart-empty__icon {
    color: var(--ink-4);
    font-size: 2rem;
    display: block;
    margin-bottom: 0.5rem;
}
.sub-stock-issue .cart-empty__title { font-weight: 600; color: var(--ink-2); margin-bottom: 0.15rem; }
.sub-stock-issue .cart-empty__caption { font-size: 0.82rem; color: var(--ink-3); }

/* ---------- Cart list ---------- */
.sub-stock-issue .cart-list {
    list-style: none;
    margin: 0;
    padding: 0;
    max-height: 52vh;
    overflow-y: auto;
    display: flex; flex-direction: column; gap: 0.4rem;
}
.sub-stock-issue .cart-item {
    display: flex; gap: 0.65rem; align-items: center;
    padding: 0.65rem 0.75rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    transition: border-color var(--t-fast), background-color var(--t-fast), transform var(--t-fast), opacity var(--t-fast);
}
.sub-stock-issue .cart-item:hover { border-color: var(--line-strong); }
.sub-stock-issue .cart-item.is-new {
    animation: cart-item-pop 280ms var(--ease);
    border-color: var(--primary-line);
    background: var(--primary-soft);
}
@keyframes cart-item-pop {
    0% { opacity: 0; transform: scale(0.92); }
    60% { opacity: 1; transform: scale(1.02); }
    100% { transform: scale(1); }
}
.sub-stock-issue .cart-item--removing { opacity: 0; transform: translateX(20px); pointer-events: none; }
.sub-stock-issue .cart-item__icon {
    width: 34px; height: 34px; flex-shrink: 0;
    border-radius: 8px;
    background: var(--surface-3);
    color: var(--ink-2);
    display: inline-flex; align-items: center; justify-content: center;
}
.sub-stock-issue .cart-item__body { flex-grow: 1; min-width: 0; }
.sub-stock-issue .cart-item__name { font-weight: 600; color: var(--ink-1); line-height: 1.3; word-break: break-word; font-size: 0.9rem; }
.sub-stock-issue .cart-item__meta { font-size: 0.75rem; color: var(--ink-3); display: flex; gap: 0.5rem; flex-wrap: wrap; margin-top: 0.1rem; }
.sub-stock-issue .cart-item__qty-wrap { text-align: right; flex-shrink: 0; }
.sub-stock-issue .cart-item__qty { font-weight: 700; color: var(--ink-1); font-size: 1.02rem; line-height: 1.1; }
.sub-stock-issue .cart-item__unit { font-size: 0.7rem; color: var(--ink-3); }
.sub-stock-issue .cart-item__remove {
    background: transparent; border: 0;
    color: var(--ink-4);
    border-radius: 6px;
    width: 30px; height: 30px;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    cursor: pointer;
    transition: color var(--t-fast), background-color var(--t-fast);
}
.sub-stock-issue .cart-item__remove:hover { background: var(--danger-soft); color: var(--danger); }

/* FLIP fly clone */
.sub-stock-issue .flip-fly {
    position: fixed;
    z-index: 1050;
    pointer-events: none;
    will-change: transform, opacity;
    background: var(--primary-soft);
    border: 1px solid var(--primary-line);
    border-radius: var(--radius-sm);
    padding: 0.65rem 0.75rem;
    display: flex; align-items: center; gap: 0.65rem;
    color: var(--primary-ink);
    box-shadow: var(--shadow-2);
    font-weight: 600; font-size: 0.85rem;
    max-width: 320px;
}

/* ---------- Summary totals ---------- */
.sub-stock-issue .summary-totals {
    margin-top: 0.85rem;
    padding: 0.65rem 0.85rem;
    background: var(--surface-2);
    border: 1px solid var(--line);
    border-radius: var(--radius-sm);
    display: flex; flex-direction: column; gap: 0.3rem;
}
.sub-stock-issue .summary-totals__row {
    display: flex; justify-content: space-between; align-items: baseline;
    font-size: 0.85rem;
    color: var(--ink-2);
}
.sub-stock-issue .summary-totals__value {
    font-weight: 700; color: var(--ink-1); font-size: 0.95rem;
    font-variant-numeric: tabular-nums;
}

.sub-stock-issue .order-summary__actions { margin-top: 1rem; }
.sub-stock-issue .order-summary__hint {
    margin-top: 0.65rem;
    text-align: center;
    font-size: 0.75rem;
    color: var(--ink-3);
}

/* ---------- Sticky savebar ---------- */
.sub-stock-issue .sticky-savebar {
    position: fixed; left: 0; right: 0; bottom: 0;
    background: var(--surface);
    padding: 0.6rem 0.85rem calc(0.6rem + env(safe-area-inset-bottom));
    border-top: 1px solid var(--line);
    box-shadow: 0 -4px 16px rgba(15, 23, 42, 0.06);
    z-index: 1030;
    display: flex; align-items: center; gap: 0.6rem;
}
.sub-stock-issue .sticky-savebar__meta {
    font-size: 0.78rem;
    color: var(--ink-3);
    display: flex; align-items: center; gap: 0.3rem;
    line-height: 1.2;
    flex-shrink: 0;
    max-width: 35%;
}
.sub-stock-issue .sticky-savebar__dot { color: var(--ink-4); }
.sub-stock-issue .sticky-savebar__btn { flex-grow: 1; min-height: 44px; }
@media (min-width: 992px) {
    .sub-stock-issue .sticky-savebar { display: none !important; }
}
@media (max-width: 991.98px) {
    body.has-sticky-savebar { padding-bottom: 80px; }
}

/* ---------- Sticky context bar ---------- */
.sticky-context-bar {
    position: sticky;
    top: 0;
    z-index: 1025;
    background: rgba(255, 255, 255, 0.96);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    padding: 0.55rem 0;
    border-bottom: 1px solid rgba(15, 23, 42, 0.08);
    animation: ctxbar-enter 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes ctxbar-enter {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
.ctx-chip {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.3rem 0.7rem;
    background: #f1f5f9;
    color: #1a202c;
    border-radius: 999px;
    font-size: 0.82rem; font-weight: 500;
    max-width: 100%;
    border: 1px solid rgba(15, 23, 42, 0.06);
}
.ctx-chip i { color: #718096; font-size: 0.82rem; }
.ctx-chip > span[data-text] {
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    max-width: 12rem;
}

/* ---------- Sticky cart on desktop ---------- */
@media (min-width: 992px) {
    .sub-stock-issue .cart-card { position: sticky; top: 1rem; }
}

/* ---------- Empty block (no warehouse) ---------- */
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

/* ---------- Undo toast ---------- */
.undo-toast {
    position: fixed;
    left: 50%;
    bottom: calc(1rem + env(safe-area-inset-bottom));
    transform: translateX(-50%) translateY(8px);
    background: #1a202c;
    color: #fff;
    border-radius: 999px;
    padding: 0.6rem 0.65rem 0.6rem 1rem;
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
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    font-weight: 600; font-size: 0.85rem;
    cursor: pointer;
    transition: background-color 120ms cubic-bezier(0.16, 1, 0.3, 1);
}
.undo-toast__btn:hover { background: rgba(255,255,255,0.2); }
@media (min-width: 992px) {
    .undo-toast { bottom: 1.25rem; left: auto; right: 1.5rem; transform: translateY(8px); }
    .undo-toast.is-show { transform: translateY(0); }
}

/* ---------- Spin ---------- */
.sub-stock-issue .spin { animation: spin 0.8s linear infinite; display: inline-block; }
@keyframes spin { to { transform: rotate(360deg); } }

/* ---------- Reduced motion ---------- */
@media (prefers-reduced-motion: reduce) {
    .sub-stock-issue *,
    .sub-stock-issue *::before,
    .sub-stock-issue *::after,
    .sticky-context-bar,
    .undo-toast { animation: none !important; transition: opacity 80ms linear !important; }
    .sub-stock-issue .qty-stepper__btn:active { transform: none; }
    .sub-stock-issue .btn-primary:active:not(:disabled) { transform: none; }
    .undo-toast { transform: translateX(-50%) translateY(0); }
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
    var selectedItem = null;
    var isInitialWarehouseChange = true;
    var lastWarehouseValue = $('#warehouseSelect').val() || '';
    var activeResultIndex = -1;
    var visibleResults = [];
    var lastRemoved = null;
    var undoTimer = null;
    var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var isDesktop = window.matchMedia('(min-width: 992px)').matches;

    document.body.classList.add('has-sticky-savebar');

    /* =========================
       Stepper state
       ========================= */
    function updateStepper() {
        var hasWh = !!$('#warehouseSelect').val();
        var hasItems = rows.length > 0;
        var \$steps = $('.issue-step');
        \$steps.removeClass('is-done is-active');
        if (!hasWh) {
            \$steps.eq(0).addClass('is-active');
        } else if (!hasItems) {
            \$steps.eq(0).addClass('is-done');
            \$steps.eq(1).addClass('is-active');
        } else {
            \$steps.eq(0).addClass('is-done');
            \$steps.eq(1).addClass('is-done');
            \$steps.eq(2).addClass('is-active');
        }
    }

    /* =========================
       Picker states
       ========================= */
    function showPickerState(state) {
        ['Initial', 'Loading', 'Empty', 'Ready'].forEach(function(s) {
            var el = document.getElementById('pickerState' + s);
            if (el) el.classList.toggle('d-none', s !== state);
        });
    }

    /* =========================
       Sticky context bar
       ========================= */
    function updateStickyContext() {
        var whText = $('#warehouseSelect option:selected').text() || '—';
        var jobText = $('#jobType option:selected').text() || '—';
        var refText = ($('#referenceInput').val() || '').trim();
        $('#stickyCtxWarehouse [data-text]').text(whText);
        $('#stickyCtxJob [data-text]').text(jobText);
        if (refText) {
            $('#stickyCtxRef').prop('hidden', false).find('[data-text]').text(refText);
        } else {
            $('#stickyCtxRef').prop('hidden', true);
        }
        updateSummaryRecap();
    }
    var ctxCard = document.getElementById('contextCard');
    var stickyBar = document.getElementById('stickyContextBar');
    if (ctxCard && stickyBar && 'IntersectionObserver' in window) {
        var ctxObserver = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                var hasWarehouse = !!$('#warehouseSelect').val();
                stickyBar.hidden = entry.isIntersecting || !hasWarehouse;
            });
        }, { rootMargin: '-20px 0px 0px 0px', threshold: 0 });
        ctxObserver.observe(ctxCard);
    }
    $('#stickyCtxEdit').on('click', function() {
        ctxCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        setTimeout(function(){ \$('#warehouseSelect').focus(); }, 240);
    });

    /* =========================
       Order summary recap
       ========================= */
    function updateSummaryRecap() {
        var hasWh = !!$('#warehouseSelect').val();
        var recap = $('#summaryRecap');
        var divider = $('#summaryDivider');
        recap.prop('hidden', !hasWh);
        divider.prop('hidden', !hasWh);
        if (!hasWh) return;
        $('#recapWh').text($('#warehouseSelect option:selected').text() || '—');
        $('#recapJob').text($('.seg-control__item.is-active .seg-control__title').text() || '—');
        var refLabel = $('#dynamicLabel').text() || 'อ้างอิง';
        var refValue = ($('#referenceInput').val() || '').trim();
        $('#recapRefLabel').text(refLabel);
        if (refValue) {
            $('#recapRefRow').prop('hidden', false);
            $('#recapRef').text(refValue);
        } else {
            $('#recapRefRow').prop('hidden', true);
        }
    }

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
       Search picker
       ========================= */
    function highlight(text, query) {
        if (!query) return text;
        var safe = String(text || '');
        var idx = safe.toLowerCase().indexOf(query.toLowerCase());
        if (idx < 0) return safe;
        var before = safe.substring(0, idx);
        var match  = safe.substring(idx, idx + query.length);
        var after  = safe.substring(idx + query.length);
        var esc = function(s) { return s.replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"})[c]; }); };
        return esc(before) + '<mark>' + esc(match) + '</mark>' + esc(after);
    }

    function renderSearchResults(query) {
        var q = (query || '').trim().toLowerCase();
        var \$list = $('#searchResults');
        var \$empty = $('#searchEmpty');
        \$list.empty();
        visibleResults = [];
        activeResultIndex = -1;

        if (q === '') {
            \$list.addClass('d-none');
            \$empty.addClass('d-none');
            return;
        }
        lotsData.forEach(function(o) {
            var name = String(o.item_name || o.item_code || '');
            var code = String(o.item_code || '');
            var lot  = String(o.lot_number || '');
            if (name.toLowerCase().indexOf(q) >= 0 ||
                code.toLowerCase().indexOf(q) >= 0 ||
                lot.toLowerCase().indexOf(q) >= 0) {
                visibleResults.push(o);
            }
        });
        if (visibleResults.length === 0) {
            \$list.addClass('d-none');
            \$empty.removeClass('d-none');
            return;
        }
        \$empty.addClass('d-none');
        visibleResults.slice(0, 30).forEach(function(o, i) {
            var li = $('<li class="search-result" role="option"></li>')
                .attr('data-index', i)
                .attr('aria-selected', 'false');
            li.append('<div class="search-result__icon"><i class="bi bi-box-seam"></i></div>');
            var body = $('<div class="flex-grow-1 min-w-0"></div>');
            body.append($('<div class="search-result__name"></div>').html(highlight(o.item_name || o.item_code, query)));
            var meta = $('<div class="search-result__meta"></div>');
            meta.append('<span><i class="bi bi-bookmark me-1"></i>Lot ' + (o.lot_number || '-') + '</span>');
            if (o.item_code) meta.append('<span class="font-monospace">' + o.item_code + '</span>');
            body.append(meta);
            li.append(body);
            li.append('<div class="search-result__balance">' + (o.balance_qty || 0) + (o.unit ? ' ' + o.unit : '') + '</div>');
            li.on('click', function() { selectItem(o); });
            \$list.append(li);
        });
        \$list.removeClass('d-none');
    }

    function setActiveResult(idx) {
        var items = $('#searchResults .search-result');
        if (items.length === 0) return;
        if (idx < 0) idx = items.length - 1;
        if (idx >= items.length) idx = 0;
        items.removeClass('is-active').attr('aria-selected', 'false');
        var active = items.eq(idx);
        active.addClass('is-active').attr('aria-selected', 'true');
        activeResultIndex = idx;
        var li = active[0];
        if (li && li.scrollIntoView) li.scrollIntoView({ block: 'nearest' });
    }

    function clearSearch() {
        $('#itemSearchInput').val('').focus();
        $('#itemSearchClear').prop('hidden', true);
        $('#searchResults').addClass('d-none').empty();
        $('#searchEmpty').addClass('d-none');
        visibleResults = [];
        activeResultIndex = -1;
    }

    $('#itemSearchInput').on('input', function() {
        var v = this.value;
        $('#itemSearchClear').prop('hidden', v.length === 0);
        renderSearchResults(v);
    }).on('keydown', function(e) {
        if (e.key === 'ArrowDown') { e.preventDefault(); setActiveResult(activeResultIndex + 1); }
        else if (e.key === 'ArrowUp') { e.preventDefault(); setActiveResult(activeResultIndex - 1); }
        else if (e.key === 'Enter') {
            e.preventDefault();
            var idx = activeResultIndex >= 0 ? activeResultIndex : 0;
            if (visibleResults[idx]) selectItem(visibleResults[idx]);
        } else if (e.key === 'Escape') {
            if (this.value) clearSearch();
            else $(this).blur();
        }
    });
    $('#itemSearchClear').on('click', clearSearch);

    /* =========================
       Recent chips
       ========================= */
    function renderRecentChips() {
        var \$wrap = $('#recentChips');
        var \$list = $('#recentChipsList').empty();
        var matched = [];
        recentItemCodes.forEach(function(code) {
            for (var i = 0; i < lotsData.length; i++) {
                if (lotsData[i].item_code === code) {
                    matched.push(lotsData[i]);
                    return;
                }
            }
        });
        if (matched.length === 0) { \$wrap.prop('hidden', true); return; }
        matched.forEach(function(o) {
            var chip = $('<button type="button" class="recent-chip"></button>');
            chip.append('<i class="bi bi-arrow-up-right-circle"></i>');
            chip.append($('<span class="recent-chip__name"></span>').text(o.item_name || o.item_code));
            chip.on('click', function() { selectItem(o); });
            \$list.append(chip);
        });
        \$wrap.prop('hidden', false);
    }

    /* =========================
       Select / Add to cart
       ========================= */
    function selectItem(item) {
        selectedItem = item;
        $('#selectedItemName').text(item.item_name || item.item_code);
        var meta = $('#selectedItemMeta').empty();
        meta.append('<span><i class="bi bi-bookmark me-1"></i>Lot ' + (item.lot_number || '-') + '</span>');
        meta.append('<span><i class="bi bi-check2-circle me-1"></i>คงเหลือ ' + (item.balance_qty || 0) + (item.unit ? ' ' + item.unit : '') + '</span>');
        if (item.item_code) meta.append('<span class="font-monospace">' + item.item_code + '</span>');
        $('#selectedItemPanel').removeClass('d-none');
        $('#inputQty').val(1).focus().select();
        $('#searchResults').addClass('d-none').empty();
        $('#searchEmpty').addClass('d-none');
        $('#itemSearchInput').val('');
        $('#itemSearchClear').prop('hidden', true);
        updateBalanceHint();
    }

    function clearSelected() {
        selectedItem = null;
        $('#selectedItemPanel').addClass('d-none');
        if (isDesktop) $('#itemSearchInput').focus();
    }
    $('#selectedItemClear').on('click', clearSelected);

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

    function updateBalanceHint() {
        var hint = $('#balanceHint');
        var btn = $('#btnAddToList');
        if (!selectedItem) {
            hint.removeClass('is-ok is-warn').html('<i class="bi bi-info-circle me-1"></i>เลือกพัสดุเพื่อดูยอดคงเหลือ');
            btn.prop('disabled', true);
            return;
        }
        var balance = selectedItem.balance_qty || 0;
        var qty = parseFloat($('#inputQty').val()) || 0;
        var unit = selectedItem.unit || '';
        var unitText = unit ? ' ' + unit : '';
        var existingIdx = findCartIndex(selectedItem);
        var inCart = existingIdx >= 0 ? (parseFloat(rows[existingIdx].qty) || 0) : 0;
        var available = balance - inCart;

        if (qty <= 0) {
            hint.removeClass('is-ok').addClass('is-warn').html('<i class="bi bi-info-circle me-1"></i>ระบุจำนวนมากกว่า 0');
            btn.prop('disabled', true);
        } else if (qty > available) {
            var msg = inCart > 0
                ? 'ในตะกร้าแล้ว ' + inCart + unitText + ' · เพิ่มได้อีก ' + Math.max(0, available) + unitText
                : 'เกินยอดคงเหลือ (มี ' + balance + unitText + ')';
            hint.removeClass('is-ok').addClass('is-warn')
                .html('<i class="bi bi-exclamation-triangle me-1"></i>' + msg);
            btn.prop('disabled', true);
        } else {
            var note = inCart > 0
                ? 'ในตะกร้า ' + inCart + unitText + ' · เพิ่มได้อีก ' + available + unitText
                : 'คงเหลือ ' + balance + unitText;
            hint.removeClass('is-warn').addClass('is-ok')
                .html('<i class="bi bi-check-circle me-1"></i>' + note);
            btn.prop('disabled', false);
        }
        $('#btnAddToList .btn-add-mode').text(existingIdx >= 0 ? 'เพิ่มเข้ารายการเดิม' : 'เพิ่มเข้ารายการ');
    }
    $('#inputQty').on('input', updateBalanceHint).on('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); if (!$('#btnAddToList').is(':disabled')) $('#btnAddToList').trigger('click'); }
    });
    $('#qtyMinus').on('click', function() {
        var v = parseFloat($('#inputQty').val()) || 0;
        var step = parseFloat($('#inputQty').attr('step')) || 1;
        var min = parseFloat($('#inputQty').attr('min')) || 0;
        var next = Math.max(min, +(v - step).toFixed(2));
        $('#inputQty').val(next).trigger('input');
    });
    $('#qtyPlus').on('click', function() {
        var v = parseFloat($('#inputQty').val()) || 0;
        var step = parseFloat($('#inputQty').attr('step')) || 1;
        $('#inputQty').val(+(v + step).toFixed(2)).trigger('input');
    });

    /* =========================
       FLIP "fly to cart" animation
       ========================= */
    function flyToCart(item) {
        if (reducedMotion) return;
        var target = document.querySelector('.order-summary') || document.querySelector('#cartList');
        var source = document.querySelector('#selectedItemPanel');
        if (!target || !source) return;
        var srcRect = source.getBoundingClientRect();
        var dstRect = target.getBoundingClientRect();
        var clone = document.createElement('div');
        clone.className = 'flip-fly';
        clone.innerHTML = '<i class="bi bi-box-seam"></i><span>' + (item.item_name || item.item_code || 'พัสดุ') + '</span>';
        document.body.appendChild(clone);
        var cloneRect = clone.getBoundingClientRect();
        var startX = srcRect.left + (srcRect.width - cloneRect.width) / 2;
        var startY = srcRect.top + 20;
        clone.style.left = startX + 'px';
        clone.style.top = startY + 'px';
        var endX = dstRect.left + dstRect.width / 2 - cloneRect.width / 2;
        var endY = dstRect.top + 60;
        var tx = endX - startX;
        var ty = endY - startY;
        var anim = clone.animate([
            { transform: 'translate(0,0) scale(1)', opacity: 1, offset: 0 },
            { transform: 'translate(' + (tx * 0.4) + 'px,' + (ty * 0.3 - 30) + 'px) scale(0.95)', opacity: 1, offset: 0.5 },
            { transform: 'translate(' + tx + 'px,' + ty + 'px) scale(0.55)', opacity: 0, offset: 1 }
        ], { duration: 480, easing: 'cubic-bezier(0.5, 0, 0.75, 0)' });
        anim.onfinish = function() { clone.remove(); };
    }

    /* =========================
       Notifications
       ========================= */
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
        }
        hideUndoToast();
    });

    /* =========================
       Cart render + totals
       ========================= */
    function renderCart(opts) {
        opts = opts || {};
        var \$list = $('#cartList').empty();
        var count = rows.length;
        $('#cartCount').text(count + ' รายการ').toggleClass('is-active', count > 0);

        // Totals
        var totalQty = rows.reduce(function(s, r) { return s + (parseFloat(r.qty) || 0); }, 0);
        $('#totalCount').text(count);
        $('#totalQty').text((Math.round(totalQty * 100) / 100));
        $('#summaryTotals').prop('hidden', count === 0);

        // Mobile savebar meta
        $('#savebarCount').text(count + ' รายการ');
        $('#savebarQty').text('รวม ' + (Math.round(totalQty * 100) / 100));

        $('#btnSaveFinal, #btnSaveFinalMobile').prop('disabled', count === 0);
        $('#saveHint').toggle(count === 0);

        if (count === 0) {
            $('#cartEmpty').show();
            \$list.addClass('d-none');
            updateStepper();
            return;
        }
        $('#cartEmpty').hide();
        \$list.removeClass('d-none');
        rows.forEach(function(r, i) {
            var li = $('<li class="cart-item"></li>');
            if (i === opts.markIndex) li.addClass('is-new');
            li.append('<div class="cart-item__icon"><i class="bi bi-box-seam"></i></div>');
            var body = $('<div class="cart-item__body"></div>');
            body.append($('<div class="cart-item__name"></div>').text(r.item_name || r.item_code));
            var meta = $('<div class="cart-item__meta"></div>');
            meta.append($('<span></span>').html('<i class="bi bi-bookmark me-1"></i>Lot: ' + (r.lot_number || '-')));
            if (r.item_code) meta.append($('<span class="font-monospace"></span>').text(r.item_code));
            body.append(meta);
            li.append(body);
            var qtyWrap = $('<div class="cart-item__qty-wrap"></div>');
            qtyWrap.append('<div class="cart-item__qty">' + r.qty + '</div>');
            if (r.unit) qtyWrap.append('<div class="cart-item__unit">' + r.unit + '</div>');
            li.append(qtyWrap);
            var removeBtn = $('<button type="button" class="cart-item__remove" aria-label="ลบรายการ" title="ลบ"><i class="bi bi-x-lg"></i></button>');
            removeBtn.on('click', function() {
                var idx = i;
                lastRemoved = { index: idx, item: rows[idx] };
                li.addClass('cart-item--removing');
                setTimeout(function() {
                    rows.splice(idx, 1);
                    renderCart();
                    showUndoToast('ลบ "' + (lastRemoved.item.item_name || lastRemoved.item.item_code) + '" แล้ว');
                }, 160);
            });
            li.append(removeBtn);
            \$list.append(li);
        });
        updateStepper();
    }

    /* =========================
       Warehouse change
       ========================= */
    function loadLots(wh) {
        if (!wh) { showPickerState('Initial'); updateStepper(); return; }
        showPickerState('Loading');
        \$.get(getLotsUrl, { warehouse_id: wh }).done(function(data) {
            lotsData = data || [];
            if (lotsData.length === 0) { showPickerState('Empty'); updateStepper(); return; }
            showPickerState('Ready');
            renderRecentChips();
            $('#itemSearchInput').val('');
            updateStepper();
            if (isDesktop) $('#itemSearchInput').focus();
        }).fail(function() {
            showPickerState('Initial');
            notify('error', 'โหลดรายการพัสดุไม่สำเร็จ ลองอีกครั้ง');
        });
    }

    $('#warehouseSelect').on('change', function() {
        var wh = $(this).val();
        var \$sel = $(this);
        var doSwitch = function() {
            lastWarehouseValue = wh;
            isInitialWarehouseChange = false;
            updateStickyContext();
            if (rows.length === 0 && !isInitialWarehouseChange && wh && wh !== '__initial') {
                window.location.href = window.location.pathname + '?warehouse_id=' + wh;
                return;
            }
            loadLots(wh);
        };
        if (isInitialWarehouseChange) { isInitialWarehouseChange = false; lastWarehouseValue = wh; updateStickyContext(); loadLots(wh); return; }
        if (rows.length > 0) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'มีรายการในตะกร้า',
                    text: 'เปลี่ยนคลังจะล้างรายการที่เพิ่มไว้ ดำเนินการต่อ?',
                    showCancelButton: true,
                    confirmButtonText: 'ล้างและเปลี่ยนคลัง',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#dc3545'
                }).then(function(result) {
                    if (result.isConfirmed) { rows = []; renderCart(); doSwitch(); }
                    else { \$sel.val(lastWarehouseValue); }
                });
                return;
            } else if (!confirm('มีรายการในตะกร้า เปลี่ยนคลังจะล้างรายการ ดำเนินการต่อ?')) {
                \$sel.val(lastWarehouseValue);
                return;
            }
            rows = []; renderCart();
        }
        doSwitch();
    });

    // Initial load
    var initialWh = $('#warehouseSelect').val();
    if (initialWh) {
        lastWarehouseValue = initialWh;
        updateStickyContext();
        loadLots(initialWh);
        isInitialWarehouseChange = false;
    } else {
        showPickerState('Initial');
    }
    updateStepper();
    updateSummaryRecap();

    /* =========================
       Job type / reference
       ========================= */
    $('#jobType').on('change', function() {
        var labels = {
            patient: 'HN / ชื่อคนไข้',
            maintenance: 'เลขที่ใบแจ้งซ่อม (Job)',
            office: 'รหัสผู้เบิก/โครงการ',
            emergency: 'อ้างอิงการเบิก'
        };
        $('#dynamicLabel').text(labels[$(this).val()] || 'อ้างอิง');
        updateStickyContext();
    });
    $('#referenceInput').on('input', updateStickyContext);

    /* =========================
       Add to cart
       ========================= */
    $('#btnAddToList').on('click', function() {
        if (!selectedItem) { notify('warning', 'ค้นหาและเลือกพัสดุก่อน'); return; }
        var qty = parseFloat($('#inputQty').val()) || 0;
        if (qty <= 0) { notify('warning', 'จำนวนต้องมากกว่า 0'); return; }

        var balance = selectedItem.balance_qty || 0;
        var unit = selectedItem.unit || '';
        var unitText = unit ? ' ' + unit : '';
        var existingIndex = findCartIndex(selectedItem);
        var existingQty = existingIndex >= 0 ? (parseFloat(rows[existingIndex].qty) || 0) : 0;
        var totalQty = +(existingQty + qty).toFixed(2);

        if (totalQty > balance) {
            var msg = existingQty > 0
                ? 'รวมกับที่อยู่ในตะกร้าแล้ว (' + existingQty + unitText + ') จะเกินยอดคงเหลือ (มี ' + balance + unitText + ')'
                : 'จำนวนเกินยอดคงเหลือใน Lot นี้ (มี ' + balance + unitText + ')';
            notify('error', msg);
            return;
        }

        flyToCart(selectedItem);
        var targetIndex;
        if (existingIndex >= 0) {
            rows[existingIndex].qty = totalQty;
            targetIndex = existingIndex;
        } else {
            rows.push({
                item_code: selectedItem.item_code,
                item_name: selectedItem.item_name,
                lot_number: selectedItem.lot_number,
                unit: unit,
                qty: qty
            });
            targetIndex = rows.length - 1;
        }
        setTimeout(function() {
            renderCart({ markIndex: targetIndex });
        }, reducedMotion ? 0 : 280);
        clearSelected();
        $('#inputQty').val(1);
        updateBalanceHint();
    });

    /* =========================
       Keyboard shortcuts
       ========================= */
    document.addEventListener('keydown', function(e) {
        var tag = e.target.tagName;
        var inField = tag === 'INPUT' || tag === 'TEXTAREA' || tag === 'SELECT';
        if (e.key === '/' && !inField) {
            var searchInput = document.getElementById('itemSearchInput');
            if (searchInput && !searchInput.closest('.d-none')) {
                e.preventDefault();
                searchInput.focus();
            }
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

        var btns = $('#btnSaveFinal, #btnSaveFinalMobile').prop('disabled', true).addClass('is-saving');
        btns.find('.btn-save__label i').removeClass('bi-check2').addClass('bi-arrow-clockwise spin');

        \$.post(saveUrl, { warehouse_id: wh, job_type: jobType, reference: reference, items: items })
            .done(function(res) {
                if (res.success) {
                    btns.removeClass('is-saving').addClass('is-success')
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
                    btns.prop('disabled', false).removeClass('is-saving is-success')
                        .find('.btn-save__label i').removeClass('bi-arrow-clockwise spin').addClass('bi-check2');
                }
            })
            .fail(function() {
                notify('error', 'เชื่อมต่อ server ไม่สำเร็จ');
                btns.prop('disabled', false).removeClass('is-saving is-success')
                    .find('.btn-save__label i').removeClass('bi-arrow-clockwise spin').addClass('bi-check2');
            });
    }
    $('#btnSaveFinal, #btnSaveFinalMobile').on('click', doSave);
})();
JS,
        View::POS_READY
    );
    ?>
<?php endif; ?>

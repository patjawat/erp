<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \app\modules\inventoryV2\models\StockOrder $model */
/** @var bool $isCurrentUserApprover */

$saveDraftUrl = Url::to(['save-draft', 'id' => $model->id]);
$approveUrl = Url::to(['approve-with-edits', 'id' => $model->id]);
$getItemInWhUrl = Url::to(['/inventory-v2/stock-item/get-items-by-warehouse']);
$mainWhId = (int) $model->main_warehouse_id;

$rows = [];
foreach ($model->stockDetails as $detail) {
    $item = $detail->item;
    $balance = $item ? $item->getStockBalance($mainWhId) : 0;
    $rows[] = [
        'item_code' => (string) $detail->item_code,
        'item_name' => $item ? ($item->item_name ?? $detail->item_code) : $detail->item_code,
        'unit_name' => $item && method_exists($item, 'getUnitName') ? ($item->getUnitName() ?: '-') : '-',
        'qty' => (float) $detail->qty,
        'lot_number' => (string) ($detail->lot_number ?: '-'),
        'balance' => (float) $balance,
        'item_img' => $item && method_exists($item, 'ShowImg') ? (string) $item->ShowImg() : '',
    ];
}
$rowsJson = Html::encode(json_encode($rows, JSON_UNESCAPED_UNICODE));
?>

<div class="surface-card mb-3" id="approver-edit-panel" data-rows="<?= $rowsJson ?>" data-main-warehouse="<?= $mainWhId ?>">
    <div class="surface-card__head">
        <div class="surface-card__title">รายการขอเบิก — โหมดอนุมัติ</div>
        <div class="surface-card__caption text-muted small">
            หัวหน้าปรับจำนวน/ลบรายการได้ ก่อนกดอนุมัติ — ระบบจะบันทึกประวัติการปรับเพื่อตรวจสอบย้อนหลัง
        </div>
    </div>
    <div class="surface-card__body p-0">
        <table class="table align-middle mb-0" id="ae-table">
            <thead>
                <tr class="ae-thead">
                    <th class="ae-th-item">รายการวัสดุ</th>
                    <th class="ae-th-unit text-center">หน่วย</th>
                    <th class="ae-th-qty text-center">จำนวนที่ขอเบิก</th>
                    <th class="ae-th-balance text-end">ยอดคงเหลือคลังจ่าย</th>
                    <th class="ae-th-remove text-center"></th>
                </tr>
            </thead>
            <tbody id="ae-tbody"></tbody>
        </table>
        <div class="ae-empty d-none">
            <div class="picker-state">
                <div class="picker-state__icon"><i class="bi bi-inbox"></i></div>
                <div class="picker-state__title">ไม่มีรายการในใบนี้</div>
                <div class="picker-state__caption">เพิ่มวัสดุที่แถบด้านล่างก่อนอนุมัติ</div>
            </div>
        </div>

        <!-- inline add row — แทน modal -->
        <div class="ae-add-row">
            <div class="ae-add-row__field ae-add-row__field--item">
                <label class="ae-add-row__label">เพิ่มวัสดุ</label>
                <select id="ae-item-select" placeholder="พิมพ์ชื่อหรือรหัสวัสดุ..."></select>
            </div>
            <div class="ae-add-row__field ae-add-row__field--qty">
                <label class="ae-add-row__label">จำนวน</label>
                <div class="ae-stepper ae-stepper--add">
                    <button type="button" class="ae-stepper__btn" id="ae-add-dec" aria-label="ลด">−</button>
                    <input type="number" id="ae-item-qty" class="ae-stepper__input" min="0.01" step="0.01" value="1">
                    <button type="button" class="ae-stepper__btn" id="ae-add-inc" aria-label="เพิ่ม">+</button>
                </div>
            </div>
            <div class="ae-add-row__field ae-add-row__field--action">
                <button type="button" class="btn btn-light ae-add-row__btn" id="ae-add-confirm" disabled>
                    <i class="bi bi-plus-lg me-1"></i> เพิ่มเข้าใบ
                </button>
            </div>
        </div>
    </div>
    <div class="ae-actions">
        <button type="button" class="btn btn-light btn-block" id="ae-save-draft">
            <i class="bi bi-bookmark me-1"></i> บันทึก (ยังไม่อนุมัติ)
        </button>
        <button type="button" class="btn btn-primary btn-block btn-save" id="ae-save-approve">
            <span class="btn-save__label">
                <i class="bi bi-check-circle me-1"></i> บันทึก &amp; อนุมัติ
            </span>
            <span class="btn-save__progress" aria-hidden="true"></span>
        </button>
    </div>
</div>

<?php
\app\assets\TomSelectAsset::register($this);

$this->registerCss(<<<CSS
:root {
    --ae-ink-1: #1a202c;
    --ae-ink-2: #4a5568;
    --ae-ink-3: #718096;
    --ae-ink-4: #a0aec0;
    --ae-surface: #ffffff;
    --ae-surface-2: #f7f9fc;
    --ae-surface-3: #eef2f7;
    --ae-surface-hover: #f1f5f9;
    --ae-line: rgba(15, 23, 42, 0.08);
    --ae-line-strong: rgba(15, 23, 42, 0.14);
    --ae-primary: #0d6efd;
    --ae-primary-ink: #0a58ca;
    --ae-primary-soft: rgba(13, 110, 253, 0.08);
    --ae-success: #15803d;
    --ae-success-soft: rgba(21, 128, 61, 0.10);
    --ae-warning: #b45309;
    --ae-warning-soft: rgba(180, 83, 9, 0.10);
    --ae-radius: 10px;
    --ae-radius-sm: 8px;
    --ae-ease: cubic-bezier(0.16, 1, 0.3, 1);
    --ae-t-fast: 120ms;
    --ae-t-mid: 180ms;
    --ae-t-slow: 240ms;
}

#approver-edit-panel.surface-card {
    background: var(--ae-surface);
    border: 1px solid var(--ae-line);
    border-radius: var(--ae-radius);
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04), 0 1px 1px rgba(15, 23, 42, 0.03);
    overflow: hidden;
}
#approver-edit-panel .surface-card__head {
    padding: 0.85rem 1.1rem;
    border-bottom: 1px solid var(--ae-line);
    background: var(--ae-surface-2);
}
#approver-edit-panel .surface-card__title {
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--ae-ink-1);
}
#approver-edit-panel .surface-card__caption {
    color: var(--ae-ink-3);
    line-height: 1.3;
    margin-top: 0.15rem;
}

/* Table */
#ae-table { border-collapse: separate; border-spacing: 0; }
#ae-table thead.ae-thead, #ae-table tr.ae-thead { display: none; }
#ae-table thead tr.ae-thead { display: table-row; }
#ae-table thead th {
    font-weight: 600;
    font-size: 0.78rem;
    color: var(--ae-ink-2);
    background: var(--ae-surface-2);
    border-bottom: 1px solid var(--ae-line);
    padding: 0.55rem 0.75rem;
}
#ae-table .ae-th-item { width: 42%; }
#ae-table .ae-th-unit { width: 80px; }
#ae-table .ae-th-qty { width: 180px; }
#ae-table .ae-th-balance { width: 160px; }
#ae-table .ae-th-remove { width: 56px; }

#ae-table tbody tr {
    transition: background var(--ae-t-fast) var(--ae-ease);
}
#ae-table tbody tr:hover { background: var(--ae-surface-2); }
#ae-table tbody td {
    padding: 0.65rem 0.75rem;
    border-bottom: 1px solid var(--ae-line);
    vertical-align: middle;
}
#ae-table tbody tr.ae-row-enter td {
    animation: ae-row-enter var(--ae-t-slow) var(--ae-ease) both;
}
#ae-table tbody tr.ae-row-removing td {
    animation: ae-row-exit var(--ae-t-mid) var(--ae-ease) both;
}
@keyframes ae-row-enter {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: none; }
}
@keyframes ae-row-exit {
    from { opacity: 1; }
    to { opacity: 0; transform: translateX(8px); }
}

.ae-item-cell { display: flex; align-items: center; gap: 0.6rem; min-width: 0; }
.ae-item-thumb {
    width: 36px; height: 36px; object-fit: cover;
    border-radius: 6px; border: 1px solid var(--ae-line); flex-shrink: 0;
    background: var(--ae-surface-3);
}
.ae-item-name { font-weight: 600; color: var(--ae-ink-1); line-height: 1.2; }
.ae-item-code { font-size: 0.72rem; color: var(--ae-ink-3); font-family: ui-monospace, monospace; }
.ae-unit { font-size: 0.8rem; color: var(--ae-ink-3); }
.ae-balance { font-variant-numeric: tabular-nums; color: var(--ae-ink-2); }
.ae-balance.is-low { color: var(--ae-warning); font-weight: 600; }
.ae-balance.is-zero { color: var(--ae-warning); font-weight: 600; }

/* qty stepper */
.ae-stepper {
    display: inline-flex; align-items: stretch;
    border: 1px solid var(--ae-line-strong);
    border-radius: var(--ae-radius-sm);
    overflow: hidden;
    height: 38px;
    background: var(--ae-surface);
    transition: border-color var(--ae-t-fast) var(--ae-ease), box-shadow var(--ae-t-fast) var(--ae-ease);
}
.ae-stepper:focus-within {
    border-color: var(--ae-primary);
    box-shadow: 0 0 0 3px var(--ae-primary-soft);
}
.ae-stepper__btn {
    border: 0; background: transparent; width: 34px;
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--ae-ink-2); cursor: pointer;
    transition: background var(--ae-t-fast) var(--ae-ease), color var(--ae-t-fast) var(--ae-ease);
}
.ae-stepper__btn:hover { background: var(--ae-surface-hover); color: var(--ae-ink-1); }
.ae-stepper__btn:active { transform: translateY(1px); }
.ae-stepper__btn:disabled { opacity: 0.4; cursor: not-allowed; }
.ae-stepper__input {
    border: 0; outline: none; text-align: center; width: 72px;
    font-variant-numeric: tabular-nums;
    font-weight: 600; color: var(--ae-ink-1);
    background: transparent;
}
.ae-stepper__input::-webkit-outer-spin-button,
.ae-stepper__input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; }
.ae-stepper__input { -moz-appearance: textfield; }

.ae-hint {
    display: block; font-size: 0.72rem; color: var(--ae-ink-3);
    margin-top: 0.2rem;
}
.ae-hint.is-warn { color: var(--ae-warning); }
.ae-hint.is-ok { color: var(--ae-success); }

/* remove btn */
.ae-icon-btn {
    width: 32px; height: 32px;
    display: inline-flex; align-items: center; justify-content: center;
    border: 0; background: transparent; color: var(--ae-ink-4);
    border-radius: 6px;
    transition: background var(--ae-t-fast) var(--ae-ease), color var(--ae-t-fast) var(--ae-ease);
    cursor: pointer;
}
.ae-icon-btn:hover { background: var(--ae-surface-hover); color: #b91c1c; }
.ae-icon-btn:active { transform: translateY(1px); }

/* empty state */
.ae-empty .picker-state {
    padding: 2rem 1rem; text-align: center;
}
.ae-empty .picker-state__icon {
    width: 56px; height: 56px; border-radius: 14px;
    background: var(--ae-surface-3);
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--ae-ink-4); font-size: 1.4rem;
    margin-bottom: 0.8rem;
}
.ae-empty .picker-state__title { font-weight: 600; color: var(--ae-ink-1); }
.ae-empty .picker-state__caption { color: var(--ae-ink-3); font-size: 0.85rem; margin-top: 0.2rem; }

/* action bar */
.ae-actions {
    display: flex; gap: 0.6rem;
    padding: 0.85rem 1.1rem;
    border-top: 1px solid var(--ae-line);
    background: var(--ae-surface-2);
    justify-content: flex-end;
    flex-wrap: wrap;
}
.ae-actions .btn-block { min-width: 200px; min-height: 44px; font-weight: 600; }
@media (max-width: 575px) {
    .ae-actions { flex-direction: column-reverse; }
    .ae-actions .btn-block { min-width: 0; width: 100%; }
}

/* save button with progress */
#ae-save-approve.btn-save { position: relative; overflow: hidden; }
#ae-save-approve.btn-save .btn-save__progress {
    position: absolute; left: 0; bottom: 0; height: 3px;
    width: 0; background: rgba(255, 255, 255, 0.75);
    transition: width var(--ae-t-slow) linear;
}
#ae-save-approve.is-saving .btn-save__progress { width: 100%; transition-duration: 600ms; }
#ae-save-approve.is-saved {
    background: var(--ae-success); border-color: var(--ae-success);
}

/* inline add-row (แทน modal) */
.ae-add-row {
    display: grid;
    grid-template-columns: 1fr 180px auto;
    gap: 0.75rem;
    align-items: end;
    padding: 0.85rem 1.1rem;
    background: var(--ae-surface-2);
    border-top: 1px solid var(--ae-line);
}
@media (max-width: 767px) {
    .ae-add-row { grid-template-columns: 1fr; }
    .ae-add-row__field--action { display: flex; }
    .ae-add-row__btn { width: 100%; }
}
.ae-add-row__label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: var(--ae-ink-2);
    margin-bottom: 0.3rem;
}
.ae-add-row__field--item .ts-wrapper { width: 100%; }
.ae-add-row__field--item .ts-control {
    min-height: 38px;
    border: 1px solid var(--ae-line-strong) !important;
    border-radius: var(--ae-radius-sm) !important;
    background: var(--ae-surface) !important;
    transition: border-color var(--ae-t-fast) var(--ae-ease), box-shadow var(--ae-t-fast) var(--ae-ease);
}
.ae-add-row__field--item .ts-wrapper.focus .ts-control {
    border-color: var(--ae-primary) !important;
    box-shadow: 0 0 0 3px var(--ae-primary-soft);
}
.ae-stepper--add { width: 100%; justify-content: space-between; }
.ae-stepper--add .ae-stepper__input { flex: 1; min-width: 0; }
.ae-add-row__btn {
    min-height: 38px;
    font-weight: 600;
    padding: 0 1rem;
    border-color: var(--ae-line-strong);
    background: var(--ae-surface);
    color: var(--ae-ink-2);
    transition: background var(--ae-t-fast) var(--ae-ease), color var(--ae-t-fast) var(--ae-ease), border-color var(--ae-t-fast) var(--ae-ease);
}
.ae-add-row__btn:not(:disabled):hover {
    background: var(--ae-primary);
    border-color: var(--ae-primary);
    color: #fff;
}
.ae-add-row__btn:disabled {
    opacity: 0.55;
    cursor: not-allowed;
}

/* TomSelect dropdown */
.ts-dropdown.ae-item-dropdown {
    z-index: 1080 !important;
    border: 1px solid var(--ae-line-strong) !important;
    border-radius: var(--ae-radius-sm) !important;
    box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06), 0 2px 4px rgba(15, 23, 42, 0.04) !important;
    background: var(--ae-surface) !important;
}
.ae-option-thumb {
    width: 28px; height: 28px; object-fit: cover; border-radius: 4px;
    border: 1px solid var(--ae-line); flex-shrink: 0; background: var(--ae-surface-3);
    margin-right: 0.4rem;
}
.ts-dropdown.ae-item-dropdown .ae-ts-hint {
    padding: 0.55rem 0.75rem;
    font-size: 0.78rem;
    color: var(--ae-ink-3);
}

/* option row inside dropdown */
.ts-dropdown.ae-item-dropdown .option { padding: 0; }
.ts-dropdown.ae-item-dropdown .ae-opt {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    padding: 0.45rem 0.7rem;
    min-width: 0;
}
.ae-opt__main {
    flex: 1 1 auto;
    min-width: 0;
}
.ae-opt__name {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--ae-ink-1);
    line-height: 1.25;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.ae-opt__code {
    font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
    font-size: 0.7rem;
    color: var(--ae-ink-3);
    line-height: 1.2;
    margin-top: 0.1rem;
}
.ae-opt__bal-wrap {
    flex: 0 0 auto;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    text-align: right;
    padding-left: 0.5rem;
    border-left: 1px solid var(--ae-line);
    margin-left: 0.25rem;
}
.ae-opt__bal-label {
    font-size: 0.65rem;
    color: var(--ae-ink-3);
    font-weight: 500;
    letter-spacing: 0.01em;
    line-height: 1;
    margin-bottom: 0.2rem;
}
.ae-opt-bal {
    font-variant-numeric: tabular-nums;
    font-size: 0.82rem;
    font-weight: 600;
    color: var(--ae-ink-1);
    line-height: 1;
}
.ae-opt-bal.is-empty {
    color: var(--ae-warning);
    font-size: 0.72rem;
    font-weight: 600;
}
/* TomSelect active row: keep search results readable even when theme background stays light. */
.ts-dropdown.ae-item-dropdown .option.active {
    background: var(--ae-primary-soft) !important;
}
.ts-dropdown.ae-item-dropdown .option.active .ae-opt__name,
.ts-dropdown.ae-item-dropdown .option.active .ae-opt-bal {
    color: var(--ae-primary-ink);
}
.ts-dropdown.ae-item-dropdown .option.active .ae-opt__code,
.ts-dropdown.ae-item-dropdown .option.active .ae-opt__bal-label {
    color: var(--ae-ink-2);
}
.ts-dropdown.ae-item-dropdown .option.active .ae-opt__bal-wrap {
    border-left-color: var(--ae-primary-soft);
}
.ts-dropdown.ae-item-dropdown .option.active .ae-opt-bal.is-empty {
    color: var(--ae-warning);
}

@media (prefers-reduced-motion: reduce) {
    #ae-table tbody tr.ae-row-enter td,
    #ae-table tbody tr.ae-row-removing td {
        animation: none;
        transition: opacity 80ms linear;
    }
    .ae-stepper, .ae-stepper__btn, .ae-icon-btn, #ae-save-approve.btn-save .btn-save__progress {
        transition-duration: 80ms !important;
    }
}

/* SweetAlert action row — บังคับลำดับ [ยืนยัน] [ยกเลิก] (project SweetAlert ไม่เคารพ reverseButtons:true) */
.swal2-actions.ae-swal-actions {
    flex-direction: row-reverse !important;
    justify-content: center;
}
CSS
);

$script = <<<JS
(function () {
    var panel = document.getElementById('approver-edit-panel');
    if (!panel) return;
    var tbody = document.getElementById('ae-tbody');
    var emptyEl = panel.querySelector('.ae-empty');
    var saveDraftBtn = document.getElementById('ae-save-draft');
    var saveApproveBtn = document.getElementById('ae-save-approve');
    var addConfirmBtn = document.getElementById('ae-add-confirm');
    var addQtyInput = document.getElementById('ae-item-qty');
    var addDecBtn = document.getElementById('ae-add-dec');
    var addIncBtn = document.getElementById('ae-add-inc');
    var mainWhId = panel.getAttribute('data-main-warehouse');

    var initialRows = JSON.parse(panel.getAttribute('data-rows') || '[]');
    var rowsState = initialRows.map(function (r) { return Object.assign({}, r); });

    function fmt(n) {
        var v = parseFloat(n);
        if (isNaN(v)) return '0.00';
        return v.toFixed(2);
    }
    function escapeAttr(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function renderRow(row, index, isNew) {
        var tr = document.createElement('tr');
        tr.dataset.index = index;
        if (isNew) tr.classList.add('ae-row-enter');
        var imgHtml = row.item_img
            ? '<img src="' + escapeAttr(row.item_img) + '" alt="" class="ae-item-thumb" onerror="this.style.display=\\'none\\'">'
            : '';
        var balanceCls = '';
        if (row.balance <= 0) balanceCls = 'is-zero';
        else if (row.balance < row.qty) balanceCls = 'is-low';
        var balanceText = fmt(row.balance);
        if (row.balance <= 0) balanceText += ' (ไม่มีในคลัง)';
        else if (row.balance < row.qty) balanceText += ' (ไม่พอจ่าย)';

        tr.innerHTML = ''
            + '<td>'
            +   '<div class="ae-item-cell">'
            +     imgHtml
            +     '<div class="min-w-0">'
            +       '<div class="ae-item-name">' + escapeAttr(row.item_name) + '</div>'
            +       '<div class="ae-item-code">' + escapeAttr(row.item_code) + '</div>'
            +     '</div>'
            +   '</div>'
            + '</td>'
            + '<td class="text-center ae-unit">' + escapeAttr(row.unit_name || '-') + '</td>'
            + '<td class="text-center">'
            +   '<div class="ae-stepper" role="group" aria-label="ปรับจำนวน">'
            +     '<button type="button" class="ae-stepper__btn ae-dec" aria-label="ลดจำนวน">−</button>'
            +     '<input type="number" class="ae-stepper__input ae-qty" min="0.01" step="0.01" value="' + fmt(row.qty) + '">'
            +     '<button type="button" class="ae-stepper__btn ae-inc" aria-label="เพิ่มจำนวน">+</button>'
            +   '</div>'
            +   '<span class="ae-hint"></span>'
            + '</td>'
            + '<td class="text-end ae-balance ' + balanceCls + '">' + balanceText + '</td>'
            + '<td class="text-center">'
            +   '<button type="button" class="ae-icon-btn ae-remove" aria-label="ลบแถว"><i class="bi bi-trash"></i></button>'
            + '</td>';
        return tr;
    }

    function updateHint(tr, row) {
        var hint = tr.querySelector('.ae-hint');
        if (!hint) return;
        if (row.qty <= 0) {
            hint.textContent = 'จำนวนต้องมากกว่า 0';
            hint.className = 'ae-hint is-warn';
        } else if (row.balance > 0 && row.qty > row.balance) {
            hint.textContent = 'มากกว่ายอดคงเหลือ';
            hint.className = 'ae-hint is-warn';
        } else {
            hint.textContent = '';
            hint.className = 'ae-hint';
        }
    }

    function updateBalanceCell(tr, row) {
        var cell = tr.querySelector('.ae-balance');
        if (!cell) return;
        cell.classList.remove('is-low', 'is-zero');
        var text = fmt(row.balance);
        if (row.balance <= 0) {
            cell.classList.add('is-zero');
            text += ' (ไม่มีในคลัง)';
        } else if (row.balance < row.qty) {
            cell.classList.add('is-low');
            text += ' (ไม่พอจ่าย)';
        }
        cell.textContent = text;
    }

    function render() {
        tbody.innerHTML = '';
        rowsState.forEach(function (r, idx) {
            var tr = renderRow(r, idx, false);
            tbody.appendChild(tr);
            updateHint(tr, r);
        });
        emptyEl.classList.toggle('d-none', rowsState.length > 0);
        updateSubmitState();
    }

    function updateSubmitState() {
        var anyInvalid = rowsState.some(function (r) { return !(r.qty > 0); });
        var hasAny = rowsState.length > 0;
        saveDraftBtn.disabled = !hasAny || anyInvalid;
        saveApproveBtn.disabled = !hasAny || anyInvalid;
    }

    tbody.addEventListener('click', function (e) {
        var tr = e.target.closest('tr');
        if (!tr) return;
        var idx = parseInt(tr.dataset.index, 10);
        var row = rowsState[idx];
        if (!row) return;

        if (e.target.closest('.ae-dec')) {
            row.qty = Math.max(0, Math.round((row.qty - 1) * 100) / 100);
            tr.querySelector('.ae-qty').value = fmt(row.qty);
            updateHint(tr, row); updateBalanceCell(tr, row); updateSubmitState();
        } else if (e.target.closest('.ae-inc')) {
            row.qty = Math.round((row.qty + 1) * 100) / 100;
            tr.querySelector('.ae-qty').value = fmt(row.qty);
            updateHint(tr, row); updateBalanceCell(tr, row); updateSubmitState();
        } else if (e.target.closest('.ae-remove')) {
            tr.classList.add('ae-row-removing');
            setTimeout(function () {
                rowsState.splice(idx, 1);
                render();
            }, 180);
        }
    });

    tbody.addEventListener('input', function (e) {
        if (!e.target.classList.contains('ae-qty')) return;
        var tr = e.target.closest('tr');
        var idx = parseInt(tr.dataset.index, 10);
        var row = rowsState[idx];
        var v = parseFloat(e.target.value);
        row.qty = isNaN(v) ? 0 : v;
        updateHint(tr, row); updateBalanceCell(tr, row); updateSubmitState();
    });

    // ---------- Inline add row ----------
    var ADD_MIN_QUERY = 2;
    var addPicker = new TomSelect('#ae-item-select', {
        valueField: 'item_code',
        labelField: 'item_name',
        searchField: ['item_name', 'item_code'],
        placeholder: 'พิมพ์ ' + ADD_MIN_QUERY + ' ตัวอักษรขึ้นไปเพื่อค้นหา...',
        maxItems: 1,
        preload: false,
        loadThrottle: 350,
        shouldLoad: function (query) {
            return (query || '').trim().length >= ADD_MIN_QUERY;
        },
        onDropdownOpen: function () {
            if (this.dropdown) this.dropdown.classList.add('ae-item-dropdown');
        },
        load: function (query, callback) {
            if (!mainWhId) return callback();
            var q = (query || '').replace(/^["'\s]+|["'\s]+$/g, '');
            if (q.length < ADD_MIN_QUERY) return callback();
            var existing = rowsState.map(function (r) { return r.item_code; });
            var url = '{$getItemInWhUrl}?warehouse_id=' + mainWhId + '&q=' + encodeURIComponent(q);
            fetch(url)
                .then(function (r) { return r.ok ? r.json() : []; })
                .then(function (json) {
                    var list = Array.isArray(json) ? json : (json && (json.results || json.data)) || [];
                    list = list.filter(function (it) { return it && it.item_code && existing.indexOf(it.item_code) === -1; });
                    callback(list);
                })
                .catch(function () { callback([]); });
        },
        render: {
            option: function (item, escape) {
                var img = item.item_img
                    ? '<img src="' + escape(item.item_img) + '" alt="" class="ae-option-thumb" onerror="this.style.display=\\'none\\'">'
                    : '';
                var balance = parseFloat(item.balance_qty || 0);
                var unit = item.unit_name && item.unit_name !== '-' ? item.unit_name : '';
                var balanceCls = 'ae-opt-bal';
                var balanceLabel;
                if (balance <= 0) {
                    balanceCls += ' is-empty';
                    balanceLabel = 'ไม่มีในคลัง';
                } else {
                    balanceLabel = balance.toFixed(2) + (unit ? ' ' + escape(unit) : '');
                }
                return ''
                    + '<div class="ae-opt">'
                    +   img
                    +   '<div class="ae-opt__main">'
                    +     '<div class="ae-opt__name">' + escape(item.item_name) + '</div>'
                    +     '<div class="ae-opt__code">' + escape(item.item_code) + '</div>'
                    +   '</div>'
                    +   '<div class="ae-opt__bal-wrap">'
                    +     '<span class="ae-opt__bal-label">คงเหลือ</span>'
                    +     '<span class="' + balanceCls + '">' + balanceLabel + '</span>'
                    +   '</div>'
                    + '</div>';
            },
            not_loading: function (data, escape) {
                return '<div class="ae-ts-hint">พิมพ์อย่างน้อย ' + ADD_MIN_QUERY + ' ตัวอักษรเพื่อค้นหา</div>';
            },
            no_results: function (data, escape) {
                return '<div class="ae-ts-hint">ไม่พบ "' + escape(data.input) + '" ในคลังที่จ่ายของ</div>';
            }
        },
        onChange: function () { syncAddButtonState(); }
    });

    function getAddQty() {
        var v = parseFloat(addQtyInput.value);
        return isNaN(v) ? 0 : v;
    }
    function syncAddButtonState() {
        var hasItem = !!addPicker.getValue();
        var qty = getAddQty();
        addConfirmBtn.disabled = !hasItem || !(qty > 0);
    }

    addDecBtn.addEventListener('click', function () {
        var q = Math.max(0, Math.round((getAddQty() - 1) * 100) / 100);
        addQtyInput.value = q.toFixed(2);
        syncAddButtonState();
    });
    addIncBtn.addEventListener('click', function () {
        var q = Math.round((getAddQty() + 1) * 100) / 100;
        addQtyInput.value = q.toFixed(2);
        syncAddButtonState();
    });
    addQtyInput.addEventListener('input', syncAddButtonState);
    addQtyInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); addConfirmBtn.click(); }
    });

    addConfirmBtn.addEventListener('click', function () {
        var code = addPicker.getValue();
        var qty = getAddQty();
        if (!code || !(qty > 0)) return;

        var data = addPicker.options[code] || {};
        var existingIdx = rowsState.findIndex(function (r) { return r.item_code === code; });
        if (existingIdx >= 0) {
            rowsState[existingIdx].qty += qty;
        } else {
            rowsState.push({
                item_code: code,
                item_name: data.item_name || code,
                unit_name: data.unit_name || '-',
                qty: qty,
                lot_number: '-',
                balance: parseFloat(data.balance_qty || 0),
                item_img: data.item_img || ''
            });
        }
        // rerender, animate the new/updated row
        tbody.innerHTML = '';
        var targetIdx = existingIdx >= 0 ? existingIdx : rowsState.length - 1;
        rowsState.forEach(function (r, idx) {
            var tr = renderRow(r, idx, idx === targetIdx);
            tbody.appendChild(tr);
            updateHint(tr, r);
        });
        emptyEl.classList.toggle('d-none', rowsState.length > 0);
        updateSubmitState();

        // reset picker + qty, refocus picker for rapid add
        addPicker.clear();
        addPicker.clearOptions();
        addQtyInput.value = '1';
        syncAddButtonState();
        setTimeout(function () { addPicker.focus(); }, 50);
    });

    syncAddButtonState();

    // ---------- Submit ----------
    function buildPayload() {
        return rowsState
            .filter(function (r) { return r.qty > 0 && r.item_code; })
            .map(function (r) {
                return { item_code: r.item_code, qty: r.qty, lot_number: r.lot_number || '-' };
            });
    }

    function submit(url, btn, withProgress) {
        var items = buildPayload();
        if (items.length === 0) {
            Swal.fire('คำเตือน', 'ต้องมีรายการอย่างน้อย 1 รายการ', 'warning');
            return;
        }
        btn.disabled = true;
        if (withProgress) btn.classList.add('is-saving');

        var form = new FormData();
        items.forEach(function (it, i) {
            form.append('items[' + i + '][item_code]', it.item_code);
            form.append('items[' + i + '][qty]', it.qty);
            form.append('items[' + i + '][lot_number]', it.lot_number);
        });
        var csrfParam = document.querySelector('meta[name=csrf-param]');
        var csrfToken = document.querySelector('meta[name=csrf-token]');
        if (csrfParam && csrfToken) {
            form.append(csrfParam.getAttribute('content'), csrfToken.getAttribute('content'));
        }

        fetch(url, { method: 'POST', body: form, credentials: 'same-origin', headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.success) {
                    if (withProgress) {
                        btn.classList.add('is-saved');
                        btn.querySelector('.btn-save__label').innerHTML = '<i class="bi bi-check-lg me-1"></i> เสร็จสิ้น';
                    }
                    // อยู่หน้านี้ต่อ — reload หน้าปัจจุบันเพื่อให้เห็นข้อมูล + status + revision ที่อัปเดต
                    setTimeout(function () { window.location.reload(); }, withProgress ? 350 : 0);
                } else {
                    btn.disabled = false;
                    btn.classList.remove('is-saving');
                    Swal.fire('ผิดพลาด', (res && res.message) || 'บันทึกไม่สำเร็จ', 'error');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.classList.remove('is-saving');
                Swal.fire('ผิดพลาด', 'เชื่อมต่อระบบไม่ได้', 'error');
            });
    }

    saveDraftBtn.addEventListener('click', function () {
        submit('{$saveDraftUrl}', saveDraftBtn, false);
    });
    saveApproveBtn.addEventListener('click', function () {
        Swal.fire({
            title: 'ยืนยันบันทึก & อนุมัติ?',
            text: 'ระบบจะเก็บประวัติการปรับ และเปลี่ยนสถานะเป็น "อนุมัติแล้ว" — คลังจะดำเนินการจ่ายต่อ',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true,
            customClass: { actions: 'ae-swal-actions' }
        }).then(function (r) {
            if (r.isConfirmed) submit('{$approveUrl}', saveApproveBtn, true);
        });
    });

    render();
})();
JS;
$this->registerJs($script);
?>

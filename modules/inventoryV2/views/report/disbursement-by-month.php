<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ประวัติจ่ายวัสดุ × เดือน';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$monthLabels = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
$monthFull = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
$hasRows = !empty($rows);
$queryParams = [
    'year' => $year,
    'main_warehouse_id' => $mainWarehouseId,
    'sub_warehouse_id' => $subWarehouseId,
    'category_id' => $categoryId,
    'q' => $search,
];
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-calendar3-week fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">วัสดุที่จ่ายออกตามคลังปลายทาง แยกตามเดือนของปีงบประมาณ</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= Html::a('<i class="bi bi-arrow-left me-1"></i> กลับ', ['/inventory-v2/main-stock/dashboard'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4 disbursement-report">
    <!-- Filter card -->
    <div class="filter-card">
        <form method="get" action="<?= Url::to(['/inventory-v2/report/disbursement-by-month']) ?>" id="filter-form">
            <div class="filter-grid">
                <div class="filter-field">
                    <label class="filter-label" for="f-year">ปี (พ.ศ.)</label>
                    <select name="year" id="f-year" class="filter-input">
                        <?php foreach ($yearOptions as $y): ?>
                            <option value="<?= (int)$y ?>" <?= (int)$y === (int)$year ? 'selected' : '' ?>><?= (int)$y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-field">
                    <label class="filter-label" for="f-main">คลังต้นทาง</label>
                    <select name="main_warehouse_id" id="f-main" class="filter-input">
                        <?php foreach ($mainWarehouses as $id => $name): ?>
                            <option value="<?= $id === '' ? '' : (int)$id ?>" <?= (string)$mainWarehouseId === (string)$id ? 'selected' : '' ?>>
                                <?= Html::encode($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-field">
                    <label class="filter-label" for="f-sub">คลังปลายทาง</label>
                    <select name="sub_warehouse_id" id="f-sub" class="filter-input">
                        <?php foreach ($subWarehouses as $id => $name): ?>
                            <option value="<?= $id === '' ? '' : (int)$id ?>" <?= (string)$subWarehouseId === (string)$id ? 'selected' : '' ?>>
                                <?= Html::encode($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-field">
                    <label class="filter-label" for="f-cat">ประเภทวัสดุ</label>
                    <select name="category_id" id="f-cat" class="filter-input">
                        <?php foreach ($categories as $code => $title): ?>
                            <option value="<?= Html::encode($code) ?>" <?= (string)$categoryId === (string)$code ? 'selected' : '' ?>>
                                <?= Html::encode($title) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-field filter-field--search">
                    <label class="filter-label" for="f-q">ค้นหา (รหัส / ชื่อวัสดุ)</label>
                    <div class="filter-search-wrap">
                        <i class="bi bi-search filter-search-icon"></i>
                        <input type="search" name="q" id="f-q" class="filter-input filter-input--search" value="<?= Html::encode($search) ?>" placeholder="เช่น กระดาษ A4">
                    </div>
                </div>

                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary px-3">
                        <i class="bi bi-funnel me-1"></i> ใช้ตัวกรอง
                    </button>
                    <?php if ($hasRows): ?>
                        <a href="<?= Url::to(array_merge(['/inventory-v2/report/export-disbursement-by-month'], $queryParams)) ?>" class="btn btn-success px-3">
                            <i class="bi bi-file-earmark-excel me-1"></i> Excel
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- Summary chips -->
    <?php if ($hasRows): ?>
        <div class="report-summary">
            <div class="report-summary__chip">
                <span class="report-summary__label">รายการ</span>
                <span class="report-summary__value"><?= number_format(count($rows)) ?></span>
                <span class="report-summary__unit">รายการ</span>
            </div>
            <div class="report-summary__chip">
                <span class="report-summary__label">รวมจำนวนจ่าย</span>
                <span class="report-summary__value"><?= number_format($grandQty, 2) ?></span>
            </div>
            <div class="report-summary__chip report-summary__chip--accent">
                <span class="report-summary__label">รวมมูลค่า</span>
                <span class="report-summary__value"><?= number_format($grandValue, 2) ?></span>
                <span class="report-summary__unit">บาท</span>
            </div>
        </div>
    <?php endif; ?>

    <!-- Pivot table -->
    <?php if (!$hasRows): ?>
        <div class="empty-block">
            <div class="empty-block__icon"><i class="bi bi-inbox"></i></div>
            <p class="empty-block__title">ไม่มีข้อมูลในช่วงที่เลือก</p>
            <p class="empty-block__caption">ลองเปลี่ยนปี คลังปลายทาง หรือคำค้น</p>
        </div>
    <?php else: ?>
        <div class="pivot-card">
            <div class="pivot-table-wrap">
                <table class="pivot-table">
                    <thead>
                        <tr>
                            <th rowspan="2" class="pivot-th--sticky" style="width: 3rem;">#</th>
                            <th rowspan="2" class="pivot-th--sticky pivot-th--name">รายการวัสดุ</th>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <th colspan="2" class="pivot-th--month" data-month="<?= $m ?>"><?= $monthLabels[$m - 1] ?></th>
                            <?php endfor; ?>
                            <th colspan="2" class="pivot-th--total">รวม</th>
                        </tr>
                        <tr>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <th class="pivot-th--sub">จำนวน</th>
                                <th class="pivot-th--sub pivot-th--sub-val">บาท</th>
                            <?php endfor; ?>
                            <th class="pivot-th--sub">จำนวน</th>
                            <th class="pivot-th--sub pivot-th--sub-val">บาท</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $i => $r): ?>
                            <tr>
                                <td class="pivot-td--sticky text-center text-muted"><?= $i + 1 ?></td>
                                <td class="pivot-td--sticky pivot-td--name">
                                    <div class="pivot-item-name"><?= Html::encode($r['item_name']) ?></div>
                                    <div class="pivot-item-meta">
                                        <span class="pivot-item-code"><?= Html::encode($r['item_code']) ?></span>
                                        <?php if (!empty($r['category_title'])): ?>
                                            <span class="pivot-item-cat"><?= Html::encode($r['category_title']) ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($r['unit_name'])): ?>
                                            <span class="pivot-item-unit"><?= Html::encode($r['unit_name']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <?php for ($m = 1; $m <= 12; $m++):
                                    $cell = $r['monthly'][$m];
                                    $hasData = $cell['qty'] > 0 || $cell['value'] > 0;
                                ?>
                                    <td class="pivot-td--qty<?= $hasData ? ' is-clickable' : ' is-empty' ?>"
                                        <?= $hasData ? 'data-drill="1" data-item="' . Html::encode($r['item_code']) . '" data-month="' . $m . '" tabindex="0" role="button" aria-label="ดูรายการเอกสาร ' . Html::encode($r['item_name']) . ' เดือน ' . $monthFull[$m - 1] . '"' : '' ?>>
                                        <?= $hasData ? number_format($cell['qty'], 2) : '—' ?>
                                    </td>
                                    <td class="pivot-td--val<?= $hasData ? ' is-clickable' : ' is-empty' ?>"
                                        <?= $hasData ? 'data-drill="1" data-item="' . Html::encode($r['item_code']) . '" data-month="' . $m . '" tabindex="0" role="button"' : '' ?>>
                                        <?= $hasData ? number_format($cell['value'], 2) : '' ?>
                                    </td>
                                <?php endfor; ?>
                                <td class="pivot-td--qty pivot-td--total"><?= number_format($r['total_qty'], 2) ?></td>
                                <td class="pivot-td--val pivot-td--total"><?= number_format($r['total_value'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="pivot-tfoot">
                            <td class="pivot-td--sticky text-center"></td>
                            <td class="pivot-td--sticky pivot-td--name">รวมทั้งหมด</td>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <td class="pivot-td--qty"><?= $monthTotals[$m]['qty'] > 0 ? number_format($monthTotals[$m]['qty'], 2) : '—' ?></td>
                                <td class="pivot-td--val"><?= $monthTotals[$m]['value'] > 0 ? number_format($monthTotals[$m]['value'], 2) : '' ?></td>
                            <?php endfor; ?>
                            <td class="pivot-td--qty pivot-td--total"><?= number_format($grandQty, 2) ?></td>
                            <td class="pivot-td--val pivot-td--total"><?= number_format($grandValue, 2) ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <p class="pivot-hint">
                <i class="bi bi-info-circle"></i>
                คลิกที่ตัวเลขในเซลล์เพื่อดูรายการเอกสารใบเบิกของเดือนนั้น มูลค่าคำนวณจาก lot ที่จ่ายจริง
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- Drill-down modal -->
<div class="modal fade" id="drillModal" tabindex="-1" aria-labelledby="drillModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content drill-modal">
            <div class="modal-header">
                <h5 class="modal-title" id="drillModalTitle">รายการเอกสารใบเบิก</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body" id="drillModalBody">
                <div class="drill-loading">
                    <div class="spinner-border spinner-border-sm text-primary"></div>
                    <span class="ms-2 text-muted small">กำลังโหลด...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.disbursement-report {
    --ink-1: #1a202c; --ink-2: #4a5568; --ink-3: #718096; --ink-4: #a0aec0;
    --surface: #ffffff; --surface-2: #f7f9fc; --surface-3: #eef2f7; --surface-hover: #f1f5f9;
    --line: rgba(15, 23, 42, 0.08); --line-strong: rgba(15, 23, 42, 0.14);
    --primary: #0d6efd; --primary-ink: #0a58ca; --primary-soft: rgba(13, 110, 253, 0.08); --primary-line: rgba(13, 110, 253, 0.22);
    --success: #15803d; --success-soft: rgba(21, 128, 61, 0.10);
    --warning: #b45309; --danger: #b91c1c;
    --radius: 10px; --radius-sm: 8px; --radius-xs: 6px;
    --shadow-1: 0 1px 2px rgba(15,23,42,0.04), 0 1px 1px rgba(15,23,42,0.03);
    --shadow-2: 0 6px 18px rgba(15,23,42,0.06), 0 2px 4px rgba(15,23,42,0.04);
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    --t-fast: 120ms; --t-mid: 180ms;
    color: var(--ink-1);
}

/* ─── Filter card ─── */
.disbursement-report .filter-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    padding: 0.9rem 1.1rem;
    margin-bottom: 1rem;
}
.disbursement-report .filter-grid {
    display: grid;
    grid-template-columns: 110px minmax(160px, 1fr) minmax(160px, 1fr) minmax(140px, 1fr) minmax(220px, 1.5fr) auto;
    gap: 0.75rem 1rem;
    align-items: end;
}
.disbursement-report .filter-field { display: flex; flex-direction: column; gap: 0.35rem; min-width: 0; }
.disbursement-report .filter-field--search { min-width: 0; }
.disbursement-report .filter-label {
    font-size: 0.75rem; font-weight: 600; color: var(--ink-3); line-height: 1;
}
.disbursement-report .filter-input {
    appearance: none;
    background: var(--surface);
    border: 1px solid var(--line-strong);
    border-radius: var(--radius-sm);
    padding: 0.4rem 0.7rem;
    min-height: 38px; width: 100%;
    font-size: 0.88rem; color: var(--ink-1);
    transition: border-color var(--t-fast) var(--ease), box-shadow var(--t-fast) var(--ease);
}
.disbursement-report select.filter-input {
    background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 16 16' fill='%234a5568'><path d='M3.205 5.795l4.795 4.795 4.795-4.795-1.41-1.41-3.385 3.385-3.385-3.385z'/></svg>");
    background-repeat: no-repeat; background-position: right 0.6rem center; background-size: 0.7rem;
    padding-right: 1.8rem; cursor: pointer;
}
.disbursement-report .filter-input:hover { border-color: var(--primary-line); }
.disbursement-report .filter-input:focus-visible {
    outline: none; border-color: var(--primary);
    box-shadow: 0 0 0 3px var(--primary-soft);
}
.disbursement-report .filter-search-wrap { position: relative; }
.disbursement-report .filter-search-icon {
    position: absolute; left: 0.6rem; top: 50%; transform: translateY(-50%);
    color: var(--ink-3); pointer-events: none;
}
.disbursement-report .filter-input--search { padding-left: 2rem; }
.disbursement-report .filter-actions { display: flex; gap: 0.5rem; flex-wrap: wrap; }
.disbursement-report .btn-primary {
    background: var(--primary); border: 1px solid var(--primary);
    transition: background var(--t-fast) var(--ease);
}
.disbursement-report .btn-primary:hover { background: var(--primary-ink); border-color: var(--primary-ink); }
.disbursement-report .btn-primary:focus-visible { box-shadow: 0 0 0 3px var(--primary-soft); }

/* ─── Summary chips ─── */
.disbursement-report .report-summary {
    display: flex; flex-wrap: wrap; gap: 0.6rem;
    margin-bottom: 1rem;
}
.disbursement-report .report-summary__chip {
    display: inline-flex; align-items: baseline; gap: 0.4rem;
    background: var(--surface); border: 1px solid var(--line);
    padding: 0.55rem 0.95rem; border-radius: var(--radius-sm);
    box-shadow: var(--shadow-1);
}
.disbursement-report .report-summary__chip--accent { border-color: var(--primary-line); background: var(--primary-soft); }
.disbursement-report .report-summary__chip--accent .report-summary__value { color: var(--primary-ink); }
.disbursement-report .report-summary__label { font-size: 0.78rem; color: var(--ink-3); font-weight: 600; }
.disbursement-report .report-summary__value {
    font-size: 1.05rem; font-weight: 700; color: var(--ink-1);
    font-variant-numeric: tabular-nums;
}
.disbursement-report .report-summary__unit { font-size: 0.75rem; color: var(--ink-3); }

/* ─── Pivot card / table ─── */
.disbursement-report .pivot-card {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    overflow: hidden;
}
.disbursement-report .pivot-table-wrap {
    overflow-x: auto;
    max-height: calc(100vh - 320px);
}
.disbursement-report .pivot-table {
    width: 100%;
    border-collapse: separate; border-spacing: 0;
    font-size: 0.83rem;
    font-variant-numeric: tabular-nums;
}
.disbursement-report .pivot-table thead th {
    background: var(--surface-2); color: var(--ink-2);
    font-weight: 600;
    border-bottom: 1px solid var(--line);
    padding: 0.5rem 0.65rem;
    text-align: center;
    white-space: nowrap;
    position: sticky; top: 0; z-index: 2;
}
.disbursement-report .pivot-table thead tr:nth-child(2) th { top: 36px; }
.disbursement-report .pivot-th--month { font-size: 0.85rem; color: var(--ink-1); }
.disbursement-report .pivot-th--total { background: var(--primary-soft); color: var(--primary-ink); }
.disbursement-report .pivot-th--sub {
    font-size: 0.72rem; font-weight: 500;
    color: var(--ink-3); padding: 0.35rem 0.6rem;
    border-top: 1px solid var(--line);
}
.disbursement-report .pivot-th--sub-val { border-left: 1px dashed var(--line); }
.disbursement-report .pivot-th--sticky {
    position: sticky; left: 0;
    background: var(--surface-2);
    z-index: 3 !important;
    border-right: 1px solid var(--line);
}
.disbursement-report .pivot-th--sticky:nth-child(2) { left: 3rem; }
.disbursement-report .pivot-th--name { text-align: left; min-width: 220px; }

.disbursement-report .pivot-table tbody td {
    padding: 0.55rem 0.65rem;
    border-bottom: 1px solid var(--line);
    text-align: right;
    color: var(--ink-1);
    transition: background var(--t-fast) var(--ease);
}
.disbursement-report .pivot-table tbody tr:nth-child(even) td { background: rgba(0, 0, 0, 0.012); }
.disbursement-report .pivot-table tbody tr:hover td { background: var(--surface-hover); }
.disbursement-report .pivot-td--sticky {
    position: sticky; left: 0;
    background: var(--surface);
    z-index: 1;
    border-right: 1px solid var(--line);
}
.disbursement-report .pivot-table tbody tr:nth-child(even) .pivot-td--sticky { background: #fbfcfe; }
.disbursement-report .pivot-table tbody tr:hover .pivot-td--sticky { background: var(--surface-hover); }
.disbursement-report .pivot-td--sticky:nth-child(2) { left: 3rem; text-align: left; }
.disbursement-report .pivot-td--name { min-width: 220px; }
.disbursement-report .pivot-item-name { font-weight: 600; color: var(--ink-1); line-height: 1.3; }
.disbursement-report .pivot-item-meta {
    display: flex; flex-wrap: wrap; gap: 0.35rem; align-items: center;
    margin-top: 0.2rem; font-size: 0.7rem; color: var(--ink-3);
}
.disbursement-report .pivot-item-code { font-family: ui-monospace, SFMono-Regular, monospace; }
.disbursement-report .pivot-item-cat::before { content: "·"; margin-right: 0.35rem; color: var(--ink-4); }
.disbursement-report .pivot-item-unit::before { content: "·"; margin-right: 0.35rem; color: var(--ink-4); }

.disbursement-report .pivot-td--val { border-left: 1px dashed var(--line); color: var(--ink-2); font-size: 0.78rem; }
.disbursement-report .pivot-td--qty { font-weight: 600; }
.disbursement-report .pivot-td--total { background: var(--primary-soft) !important; color: var(--primary-ink) !important; font-weight: 700; }
.disbursement-report .pivot-table tbody tr:hover .pivot-td--total { background: rgba(13, 110, 253, 0.12) !important; }
.disbursement-report .pivot-td--qty.is-empty,
.disbursement-report .pivot-td--val.is-empty { color: var(--ink-4); font-weight: 400; }
.disbursement-report .pivot-td--qty.is-clickable,
.disbursement-report .pivot-td--val.is-clickable {
    cursor: pointer;
    position: relative;
}
.disbursement-report .pivot-td--qty.is-clickable:hover,
.disbursement-report .pivot-td--val.is-clickable:hover {
    background: var(--primary-soft) !important;
    color: var(--primary-ink);
    text-decoration: underline;
    text-decoration-style: dotted;
    text-decoration-thickness: 1px;
    text-underline-offset: 3px;
}
.disbursement-report .pivot-td--qty.is-clickable:focus-visible,
.disbursement-report .pivot-td--val.is-clickable:focus-visible {
    outline: 2px solid var(--primary); outline-offset: -2px;
    background: var(--primary-soft);
}

.disbursement-report .pivot-tfoot td {
    background: #fffbe6 !important;
    border-top: 2px solid var(--warning);
    font-weight: 700; color: var(--ink-1);
    position: sticky; bottom: 0; z-index: 1;
}
.disbursement-report .pivot-tfoot .pivot-td--total { background: #ffd966 !important; color: var(--ink-1) !important; }

.disbursement-report .pivot-hint {
    margin: 0; padding: 0.65rem 1.1rem;
    background: var(--surface-2); color: var(--ink-3);
    font-size: 0.78rem; border-top: 1px solid var(--line);
    display: flex; align-items: center; gap: 0.4rem;
}

/* ─── Empty state ─── */
.disbursement-report .empty-block {
    background: var(--surface);
    border: 1px dashed var(--line-strong);
    border-radius: var(--radius);
    padding: 3rem 1.5rem; text-align: center;
}
.disbursement-report .empty-block__icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 64px; height: 64px;
    background: var(--surface-3); color: var(--ink-3);
    border-radius: 16px; font-size: 1.75rem;
    margin-bottom: 0.75rem;
}
.disbursement-report .empty-block__title { margin: 0 0 0.25rem; font-weight: 600; color: var(--ink-2); }
.disbursement-report .empty-block__caption { margin: 0; color: var(--ink-3); font-size: 0.85rem; }

/* ─── Drill modal ─── */
.drill-modal { border: 1px solid var(--line, rgba(15,23,42,0.08)); border-radius: 10px; }
.drill-modal .modal-header { border-bottom: 1px solid var(--line, rgba(15,23,42,0.08)); padding: 0.85rem 1.1rem; }
.drill-modal .modal-title { font-weight: 600; font-size: 1rem; }
.drill-modal .drill-loading { display: flex; align-items: center; justify-content: center; padding: 2rem; }
.drill-modal .drill-table {
    width: 100%; font-size: 0.85rem; border-collapse: collapse;
    font-variant-numeric: tabular-nums;
}
.drill-modal .drill-table th {
    background: #f7f9fc; color: #4a5568;
    font-weight: 600; font-size: 0.78rem;
    padding: 0.55rem 0.7rem; text-align: left;
    border-bottom: 1px solid rgba(15,23,42,0.08);
}
.drill-modal .drill-table th.text-end { text-align: right; }
.drill-modal .drill-table td {
    padding: 0.55rem 0.7rem;
    border-bottom: 1px solid rgba(15,23,42,0.06);
}
.drill-modal .drill-table td.text-end { text-align: right; }
.drill-modal .drill-table tfoot td {
    background: #fffbe6;
    font-weight: 700;
    border-top: 2px solid #b45309;
}
.drill-modal .drill-meta {
    background: #f7f9fc; padding: 0.65rem 0.85rem;
    border-radius: 8px; margin-bottom: 0.85rem;
    font-size: 0.85rem; color: #4a5568;
}
.drill-modal .drill-meta strong { color: #1a202c; }
.drill-modal .drill-empty { padding: 2rem; text-align: center; color: #718096; }

/* ─── Responsive ─── */
@media (max-width: 991.98px) {
    .disbursement-report .filter-grid {
        grid-template-columns: 1fr 1fr;
    }
    .disbursement-report .filter-field--search { grid-column: span 2; }
    .disbursement-report .filter-actions { grid-column: span 2; justify-content: flex-end; }
}
@media (max-width: 575.98px) {
    .disbursement-report .filter-grid { grid-template-columns: 1fr; }
    .disbursement-report .filter-field--search,
    .disbursement-report .filter-actions { grid-column: span 1; }
    .disbursement-report .pivot-table-wrap { max-height: calc(100vh - 280px); }
    .disbursement-report .pivot-th--sticky:nth-child(2),
    .disbursement-report .pivot-td--sticky:nth-child(2) { left: 2.5rem; min-width: 160px; }
}

/* ─── Reduced motion ─── */
@media (prefers-reduced-motion: reduce) {
    .disbursement-report .pivot-table tbody td,
    .disbursement-report .filter-input,
    .disbursement-report .btn-primary { transition: none !important; }
}
</style>

<?php
$drillUrl = Url::to(['/inventory-v2/report/disbursement-detail']);
$drillContext = json_encode([
    'year' => $year,
    'main_warehouse_id' => $mainWarehouseId,
    'sub_warehouse_id' => $subWarehouseId,
    'month_full' => $monthFull,
]);
$itemNameMap = [];
foreach ($rows as $r) {
    $itemNameMap[$r['item_code']] = $r['item_name'];
}
$itemNameJson = json_encode($itemNameMap, JSON_UNESCAPED_UNICODE);

$js = <<<JS
(function(){
    var drillUrl = '{$drillUrl}';
    var ctx = {$drillContext};
    var itemNames = {$itemNameJson};
    var modalEl = document.getElementById('drillModal');
    var modalInstance = null;

    function openModal() {
        if (!modalInstance) { modalInstance = new bootstrap.Modal(modalEl); }
        modalInstance.show();
    }

    function formatNum(v) {
        return (Number(v) || 0).toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderRows(data, item, month) {
        var monthLabel = ctx.month_full[month - 1] + ' ' + ctx.year;
        var itemName = itemNames[item] || item;
        var html = '';
        html += '<div class="drill-meta">';
        html += '<div><strong>' + escapeHtml(itemName) + '</strong> <span class="text-muted">(' + escapeHtml(item) + ')</span></div>';
        html += '<div class="mt-1 small">เดือน <strong>' + escapeHtml(monthLabel) + '</strong>';
        html += ' · เอกสารทั้งหมด <strong>' + (data.rows ? data.rows.length : 0) + '</strong> ใบ</div>';
        html += '</div>';

        if (!data.rows || data.rows.length === 0) {
            html += '<div class="drill-empty"><i class="bi bi-inbox fs-2"></i><p class="mb-0 mt-2">ไม่มีข้อมูล</p></div>';
            return html;
        }

        html += '<div class="table-responsive"><table class="drill-table">';
        html += '<thead><tr>';
        html += '<th>วันที่</th><th>เลขที่เอกสาร</th><th>คลังต้นทาง</th><th>คลังปลายทาง</th><th>Lot</th>';
        html += '<th class="text-end">จำนวน</th><th class="text-end">ราคา/หน่วย</th><th class="text-end">มูลค่า</th>';
        html += '</tr></thead><tbody>';
        data.rows.forEach(function(r) {
            html += '<tr>';
            html += '<td>' + escapeHtml(r.order_date) + '</td>';
            html += '<td>' + escapeHtml(r.order_no) + '</td>';
            html += '<td>' + escapeHtml(r.main_warehouse) + '</td>';
            html += '<td>' + escapeHtml(r.sub_warehouse) + '</td>';
            html += '<td>' + escapeHtml(r.lot_number) + '</td>';
            html += '<td class="text-end">' + formatNum(r.qty) + '</td>';
            html += '<td class="text-end">' + formatNum(r.unit_price) + '</td>';
            html += '<td class="text-end">' + formatNum(r.value) + '</td>';
            html += '</tr>';
        });
        html += '</tbody>';
        html += '<tfoot><tr>';
        html += '<td colspan="5" class="text-end">รวม</td>';
        html += '<td class="text-end">' + formatNum(data.total_qty) + '</td>';
        html += '<td></td>';
        html += '<td class="text-end">' + formatNum(data.total_value) + '</td>';
        html += '</tr></tfoot>';
        html += '</table></div>';
        return html;
    }

    function escapeHtml(s) {
        if (s === null || s === undefined) return '';
        return String(s).replace(/[&<>"']/g, function(c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function loadDrill(item, month) {
        document.getElementById('drillModalBody').innerHTML = '<div class="drill-loading"><div class="spinner-border spinner-border-sm text-primary"></div><span class="ms-2 text-muted small">กำลังโหลด...</span></div>';
        document.getElementById('drillModalTitle').textContent = 'รายการเอกสารใบเบิก — ' + ctx.month_full[month - 1] + ' ' + ctx.year;
        openModal();

        \$.get(drillUrl, {
            item_code: item,
            year: ctx.year,
            month: month,
            main_warehouse_id: ctx.main_warehouse_id || '',
            sub_warehouse_id: ctx.sub_warehouse_id || ''
        }).done(function(data) {
            document.getElementById('drillModalBody').innerHTML = renderRows(data, item, month);
        }).fail(function() {
            document.getElementById('drillModalBody').innerHTML = '<div class="drill-empty text-danger"><i class="bi bi-exclamation-triangle fs-2"></i><p class="mb-0 mt-2">โหลดข้อมูลไม่สำเร็จ</p></div>';
        });
    }

    // Click + keyboard activation
    document.addEventListener('click', function(e) {
        var cell = e.target.closest('[data-drill="1"]');
        if (!cell) return;
        loadDrill(cell.dataset.item, parseInt(cell.dataset.month, 10));
    });
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        var cell = e.target.closest('[data-drill="1"]');
        if (!cell) return;
        e.preventDefault();
        loadDrill(cell.dataset.item, parseInt(cell.dataset.month, 10));
    });

    // Auto-submit on year/sub change for fast iteration
    var quickInputs = ['f-year', 'f-sub', 'f-main', 'f-cat'];
    quickInputs.forEach(function(id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('change', function() { document.getElementById('filter-form').submit(); });
    });
})();
JS;
$this->registerJs($js);
?>

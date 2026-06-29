<?php
use yii\helpers\Html;
use yii\helpers\Url;

/** @var int $year ค.ศ. ที่เก็บใน DB */
/** @var int $month */
/** @var int|null $warehouseId */
/** @var array $warehouses */
/** @var array $rows */
/** @var bool $hasData */
/** @var array{closed_at: int|null, closed_by_name: string|null}|null $closeMeta */

$this->title = 'สรุปรายงานวัสดุคงคลัง';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$monthNames = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
    5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
    9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม',
];
$periodLabel = isset($monthNames[$month]) ? $monthNames[$month] . ' ' . ($year + 543) . ' (พ.ศ.)' : '';
$closedAtLabel = '';
if ($hasData && !empty($closeMeta['closed_at'])) {
    $closedAtLabel = date('d/m/', (int) $closeMeta['closed_at']) . (date('Y', (int) $closeMeta['closed_at']) + 543) . ' เวลา ' . date('H:i', (int) $closeMeta['closed_at']);
}

// คอลัมน์ที่ accountant ต้อง audit (clickable cells)
// ใช้ key เดียวกับ DB / kind ของ actionCategoryDrilldown
$drillKinds = ['opening', 'in', 'out_sub', 'out_hosp', 'total_out', 'closing'];

/**
 * Format ตัวเลข — ถ้า < 0 ห่อด้วย <span class="is-negative"> (สีแดง)
 * ถ้า ~ 0 (|val| < 0.005) คืน em-dash (ใช้ cell-zero styling)
 */
$fmtSigned = function ($value) {
    $v = (float) $value;
    if (abs($v) < 0.005) {
        return '<span class="cell-zero" aria-label="ไม่มียอด">—</span>';
    }
    $formatted = number_format($v, 2);
    if ($v < 0) {
        return '<span class="is-negative" aria-label="ติดลบ">' . $formatted . '</span>';
    }
    return $formatted;
};

/**
 * Render cell ตัวเลข — ถ้า value = 0 แสดง em-dash ไม่ทำ button
 * ถ้า kind อยู่ใน $drillKinds → ทำเป็น button .cell-drill (เปิด modal)
 * อย่างอื่น → static text
 */
$renderCell = function ($value, $kind, $categoryCode) use ($drillKinds) {
    $v = (float) $value;
    if (abs($v) < 0.005) {
        return '<span class="cell-zero" aria-label="ไม่มียอด">—</span>';
    }
    $formatted = number_format($v, 2);
    $negCls = $v < 0 ? ' is-negative' : '';
    if (!in_array($kind, $drillKinds, true)) {
        return $negCls !== '' ? '<span class="is-negative" aria-label="ติดลบ">' . $formatted . '</span>' : $formatted;
    }
    return '<button type="button" class="cell-drill' . $negCls . '"'
        . ' data-kind="' . Html::encode($kind) . '"'
        . ' data-category="' . Html::encode($categoryCode) . '"'
        . ' aria-label="' . ($v < 0 ? 'ติดลบ ' : '') . 'ดูรายการ ' . Html::encode($kind) . ' ของประเภท ' . Html::encode($categoryCode) . '">'
        . $formatted
        . '</button>';
};
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-journal-text fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">รายงานสรุปตามประเภทวัสดุ สำหรับส่งบัญชี เลือกเดือนและคลัง แล้วกดปิดเดือนหากยังไม่มีข้อมูล</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'report']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid py-4 ms-report-summary">
    <div class="card border shadow-sm rounded-3 mb-4">
        <div class="card-body py-3 px-4">
            <form method="get" action="<?= Url::to(['/inventory-v2/report/material-summary']) ?>" id="form-material-summary">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-md-auto">
                        <label class="form-label small text-muted mb-1" for="ms-month">เดือน / ปีงบประมาณ (พ.ศ.)</label>
                        <div class="d-flex gap-2 flex-nowrap">
                            <select id="ms-month" name="month" class="form-select" style="min-width: 130px;">
                                <?php for ($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= (int)$month === $m ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                                <?php endfor; ?>
                            </select>
                            <select id="ms-year" name="year" class="form-select" aria-label="ปี พ.ศ." style="min-width: 110px;">
                                <?php for ($y = date('Y') + 543; $y >= (date('Y') + 543 - 5); $y--): ?>
                                    <option value="<?= $y - 543 ?>" <?= (int)$year === ($y - 543) ? 'selected' : '' ?>><?= $y ?> (พ.ศ.)</option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-12 col-md-auto">
                        <label class="form-label small text-muted mb-1" for="ms-warehouse">คลัง</label>
                        <select id="ms-warehouse" name="warehouse_id" class="form-select" style="min-width: 200px;">
                            <?php foreach ($warehouses as $wid => $wname): ?>
                                <option value="<?= $wid === '' ? '' : (int)$wid ?>" <?= (string)$warehouseId === (string)$wid ? 'selected' : '' ?>><?= Html::encode($wname) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md d-flex flex-wrap gap-2 justify-content-md-end">
                        <button type="submit" class="btn btn-primary px-3">
                            <i class="bi bi-search me-1"></i> แสดงรายงาน
                        </button>
                        <?php if ($hasData): ?>
                            <a href="<?= Url::to(array_merge(['/inventory-v2/report/export-excel'], ['year' => $year, 'month' => $month], $warehouseId ? ['warehouse_id' => $warehouseId] : [])) ?>" class="btn btn-success px-3">
                                <i class="bi bi-file-earmark-excel me-1"></i> Excel
                            </a>
                        <?php endif; ?>
                        <a href="<?= Url::to(array_merge(['/inventory-v2/report/material-by-item'], ['year' => $year, 'month' => $month], $warehouseId ? ['warehouse_id' => $warehouseId] : [])) ?>" class="btn btn-outline-secondary px-3">
                            <i class="bi bi-list-ul me-1"></i> แยกรายการ
                        </a>
                        <button type="button" class="btn btn-outline-secondary px-3" id="btn-close-month" data-bs-toggle="modal" data-bs-target="#modal-close-month">
                            <i class="bi bi-calendar-check me-1"></i> ปิดเดือน
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (!$hasData): ?>
        <div class="alert alert-info border-0 shadow-sm">
            <i class="bi bi-info-circle me-2"></i>
            ยังไม่มีข้อมูลปิดเดือนสำหรับเดือน<?= isset($monthNames[$month]) ? ' ' . $monthNames[$month] . ' ' . ($year + 543) . ' (พ.ศ.)' : '' ?> กรุณาเลือกคลังด้านบน แล้วกด <strong>ปิดเดือน</strong> เพื่อคำนวณและบันทึกข้อมูล
        </div>
    <?php else: ?>
        <div class="card border shadow-sm rounded-3">
            <div class="ms-report-summary__head">
                <div class="ms-report-summary__head-titles">
                    <h6 class="mb-0 fw-semibold text-body">สรุปยอดวัสดุคงคลัง <?= Html::encode($periodLabel) ?></h6>
                    <span class="ms-report-summary__head-hint">หน่วยตัวเลข: บาท · คลิกตัวเลขเพื่อดูรายการรายตัว</span>
                </div>
                <?php if ($closedAtLabel !== ''): ?>
                    <span class="ms-report-summary__closed-badge" title="วันเวลาที่ปิดเดือนล่าสุด">
                        <i class="bi bi-check-circle-fill" aria-hidden="true"></i>
                        ปิดเดือนแล้ว
                        <span class="ms-report-summary__closed-badge-meta">
                            <?= Html::encode($closedAtLabel) ?>
                            <?= !empty($closeMeta['closed_by_name']) ? ' โดย ' . Html::encode($closeMeta['closed_by_name']) : '' ?>
                        </span>
                    </span>
                <?php endif; ?>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 ms-report-summary__table">
                        <thead>
                            <tr>
                                <th style="width: 4%;" class="text-center">#</th>
                                <th style="width: 22%;">รายการ</th>
                                <th style="width: 10%;" class="text-end">สินค้าคงเหลือ (บาท)</th>
                                <th style="width: 10%;" class="text-end">ซื้อระหว่างเดือน (บาท)</th>
                                <th style="width: 10%;" class="text-end">รวม (บาท)</th>
                                <th style="width: 10%;" class="text-end">
                                    จ่ายส่วนของ รพ.สต. (บาท)
                                    <i class="bi bi-info-circle ms-report-summary__th-info"
                                       data-bs-toggle="tooltip"
                                       title="คลังย่อยที่กำหนดใน params: inventoryV2.disburseSubWarehouseIds"></i>
                                </th>
                                <th style="width: 12%;" class="text-end">จ่ายส่วนของโรงพยาบาล (บาท)</th>
                                <th style="width: 10%;" class="text-end">รวมจ่าย (บาท)</th>
                                <th style="width: 12%;" class="text-end">ยอดยกไป (บาท)</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php
                            $totOpening = $totIn = $totOutSub = $totOutHosp = $totOut = $totClosing = 0;
                            foreach ($rows as $i => $r):
                                $totOpening += $r['opening_value'];
                                $totIn += $r['in_value'];
                                $totOutSub += $r['out_sub_value'];
                                $totOutHosp += $r['out_hosp_value'];
                                $totOut += $r['total_out_value'];
                                $totClosing += $r['closing_value'];
                                $totalAvail = $r['opening_value'] + $r['in_value'];
                                $catCode = $r['category_code'];
                            ?>
                                <tr>
                                    <td class="text-center text-muted"><?= $i + 1 ?></td>
                                    <td><?= Html::encode($r['category_label']) ?></td>
                                    <td class="text-end"><?= $renderCell($r['opening_value'], 'opening', $catCode) ?></td>
                                    <td class="text-end"><?= $renderCell($r['in_value'], 'in', $catCode) ?></td>
                                    <td class="text-end"><?= $fmtSigned($totalAvail) ?></td>
                                    <td class="text-end"><?= $renderCell($r['out_sub_value'], 'out_sub', $catCode) ?></td>
                                    <td class="text-end"><?= $renderCell($r['out_hosp_value'], 'out_hosp', $catCode) ?></td>
                                    <td class="text-end"><?= $renderCell($r['total_out_value'], 'total_out', $catCode) ?></td>
                                    <td class="text-end"><?= $renderCell($r['closing_value'], 'closing', $catCode) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr class="ms-report-summary__total-row">
                                <td class="text-center"></td>
                                <td>รวม</td>
                                <td class="text-end"><?= $fmtSigned($totOpening) ?></td>
                                <td class="text-end"><?= $fmtSigned($totIn) ?></td>
                                <td class="text-end"><?= $fmtSigned($totOpening + $totIn) ?></td>
                                <td class="text-end"><?= $fmtSigned($totOutSub) ?></td>
                                <td class="text-end"><?= $fmtSigned($totOutHosp) ?></td>
                                <td class="text-end"><?= $fmtSigned($totOut) ?></td>
                                <td class="text-end"><?= $fmtSigned($totClosing) ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal ปิดเดือน -->
<div class="modal fade" id="modal-close-month" tabindex="-1" aria-labelledby="modal-close-month-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-close-month-title">ปิดเดือน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">ระบบจะคำนวณยอดจากธุรกรรมรับ-จ่ายในเดือนที่เลือก แล้วบันทึกลงรายงานประจำเดือน</p>
                <div class="mb-3">
                    <label class="form-label" for="close-warehouse-id">คลัง</label>
                    <select id="close-warehouse-id" class="form-select">
                        <option value="">เลือกคลัง</option>
                        <option value="all">ปิดรวมทุกคลังหลัก</option>
                        <?php foreach ($warehouses as $wid => $wname): ?>
                            <?php if ($wid !== ''): ?>
                                <option value="<?= (int)$wid ?>"><?= Html::encode($wname) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">เดือน / ปีงบประมาณ (พ.ศ.)</label>
                    <div class="d-flex gap-2">
                        <select id="close-month" class="form-select" aria-label="เดือน">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= (int)$month === $m ? 'selected' : '' ?>><?= $monthNames[$m] ?></option>
                            <?php endfor; ?>
                        </select>
                        <select id="close-year" class="form-select" aria-label="ปี พ.ศ.">
                            <?php for ($y = date('Y') + 543; $y >= (date('Y') + 543 - 5); $y--): ?>
                                <option value="<?= $y - 543 ?>" <?= (int)$year === ($y - 543) ? 'selected' : '' ?>><?= $y ?> (พ.ศ.)</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div id="close-month-result" class="alert d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary me-2" id="btn-do-close-month">
                    <i class="bi bi-calendar-check"></i> ปิดเดือน
                </button>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">ยกเลิก</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: ดูรายการ item ที่ประกอบเป็นยอดของ cell ที่คลิก -->
<div class="modal fade" id="categoryDrillModal" tabindex="-1" aria-labelledby="categoryDrillModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content cd-modal">
            <header class="cd-modal__head">
                <div class="cd-modal__head-row">
                    <span class="cd-modal__kind-badge" id="cd-kind-badge">
                        <i class="bi bi-arrow-down-left-circle" aria-hidden="true"></i>
                        <span id="cd-kind-label">—</span>
                    </span>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="ปิด"></button>
                </div>
                <h5 id="categoryDrillModalTitle" class="cd-modal__title">
                    <span id="cd-category-label">ประเภทวัสดุ</span>
                </h5>
                <p class="cd-modal__caption">
                    <span id="cd-period">—</span>
                    <span class="cd-modal__caption-sep" aria-hidden="true">·</span>
                    <span id="cd-warehouse">—</span>
                </p>
                <div class="cd-modal__stat-strip" id="cd-stat-strip" hidden>
                    <span class="cd-modal__stat">
                        <span class="cd-modal__stat-label">รายการ</span>
                        <span class="cd-modal__stat-value" id="cd-count">0</span>
                        <span class="cd-modal__stat-unit">ชนิด</span>
                    </span>
                    <span class="cd-modal__stat-sep" aria-hidden="true"></span>
                    <span class="cd-modal__stat cd-modal__stat--total">
                        <span class="cd-modal__stat-label">รวมมูลค่า</span>
                        <span class="cd-modal__stat-value" id="cd-total">0.00</span>
                        <span class="cd-modal__stat-unit">บาท</span>
                    </span>
                </div>
            </header>

            <div class="cd-modal__filter" id="cd-filter" hidden>
                <label for="cd-search" class="visually-hidden">ค้นหา</label>
                <div class="cd-modal__search-wrap">
                    <i class="bi bi-search cd-modal__search-icon" aria-hidden="true"></i>
                    <input type="search"
                           id="cd-search"
                           class="cd-modal__search"
                           placeholder="ค้นหารหัส / ชื่อวัสดุ"
                           autocomplete="off"
                           aria-controls="cd-body">
                    <button type="button" class="cd-modal__search-clear" id="cd-search-clear" aria-label="ล้างค้นหา" hidden>
                        <i class="bi bi-x-circle-fill" aria-hidden="true"></i>
                    </button>
                </div>
                <span class="cd-modal__filter-meta" id="cd-filter-meta" aria-live="polite"></span>
            </div>

            <div class="cd-modal__body" id="cd-body" aria-live="polite" aria-busy="false">
                <!-- injected by JS -->
            </div>

            <footer class="cd-modal__foot">
                <span class="cd-modal__foot-hint">
                    <i class="bi bi-info-circle" aria-hidden="true"></i>
                    มูลค่าจ่ายออกอ้างราคาทุนตามล็อตที่บันทึกตอนปิดเดือน
                </span>
                <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">ปิด</button>
            </footer>
        </div>
    </div>
</div>

<style>
/* Tokens — ใช้ร่วมกับ modal ที่อยู่นอก .ms-report-summary scope (Bootstrap modal positioned fixed) */
.ms-report-summary,
#categoryDrillModal,
#modal-close-month {
    --ms-ink-1: #1a202c;
    --ms-ink-2: #4a5568;
    --ms-ink-3: #718096;
    --ms-ink-4: #a0aec0;
    --ms-surface: #ffffff;
    --ms-surface-2: #f7f9fc;
    --ms-surface-3: #eef2f7;
    --ms-line: rgba(15, 23, 42, 0.08);
    --ms-line-strong: rgba(15, 23, 42, 0.18);
    --ms-primary: #0d6efd;
    --ms-primary-ink: #0a58ca;
    --ms-primary-soft: rgba(13, 110, 253, 0.08);
    --ms-success: #15803d;
    --ms-success-soft: rgba(21, 128, 61, 0.10);
    --ms-radius: 10px;
    --ms-radius-sm: 8px;
    --ms-ease: cubic-bezier(0.16, 1, 0.3, 1);
}

.ms-report-summary__head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 0.85rem 1.1rem;
    background: var(--ms-surface-2);
    border-bottom: 1px solid var(--ms-line);
    flex-wrap: wrap;
}
.ms-report-summary__head-titles { display: flex; flex-direction: column; gap: 0.15rem; }
.ms-report-summary__head-hint { font-size: 0.78rem; color: var(--ms-ink-3); }

.ms-report-summary__closed-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.7rem;
    border-radius: 999px;
    background: var(--ms-success-soft);
    color: var(--ms-success);
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.2;
}
.ms-report-summary__closed-badge i { font-size: 0.85rem; }
.ms-report-summary__closed-badge-meta {
    color: var(--ms-ink-2);
    font-weight: 500;
    padding-left: 0.4rem;
    border-left: 1px solid rgba(21, 128, 61, 0.25);
}

.ms-report-summary__th-info { color: var(--ms-ink-4); cursor: help; margin-left: 0.2rem; }

.ms-report-summary__table thead th {
    background: var(--ms-surface-2);
    color: var(--ms-ink-2);
    font-weight: 600;
    font-size: 0.85rem;
    border-bottom: 1px solid var(--ms-line);
}
.ms-report-summary__table tbody td { font-size: 0.9rem; color: var(--ms-ink-1); }
.ms-report-summary__table tbody tr:hover td { background: var(--ms-surface-3); }
.ms-report-summary__table .text-end { font-variant-numeric: tabular-nums; }

.ms-report-summary__total-row td {
    background: var(--ms-surface-2);
    color: var(--ms-ink-1);
    font-weight: 700;
    border-top: 2px solid var(--ms-line);
}

/* Clickable cell button */
.ms-report-summary .cell-drill {
    appearance: none;
    border: none;
    background: transparent;
    padding: 0.1rem 0.35rem;
    margin: -0.1rem -0.35rem;
    border-radius: var(--ms-radius-sm);
    font: inherit;
    color: var(--ms-primary-ink);
    cursor: pointer;
    font-variant-numeric: tabular-nums;
    transition: background-color 120ms var(--ms-ease), color 120ms var(--ms-ease);
}
.ms-report-summary .cell-drill:hover { background: var(--ms-primary-soft); text-decoration: underline; }
.ms-report-summary .cell-drill:focus-visible { outline: 2px solid var(--ms-primary); outline-offset: 1px; }
.ms-report-summary .cell-zero { color: var(--ms-ink-4); }

/* Negative numbers — สีแดงทั้งในตารางสรุปและใน drill-down modal */
.ms-report-summary .is-negative,
.cd-modal .is-negative,
.cd-modal__num.is-negative { color: #b91c1c; }
.ms-report-summary .cell-drill.is-negative { color: #b91c1c; }
.ms-report-summary .cell-drill.is-negative:hover { background: rgba(185, 28, 28, 0.08); }

@media (prefers-reduced-motion: reduce) {
    .ms-report-summary .cell-drill { transition: none; }
}

/* Drill-down modal — head + summary + filter sticky, body scroll, foot sticky */
.cd-modal {
    border-radius: var(--ms-radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 100%;
}
.cd-modal__head { padding: 1rem 1.25rem 0.5rem; flex: 0 0 auto; }
.cd-modal__head-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.6rem; }
.cd-modal__kind-badge {
    display: inline-flex; align-items: center; gap: 0.4rem;
    padding: 0.3rem 0.75rem;
    border-radius: 999px;
    background: var(--ms-primary-soft);
    color: var(--ms-primary-ink);
    font-size: 0.78rem;
    font-weight: 600;
}
.cd-modal__title { margin: 0; font-size: 1.05rem; font-weight: 600; color: var(--ms-ink-1); }
.cd-modal__caption { margin: 0.25rem 0 0; color: var(--ms-ink-3); font-size: 0.85rem; }
.cd-modal__caption-sep { margin: 0 0.4rem; color: var(--ms-ink-4); }

/* Inline stat strip (replaces nested summary card) */
.cd-modal__stat-strip {
    display: flex; align-items: baseline; gap: 0.75rem;
    margin-top: 0.6rem;
    padding-top: 0.55rem;
    border-top: 1px dashed var(--ms-line);
}
.cd-modal__stat { display: inline-flex; align-items: baseline; gap: 0.35rem; }
.cd-modal__stat-label { color: var(--ms-ink-3); font-size: 0.78rem; }
.cd-modal__stat-value { color: var(--ms-ink-1); font-weight: 700; font-size: 1rem; font-variant-numeric: tabular-nums; letter-spacing: -0.01em; }
.cd-modal__stat-unit { color: var(--ms-ink-3); font-size: 0.72rem; }
.cd-modal__stat--total { margin-left: auto; }
.cd-modal__stat--total .cd-modal__stat-value { font-size: 1.15rem; }
.cd-modal__stat--total .cd-modal__stat-value.is-negative { color: #b91c1c; }
.cd-modal__stat-sep { width: 1px; align-self: stretch; background: var(--ms-line); }

.cd-modal__body {
    padding: 0.5rem 1.25rem 1rem;
    flex: 1 1 auto;
    overflow-y: auto;
    min-height: 0; /* allow flex child to shrink under content */
}
/* Sticky thead — ทึบจริง + เงาด้านล่างเพื่อบอก boundary ตอน content เลื่อนใต้ */
.cd-modal__body thead th {
    position: sticky;
    top: 0;
    background-color: #ffffff;
    z-index: 2;
    color: var(--ms-ink-2);
    border-bottom: 1px solid var(--ms-line-strong);
    box-shadow: 0 2px 4px -2px rgba(15, 23, 42, 0.08);
}
.cd-modal__body td.cd-modal__num { font-variant-numeric: tabular-nums; white-space: nowrap; }
.cd-modal__body tbody tr.is-hit-row mark { background: rgba(13, 110, 253, 0.18); color: var(--ms-ink-1); padding: 0 1px; border-radius: 2px; }

/* Image cell */
.cd-modal__img-cell { width: 56px; padding: 0.5rem 0.4rem !important; }
.cd-modal__img {
    width: 44px; height: 44px;
    object-fit: cover; object-position: center;
    border-radius: var(--ms-radius-sm);
    border: 1px solid var(--ms-line);
    background: var(--ms-surface-2);
    display: block;
}

/* Item meta cell */
.cd-modal__item-meta { display: flex; flex-direction: column; gap: 0.1rem; min-width: 0; }
.cd-modal__item-code { color: var(--ms-ink-3); font-size: 0.74rem; font-variant-numeric: tabular-nums; letter-spacing: 0.01em; }
.cd-modal__item-name { color: var(--ms-ink-1); font-size: 0.9rem; font-weight: 500; line-height: 1.3; }

/* Percent bar under value */
.cd-modal__value-stack { display: inline-flex; flex-direction: column; align-items: flex-end; gap: 0.25rem; min-width: 7rem; }
.cd-modal__value-num { font-variant-numeric: tabular-nums; }
.cd-modal__pct-bar {
    width: 100%;
    height: 3px;
    border-radius: 999px;
    background: var(--ms-line);
    position: relative;
    overflow: hidden;
}
.cd-modal__pct-fill {
    position: absolute; top: 0; bottom: 0; right: 0;
    width: var(--pct, 0%);
    background: var(--ms-primary);
    border-radius: 999px;
    transition: width 240ms var(--ms-ease);
}
.cd-modal__pct-fill.is-negative { background: #b91c1c; }
@media (prefers-reduced-motion: reduce) { .cd-modal__pct-fill { transition: none; } }

/* Row stagger reveal (only on initial load — flagged via .is-stagger) */
@keyframes cd-row-in {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: none; }
}
.cd-modal__body tbody tr.is-stagger {
    animation: cd-row-in 220ms var(--ms-ease) backwards;
    animation-delay: calc(var(--i, 0) * 14ms);
}
@media (prefers-reduced-motion: reduce) {
    .cd-modal__body tbody tr.is-stagger { animation: none; }
}

/* Filter crossfade for tbody (when re-rendering after typing) */
.cd-modal__body tbody.is-filter-fade { animation: cd-row-fade 140ms var(--ms-ease) backwards; }
@keyframes cd-row-fade { from { opacity: 0.35; } to { opacity: 1; } }
@media (prefers-reduced-motion: reduce) { .cd-modal__body tbody.is-filter-fade { animation: none; } }

/* Filter / search */
.cd-modal__filter {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0.75rem 1.25rem 0;
    flex: 0 0 auto;
}
.cd-modal__search-wrap { position: relative; flex: 1 1 auto; min-width: 0; }
.cd-modal__search-icon {
    position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
    color: var(--ms-ink-3); font-size: 0.9rem; pointer-events: none;
}
.cd-modal__search {
    width: 100%;
    height: 40px;
    padding: 0.4rem 2.4rem 0.4rem 2.3rem;
    border: 1px solid var(--ms-line-strong);
    border-radius: var(--ms-radius-sm);
    background-color: #ffffff;
    color: var(--ms-ink-1);
    font-size: 0.92rem;
    transition: border-color 120ms var(--ms-ease), box-shadow 120ms var(--ms-ease);
}
.cd-modal__search::placeholder { color: var(--ms-ink-3); opacity: 1; }
.cd-modal__search:hover { border-color: var(--ms-ink-3); }
.cd-modal__search:focus,
.cd-modal__search:focus-visible {
    outline: none;
    border-color: var(--ms-primary);
    box-shadow: 0 0 0 3px var(--ms-primary-soft);
}
.cd-modal__search:focus + .cd-modal__search-clear,
.cd-modal__search-wrap:focus-within .cd-modal__search-icon { color: var(--ms-primary); }
.cd-modal__search-clear {
    position: absolute; right: 0.4rem; top: 50%; transform: translateY(-50%);
    border: 0; background: transparent; color: var(--ms-ink-4);
    padding: 0.2rem; line-height: 1; cursor: pointer; border-radius: 999px;
    transition: color 120ms var(--ms-ease);
}
.cd-modal__search-clear:hover { color: var(--ms-ink-2); }
.cd-modal__filter-meta { color: var(--ms-ink-3); font-size: 0.78rem; white-space: nowrap; flex: 0 0 auto; }

@media (prefers-reduced-motion: reduce) {
    .cd-modal__search, .cd-modal__search-clear { transition: none; }
}

/* Skeleton — match real row shape: square thumb + two stacked lines + two number cells */
.cd-modal__skeleton { display: flex; flex-direction: column; gap: 0.5rem; padding: 0.5rem 0; }
.cd-modal__skeleton-row {
    display: grid;
    grid-template-columns: 44px 1fr 5rem 7rem;
    gap: 0.75rem;
    align-items: center;
    padding: 0.4rem 0.25rem;
}
.cd-modal__skeleton-thumb { width: 44px; height: 44px; border-radius: var(--ms-radius-sm); }
.cd-modal__skeleton-lines { display: flex; flex-direction: column; gap: 0.35rem; }
.cd-modal__skeleton-line { height: 0.7rem; border-radius: 999px; }
.cd-modal__skeleton-line--sm { height: 0.55rem; width: 35%; }
.cd-modal__skeleton-line--md { width: 75%; }
.cd-modal__skeleton-num { height: 0.7rem; border-radius: 999px; justify-self: end; width: 80%; }
.cd-modal__skeleton-thumb,
.cd-modal__skeleton-line,
.cd-modal__skeleton-num {
    background: linear-gradient(90deg, var(--ms-surface-2), var(--ms-surface-3), var(--ms-surface-2));
    background-size: 200% 100%;
    animation: cd-shimmer 1.2s linear infinite;
}
@keyframes cd-shimmer { from { background-position: 200% 0; } to { background-position: -200% 0; } }
@media (prefers-reduced-motion: reduce) {
    .cd-modal__skeleton-thumb,
    .cd-modal__skeleton-line,
    .cd-modal__skeleton-num { animation: none; background: var(--ms-surface-3); }
}

/* Empty / error — iconified */
.cd-modal__empty, .cd-modal__error {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--ms-ink-3);
    display: flex; flex-direction: column; align-items: center; gap: 0.5rem;
}
.cd-modal__empty i, .cd-modal__error i { font-size: 1.75rem; color: var(--ms-ink-4); }
.cd-modal__empty p, .cd-modal__error p { margin: 0; color: var(--ms-ink-2); }
.cd-modal__empty small, .cd-modal__error small { color: var(--ms-ink-3); }
.cd-modal__error { color: #b91c1c; }
.cd-modal__error i { color: #b91c1c; }
.cd-modal__error p { color: #b91c1c; }
.cd-modal__error button { margin-top: 0.5rem; }

.cd-modal__foot {
    display: flex; justify-content: space-between; align-items: center; gap: 0.75rem;
    padding: 0.75rem 1.25rem;
    border-top: 1px solid var(--ms-line);
    background: var(--ms-surface-2);
    flex: 0 0 auto;
}
.cd-modal__foot-hint { color: var(--ms-ink-3); font-size: 0.78rem; }

@media (max-width: 768px) {
    .ms-report-summary__head { flex-direction: column; align-items: flex-start; }
    .cd-modal__summary { margin: 0.5rem 0.75rem 0; }
    .cd-modal__head, .cd-modal__body, .cd-modal__foot { padding-left: 0.75rem; padding-right: 0.75rem; }
}
</style>

<?php
$closeUrl = Url::to(['/inventory-v2/report/close-month']);
$reportUrl = Url::to(['/inventory-v2/report/material-summary']);
$drillUrl = Url::to(['/inventory-v2/report/category-drilldown']);
$placeholderUrl = Yii::getAlias('@web') . '/img/placeholder-img.jpg';
$ctxYear = (int) $year;
$ctxMonth = (int) $month;
$ctxWarehouse = $warehouseId === null ? 'null' : (int) $warehouseId;
$this->registerJs(<<<JS
(function(){
    // ── Close-month modal ──
    \$('#btn-do-close-month').on('click', function(){
        var \$btn = $(this).prop('disabled', true);
        var wh = $('#close-warehouse-id').val();
        var month = $('#close-month').val();
        var year = $('#close-year').val();
        var \$result = $('#close-month-result').addClass('d-none');
        if (!wh) {
            \$result.removeClass('d-none alert-success alert-danger').addClass('alert-warning').text('กรุณาเลือกคลังหรือปิดรวมทุกคลัง').show();
            \$btn.prop('disabled', false);
            return;
        }
        $.post('{$closeUrl}', { warehouse_id: wh, month: month, year: year })
            .done(function(res){
                if (res.success) {
                    var msg = 'ปิดเดือนเรียบร้อย รายการ ' + (res.count || 0) + ' รายการ';
                    if (res.warehouses_count > 1) msg += ' (' + res.warehouses_count + ' คลัง)';
                    \$result.removeClass('alert-danger alert-warning').addClass('alert-success').html(msg).removeClass('d-none');
                    var qs = 'year=' + year + '&month=' + month;
                    if (wh !== 'all') qs += '&warehouse_id=' + wh;
                    setTimeout(function(){ window.location.href = '{$reportUrl}?' + qs; }, 1200);
                } else {
                    \$result.removeClass('alert-success alert-warning').addClass('alert-danger').text(res.message || 'เกิดข้อผิดพลาด').removeClass('d-none');
                    \$btn.prop('disabled', false);
                }
            })
            .fail(function(){
                \$result.removeClass('alert-success alert-warning').addClass('alert-danger').text('เกิดข้อผิดพลาด').removeClass('d-none');
                \$btn.prop('disabled', false);
            });
    });

    // ── Bootstrap tooltip for tooltip-info icons ──
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el){
            new bootstrap.Tooltip(el);
        });
    }

    // ── Cell drill-down ──
    var ctxYear = {$ctxYear};
    var ctxMonth = {$ctxMonth};
    var ctxWarehouse = {$ctxWarehouse};
    var drillUrl = '{$drillUrl}';
    var modalEl = document.getElementById('categoryDrillModal');
    var modal = (typeof bootstrap !== 'undefined' && bootstrap.Modal) ? new bootstrap.Modal(modalEl) : null;
    var lastTrigger = null;
    var currentXhr = null;

    var dirIconByKind = {
        opening: 'bi-archive',
        in: 'bi-arrow-down-left-circle',
        out_sub: 'bi-arrow-up-right-circle',
        out_hosp: 'bi-arrow-up-right-circle',
        total_out: 'bi-arrow-up-right-circle',
        closing: 'bi-archive-fill'
    };

    var currentItems = [];
    var currentQuery = '';

    function fmt(n){
        return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    function fmtQty(n){
        return Number(n || 0).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    }
    function escapeHtml(s){
        return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function renderSkeleton(){
        var row = '<div class="cd-modal__skeleton-row">'
            + '<div class="cd-modal__skeleton-thumb"></div>'
            + '<div class="cd-modal__skeleton-lines">'
            +   '<div class="cd-modal__skeleton-line cd-modal__skeleton-line--sm"></div>'
            +   '<div class="cd-modal__skeleton-line cd-modal__skeleton-line--md"></div>'
            + '</div>'
            + '<div class="cd-modal__skeleton-num"></div>'
            + '<div class="cd-modal__skeleton-num"></div>'
            + '</div>';
        return '<div class="cd-modal__skeleton" aria-hidden="true">' + row + row + row + row + '</div>';
    }

    function highlight(text, query){
        if (!query) return escapeHtml(text);
        var safe = escapeHtml(text);
        var safeQ = escapeHtml(query).replace(/[.*+?^\${}()|[\]\\\\]/g, '\\\\\$&');
        try {
            return safe.replace(new RegExp('(' + safeQ + ')', 'gi'), '<mark>\$1</mark>');
        } catch (e) {
            return safe;
        }
    }

    function renderTable(items, query, animate){
        if (!items.length) {
            if (query) {
                return '<div class="cd-modal__empty">'
                    + '<i class="bi bi-search" aria-hidden="true"></i>'
                    + '<p>ไม่พบรายการที่ตรงกับ "' + escapeHtml(query) + '"</p>'
                    + '<small>ลองค้นด้วยรหัสหรือคำในชื่อ</small>'
                    + '</div>';
            }
            return '<div class="cd-modal__empty">'
                + '<i class="bi bi-inbox" aria-hidden="true"></i>'
                + '<p>ไม่พบรายการที่ประกอบเป็นยอดนี้</p>'
                + '</div>';
        }
        var hasQuery = !!query;
        var placeholderUrl = '{$placeholderUrl}';
        var rows = items.map(function(it, idx){
            var classes = [];
            if (hasQuery) classes.push('is-hit-row');
            if (animate && idx < 25) classes.push('is-stagger');
            var classAttr = classes.length ? ' class="' + classes.join(' ') + '"' : '';
            var styleAttr = (animate && idx < 25) ? ' style="--i:' + idx + '"' : '';
            var code = hasQuery ? highlight(it.item_code, query) : escapeHtml(it.item_code);
            var name = hasQuery ? highlight(it.item_name, query) : escapeHtml(it.item_name);
            var qty = Number(it.qty || 0);
            var val = Number(it.value || 0);
            var pct = Number(it.percent_of_total || 0);
            var qtyCls = 'cd-modal__num text-end' + (qty < 0 ? ' is-negative' : '');
            var valCls = 'cd-modal__num text-end' + (val < 0 ? ' is-negative' : '');
            var fillCls = 'cd-modal__pct-fill' + (val < 0 ? ' is-negative' : '');
            var img = escapeHtml(it.image_url || placeholderUrl);
            var imgFallback = escapeHtml(placeholderUrl);
            return '<tr' + classAttr + styleAttr + '>'
                + '<td class="cd-modal__img-cell">'
                +   '<img src="' + img + '" alt="" loading="lazy" class="cd-modal__img"'
                +   ' onerror="this.onerror=null;this.src=\\'' + imgFallback + '\\';">'
                + '</td>'
                + '<td>'
                +   '<div class="cd-modal__item-meta">'
                +     '<span class="cd-modal__item-code">' + code + '</span>'
                +     '<span class="cd-modal__item-name">' + name + '</span>'
                +   '</div>'
                + '</td>'
                + '<td class="' + qtyCls + '">' + fmtQty(qty) + ' <span class="cd-modal__unit">' + escapeHtml(it.unit_name || '') + '</span></td>'
                + '<td class="' + valCls + '">'
                +   '<span class="cd-modal__value-stack">'
                +     '<span class="cd-modal__value-num">' + fmt(val) + '</span>'
                +     '<span class="cd-modal__pct-bar" title="' + pct.toFixed(1) + '% ของยอดรวม">'
                +       '<span class="' + fillCls + '" style="--pct:' + pct + '%"></span>'
                +     '</span>'
                +   '</span>'
                + '</td>'
                + '</tr>';
        }).join('');
        var tbodyCls = animate ? '' : ' class="is-filter-fade"';
        return '<table class="table table-hover table-sm align-middle mb-0">'
            + '<thead><tr>'
            + '<th class="cd-modal__img-cell"></th>'
            + '<th>รายการวัสดุ</th>'
            + '<th class="cd-modal__num text-end">จำนวน</th>'
            + '<th class="cd-modal__num text-end">มูลค่า (บาท)</th>'
            + '</tr></thead>'
            + '<tbody' + tbodyCls + '>' + rows + '</tbody>'
            + '</table>';
    }

    function applyFilter(animate){
        var q = currentQuery.trim().toLowerCase();
        var filtered;
        if (!q) {
            filtered = currentItems;
        } else {
            filtered = currentItems.filter(function(it){
                return (it.item_code || '').toLowerCase().indexOf(q) !== -1
                    || (it.item_name || '').toLowerCase().indexOf(q) !== -1;
            });
        }
        $('#cd-body').scrollTop(0).html(renderTable(filtered, q, !!animate));
        var meta = '';
        if (q) {
            meta = 'พบ ' + filtered.length.toLocaleString('en-US') + ' / ' + currentItems.length.toLocaleString('en-US') + ' รายการ';
        }
        $('#cd-filter-meta').text(meta);
        $('#cd-search-clear').prop('hidden', !q);
    }

    function renderError(message){
        return '<div class="cd-modal__error">'
            + '<i class="bi bi-exclamation-triangle" aria-hidden="true"></i>'
            + '<p>' + escapeHtml(message || 'โหลดข้อมูลไม่สำเร็จ') + '</p>'
            + '<button type="button" class="btn btn-outline-secondary btn-sm" id="cd-retry">ลองอีกครั้ง</button>'
            + '</div>';
    }

    function load(kind, category, trigger){
        lastTrigger = trigger || null;
        currentItems = [];
        currentQuery = '';
        $('#cd-search').val('');
        $('#cd-search-clear').prop('hidden', true);
        $('#cd-filter').attr('hidden', true);
        $('#cd-filter-meta').text('');
        var \$body = $('#cd-body').attr('aria-busy', 'true').scrollTop(0).html(renderSkeleton());
        $('#cd-stat-strip').attr('hidden', true);
        $('#cd-kind-label').text('กำลังโหลด...');
        $('#cd-category-label').text('—');
        $('#cd-period').text('—');
        $('#cd-warehouse').text('—');
        $('#cd-kind-badge').find('i').attr('class', 'bi ' + (dirIconByKind[kind] || 'bi-list-ul'));

        if (currentXhr && currentXhr.readyState !== 4) {
            currentXhr.abort();
        }
        currentXhr = $.ajax({
            url: drillUrl,
            method: 'GET',
            dataType: 'json',
            data: {
                year: ctxYear,
                month: ctxMonth,
                warehouse_id: ctxWarehouse === null ? '' : ctxWarehouse,
                category: category,
                kind: kind
            }
        }).done(function(res){
            if (!res || !res.success) {
                \$body.attr('aria-busy', 'false').html(renderError((res && res.message) || 'โหลดข้อมูลไม่สำเร็จ'));
                return;
            }
            currentItems = Array.isArray(res.items) ? res.items : [];
            currentQuery = '';
            $('#cd-kind-label').text(res.meta.kind_label);
            $('#cd-category-label').text(res.meta.category_label);
            $('#cd-period').text(res.meta.period_label + ' (พ.ศ.)');
            $('#cd-warehouse').text(res.meta.warehouse_label);
            $('#cd-count').text(res.summary.count.toLocaleString('en-US'));
            var totalVal = Number(res.summary.total_value || 0);
            $('#cd-total').text(fmt(totalVal)).toggleClass('is-negative', totalVal < 0);
            $('#cd-stat-strip').removeAttr('hidden');
            \$body.attr('aria-busy', 'false');
            applyFilter(true);  // stagger reveal on initial load only
            if (currentItems.length > 0) {
                $('#cd-filter').removeAttr('hidden');
            }
        }).fail(function(xhr, status){
            if (status === 'abort') return;
            \$body.attr('aria-busy', 'false').html(renderError('โหลดข้อมูลไม่สำเร็จ'));
        });
    }

    // Search input — debounced
    var searchTimer = null;
    $(document).on('input', '#cd-search', function(){
        var val = this.value;
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function(){
            currentQuery = val;
            applyFilter();
        }, 120);
    });
    $(document).on('click', '#cd-search-clear', function(){
        $('#cd-search').val('').focus();
        currentQuery = '';
        applyFilter();
    });
    // Esc inside search → clear query first (don't close modal)
    $(document).on('keydown', '#cd-search', function(e){
        if (e.key === 'Escape' && this.value !== '') {
            e.stopPropagation();
            this.value = '';
            currentQuery = '';
            applyFilter();
        }
    });

    $(document).on('click', '.ms-report-summary .cell-drill', function(){
        var kind = this.getAttribute('data-kind');
        var category = this.getAttribute('data-category');
        if (!kind || !category) return;
        load(kind, category, this);
        if (modal) modal.show();
    });

    $(document).on('click', '#cd-retry', function(){
        if (lastTrigger) {
            var kind = lastTrigger.getAttribute('data-kind');
            var category = lastTrigger.getAttribute('data-category');
            if (kind && category) load(kind, category, lastTrigger);
        }
    });

    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function(){
            if (lastTrigger && typeof lastTrigger.focus === 'function') {
                lastTrigger.focus();
            }
        });
    }
})();
JS
);
?>

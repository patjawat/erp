<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetailSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ประวัติการสอบเทียบ';
$this->params['breadcrumbs'][] = $this->title;

/** @var \app\modules\am\models\AssetDetail[] $models */
$models = $dataProvider->getModels();
$total  = $dataProvider->getTotalCount();

// ---- aggregate stats (across all pages) ----
$allQuery = clone $dataProvider->query;
$allRows  = $allQuery->all();
$passCount = 0;
$failCount = 0;
$latestDate = null;
foreach ($allRows as $r) {
    if ($r->cal_result === 'pass') {
        $passCount++;
    } elseif ($r->cal_result === 'fail') {
        $failCount++;
    }
    $d = $r->date_end ?: $r->date_start;
    if ($d && (!$latestDate || $d > $latestDate)) {
        $latestDate = $d;
    }
}

// ---- group current-page records by Thai (พ.ศ.) year, preserving original order ----
$grouped = [];
$groupOrder = [];
foreach ($models as $key => $item) {
    $d = $item->date_end ?: $item->date_start;
    if ($d) {
        $year = (int) substr($d, 0, 4) + 543;
    } else {
        $year = 'ไม่ระบุปี';
    }
    if (!isset($grouped[$year])) {
        $grouped[$year] = [];
        $groupOrder[] = $year;
    }
    $grouped[$year][] = ['_idx' => $key, 'model' => $item];
}
// numeric desc, non-numeric last
usort($groupOrder, function ($a, $b) {
    $na = is_numeric($a); $nb = is_numeric($b);
    if ($na && $nb) return ((int)$b) <=> ((int)$a);
    if ($na) return -1;
    if ($nb) return 1;
    return strcmp((string)$a, (string)$b);
});

// helpers
$riskMeta = [
    'L' => ['label' => 'ต่ำ',   'tone' => 'success'],
    'M' => ['label' => 'กลาง', 'tone' => 'warning'],
    'H' => ['label' => 'สูง',  'tone' => 'danger'],
];
$getRisk = static function ($item) {
    $dj = $item->data_json;
    if (is_string($dj)) {
        $dj = json_decode($dj, true) ?: [];
    }
    if (is_array($dj) && isset($dj['risk_level']) && $dj['risk_level'] !== '') {
        return (string) $dj['risk_level'];
    }
    return null;
};
$getRemark = static function ($item) {
    if (is_array($item->data_json) && !empty($item->data_json['remark'])) {
        return (string) $item->data_json['remark'];
    }
    if (is_string($item->data_json)) {
        $dj = json_decode($item->data_json, true) ?: [];
        return (string) ($dj['remark'] ?? '');
    }
    return '';
};
$iconClean = '<i data-lucide="circle-plus"></i> ';
$createUrl = ['create', 'code' => $searchModel->code, 'title' => $iconClean . ' แบบฟอร์มบันทึกข้อมูลการสอบเทียบ'];

$offsetBase = ($dataProvider->pagination?->offset ?? 0);
$rowIndex = 0;
?>

<?php
$css = <<<CSS
/* Local Enterprise tokens */
.cal-history {
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
    --success-soft: rgba(21, 128, 61, 0.08);
    --success-line: rgba(21, 128, 61, 0.22);
    --warning: #b45309;
    --warning-soft: rgba(180, 83, 9, 0.08);
    --warning-line: rgba(180, 83, 9, 0.22);
    --danger: #b91c1c;
    --danger-soft: rgba(185, 28, 28, 0.08);
    --danger-line: rgba(185, 28, 28, 0.22);
    --radius: 10px;
    --radius-sm: 8px;
    --radius-xs: 6px;
    --shadow-1: 0 1px 2px rgba(15,23,42,.04), 0 1px 1px rgba(15,23,42,.03);
    --ease: cubic-bezier(0.16, 1, 0.3, 1);
    --t-fast: 120ms var(--ease);
    --t-mid: 180ms var(--ease);

    color: var(--ink-1);
}

/* Hero */
.cal-history__hero {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
    gap: 0.75rem; margin-bottom: 1rem;
}
.cal-history__title {
    display: inline-flex; align-items: center; gap: 0.5rem;
    font-weight: 600; font-size: 0.95rem;
    color: var(--ink-1); margin: 0;
}
.cal-history__title i { color: var(--primary); width: 1.05rem; height: 1.05rem; }
.cal-history__count {
    display: inline-flex; align-items: center;
    padding: 0.1rem 0.5rem;
    background: var(--surface-3); color: var(--ink-2);
    border-radius: var(--radius-xs);
    font-size: 0.74rem; font-weight: 600;
}

/* KPI strip */
.cal-kpis {
    display: grid; grid-template-columns: repeat(2, minmax(0,1fr));
    gap: 0.5rem; margin-bottom: 1rem;
}
@media (min-width: 768px) { .cal-kpis { grid-template-columns: repeat(4, minmax(0,1fr)); } }
.cal-kpi {
    display: flex; align-items: center; gap: 0.6rem;
    padding: 0.65rem 0.8rem;
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    transition: border-color var(--t-fast);
}
.cal-kpi:hover { border-color: var(--line-strong); }
.cal-kpi__icon {
    width: 1.85rem; height: 1.85rem;
    border-radius: var(--radius-xs);
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    background: var(--surface-3);
    color: var(--ink-2);
    border: 1px solid var(--line);
}
.cal-kpi__icon i { width: 0.95rem; height: 0.95rem; }
.cal-kpi--pass   .cal-kpi__icon { color: var(--success); }
.cal-kpi--fail   .cal-kpi__icon { color: var(--danger); }
.cal-kpi--latest .cal-kpi__icon { color: var(--primary); }
.cal-kpi__body  { line-height: 1.2; min-width: 0; }
.cal-kpi__value {
    font-size: 1rem; font-weight: 600; color: var(--ink-1);
    letter-spacing: -0.01em;
    font-variant-numeric: tabular-nums;
}
.cal-kpi__value--date { font-size: 0.88rem; }
.cal-kpi__label {
    font-size: 0.74rem; color: var(--ink-3); font-weight: 500; margin-top: 0.05rem;
}

/* Empty state */
.cal-empty {
    text-align: center; padding: 2.75rem 1rem;
    border: 1px dashed var(--line-strong);
    border-radius: var(--radius);
    background: var(--surface-2);
}
.cal-empty__icon {
    width: 3rem; height: 3rem; margin: 0 auto 0.75rem;
    background: var(--surface-3);
    color: var(--ink-3);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    display: inline-flex; align-items: center; justify-content: center;
}
.cal-empty__icon i { width: 1.5rem; height: 1.5rem; }
.cal-empty__title    { font-weight: 600; color: var(--ink-1); font-size: 0.95rem; margin: 0 0 0.25rem; }
.cal-empty__subtitle { color: var(--ink-3); font-size: 0.85rem; margin: 0 0 0.85rem; max-width: 32rem; margin-left:auto; margin-right:auto; }

/* Table shell */
.cal-table-shell {
    background: var(--surface);
    border: 1px solid var(--line);
    border-radius: var(--radius);
    box-shadow: var(--shadow-1);
    overflow: hidden;
}
.cal-table-scroll {
    overflow-x: auto;
    /* allow column dropdowns to portal out if any */
}

.cal-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    margin-bottom: 0;
    font-size: 0.86rem;
    color: var(--ink-1);
    background: var(--surface);
}
.cal-table thead th {
    font-size: 0.74rem;
    font-weight: 600;
    color: var(--ink-2);
    background: var(--surface-2);
    border-bottom: 1px solid var(--line);
    text-align: left;
    padding: 0.7rem 0.85rem;
    white-space: nowrap;
    letter-spacing: 0;
    position: sticky;
    top: 0;
    z-index: 1;
}
.cal-table thead th.is-center { text-align: center; }
.cal-table thead th.is-end    { text-align: right; }
.cal-table thead th .sub {
    display: block;
    font-weight: 400;
    font-size: 0.7rem;
    color: var(--ink-4);
    margin-top: 0.05rem;
}

.cal-table tbody td {
    padding: 0.7rem 0.85rem;
    border-bottom: 1px solid var(--line);
    vertical-align: middle;
    color: var(--ink-1);
    font-variant-numeric: tabular-nums;
}
.cal-table tbody td.is-center { text-align: center; }
.cal-table tbody td.is-end    { text-align: right; }
.cal-table tbody td.is-num    { font-variant-numeric: tabular-nums; }
.cal-table tbody td.is-muted  { color: var(--ink-3); }

/* Year group separator row */
.cal-table tbody tr.cal-table__group td {
    background: var(--surface-2);
    border-bottom: 1px solid var(--line);
    padding: 0.45rem 0.85rem;
    font-size: 0.78rem;
    color: var(--ink-2);
    font-weight: 600;
}
.cal-table tbody tr.cal-table__group .badge-year {
    display: inline-flex; align-items: center; gap: 0.3rem;
    background: var(--surface-3); color: var(--ink-2);
    padding: 0.1rem 0.45rem;
    border-radius: var(--radius-xs);
    font-size: 0.7rem;
    border: 1px solid var(--line);
    letter-spacing: 0.02em;
}
.cal-table tbody tr.cal-table__group .group-meta {
    color: var(--ink-3); font-weight: 500; font-size: 0.76rem;
    margin-left: 0.6rem;
}
.cal-table tbody tr.cal-table__group .group-meta .pass-n { color: var(--success); }
.cal-table tbody tr.cal-table__group .group-meta .fail-n { color: var(--danger); }

/* Data rows */
.cal-table tbody tr.cal-table__row {
    transition: background-color var(--t-fast);
    opacity: 0;
    transform: translateY(2px);
    animation: calRowIn 160ms var(--ease) forwards;
    animation-delay: calc(var(--i, 0) * 18ms);
}
.cal-table tbody tr.cal-table__row:hover {
    background: var(--surface-hover);
}
.cal-table tbody tr.cal-table__row:last-child td { border-bottom: 0; }
.cal-table tbody tr.cal-table__row:focus-within {
    background: var(--primary-soft);
}
@keyframes calRowIn { to { opacity: 1; transform: none; } }
@media (prefers-reduced-motion: reduce) {
    .cal-table tbody tr.cal-table__row { opacity: 1; transform: none; animation: none; }
}

/* Cell content */
.cell-num {
    color: var(--ink-3);
    font-size: 0.78rem;
    font-weight: 500;
}
.cell-date {
    display: inline-flex; flex-direction: column; line-height: 1.2;
}
.cell-date__main { font-weight: 500; color: var(--ink-1); }
.cell-date__sub  { font-size: 0.74rem; color: var(--ink-3); }

.cell-provider { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--ink-2); }
.cell-provider i { width: 0.95rem; height: 0.95rem; color: var(--ink-4); }

.cell-person { display: inline-flex; align-items: center; gap: 0.35rem; color: var(--ink-2); }
.cell-person i { width: 0.95rem; height: 0.95rem; color: var(--ink-4); }

.cell-remark {
    color: var(--ink-2);
    font-size: 0.83rem;
    max-width: 28rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
    line-height: 1.4;
}
.cell-remark--empty { color: var(--ink-4); font-style: normal; }

/* Pills (status / risk) */
.cal-pill {
    display: inline-flex; align-items: center; gap: 0.3rem;
    padding: 0.16rem 0.5rem;
    border-radius: 999px;
    font-size: 0.74rem;
    font-weight: 600;
    border: 1px solid var(--line);
    background: var(--surface);
    color: var(--ink-2);
    line-height: 1.2;
    white-space: nowrap;
}
.cal-pill i { width: 0.82rem; height: 0.82rem; }
.cal-pill--pass {
    background: var(--success-soft); color: var(--success); border-color: var(--success-line);
}
.cal-pill--fail {
    background: var(--danger-soft); color: var(--danger); border-color: var(--danger-line);
}
.cal-pill--pending {
    background: var(--surface-3); color: var(--ink-3); border-color: var(--line);
}
.cal-pill--risk-success {
    background: var(--success-soft); color: var(--success); border-color: var(--success-line);
}
.cal-pill--risk-warning {
    background: var(--warning-soft); color: var(--warning); border-color: var(--warning-line);
}
.cal-pill--risk-danger {
    background: var(--danger-soft); color: var(--danger); border-color: var(--danger-line);
}
.cal-pill--empty {
    color: var(--ink-4);
    background: transparent;
    border: 1px dashed var(--line);
}

/* Action buttons in row */
.cal-table .row-actions {
    display: inline-flex; align-items: center; gap: 0.15rem;
    justify-content: flex-end;
}
.cal-table .row-actions .btn {
    padding: 0.28rem 0.45rem; line-height: 1;
    border-color: var(--line);
    color: var(--ink-2);
    background: var(--surface);
    transition: background-color var(--t-fast), border-color var(--t-fast), color var(--t-fast);
}
.cal-table .row-actions .btn:hover {
    background: var(--surface-hover); border-color: var(--line-strong); color: var(--ink-1);
}
.cal-table .row-actions .btn-outline-primary { color: var(--primary); }
.cal-table .row-actions .btn-outline-primary:hover {
    background: var(--primary-soft); border-color: var(--primary-line); color: var(--primary-ink);
}
.cal-table .row-actions .btn-outline-danger { color: var(--danger); }
.cal-table .row-actions .btn-outline-danger:hover {
    background: var(--danger-soft); border-color: var(--danger-line); color: var(--danger);
}
.cal-table .row-actions .btn:focus-visible {
    outline: none; box-shadow: 0 0 0 3px var(--primary-soft); border-color: var(--primary-line);
}

/* Footer of shell */
.cal-table-footer {
    display: flex; align-items: center; justify-content: space-between;
    gap: 0.75rem;
    padding: 0.55rem 0.85rem;
    background: var(--surface-2);
    border-top: 1px solid var(--line);
    font-size: 0.78rem;
    color: var(--ink-3);
}
.cal-table-footer .pagination {
    margin: 0; gap: 0.15rem;
}
.cal-table-footer .pagination .page-link {
    color: var(--ink-2); border-color: var(--line);
    font-size: 0.78rem;
    padding: 0.25rem 0.55rem;
}
.cal-table-footer .pagination .page-item.active .page-link {
    background: var(--primary); border-color: var(--primary); color: #ffffff;
}
.cal-table-footer .pagination .page-link:focus {
    box-shadow: 0 0 0 3px var(--primary-soft);
}

/* Column min-widths for readable scroll */
@media (max-width: 991.98px) {
    .cal-table { min-width: 920px; }
}
CSS;
$this->registerCss($css, [], 'cal-history-css');
?>

<div class="cal-history">

    <!-- Hero -->
    <div class="cal-history__hero">
        <h6 class="cal-history__title">
            <i data-lucide="calendar-sync"></i>
            <?= Html::encode($this->title) ?>
            <?php if ($total > 0): ?>
                <span class="cal-history__count"><?= number_format($total) ?> ครั้ง</span>
            <?php endif; ?>
        </h6>
        <?= Html::a('<i data-lucide="circle-plus"></i> บันทึกการสอบเทียบ', $createUrl, [
            'class' => 'btn btn-primary btn-sm open-modal d-inline-flex align-items-center gap-1',
            'data' => ['size' => 'modal-lg'],
        ]) ?>
    </div>

    <!-- KPI strip -->
    <?php if ($total > 0): ?>
        <div class="cal-kpis">
            <div class="cal-kpi">
                <div class="cal-kpi__icon"><i data-lucide="list-checks"></i></div>
                <div class="cal-kpi__body">
                    <div class="cal-kpi__value"><?= number_format($total) ?></div>
                    <div class="cal-kpi__label">ทั้งหมด</div>
                </div>
            </div>
            <div class="cal-kpi cal-kpi--pass">
                <div class="cal-kpi__icon"><i data-lucide="circle-check"></i></div>
                <div class="cal-kpi__body">
                    <div class="cal-kpi__value"><?= number_format($passCount) ?></div>
                    <div class="cal-kpi__label">ผ่าน (Pass)</div>
                </div>
            </div>
            <div class="cal-kpi cal-kpi--fail">
                <div class="cal-kpi__icon"><i data-lucide="circle-x"></i></div>
                <div class="cal-kpi__body">
                    <div class="cal-kpi__value"><?= number_format($failCount) ?></div>
                    <div class="cal-kpi__label">ไม่ผ่าน (Fail)</div>
                </div>
            </div>
            <div class="cal-kpi cal-kpi--latest">
                <div class="cal-kpi__icon"><i data-lucide="calendar-clock"></i></div>
                <div class="cal-kpi__body">
                    <div class="cal-kpi__value cal-kpi__value--date">
                        <?= $latestDate ? Yii::$app->thaiDate->toThaiDate($latestDate, false, true) : 'ยังไม่มีข้อมูล' ?>
                    </div>
                    <div class="cal-kpi__label">ครั้งล่าสุด</div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php Pjax::begin(['id' => 'pjax-cal-history', 'timeout' => false]); ?>

    <?php if ($total === 0): ?>
        <!-- Empty state -->
        <div class="cal-empty">
            <div class="cal-empty__icon"><i data-lucide="calendar-sync" style="width:2rem;height:2rem;"></i></div>
            <h6 class="cal-empty__title">ยังไม่มีประวัติการสอบเทียบ</h6>
            <p class="cal-empty__subtitle">เริ่มบันทึกครั้งแรกเพื่อให้ระบบช่วยติดตามรอบการสอบเทียบและประเมินความเสี่ยงในระยะยาว</p>
            <?= Html::a('<i data-lucide="circle-plus"></i> บันทึกการสอบเทียบครั้งแรก', $createUrl, [
                'class' => 'btn btn-primary btn-sm open-modal d-inline-flex align-items-center gap-1',
                'data' => ['size' => 'modal-lg'],
            ]) ?>
        </div>
    <?php else: ?>
        <!-- Table -->
        <div class="cal-table-shell">
            <div class="cal-table-scroll">
                <table class="cal-table" role="table" aria-label="ประวัติการสอบเทียบ">
                    <thead>
                        <tr>
                            <th scope="col" class="is-center" style="width:48px">#</th>
                            <th scope="col" style="width:160px">วันที่ดำเนินการ<span class="sub">วันตามแผน</span></th>
                            <th scope="col" style="width:170px">ผู้ให้บริการ</th>
                            <th scope="col" style="width:130px">ผลการสอบเทียบ</th>
                            <th scope="col" style="width:140px">ระดับความเสี่ยง</th>
                            <th scope="col" style="width:170px">ผู้บันทึก</th>
                            <th scope="col">หมายเหตุ</th>
                            <th scope="col" class="is-end" style="width:140px">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groupOrder as $year):
                            $items = $grouped[$year];
                            $yearPass = 0; $yearFail = 0;
                            foreach ($items as $row) {
                                if ($row['model']->cal_result === 'pass') $yearPass++;
                                elseif ($row['model']->cal_result === 'fail') $yearFail++;
                            }
                        ?>
                            <tr class="cal-table__group" aria-label="กลุ่มปี <?= Html::encode((string) $year) ?>">
                                <td colspan="8">
                                    <?php if (is_numeric($year)): ?>
                                        <span class="badge-year">พ.ศ.</span>
                                    <?php endif; ?>
                                    <strong><?= Html::encode((string) $year) ?></strong>
                                    <span class="group-meta">
                                        <?= count($items) ?> ครั้ง
                                        <?php if ($yearPass): ?> · <span class="pass-n">ผ่าน <?= $yearPass ?></span><?php endif; ?>
                                        <?php if ($yearFail): ?> · <span class="fail-n">ไม่ผ่าน <?= $yearFail ?></span><?php endif; ?>
                                    </span>
                                </td>
                            </tr>

                            <?php foreach ($items as $row):
                                $item = $row['model'];
                                $rowIndex++;
                                $isPass = $item->cal_result === 'pass';
                                $isFail = $item->cal_result === 'fail';
                                $effectiveDate = $item->date_end ?: null;
                                $planDate = $item->date_start ?: null;
                                $providerLabel = $item->provider_type === 'external' ? 'หน่วยงานภายนอก' : 'ดำเนินการเอง';
                                $providerIcon  = $item->provider_type === 'external' ? 'building-2' : 'hospital';
                                $risk = $getRisk($item);
                                $remark = $getRemark($item);
                                $createdName = $item->createdBy->employee->fullname ?? null;
                            ?>
                                <tr class="cal-table__row" style="--i:<?= $rowIndex ?>;">
                                    <td class="is-center is-muted"><span class="cell-num"><?= $offsetBase + $rowIndex ?></span></td>
                                    <td>
                                        <span class="cell-date">
                                            <span class="cell-date__main">
                                                <?= $effectiveDate ? Yii::$app->thaiDate->toThaiDate($effectiveDate, false, true) : '<span class="text-muted">ยังไม่ดำเนินการ</span>' ?>
                                            </span>
                                            <?php if ($planDate && $planDate !== $effectiveDate): ?>
                                                <span class="cell-date__sub">ตามแผน <?= Yii::$app->thaiDate->toThaiDate($planDate, false, true) ?></span>
                                            <?php endif; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="cell-provider"><?= Html::encode($providerLabel) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($isPass): ?>
                                            <span class="cal-pill cal-pill--pass">ผ่าน</span>
                                        <?php elseif ($isFail): ?>
                                            <span class="cal-pill cal-pill--fail">ไม่ผ่าน</span>
                                        <?php else: ?>
                                            <span class="cal-pill cal-pill--pending">รอผล</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($risk && isset($riskMeta[$risk])):
                                            $rm = $riskMeta[$risk];
                                        ?>
                                            <span class="cal-pill cal-pill--risk-<?= $rm['tone'] ?>"><?= Html::encode($rm['label']) ?></span>
                                        <?php else: ?>
                                            <span class="cal-pill cal-pill--empty">ไม่ระบุ</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($createdName): ?>
                                            <span class="cell-person"><?= Html::encode($createdName) ?></span>
                                        <?php else: ?>
                                            <span class="cell-remark--empty">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($remark !== ''): ?>
                                            <div class="cell-remark" title="<?= Html::encode($remark) ?>"><?= Html::encode($remark) ?></div>
                                        <?php else: ?>
                                            <span class="cell-remark--empty">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="is-end">
                                        <div class="row-actions">
                                            <?= Html::a('<i class="fa-regular fa-eye"></i>', Url::to(['/am/calibration/view', 'id' => $item->id]), [
                                                'class' => 'btn btn-sm btn-outline-secondary open-modal',
                                                'data-size' => 'modal-lg',
                                                'title' => 'ดูรายละเอียด',
                                            ]) ?>
                                            <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>', Url::to(['/am/calibration/update', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไขการสอบเทียบ']), [
                                                'class' => 'btn btn-sm btn-outline-primary open-modal',
                                                'data-size' => 'modal-lg',
                                                'title' => 'แก้ไข',
                                            ]) ?>
                                            <?= Html::a('<i class="fa-regular fa-trash-can"></i>', Url::to(['/am/calibration/delete', 'id' => $item->id]), [
                                                'class' => 'btn btn-sm btn-outline-danger delete-item',
                                                'title' => 'ลบ',
                                            ]) ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($dataProvider->pagination && $dataProvider->pagination->pageCount > 1): ?>
                <div class="cal-table-footer">
                    <span>
                        แสดง <?= $offsetBase + 1 ?>–<?= $offsetBase + count($models) ?>
                        จาก <?= number_format($total) ?> รายการ
                    </span>
                    <?= \yii\bootstrap5\LinkPager::widget([
                        'pagination' => $dataProvider->pagination,
                        'firstPageLabel' => 'หน้าแรก',
                        'lastPageLabel' => 'หน้าสุดท้าย',
                        'options' => ['class' => 'pagination pagination-sm mb-0'],
                    ]) ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php Pjax::end(); ?>
</div>

<?php
$js = <<<JS
(function() {
    if (typeof lucide !== 'undefined' && lucide.createIcons) {
        try { lucide.createIcons(); } catch (e) {}
    }
})();
JS;
$this->registerJs($js, View::POS_END);
?>

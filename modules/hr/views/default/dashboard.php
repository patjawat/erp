<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

$this->title = 'Dashboard บุคลากร (มุมมองผู้บริหาร)';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = 'Dashboard';

$totalCount = (int)($totalCount ?? 0);
$countMale = (int)($countMale ?? 0);
$countFemale = (int)($countFemale ?? 0);
$organizationDiagramCount = (int)($organizationDiagramCount ?? 0);
$teamGroupCount = (int)($teamGroupCount ?? 0);
$genderRatio = $totalCount > 0 ? (round($countMale / $totalCount * 100) . ' : ' . round($countFemale / $totalCount * 100)) : '—';
$renderDashboardFilterLinks = static function (
    string $id,
    array $labels,
    array $values,
    string $filterKey,
    array $codes,
    string $ariaLabel
): string {
    return Html::tag('span', $ariaLabel, [
        'id' => $id,
        'style' => 'position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;',
    ]);

};
$genderLabels = ['ชาย', 'หญิง'];
$genderValues = [$countMale, $countFemale];
$genCounts = $genCounts ?? [];
$genLabels = array_keys($genCounts);
$genValues = array_values($genCounts);
$workgroupRows = $workgroupRows ?? [];
$workgroupLabels = array_map(static function ($row) {
    return (string)($row['name'] ?? '');
}, $workgroupRows);
$workgroupValues = array_map(static function ($row) {
    return array_sum(array_map('intval', $row['data'] ?? []));
}, $workgroupRows);
$workgroupCodes = array_map(static function ($row) {
    return (string)($row['code'] ?? $row['name'] ?? '');
}, $workgroupRows);
$ageMaleTotal = array_sum(array_map('intval', $ageMale ?? []));
$ageFemaleTotal = array_sum(array_map('intval', $ageFemale ?? []));

$this->registerCss(<<<'CSS'
.hr-dashboard {
    --hr-primary: var(--primary, #0d6efd);
    --hr-blue: #3b82f6;
    --hr-sky: #0ea5e9;
    --hr-teal: #14b8a6;
    --hr-violet: #8b5cf6;
    --hr-rose: #f43f5e;
    --hr-amber: #f59e0b;
    --hr-success: #15803d;
    --hr-warning: #b45309;
    --hr-danger: #b91c1c;
    --hr-ink-2: #4a5568;
    --hr-ink-3: #64748b;
    --hr-surface: #ffffff;
    --hr-surface-1: #f7f9fc;
    --hr-surface-2: #eef2f7;
    --hr-line: rgba(15, 23, 42, .10);
}

.hr-dashboard .card {
    border-radius: 8px;
}

.hr-dashboard-summary-card {
    border: 1px solid var(--hr-line) !important;
    background: linear-gradient(90deg, rgba(59, 130, 246, .09), rgba(20, 184, 166, .08) 48%, var(--hr-surface));
}

.hr-dashboard-filter-card {
    background: var(--hr-surface-1);
    border: 1px solid var(--hr-line) !important;
}

.hr-dashboard-kpi-grid {
    --kpi-accent: var(--hr-blue);
    --kpi-tint: rgba(59, 130, 246, .10);
}

.hr-dashboard-kpi-grid > .col:nth-child(2) {
    --kpi-accent: var(--hr-teal);
    --kpi-tint: rgba(20, 184, 166, .11);
}

.hr-dashboard-kpi-grid > .col:nth-child(3) {
    --kpi-accent: var(--hr-violet);
    --kpi-tint: rgba(139, 92, 246, .11);
}

.hr-dashboard-kpi-grid > .col:nth-child(4) {
    --kpi-accent: var(--hr-amber);
    --kpi-tint: rgba(245, 158, 11, .12);
}

.hr-dashboard-kpi-grid > .col:nth-child(5) {
    --kpi-accent: var(--hr-sky);
    --kpi-tint: rgba(14, 165, 233, .10);
}

.hr-dashboard-kpi-grid > .col:nth-child(6) {
    --kpi-accent: var(--hr-rose);
    --kpi-tint: rgba(244, 63, 94, .10);
}

.hr-dashboard-kpi-grid > .col:nth-child(7) {
    --kpi-accent: var(--hr-success);
    --kpi-tint: rgba(21, 128, 61, .10);
}

/* การ์ด KPI ทุกใบมีโครงเดียวกัน จึงสูงเท่ากันเสมอ และกดได้เพื่อเปิดรายการตรวจสอบ */
.hr-dashboard-kpi-grid .hr-kpi {
    display: flex;
    width: 100%;
    height: 100%;
    min-height: 138px;
    flex-direction: column;
    align-items: stretch;
    padding: .85rem 1rem 1rem;
    border: 1px solid var(--hr-line);
    border-radius: 8px;
    background: var(--hr-surface);
    text-align: start;
    cursor: pointer;
    transition: border-color var(--hr-t, 140ms) var(--hr-ease, cubic-bezier(.16, 1, .3, 1)),
                box-shadow var(--hr-t, 140ms) var(--hr-ease, cubic-bezier(.16, 1, .3, 1)),
                transform var(--hr-t, 140ms) var(--hr-ease, cubic-bezier(.16, 1, .3, 1));
}

.hr-dashboard-kpi-grid .hr-kpi:hover {
    border-color: var(--kpi-accent);
    box-shadow: 0 6px 18px rgba(15, 23, 42, .07);
}

.hr-dashboard-kpi-grid .hr-kpi:focus-visible {
    outline: 2px solid var(--kpi-accent);
    outline-offset: 2px;
}

.hr-dashboard-kpi-grid .hr-kpi:active {
    transform: translateY(1px);
}

.hr-dashboard-kpi-grid .hr-kpi[aria-expanded="true"] {
    border-color: var(--kpi-accent);
    box-shadow: inset 0 0 0 1px var(--kpi-accent);
}

.hr-kpi__top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: .6rem;
}

.hr-kpi__icon {
    display: inline-grid;
    width: 40px;
    height: 40px;
    flex: 0 0 auto;
    place-items: center;
    border-radius: 50rem;
    background: var(--kpi-tint);
    color: var(--kpi-accent);
    font-size: 1.1rem;
}

.hr-kpi__chevron {
    color: var(--hr-ink-2);
    font-size: .8rem;
    transition: transform 140ms cubic-bezier(.16, 1, .3, 1), color 140ms linear;
}

.hr-kpi:hover .hr-kpi__chevron {
    color: var(--kpi-accent);
}

.hr-kpi[aria-expanded="true"] .hr-kpi__chevron {
    color: var(--kpi-accent);
    transform: rotate(180deg);
}

.hr-kpi__value {
    display: block;
    margin-top: auto;
    color: var(--bs-body-color, #212529);
    font-size: 1.75rem;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
    line-height: 1.2;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* ป้ายชื่อการ์ดใช้สีข้อความปกติ ไม่ใช้สี accent เพราะ contrast ไม่ผ่านเกณฑ์อ่านง่าย
   สี accent อยู่ที่ไอคอนและเส้นขอบตอน hover/เปิด ซึ่งเป็นองค์ประกอบที่ไม่ใช่ข้อความ */
.hr-kpi__label {
    display: block;
    margin-top: .2rem;
    color: var(--hr-ink-2);
    font-size: .86rem;
    font-weight: 500;
    line-height: 1.35;
}

/* แผงตรวจสอบรายการเบื้องหลังตัวเลข */
.hr-kpi-detail {
    border: 1px solid var(--hr-line);
    border-radius: 8px;
    background: var(--hr-surface);
    box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
    overflow: hidden;
}

.hr-kpi-detail[hidden] {
    display: none;
}

.hr-kpi-detail.is-open {
    animation: hrKpiDetailIn 180ms cubic-bezier(.16, 1, .3, 1) both;
}

@keyframes hrKpiDetailIn {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: none; }
}

.hr-kpi-detail__head {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: .5rem .75rem;
    padding: .7rem 1rem;
    border-bottom: 1px solid var(--hr-line);
    background: var(--hr-surface-1);
}

.hr-kpi-detail__heading {
    display: flex;
    min-width: 0;
    align-items: center;
    gap: .5rem;
}

.hr-kpi-detail__title {
    margin: 0;
    color: var(--hr-ink-2);
    font-size: .92rem;
    font-weight: 600;
    line-height: 1.35;
}

.hr-kpi-detail__title:focus {
    outline: none;
}

.hr-kpi-detail__count {
    flex: 0 0 auto;
    padding: .1rem .55rem;
    border-radius: 50rem;
    background: var(--hr-surface-2);
    color: var(--hr-ink-2);
    font-size: .75rem;
    font-variant-numeric: tabular-nums;
}

.hr-kpi-detail__tools {
    display: flex;
    align-items: center;
    gap: .5rem;
}

.hr-kpi-detail__search {
    width: 15rem;
    max-width: 45vw;
}

.hr-kpi-detail__body {
    max-height: 26rem;
    overflow: auto;
}

.hr-kpi-detail__table {
    font-size: .84rem;
}

.hr-kpi-detail__table thead th {
    position: sticky;
    top: 0;
    z-index: 1;
    border-bottom: 1px solid var(--hr-line);
    background: var(--hr-surface);
    color: var(--hr-ink-2);
    font-weight: 600;
    white-space: nowrap;
    cursor: pointer;
    user-select: none;
}

.hr-kpi-detail__table thead th:hover {
    color: var(--bs-body-color, #212529);
}

.hr-kpi-detail__table thead th[aria-sort="ascending"]::after {
    content: " \2191";
}

.hr-kpi-detail__table thead th[aria-sort="descending"]::after {
    content: " \2193";
}

.hr-kpi-detail__table tbody td {
    color: var(--bs-body-color, #212529);
    vertical-align: middle;
}

.hr-kpi-detail__table tbody td.is-num {
    font-variant-numeric: tabular-nums;
}

.hr-kpi-detail__table tbody tr:hover td {
    background: var(--hr-surface-1);
}

.hr-kpi-detail__empty {
    padding: 1.5rem 1rem;
    color: var(--hr-ink-3);
    font-size: .86rem;
    text-align: center;
}

.hr-dashboard-kpi-label,
.hr-dashboard-section-label {
    color: var(--hr-ink-2);
    font-weight: 600;
    line-height: 1.35;
}

.hr-dashboard-kpi-label {
    color: var(--kpi-accent, var(--hr-primary));
    font-size: .86rem;
    font-weight: 500;
    line-height: 1.35;
}

.hr-dashboard-section-label {
    font-size: .86rem;
}

.hr-dashboard-chart {
    min-height: 240px;
}

.hr-dashboard-chart--bar {
    min-height: 285px;
}

.hr-dashboard .card:has(.hr-dashboard-chart) {
    --chart-accent: var(--hr-blue);
    --chart-tint: rgba(59, 130, 246, .08);
    border: 1px solid var(--hr-line) !important;
    overflow: hidden;
    box-shadow: 0 10px 24px rgba(15, 23, 42, .08) !important;
}

.hr-dashboard .card:has(#dashboardGenerationPie) {
    --chart-accent: var(--hr-violet);
    --chart-tint: rgba(139, 92, 246, .09);
}

.hr-dashboard .card:has(#dashboardPositionTypePie) {
    --chart-accent: var(--hr-teal);
    --chart-tint: rgba(20, 184, 166, .09);
}

.hr-dashboard .card:has(#dashboardWorkgroupChart) {
    --chart-accent: var(--hr-amber);
    --chart-tint: rgba(245, 158, 11, .10);
}

.hr-dashboard .card:has(#dashboardAgeChart) {
    --chart-accent: var(--hr-rose);
    --chart-tint: rgba(244, 63, 94, .08);
}

.hr-dashboard .card:has(#dashboardPositionNameChart) {
    --chart-accent: var(--hr-sky);
    --chart-tint: rgba(14, 165, 233, .08);
}

.hr-dashboard .card:has(#dashboardServiceBandChart) {
    --chart-accent: var(--hr-success);
    --chart-tint: rgba(21, 128, 61, .08);
}

.hr-dashboard .card:has(#dashboardDepartmentChart) {
    --chart-accent: var(--hr-violet);
    --chart-tint: rgba(139, 92, 246, .08);
}

.hr-dashboard .card:has(.hr-dashboard-chart) .card-header {
    display: flex;
    min-height: 58px;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: .35rem .65rem;
    background: linear-gradient(90deg, var(--chart-tint), var(--hr-surface));
}

.hr-dashboard .card:has(.hr-dashboard-chart) .card-header h6 {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    color: #1f2937;
    line-height: 1.25;
}

.hr-dashboard .card:has(.hr-dashboard-chart) .card-header h6::before {
    content: "";
    width: .52rem;
    height: .52rem;
    flex: 0 0 auto;
    border-radius: 50%;
    background: var(--chart-accent);
    box-shadow: 0 0 0 4px var(--chart-tint);
}

.hr-dashboard .card:has(.hr-dashboard-chart) .card-header .small {
    color: var(--hr-ink-3) !important;
    font-size: .74rem;
}

.hr-dashboard .card:has(.hr-dashboard-chart) .card-body {
    padding-top: .8rem !important;
}

.hr-chart-tooltip {
    min-width: 220px;
    max-width: 280px;
    border: 1px solid var(--hr-line);
    border-radius: 8px;
    background: var(--hr-surface);
    box-shadow: 0 10px 24px rgba(15, 23, 42, .14);
    color: var(--hr-ink-2);
    padding: .75rem;
}

.hr-chart-tooltip__eyebrow {
    color: var(--hr-ink-3);
    font-size: .72rem;
    line-height: 1.25;
}

.hr-chart-tooltip__title {
    margin-top: .1rem;
    color: var(--hr-ink-2);
    font-size: .88rem;
    font-weight: 700;
    line-height: 1.35;
}

.hr-chart-tooltip__meta {
    margin-top: .15rem;
    color: var(--hr-ink-3);
    font-size: .76rem;
}

.hr-avatar-stack {
    display: flex;
    align-items: center;
    min-height: 34px;
    margin-top: .65rem;
    padding-left: .35rem;
}

.hr-avatar-stack__item {
    width: 32px;
    height: 32px;
    margin-left: -.35rem;
    border: 2px solid var(--hr-surface);
    border-radius: 50%;
    background: var(--hr-surface-2);
    box-shadow: 0 1px 3px rgba(15, 23, 42, .16);
    object-fit: cover;
}

.hr-avatar-stack__more {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--hr-ink-2);
    font-size: .72rem;
    font-weight: 700;
}

.hr-chart-tooltip__empty {
    margin-top: .55rem;
    color: var(--hr-ink-3);
    font-size: .76rem;
}

.hr-person-panel {
    position: fixed;
    inset: 0;
    z-index: 1090;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1rem;
}

.hr-person-panel.is-open {
    display: flex;
}

.hr-person-panel__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(15, 23, 42, .36);
}

.hr-person-panel__sheet {
    position: relative;
    width: min(520px, calc(100vw - 2rem));
    max-height: min(680px, calc(100vh - 2rem));
    overflow: hidden;
    background: #ffffff;
    border: 1px solid rgba(148, 163, 184, .28);
    border-radius: 8px;
    box-shadow: 0 24px 60px rgba(15, 23, 42, .24);
}

.hr-person-panel__header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1rem 1.1rem .85rem;
    border-bottom: 1px solid rgba(148, 163, 184, .18);
}

.hr-person-panel__eyebrow {
    color: var(--hr-ink-3);
    font-size: .75rem;
    font-weight: 700;
}

.hr-person-panel__title {
    color: var(--hr-ink-1);
    font-size: 1rem;
    font-weight: 800;
}

.hr-person-panel__meta {
    color: var(--hr-ink-3);
    font-size: .78rem;
    margin-top: .2rem;
}

.hr-person-panel__action-row {
    display: none;
    margin-top: .65rem;
}

.hr-person-panel__action-row.is-visible {
    display: flex;
}

.hr-person-panel__filter-btn,
.hr-filter-confirm__apply {
    min-height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 7px;
    background: #2563eb;
    color: #ffffff;
    font-size: .78rem;
    font-weight: 800;
    line-height: 1.1;
    padding: .45rem .75rem;
    box-shadow: 0 8px 18px rgba(37, 99, 235, .22);
}

.hr-person-panel__filter-btn:hover,
.hr-filter-confirm__apply:hover {
    background: #1d4ed8;
}

.hr-person-panel__close {
    width: 34px;
    height: 34px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 0;
    border-radius: 50%;
    color: var(--hr-ink-2);
    background: rgba(148, 163, 184, .12);
    font-size: 1.35rem;
    line-height: 1;
}

.hr-person-panel__close:hover {
    background: rgba(148, 163, 184, .2);
}

.hr-person-panel__list {
    max-height: calc(min(680px, calc(100vh - 2rem)) - 92px);
    overflow: auto;
    padding: .55rem;
}

.hr-person-panel__row {
    display: grid;
    grid-template-columns: 44px minmax(0, 1fr) auto;
    gap: .75rem;
    align-items: center;
    padding: .65rem;
    border-radius: 8px;
}

.hr-person-panel__content {
    min-width: 0;
}

.hr-person-panel__row + .hr-person-panel__row {
    border-top: 1px solid rgba(148, 163, 184, .14);
}

.hr-person-panel__avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    box-shadow: 0 0 0 2px #ffffff, 0 4px 12px rgba(15, 23, 42, .16);
}

.hr-person-panel__name {
    color: var(--hr-ink-1);
    font-size: .88rem;
    font-weight: 800;
    line-height: 1.3;
}

.hr-person-panel__detail {
    color: var(--hr-ink-3);
    font-size: .78rem;
    line-height: 1.35;
    margin-top: .16rem;
}

.hr-person-panel__profile-link {
    grid-column: 3;
    grid-row: 1;
    justify-self: end;
    align-self: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 30px;
    padding: .35rem .6rem;
    border: 1px solid rgba(37, 99, 235, .22);
    border-radius: 7px;
    background: rgba(37, 99, 235, .08);
    color: #1d4ed8;
    font-size: .76rem;
    font-weight: 800;
    line-height: 1;
    text-decoration: none;
    white-space: nowrap;
}

.hr-person-panel__profile-link:hover {
    background: rgba(37, 99, 235, .14);
    color: #1d4ed8;
    text-decoration: none;
}

#dashboardRoot .apexcharts-bar-area,
#dashboardRoot .apexcharts-pie-area,
#dashboardRoot .apexcharts-radialbar-area {
    cursor: pointer;
}

.hr-filter-confirm {
    position: fixed;
    right: 1rem;
    bottom: 1rem;
    z-index: 1095;
    display: none;
    width: min(360px, calc(100vw - 2rem));
    padding: .8rem;
    background: #ffffff;
    border: 1px solid rgba(148, 163, 184, .28);
    border-radius: 8px;
    box-shadow: 0 18px 42px rgba(15, 23, 42, .18);
}

.hr-filter-confirm.is-open {
    display: block;
}

.hr-filter-confirm__title {
    color: var(--hr-ink-1);
    font-size: .9rem;
    font-weight: 800;
    line-height: 1.35;
}

.hr-filter-confirm__meta {
    color: var(--hr-ink-3);
    font-size: .76rem;
    line-height: 1.35;
    margin-top: .15rem;
}

.hr-filter-confirm__actions {
    display: flex;
    gap: .45rem;
    justify-content: flex-end;
    margin-top: .7rem;
}

.hr-filter-confirm__cancel {
    min-height: 34px;
    border: 1px solid rgba(148, 163, 184, .32);
    border-radius: 7px;
    background: #ffffff;
    color: var(--hr-ink-2);
    font-size: .78rem;
    font-weight: 800;
    padding: .45rem .7rem;
}

.hr-dashboard-empty {
    display: grid;
    gap: .25rem;
    min-height: 180px;
    place-items: center;
    border: 1px dashed var(--hr-line);
    border-radius: 8px;
    background: var(--hr-surface-1);
    color: var(--hr-ink-3);
    font-size: .86rem;
    text-align: center;
}

.hr-dashboard-empty__title {
    color: var(--hr-ink-2);
    font-weight: 600;
}

.hr-dashboard-empty__caption {
    color: var(--hr-ink-3);
    font-size: .78rem;
}

.hr-dashboard a:focus-visible,
.hr-dashboard button:focus-visible,
.hr-dashboard [role="img"]:focus-visible {
    outline: 3px solid rgba(13, 110, 253, .28);
    outline-offset: 2px;
}

@media (prefers-reduced-motion: reduce) {
    .hr-dashboard *,
    .hr-dashboard *::before,
    .hr-dashboard *::after {
        animation-duration: .01ms !important;
        animation-iteration-count: 1 !important;
        scroll-behavior: auto !important;
        transition-duration: .01ms !important;
    }
}

@media (max-width: 575.98px) {
    .hr-dashboard-kpi-grid .hr-kpi {
        min-height: 116px;
        padding: .7rem .75rem .8rem;
    }

    .hr-kpi__value {
        font-size: 1.38rem;
    }

    .hr-kpi__icon {
        width: 34px;
        height: 34px;
        font-size: .95rem;
    }

    .hr-kpi__label {
        font-size: .8rem;
    }

    .hr-kpi-detail__head {
        align-items: stretch;
    }

    .hr-kpi-detail__tools {
        width: 100%;
    }

    .hr-kpi-detail__search {
        width: auto;
        max-width: none;
        flex: 1 1 auto;
    }

    .hr-dashboard .card-header {
        gap: .35rem;
    }

    .hr-dashboard-chart--bar {
        min-height: 280px;
    }
}

/* Dark mode: remap หน้านี้ให้ใช้พื้นผิว/เส้น/ตัวอักษรมาตรฐาน Bootstrap */
[data-bs-theme="dark"] .hr-dashboard {
    --hr-surface: var(--bs-secondary-bg);
    --hr-surface-1: var(--bs-tertiary-bg);
    --hr-surface-2: var(--bs-tertiary-bg);
    --hr-line: var(--bs-border-color);
    --hr-ink-1: var(--bs-emphasis-color);
    --hr-ink-2: var(--bs-body-color);
    --hr-ink-3: var(--bs-secondary-color);
}

[data-bs-theme="dark"] .hr-dashboard-summary-card {
    background: var(--bs-secondary-bg);
}

[data-bs-theme="dark"] .hr-dashboard .card:has(.hr-dashboard-chart) .card-header {
    background: var(--bs-tertiary-bg);
}

[data-bs-theme="dark"] .hr-dashboard .card:has(.hr-dashboard-chart) .card-header h6 {
    color: var(--bs-body-color);
}

[data-bs-theme="dark"] .hr-dashboard .card-footer.bg-light {
    background-color: var(--bs-tertiary-bg) !important;
}

[data-bs-theme="dark"] .hr-person-panel__sheet,
[data-bs-theme="dark"] .hr-filter-confirm,
[data-bs-theme="dark"] .hr-chart-tooltip {
    background: var(--bs-secondary-bg);
    border-color: var(--bs-border-color);
}
CSS);
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 id="hr-dashboard-title" class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="layout-dashboard" aria-hidden="true"></i>
        <span class="d-block"><?= Html::encode($this->title) ?></span>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>

<section class="hr-dashboard" aria-labelledby="hr-dashboard-title">

<?php
// แถบเลือกปีงบประมาณ – ทุกการ์ด/ชาร์ตบนหน้านี้อิงปีที่เลือก
$budgetYear = (int) ($budgetYear ?? 0);
$currentBudgetYear = (int) ($currentBudgetYear ?? 0);
$budgetYearOptions = $budgetYearOptions ?? [];
$isCurrentBudgetYear = (bool) ($isCurrentBudgetYear ?? true);
$chartFilterParams = array_filter([
    'gender' => $filterGender ?? null,
    'department' => $filterDepartment ?? null,
    'employee_type_id' => $filterPositionType ?? null,
    'workgroup' => $filterWorkgroup ?? null,
    'gen' => $filterGen ?? null,
    'employee_position_id' => $filterPositionName ?? null,
    'service_band' => $filterServiceBand ?? null,
], function ($v) { return $v !== null && $v !== ''; });
?>
<div class="card hr-dashboard-period-card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center gap-2">
        <span class="small fw-semibold text-body d-flex align-items-center gap-1">
            <i class="bi bi-calendar3" aria-hidden="true"></i>ปีงบประมาณ
        </span>
        <form method="get" action="<?= Url::to(['/hr/default/dashboard']) ?>" class="d-flex align-items-center gap-2 mb-0">
            <?php foreach ($chartFilterParams as $k => $v): ?>
                <input type="hidden" name="<?= Html::encode($k) ?>" value="<?= Html::encode($v) ?>">
            <?php endforeach; ?>
            <select name="budget_year" class="form-select form-select-sm w-auto" onchange="this.form.submit()" aria-label="เลือกปีงบประมาณ">
                <?php foreach ($budgetYearOptions as $optionYear): ?>
                    <option value="<?= (int) $optionYear ?>"<?= (int) $optionYear === $budgetYear ? ' selected' : '' ?>>
                        <?= (int) $optionYear ?><?= (int) $optionYear === $currentBudgetYear ? ' (ปีปัจจุบัน)' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <noscript><button type="submit" class="btn btn-sm btn-primary">ดู</button></noscript>
        </form>
        <span class="small text-muted"><?= Html::encode($movementPeriodText ?? '') ?></span>
        <span class="small text-muted ms-auto">ข้อมูล ณ <?= Html::encode($asOfDateText ?? '') ?></span>
    </div>
    <?php if (!$isCurrentBudgetYear): ?>
    <div class="card-footer py-1 px-3 border-0 small text-body-secondary">
        <i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
        ปีย้อนหลังนับจากประวัติตำแหน่ง คนที่ออกไปก่อนเริ่มบันทึกประวัติจะไม่ถูกนับ
    </div>
    <?php endif; ?>
</div>

<?php
$hasFilter =!empty($filterGender) || (isset($filterDepartment) && $filterDepartment !== '') || (isset($filterPositionType) && $filterPositionType !== '') || (isset($filterWorkgroup) && $filterWorkgroup !== '') || !empty($filterGen) || (isset($filterPositionName) && $filterPositionName !== '') || (isset($filterServiceBand) && $filterServiceBand !== '');
?>
<?php if ($hasFilter): ?>
<div class="card hr-dashboard-filter-card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center gap-2">
        <span class="small fw-semibold text-body me-1">ตัวกรองปัจจุบัน:</span>
        <?php
        $parts = [];
        if (!empty($filterGender)) $parts[] = 'เพศ ' . Html::encode($filterGender);
        if (!empty($filterDepartment) && !empty($departmentLabels)) {
            $idx = null;
            foreach (($departmentCodes ?? []) as $i => $c) {
                if ((string)$c === (string)$filterDepartment) { $idx = $i; break; }
            }
            $parts[] = 'แผนก ' . ($idx !== null ? Html::encode($departmentLabels[$idx] ?? $filterDepartment) : Html::encode($filterDepartment));
        }
        if (!empty($filterPositionType) && !empty($positionTypeLabels)) {
            $ptIdx = null;
            foreach (($positionTypeCodes ?? []) as $i => $c) {
                if ((string)$c === (string)$filterPositionType) { $ptIdx = $i; break; }
            }
            $parts[] = 'ประเภทพนักงาน ' . ($ptIdx !== null ? Html::encode($positionTypeLabels[$ptIdx] ?? $filterPositionType) : Html::encode($filterPositionType));
        }
        if (!empty($filterWorkgroup) && !empty($workgroupRows)) {
            $wgName = $filterWorkgroup;
            foreach ($workgroupRows as $wr) {
                if (isset($wr['code']) && $wr['code'] == $filterWorkgroup) {
                    $wgName = $wr['name'] ?? $filterWorkgroup;
                    break;
                }
            }
            $parts[] = 'กลุ่มงาน ' . Html::encode($wgName);
        }
        if (!empty($filterGen)) $parts[] = 'ช่วงวัย ' . Html::encode($filterGen);
        if (!empty($filterPositionName) && !empty($positionNameCategories)) {
            $pnIdx = null;
            foreach (($positionNameCodes ?? []) as $i => $c) {
                if ((string)$c === (string)$filterPositionName) { $pnIdx = $i; break; }
            }
            $parts[] = 'ชื่อตำแหน่ง ' . ($pnIdx !== null ? Html::encode($positionNameCategories[$pnIdx] ?? $filterPositionName) : Html::encode($filterPositionName));
        }
        if (!empty($filterServiceBand)) $parts[] = 'ช่วงอายุงาน ' . Html::encode($filterServiceBand);
        echo implode(' · ', $parts);
        ?>
        <a href="<?= Url::to($isCurrentBudgetYear ? ['/hr/default/dashboard'] : ['/hr/default/dashboard', 'budget_year' => $budgetYear]) ?>" class="btn btn-sm btn-outline-secondary ms-auto" title="แสดงข้อมูลทั้งหมด (ยกเลิกตัวกรองจากชาร์ต)" aria-label="ล้างตัวกรองและแสดงข้อมูลทั้งหมด">
            <i class="bi bi-x-circle me-1" aria-hidden="true"></i>ล้างตัวกรอง
        </a>
    </div>
    <div class="card-footer py-1 px-3 bg-light border-0 small text-muted">
        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>กดปุ่ม "ล้างตัวกรอง" เพื่อกลับไปแสดงข้อมูลทั้งหมดเหมือนก่อนกดที่ชาร์ต
    </div>
</div>
<?php endif; ?>

<!-- มุมมองผู้บริหาร สรุปหนึ่งบรรทัด -->
<div class="card hr-dashboard-summary-card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <p class="text-muted small mb-0">
            <strong>มุมมองผู้บริหาร:</strong>
            องค์กรมีบุคลากรปฏิบัติราชการ ทั้งหมด <strong><?= $totalCount ?></strong> คน
            (ชาย <?= $countMale ?> · หญิง <?= $countFemale ?> · สัดส่วน <?= $genderRatio ?>)
            กระจายใน <strong><?= (int)($numWorkgroups ?? 0) ?></strong> กลุ่มงาน
            และ <strong><?= (int)($numPositionTypes ?? 0) ?></strong> ประเภทพนักงาน
            <?php if (isset($newHiresThisYear) || isset($leftThisYear)): ?>
            · ปีงบประมาณ <?= (int)($movementBudgetYear ?? 0) ?> บรรจุใหม่ <strong><?= (int)($newHiresThisYear ?? 0) ?></strong> คน · ลาออก/สิ้นสุด <strong><?= (int)($leftThisYear ?? 0) ?></strong> คน
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- KPI Cards -->
<?php
$asOfLabel = Html::encode($asOfDateText ?? '');
$periodLabel = Html::encode($movementPeriodText ?? '');
$avgTenureText = isset($avgYearsService) && $avgYearsService !== null ? $avgYearsService : '—';
$kpiCards = [
    [
        'key' => 'headcount',
        'label' => 'บุคลากรปฏิบัติงาน',
        'value' => number_format($totalCount),
        'icon' => 'bi-people',
        'source' => 'people',
        'title' => 'บุคลากรที่ปฏิบัติงาน ณ ' . $asOfLabel,
    ],
    [
        'key' => 'gender',
        'label' => 'ชาย / หญิง',
        'value' => $countMale . ' / ' . $countFemale,
        'icon' => 'bi-gender-ambiguous',
        'source' => 'people',
        'sort' => [1, 'asc'],
        'title' => 'บุคลากรแยกตามเพศ ณ ' . $asOfLabel,
    ],
    [
        'key' => 'orgUnits',
        'label' => 'หน่วยงานในผังองค์กร',
        'value' => number_format($organizationDiagramCount),
        'icon' => 'bi-diagram-3',
        'source' => 'orgUnits',
        'title' => 'หน่วยงานในผังองค์กร',
        'link' => Url::to(['/hr/organization/diagram']),
        'linkLabel' => 'เปิดผังองค์กร',
    ],
    [
        'key' => 'teamGroups',
        'label' => 'กลุ่ม / ทีมประสานงาน',
        'value' => number_format($teamGroupCount),
        'icon' => 'bi-person-workspace',
        'source' => 'teamGroups',
        'title' => 'กลุ่ม / ทีมประสานงาน',
        'link' => Url::to(['/hr/team-group/index']),
        'linkLabel' => 'เปิดทะเบียนทีม',
    ],
    [
        'key' => 'newHires',
        'label' => 'บรรจุใหม่',
        'value' => number_format((int)($newHiresThisYear ?? 0)),
        'icon' => 'bi-person-plus',
        'source' => 'newHires',
        'title' => 'บรรจุใหม่ ปีงบประมาณ ' . (int) $budgetYear . ' · ' . $periodLabel,
    ],
    [
        'key' => 'exits',
        'label' => 'ลาออก / สิ้นสุด',
        'value' => number_format((int)($leftThisYear ?? 0)),
        'icon' => 'bi-person-dash',
        'source' => 'exits',
        'title' => 'พ้นจากหน่วยงาน ปีงบประมาณ ' . (int) $budgetYear . ' · ' . $periodLabel,
    ],
    [
        'key' => 'tenure',
        'label' => 'อายุงานเฉลี่ย (ปี)',
        'value' => $avgTenureText,
        'icon' => 'bi-clock-history',
        'source' => 'people',
        'sort' => [6, 'desc'],
        'title' => 'อายุงานรายบุคคล ณ ' . $asOfLabel . ' · เฉลี่ย ' . $avgTenureText . ' ปี',
    ],
];
?>
<div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3 mt-1 mb-3 hr-dashboard-kpi-grid">
    <?php foreach ($kpiCards as $card): ?>
    <div class="col">
        <button type="button"
                class="hr-kpi"
                data-kpi="<?= Html::encode($card['key']) ?>"
                data-source="<?= Html::encode($card['source']) ?>"
                data-title="<?= Html::encode($card['title']) ?>"
                <?php if (!empty($card['sort'])): ?>data-sort-index="<?= (int) $card['sort'][0] ?>" data-sort-dir="<?= Html::encode($card['sort'][1]) ?>"<?php endif; ?>
                <?php if (!empty($card['link'])): ?>data-link="<?= Html::encode($card['link']) ?>" data-link-label="<?= Html::encode($card['linkLabel']) ?>"<?php endif; ?>
                aria-expanded="false"
                aria-controls="hrKpiDetail"
                title="กดเพื่อดูรายการที่อยู่เบื้องหลังตัวเลขนี้">
            <span class="hr-kpi__top">
                <span class="hr-kpi__icon"><i class="bi <?= Html::encode($card['icon']) ?>" aria-hidden="true"></i></span>
                <i class="bi bi-chevron-down hr-kpi__chevron" aria-hidden="true"></i>
            </span>
            <span class="hr-kpi__value"><?= Html::encode($card['value']) ?></span>
            <span class="hr-kpi__label"><?= Html::encode($card['label']) ?></span>
        </button>
    </div>
    <?php endforeach; ?>
</div>

<section class="hr-kpi-detail mb-4" id="hrKpiDetail" hidden>
    <header class="hr-kpi-detail__head">
        <div class="hr-kpi-detail__heading">
            <h3 class="hr-kpi-detail__title" id="hrKpiDetailTitle" tabindex="-1"></h3>
            <span class="hr-kpi-detail__count" id="hrKpiDetailCount"></span>
        </div>
        <div class="hr-kpi-detail__tools">
            <a href="#" class="btn btn-sm btn-outline-secondary" id="hrKpiDetailLink" hidden></a>
            <label class="visually-hidden" for="hrKpiDetailSearch">ค้นหาในรายการ</label>
            <input type="search" id="hrKpiDetailSearch" class="form-control form-control-sm hr-kpi-detail__search" placeholder="ค้นหาในรายการ" autocomplete="off">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="hrKpiDetailClose">ปิดรายการ</button>
        </div>
    </header>
    <div class="hr-kpi-detail__body">
        <table class="table table-sm align-middle mb-0 hr-kpi-detail__table" id="hrKpiDetailTable">
            <thead><tr></tr></thead>
            <tbody></tbody>
        </table>
    </div>
    <p class="hr-kpi-detail__empty mb-0" id="hrKpiDetailEmpty" hidden>ไม่พบรายการที่ตรงกับคำค้น</p>
</section>

<div class="row mb-3">
    <div class="col-12">
        <h6 class="hr-dashboard-section-label mb-0">โครงสร้างและประชากรบุคลากร</h6>
    </div>
</div>

<!-- แถวที่ 1: สัดส่วน (Donut) — เปรียบเทียบสัดส่วนได้เร็ว -->
<?php $donutCol = 'col-md-4'; ?>
<div class="row g-3 mb-4">
    <div class="col-12 <?= $donutCol ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 small fw-semibold text-muted">เพศ</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-stretch justify-content-center py-3">
                <div id="dashboardGenderPie" class="hr-dashboard-chart w-100" role="img" tabindex="0" aria-label="กราฟสัดส่วนบุคลากรตามเพศ" aria-describedby="dashboardGenderFilters"></div>
                <?= $renderDashboardFilterLinks('dashboardGenderFilters', $genderLabels, $genderValues, 'gender', $genderLabels, 'ตัวเลือกกรองจากกราฟเพศ') ?>
            </div>
        </div>
    </div>
    <div class="col-12 <?= $donutCol ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 small fw-semibold text-muted">ช่วงวัย (Generation)</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-stretch justify-content-center py-3">
                <?php if (!empty($genCounts)): ?>
                <div id="dashboardGenerationPie" class="hr-dashboard-chart w-100" role="img" tabindex="0" aria-label="กราฟสัดส่วนบุคลากรตามช่วงวัย" aria-describedby="dashboardGenerationFilters"></div>
                <?= $renderDashboardFilterLinks('dashboardGenerationFilters', $genLabels, $genValues, 'gen', $genLabels, 'ตัวเลือกกรองจากกราฟช่วงวัย') ?>
                <?php else: ?>
                <div class="hr-dashboard-empty w-100" role="status">
                    <span class="hr-dashboard-empty__title">ไม่มีข้อมูลช่วงวัยสำหรับตัวกรองนี้</span>
                    <span class="hr-dashboard-empty__caption">ลองล้างตัวกรองหรือเลือกช่วงข้อมูลที่กว้างขึ้น</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if (!empty($positionTypeLabels)): ?>
    <div class="col-12 <?= $donutCol ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 small fw-semibold text-muted">ประเภทพนักงาน</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-stretch justify-content-center py-3">
                <div id="dashboardPositionTypePie" class="hr-dashboard-chart w-100" role="img" tabindex="0" aria-label="กราฟสัดส่วนบุคลากรตามประเภทพนักงาน" aria-describedby="dashboardPositionTypeFilters"></div>
                <?= $renderDashboardFilterLinks('dashboardPositionTypeFilters', $positionTypeLabels ?? [], $positionTypeCounts ?? [], 'employee_type_id', $positionTypeCodes ?? [], 'ตัวเลือกกรองจากกราฟประเภทพนักงาน') ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-12 <?= $donutCol ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 small fw-semibold text-muted">ประเภทพนักงาน</h6>
            </div>
            <div class="card-body py-3">
                <div class="hr-dashboard-empty" role="status">
                    <span class="hr-dashboard-empty__title">ไม่มีข้อมูลประเภทพนักงานสำหรับตัวกรองนี้</span>
                    <span class="hr-dashboard-empty__caption">ลองล้างตัวกรองหรือเลือกมุมมองอื่น</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- แถวที่ 2: กลุ่มงาน × ประเภทพนักงาน — ข้อมูลหลัก ใช้พื้นที่เต็มความกว้าง -->
<?php if (!empty($workgroupRows) && !empty($positionTypeLabels)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-semibold">จำแนกตามกลุ่มงานและประเภทพนักงาน</h6>
                <span class="small text-muted">คลิกแท่งเพื่อกรองตามกลุ่มงาน</span>
            </div>
            <div class="card-body pt-3 pb-2 px-3">
                <div id="dashboardWorkgroupChart" class="hr-dashboard-chart hr-dashboard-chart--bar" role="img" tabindex="0" aria-label="กราฟจำแนกบุคลากรตามกลุ่มงานและประเภทพนักงาน" aria-describedby="dashboardWorkgroupFilters"></div>
                <?= $renderDashboardFilterLinks('dashboardWorkgroupFilters', $workgroupLabels, $workgroupValues, 'workgroup', $workgroupCodes, 'ตัวเลือกกรองจากกราฟกลุ่มงาน') ?>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-semibold">จำแนกตามกลุ่มงานและประเภทพนักงาน</h6>
            </div>
            <div class="card-body py-3">
                <div class="hr-dashboard-empty" role="status">
                    <span class="hr-dashboard-empty__title">ไม่มีข้อมูลกลุ่มงานหรือประเภทพนักงานสำหรับตัวกรองนี้</span>
                    <span class="hr-dashboard-empty__caption">ลองล้างตัวกรองหรือเลือกข้อมูลบุคลากรมุมอื่น</span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- แถวที่ 3: ประชากรตามช่วงอายุ (ชาย/หญิง) — ไม่แก้รูปแบบ -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-semibold">ประชากรตามช่วงอายุ (ชาย/หญิง)</h6>
            </div>
            <div class="card-body pt-3 pb-2 px-3">
                <div id="dashboardAgeChart" class="hr-dashboard-chart hr-dashboard-chart--bar" role="img" tabindex="0" aria-label="กราฟประชากรตามช่วงอายุ แยกชายและหญิง" aria-describedby="dashboardAgeSummary"></div>
                <div id="dashboardAgeSummary" class="visually-hidden">
                    ชายรวม <?= number_format($ageMaleTotal) ?> คน หญิงรวม <?= number_format($ageFemaleTotal) ?> คน
                </div>
            </div>
        </div>
    </div>
</div>

<!-- แถวที่ 4: ตำแหน่ง + ช่วงอายุงาน — คู่กัน เหมาะกับข้อมูลแบบหมวดหมู่ -->
<div class="row g-3 mb-4">
    <?php if (!empty($positionNameCategories)): ?>
    <?php $hasPositionOthers = end($positionNameCategories) === 'อื่นๆ'; ?>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-semibold">จำนวนคนตามชื่อตำแหน่ง</h6>
                <span class="small text-muted">คลิกแท่งเพื่อกรอง</span>
                <?php if ($hasPositionOthers): ?>
                <span class="small text-muted d-block mt-1">แสดง 12 อันดับแรก ที่เหลือรวมเป็น อื่นๆ</span>
                <?php endif; ?>
            </div>
            <div class="card-body pt-3 pb-2 px-3">
                <div id="dashboardPositionNameChart" class="hr-dashboard-chart hr-dashboard-chart--bar" role="img" tabindex="0" aria-label="กราฟจำนวนบุคลากรตามชื่อตำแหน่ง" aria-describedby="dashboardPositionNameFilters"></div>
                <?= $renderDashboardFilterLinks('dashboardPositionNameFilters', $positionNameCategories ?? [], $positionNameValues ?? [], 'employee_position_id', $positionNameCodes ?? [], 'ตัวเลือกกรองจากกราฟชื่อตำแหน่ง') ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-semibold">จำนวนคนตามชื่อตำแหน่ง</h6>
            </div>
            <div class="card-body py-3">
                <div class="hr-dashboard-empty" role="status">
                    <span class="hr-dashboard-empty__title">ไม่มีข้อมูลชื่อตำแหน่งสำหรับตัวกรองนี้</span>
                    <span class="hr-dashboard-empty__caption">ลองล้างตัวกรองหรือเลือกกลุ่มงานอื่น</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($serviceBandLabels)): ?>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-semibold">กระจายตามช่วงอายุงาน</h6>
                <span class="small text-muted">คลิกแท่งเพื่อกรอง</span>
            </div>
            <div class="card-body pt-3 pb-2 px-3">
                <div id="dashboardServiceBandChart" class="hr-dashboard-chart hr-dashboard-chart--bar" role="img" tabindex="0" aria-label="กราฟกระจายบุคลากรตามช่วงอายุงาน" aria-describedby="dashboardServiceBandFilters"></div>
                <?= $renderDashboardFilterLinks('dashboardServiceBandFilters', $serviceBandLabels ?? [], $serviceBandValues ?? [], 'service_band', $serviceBandLabels ?? [], 'ตัวเลือกกรองจากกราฟช่วงอายุงาน') ?>
            </div>
        </div>
    </div>
    <?php else: ?>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-semibold">กระจายตามช่วงอายุงาน</h6>
            </div>
            <div class="card-body py-3">
                <div class="hr-dashboard-empty" role="status">
                    <span class="hr-dashboard-empty__title">ไม่มีข้อมูลช่วงอายุงานสำหรับตัวกรองนี้</span>
                    <span class="hr-dashboard-empty__caption">ลองล้างตัวกรองหรือเลือกแผนกอื่น</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- แถวที่ 5: จำนวนบุคลากรแยกตามแผนก — แนวนอน ให้พื้นที่ตามจำนวนแผนก -->
<?php if (!empty($departmentLabels)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-semibold">จำนวนบุคลากรแยกตามแผนก</h6>
                <span class="small text-muted">คลิกแท่งเพื่อกรอง</span>
            </div>
            <div class="card-body pt-3 pb-2 px-3">
                <div id="dashboardDepartmentChart" class="hr-dashboard-chart hr-dashboard-chart--bar" role="img" tabindex="0" aria-label="กราฟจำนวนบุคลากรแยกตามแผนก" aria-describedby="dashboardDepartmentFilters"></div>
                <?= $renderDashboardFilterLinks('dashboardDepartmentFilters', $departmentLabels ?? [], $departmentValues ?? [], 'department', $departmentCodes ?? [], 'ตัวเลือกกรองจากกราฟแผนก') ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($departmentLabels)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="hr-dashboard-empty" role="status">
            <span class="hr-dashboard-empty__title">ไม่มีข้อมูลแผนกสำหรับตัวกรองนี้</span>
            <span class="hr-dashboard-empty__caption">ลองล้างตัวกรองหรือเลือกกลุ่มงานอื่น</span>
        </div>
    </div>
</div>
<?php endif; ?>

</section>

<?php
$positionTypeLabelsJson = Json::encode($positionTypeLabels ?? []);
$positionTypeCountsJson = Json::encode($positionTypeCounts ?? []);
$workgroupRowsJson = Json::encode($workgroupRows ?? []);
$ageCategoriesJson = Json::encode($ageCategories ?? []);
$ageMaleJson = Json::encode($ageMale ?? []);
$ageFemaleJson = Json::encode($ageFemale ?? []);
$positionNameCategoriesJson = Json::encode($positionNameCategories ?? []);
$positionNameValuesJson = Json::encode($positionNameValues ?? []);
$positionNameCodesJson = Json::encode($positionNameCodes ?? []);
$genLabelsJson = Json::encode(array_keys($genCounts));
$genSeriesJson = Json::encode(array_values($genCounts));
$departmentLabelsJson = Json::encode($departmentLabels ?? []);
$departmentValuesJson = Json::encode($departmentValues ?? []);
$serviceBandLabelsJson = Json::encode($serviceBandLabels ?? []);
$serviceBandValuesJson = Json::encode($serviceBandValues ?? []);
$dashboardUrl = $dashboardUrl ?? Url::to(['/hr/default/dashboard']);
$positionTypeCodesJson = Json::encode($positionTypeCodes ?? []);
$departmentCodesJson = Json::encode($departmentCodes ?? []);
$dashboardTooltipPeopleJson = Json::encode($dashboardTooltipPeople ?? []);
?>
<script>
window.__hrDashboard = {
  baseUrl: <?= Json::encode($dashboardUrl) ?>,
  budgetYear: <?= (int) ($budgetYear ?? 0) ?>,
  currentBudgetYear: <?= (int) ($currentBudgetYear ?? 0) ?>,
  filter: {
    gender: <?= Json::encode($filterGender ?? '') ?>,
    department: <?= Json::encode($filterDepartment ?? '') ?>,
    employee_type_id: <?= Json::encode($filterPositionType ?? '') ?>,
    workgroup: <?= Json::encode($filterWorkgroup ?? '') ?>,
    gen: <?= Json::encode($filterGen ?? '') ?>,
    employee_position_id: <?= Json::encode($filterPositionName ?? '') ?>,
    service_band: <?= Json::encode($filterServiceBand ?? '') ?>
  },
  positionTypeCodes: <?= $positionTypeCodesJson ?>,
  departmentCodes: <?= $departmentCodesJson ?>,
  tooltipPeople: <?= $dashboardTooltipPeopleJson ?>,
  kpiDetail: <?= Json::encode($kpiDetailSources ?? []) ?>,
  totalCount: <?= (int) $totalCount ?>,
  countMale: <?= (int) $countMale ?>,
  countFemale: <?= (int) $countFemale ?>,
  positionTypeLabels: <?= $positionTypeLabelsJson ?>,
  positionTypeCounts: <?= $positionTypeCountsJson ?>,
  workgroupRows: <?= $workgroupRowsJson ?>,
  ageCategories: <?= $ageCategoriesJson ?>,
  ageMale: <?= $ageMaleJson ?>,
  ageFemale: <?= $ageFemaleJson ?>,
  positionNameCategories: <?= $positionNameCategoriesJson ?>,
  positionNameValues: <?= $positionNameValuesJson ?>,
  positionNameCodes: <?= $positionNameCodesJson ?>,
  genLabels: <?= $genLabelsJson ?>,
  genSeries: <?= $genSeriesJson ?>,
  departmentLabels: <?= $departmentLabelsJson ?>,
  departmentValues: <?= $departmentValuesJson ?>,
  serviceBandLabels: <?= $serviceBandLabelsJson ?>,
  serviceBandValues: <?= $serviceBandValuesJson ?>
};
</script>
<?php
$js = <<<'JS'
(function() {
  var d = window.__hrDashboard;
  if (!d) return;
  var dashboardRoot = document.querySelector('.hr-dashboard') || document.documentElement;
  var dashboardStyles = window.getComputedStyle(dashboardRoot);
  var cssToken = function(name, fallback) {
    var value = dashboardStyles.getPropertyValue(name);
    return value ? value.trim() || fallback : fallback;
  };
  var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var palette = {
    primary: cssToken('--hr-primary', '#0d6efd'),
    blue: cssToken('--hr-blue', '#3b82f6'),
    sky: cssToken('--hr-sky', '#0ea5e9'),
    teal: cssToken('--hr-teal', '#14b8a6'),
    violet: cssToken('--hr-violet', '#8b5cf6'),
    rose: cssToken('--hr-rose', '#f43f5e'),
    amber: cssToken('--hr-amber', '#f59e0b'),
    success: cssToken('--hr-success', '#15803d'),
    danger: cssToken('--hr-danger', '#b91c1c'),
    warning: cssToken('--hr-warning', '#b45309'),
    ink: cssToken('--hr-ink-2', '#4a5568'),
    muted: cssToken('--hr-ink-3', '#64748b'),
    surface: cssToken('--hr-surface', '#ffffff'),
    line: cssToken('--hr-line', 'rgba(15, 23, 42, .10)')
  };
  var chartPalette = [palette.blue, palette.teal, palette.violet, palette.amber, palette.rose, palette.sky, palette.success, palette.warning];
  var genderPalette = [palette.blue, palette.rose];
  function withDashboardChartDefaults(options) {
    options = options || {};
    options.chart = Object.assign({
      foreColor: palette.ink,
      fontFamily: 'inherit',
      parentHeightOffset: 0,
      toolbar: { show: false },
      animations: {
        enabled: !reducedMotion,
        speed: reducedMotion ? 0 : 220,
        animateGradually: { enabled: false },
        dynamicAnimation: { enabled: !reducedMotion, speed: 180 }
      }
    }, options.chart || {});
    options.grid = Object.assign({
      borderColor: palette.line,
      strokeDashArray: 3,
      padding: { left: 8, right: 8, top: 0, bottom: 0 }
    }, options.grid || {});
    options.legend = Object.assign({
      fontSize: '12px',
      fontWeight: 600,
      markers: { radius: 8 },
      itemMargin: { horizontal: 8, vertical: 4 }
    }, options.legend || {});
    options.noData = options.noData || {
      text: 'ไม่มีข้อมูลสำหรับตัวกรองนี้',
      align: 'center',
      verticalAlign: 'middle',
      style: { color: palette.muted, fontSize: '13px' }
    };
    // ชาร์ตที่ระบุกลุ่มมาเองไม่ต้องเดาจากรูปร่างข้อมูล
    var explicitTooltipGroup = options.hrTooltipGroup;
    if (explicitTooltipGroup) {
      delete options.hrTooltipGroup;
    }
    var inferredTooltipGroup = explicitTooltipGroup || inferDashboardTooltipGroup(options);
    if (!options.tooltip && inferredTooltipGroup) {
      options.tooltip = dashboardTooltip(inferredTooltipGroup);
    }
    if (inferredTooltipGroup) {
      var existingDataPointSelection = options.chart.events && options.chart.events.dataPointSelection;
      options.chart.events = Object.assign({}, options.chart.events || {}, {
        dataPointSelection: function(event, chartContext, opts) {
          openPeoplePanel(inferredTooltipGroup, opts || {});
          if (typeof existingDataPointSelection === 'function') {
            return existingDataPointSelection(event, chartContext, opts);
          }
        }
      });
    }
    return options;
  }
  var tooltipPeople = d.tooltipPeople || {};
  var fallbackGenderLabels = ['ชาย', 'หญิง'];
  var escapeHtml = function(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  };
  function inferDashboardTooltipGroup(options) {
    var chart = options.chart || {};
    var labels = options.labels || [];
    var xaxisCategories = options.xaxis && Array.isArray(options.xaxis.categories) ? options.xaxis.categories : [];
    var seriesNames = Array.isArray(options.series) ? options.series.map(function(series) { return series && series.name ? series.name : ''; }) : [];
    if (chart.type === 'donut' && labels.length === 2 && labels[0] === 'ชาย' && labels[1] === 'หญิง') return 'gender';
    if (chart.type === 'donut' && labels === d.genLabels) return 'generation';
    if (chart.type === 'donut' && labels === d.positionTypeLabels) return 'positionType';
    if (chart.type === 'bar' && chart.stacked && xaxisCategories === d.ageCategories) return 'age';
    if (chart.type === 'bar' && xaxisCategories === d.positionNameCategories) return 'positionName';
    if (chart.type === 'bar' && xaxisCategories === d.departmentLabels) return 'department';
    if (chart.type === 'bar' && xaxisCategories === d.serviceBandLabels) return 'serviceBand';
    if (chart.type === 'bar' && xaxisCategories === d.positionTypeLabels && seriesNames.length) return 'workgroup';
    return null;
  }
  var dashboardIndex = function(value) {
    return typeof value === 'number' && isFinite(value) && value >= 0 ? value : -1;
  };
  var getSeriesValue = function(opts) {
    if (!opts) return 0;
    var dataIndex = dashboardIndex(opts.dataPointIndex);
    var seriesIndex = dashboardIndex(opts.seriesIndex);
    if (Array.isArray(opts.series) && Array.isArray(opts.series[seriesIndex]) && dataIndex >= 0) {
      return Number(opts.series[seriesIndex][dataIndex]) || 0;
    }
    if (Array.isArray(opts.series) && dataIndex >= 0) {
      return Number(opts.series[dataIndex]) || 0;
    }
    if (Array.isArray(opts.series) && seriesIndex >= 0) {
      return Number(opts.series[seriesIndex]) || 0;
    }
    return 0;
  };
  var getTooltipContext = function(group, opts) {
    var index = opts ? dashboardIndex(opts.dataPointIndex) : -1;
    var seriesIndex = opts ? dashboardIndex(opts.seriesIndex) : -1;
    if (index < 0 && (group === 'gender' || group === 'generation' || group === 'positionType')) {
      index = seriesIndex;
    }
    var label = '';
    var seriesLabel = '';
    var bucketGroup = group;
    var key = '';
    var bucketKey = '';

    if (group === 'gender') {
      label = fallbackGenderLabels[index] || '';
      key = label;
    } else if (group === 'generation') {
      label = d.genLabels[index] || '';
      key = label;
    } else if (group === 'positionType') {
      label = d.positionTypeLabels[index] || '';
      key = d.positionTypeCodes[index] || label;
    } else if (group === 'workgroup') {
      // ชาร์ตกลุ่มงานเป็นแท่งนอนแบบซ้อน: แถว (dataPointIndex) = กลุ่มงาน, series = ประเภทพนักงาน
      var workgroup = d.workgroupRows[index] || {};
      var wgTypeIndex = Array.isArray(d.workgroupTypeIndexes) && d.workgroupTypeIndexes.length
        ? d.workgroupTypeIndexes[seriesIndex]
        : seriesIndex;
      label = workgroup.name || '';
      seriesLabel = d.positionTypeLabels[wgTypeIndex] || '';
      key = workgroup.code || label;
      if (wgTypeIndex >= 0 && d.positionTypeCodes[wgTypeIndex]) {
        bucketGroup = 'workgroupPositionType';
        key = String(key) + '|' + String(d.positionTypeCodes[wgTypeIndex]);
      }
    } else if (group === 'age') {
      label = d.ageCategories[index] || '';
      seriesLabel = fallbackGenderLabels[seriesIndex] || '';
      key = label;
      if (seriesLabel) {
        bucketGroup = 'ageGender';
        bucketKey = String(label) + '|' + String(seriesLabel).trim();
      }
    } else if (group === 'positionName') {
      label = d.positionNameCategories[index] || '';
      key = d.positionNameCodes[index] || label;
    } else if (group === 'department') {
      label = d.departmentLabels[index] || '';
      key = d.departmentCodes[index] || label;
    } else if (group === 'serviceBand') {
      label = d.serviceBandLabels[index] || '';
      key = label;
    }

    bucketKey = bucketKey || key;
    var people = ((tooltipPeople[bucketGroup] || {})[String(bucketKey)] || (tooltipPeople[group] || {})[String(key)] || []);
    return { label: label, seriesLabel: seriesLabel, people: Array.isArray(people) ? people : [] };
  };
  var renderAvatarTooltip = function(group, opts) {
    var context = getTooltipContext(group, opts || {});
    var value = getSeriesValue(opts || {});
    var absoluteValue = Math.abs(value);
    var displayCount = absoluteValue > 0 ? Math.min(5, absoluteValue) : 0;
    var people = context.people.slice(0, displayCount);
    var remaining = Math.max(0, absoluteValue - people.length);
    var title = context.seriesLabel ? context.label + ' · ' + context.seriesLabel : context.label;
    var avatars = people.map(function(person) {
      var name = person && person.name ? person.name : 'ไม่ระบุชื่อ';
      var src = person && person.avatar ? person.avatar : '';
      return '<img class="hr-avatar-stack__item" src="' + escapeHtml(src) + '" alt="' + escapeHtml(name) + '" title="' + escapeHtml(name) + '" loading="lazy">';
    }).join('');
    if (remaining > 0) {
      avatars += '<span class="hr-avatar-stack__item hr-avatar-stack__more">+' + escapeHtml(remaining) + '</span>';
    }
    return '<div class="hr-chart-tooltip">' +
      '<div class="hr-chart-tooltip__eyebrow">ตัวอย่างบุคลากรในข้อมูลนี้</div>' +
      '<div class="hr-chart-tooltip__title">' + escapeHtml(title || 'ข้อมูล') + '</div>' +
      '<div class="hr-chart-tooltip__meta">' + escapeHtml(absoluteValue.toLocaleString('th-TH')) + ' คน</div>' +
      (avatars ? '<div class="hr-avatar-stack">' + avatars + '</div>' : '<div class="hr-chart-tooltip__empty">ไม่มีรูปบุคลากรในกลุ่มนี้</div>') +
    '</div>';
  };
  var peoplePanelEl = null;
  var filterConfirmEl = null;
  var pendingFilterAction = null;
  var closePeoplePanel = function() {
    if (peoplePanelEl) {
      peoplePanelEl.classList.remove('is-open');
    }
  };

  var ensurePeoplePanel = function() {
    if (peoplePanelEl) {
      return peoplePanelEl;
    }

    peoplePanelEl = document.createElement('div');
    peoplePanelEl.className = 'hr-person-panel';
    peoplePanelEl.setAttribute('role', 'dialog');
    peoplePanelEl.setAttribute('aria-modal', 'true');
    peoplePanelEl.innerHTML =
      '<div class="hr-person-panel__backdrop" data-hr-person-close></div>' +
      '<div class="hr-person-panel__sheet">' +
        '<div class="hr-person-panel__header">' +
          '<div>' +
            '<div class="hr-person-panel__eyebrow">รายชื่อบุคลากรในข้อมูลนี้</div>' +
            '<div class="hr-person-panel__title" data-hr-person-title></div>' +
            '<div class="hr-person-panel__meta" data-hr-person-meta></div>' +
            '<div class="hr-person-panel__action-row" data-hr-filter-action-row>' +
              '<button type="button" class="hr-person-panel__filter-btn" data-hr-filter-apply>กรองข้อมูลนี้</button>' +
            '</div>' +
          '</div>' +
          '<button type="button" class="hr-person-panel__close" data-hr-person-close aria-label="ปิด">&times;</button>' +
        '</div>' +
        '<div class="hr-person-panel__list" data-hr-person-list></div>' +
      '</div>';

    peoplePanelEl.addEventListener('click', function(event) {
      if (event.target && event.target.hasAttribute('data-hr-filter-apply')) {
        applyPendingFilter();
        return;
      }
      if (event.target && event.target.hasAttribute('data-hr-person-close')) {
        closePeoplePanel();
      }
    });
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape') {
        closePeoplePanel();
      }
    });
    document.body.appendChild(peoplePanelEl);
    return peoplePanelEl;
  };

  var closeFilterConfirm = function() {
    if (filterConfirmEl) {
      filterConfirmEl.classList.remove('is-open');
    }
  };

  var applyPendingFilter = function() {
    if (!pendingFilterAction) {
      return;
    }
    applyFilter(pendingFilterAction.key, pendingFilterAction.value);
  };

  var ensureFilterConfirm = function() {
    if (filterConfirmEl) {
      return filterConfirmEl;
    }

    filterConfirmEl = document.createElement('div');
    filterConfirmEl.className = 'hr-filter-confirm';
    filterConfirmEl.innerHTML =
      '<div class="hr-filter-confirm__title" data-hr-filter-title></div>' +
      '<div class="hr-filter-confirm__meta">เลือกข้อมูลจากกราฟแล้ว</div>' +
      '<div class="hr-filter-confirm__actions">' +
        '<button type="button" class="hr-filter-confirm__cancel" data-hr-filter-cancel>ยกเลิก</button>' +
        '<button type="button" class="hr-filter-confirm__apply" data-hr-filter-apply>กรองข้อมูลนี้</button>' +
      '</div>';

    filterConfirmEl.addEventListener('click', function(event) {
      if (event.target && event.target.hasAttribute('data-hr-filter-apply')) {
        applyPendingFilter();
        return;
      }
      if (event.target && event.target.hasAttribute('data-hr-filter-cancel')) {
        closeFilterConfirm();
      }
    });
    document.body.appendChild(filterConfirmEl);
    return filterConfirmEl;
  };

  var setPeoplePanelFilterAction = function(action) {
    if (!peoplePanelEl) {
      return;
    }

    var actionRow = peoplePanelEl.querySelector('[data-hr-filter-action-row]');
    if (!actionRow) {
      return;
    }

    actionRow.classList.toggle('is-visible', !!action);
  };

  var requestFilter = function(key, value, label) {
    if (!key || value === undefined || value === null || value === '') {
      return;
    }

    pendingFilterAction = {
      key: key,
      value: value,
      label: label || value
    };

    if (peoplePanelEl && peoplePanelEl.classList.contains('is-open')) {
      setPeoplePanelFilterAction(pendingFilterAction);
      closeFilterConfirm();
      return;
    }

    var confirm = ensureFilterConfirm();
    confirm.querySelector('[data-hr-filter-title]').textContent = pendingFilterAction.label;
    confirm.classList.add('is-open');
  };

  var getSortedPeople = function(people) {
    return people.slice().sort(function(a, b) {
      var departmentCompare = String(a.department || '').localeCompare(String(b.department || ''), 'th');
      if (departmentCompare !== 0) return departmentCompare;
      var positionCompare = String(a.position || '').localeCompare(String(b.position || ''), 'th');
      if (positionCompare !== 0) return positionCompare;
      return String(a.name || '').localeCompare(String(b.name || ''), 'th');
    });
  };

  var openPeoplePanel = function(group, opts) {
    var context = getTooltipContext(group, opts || {});
    var value = Math.abs(getSeriesValue(opts || {}));
    var people = getSortedPeople(context.people || []);
    var totalCount = Math.max(value, people.length);
    var title = context.seriesLabel ? context.label + ' · ' + context.seriesLabel : context.label;
    var panel = ensurePeoplePanel();
    setPeoplePanelFilterAction(null);
    var metaText = people.length < totalCount
      ? 'แสดง ' + people.length.toLocaleString('th-TH') + ' จาก ' + totalCount.toLocaleString('th-TH') + ' คน'
      : people.length.toLocaleString('th-TH') + ' คน';

    panel.querySelector('[data-hr-person-title]').textContent = title || 'ข้อมูล';
    panel.querySelector('[data-hr-person-meta]').textContent = metaText;
    panel.querySelector('[data-hr-person-list]').innerHTML = people.length ? people.map(function(person) {
      var name = person && person.name ? person.name : 'ไม่ระบุชื่อ';
      var src = person && person.avatar ? person.avatar : '';
      var position = person && person.position ? person.position : 'ไม่ระบุตำแหน่ง';
      var department = person && person.department ? person.department : 'ไม่ระบุแผนก/ฝ่าย';
      var profileUrl = person && person.profileUrl ? person.profileUrl : '';
      return '<div class="hr-person-panel__row">' +
        '<img class="hr-person-panel__avatar" src="' + escapeHtml(src) + '" alt="' + escapeHtml(name) + '" loading="lazy">' +
        '<div class="hr-person-panel__content">' +
          '<div class="hr-person-panel__name">' + escapeHtml(name) + '</div>' +
          '<div class="hr-person-panel__detail">' + escapeHtml(position) + '</div>' +
          '<div class="hr-person-panel__detail">' + escapeHtml(department) + '</div>' +
        '</div>' +
        (profileUrl ? '<a class="hr-person-panel__profile-link" href="' + escapeHtml(profileUrl) + '" target="_blank" rel="noopener noreferrer">แสดงโปรไฟล์</a>' : '') +
      '</div>';
    }).join('') : '<div class="hr-chart-tooltip__empty">ไม่มีข้อมูลบุคลากรในกลุ่มนี้</div>';
    panel.classList.add('is-open');
  };

  var dashboardTooltip = function(group) {
    return { custom: function(opts) { return renderAvatarTooltip(group, opts); } };
  };
  var totalCount = Number(d.totalCount) || 0;
  var ensureArr = function(v) { return Array.isArray(v) ? v : []; };
  d.positionTypeLabels = ensureArr(d.positionTypeLabels);
  d.positionTypeCounts = ensureArr(d.positionTypeCounts);
  d.workgroupRows = ensureArr(d.workgroupRows);
  d.ageCategories = ensureArr(d.ageCategories);
  d.ageMale = ensureArr(d.ageMale);
  d.ageFemale = ensureArr(d.ageFemale);
  d.positionNameCategories = ensureArr(d.positionNameCategories);
  d.positionNameValues = ensureArr(d.positionNameValues);
  d.genLabels = ensureArr(d.genLabels);
  d.genSeries = ensureArr(d.genSeries);
  d.departmentLabels = ensureArr(d.departmentLabels);
  d.departmentValues = ensureArr(d.departmentValues);
  d.serviceBandLabels = ensureArr(d.serviceBandLabels);
  d.serviceBandValues = ensureArr(d.serviceBandValues);
  d.positionTypeCodes = ensureArr(d.positionTypeCodes);
  d.departmentCodes = ensureArr(d.departmentCodes);
  d.positionNameCodes = ensureArr(d.positionNameCodes);
  if (typeof d.filter === 'undefined') d.filter = {};

  function applyFilter(key, value) {
    var params = {};
    if (d.budgetYear && d.budgetYear !== d.currentBudgetYear) params.budget_year = d.budgetYear;
    if (d.filter.gender) params.gender = d.filter.gender;
    if (d.filter.department) params.department = d.filter.department;
    if (d.filter.employee_type_id) params.employee_type_id = d.filter.employee_type_id;
    if (d.filter.workgroup) params.workgroup = d.filter.workgroup;
    if (d.filter.gen) params.gen = d.filter.gen;
    if (d.filter.employee_position_id) params.employee_position_id = d.filter.employee_position_id;
    if (d.filter.service_band) params.service_band = d.filter.service_band;
    if (key && value !== undefined && value !== '') params[key] = value;
    else if (key) delete params[key];
    var q = Object.keys(params).map(function(k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); }).join('&');
    window.location = d.baseUrl + (q ? '?' + q : '');
  }

  var el = document.querySelector("#dashboardWorkgroupChart");
  if (el && d.workgroupRows.length && d.positionTypeLabels.length) {
    // แท่งนอนแบบซ้อน: 1 แถว = 1 กลุ่มงาน, สีในแถบ = ประเภทพนักงาน
    // (เดิมทำเป็นแท่งตั้งโดยให้กลุ่มงานเป็น series ทำให้มี 16 สีซ้อนกันในทุกหมวด อ่านไม่ได้)
    var wgLabels = d.workgroupRows.map(function(r) { return r.name || 'ไม่ระบุ'; });
    var wgCodes = d.workgroupRows.map(function(r) { return r.code || ''; });
    var wgSeries = [];
    // จำ index เดิมของประเภทพนักงานไว้ เพราะ series ที่ไม่มีคนเลยถูกตัดออกจาก legend
    d.workgroupTypeIndexes = [];
    d.positionTypeLabels.forEach(function(label, typeIndex) {
      var data = d.workgroupRows.map(function(r) {
        return Array.isArray(r.data) && r.data[typeIndex] ? r.data[typeIndex] : 0;
      });
      if (data.some(function(v) { return v > 0; })) {
        wgSeries.push({ name: label, data: data });
        d.workgroupTypeIndexes.push(typeIndex);
      }
    });
    var wgNarrow = window.innerWidth < 576;
    var wgRowHeight = wgNarrow ? 34 : 30;
    var wgHeight = Math.max(300, 96 + wgLabels.length * wgRowHeight);
    el.style.minHeight = wgHeight + 'px';
    new ApexCharts(el, withDashboardChartDefaults({
      hrTooltipGroup: 'workgroup',
      series: wgSeries,
      chart: {
        type: 'bar',
        stacked: true,
        height: wgHeight,
        events: { dataPointSelection: function(e, chart, opts) {
          var wcode = wgCodes[opts.dataPointIndex];
          if (wcode) requestFilter('workgroup', wcode, wgLabels[opts.dataPointIndex] || wcode);
        } }
      },
      colors: chartPalette,
      plotOptions: { bar: {
        horizontal: true,
        barHeight: '68%',
        borderRadius: 4,
        borderRadiusApplication: 'end',
        dataLabels: {
          total: {
            enabled: true,
            offsetX: 6,
            // ยอดรวมท้ายแท่งต้องแสดงทุกแถว ไม่ใช้เกณฑ์ซ่อนเลขน้อยแบบชิ้นย่อย
            formatter: function(v) { return v; },
            style: { fontSize: '12px', fontWeight: 600, color: palette.ink }
          }
        }
      } },
      dataLabels: {
        enabled: true,
        // ตัวเลขในแถบจะอ่านออกเมื่อชิ้นกว้างพอเท่านั้น ชิ้นเล็กปล่อยว่างไว้ให้ tooltip บอกแทน
        formatter: function(v) { return v >= 5 ? v : ''; },
        style: { fontSize: '11px', fontWeight: 500 }
      },
      stroke: { show: true, width: 1, colors: [palette.surface] },
      xaxis: {
        categories: wgLabels,
        title: { text: 'จำนวนคน' },
        labels: { formatter: function(v) { return Math.round(v); } }
      },
      yaxis: { labels: { maxWidth: wgNarrow ? 118 : 260, style: { fontSize: wgNarrow ? '11px' : '12px' } } },
      fill: { opacity: 1 },
      tooltip: { shared: true, intersect: false, y: { formatter: function(v) { return v + ' คน'; } } },
      legend: { position: 'bottom', horizontalAlign: 'left', itemMargin: { horizontal: 8, vertical: 4 } },
      grid: { padding: { left: 8, right: 24 } }
    })).render();
  }

  el = document.querySelector("#dashboardAgeChart");
  if (el) {
    new ApexCharts(el, withDashboardChartDefaults({
      series: [{ name: 'ชาย', data: d.ageMale }, { name: 'หญิง', data: d.ageFemale }],
      chart: { type: 'bar', height: 300, stacked: true },
      colors: genderPalette,
      plotOptions: {
        bar: {
          borderRadius: 6,
          borderRadiusApplication: 'end',
          borderRadiusWhenStacked: 'all',
          horizontal: true,
          barHeight: '70%'
        }
      },
      dataLabels: { enabled: true, formatter: function(v) { return (totalCount ? Math.abs(Math.round(v * 100 / totalCount)) : 0) + '%'; } },
      stroke: { width: 1, colors: [palette.surface] },
      xaxis: { categories: d.ageCategories },
      grid: { xaxis: { lines: { show: false } }, padding: { left: 8, right: 8 } }
    })).render();
  }

  el = document.querySelector("#dashboardPositionNameChart");
  if (el && d.positionNameCategories.length) {
    var pnCount = d.positionNameCategories.length;
    var pnHeight = Math.max(280, Math.min(360, pnCount * 28));
    new ApexCharts(el, withDashboardChartDefaults({
      series: [{ name: 'จำนวนคน', data: d.positionNameValues }],
      chart: { type: 'bar', height: pnHeight, events: { dataPointSelection: function(e, chart, opts) {
        var code = d.positionNameCodes[opts.dataPointIndex];
        if (code != null && code !== '') requestFilter('employee_position_id', code, d.positionNameCategories[opts.dataPointIndex] || code);
      } } },
      plotOptions: { bar: { horizontal: true, distributed: true, borderRadius: 6, barHeight: '68%', dataLabels: { position: 'top' } } },
      dataLabels: { enabled: true },
      xaxis: { categories: d.positionNameCategories, tickAmount: 6 },
      yaxis: { labels: { maxWidth: 200 } },
      grid: { padding: { left: 8, right: 8 } },
      colors: chartPalette,
      legend: { show: false }
    })).render();
  }

  el = document.querySelector("#dashboardGenderPie");
  if (el) {
    new ApexCharts(el, withDashboardChartDefaults({
      chart: { type: 'donut', height: 260, events: { dataPointSelection: function(e, chart, opts) {
        var g = ['ชาย', 'หญิง'][opts.dataPointIndex];
        if (g) requestFilter('gender', g, g);
      } } },
      labels: ['ชาย', 'หญิง'],
      series: [Number(d.countMale) || 0, Number(d.countFemale) || 0],
      colors: genderPalette,
      stroke: { width: 0 },
      legend: { position: 'bottom', horizontalAlign: 'center' },
      plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'ทั้งหมด' } } } } }
    })).render();
  }

  el = document.querySelector("#dashboardGenerationPie");
  if (el && d.genLabels.length && d.genSeries.length) {
    new ApexCharts(el, withDashboardChartDefaults({
      chart: { type: 'donut', height: 260, events: { dataPointSelection: function(e, chart, opts) {
        var gen = d.genLabels[opts.dataPointIndex];
        if (gen) requestFilter('gen', gen, gen);
      } } },
      labels: d.genLabels,
      series: d.genSeries,
      colors: chartPalette,
      stroke: { width: 0 },
      legend: { position: 'bottom', horizontalAlign: 'center' },
      plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'ทั้งหมด' } } } } }
    })).render();
  }

  el = document.querySelector("#dashboardPositionTypePie");
  if (el && d.positionTypeLabels.length && d.positionTypeCounts.length) {
    new ApexCharts(el, withDashboardChartDefaults({
      chart: { type: 'donut', height: 260, events: { dataPointSelection: function(e, chart, opts) {
        var code = d.positionTypeCodes[opts.dataPointIndex];
        if (code) requestFilter('employee_type_id', code, d.positionTypeLabels[opts.dataPointIndex] || code);
      } } },
      labels: d.positionTypeLabels,
      series: d.positionTypeCounts,
      colors: chartPalette,
      stroke: { width: 0 },
      legend: { position: 'bottom', horizontalAlign: 'center' },
      plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'ทั้งหมด' } } } } }
    })).render();
  }

  el = document.querySelector("#dashboardDepartmentChart");
  if (el && d.departmentLabels.length) {
    var deptCount = d.departmentLabels.length;
    var deptHeight = Math.min(440, Math.max(260, deptCount * 28));
    new ApexCharts(el, withDashboardChartDefaults({
      series: [{ name: 'จำนวนคน', data: d.departmentValues }],
      chart: { type: 'bar', height: deptHeight, events: { dataPointSelection: function(e, chart, opts) {
        var code = d.departmentCodes[opts.dataPointIndex];
        if (code != null && code !== '') requestFilter('department', code, d.departmentLabels[opts.dataPointIndex] || code);
      } } },
      plotOptions: { bar: { horizontal: true, distributed: true, borderRadius: 6, barHeight: '68%', dataLabels: { position: 'top' } } },
      dataLabels: { enabled: true },
      xaxis: { categories: d.departmentLabels, tickAmount: 6 },
      yaxis: { labels: { maxWidth: 200 } },
      grid: { padding: { left: 8, right: 8 } },
      colors: chartPalette,
      legend: { show: false }
    })).render();
  }

  el = document.querySelector("#dashboardServiceBandChart");
  if (el && d.serviceBandLabels.length) {
    new ApexCharts(el, withDashboardChartDefaults({
      series: [{ name: 'จำนวนคน', data: d.serviceBandValues }],
      chart: { type: 'bar', height: 300, events: { dataPointSelection: function(e, chart, opts) {
        var label = d.serviceBandLabels[opts.dataPointIndex];
        if (label) requestFilter('service_band', label, label);
      } } },
      plotOptions: { bar: { distributed: true, borderRadius: 6, columnWidth: '52%', dataLabels: { position: 'top' } } },
      dataLabels: { enabled: true },
      xaxis: { categories: d.serviceBandLabels, labels: { rotate: -25, maxWidth: 100 } },
      yaxis: { tickAmount: 6 },
      grid: { padding: { left: 8, right: 8 } },
      colors: chartPalette,
      legend: { show: false }
    })).render();
  }

  // แผงตรวจสอบใต้การ์ด KPI: กดการ์ดเพื่อกางรายการที่อยู่เบื้องหลังตัวเลข
  (function initKpiDetail() {
    var panel = document.getElementById('hrKpiDetail');
    var cards = Array.prototype.slice.call(document.querySelectorAll('.hr-kpi'));
    if (!panel || !cards.length) return;

    var sources = d.kpiDetail || {};
    var titleEl = document.getElementById('hrKpiDetailTitle');
    var countEl = document.getElementById('hrKpiDetailCount');
    var linkEl = document.getElementById('hrKpiDetailLink');
    var searchEl = document.getElementById('hrKpiDetailSearch');
    var closeEl = document.getElementById('hrKpiDetailClose');
    var tableEl = document.getElementById('hrKpiDetailTable');
    var emptyEl = document.getElementById('hrKpiDetailEmpty');
    var headRow = tableEl.querySelector('thead tr');
    var bodyEl = tableEl.querySelector('tbody');
    var state = { key: null, columns: [], rows: [], sortIndex: null, sortDir: 'asc' };

    function cellText(cell) {
      if (cell === null || cell === undefined) return '';
      if (typeof cell === 'object') return String(cell.t === undefined ? '' : cell.t);
      return String(cell);
    }
    function cellSortValue(cell) {
      if (cell === null || cell === undefined) return '';
      if (typeof cell === 'object') return cell.v === undefined ? '' : cell.v;
      return cell;
    }
    function isNumeric(value) {
      return typeof value === 'number' || (value !== '' && !isNaN(Number(value)));
    }

    function visibleRows() {
      var term = (searchEl.value || '').trim().toLowerCase();
      var rows = state.rows;
      if (term) {
        rows = rows.filter(function(row) {
          return row.some(function(cell) { return cellText(cell).toLowerCase().indexOf(term) !== -1; });
        });
      }
      if (state.sortIndex !== null) {
        var i = state.sortIndex;
        var dir = state.sortDir === 'desc' ? -1 : 1;
        rows = rows.slice().sort(function(a, b) {
          var av = cellSortValue(a[i]);
          var bv = cellSortValue(b[i]);
          if (isNumeric(av) && isNumeric(bv)) return (Number(av) - Number(bv)) * dir;
          return String(av).localeCompare(String(bv), 'th') * dir;
        });
      }
      return rows;
    }

    function renderRows() {
      var rows = visibleRows();
      var html = rows.map(function(row, index) {
        var cells = row.map(function(cell, ci) {
          var align = (state.columns[ci] && state.columns[ci].align) === 'end' ? ' class="text-end is-num"' : '';
          var text = cellText(cell)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
          return '<td' + align + '>' + text + '</td>';
        }).join('');
        return '<tr><th scope="row" class="fw-normal text-body-secondary is-num">' + (index + 1) + '</th>' + cells + '</tr>';
      }).join('');
      bodyEl.innerHTML = html;
      emptyEl.hidden = rows.length > 0;
      countEl.textContent = rows.length === state.rows.length
        ? state.rows.length + ' รายการ'
        : rows.length + ' / ' + state.rows.length + ' รายการ';
    }

    function renderHead() {
      var html = '<th scope="col" class="text-body-secondary" data-nosort>#</th>';
      html += state.columns.map(function(col, i) {
        var align = col.align === 'end' ? ' class="text-end"' : '';
        var sorted = state.sortIndex === i ? (state.sortDir === 'desc' ? 'descending' : 'ascending') : 'none';
        return '<th scope="col"' + align + ' data-col="' + i + '" aria-sort="' + sorted + '">' + col.label + '</th>';
      }).join('');
      headRow.innerHTML = html;
    }

    function open(card) {
      var source = sources[card.dataset.source];
      if (!source) return;
      state.key = card.dataset.kpi;
      state.columns = source.columns || [];
      state.rows = source.rows || [];
      var sortIndex = card.dataset.sortIndex;
      state.sortIndex = sortIndex === undefined ? null : parseInt(sortIndex, 10);
      state.sortDir = card.dataset.sortDir || 'asc';
      titleEl.textContent = card.dataset.title || '';
      searchEl.value = '';
      if (card.dataset.link) {
        linkEl.href = card.dataset.link;
        linkEl.textContent = card.dataset.linkLabel || 'เปิดหน้าเต็ม';
        linkEl.hidden = false;
      } else {
        linkEl.hidden = true;
      }
      cards.forEach(function(c) { c.setAttribute('aria-expanded', String(c === card)); });
      renderHead();
      renderRows();
      panel.hidden = false;
      panel.classList.remove('is-open');
      void panel.offsetWidth;
      panel.classList.add('is-open');
      titleEl.focus();
    }

    function close() {
      panel.hidden = true;
      panel.classList.remove('is-open');
      cards.forEach(function(c) { c.setAttribute('aria-expanded', 'false'); });
      state.key = null;
    }

    cards.forEach(function(card) {
      card.addEventListener('click', function() {
        if (state.key === card.dataset.kpi) { close(); return; }
        open(card);
      });
    });

    headRow.addEventListener('click', function(event) {
      var th = event.target.closest('th[data-col]');
      if (!th) return;
      var index = parseInt(th.dataset.col, 10);
      if (state.sortIndex === index) {
        state.sortDir = state.sortDir === 'asc' ? 'desc' : 'asc';
      } else {
        state.sortIndex = index;
        state.sortDir = 'asc';
      }
      renderHead();
      renderRows();
    });

    searchEl.addEventListener('input', renderRows);
    closeEl.addEventListener('click', close);
    document.addEventListener('keydown', function(event) {
      if (event.key === 'Escape' && !panel.hidden) { close(); }
    });
  })();
})();
JS;
$this->registerJS($js, View::POS_END);
?>

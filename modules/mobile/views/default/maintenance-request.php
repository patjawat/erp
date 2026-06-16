<?php

use app\components\ThaiDateHelper;
use app\modules\mobile\services\MobileMaintenanceService;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var MobileMaintenanceService $service */
/** @var \app\modules\helpdesk2\models\Helpdesk[] $myRequests */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */
/** @var int $currentYear */
/** @var string $bucket */
/** @var array{all:int,waiting:int,in_progress:int,done:int,cancelled:int,other:int} $kpi */

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'งานแจ้งซ่อมของฉัน';

$myRequests  = $myRequests ?? [];
$fiscalYears = $fiscalYears ?? [];
$filterYear  = (int) ($filterYear ?? 0);
$bucket      = $bucket ?? 'all';
$kpi         = $kpi ?? ['all' => 0, 'waiting' => 0, 'in_progress' => 0, 'done' => 0, 'cancelled' => 0, 'other' => 0];

$fiscalLabel = $fiscalYears[$filterYear] ?? ($filterYear > 0 ? 'พ.ศ. ' . $filterYear : 'ปีปัจจุบัน');
$this->params['mobileSubtitle'] = 'ปีงบประมาณ ' . $fiscalLabel . ' · ' . (int) $kpi['all'] . ' รายการ';

$baseUrl = Url::to(['/mobile/default/maintenance-request']);
$newUrl  = Url::to(['/mobile/default/repair-request']);

$chips = [
    ['key' => 'all',          'label' => 'ทั้งหมด',     'count' => (int) $kpi['all'],         'tone' => 'primary'],
    ['key' => 'waiting',      'label' => 'รอรับเรื่อง',  'count' => (int) $kpi['waiting'],     'tone' => 'warning'],
    ['key' => 'in_progress',  'label' => 'กำลังซ่อม',    'count' => (int) $kpi['in_progress'], 'tone' => 'info'],
    ['key' => 'done',         'label' => 'เสร็จแล้ว',    'count' => (int) $kpi['done'],        'tone' => 'success'],
    ['key' => 'cancelled',    'label' => 'ยกเลิก',       'count' => (int) $kpi['cancelled'],   'tone' => 'danger'],
];

$emptyMessages = [
    'all'         => ['title' => 'ยังไม่มีงานแจ้งซ่อม',       'text' => 'เริ่มต้นด้วยการแตะปุ่ม "แจ้งซ่อมใหม่" ด้านล่าง',       'icon' => 'wrench'],
    'waiting'     => ['title' => 'ไม่มีงานรอรับเรื่อง',         'text' => 'งานที่ส่งใหม่จะรอช่างรับเรื่องที่ขั้นตอนนี้',          'icon' => 'inbox'],
    'in_progress' => ['title' => 'ไม่มีงานกำลังดำเนินการ',    'text' => 'เมื่อช่างรับเรื่องและเริ่มซ่อม รายการจะปรากฏที่นี่',     'icon' => 'hard-hat'],
    'done'        => ['title' => 'ยังไม่มีงานที่ซ่อมเสร็จ',     'text' => 'รายการที่ซ่อมเสร็จและรอลงคะแนนจะมาอยู่ที่นี่',         'icon' => 'check-check'],
    'cancelled'   => ['title' => 'ไม่มีงานที่ถูกยกเลิก',        'text' => 'งานที่ยกเลิกหรือจำหน่ายแล้วจะปรากฏที่หน้านี้',         'icon' => 'circle-slash'],
];
$empty = $emptyMessages[$bucket] ?? $emptyMessages['all'];
?>

<style>
.mr-root {
    margin: -1rem -1rem 0;
    display: flex;
    flex-direction: column;
}
.mr-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 9.5rem);
}
.mr-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    padding: var(--space-md);
}
.mr-flash {
    border-radius: 14px;
    padding: var(--space-sm) var(--space-md);
    font-size: var(--fs-sm);
    line-height: 1.45;
    border: 1px solid transparent;
}
.mr-flash[data-tone="success"] { background: var(--success-soft); border-color: color-mix(in oklch, var(--success) 18%, transparent); color: var(--success); }
.mr-flash[data-tone="danger"]  { background: var(--danger-soft);  border-color: color-mix(in oklch, var(--danger)  18%, transparent); color: var(--danger-strong); }

/* Sticky toolbar (chips + filter + search) */
.mr-toolbar {
    position: sticky;
    top: var(--shell-h, 13rem);
    z-index: calc(var(--z-sticky) - 1);
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
    padding: var(--space-sm);
    border-radius: 14px;
    background: var(--surface);
    box-shadow: 0 1px 0 var(--ink-line), 0 6px 18px color-mix(in oklch, var(--ink) 5%, transparent);
}

/* KPI chips (horizontal scroll) */
.mr-chips {
    display: flex;
    gap: var(--space-2xs);
    overflow-x: auto;
    overflow-y: hidden;
    scroll-snap-type: x proximity;
    -webkit-overflow-scrolling: touch;
    margin: 0 calc(var(--space-sm) * -1);
    padding: 0 var(--space-sm) 2px;
    scrollbar-width: none;
}
.mr-chips::-webkit-scrollbar { display: none; }
.mr-chip {
    flex: 0 0 auto;
    scroll-snap-align: start;
    display: inline-flex;
    align-items: center;
    gap: var(--space-2xs);
    min-height: 2.5rem;
    padding: 0 var(--space-sm) 0 var(--space-md);
    border-radius: 999px;
    border: 1px solid var(--ink-line);
    background: var(--surface);
    color: var(--ink-2);
    font-size: var(--fs-sm);
    font-weight: 700;
    text-decoration: none;
    -webkit-tap-highlight-color: transparent;
    transition:
        transform 160ms cubic-bezier(0.16, 1, 0.3, 1),
        background 160ms cubic-bezier(0.16, 1, 0.3, 1),
        border-color 160ms cubic-bezier(0.16, 1, 0.3, 1),
        color 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.mr-chip-count {
    min-width: 1.5rem;
    padding: 0 var(--space-2xs);
    border-radius: 999px;
    background: var(--surface-2);
    color: var(--ink-3);
    font-size: var(--fs-2xs);
    font-weight: 800;
    line-height: 1.6;
    text-align: center;
}
.mr-chip.is-active {
    background: var(--mobile-primary);
    border-color: var(--mobile-primary);
    color: #fff;
}
.mr-chip.is-active .mr-chip-count {
    background: color-mix(in oklch, white 22%, var(--mobile-primary));
    color: #fff;
}
.mr-chip[data-tone="warning"].is-active  { background: var(--warning); border-color: var(--warning); }
.mr-chip[data-tone="warning"].is-active .mr-chip-count  { background: color-mix(in oklch, white 22%, var(--warning)); }
.mr-chip[data-tone="success"].is-active  { background: var(--success); border-color: var(--success); }
.mr-chip[data-tone="success"].is-active .mr-chip-count  { background: color-mix(in oklch, white 22%, var(--success)); }
.mr-chip[data-tone="danger"].is-active   { background: var(--danger);  border-color: var(--danger); }
.mr-chip[data-tone="danger"].is-active .mr-chip-count   { background: color-mix(in oklch, white 22%, var(--danger)); }
.mr-chip[data-tone="info"].is-active     { background: oklch(0.55 0.13 240); border-color: oklch(0.55 0.13 240); }
.mr-chip[data-tone="info"].is-active .mr-chip-count     { background: color-mix(in oklch, white 22%, oklch(0.55 0.13 240)); }
.mr-chip:active { transform: scale(0.96); }

/* Search + year filter row */
.mr-filter-row {
    display: grid;
    grid-template-columns: 1fr auto;
    gap: var(--space-sm);
    align-items: stretch;
}
.mr-search {
    display: block;
    width: 100%;
    min-height: 2.75rem;
    border: 1px solid transparent;
    border-radius: 12px;
    background: var(--surface-2)
        url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'><circle cx='11' cy='11' r='8'/><path d='m21 21-4.3-4.3'/></svg>")
        no-repeat 0.75rem center;
    color: var(--ink);
    font-size: var(--fs-md);
    padding: 0.65rem 0.9rem 0.65rem 2.45rem;
    -webkit-appearance: none;
    appearance: none;
    transition: border-color 160ms ease-out, box-shadow 160ms ease-out, background-color 160ms ease-out;
}
.mr-search::placeholder { color: var(--ink-4); }
.mr-search:focus {
    outline: 0;
    background-color: var(--surface);
    border-color: var(--mobile-primary);
    box-shadow: 0 0 0 3px var(--mobile-primary-soft-border);
}
.mr-search::-webkit-search-cancel-button { -webkit-appearance: none; display: none; }
.mr-yearbar { margin: 0; }
.mr-yearbar.mobile-year-filter {
    min-height: 2.75rem;
    padding: 0 var(--space-xs);
    border-radius: 12px;
}

/* List */
.mr-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}
.mr-card {
    display: grid;
    gap: var(--space-xs);
    padding: var(--space-md);
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    color: inherit;
    text-decoration: none;
    -webkit-tap-highlight-color: transparent;
    transition:
        transform 180ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 180ms cubic-bezier(0.16, 1, 0.3, 1),
        border-color 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.mr-card[hidden] { display: none; }
.mr-card:focus-visible {
    outline: 2px solid var(--mobile-primary);
    outline-offset: 2px;
}
.mr-card:active { transform: scale(0.992); }
.mr-card-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-sm);
}
.mr-code {
    color: var(--ink-4);
    font-family: ui-monospace, "SF Mono", Menlo, Consolas, monospace;
    font-size: var(--fs-xs);
    font-weight: 700;
    letter-spacing: 0.02em;
}
.mr-pill {
    flex-shrink: 0;
    border-radius: 999px;
    padding: 4px 10px;
    font-size: var(--fs-2xs);
    font-weight: 800;
    line-height: 1.3;
    white-space: nowrap;
}
.mr-pill[data-tone="warning"]   { background: var(--warning-soft);  color: var(--warning); }
.mr-pill[data-tone="info"]      { background: color-mix(in oklch, oklch(0.55 0.13 240) 14%, transparent); color: oklch(0.45 0.13 240); }
.mr-pill[data-tone="success"]   { background: var(--success-soft);  color: var(--success); }
.mr-pill[data-tone="danger"]    { background: var(--danger-soft);   color: var(--danger-strong); }
.mr-pill[data-tone="secondary"] { background: color-mix(in oklch, var(--ink-4) 18%, transparent); color: var(--ink-3); }
.mr-card-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 800;
    line-height: 1.35;
    word-break: break-word;
    text-wrap: pretty;
}
.mr-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 0.35rem var(--space-md);
    color: var(--ink-3);
    font-size: var(--fs-xs);
}
.mr-meta-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.mr-meta-item svg {
    width: 0.85rem;
    height: 0.85rem;
    color: var(--ink-4);
    flex-shrink: 0;
}
.mr-meta-item.is-rating {
    color: #b45309;
    font-weight: 800;
}
.mr-meta-item.is-rating svg { color: #f59e0b; fill: currentColor; }

.mr-no-results {
    border-radius: 12px;
    background: var(--surface-2);
    color: var(--ink-3);
    font-size: var(--fs-sm);
    padding: var(--space-md);
    text-align: center;
}
.mr-no-results[hidden] { display: none; }

/* Empty state */
.mr-empty {
    padding: var(--space-2xl) var(--space-md);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    text-align: center;
}
.mr-empty-icon {
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--space-md);
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    box-shadow: inset 0 0 0 6px color-mix(in oklch, var(--mobile-primary) 5%, transparent);
}
.mr-empty-icon svg { width: 1.75rem; height: 1.75rem; }
.mr-empty-title {
    margin: 0 0 var(--space-2xs);
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 800;
}
.mr-empty-text {
    margin: 0 auto;
    max-width: 32ch;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.55;
}

/* Bottom sticky CTA — สูงพอที่จะลอยเหนือ .mobile-bottom-nav (min-height 4.75rem + safe-area) */
.mr-actions {
    position: fixed;
    left: 0;
    right: 0;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 4.75rem);
    z-index: var(--z-sticky, 50);
    padding: var(--space-sm) var(--space-md);
    background: color-mix(in oklch, var(--surface) 94%, transparent);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    box-shadow: 0 -1px 0 var(--ink-line), 0 -10px 28px color-mix(in oklch, var(--ink) 8%, transparent);
}
.mr-actions .btn {
    width: 100%;
    min-height: 3.25rem;
    border-radius: 14px;
    font-size: var(--fs-md);
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-xs);
    transition: transform 140ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 140ms cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 4px 14px color-mix(in oklch, var(--mobile-primary) 35%, transparent);
}
.mr-actions .btn:active { transform: translateY(1px) scale(0.985); }
.mr-actions .btn svg { width: 1.125rem; height: 1.125rem; }

@media (hover: hover) {
    .mr-card:hover {
        color: inherit;
        transform: translateY(-2px);
        border-color: var(--mobile-primary-soft-border);
        box-shadow: var(--shadow-md);
    }
    .mr-chip:hover {
        color: var(--mobile-primary);
        border-color: var(--mobile-primary-soft-border);
    }
    .mr-chip.is-active:hover { color: #fff; }
    .mr-actions .btn:hover { box-shadow: var(--shadow-lg); }
}

@media (prefers-reduced-motion: reduce) {
    .mr-card, .mr-chip, .mr-search, .mr-toolbar, .mr-actions .btn {
        transition: none !important;
        animation: none !important;
    }
    .mr-card:hover, .mr-card:active, .mr-chip:active { transform: none !important; }
}
@media (prefers-reduced-motion: no-preference) {
    .mr-toolbar { animation: mr-fade-down 240ms cubic-bezier(0.16, 1, 0.3, 1) both; }
    .mr-card {
        animation: mr-card-in 280ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--mr-i, 0) * 28ms);
    }
    .mr-empty { animation: mr-fade-up 360ms cubic-bezier(0.16, 1, 0.3, 1) both; }
    .mr-empty-icon { animation: mr-pulse 2400ms cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    .mr-actions { animation: mr-bar-up 320ms cubic-bezier(0.16, 1, 0.3, 1) both; animation-delay: 100ms; }
}
@keyframes mr-fade-down { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
@keyframes mr-fade-up   { from { opacity: 0; transform: translateY(8px); }  to { opacity: 1; transform: translateY(0); } }
@keyframes mr-card-in   { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes mr-bar-up    { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
@keyframes mr-pulse {
    0%, 100% { box-shadow: inset 0 0 0 6px  color-mix(in oklch, var(--mobile-primary) 5%, transparent); }
    50%      { box-shadow: inset 0 0 0 10px color-mix(in oklch, var(--mobile-primary) 7%, transparent); }
}
</style>

<div class="mr-root">

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'       => 'wrench',
        'title'      => $this->params['mobileTitle'],
        'subtitle'   => $this->params['mobileSubtitle'],
        'stats'      => [
            ['value' => (int) $kpi['waiting'],     'label' => 'รอรับ',     'tone' => 'warning'],
            ['value' => (int) $kpi['in_progress'], 'label' => 'กำลังซ่อม', 'tone' => 'primary'],
            ['value' => (int) $kpi['done'],        'label' => 'เสร็จแล้ว', 'tone' => 'success'],
            ['value' => (int) $kpi['all'],         'label' => 'รวมทั้งหมด', 'tone' => 'primary'],
        ],
        'statsLabel' => 'สรุปจำนวนงานแจ้งซ่อม',
    ]) ?>

    <div class="app-scroll has-stats mr-scroll">
        <div class="mr-body">

            <?php if (Yii::$app->session->hasFlash('success')): ?>
                <div class="mr-flash" data-tone="success" role="status">
                    <?= Html::encode(Yii::$app->session->getFlash('success')) ?>
                </div>
            <?php endif; ?>
            <?php if (Yii::$app->session->hasFlash('error')): ?>
                <div class="mr-flash" data-tone="danger" role="alert">
                    <?= Html::encode(Yii::$app->session->getFlash('error')) ?>
                </div>
            <?php endif; ?>

            <div class="mr-toolbar" role="region" aria-label="ตัวกรองงานแจ้งซ่อม">
                <nav class="mr-chips" aria-label="กรองตามสถานะ">
                    <?php foreach ($chips as $chip):
                        $isActive = $bucket === $chip['key'];
                        $chipUrl  = ['/mobile/default/maintenance-request', 'year' => $filterYear, 'bucket' => $chip['key']];
                    ?>
                        <a href="<?= Html::encode(Url::to($chipUrl)) ?>"
                           class="mr-chip<?= $isActive ? ' is-active' : '' ?>"
                           data-tone="<?= Html::encode($chip['tone']) ?>"
                           aria-current="<?= $isActive ? 'page' : 'false' ?>">
                            <span><?= Html::encode($chip['label']) ?></span>
                            <span class="mr-chip-count"><?= (int) $chip['count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="mr-filter-row">
                    <input type="search"
                           id="mr-search"
                           class="mr-search"
                           placeholder="ค้นหารหัส, หัวข้อ, สถานที่"
                           autocomplete="off"
                           aria-label="ค้นหารายการแจ้งซ่อม">
                    <form method="get" action="<?= Html::encode($baseUrl) ?>" class="mr-yearbar mobile-year-filter">
                        <input type="hidden" name="bucket" value="<?= Html::encode($bucket) ?>">
                        <label for="mr-year-filter" class="mobile-year-filter-label visually-hidden">ปีงบประมาณ</label>
                        <i data-lucide="calendar-days" aria-hidden="true"></i>
                        <select name="year" id="mr-year-filter" class="mobile-year-filter-select"
                                onchange="this.form.submit()" aria-label="กรองปีงบประมาณ">
                            <?php foreach ($fiscalYears as $year => $label): ?>
                                <?php $year = (int) $year; ?>
                                <option value="<?= $year ?>" <?= $filterYear === $year ? 'selected' : '' ?>>
                                    <?= Html::encode($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <?php if (empty($myRequests)): ?>
                <div class="mr-empty" role="status">
                    <span class="mr-empty-icon" aria-hidden="true">
                        <i data-lucide="<?= Html::encode($empty['icon']) ?>"></i>
                    </span>
                    <p class="mr-empty-title"><?= Html::encode($empty['title']) ?></p>
                    <p class="mr-empty-text"><?= Html::encode($empty['text']) ?></p>
                </div>
            <?php else: ?>
                <div class="mr-list" id="mr-list">
                    <?php foreach ($myRequests as $i => $row):
                        $info      = $service->statusInfo((string) $row->status);
                        $infoTone  = (string) $info['tone'];
                        $infoLabel = (string) $info['label'];
                        $title     = (string) ($row->title ?? 'งานซ่อม');
                        $repairNo  = (string) ($row->repair_number ?? '');
                        $createdAt = '';
                        if (!empty($row->created_at)) {
                            try { $createdAt = ThaiDateHelper::formatThaiDate((string) $row->created_at, 'short'); }
                            catch (\Throwable $e) { $createdAt = (string) $row->created_at; }
                        }
                        $rating   = trim((string) ($row->rating ?? ''));
                        $location = (string) (is_array($row->data_json ?? null) ? ($row->data_json['location'] ?? '') : '');
                        $assetCode = (string) (is_array($row->data_json ?? null) ? ($row->data_json['asset_number'] ?? $row->data_json['asset_code'] ?? '') : '');

                        $searchText = mb_strtolower(implode(' ', array_filter([
                            $repairNo, $title, $location, $assetCode, $infoLabel,
                        ])), 'UTF-8');
                    ?>
                        <a class="mr-card"
                           href="<?= Html::encode(Url::to(['/mobile/default/maintenance-view', 'id' => $row->id])) ?>"
                           style="--mr-i: <?= (int) min($i, 12) ?>"
                           data-search="<?= Html::encode($searchText) ?>">
                            <header class="mr-card-head">
                                <span class="mr-code"><?= Html::encode($repairNo !== '' ? $repairNo : '#' . (int) $row->id) ?></span>
                                <span class="mr-pill" data-tone="<?= Html::encode($infoTone) ?>"><?= Html::encode($infoLabel) ?></span>
                            </header>
                            <h2 class="mr-card-title"><?= Html::encode($title) ?></h2>
                            <div class="mr-meta">
                                <?php if ($createdAt !== ''): ?>
                                    <span class="mr-meta-item">
                                        <i data-lucide="calendar" aria-hidden="true"></i>
                                        <?= Html::encode($createdAt) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($location !== ''): ?>
                                    <span class="mr-meta-item">
                                        <i data-lucide="map-pin" aria-hidden="true"></i>
                                        <?= Html::encode($location) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($assetCode !== ''): ?>
                                    <span class="mr-meta-item">
                                        <i data-lucide="qr-code" aria-hidden="true"></i>
                                        <?= Html::encode($assetCode) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if ($rating !== '' && $rating !== '0'): ?>
                                    <span class="mr-meta-item is-rating" aria-label="คะแนน <?= Html::encode($rating) ?> จาก 5">
                                        <i data-lucide="star" aria-hidden="true"></i>
                                        <?= Html::encode($rating) ?>/5
                                    </span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
                <p class="mr-no-results" id="mr-no-results" role="status" hidden>
                    ไม่พบรายการที่ตรงกับการค้นหา
                </p>
            <?php endif; ?>

        </div>
    </div>

    <div class="mr-actions">
        <a href="<?= Html::encode($newUrl) ?>" class="btn btn-primary">
            <i data-lucide="plus" aria-hidden="true"></i>
            <span>แจ้งซ่อม</span>
        </a>
    </div>
</div>

<?php
$this->registerJs(<<<'JS'
(function () {
    var search = document.getElementById('mr-search');
    var list   = document.getElementById('mr-list');
    var noRes  = document.getElementById('mr-no-results');
    if (!search || !list) return;

    var cards = Array.prototype.slice.call(list.querySelectorAll('.mr-card'));
    var raf = null;

    function filter() {
        var q = (search.value || '').trim().toLowerCase();
        var shown = 0;
        cards.forEach(function (card) {
            var hay = card.getAttribute('data-search') || '';
            var match = q === '' || hay.indexOf(q) !== -1;
            card.hidden = !match;
            if (match) shown++;
        });
        if (noRes) noRes.hidden = (shown > 0);
    }

    search.addEventListener('input', function () {
        if (raf) cancelAnimationFrame(raf);
        raf = requestAnimationFrame(filter);
    });
})();
JS, \yii\web\View::POS_END);
?>

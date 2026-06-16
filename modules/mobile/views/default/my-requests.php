<?php

use app\components\ThaiDateHelper;
use app\modules\mobile\services\MobileBookingStatus;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var string $type */
/** @var \app\modules\booking\models\Meeting[] $meetings */
/** @var \app\modules\leave\models\Leave[] $leaves */
/** @var \app\modules\booking\models\Vehicle[] $vehicles */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */

$this->params['current_page'] = $current_page ?? 'profile';
$this->params['mobileTitle'] = 'คำขอของฉัน';
$this->params['mobileSubtitle'] = 'ติดตามสถานะคำขอทั้งหมด';

$meetings = $meetings ?? [];
$leaves = $leaves ?? [];
$vehicles = $vehicles ?? [];
$fiscalYears = $fiscalYears ?? [];
$filterYear = (int) ($filterYear ?? 0);
$type = (string) ($type ?? 'all');
if (empty($fiscalYears) && $filterYear > 0) {
    $fiscalYears[$filterYear] = 'พ.ศ. ' . $filterYear;
}

$tabs = [
    'all' => ['label' => 'ทั้งหมด', 'icon' => 'list-checks', 'cat' => 'document'],
    'meeting' => ['label' => 'ห้องประชุม', 'icon' => 'calendar', 'cat' => 'meeting'],
    'leave' => ['label' => 'ใบลา', 'icon' => 'calendar-off', 'cat' => 'leave'],
    'vehicle' => ['label' => 'รถราชการ', 'icon' => 'car', 'cat' => 'vehicle'],
    'maintenance' => ['label' => 'แจ้งซ่อม', 'icon' => 'wrench', 'cat' => 'maintenance'],
];
if (!isset($tabs[$type])) {
    $type = 'all';
}

$fiscalLabel = $fiscalYears[$filterYear] ?? ($filterYear > 0 ? 'พ.ศ. ' . $filterYear : 'ปีปัจจุบัน');
$profileUrl = Url::to(['/mobile/default/profile']);
$baseUrl = Url::to(['/mobile/default/my-requests']);

$statusInfo = static function (?string $status): array {
    return MobileBookingStatus::info((string) $status);
};
$pendingCount = static function (array $items) use ($statusInfo): int {
    $count = 0;
    foreach ($items as $item) {
        $info = $statusInfo((string) ($item->status ?? ''));
        if (($info['bucket'] ?? '') === 'pending') {
            $count++;
        }
    }

    return $count;
};
$formatDate = static function (?string $date): string {
    if (!$date) {
        return '-';
    }
    try {
        return ThaiDateHelper::formatThaiDate($date, 'short');
    } catch (\Throwable $e) {
        $ts = strtotime((string) $date);
        return $ts ? date('d/m/Y', $ts) : (string) $date;
    }
};
$dateRange = static function (?string $start, ?string $end) use ($formatDate): string {
    $start = (string) $start;
    $end = (string) $end;
    if ($start === '' && $end === '') {
        return '-';
    }
    if ($end === '' || $start === $end) {
        return $formatDate($start);
    }

    return $formatDate($start) . ' ถึง ' . $formatDate($end);
};
$timeRange = static function (?string $start, ?string $end): string {
    $start = trim(substr((string) $start, 0, 5));
    $end = trim(substr((string) $end, 0, 5));
    if ($start === '' && $end === '') {
        return '';
    }
    if ($end === '') {
        return $start . ' น.';
    }

    return $start . ' - ' . $end . ' น.';
};

$tabCounts = [
    'meeting' => count($meetings),
    'leave' => count($leaves),
    'vehicle' => count($vehicles),
    'maintenance' => 0,
];
$tabCounts['all'] = array_sum($tabCounts);

$pendingCounts = [
    'meeting' => $pendingCount($meetings),
    'leave' => $pendingCount($leaves),
    'vehicle' => $pendingCount($vehicles),
    'maintenance' => 0,
];
$pendingCounts['all'] = array_sum($pendingCounts);

$activeCount = (int) ($tabCounts[$type] ?? 0);
$activePending = (int) ($pendingCounts[$type] ?? 0);
$activeLabel = $tabs[$type]['label'];
$hasAnyRequest = $tabCounts['all'] > 0;
?>

<style>
.mr-root {
    margin: -1rem -1rem 0;
}
.mr-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 6rem);
}
.mr-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    padding: var(--space-md);
}
.mr-back {
    min-height: 2.75rem;
    width: fit-content;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
    font-weight: 800;
    box-shadow: 0 1px 0 var(--ink-line);
}
.mr-back svg {
    width: 1.125rem;
    height: 1.125rem;
}
.mr-filterbar,
.mr-tabs-wrap,
.mr-section,
.mr-empty-card {
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
}
.mr-filterbar {
    position: sticky;
    top: var(--shell-h, 13rem);
    z-index: calc(var(--z-sticky) - 1);
    padding: var(--space-sm);
}
.mr-tabs-wrap {
    padding: var(--space-xs);
}
.mr-tabs {
    display: flex;
    gap: var(--space-xs);
    overflow-x: auto;
    margin: 0;
    padding: 0 0 2px;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
}
.mr-tabs::-webkit-scrollbar {
    display: none;
}
.mr-tab {
    min-height: 2.75rem;
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: var(--space-xs);
    padding: .45rem .55rem .45rem .45rem;
    border-radius: 14px;
    color: var(--ink-3);
    text-decoration: none;
    white-space: nowrap;
    -webkit-tap-highlight-color: transparent;
    transition:
        background 160ms cubic-bezier(0.16, 1, 0.3, 1),
        color 160ms cubic-bezier(0.16, 1, 0.3, 1),
        transform 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.mr-tab:hover {
    color: var(--ink);
}
.mr-tab.is-active {
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
}
.mr-tab-icon {
    width: 2rem;
    height: 2rem;
    border-radius: 11px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    background: var(--surface-2);
    color: currentColor;
}
.mr-tab-icon svg {
    width: 1rem;
    height: 1rem;
}
.mr-tab-text {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2xs);
    font-size: var(--fs-sm);
    font-weight: 800;
}
.mr-tab-count {
    min-width: 1.35rem;
    height: 1.35rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 .35rem;
    background: var(--surface-2);
    color: var(--ink-3);
    font-size: var(--fs-2xs);
    font-weight: 900;
    font-variant-numeric: tabular-nums;
}
.mr-tab.is-active .mr-tab-count {
    background: var(--mobile-primary);
    color: #fff;
}
.mr-section {
    overflow: hidden;
}
.mr-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-sm);
    padding: var(--space-md);
    border-bottom: 1px solid var(--ink-line);
}
.mr-group-title {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    min-width: 0;
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 900;
    line-height: 1.3;
}
.mr-group-title svg {
    width: 1.1rem;
    height: 1.1rem;
    color: var(--mobile-primary);
}
.mr-section-count {
    flex: 0 0 auto;
    color: var(--ink-4);
    font-size: var(--fs-xs);
    font-weight: 800;
}
.mr-list {
    display: flex;
    flex-direction: column;
}
.mr-card {
    display: grid;
    grid-template-columns: auto minmax(0, 1fr) auto;
    gap: var(--space-sm);
    align-items: center;
    padding: var(--space-md);
    border-bottom: 1px solid var(--ink-line);
    color: inherit;
    text-decoration: none;
    -webkit-tap-highlight-color: transparent;
    transition:
        background 160ms cubic-bezier(0.16, 1, 0.3, 1),
        transform 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.mr-card:last-child {
    border-bottom: 0;
}
.mr-card:hover {
    color: inherit;
}
.mr-card:focus-visible,
.mr-tab:focus-visible,
.mr-back:focus-visible {
    outline: 2px solid var(--mobile-primary);
    outline-offset: 2px;
}
.mr-card:active,
.mr-tab:active {
    transform: scale(0.992);
}
.mr-medal {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
}
.mr-medal svg {
    width: 1.25rem;
    height: 1.25rem;
}
.mr-medal.is-meeting { background: var(--cat-meeting-bg); color: var(--cat-meeting-fg); }
.mr-medal.is-leave { background: var(--cat-leave-bg); color: var(--cat-leave-fg); }
.mr-medal.is-vehicle { background: var(--cat-vehicle-bg); color: var(--cat-vehicle-fg); }
.mr-medal.is-maintenance { background: var(--cat-maintenance-bg); color: var(--cat-maintenance-fg); }
.mr-card-main {
    min-width: 0;
}
.mr-card-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-xs);
}
.mr-card-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 900;
    line-height: 1.35;
    text-wrap: pretty;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.mr-pill {
    flex: 0 0 auto;
    border-radius: 999px;
    padding: .3rem .55rem;
    font-size: var(--fs-2xs);
    font-weight: 900;
    line-height: 1.15;
    white-space: nowrap;
}
.mr-pill[data-tone="primary"] { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.mr-pill[data-tone="success"] { background: var(--success-soft); color: var(--success); }
.mr-pill[data-tone="warning"] { background: var(--warning-soft); color: var(--warning); }
.mr-pill[data-tone="danger"] { background: var(--danger-soft); color: var(--danger-strong); }
.mr-pill[data-tone="secondary"] { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }
.mr-card-meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2xs) var(--space-sm);
    margin-top: var(--space-xs);
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 700;
    line-height: 1.35;
}
.mr-card-meta span {
    min-width: 0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.mr-card-meta svg {
    width: .9rem;
    height: .9rem;
    flex: 0 0 auto;
    color: var(--ink-5);
}
.mr-card-code {
    margin-top: 3px;
    color: var(--ink-4);
    font-size: var(--fs-2xs);
    font-weight: 800;
    line-height: 1.3;
    overflow-wrap: anywhere;
}
.mr-chevron {
    width: 1rem;
    height: 1rem;
    color: var(--ink-5);
}
.mr-empty-card {
    padding: var(--space-2xl) var(--space-md);
    text-align: center;
}
.mr-empty-icon {
    width: 4.25rem;
    height: 4.25rem;
    border-radius: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--space-md);
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
}
.mr-empty-icon svg {
    width: 2rem;
    height: 2rem;
}
.mr-empty-title {
    margin: 0 0 var(--space-2xs);
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 900;
}
.mr-empty-text {
    margin: 0 auto;
    max-width: 35ch;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.55;
}
.mr-empty-actions {
    display: grid;
    gap: var(--space-sm);
    margin-top: var(--space-lg);
}
.mr-empty-actions .btn {
    min-height: 2.75rem;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
    font-weight: 800;
}
.mr-empty-actions svg {
    width: 1.125rem;
    height: 1.125rem;
}
@media (hover: hover) {
    .mr-back,
    .mr-tab,
    .mr-card,
    .mr-empty-actions .btn {
        transition:
            transform 160ms cubic-bezier(0.16, 1, 0.3, 1),
            box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1),
            background 160ms cubic-bezier(0.16, 1, 0.3, 1);
    }
    .mr-back:hover,
    .mr-empty-actions .btn:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
    .mr-card:hover {
        background: color-mix(in oklch, var(--surface-2) 48%, var(--surface));
    }
}
@media (max-width: 360px) {
    .mr-card {
        grid-template-columns: auto minmax(0, 1fr);
    }
    .mr-chevron {
        display: none;
    }
    .mr-card-top {
        flex-direction: column;
        align-items: flex-start;
    }
}
@media (prefers-reduced-motion: reduce) {
    .mr-back,
    .mr-filterbar,
    .mr-tabs-wrap,
    .mr-section,
    .mr-empty-card,
    .mr-tab,
    .mr-card,
    .mr-empty-actions .btn {
        animation: none !important;
        transition: none !important;
    }
    .mr-back:hover,
    .mr-card:hover,
    .mr-tab:hover,
    .mr-card:active,
    .mr-tab:active,
    .mr-empty-actions .btn:hover {
        transform: none !important;
    }
}
@media (prefers-reduced-motion: no-preference) {
    .mr-back,
    .mr-filterbar,
    .mr-tabs-wrap,
    .mr-section,
    .mr-empty-card {
        animation: mr-item-in 220ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--mr-i, 0) * 35ms);
    }
    .mr-card {
        animation: mr-card-in 200ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--mr-card-i, 0) * 18ms);
    }
}
@keyframes mr-item-in {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes mr-card-in {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?php
$renderEmpty = static function (array $config): void {
    ?>
    <section class="mr-empty-card" style="--mr-i: <?= (int) ($config['index'] ?? 3) ?>" role="status">
        <span class="mr-empty-icon" aria-hidden="true">
            <i data-lucide="<?= Html::encode($config['icon']) ?>"></i>
        </span>
        <h2 class="mr-empty-title"><?= Html::encode($config['title']) ?></h2>
        <p class="mr-empty-text"><?= Html::encode($config['text']) ?></p>
        <?php if (!empty($config['actionUrl']) && !empty($config['actionLabel'])): ?>
            <div class="mr-empty-actions">
                <a href="<?= Html::encode($config['actionUrl']) ?>" class="btn btn-primary">
                    <i data-lucide="<?= Html::encode($config['actionIcon'] ?? 'plus') ?>" aria-hidden="true"></i>
                    <span><?= Html::encode($config['actionLabel']) ?></span>
                </a>
            </div>
        <?php endif; ?>
    </section>
    <?php
};

$sectionIndex = 3;
$cardIndex = 0;
?>

<div class="mr-root">
    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon' => 'clipboard-list',
        'title' => $this->params['mobileTitle'],
        'subtitle' => $activeLabel . ', ปีงบประมาณ ' . $fiscalLabel,
        'stats' => [
            ['value' => $activeCount, 'label' => 'รายการ', 'tone' => 'primary'],
            ['value' => $activePending, 'label' => 'รออนุมัติ', 'tone' => 'warning'],
        ],
        'statsLabel' => 'สรุปคำขอของฉัน',
    ]) ?>

    <div class="app-scroll has-stats mr-scroll">
        <div class="mr-body">
            <a href="<?= Html::encode($profileUrl) ?>" class="btn btn-outline-secondary mr-back" style="--mr-i: 0">
                <i data-lucide="arrow-left" aria-hidden="true"></i>
                <span>กลับไปโปรไฟล์</span>
            </a>

            <div class="mr-filterbar" style="--mr-i: 1">
                <form method="get" action="<?= Html::encode($baseUrl) ?>" class="mobile-year-filter">
                    <input type="hidden" name="type" value="<?= Html::encode($type) ?>">
                    <label for="req-year-filter" class="mobile-year-filter-label">
                        <i data-lucide="calendar-days" aria-hidden="true"></i>
                        ปีงบประมาณ
                    </label>
                    <select name="year" id="req-year-filter" class="mobile-year-filter-select" onchange="this.form.submit()" aria-label="กรองปีงบประมาณ">
                        <?php foreach ($fiscalYears as $year => $label): ?>
                            <?php $year = (int) $year; ?>
                            <option value="<?= $year ?>" <?= $filterYear === $year ? 'selected' : '' ?>>
                                <?= Html::encode($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>

            <nav class="mr-tabs-wrap" style="--mr-i: 2" aria-label="ประเภทคำขอ">
                <div class="mr-tabs" role="tablist">
                    <?php foreach ($tabs as $key => $tab): ?>
                        <?php $isActive = $type === $key; ?>
                        <a href="<?= Html::encode(Url::to(['/mobile/default/my-requests', 'type' => $key, 'year' => $filterYear])) ?>"
                           class="mr-tab<?= $isActive ? ' is-active' : '' ?>"
                           role="tab"
                           aria-selected="<?= $isActive ? 'true' : 'false' ?>">
                            <span class="mr-tab-icon cat-<?= Html::encode($tab['cat']) ?>" aria-hidden="true">
                                <i data-lucide="<?= Html::encode($tab['icon']) ?>"></i>
                            </span>
                            <span class="mr-tab-text">
                                <?= Html::encode($tab['label']) ?>
                                <span class="mr-tab-count"><?= (int) ($tabCounts[$key] ?? 0) ?></span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </nav>

            <?php if (!$hasAnyRequest && $type === 'all'): ?>
                <?php $renderEmpty([
                    'index' => 3,
                    'icon' => 'inbox',
                    'title' => 'ยังไม่มีคำขอในปีงบประมาณนี้',
                    'text' => 'เมื่อคุณส่งคำขอจองห้องประชุม ขอลา หรือจองรถ รายการจะมารวมอยู่ที่หน้านี้',
                    'actionUrl' => Url::to(['/mobile/default/services']),
                    'actionLabel' => 'ไปที่บริการ',
                    'actionIcon' => 'grid-3x3',
                ]); ?>
            <?php endif; ?>

            <?php if ($type === 'all' || $type === 'meeting'): ?>
                <?php if (!empty($meetings)): ?>
                    <section class="mr-section" style="--mr-i: <?= $sectionIndex++ ?>" aria-labelledby="mr-meeting-title">
                        <header class="mr-section-head">
                            <h2 class="mr-group-title" id="mr-meeting-title">
                                <i data-lucide="calendar" aria-hidden="true"></i>
                                จองห้องประชุม
                            </h2>
                            <span class="mr-section-count"><?= count($meetings) ?> รายการ</span>
                        </header>
                        <div class="mr-list">
                            <?php foreach ($meetings as $m): ?>
                                <?php
                                $info = $statusInfo((string) ($m->status ?? ''));
                                try {
                                    $statusInfoRaw = $m->getStatus($m->status);
                                    $statusLabel = $statusInfoRaw['title'] ?? $info['label'];
                                } catch (\Throwable $e) {
                                    $statusLabel = $info['label'];
                                }
                                $roomTitle = '';
                                try {
                                    $roomTitle = $m->room ? (string) $m->room->title : (string) $m->room_id;
                                } catch (\Throwable $e) {
                                    $roomTitle = (string) $m->room_id;
                                }
                                $title = trim((string) ($m->title ?? '')) ?: (trim((string) ($m->code ?? '')) ?: 'คำขอจองห้องประชุม');
                                $dateText = $dateRange((string) ($m->date_start ?? ''), (string) ($m->date_end ?? ''));
                                $timeText = $timeRange((string) ($m->time_start ?? ''), (string) ($m->time_end ?? ''));
                                $viewUrl = Url::to(['/mobile/default/meeting-view', 'id' => $m->id]);
                                ?>
                                <a href="<?= Html::encode($viewUrl) ?>" class="mr-card" style="--mr-card-i: <?= (int) min($cardIndex++, 10) ?>">
                                    <span class="mr-medal is-meeting" aria-hidden="true">
                                        <i data-lucide="calendar"></i>
                                    </span>
                                    <span class="mr-card-main">
                                        <span class="mr-card-top">
                                            <span class="mr-card-title"><?= Html::encode($title) ?></span>
                                            <span class="mr-pill" data-tone="<?= Html::encode($info['tone']) ?>"><?= Html::encode($statusLabel) ?></span>
                                        </span>
                                        <span class="mr-card-meta">
                                            <span><i data-lucide="door-open" aria-hidden="true"></i><?= Html::encode($roomTitle !== '' ? $roomTitle : '-') ?></span>
                                            <span><i data-lucide="calendar-days" aria-hidden="true"></i><?= Html::encode($dateText) ?></span>
                                            <?php if ($timeText !== ''): ?>
                                                <span><i data-lucide="clock-3" aria-hidden="true"></i><?= Html::encode($timeText) ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="mr-card-code">รหัส <?= Html::encode((string) ($m->code ?? '-')) ?></span>
                                    </span>
                                    <i data-lucide="chevron-right" class="mr-chevron" aria-hidden="true"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php elseif ($type === 'meeting'): ?>
                    <?php $renderEmpty([
                        'index' => 3,
                        'icon' => 'calendar',
                        'title' => 'ยังไม่มีคำขอจองห้องประชุม',
                        'text' => 'รายการจองห้องประชุมของปีงบประมาณนี้จะแสดงที่นี่',
                        'actionUrl' => Url::to(['/mobile/default/booking-meeting']),
                        'actionLabel' => 'จองห้องประชุม',
                        'actionIcon' => 'calendar-plus',
                    ]); ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($type === 'all' || $type === 'leave'): ?>
                <?php if (!empty($leaves)): ?>
                    <section class="mr-section" style="--mr-i: <?= $sectionIndex++ ?>" aria-labelledby="mr-leave-title">
                        <header class="mr-section-head">
                            <h2 class="mr-group-title" id="mr-leave-title">
                                <i data-lucide="calendar-off" aria-hidden="true"></i>
                                ใบลา
                            </h2>
                            <span class="mr-section-count"><?= count($leaves) ?> รายการ</span>
                        </header>
                        <div class="mr-list">
                            <?php foreach ($leaves as $leave): ?>
                                <?php
                                $info = $statusInfo((string) ($leave->status ?? ''));
                                $statusLabel = $info['label'];
                                try {
                                    if ($leave->leaveStatus && !empty($leave->leaveStatus->title)) {
                                        $statusLabel = (string) $leave->leaveStatus->title;
                                    }
                                } catch (\Throwable $e) {}
                                $leaveTitle = 'คำขอลา';
                                try {
                                    $leaveTitle = (string) ($leave->leaveType->title ?? 'คำขอลา');
                                } catch (\Throwable $e) {}
                                $dateText = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
                                $totalDays = (float) ($leave->total_days ?? 0);
                                $viewUrl = Url::to(['/mobile/default/leave-request-view', 'id' => $leave->id]);
                                ?>
                                <a href="<?= Html::encode($viewUrl) ?>" class="mr-card" style="--mr-card-i: <?= (int) min($cardIndex++, 10) ?>">
                                    <span class="mr-medal is-leave" aria-hidden="true">
                                        <i data-lucide="calendar-off"></i>
                                    </span>
                                    <span class="mr-card-main">
                                        <span class="mr-card-top">
                                            <span class="mr-card-title"><?= Html::encode($leaveTitle) ?></span>
                                            <span class="mr-pill" data-tone="<?= Html::encode($info['tone']) ?>"><?= Html::encode($statusLabel) ?></span>
                                        </span>
                                        <span class="mr-card-meta">
                                            <span><i data-lucide="calendar-days" aria-hidden="true"></i><?= Html::encode($dateText !== '' ? $dateText : '-') ?></span>
                                            <?php if ($totalDays > 0): ?>
                                                <span><i data-lucide="clock-3" aria-hidden="true"></i><?= Html::encode(rtrim(rtrim(number_format($totalDays, 1), '0'), '.')) ?> วัน</span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="mr-card-code">รหัสคำขอ <?= Html::encode((string) ($leave->id ?? '-')) ?></span>
                                    </span>
                                    <i data-lucide="chevron-right" class="mr-chevron" aria-hidden="true"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php elseif ($type === 'leave'): ?>
                    <?php $renderEmpty([
                        'index' => 3,
                        'icon' => 'calendar-off',
                        'title' => 'ยังไม่มีคำขอลา',
                        'text' => 'ใบลาที่คุณส่งในปีงบประมาณนี้จะแสดงพร้อมสถานะล่าสุด',
                        'actionUrl' => Url::to(['/mobile/default/leave-request']),
                        'actionLabel' => 'ขอลาออนไลน์',
                        'actionIcon' => 'plus',
                    ]); ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($type === 'all' || $type === 'vehicle'): ?>
                <?php if (!empty($vehicles)): ?>
                    <section class="mr-section" style="--mr-i: <?= $sectionIndex++ ?>" aria-labelledby="mr-vehicle-title">
                        <header class="mr-section-head">
                            <h2 class="mr-group-title" id="mr-vehicle-title">
                                <i data-lucide="car" aria-hidden="true"></i>
                                จองรถราชการ
                            </h2>
                            <span class="mr-section-count"><?= count($vehicles) ?> รายการ</span>
                        </header>
                        <div class="mr-list">
                            <?php foreach ($vehicles as $vehicle): ?>
                                <?php
                                $info = $statusInfo((string) ($vehicle->status ?? ''));
                                $location = '';
                                try {
                                    if ($vehicle->locationOrg && !empty($vehicle->locationOrg->title)) {
                                        $location = (string) $vehicle->locationOrg->title;
                                    }
                                } catch (\Throwable $e) {
                                    $location = '';
                                }
                                if ($location === '') {
                                    $location = trim((string) ($vehicle->location ?? ''));
                                }
                                $reason = trim((string) ($vehicle->reason ?? ''));
                                $title = $location !== '' ? 'ไป ' . $location : ($reason !== '' ? $reason : 'คำขอจองรถ');
                                $dateText = $dateRange((string) ($vehicle->date_start ?? ''), (string) ($vehicle->date_end ?? ''));
                                $timeText = trim(substr((string) ($vehicle->time_start ?? ''), 0, 5));
                                $isUrgent = in_array((string) ($vehicle->urgent ?? ''), ['ด่วน', 'ด่วนที่สุด'], true);
                                ?>
                                <a href="<?= Html::encode(Url::to(['/mobile/default/vehicle-view', 'id' => $vehicle->id])) ?>" class="mr-card" style="--mr-card-i: <?= (int) min($cardIndex++, 10) ?>">
                                    <span class="mr-medal is-vehicle" aria-hidden="true">
                                        <i data-lucide="car"></i>
                                    </span>
                                    <span class="mr-card-main">
                                        <span class="mr-card-top">
                                            <span class="mr-card-title"><?= Html::encode($title) ?></span>
                                            <span class="mr-pill" data-tone="<?= Html::encode($info['tone']) ?>"><?= Html::encode($info['label']) ?></span>
                                        </span>
                                        <span class="mr-card-meta">
                                            <span><i data-lucide="calendar-days" aria-hidden="true"></i><?= Html::encode($dateText) ?></span>
                                            <?php if ($timeText !== ''): ?>
                                                <span><i data-lucide="clock-3" aria-hidden="true"></i><?= Html::encode($timeText) ?> น.</span>
                                            <?php endif; ?>
                                            <?php if ($isUrgent): ?>
                                                <span><i data-lucide="alert-triangle" aria-hidden="true"></i><?= Html::encode((string) $vehicle->urgent) ?></span>
                                            <?php endif; ?>
                                        </span>
                                        <span class="mr-card-code">รหัส <?= Html::encode((string) ($vehicle->code ?? '-')) ?></span>
                                    </span>
                                    <i data-lucide="chevron-right" class="mr-chevron" aria-hidden="true"></i>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php elseif ($type === 'vehicle'): ?>
                    <?php $renderEmpty([
                        'index' => 3,
                        'icon' => 'car',
                        'title' => 'ยังไม่มีคำขอจองรถ',
                        'text' => 'คำขอใช้รถราชการของปีงบประมาณนี้จะแสดงที่นี่',
                        'actionUrl' => Url::to(['/mobile/default/booking-vehicle']),
                        'actionLabel' => 'จองรถราชการ',
                        'actionIcon' => 'car',
                    ]); ?>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ($type === 'maintenance'): ?>
                <?php $renderEmpty([
                    'index' => 3,
                    'icon' => 'wrench',
                    'title' => 'ยังไม่มีคำขอแจ้งซ่อม',
                    'text' => 'ระบบมือถือยังไม่แสดงประวัติแจ้งซ่อม แต่คุณสามารถสร้างคำขอใหม่ได้ทันที',
                    'actionUrl' => Url::to(['/mobile/default/repair-request']),
                    'actionLabel' => 'แจ้งซ่อม',
                    'actionIcon' => 'wrench',
                ]); ?>
            <?php endif; ?>
        </div>
    </div>
</div>

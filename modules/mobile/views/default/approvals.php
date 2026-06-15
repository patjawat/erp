<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use app\modules\mobile\services\MobileApprovalService;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\approveV2\models\Approve[] $approvals */
/** @var MobileApprovalService $service */
/** @var array{pending:int,approved:int,rejected:int} $counts */
/** @var string $bucket */
/** @var string $type */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */
/** @var int $currentYear */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle'] = 'งานอนุมัติ';
$this->params['mobileSubtitle'] = 'รายการที่รอการพิจารณาจากคุณ';

$approvals   = $approvals ?? [];
$counts      = $counts ?? ['pending' => 0, 'approved' => 0, 'rejected' => 0];
$bucket      = $bucket ?? 'pending';
$type        = (string) ($type ?? 'all');
$fiscalYears = $fiscalYears ?? [];
$filterYear  = (int) ($filterYear ?? 0);

$fiscalLabel = $fiscalYears[$filterYear] ?? ($filterYear > 0 ? 'พ.ศ. ' . $filterYear : 'ปีปัจจุบัน');
$backUrl     = Url::to(['/mobile/default/services']);
$baseUrl     = ['/mobile/default/approvals', 'year' => $filterYear, 'type' => $type];

$typeMeta = MobileApprovalService::typeMeta();

/*
 * Timeline batch-load — instead of N queries (one per card), grab every approve row
 * for the (name, from_id) pairs in this page in a single query, then group in PHP.
 * Uses the same Approve model the service uses; no logic change in approve-v2.
 */
$timelineGroups = [];
if (!empty($approvals)) {
    $pairConditions = ['or'];
    $seenPairs = [];
    foreach ($approvals as $a) {
        $key = ((string) $a->name) . '|' . ((string) $a->from_id);
        if (isset($seenPairs[$key])) continue;
        $seenPairs[$key] = true;
        $pairConditions[] = ['and',
            ['name' => (string) $a->name],
            ['from_id' => (string) $a->from_id],
        ];
    }
    if (count($pairConditions) > 1) {
        try {
            $allRows = \app\modules\approveV2\models\Approve::find()
                ->where($pairConditions)
                ->andWhere(['IS', 'deleted_at', null])
                ->orderBy(['level' => SORT_ASC, 'id' => SORT_ASC])
                ->all();
            foreach ($allRows as $row) {
                $k = ((string) $row->name) . '|' . ((string) $row->from_id);
                $timelineGroups[$k][] = $row;
            }
        } catch (\Throwable $e) {
            $timelineGroups = [];
        }
    }
}

// Pre-compute per-card data + count by type so the toolbar's type-chips show live numbers
$cards = [];
$typeCounts = [];
foreach ($approvals as $approve) {
    $name   = (string) $approve->name;
    $meta   = $typeMeta[$name] ?? ['label' => $name, 'icon' => 'file-text', 'cat' => 'document'];
    $parent = $service->loadParent($approve);
    $info   = $service->buildMeta($approve, $parent);
    $status = $service->statusInfo((string) $approve->status);

    $approveData = is_array($approve->data_json ?? null) ? $approve->data_json : [];
    $levelLabel  = (string) ($approveData['label'] ?? $approve->title ?? ('ระดับ ' . (int) $approve->level));

    // Mini-step indicator — read from pre-grouped timeline (single query for the page)
    $timelineKey = $name . '|' . ((string) $approve->from_id);
    $timeline    = $timelineGroups[$timelineKey] ?? [];
    $steps = [];
    $currentIndex = 0;
    foreach ($timeline as $idx => $t) {
        $tStatus = $service->statusInfo((string) $t->status);
        $steps[] = $tStatus['tone'];
        if ((int) $t->id === (int) $approve->id) {
            $currentIndex = $idx;
        }
    }

    // Avatar fallback — leave provides showAvatar(); others fall back to initials
    $avatarUrl = (string) ($info['requesterAvatar'] ?? '');
    $initials  = '';
    if ($avatarUrl === '' && $info['requester'] !== '' && $info['requester'] !== '-') {
        $parts = preg_split('/\s+/', trim($info['requester']));
        foreach ($parts as $p) {
            $initials .= mb_substr($p, 0, 1, 'UTF-8');
            if (mb_strlen($initials, 'UTF-8') >= 2) break;
        }
    }

    $commentPreview = trim((string) ($approve->comment ?? ''));
    if ($commentPreview !== '' && mb_strlen($commentPreview, 'UTF-8') > 110) {
        $commentPreview = mb_substr($commentPreview, 0, 110, 'UTF-8') . '…';
    }

    // search index — concat queryable strings for client-side keyword filter
    $searchHaystack = mb_strtolower(implode(' ', array_filter([
        $meta['label'] ?? '',
        $info['requester'] ?? '',
        $info['title'] ?? '',
        $info['summary'] ?? '',
        $levelLabel,
        (string) $approve->from_id,
    ])), 'UTF-8');

    $isPending = (string) $approve->status === 'Pending';

    $cards[] = [
        'id'           => (int) $approve->id,
        'name'         => $name,
        'meta'         => $meta,
        'info'         => $info,
        'status'       => $status,
        'statusKey'    => $service->bucket((string) $approve->status),
        'levelLabel'   => $levelLabel,
        'level'        => (int) $approve->level,
        'steps'        => $steps,
        'currentIndex' => $currentIndex,
        'avatarUrl'    => $avatarUrl,
        'initials'     => $initials,
        'comment'      => $commentPreview,
        'search'       => $searchHaystack,
        'isPending'    => $isPending,
        'typeLabel'    => $meta['label'] ?? $name,
    ];

    if (!isset($typeCounts[$name])) {
        $typeCounts[$name] = 0;
    }
    $typeCounts[$name]++;
}

// Group cards by type — แต่ละ section ใน UI ใช้ key เดียวกับ MobileApprovalService::typeMeta()
$cardsByType = [];
foreach ($cards as $c) {
    $cardsByType[$c['name']][] = $c;
}

// Section order: ตามลำดับ typeMeta — render เฉพาะ type ที่มีข้อมูล
$sectionOrder  = array_keys($typeMeta);
$typesToRender = array_values(array_filter($sectionOrder, static fn($n) => !empty($cardsByType[$n])));
?>

<style>
/* ---- Layout shell ---- */
.apv-root { margin: -1rem -1rem 0; }
.apv-scroll { padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 6rem); }
.apv-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    padding: var(--space-md);
}
.apv-back {
    min-height: 2.75rem;
    width: fit-content;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: var(--space-2xs);
    font-weight: 700;
    box-shadow: 0 1px 0 var(--ink-line);
    transition: transform 160ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.apv-back svg { width: 1.125rem; height: 1.125rem; }

/* ---- Sticky toolbar ---- */
.apv-toolbar {
    position: sticky;
    top: var(--shell-h, 13rem);
    z-index: calc(var(--z-sticky) - 1);
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
    padding: var(--space-sm);
    border-radius: 14px;
    background: var(--surface);
    box-shadow: 0 1px 0 var(--ink-line), 0 6px 18px color-mix(in oklch, var(--ink) 5%, transparent);
}
/* ---- Hero stats as bucket filter — scoped override of .app-stat
       เพิ่ม indicator bar ใต้ active stat ตามโทนสี เพื่อสื่อ "นี่คือสถานะที่กำลังดู" */
.apv-root .app-stat {
    position: relative;
    overflow: hidden;
}
.apv-root .app-stat::after {
    content: '';
    position: absolute;
    left: 12%;
    right: 12%;
    bottom: 4px;
    height: 3px;
    border-radius: 999px;
    background: transparent;
    transition: background 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.apv-root .app-stat.is-active[data-tone="warning"] { background: var(--warning-soft); }
.apv-root .app-stat.is-active[data-tone="warning"]::after { background: var(--warning); }
.apv-root .app-stat.is-active[data-tone="success"] { background: var(--success-soft); }
.apv-root .app-stat.is-active[data-tone="success"]::after { background: var(--success); }
.apv-root .app-stat.is-active[data-tone="danger"]  { background: var(--danger-soft); }
.apv-root .app-stat.is-active[data-tone="danger"]::after  { background: var(--danger); }
.apv-root .app-stat:focus-visible {
    outline: 2px solid var(--mobile-primary);
    outline-offset: -2px;
}

/* ---- Section (one per type) ---- */
.apv-section {
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}
.apv-section[hidden] { display: none; }
.apv-section-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-sm);
    padding: var(--space-sm) var(--space-md);
    border-bottom: 1px solid var(--ink-line);
    background: linear-gradient(180deg, var(--surface) 0%, var(--surface-2) 100%);
}
.apv-section-title {
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
.apv-section-title svg {
    width: 1.1rem;
    height: 1.1rem;
}
.apv-section-medal {
    width: 2rem;
    height: 2rem;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
}
.apv-section-count {
    flex: 0 0 auto;
    color: var(--ink-4);
    font-size: var(--fs-xs);
    font-weight: 800;
    font-variant-numeric: tabular-nums;
}
.apv-section-body {
    padding: var(--space-sm);
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}

/* ---- Search ---- */
.apv-search {
    position: relative;
    display: flex;
    align-items: center;
    background: var(--surface-3);
    border: 1px solid transparent;
    border-radius: 12px;
    min-height: 2.5rem;
    padding: 0 var(--space-sm);
    transition: border-color 160ms ease-out, background 160ms ease-out;
}
.apv-search:focus-within {
    border-color: var(--mobile-primary);
    background: var(--surface);
    box-shadow: 0 0 0 3px var(--mobile-primary-soft-border);
}
.apv-search > svg { width: 1rem; height: 1rem; color: var(--ink-4); flex-shrink: 0; }
.apv-search-input {
    flex: 1 1 auto;
    border: 0;
    background: transparent;
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 600;
    padding: 0 var(--space-sm);
    min-width: 0;
}
.apv-search-input:focus { outline: none; }
.apv-search-input::placeholder { color: var(--ink-4); font-weight: 500; }
.apv-search-clear {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 999px;
    border: 0;
    background: var(--ink-line);
    color: var(--ink-3);
    display: none;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
}
.apv-search-clear svg { width: 0.85rem; height: 0.85rem; }
.apv-search.has-value .apv-search-clear { display: inline-flex; }

.apv-yearbar { margin: 0; }

/* Filter-active strip — appears only when type/search are filtering; quiet otherwise. */
.apv-summary-row {
    display: none;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-xs);
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 700;
    padding: var(--space-2xs) var(--space-xs);
    margin-top: 2px;
    background: var(--mobile-primary-soft);
    border-radius: 10px;
}
.apv-summary-row.is-filtered { display: flex; }
.apv-summary-row .apv-count {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: var(--mobile-primary);
}
.apv-summary-row .apv-count strong {
    color: var(--mobile-primary);
    font-weight: 800;
    font-size: var(--fs-sm);
}
.apv-summary-row .apv-clear-all {
    background: transparent;
    border: 0;
    color: var(--mobile-primary);
    font-weight: 800;
    font-size: var(--fs-xs);
    padding: 4px var(--space-2xs);
    border-radius: 8px;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    -webkit-tap-highlight-color: transparent;
}

/* ---- Card list ---- */
.apv-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}
.apv-card {
    position: relative;
    display: block;
    padding: var(--space-md);
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    background: var(--surface);
    color: inherit;
    text-decoration: none;
    box-shadow: var(--shadow-sm);
    -webkit-tap-highlight-color: transparent;
    transition: transform 200ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 200ms ease-out, border-color 200ms ease-out, opacity 280ms ease-out;
}
.apv-card[hidden] { display: none; }
.apv-card.is-removing {
    opacity: 0;
    transform: translateX(40%) scale(0.98);
    pointer-events: none;
}
.apv-card:focus-visible { outline: 2px solid var(--mobile-primary); outline-offset: 2px; }
.apv-card:active { transform: scale(0.992); }

.apv-card-link {
    display: block;
    color: inherit;
    text-decoration: none;
    border-radius: 12px;
    -webkit-tap-highlight-color: transparent;
}
.apv-card-link:focus-visible { outline: 2px solid var(--mobile-primary); outline-offset: 2px; }
.apv-card-head {
    display: flex;
    align-items: flex-start;
    gap: var(--space-sm);
}
.apv-card-medallion {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    align-self: flex-start;
}
.apv-card-medallion svg { width: 1.25rem; height: 1.25rem; }
.apv-card-head-body { flex: 1 1 auto; min-width: 0; }
.apv-card-type {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2xs);
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 700;
    line-height: 1.3;
    letter-spacing: 0.01em;
}
.apv-card-type::after {
    content: '·';
    color: var(--ink-5);
    margin: 0 2px;
}
.apv-card-type-meta {
    color: var(--ink-4);
    font-size: var(--fs-xs);
    font-weight: 600;
}
.apv-card-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-xs);
    margin-top: 2px;
}
.apv-card-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 800;
    line-height: 1.35;
    text-wrap: pretty;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.apv-card-pill {
    flex: 0 0 auto;
    border-radius: 999px;
    padding: 3px 10px;
    font-size: var(--fs-2xs);
    font-weight: 800;
    line-height: 1.3;
    white-space: nowrap;
}
.apv-card-pill[data-tone="warning"]   { background: var(--warning-soft);  color: var(--warning); }
.apv-card-pill[data-tone="success"]   { background: var(--success-soft);  color: var(--success); }
.apv-card-pill[data-tone="danger"]    { background: var(--danger-soft);   color: var(--danger-strong); }
.apv-card-pill[data-tone="info"]      { background: color-mix(in oklch, oklch(0.55 0.13 240) 14%, transparent); color: oklch(0.45 0.13 240); }
.apv-card-pill[data-tone="secondary"],
.apv-card-pill[data-tone="primary"]   { background: var(--mobile-primary-soft); color: var(--mobile-primary); }

/* requester row */
.apv-card-requester {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    margin-top: var(--space-xs);
    min-width: 0;
}
.apv-avatar {
    width: 1.5rem;
    height: 1.5rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    overflow: hidden;
    background: var(--surface-3);
    color: var(--ink-3);
    font-size: 0.6875rem;
    font-weight: 800;
    border: 1px solid var(--ink-line);
}
.apv-avatar img { width: 100%; height: 100%; object-fit: cover; display: block; }
.apv-card-requester-name {
    color: var(--ink-2);
    font-size: var(--fs-sm);
    font-weight: 700;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
}
.apv-card-requester-date {
    color: var(--ink-4);
    font-size: var(--fs-xs);
    font-weight: 600;
    line-height: 1.3;
    margin-left: auto;
    flex-shrink: 0;
}

/* mini step indicator */
.apv-steps {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: var(--space-sm);
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 700;
}
.apv-steps-dots { display: inline-flex; align-items: center; gap: 3px; }
.apv-step-dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: var(--ink-line);
    transition: background 200ms ease-out, transform 200ms cubic-bezier(0.16, 1, 0.3, 1);
}
.apv-step-dot[data-tone="success"] { background: var(--success); }
.apv-step-dot[data-tone="danger"]  { background: var(--danger); }
.apv-step-dot[data-tone="info"]    { background: oklch(0.55 0.13 240); }
.apv-step-dot[data-tone="warning"] { background: var(--warning); }
.apv-step-dot.is-current {
    background: var(--mobile-primary);
    transform: scale(1.5);
    box-shadow: 0 0 0 3px var(--mobile-primary-soft);
}
.apv-steps-label { color: var(--ink-3); }
.apv-steps-current { color: var(--ink); font-weight: 800; }

/* Summary preview */
.apv-card-summary {
    margin-top: var(--space-xs);
    color: var(--ink-2);
    font-size: var(--fs-sm);
    line-height: 1.5;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Comment note (last rejection/sendback) */
.apv-card-note {
    margin-top: var(--space-xs);
    padding: var(--space-2xs) var(--space-xs);
    background: var(--surface-3);
    border-radius: 8px;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.5;
    display: flex;
    gap: 6px;
    align-items: flex-start;
}
.apv-card-note svg { width: 0.85rem; height: 0.85rem; color: var(--ink-4); flex-shrink: 0; margin-top: 2px; }

/* footer with actions */
.apv-card-foot {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    margin-top: var(--space-sm);
    padding-top: var(--space-sm);
    border-top: 1px dashed var(--ink-line);
}
.apv-card-detail {
    flex: 1 1 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    min-height: 2.5rem;
    border-radius: 12px;
    border: 1px solid var(--ink-line);
    background: var(--surface);
    color: var(--ink-2);
    font-size: var(--fs-sm);
    font-weight: 700;
    text-decoration: none;
    padding: 0 var(--space-sm);
    transition: background 160ms ease-out, border-color 160ms ease-out, color 160ms ease-out, transform 140ms cubic-bezier(0.16, 1, 0.3, 1);
}
.apv-card-detail:active { transform: scale(0.98); }
.apv-card-detail svg { width: 0.95rem; height: 0.95rem; }
.apv-quick-approve {
    flex: 1 1 auto;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    min-height: 2.5rem;
    border-radius: 12px;
    border: 0;
    background: var(--mobile-primary);
    color: #fff;
    font-size: var(--fs-sm);
    font-weight: 800;
    cursor: pointer;
    padding: 0 var(--space-sm);
    box-shadow: 0 2px 8px color-mix(in oklch, var(--mobile-primary) 25%, transparent);
    -webkit-tap-highlight-color: transparent;
    transition: background 140ms ease-out, transform 140ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 140ms ease-out;
}
.apv-quick-approve:active { transform: translateY(1px) scale(0.98); }
.apv-quick-approve svg { width: 1rem; height: 1rem; }
.apv-quick-approve:disabled { opacity: 0.6; cursor: progress; }

/* ---- Empty state ---- */
.apv-empty {
    padding: var(--space-2xl) var(--space-md);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    text-align: center;
}
.apv-empty-icon {
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
.apv-empty-icon svg { width: 1.75rem; height: 1.75rem; }
.apv-empty-title {
    margin: 0 0 var(--space-2xs);
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 800;
}
.apv-empty-text {
    margin: 0 auto var(--space-md);
    max-width: 32ch;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.55;
}
.apv-empty-cta {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2xs);
    min-height: 2.5rem;
    padding: 0 var(--space-md);
    border-radius: 10px;
    border: 1px solid var(--mobile-primary-soft-border);
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    font-size: var(--fs-sm);
    font-weight: 800;
    text-decoration: none;
    cursor: pointer;
}
.apv-empty[hidden] { display: none; }

/* ---- Hover (desktop) ---- */
@media (hover: hover) {
    .apv-back:hover { transform: translateY(-1px); box-shadow: var(--shadow-sm); }
    .apv-card:hover {
        color: inherit;
        transform: translateY(-2px);
        border-color: var(--mobile-primary-soft-border);
        box-shadow: var(--shadow-md);
    }
    .apv-card-detail:hover { border-color: var(--mobile-primary-soft-border); color: var(--mobile-primary); }
    .apv-quick-approve:hover { background: var(--mobile-primary-dark); box-shadow: 0 4px 14px color-mix(in oklch, var(--mobile-primary) 35%, transparent); }
}

/* ---- Motion ---- */
@media (prefers-reduced-motion: reduce) {
    .apv-back, .apv-card, .apv-section, .apv-toolbar, .apv-step-dot, .apv-card-detail, .apv-quick-approve, .apv-search {
        transition: none !important;
        animation: none !important;
    }
    .apv-back:hover, .apv-card:hover, .apv-card:active { transform: none !important; }
}
@media (prefers-reduced-motion: no-preference) {
    .apv-toolbar { animation: apv-fade-down 240ms cubic-bezier(0.16, 1, 0.3, 1) both; }
    .apv-section {
        animation: apv-fade-up 280ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--apv-i, 0) * 60ms + 120ms);
    }
    .apv-card {
        animation: apv-card-in 260ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--apv-i, 0) * 28ms + 180ms);
    }
    .apv-empty { animation: apv-fade-up 360ms cubic-bezier(0.16, 1, 0.3, 1) both; }
    .apv-empty-icon { animation: apv-pulse 2400ms cubic-bezier(0.4, 0, 0.6, 1) infinite; }
}
@keyframes apv-fade-down { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }
@keyframes apv-fade-up   { from { opacity: 0; transform: translateY(8px); }  to { opacity: 1; transform: translateY(0); } }
@keyframes apv-card-in   { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
@keyframes apv-pulse {
    0%, 100% { box-shadow: inset 0 0 0 6px color-mix(in oklch, var(--mobile-primary) 5%, transparent); }
    50%      { box-shadow: inset 0 0 0 10px color-mix(in oklch, var(--mobile-primary) 7%, transparent); }
}

/* Swal success checkmark */
.apv-checkmark {
    width: 72px; height: 72px; border-radius: 50%;
    display: block; stroke-width: 4; stroke: var(--success);
    stroke-miterlimit: 10;
    box-shadow: inset 0 0 0 var(--success);
    animation: apv-fill .35s ease-in-out .35s forwards, apv-scale .25s ease-in-out .7s both;
    margin: 0 auto var(--space-md);
}
.apv-checkmark-circle {
    stroke-dasharray: 166; stroke-dashoffset: 166; stroke-width: 4;
    stroke-miterlimit: 10; stroke: var(--success); fill: none;
    animation: apv-stroke .55s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}
.apv-checkmark-check {
    transform-origin: 50% 50%; stroke-dasharray: 48; stroke-dashoffset: 48;
    animation: apv-stroke .25s cubic-bezier(0.65, 0, 0.45, 1) .65s forwards;
}
@keyframes apv-stroke { 100% { stroke-dashoffset: 0; } }
@keyframes apv-scale  { 0%,100% { transform: none; } 50% { transform: scale3d(1.08,1.08,1); } }
@keyframes apv-fill   { 100% { box-shadow: inset 0 0 0 40px color-mix(in oklch, var(--success) 12%, transparent); } }
</style>

<div class="apv-root">
    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'       => 'shield-check',
        'title'      => $this->params['mobileTitle'],
        'subtitle'   => 'ปีงบประมาณ ' . $fiscalLabel . ' · รออนุมัติ ' . (int) $counts['pending'] . ' รายการ',
        'stats'      => [
            [
                'value'    => (int) $counts['pending'],
                'label'    => 'รออนุมัติ',
                'tone'     => 'warning',
                'url'      => Url::to(array_merge($baseUrl, ['bucket' => 'pending'])),
                'isActive' => $bucket === 'pending',
            ],
            [
                'value'    => (int) $counts['approved'],
                'label'    => 'อนุมัติแล้ว',
                'tone'     => 'success',
                'url'      => Url::to(array_merge($baseUrl, ['bucket' => 'approved'])),
                'isActive' => $bucket === 'approved',
            ],
            [
                'value'    => (int) $counts['rejected'],
                'label'    => 'ไม่อนุมัติ',
                'tone'     => 'danger',
                'url'      => Url::to(array_merge($baseUrl, ['bucket' => 'rejected'])),
                'isActive' => $bucket === 'rejected',
            ],
        ],
        'statsLabel' => 'สรุปจำนวนงานอนุมัติ',
    ]) ?>

    <div class="app-scroll has-stats apv-scroll">
        <div class="apv-body">
            <a href="<?= Html::encode($backUrl) ?>" class="btn btn-outline-secondary apv-back">
                <i data-lucide="arrow-left" aria-hidden="true"></i>
                <span>กลับไปบริการ</span>
            </a>

            <div class="apv-toolbar" role="region" aria-label="ตัวกรองงานอนุมัติ" id="apv-toolbar">
                <label class="apv-search" id="apv-search">
                    <i data-lucide="search" aria-hidden="true"></i>
                    <input type="search"
                           class="apv-search-input"
                           id="apv-search-input"
                           placeholder="ค้นหาชื่อผู้ขอ ประเภท หรือเรื่อง…"
                           inputmode="search"
                           autocomplete="off"
                           aria-label="ค้นหาคำขออนุมัติ">
                    <button type="button" class="apv-search-clear" id="apv-search-clear" aria-label="ล้างคำค้น">
                        <i data-lucide="x" aria-hidden="true"></i>
                    </button>
                </label>

                <form class="apv-yearbar mobile-year-filter" method="get"
                      action="<?= Html::encode(Url::to(['/mobile/default/approvals'])) ?>">
                    <input type="hidden" name="bucket" value="<?= Html::encode($bucket) ?>">
                    <input type="hidden" name="type"   value="<?= Html::encode($type) ?>">
                    <label for="apv-year-filter" class="mobile-year-filter-label">
                        <i data-lucide="calendar-days" aria-hidden="true"></i>
                        ปีงบประมาณ
                    </label>
                    <select name="year" id="apv-year-filter" class="mobile-year-filter-select"
                            onchange="this.form.submit()" aria-label="กรองปีงบประมาณ">
                        <?php foreach ($fiscalYears as $year => $label): ?>
                            <?php $year = (int) $year; ?>
                            <option value="<?= $year ?>" <?= $filterYear === $year ? 'selected' : '' ?>>
                                <?= Html::encode($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>

                <div class="apv-summary-row" id="apv-summary-row">
                    <span class="apv-count">
                        <i data-lucide="list" aria-hidden="true" style="width:0.95rem;height:0.95rem"></i>
                        <span>แสดง <strong id="apv-visible-count"><?= count($cards) ?></strong> รายการ</span>
                    </span>
                    <button type="button" class="apv-clear-all" id="apv-clear-all">
                        <i data-lucide="rotate-ccw" aria-hidden="true" style="width:0.85rem;height:0.85rem;margin-right:4px"></i>
                        ล้างตัวกรอง
                    </button>
                </div>
            </div>

            <?php
            $emptyTitles = [
                'pending'  => 'ไม่มีงานรออนุมัติ',
                'approved' => 'ยังไม่มีรายการที่อนุมัติแล้ว',
                'rejected' => 'ยังไม่มีรายการที่ไม่อนุมัติ',
            ];
            $bucketLabel = (string) ($emptyTitles[$bucket] ?? 'ยังไม่มีรายการ');
            ?>

            <?php if (empty($cards) || empty($typesToRender)): ?>
                <div class="apv-empty" role="status">
                    <span class="apv-empty-icon" aria-hidden="true">
                        <i data-lucide="<?= $bucket === 'pending' ? 'inbox' : 'check-check' ?>"></i>
                    </span>
                    <p class="apv-empty-title"><?= Html::encode($bucketLabel) ?></p>
                    <p class="apv-empty-text">
                        <?= $bucket === 'pending'
                            ? 'เมื่อมีคำขอเข้ามา รายการจะปรากฏที่หน้านี้ ลองเปลี่ยนสถานะหรือปีงบประมาณดูได้'
                            : 'ลองเปลี่ยนสถานะหรือปีงบประมาณเพื่อดูรายการอื่น' ?>
                    </p>
                </div>
            <?php else: ?>
                <div id="apv-sections">
                    <?php
                    $cardSeq = 0;
                    foreach ($typesToRender as $sectionIdx => $typeKey):
                        $sectionMeta  = $typeMeta[$typeKey] ?? ['label' => $typeKey, 'icon' => 'file-text', 'cat' => 'document'];
                        $sectionCards = $cardsByType[$typeKey] ?? [];
                    ?>
                        <section class="apv-section"
                                 role="region"
                                 aria-labelledby="apv-sec-<?= Html::encode($typeKey) ?>"
                                 data-section-type="<?= Html::encode($typeKey) ?>"
                                 style="--apv-i: <?= (int) min($sectionIdx, 12) ?>">
                            <header class="apv-section-head">
                                <h2 class="apv-section-title" id="apv-sec-<?= Html::encode($typeKey) ?>">
                                    <span class="apv-section-medal cat-<?= Html::encode($sectionMeta['cat']) ?>" aria-hidden="true">
                                        <i data-lucide="<?= Html::encode($sectionMeta['icon']) ?>"></i>
                                    </span>
                                    <?= Html::encode($sectionMeta['label']) ?>
                                </h2>
                                <span class="apv-section-count" data-section-count><?= count($sectionCards) ?> รายการ</span>
                            </header>
                            <div class="apv-section-body apv-list" role="list">
                                <?php foreach ($sectionCards as $c):
                                    $href = Url::to(['/mobile/default/approval-view', 'id' => $c['id']]);
                                    $stepCount = count($c['steps']);
                                    $stepNo    = $stepCount > 0 ? $c['currentIndex'] + 1 : $c['level'];
                                    $stepTotal = max($stepCount, $c['level']);
                                    $cardI = $cardSeq++;
                                ?>
                                    <article class="apv-card"
                                             role="listitem"
                                             data-card-id="<?= (int) $c['id'] ?>"
                                             data-type="<?= Html::encode($c['name']) ?>"
                                             data-status-key="<?= Html::encode($c['statusKey']) ?>"
                                             data-pending="<?= $c['isPending'] ? '1' : '0' ?>"
                                             data-search="<?= Html::encode($c['search']) ?>"
                                             style="--apv-i: <?= (int) min($cardI, 12) ?>">

                                        <a href="<?= Html::encode($href) ?>" class="apv-card-link" aria-label="เปิดรายละเอียดงานอนุมัติของ <?= Html::encode($c['info']['requester']) ?>">
                                            <div class="apv-card-head">
                                                <span class="cat-medallion apv-card-medallion cat-<?= Html::encode($c['meta']['cat']) ?>" aria-hidden="true">
                                                    <i data-lucide="<?= Html::encode($c['meta']['icon']) ?>"></i>
                                                </span>
                                                <div class="apv-card-head-body">
                                                    <div>
                                                        <span class="apv-card-type"><?= Html::encode($c['typeLabel']) ?></span>
                                                        <span class="apv-card-type-meta"><?= Html::encode($c['levelLabel']) ?></span>
                                                    </div>
                                                    <div class="apv-card-title-row">
                                                        <h3 class="apv-card-title">
                                                            <?= Html::encode($c['info']['requester'] !== '' ? $c['info']['requester'] : '-') ?>
                                                        </h3>
                                                        <span class="apv-card-pill" data-tone="<?= Html::encode($c['status']['tone']) ?>">
                                                            <?= Html::encode($c['status']['label']) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="apv-card-requester">
                                                <span class="apv-avatar" aria-hidden="true">
                                                    <?php if ($c['avatarUrl'] !== ''): ?>
                                                        <img src="<?= Html::encode($c['avatarUrl']) ?>" alt="" loading="lazy" decoding="async">
                                                    <?php elseif ($c['initials'] !== ''): ?>
                                                        <?= Html::encode($c['initials']) ?>
                                                    <?php else: ?>
                                                        <i data-lucide="user" style="width:0.85rem;height:0.85rem"></i>
                                                    <?php endif; ?>
                                                </span>
                                                <span class="apv-card-requester-name">
                                                    <?= Html::encode($c['info']['title'] !== '-' && $c['info']['title'] !== '' ? $c['info']['title'] : $c['typeLabel']) ?>
                                                </span>
                                                <?php if (!empty($c['info']['createdAt']) && $c['info']['createdAt'] !== '-'): ?>
                                                    <time class="apv-card-requester-date"><?= Html::encode($c['info']['createdAt']) ?></time>
                                                <?php endif; ?>
                                            </div>

                                            <?php if ($stepTotal > 1): ?>
                                                <div class="apv-steps" aria-label="ลำดับขั้นการอนุมัติ">
                                                    <span class="apv-steps-dots" aria-hidden="true">
                                                        <?php for ($s = 0; $s < $stepTotal; $s++):
                                                            $tone = $c['steps'][$s] ?? 'secondary';
                                                            $isCurrent = $s === $c['currentIndex'];
                                                        ?>
                                                            <span class="apv-step-dot<?= $isCurrent ? ' is-current' : '' ?>"
                                                                  data-tone="<?= Html::encode($tone) ?>"></span>
                                                        <?php endfor; ?>
                                                    </span>
                                                    <span class="apv-steps-label">
                                                        ขั้นที่ <span class="apv-steps-current"><?= (int) $stepNo ?></span> / <?= (int) $stepTotal ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($c['info']['summary'] !== ''): ?>
                                                <p class="apv-card-summary"><?= Html::encode($c['info']['summary']) ?></p>
                                            <?php endif; ?>

                                            <?php if ($c['comment'] !== ''): ?>
                                                <div class="apv-card-note">
                                                    <i data-lucide="message-square" aria-hidden="true"></i>
                                                    <span><?= Html::encode($c['comment']) ?></span>
                                                </div>
                                            <?php endif; ?>
                                        </a>

                                        <div class="apv-card-foot">
                                            <a href="<?= Html::encode($href) ?>" class="apv-card-detail">
                                                <i data-lucide="file-search" aria-hidden="true"></i>
                                                ดูรายละเอียด
                                            </a>
                                            <?php if ($c['isPending']): ?>
                                                <button type="button"
                                                        class="apv-quick-approve"
                                                        data-id="<?= (int) $c['id'] ?>"
                                                        data-type-label="<?= Html::encode($c['typeLabel']) ?>"
                                                        data-requester="<?= Html::encode($c['info']['requester']) ?>"
                                                        aria-label="อนุมัติคำขอของ <?= Html::encode($c['info']['requester']) ?>">
                                                    <i data-lucide="check" aria-hidden="true"></i>
                                                    อนุมัติ
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>

                <div class="apv-empty" id="apv-empty-filtered" hidden role="status">
                    <span class="apv-empty-icon" aria-hidden="true">
                        <i data-lucide="search-x"></i>
                    </span>
                    <p class="apv-empty-title">ไม่พบรายการที่ตรงกับคำค้น</p>
                    <p class="apv-empty-text">ลองล้างคำค้นหรือเปลี่ยนประเภทงาน เพื่อดูรายการอื่น</p>
                    <button type="button" class="apv-empty-cta" id="apv-empty-reset">
                        <i data-lucide="rotate-ccw" aria-hidden="true" style="width:1rem;height:1rem"></i>
                        ล้างคำค้น
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
$approveUrlTemplate = Url::to(['/mobile/default/approval-update', 'id' => '__ID__']);
$reloadUrl          = Url::to(array_merge($baseUrl, ['bucket' => $bucket]));
$totalCount         = count($cards);
$js = <<<JS
(function () {
    'use strict';

    const sectionsRoot   = document.getElementById('apv-sections');
    const sections       = sectionsRoot ? sectionsRoot.querySelectorAll('.apv-section') : [];
    const searchInput    = document.getElementById('apv-search-input');
    const searchWrap     = document.getElementById('apv-search');
    const searchClear    = document.getElementById('apv-search-clear');
    const visibleCount   = document.getElementById('apv-visible-count');
    const summaryRow     = document.getElementById('apv-summary-row');
    const clearAllBtn    = document.getElementById('apv-clear-all');
    const emptyFiltered  = document.getElementById('apv-empty-filtered');
    const emptyReset     = document.getElementById('apv-empty-reset');
    const csrfParam      = $('meta[name="csrf-param"]').attr('content');
    const csrfToken      = $('meta[name="csrf-token"]').attr('content');
    const approveUrlTpl  = '{$approveUrlTemplate}';
    const reloadUrl      = '{$reloadUrl}';

    let activeSearch = '';
    let totalRemaining = {$totalCount};

    /* ------- Client-side search filter (within current bucket+type view) -------
       กรองข้าม section: ถ้า section ทั้ง section ไม่มี card ที่ match → ซ่อน section
    */
    function applyFilter() {
        if (!sectionsRoot) return;
        let shown = 0;
        sections.forEach(function (section) {
            const cards = section.querySelectorAll('.apv-card');
            let sectionShown = 0;
            cards.forEach(function (card) {
                if (card.classList.contains('is-removing')) {
                    card.hidden = true;
                    return;
                }
                const haystack = card.getAttribute('data-search') || '';
                const visible = activeSearch === '' || haystack.indexOf(activeSearch) !== -1;
                card.hidden = !visible;
                if (visible) {
                    sectionShown++;
                    shown++;
                }
            });
            // ซ่อนทั้ง section เมื่อไม่มี card match (เฉพาะตอนกำลัง search)
            section.hidden = activeSearch !== '' && sectionShown === 0;
            const counter = section.querySelector('[data-section-count]');
            if (counter) {
                const total = cards.length;
                counter.textContent = activeSearch !== ''
                    ? sectionShown + ' / ' + total + ' รายการ'
                    : total + ' รายการ';
            }
        });
        if (visibleCount) visibleCount.textContent = shown;
        if (summaryRow) summaryRow.classList.toggle('is-filtered', activeSearch !== '');
        if (emptyFiltered) {
            const noMatch = shown === 0 && totalRemaining > 0 && activeSearch !== '';
            emptyFiltered.hidden = !noMatch;
        }
    }

    /* ------- Search ------- */
    let searchTimer = null;
    function handleSearch(value) {
        activeSearch = (value || '').toLowerCase().trim();
        if (searchWrap) searchWrap.classList.toggle('has-value', activeSearch !== '');
        applyFilter();
    }
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            clearTimeout(searchTimer);
            const val = searchInput.value;
            searchTimer = setTimeout(function () { handleSearch(val); }, 120);
        });
    }
    if (searchClear) {
        searchClear.addEventListener('click', function () {
            if (!searchInput) return;
            searchInput.value = '';
            handleSearch('');
            searchInput.focus();
        });
    }

    function resetSearch() {
        if (searchInput) searchInput.value = '';
        handleSearch('');
    }
    if (clearAllBtn) clearAllBtn.addEventListener('click', resetSearch);
    if (emptyReset)  emptyReset.addEventListener('click', resetSearch);

    /* ------- Quick approve (inline Pass only) ------- */
    function buildApproveUrl(id) {
        return approveUrlTpl.replace('__ID__', String(id));
    }

    function showSuccess(message) {
        return Swal.fire({
            html: '<svg class="apv-checkmark" viewBox="0 0 52 52" aria-hidden="true">'
                + '<circle class="apv-checkmark-circle" cx="26" cy="26" r="25" fill="none"/>'
                + '<path class="apv-checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="4"/>'
                + '</svg>'
                + '<p style="margin:0;color:var(--ink);font-size:1.05rem;font-weight:800">' + (message || 'อนุมัติเรียบร้อย') + '</p>',
            showConfirmButton: false,
            timer: 1100,
            allowOutsideClick: false,
            allowEscapeKey: false,
        });
    }

    function removeCardFromDom(card) {
        card.classList.add('is-removing');
        setTimeout(function () {
            card.hidden = true;
            totalRemaining = Math.max(0, totalRemaining - 1);
            applyFilter();
            if (totalRemaining === 0) {
                // ทั้งหมดถูกอนุมัติแล้ว — กลับไปหน้าใหม่เพื่อโหลด count ใหม่
                window.location.href = reloadUrl;
            }
        }, 320);
    }

    function submitApprove(card, id, typeLabel, requester) {
        const btn = card.querySelector('.apv-quick-approve');
        if (btn) btn.disabled = true;

        const data = { status: 'Pass', comment: '' };
        if (csrfParam) data[csrfParam] = csrfToken;

        Swal.fire({
            title: 'กำลังบันทึก…',
            html: '<div style="color:var(--ink-3);font-size:.9rem">โปรดรอสักครู่</div>',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: function () { Swal.showLoading(); },
        });

        $.ajax({
            type: 'POST',
            url: buildApproveUrl(id),
            data: data,
            dataType: 'json',
        }).done(function (resp) {
            if (resp && resp.status === 'success') {
                showSuccess(resp.message || 'อนุมัติเรียบร้อย').then(function () {
                    removeCardFromDom(card);
                });
                return;
            }
            if (btn) btn.disabled = false;
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: (resp && resp.message) ? resp.message : 'ไม่สามารถบันทึกข้อมูลได้',
                confirmButtonText: 'ตกลง',
            });
        }).fail(function () {
            if (btn) btn.disabled = false;
            Swal.fire({
                icon: 'error',
                title: 'เชื่อมต่อไม่สำเร็จ',
                text: 'ไม่สามารถเชื่อมต่อกับระบบได้ กรุณาลองอีกครั้ง',
                confirmButtonText: 'ตกลง',
            });
        });
    }

    if (sectionsRoot) {
        sectionsRoot.addEventListener('click', function (e) {
            const btn = e.target.closest('.apv-quick-approve');
            if (!btn) return;
            e.preventDefault();
            e.stopPropagation();
            const card = btn.closest('.apv-card');
            if (!card) return;
            const id = parseInt(btn.getAttribute('data-id'), 10);
            const typeLabel = btn.getAttribute('data-type-label') || 'คำขอ';
            const requester = btn.getAttribute('data-requester') || 'ผู้ขอ';
            const subtitle = requester && requester !== '-' ? requester : 'ผู้ขอนี้';

            Swal.fire({
                title: 'ยืนยันการอนุมัติ',
                html: '<div style="color:var(--ink-2);font-size:.95rem;line-height:1.6">'
                    + 'อนุมัติ <strong>' + typeLabel + '</strong>'
                    + '<br>ของ <strong>' + subtitle + '</strong>'
                    + '<div style="margin-top:.5rem;color:var(--ink-3);font-size:.8rem">'
                    + 'หากต้องการเพิ่มหมายเหตุ หรือไม่อนุมัติ/ส่งคืน ให้กด "ดูรายละเอียด"'
                    + '</div>'
                    + '</div>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'อนุมัติ',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true,
                confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--mobile-primary').trim() || '#0d6efd',
            }).then(function (result) {
                if (result.isConfirmed) submitApprove(card, id, typeLabel, requester);
            });
        });
    }

    /* Year filter — show skeleton-ish state on submit */
    const yearSelect = document.getElementById('apv-year-filter');
    if (yearSelect) {
        yearSelect.addEventListener('change', function () {
            document.body.style.cursor = 'progress';
        });
    }

    /* Init lucide icons (re-render after JS-injected dots) */
    if (window.lucide && typeof window.lucide.createIcons === 'function') {
        window.lucide.createIcons();
    }

    /* Init filter pass */
    applyFilter();
})();
JS;
$this->registerJs($js, View::POS_END);
?>

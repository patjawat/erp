<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\modules\mobile\services\MobileApprovalService;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\approveV2\models\Approve[] $approvals */
/** @var MobileApprovalService $service */
/** @var array{all:int,pending:int,approved:int,rejected:int,sendback:int} $counts */
/** @var string $bucket */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */
/** @var int $currentYear */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle'] = 'งานอนุมัติ';
$this->params['mobileSubtitle'] = 'รายการที่รอการพิจารณาจากคุณ';

$approvals   = $approvals ?? [];
$counts      = $counts ?? ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0, 'sendback' => 0];
$bucket      = $bucket ?? 'pending';
$fiscalYears = $fiscalYears ?? [];
$filterYear  = (int) ($filterYear ?? 0);

$fiscalLabel = $fiscalYears[$filterYear] ?? ($filterYear > 0 ? 'พ.ศ. ' . $filterYear : 'ปีปัจจุบัน');
$backUrl     = Url::to(['/mobile/default/services']);
$baseUrl     = ['/mobile/default/approvals', 'year' => $filterYear];

$chips = [
    ['key' => 'all',      'label' => 'ทั้งหมด',     'count' => (int) $counts['all'],      'tone' => 'primary'],
    ['key' => 'pending',  'label' => 'รออนุมัติ',   'count' => (int) $counts['pending'],  'tone' => 'warning'],
    ['key' => 'approved', 'label' => 'อนุมัติแล้ว', 'count' => (int) $counts['approved'], 'tone' => 'success'],
    ['key' => 'rejected', 'label' => 'ไม่อนุมัติ',  'count' => (int) $counts['rejected'], 'tone' => 'danger'],
];
if (((int) $counts['sendback']) > 0) {
    $chips[] = ['key' => 'sendback', 'label' => 'ส่งคืนแก้ไข', 'count' => (int) $counts['sendback'], 'tone' => 'info'];
}

$typeMeta = MobileApprovalService::typeMeta();
?>

<style>
.apv-root {
    margin: -1rem -1rem 0;
}
.apv-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 6rem);
}
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
    transition:
        transform 160ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.apv-back svg {
    width: 1.125rem;
    height: 1.125rem;
}
.apv-toolbar {
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
.apv-chips {
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
.apv-chips::-webkit-scrollbar { display: none; }
.apv-chip {
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
.apv-chip-count {
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
.apv-chip.is-active {
    background: var(--mobile-primary);
    border-color: var(--mobile-primary);
    color: #fff;
}
.apv-chip.is-active .apv-chip-count {
    background: color-mix(in oklch, white 22%, var(--mobile-primary));
    color: #fff;
}
.apv-chip[data-tone="warning"].is-active {
    background: var(--warning);
    border-color: var(--warning);
}
.apv-chip[data-tone="warning"].is-active .apv-chip-count {
    background: color-mix(in oklch, white 22%, var(--warning));
}
.apv-chip[data-tone="success"].is-active {
    background: var(--success);
    border-color: var(--success);
}
.apv-chip[data-tone="success"].is-active .apv-chip-count {
    background: color-mix(in oklch, white 22%, var(--success));
}
.apv-chip[data-tone="danger"].is-active {
    background: var(--danger);
    border-color: var(--danger);
}
.apv-chip[data-tone="danger"].is-active .apv-chip-count {
    background: color-mix(in oklch, white 22%, var(--danger));
}
.apv-chip[data-tone="info"].is-active {
    background: oklch(0.55 0.13 240);
    border-color: oklch(0.55 0.13 240);
}
.apv-chip[data-tone="info"].is-active .apv-chip-count {
    background: color-mix(in oklch, white 22%, oklch(0.55 0.13 240));
}
.apv-chip:active {
    transform: scale(0.96);
}
.apv-yearbar {
    margin: 0;
}
.apv-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}
.apv-card {
    position: relative;
    display: flex;
    align-items: stretch;
    gap: var(--space-sm);
    padding: var(--space-md);
    border: 1px solid var(--ink-line);
    border-radius: 16px;
    background: var(--surface);
    color: inherit;
    text-decoration: none;
    box-shadow: var(--shadow-sm);
    -webkit-tap-highlight-color: transparent;
    transition:
        transform 180ms cubic-bezier(0.16, 1, 0.3, 1),
        box-shadow 180ms cubic-bezier(0.16, 1, 0.3, 1),
        border-color 180ms cubic-bezier(0.16, 1, 0.3, 1);
}
.apv-card:focus-visible {
    outline: 2px solid var(--mobile-primary);
    outline-offset: 2px;
}
.apv-card:active {
    transform: scale(0.992);
}
.apv-card-medallion {
    width: 3rem;
    height: 3rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    align-self: flex-start;
}
.apv-card-medallion svg {
    width: 1.375rem;
    height: 1.375rem;
}
.apv-card-body {
    flex: 1 1 auto;
    min-width: 0;
    display: flex;
    flex-direction: column;
    gap: var(--space-2xs);
}
.apv-card-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-xs);
}
.apv-card-type {
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 700;
    letter-spacing: 0.02em;
    text-transform: none;
}
.apv-pill {
    flex: 0 0 auto;
    border-radius: 999px;
    padding: 3px 10px;
    font-size: var(--fs-2xs);
    font-weight: 800;
    line-height: 1.3;
    white-space: nowrap;
}
.apv-pill[data-tone="warning"]  { background: var(--warning-soft);  color: var(--warning); }
.apv-pill[data-tone="success"]  { background: var(--success-soft);  color: var(--success); }
.apv-pill[data-tone="danger"]   { background: var(--danger-soft);   color: var(--danger-strong); }
.apv-pill[data-tone="info"]     { background: color-mix(in oklch, oklch(0.55 0.13 240) 14%, transparent); color: oklch(0.45 0.13 240); }
.apv-pill[data-tone="secondary"], .apv-pill[data-tone="primary"] {
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
}
.apv-card-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 800;
    line-height: 1.32;
    text-wrap: pretty;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.apv-card-sub {
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.4;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 4px 8px;
}
.apv-card-sub .dot {
    width: 3px;
    height: 3px;
    border-radius: 50%;
    background: var(--ink-4);
    display: inline-block;
}
.apv-card-summary {
    margin-top: 2px;
    color: var(--ink-2);
    font-size: var(--fs-sm);
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.apv-card-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-sm);
    margin-top: var(--space-2xs);
    padding-top: var(--space-sm);
    border-top: 1px dashed var(--ink-line);
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 600;
}
.apv-card-foot .apv-card-cta {
    color: var(--mobile-primary);
    font-weight: 800;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.apv-card-foot svg {
    width: 0.95rem;
    height: 0.95rem;
    flex-shrink: 0;
}
.apv-level {
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.apv-level svg {
    width: 0.875rem;
    height: 0.875rem;
}
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
    margin: 0 auto;
    max-width: 32ch;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.55;
}
@media (hover: hover) {
    .apv-back:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
    .apv-card:hover {
        color: inherit;
        transform: translateY(-2px);
        border-color: var(--mobile-primary-soft-border);
        box-shadow: var(--shadow-md);
    }
    .apv-chip:hover {
        color: var(--mobile-primary);
        border-color: var(--mobile-primary-soft-border);
    }
    .apv-chip.is-active:hover {
        color: #fff;
    }
}
@media (prefers-reduced-motion: reduce) {
    .apv-back, .apv-card, .apv-chip, .apv-toolbar {
        transition: none !important;
        animation: none !important;
    }
    .apv-back:hover, .apv-card:hover, .apv-card:active, .apv-chip:active { transform: none !important; }
}
@media (prefers-reduced-motion: no-preference) {
    .apv-toolbar {
        animation: apv-fade-down 240ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .apv-card {
        animation: apv-card-in 280ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--apv-i, 0) * 28ms);
    }
    .apv-empty {
        animation: apv-fade-up 360ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .apv-empty-icon {
        animation: apv-pulse 2400ms cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
}
@keyframes apv-fade-down {
    from { opacity: 0; transform: translateY(-6px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes apv-fade-up {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes apv-card-in {
    from { opacity: 0; transform: translateY(10px); }
    to   { opacity: 1; transform: translateY(0); }
}
@keyframes apv-pulse {
    0%, 100% { box-shadow: inset 0 0 0 6px color-mix(in oklch, var(--mobile-primary) 5%, transparent); }
    50%      { box-shadow: inset 0 0 0 10px color-mix(in oklch, var(--mobile-primary) 7%, transparent); }
}
</style>

<div class="apv-root">
    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'       => 'shield-check',
        'title'      => $this->params['mobileTitle'],
        'subtitle'   => 'ปีงบประมาณ ' . $fiscalLabel . ' · รออนุมัติ ' . (int) $counts['pending'] . ' รายการ',
        'stats'      => [
            ['value' => (int) $counts['pending'],  'label' => 'รออนุมัติ',  'tone' => 'warning'],
            ['value' => (int) $counts['approved'], 'label' => 'อนุมัติแล้ว', 'tone' => 'success'],
            ['value' => (int) $counts['rejected'], 'label' => 'ไม่อนุมัติ',  'tone' => 'danger'],
            ['value' => (int) $counts['all'],      'label' => 'รวมทั้งหมด',  'tone' => 'primary'],
        ],
        'statsLabel' => 'สรุปจำนวนงานอนุมัติ',
    ]) ?>

    <div class="app-scroll has-stats apv-scroll">
        <div class="apv-body">
            <a href="<?= Html::encode($backUrl) ?>" class="btn btn-outline-secondary apv-back">
                <i data-lucide="arrow-left" aria-hidden="true"></i>
                <span>กลับไปบริการ</span>
            </a>

            <div class="apv-toolbar" role="region" aria-label="ตัวกรองงานอนุมัติ">
                <nav class="apv-chips" aria-label="กรองตามสถานะ">
                    <?php foreach ($chips as $chip):
                        $isActive = $bucket === $chip['key'];
                        $chipUrl  = array_merge($baseUrl, ['bucket' => $chip['key']]);
                    ?>
                        <a href="<?= Html::encode(Url::to($chipUrl)) ?>"
                           class="apv-chip<?= $isActive ? ' is-active' : '' ?>"
                           data-tone="<?= Html::encode($chip['tone']) ?>"
                           aria-current="<?= $isActive ? 'page' : 'false' ?>">
                            <span><?= Html::encode($chip['label']) ?></span>
                            <span class="apv-chip-count"><?= (int) $chip['count'] ?></span>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <form class="apv-yearbar mobile-year-filter" method="get"
                      action="<?= Html::encode(Url::to(['/mobile/default/approvals'])) ?>">
                    <input type="hidden" name="bucket" value="<?= Html::encode($bucket) ?>">
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
            </div>

            <?php if (empty($approvals)): ?>
                <div class="apv-empty" role="status">
                    <span class="apv-empty-icon" aria-hidden="true">
                        <i data-lucide="<?= $bucket === 'pending' ? 'inbox' : 'check-check' ?>"></i>
                    </span>
                    <p class="apv-empty-title">
                        <?php
                        $emptyTitles = [
                            'all'      => 'ยังไม่มีรายการอนุมัติ',
                            'pending'  => 'ไม่มีงานรออนุมัติ',
                            'approved' => 'ยังไม่มีรายการที่อนุมัติแล้ว',
                            'rejected' => 'ยังไม่มีรายการที่ไม่อนุมัติ',
                            'sendback' => 'ยังไม่มีรายการที่ส่งคืน',
                        ];
                        echo Html::encode($emptyTitles[$bucket] ?? 'ยังไม่มีรายการ');
                        ?>
                    </p>
                    <p class="apv-empty-text">
                        <?= $bucket === 'pending'
                            ? 'เมื่อมีคำขอเข้ามา รายการจะปรากฏที่หน้านี้ คุณสามารถลองสลับสถานะหรือปีงบประมาณดูได้'
                            : 'ลองสลับสถานะหรือปีงบประมาณเพื่อดูรายการอื่น'
                        ?>
                    </p>
                </div>
            <?php else: ?>
                <div class="apv-list">
                    <?php foreach ($approvals as $i => $approve):
                        $name       = (string) $approve->name;
                        $meta       = $typeMeta[$name] ?? ['label' => $name, 'icon' => 'file-text', 'cat' => 'document'];
                        $parent     = $service->loadParent($approve);
                        $info       = $service->buildMeta($approve, $parent);
                        $status     = $service->statusInfo((string) $approve->status);
                        $approveData = is_array($approve->data_json ?? null) ? $approve->data_json : [];
                        $levelLabel  = (string) ($approveData['label'] ?? $approve->title ?? ('ระดับ ' . (int) $approve->level));
                        $detailUrl   = Url::to(['/mobile/default/approval-view', 'id' => (int) $approve->id]);
                        $requester   = $info['requester'] !== '' ? $info['requester'] : '-';
                        $summary     = $info['summary'] !== '' ? $info['summary'] : '';
                    ?>
                        <a href="<?= Html::encode($detailUrl) ?>" class="apv-card" style="--apv-i: <?= (int) min($i, 12) ?>">
                            <span class="cat-medallion apv-card-medallion cat-<?= Html::encode($meta['cat']) ?>" aria-hidden="true">
                                <i data-lucide="<?= Html::encode($meta['icon']) ?>"></i>
                            </span>
                            <span class="apv-card-body">
                                <span class="apv-card-row">
                                    <span class="apv-card-type"><?= Html::encode($meta['label']) ?></span>
                                    <span class="apv-pill" data-tone="<?= Html::encode($status['tone']) ?>">
                                        <?= Html::encode($status['label']) ?>
                                    </span>
                                </span>
                                <span class="apv-card-title"><?= Html::encode($requester) ?></span>
                                <span class="apv-card-sub">
                                    <span class="apv-level">
                                        <i data-lucide="git-branch" aria-hidden="true"></i>
                                        <?= Html::encode($levelLabel) ?>
                                    </span>
                                    <?php if (!empty($info['createdAt']) && $info['createdAt'] !== '-'): ?>
                                        <span class="dot" aria-hidden="true"></span>
                                        <span><?= Html::encode($info['createdAt']) ?></span>
                                    <?php endif; ?>
                                </span>
                                <?php if ($summary !== ''): ?>
                                    <span class="apv-card-summary"><?= Html::encode($summary) ?></span>
                                <?php endif; ?>
                                <span class="apv-card-foot">
                                    <span><?= Html::encode($info['title'] !== '-' ? $info['title'] : '') ?></span>
                                    <span class="apv-card-cta">
                                        เปิดรายละเอียด
                                        <i data-lucide="chevron-right" aria-hidden="true"></i>
                                    </span>
                                </span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php

use app\components\ThaiDateHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var string $filter */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */
/** @var int $currentYear */

$this->params['current_page'] = $current_page ?? 'news';

$filter = trim((string) ($filter ?? 'all'));
if (!in_array($filter, ['all', 'unread', 'read'], true)) {
    $filter = 'all';
}

$officialUnreadCount = (int) ($officialUnreadCount ?? 0);
$officialTotalCount  = (int) ($officialTotalCount ?? 0);
$officialReadCount   = max(0, $officialTotalCount - $officialUnreadCount);
$fiscalYears = $fiscalYears ?? [];
$filterYear = (int) ($filterYear ?? 0);
$currentYear = (int) ($currentYear ?? 0);
if (empty($fiscalYears) && $filterYear > 0) {
    $fiscalYears[$filterYear] = 'พ.ศ. ' . $filterYear;
}
$fiscalLabel = $fiscalYears[$filterYear] ?? ($filterYear > 0 ? 'พ.ศ. ' . $filterYear : '');
$newsUrl = static function (string $nextFilter) use ($filterYear): string {
    return Url::to(['/mobile/default/news', 'filter' => $nextFilter, 'year' => $filterYear]);
};

$this->params['mobileTitle']    = 'หนังสือราชการ';
if ($filter === 'unread') {
    $this->params['mobileSubtitle'] = $officialUnreadCount > 0 ? 'ยังไม่อ่าน ' . $officialUnreadCount . ' ฉบับ' : 'อ่านครบแล้ว';
} elseif ($filter === 'read') {
    $this->params['mobileSubtitle'] = 'อ่านแล้ว ' . $officialReadCount . ' ฉบับ';
} else {
    $this->params['mobileSubtitle'] = 'รายการหนังสือทั้งหมด';
}

$documents = $dataProvider->getModels();

/** Bucket a document's received date relative to today (for visual grouping). */
$dateBucket = static function (?string $date): string {
    if (!$date) return 'older';
    $ts = strtotime($date);
    if (!$ts) return 'older';
    $todayTs   = strtotime('today');
    $weekTs    = strtotime('-7 days');
    $monthTs   = strtotime('-30 days');
    if ($ts >= $todayTs)  return 'today';
    if ($ts >= $weekTs)   return 'week';
    if ($ts >= $monthTs)  return 'month';
    return 'older';
};

$bucketLabels = [
    'today' => 'วันนี้',
    'week'  => 'สัปดาห์นี้',
    'month' => 'เดือนนี้',
    'older' => 'ก่อนหน้านี้',
];
?>

<style>
/* Full-bleed escape so the shared .app-shell can reach screen edges. */
.news-root {
    margin-left: -1rem; margin-right: -1rem;
    margin-top: -1rem;
    display: flex; flex-direction: column;
}

/* ── Below-hero stack ──────────────────────────────────────────────── */
.news-body { padding: var(--space-lg) var(--space-md) 0; display: flex; flex-direction: column; gap: var(--space-md); }
.news-filterbar {
    position: sticky;
    top: var(--shell-h, 13rem);
    z-index: calc(var(--z-sticky) - 1);
    background: var(--surface);
    border-radius: 14px;
    padding: var(--space-sm);
    box-shadow: 0 1px 0 var(--ink-line), 0 2px 8px color-mix(in oklch, var(--ink) 4%, transparent);
}

/* ── Date bucket header ───────────────────────────────────────────── */
.news-bucket {
    font-size: var(--fs-sm); font-weight: 700; color: var(--ink-3);
    letter-spacing: -0.005em;
    margin: var(--space-md) 0 var(--space-xs);
    display: flex; align-items: center; gap: var(--space-xs);
}
.news-bucket:first-child { margin-top: 0; }
.news-bucket::after { content: ''; flex: 1; height: 1px; background: var(--ink-line); }
.news-bucket-count { color: var(--ink-5); font-weight: 500; }

/* ── Document card (rm-card pattern, no side stripe) ──────────────── */
.news-card {
    display: block;
    background: #fff;
    border-radius: 14px;
    box-shadow: var(--shadow-sm);
    padding: var(--space-md);
    margin-bottom: var(--space-xs);
    text-decoration: none; color: inherit;
    transition: box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1), transform 100ms cubic-bezier(0.16, 1, 0.3, 1);
}
.news-card:hover { color: inherit; box-shadow: 0 4px 14px rgba(15, 23, 42, 0.08); }
.news-card:active { transform: scale(0.995); }
.news-card:focus-visible { outline: 2px solid var(--mobile-primary); outline-offset: 2px; }
.news-card.is-unread { background: linear-gradient(135deg, rgba(13, 110, 253, 0.04) 0%, #fff 50%); }

.news-card-head { display: flex; align-items: center; gap: var(--space-sm); }
.news-medal {
    width: 2.75rem; height: 2.75rem; border-radius: 13px;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.news-medal svg { width: 1.25rem; height: 1.25rem; }
.news-medal.is-unread { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.news-medal.is-read   { background: rgba(34, 197, 94, 0.12); color: var(--success); }

.news-card-body { flex-grow: 1; min-width: 0; }
.news-card-title {
    font-size: var(--fs-md); font-weight: 600; color: var(--ink);
    margin: 0; line-height: 1.3;
    overflow: hidden; text-overflow: ellipsis;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
.news-card-meta {
    font-size: var(--fs-xs); color: var(--ink-3);
    margin: 4px 0 0; line-height: 1.4;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.news-card-meta strong { color: var(--ink-2); font-weight: 600; }
.news-card-pill {
    flex-shrink: 0;
    font-size: var(--fs-2xs); font-weight: 600;
    padding: 4px 10px; border-radius: 999px;
}
.news-card-pill.is-unread { background: var(--warning-soft); color: var(--warning); }
.news-card-pill.is-read   { background: var(--success-soft); color: var(--success); }

.news-card-tags {
    display: flex; flex-wrap: wrap; gap: var(--space-2xs);
    margin-top: var(--space-sm);
    padding-top: var(--space-sm);
    border-top: 1px solid var(--ink-line);
}
.news-tag {
    font-size: 0.6875rem; font-weight: 500;
    padding: 3px 8px; border-radius: 6px;
    background: rgba(100, 116, 139, 0.1);
    color: var(--ink-3);
}
.news-tag.is-urgent { background: var(--danger-soft); color: var(--danger-strong); font-weight: 600; }
.news-tag.is-secret { background: rgba(15, 23, 42, 0.85); color: #fff; font-weight: 600; }
.news-tag strong { font-weight: 700; color: var(--ink); }

.news-card-desc {
    font-size: var(--fs-xs); color: var(--ink-3);
    margin: var(--space-xs) 0 0; line-height: 1.45;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

/* ── Empty state ──────────────────────────────────────────────────── */
.news-empty {
    text-align: center;
    padding: var(--space-2xl) var(--space-md);
    background: #fff;
    border-radius: 16px;
    box-shadow: var(--shadow-sm);
}
.news-empty-icon {
    width: 4rem; height: 4rem; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    margin-bottom: var(--space-md);
}
.news-empty-title { font-size: var(--fs-lg); font-weight: 700; color: var(--ink); margin: 0 0 var(--space-2xs); letter-spacing: -0.01em; }
.news-empty-text { font-size: var(--fs-sm); color: var(--ink-4); margin: 0 0 var(--space-md); line-height: 1.5; max-width: 28ch; margin-left: auto; margin-right: auto; }

/* ── Pagination ────────────────────────────────────────────────── */
.news-pager .pagination .page-link {
    border-radius: 10px !important;
    border: 1px solid rgba(15, 23, 42, 0.08);
    color: var(--ink-3);
    font-weight: 500;
    padding: 0.4rem 0.75rem;
    margin: 0 2px; min-width: 2.25rem; text-align: center;
}
.news-pager .pagination .page-item.active .page-link {
    background: var(--mobile-primary); border-color: var(--mobile-primary); color: #fff;
}
.news-pager .pagination .page-item.disabled .page-link { opacity: 0.5; }

/* ── Motion ────────────────────────────────────────────────────── */
@keyframes news-enter { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }

.news-body > * { animation: news-enter 400ms cubic-bezier(0.16, 1, 0.3, 1) backwards; }
.news-body > *:nth-child(1) { animation-delay: 140ms; }
.news-body > *:nth-child(2) { animation-delay: 200ms; }
.news-body > *:nth-child(3) { animation-delay: 260ms; }

@media (prefers-reduced-motion: reduce) {
    .news-body > * { animation: none !important; }
    .news-card { transition: none !important; }
}
</style>

<div class="news-root">

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'     => 'mail',
        'title'    => 'หนังสือราชการ',
        'subtitle' => ($officialUnreadCount > 0
            ? 'มีหนังสือใหม่ ' . $officialUnreadCount . ' ฉบับรอเปิดอ่าน'
            : 'ไม่มีหนังสือใหม่ในขณะนี้') . ($fiscalLabel !== '' ? ' ปีงบประมาณ ' . $fiscalLabel : ''),
        'stats' => [
            ['url' => $newsUrl('all'),
             'value' => $officialTotalCount,  'label' => 'ทั้งหมด',   'tone' => 'primary',
             'isActive' => $filter === 'all'],
            ['url' => $newsUrl('unread'),
             'value' => $officialUnreadCount, 'label' => 'ยังไม่อ่าน', 'tone' => 'warning',
             'isActive' => $filter === 'unread'],
            ['url' => $newsUrl('read'),
             'value' => $officialReadCount,   'label' => 'อ่านแล้ว',   'tone' => 'success',
             'isActive' => $filter === 'read'],
        ],
    ]) ?>

    <!-- ── Scroll area (content under the fixed shell) ─────────── -->
    <div class="app-scroll has-stats">
    <!-- ── Below-hero stack ────────────────────────────────────── -->
    <div class="news-body">
        <div class="news-filterbar">
            <form method="get" action="<?= Html::encode(Url::to(['/mobile/default/news'])) ?>" class="mobile-year-filter">
                <input type="hidden" name="filter" value="<?= Html::encode($filter) ?>">
                <label for="news-year-filter" class="mobile-year-filter-label">
                    <i data-lucide="calendar-days" aria-hidden="true"></i>
                    ปีงบประมาณ
                </label>
                <select name="year" id="news-year-filter" class="mobile-year-filter-select" onchange="this.form.submit()" aria-label="กรองปีงบประมาณ">
                    <?php foreach ($fiscalYears as $year => $label): ?>
                        <?php $year = (int) $year; ?>
                        <option value="<?= $year ?>" <?= $filterYear === $year ? 'selected' : '' ?>>
                            <?= Html::encode($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if (empty($documents)): ?>
            <?php
            $emptyConfig = [
                'unread' => ['icon' => 'mail-check', 'title' => 'อ่านครบแล้ว',         'text' => 'ไม่มีหนังสือใหม่ที่ยังไม่ได้เปิดอ่านในขณะนี้'],
                'read'   => ['icon' => 'book-open',  'title' => 'ยังไม่มีรายการอ่านแล้ว', 'text' => 'เมื่อคุณเปิดอ่านหนังสือ รายการจะถูกย้ายมาที่นี่'],
                'all'    => ['icon' => 'inbox',      'title' => 'ยังไม่มีหนังสือราชการ',   'text' => 'เมื่อมีหนังสือส่งมาถึงคุณ รายการจะปรากฏที่นี่'],
            ];
            $cfg = $emptyConfig[$filter] ?? $emptyConfig['all'];
            ?>
            <div class="news-empty" role="status">
                <span class="news-empty-icon" aria-hidden="true">
                    <i data-lucide="<?= Html::encode($cfg['icon']) ?>" class="mi-lg"></i>
                </span>
                <p class="news-empty-title"><?= Html::encode($cfg['title']) ?></p>
                <p class="news-empty-text"><?= Html::encode($cfg['text']) ?></p>
                <?php if ($filter !== 'all' && $officialTotalCount > 0): ?>
                    <a href="<?= Html::encode($newsUrl('all')) ?>"
                       class="btn btn-outline-primary rounded-3 fw-medium">ดูหนังสือทั้งหมด</a>
                <?php else: ?>
                    <a href="<?= Html::encode(Url::to(['/mobile/default/index'])) ?>"
                       class="btn btn-outline-secondary rounded-3 fw-medium">กลับหน้าหลัก</a>
                <?php endif; ?>
            </div>
        <?php else: ?>

            <?php
            // Group documents by date bucket
            $bucketed = [];
            foreach ($documents as $doc) {
                $bucketed[$dateBucket((string) $doc->doc_transactions_date)][] = $doc;
            }
            $bucketOrder = ['today', 'week', 'month', 'older'];
            ?>

            <div>
                <?php foreach ($bucketOrder as $bucketKey): ?>
                    <?php if (empty($bucketed[$bucketKey])) continue; ?>
                    <?php $bucketCount = count($bucketed[$bucketKey]); ?>

                    <div class="news-bucket">
                        <i data-lucide="<?= $bucketKey === 'older' ? 'history' : 'calendar' ?>" class="mi-sm"></i>
                        <?= Html::encode($bucketLabels[$bucketKey]) ?>
                        <span class="news-bucket-count">(<?= $bucketCount ?>)</span>
                    </div>

                    <?php foreach ($bucketed[$bucketKey] as $item): ?>
                        <?php
                        $dataJson = is_array($item->data_json)
                            ? $item->data_json
                            : (is_string($item->data_json) && trim($item->data_json) !== ''
                                ? (json_decode($item->data_json, true) ?: [])
                                : []);
                        if (!is_array($dataJson)) $dataJson = [];

                        $detailId    = (int) ($item->detail_id ?? 0);
                        $viewUrl     = Url::to(['/mobile/default/news-view', 'id' => $detailId]);
                        $isRead      = !empty($item->doc_read);
                        $statusKey   = $isRead ? 'read' : 'unread';
                        $statusLabel = $isRead ? 'อ่านแล้ว' : 'ยังไม่อ่าน';
                        $statusIcon  = $isRead ? 'mail-check' : 'mail';
                        $receivedDate = !empty($item->doc_transactions_date)
                            ? ThaiDateHelper::formatThaiDate($item->doc_transactions_date, 'short')
                            : '—';
                        $orgTitle    = $item->documentOrg->title ?? '-';
                        $description = trim((string) ($dataJson['des'] ?? ''));
                        $speed       = (string) ($item->doc_speed ?? '');
                        $secret      = (string) ($item->secret ?? '');
                        $regis       = (string) ($item->doc_regis_number ?? '');
                        $docNo       = (string) ($item->doc_number ?? '');
                        ?>
                        <a href="<?= Html::encode($viewUrl) ?>"
                           class="news-card <?= $isRead ? 'is-read' : 'is-unread' ?>"
                           aria-label="เปิดหนังสือ <?= Html::encode($item->topic ?: $docNo) ?>">
                            <div class="news-card-head">
                                <span class="news-medal is-<?= Html::encode($statusKey) ?>" aria-hidden="true">
                                    <i data-lucide="<?= Html::encode($statusIcon) ?>"></i>
                                </span>
                                <div class="news-card-body">
                                    <h3 class="news-card-title"><?= Html::encode($item->topic ?: '-') ?></h3>
                                    <p class="news-card-meta">
                                        <strong><?= Html::encode($orgTitle) ?></strong> · <?= Html::encode($receivedDate) ?>
                                    </p>
                                </div>
                                <span class="news-card-pill is-<?= Html::encode($statusKey) ?>">
                                    <?= Html::encode($statusLabel) ?>
                                </span>
                            </div>

                            <?php if ($description !== ''): ?>
                                <p class="news-card-desc"><?= Html::encode($description) ?></p>
                            <?php endif; ?>

                            <div class="news-card-tags">
                                <?php if ($regis !== ''): ?>
                                    <span class="news-tag">เลขรับ <strong><?= Html::encode($regis) ?></strong></span>
                                <?php endif; ?>
                                <?php if ($docNo !== ''): ?>
                                    <span class="news-tag">เลขที่ <strong><?= Html::encode($docNo) ?></strong></span>
                                <?php endif; ?>
                                <?php if ($speed === 'ด่วนที่สุด'): ?>
                                    <span class="news-tag is-urgent">ด่วนที่สุด</span>
                                <?php endif; ?>
                                <?php if ($secret === 'ลับที่สุด'): ?>
                                    <span class="news-tag is-secret">ลับที่สุด</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>

            <?php if ($dataProvider->pagination !== false && $dataProvider->pagination->pageCount > 1): ?>
                <div class="news-pager d-flex justify-content-center pt-2 pb-3">
                    <?= \yii\bootstrap5\LinkPager::widget([
                        'pagination'           => $dataProvider->pagination,
                        'options'              => ['class' => 'pagination pagination-sm mb-0'],
                        'linkContainerOptions' => ['class' => 'page-item'],
                        'linkOptions'          => ['class' => 'page-link'],
                        'disabledPageCssClass' => 'disabled',
                        'activePageCssClass'   => 'active',
                    ]) ?>
                </div>
            <?php endif; ?>

        <?php endif; ?>

    </div><!-- /.news-body -->
    </div><!-- /.app-scroll -->
</div>

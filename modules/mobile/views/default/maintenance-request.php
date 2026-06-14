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
/** @var array $bucketCounts */

$this->params['current_page']   = $current_page ?? 'services';
$this->params['mobileTitle']    = 'ประวัติแจ้งซ่อม';
$this->params['mobileSubtitle'] = 'งานแจ้งซ่อมของฉัน';

$myRequests    = $myRequests ?? [];
$fiscalYears   = $fiscalYears ?? [];
$filterYear    = (int) ($filterYear ?? 0);
$bucketCounts  = $bucketCounts ?? ['all' => 0, 'pending' => 0, 'done' => 0, 'cancelled' => 0];

$baseUrl  = Url::to(['/mobile/default/maintenance-request']);
// ปุ่ม "แจ้งซ่อมใหม่" ไปที่ wizard เดียวกันกับเข้าจากหน้า asset (จากปลั๊กอินใหม่ repair-request)
$newUrl   = Url::to(['/mobile/default/repair-request']);

// Hero stats overlay (เปิดเมื่อมี ≥3 รายการ)
$showHeroStats = (int) $bucketCounts['all'] >= 3;
ob_start(); ?>
<?php if ($showHeroStats): ?>
<nav class="app-stats" data-cols="3" aria-label="สรุปงานแจ้งซ่อม">
    <a href="<?= Html::encode(Url::to(['/mobile/default/maintenance-request', 'year' => $filterYear])) ?>"
       class="app-stat" data-tone="primary">
        <span class="app-stat-num"><?= (int) $bucketCounts['all'] ?></span>
        <span class="app-stat-lbl">ทั้งหมด</span>
    </a>
    <span class="app-stat" data-tone="warning">
        <span class="app-stat-num"><?= (int) $bucketCounts['pending'] ?></span>
        <span class="app-stat-lbl">กำลังดำเนินการ</span>
    </span>
    <span class="app-stat" data-tone="success">
        <span class="app-stat-num"><?= (int) $bucketCounts['done'] ?></span>
        <span class="app-stat-lbl">ซ่อมเสร็จแล้ว</span>
    </span>
</nav>
<?php endif; ?>
<?php $overlayHtml = ob_get_clean();
$overlayHtml = trim($overlayHtml) !== '' ? $overlayHtml : null;
?>

<style>
/* ───── Maintenance list (bm-) ───── */
.bm-root { margin: -1rem -1rem 0; display: flex; flex-direction: column; }
.bm-scroll { padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 9.5rem); }

.bm-list-toolbar {
    position: sticky; top: var(--shell-h, 13rem);
    z-index: calc(var(--z-sticky) - 1);
    background: var(--surface);
    padding: var(--space-md);
    box-shadow: 0 1px 0 var(--ink-line), 0 2px 8px color-mix(in oklch, var(--ink) 4%, transparent);
}
.bm-filter-form { display: flex; align-items: center; gap: var(--space-sm); margin: 0; }
.bm-filter-label { font-size: var(--fs-sm); font-weight: 600; color: var(--ink-3); flex-shrink: 0; }
.bm-filter-select {
    flex-grow: 1; min-height: 2.75rem;
    border-radius: 12px; border: 1px solid var(--ink-line);
    background: var(--surface);
    padding: 0 var(--space-md);
    font-size: var(--fs-md); font-weight: 600;
    color: var(--ink);
}

.bm-list { padding: var(--space-md); display: flex; flex-direction: column; gap: var(--space-sm); }
.bm-list-card {
    display: flex; flex-direction: column; gap: var(--space-xs);
    padding: var(--space-md);
    border-radius: 14px; border: 1px solid var(--ink-line);
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    color: inherit; text-decoration: none;
    transition: transform 160ms cubic-bezier(0.22, 1, 0.36, 1),
                box-shadow 200ms cubic-bezier(0.22, 1, 0.36, 1);
}
.bm-list-card:active { transform: scale(0.985); box-shadow: var(--shadow-md); }
.bm-list-card-head { display: flex; align-items: center; justify-content: space-between; gap: var(--space-sm); }
.bm-list-title {
    font-size: var(--fs-md); font-weight: 700; color: var(--ink); line-height: 1.3;
    word-break: break-word; flex: 1; min-width: 0;
}
.bm-list-pill {
    flex-shrink: 0; border-radius: 999px; padding: 4px 10px;
    font-size: var(--fs-2xs); font-weight: 700; line-height: 1.4;
}
.bm-list-pill[data-tone="warning"]   { background: var(--warning-soft);  color: var(--warning); }
.bm-list-pill[data-tone="info"]      { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.bm-list-pill[data-tone="success"]   { background: var(--success-soft);  color: var(--success); }
.bm-list-pill[data-tone="danger"]    { background: var(--danger-soft);   color: var(--danger-strong); }
.bm-list-pill[data-tone="secondary"] { background: color-mix(in oklch, var(--ink-4) 15%, transparent); color: var(--ink-3); }
.bm-list-meta { display: flex; flex-wrap: wrap; gap: var(--space-2xs) var(--space-md); font-size: var(--fs-xs); color: var(--ink-3); }
.bm-list-meta-item { display: inline-flex; align-items: center; gap: 4px; }
.bm-list-meta-item svg { width: 12px; height: 12px; color: var(--ink-4); }
.bm-list-rating {
    display: inline-flex; align-items: center; gap: 2px;
    color: #b45309; font-size: var(--fs-xs); font-weight: 700;
}

.bm-list-empty {
    margin: var(--space-lg) var(--space-md) 0;
    padding: var(--space-2xl) var(--space-md); text-align: center;
    border-radius: 16px; background: var(--surface);
    box-shadow: var(--shadow-sm);
}
.bm-list-empty-icon {
    display: inline-flex; align-items: center; justify-content: center;
    width: 4rem; height: 4rem; border-radius: 50%;
    background: var(--mobile-primary-soft); color: var(--mobile-primary);
    margin-bottom: var(--space-md);
}
.bm-list-empty-title { font-size: var(--fs-lg); font-weight: 700; color: var(--ink); margin: 0 0 var(--space-2xs); }
.bm-list-empty-text { font-size: var(--fs-sm); color: var(--ink-3); margin: 0 auto; max-width: 30ch; line-height: 1.55; }

/* Sticky bottom action — "แจ้งซ่อมใหม่" → wizard */
.bm-actions {
    position: fixed; left: 0; right: 0;
    bottom: calc(env(safe-area-inset-bottom, 0px) + 4.75rem);
    background: var(--surface); padding: var(--space-md);
    box-shadow: 0 -4px 16px color-mix(in oklch, var(--ink) 8%, transparent);
    border-top: 1px solid var(--ink-line);
    z-index: 1031;
    display: grid; gap: var(--space-xs);
}
.bm-actions .btn {
    min-height: 3rem; border-radius: 12px;
    font-size: var(--fs-md); font-weight: 600;
    display: inline-flex; align-items: center; justify-content: center;
    gap: var(--space-2xs);
    transition: opacity 200ms cubic-bezier(0.22, 1, 0.36, 1),
                background-color 180ms ease-out, transform 120ms ease-out;
}
.bm-actions .btn:active { transform: scale(0.985); }

@media (prefers-reduced-motion: reduce) {
    .bm-list-card, .bm-actions .btn { transition: none !important; }
}
</style>

<div class="bm-root">

    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon'        => 'wrench',
        'title'       => $this->params['mobileTitle'],
        'subtitle'    => $this->params['mobileSubtitle'],
        'overlayHtml' => $overlayHtml,
    ]) ?>

    <div class="app-scroll <?= $showHeroStats ? 'has-overlay' : '' ?> bm-scroll">

        <?php if (Yii::$app->session->hasFlash('success')): ?>
            <div class="alert alert-success rounded-3 mx-3 mt-3 mb-0" role="status"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
        <?php endif; ?>
        <?php if (Yii::$app->session->hasFlash('error')): ?>
            <div class="alert alert-danger rounded-3 mx-3 mt-3 mb-0" role="alert"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
        <?php endif; ?>

        <div class="bm-list-toolbar">
            <form method="get" action="<?= Html::encode($baseUrl) ?>" class="bm-filter-form">
                <label for="bm-year-filter" class="bm-filter-label">ปีงบประมาณ</label>
                <select name="year" id="bm-year-filter" class="bm-filter-select"
                        onchange="this.form.submit()" aria-label="กรองตามปีงบประมาณ">
                    <?php foreach ($fiscalYears as $year => $label): ?>
                        <option value="<?= (int) $year ?>" <?= (int) $year === $filterYear ? 'selected' : '' ?>>
                            <?= Html::encode($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if (empty($myRequests)): ?>
            <div class="bm-list-empty">
                <span class="bm-list-empty-icon" aria-hidden="true">
                    <i data-lucide="wrench" class="mi-xl"></i>
                </span>
                <p class="bm-list-empty-title">ยังไม่มีงานแจ้งซ่อมในปีนี้</p>
                <p class="bm-list-empty-text">เมื่อมีอุปกรณ์เสีย แตะปุ่ม "แจ้งซ่อมใหม่" ด้านล่างเพื่อเปิดฟอร์ม</p>
            </div>
        <?php else: ?>
            <div class="bm-list">
                <?php foreach ($myRequests as $row):
                    $info       = $service->statusInfo((string) $row->status);
                    $infoTone   = (string) $info['tone'];
                    $infoLabel  = (string) $info['label'];
                    $title      = (string) ($row->title ?? 'งานซ่อม');
                    $repairNo   = (string) ($row->repair_number ?? '');
                    $createdAt  = '';
                    if (!empty($row->created_at)) {
                        try { $createdAt = ThaiDateHelper::formatThaiDate((string) $row->created_at, 'short'); }
                        catch (\Throwable $e) { $createdAt = (string) $row->created_at; }
                    }
                    $rating = trim((string) ($row->rating ?? ''));
                    $location = (string) (is_array($row->data_json ?? null) ? ($row->data_json['location'] ?? '') : '');
                ?>
                    <a class="bm-list-card"
                       href="<?= Html::encode(Url::to(['/mobile/default/maintenance-view', 'id' => $row->id])) ?>">
                        <header class="bm-list-card-head">
                            <span class="bm-list-title"><?= Html::encode($title) ?></span>
                            <span class="bm-list-pill" data-tone="<?= Html::encode($infoTone) ?>">
                                <?= Html::encode($infoLabel) ?>
                            </span>
                        </header>
                        <div class="bm-list-meta">
                            <?php if ($repairNo): ?>
                                <span class="bm-list-meta-item">
                                    <i data-lucide="hash" aria-hidden="true"></i>
                                    <?= Html::encode($repairNo) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($createdAt): ?>
                                <span class="bm-list-meta-item">
                                    <i data-lucide="calendar" aria-hidden="true"></i>
                                    <?= Html::encode($createdAt) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($location): ?>
                                <span class="bm-list-meta-item">
                                    <i data-lucide="map-pin" aria-hidden="true"></i>
                                    <?= Html::encode($location) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($rating !== '' && $rating !== '0'): ?>
                                <span class="bm-list-rating" aria-label="คะแนน <?= Html::encode($rating) ?> จาก 5">
                                    <i data-lucide="star" aria-hidden="true" style="width:12px;height:12px;fill:currentColor;"></i>
                                    <?= Html::encode($rating) ?>/5
                                </span>
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>

    <div class="bm-actions">
        <a href="<?= Html::encode($newUrl) ?>" class="btn btn-primary">
            <i data-lucide="plus" aria-hidden="true"></i>
            <span>แจ้งซ่อมใหม่</span>
        </a>
    </div>
</div>

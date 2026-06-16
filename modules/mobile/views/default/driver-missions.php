<?php

use app\components\ThaiDateHelper;
use app\modules\booking\models\VehicleDetail;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var VehicleDetail[] $missions */
/** @var int $openCount */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */
/** @var int $currentYear */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle'] = 'ภารกิจขับรถ';

$missions = $missions ?? [];
$openCount = (int) ($openCount ?? 0);
$fiscalYears = $fiscalYears ?? [];
$filterYear = (int) ($filterYear ?? 0);
$currentYear = (int) ($currentYear ?? 0);
if (empty($fiscalYears) && $filterYear > 0) {
    $fiscalYears[$filterYear] = 'พ.ศ. ' . $filterYear;
}
$this->params['mobileSubtitle'] = 'งานที่ได้รับมอบหมาย' . ($filterYear > 0 ? ' ปีงบประมาณ ' . ($fiscalYears[$filterYear] ?? ('พ.ศ. ' . $filterYear)) : '');

$statusMeta = static function (?string $status): array {
    $status = (string) $status;
    if ($status === 'Success') return ['bucket' => 'success', 'icon' => 'check-circle-2', 'label' => 'เสร็จสิ้น'];
    if ($status === 'Cancel') return ['bucket' => 'cancelled', 'icon' => 'x-circle', 'label' => 'ยกเลิก'];
    if (in_array($status, ['Pass', 'Approve'], true)) return ['bucket' => 'active', 'icon' => 'route', 'label' => 'ได้รับมอบหมาย'];
    return ['bucket' => 'pending', 'icon' => 'clock', 'label' => 'รอดำเนินการ'];
};

$formatDate = static function (?string $date): string {
    if (!$date) return '-';
    try {
        return ThaiDateHelper::formatThaiDate($date, 'short');
    } catch (\Throwable $e) {
        $ts = strtotime((string) $date);
        return $ts ? date('d/m/Y', $ts) : (string) $date;
    }
};
?>

<style>
.dm-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + var(--space-xl));
}
.dm-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}
.dm-filterbar {
    position: sticky;
    top: var(--shell-h, 13rem);
    z-index: calc(var(--z-sticky) - 1);
    background: var(--surface);
    border-radius: 14px;
    padding: var(--space-sm);
    margin-bottom: var(--space-md);
    box-shadow: 0 1px 0 var(--ink-line), 0 2px 8px color-mix(in oklch, var(--ink) 4%, transparent);
}
.dm-card {
    display: flex;
    gap: var(--space-sm);
    padding: var(--space-md);
    border-radius: 16px;
    border: 1px solid var(--ink-line);
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
.dm-card:active {
    transform: scale(0.992);
}
.dm-card:focus-visible {
    outline: 2px solid var(--mobile-primary);
    outline-offset: 2px;
}
.dm-medal {
    width: 2.75rem;
    height: 2.75rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.dm-medal svg {
    width: 1.25rem;
    height: 1.25rem;
}
.dm-medal.is-active,
.dm-medal.is-pending { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.dm-medal.is-success { background: var(--success-soft); color: var(--success); }
.dm-medal.is-cancelled { background: var(--danger-soft); color: var(--danger-strong); }
.dm-body {
    flex: 1 1 auto;
    min-width: 0;
}
.dm-title-row {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-xs);
}
.dm-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 700;
    line-height: 1.3;
    text-wrap: pretty;
}
.dm-pill {
    flex-shrink: 0;
    border-radius: 999px;
    padding: 4px 9px;
    font-size: var(--fs-2xs);
    font-weight: 700;
    line-height: 1.2;
}
.dm-pill.is-active,
.dm-pill.is-pending { background: var(--mobile-primary-soft); color: var(--mobile-primary); }
.dm-pill.is-success { background: var(--success-soft); color: var(--success); }
.dm-pill.is-cancelled { background: var(--danger-soft); color: var(--danger-strong); }
.dm-meta {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-2xs) var(--space-sm);
    margin-top: var(--space-xs);
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.35;
}
.dm-meta span {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    min-width: 0;
}
.dm-meta svg {
    width: 0.875rem;
    height: 0.875rem;
    color: var(--ink-4);
    flex-shrink: 0;
}
.dm-desc {
    margin: var(--space-xs) 0 0;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.45;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.dm-empty {
    text-align: center;
    padding: var(--space-2xl) var(--space-md);
    color: var(--ink-4);
}
.dm-empty-icon {
    width: 4rem;
    height: 4rem;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    margin-bottom: var(--space-md);
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
}
.dm-empty-title {
    margin: 0 0 var(--space-2xs);
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 700;
}
.dm-empty-text {
    margin: 0 auto;
    max-width: 30ch;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.5;
}
@media (hover: hover) {
    .dm-card:hover {
        color: inherit;
        transform: translateY(-1px);
        border-color: var(--mobile-primary-soft-border);
        box-shadow: var(--shadow-md);
    }
}
@media (prefers-reduced-motion: reduce) {
    .dm-filterbar,
    .dm-card {
        transition: none !important;
        animation: none !important;
    }
    .dm-card:hover,
    .dm-card:active {
        transform: none !important;
    }
}
@media (prefers-reduced-motion: no-preference) {
    .dm-filterbar {
        animation: dm-filter-in 220ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .dm-card {
        animation: dm-card-in 220ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--dm-i, 0) * 18ms);
    }
}
@keyframes dm-filter-in {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes dm-card-in {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
    'icon' => 'route',
    'title' => $this->params['mobileTitle'],
    'subtitle' => $this->params['mobileSubtitle'],
    'stats' => [
        ['value' => $openCount, 'label' => 'ต้องติดตาม', 'tone' => 'primary'],
        ['value' => count($missions), 'label' => 'ทั้งหมด', 'tone' => 'success'],
    ],
]) ?>

<div class="app-scroll has-stats dm-scroll">
    <?php if (Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success border-0 shadow-sm rounded-3 small mb-3"><?= Html::encode(Yii::$app->session->getFlash('success')) ?></div>
    <?php endif; ?>
    <?php if (Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm rounded-3 small mb-3"><?= Html::encode(Yii::$app->session->getFlash('error')) ?></div>
    <?php endif; ?>

    <div class="dm-filterbar">
        <form method="get" action="<?= Html::encode(Url::to(['/mobile/default/driver-missions'])) ?>" class="mobile-year-filter">
            <label for="dm-year-filter" class="mobile-year-filter-label">
                <i data-lucide="calendar-days" aria-hidden="true"></i>
                ปีงบประมาณ
            </label>
            <select name="year" id="dm-year-filter" class="mobile-year-filter-select" onchange="this.form.submit()" aria-label="กรองปีงบประมาณ">
                <?php foreach ($fiscalYears as $year => $label): ?>
                    <?php $year = (int) $year; ?>
                    <option value="<?= $year ?>" <?= $filterYear === $year ? 'selected' : '' ?>>
                        <?= Html::encode($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (empty($missions)): ?>
        <div class="dm-empty">
            <span class="dm-empty-icon" aria-hidden="true"><i data-lucide="route" class="mi-lg"></i></span>
            <p class="dm-empty-title">ยังไม่มีภารกิจขับรถ</p>
            <p class="dm-empty-text">ยังไม่มีภารกิจในปีงบประมาณนี้ เมื่อมีการจัดสรรงาน รายการจะแสดงที่นี่</p>
        </div>
    <?php else: ?>
        <div class="dm-stack">
            <?php foreach ($missions as $i => $mission):
                $vehicle = $mission->vehicle;
                $meta = $statusMeta($mission->status);
                $location = (string) ($vehicle?->locationOrg?->title ?? $vehicle?->location ?? '-');
                $reason = (string) ($vehicle?->reason ?? '');
                $plate = trim((string) ($mission->license_plate ?? ''));
                $time = trim(substr((string) $mission->time_start, 0, 5) . ' - ' . substr((string) $mission->time_end, 0, 5), ' -');
            ?>
                <a href="<?= Html::encode(Url::to(['/mobile/default/driver-mission', 'id' => $mission->id])) ?>" class="dm-card" style="--dm-i: <?= (int) min($i, 10) ?>">
                    <span class="dm-medal is-<?= Html::encode($meta['bucket']) ?>" aria-hidden="true">
                        <i data-lucide="<?= Html::encode($meta['icon']) ?>"></i>
                    </span>
                    <span class="dm-body">
                        <span class="dm-title-row">
                            <span class="dm-title"><?= Html::encode($location !== '' ? $location : 'ภารกิจขับรถ') ?></span>
                            <span class="dm-pill is-<?= Html::encode($meta['bucket']) ?>"><?= Html::encode($meta['label']) ?></span>
                        </span>
                        <span class="dm-meta">
                            <span><i data-lucide="calendar" aria-hidden="true"></i><?= Html::encode($formatDate($mission->date_start)) ?></span>
                            <?php if ($time !== ''): ?>
                                <span><i data-lucide="clock" aria-hidden="true"></i><?= Html::encode($time) ?> น.</span>
                            <?php endif; ?>
                            <?php if ($plate !== ''): ?>
                                <span><i data-lucide="car" aria-hidden="true"></i><?= Html::encode($plate) ?></span>
                            <?php endif; ?>
                        </span>
                        <?php if ($reason !== ''): ?>
                            <span class="dm-desc"><?= Html::encode($reason) ?></span>
                        <?php endif; ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

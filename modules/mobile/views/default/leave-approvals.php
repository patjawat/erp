<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var app\modules\approveV2\models\Approve[] $approvals */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */
/** @var int $currentYear */

$this->params['current_page'] = $current_page ?? 'services';
$this->params['mobileTitle'] = 'อนุมัติใบลา';
$this->params['mobileSubtitle'] = 'ตรวจสอบรายการที่รอการพิจารณาจากคุณ';

$approvals = $approvals ?? [];
$fiscalYears = $fiscalYears ?? [];
$filterYear = (int) ($filterYear ?? 0);
$currentYear = (int) ($currentYear ?? 0);
if (empty($fiscalYears) && $filterYear > 0) {
    $fiscalYears[$filterYear] = 'พ.ศ. ' . $filterYear;
}

$approvalCount = count($approvals);
$fiscalLabel = $fiscalYears[$filterYear] ?? ($filterYear > 0 ? 'พ.ศ. ' . $filterYear : 'ปีปัจจุบัน');
$backUrl = Url::to(['/mobile/default/services']);
?>

<style>
.la-root {
    margin: -1rem -1rem 0;
}
.la-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 6rem);
}
.la-body {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    padding: var(--space-md);
}
.la-back {
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
.la-back svg {
    width: 1.125rem;
    height: 1.125rem;
}
.la-filterbar {
    position: sticky;
    top: var(--shell-h, 13rem);
    z-index: calc(var(--z-sticky) - 1);
    padding: var(--space-sm);
    border-radius: 14px;
    background: var(--surface);
    box-shadow: 0 1px 0 var(--ink-line), 0 2px 8px color-mix(in oklch, var(--ink) 4%, transparent);
}
.la-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}
.la-card {
    display: flex;
    align-items: flex-start;
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
.la-card:focus-visible {
    outline: 2px solid var(--mobile-primary);
    outline-offset: 2px;
}
.la-card:active {
    transform: scale(0.992);
}
.la-avatar {
    position: relative;
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 auto;
    overflow: hidden;
    border: 1px solid var(--ink-line);
    background: var(--surface-2);
    box-shadow: 0 4px 14px color-mix(in oklch, var(--ink) 7%, transparent);
}
.la-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.la-avatar-badge {
    position: absolute;
    right: -2px;
    bottom: -2px;
    width: 1.35rem;
    height: 1.35rem;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 2px solid var(--surface);
    background: var(--warning);
    color: #fff;
}
.la-avatar-badge svg {
    width: .75rem;
    height: .75rem;
}
.la-content {
    flex: 1 1 auto;
    min-width: 0;
}
.la-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: var(--space-xs);
}
.la-title {
    margin: 0;
    color: var(--ink);
    font-size: var(--fs-md);
    font-weight: 700;
    line-height: 1.35;
    text-wrap: pretty;
}
.la-sub {
    margin: 2px 0 0;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    line-height: 1.35;
}
.la-pill {
    flex: 0 0 auto;
    border-radius: 999px;
    padding: 4px 10px;
    background: var(--warning-soft);
    color: var(--warning);
    font-size: var(--fs-2xs);
    font-weight: 700;
    line-height: 1.2;
}
.la-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: var(--space-xs);
    margin-top: var(--space-sm);
}
.la-field {
    min-width: 0;
}
.la-label {
    color: var(--ink-4);
    font-size: var(--fs-2xs);
    font-weight: 700;
    line-height: 1.3;
}
.la-value {
    margin-top: 2px;
    color: var(--ink-2);
    font-size: var(--fs-xs);
    font-weight: 700;
    line-height: 1.35;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
.la-field.is-wide {
    grid-column: 1 / -1;
}
.la-field.is-wide .la-value {
    white-space: normal;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.la-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-sm);
    margin-top: var(--space-sm);
    padding-top: var(--space-sm);
    border-top: 1px solid var(--ink-line);
    color: var(--mobile-primary);
    font-size: var(--fs-sm);
    font-weight: 700;
}
.la-foot svg {
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
}
.la-empty {
    padding: var(--space-2xl) var(--space-md);
    border-radius: 16px;
    background: var(--surface);
    box-shadow: var(--shadow-sm);
    text-align: center;
}
.la-empty-icon {
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
.la-empty-title {
    margin: 0 0 var(--space-2xs);
    color: var(--ink);
    font-size: var(--fs-lg);
    font-weight: 700;
}
.la-empty-text {
    margin: 0 auto;
    max-width: 30ch;
    color: var(--ink-3);
    font-size: var(--fs-sm);
    line-height: 1.5;
}
@media (hover: hover) {
    .la-back:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
    .la-card:hover {
        color: inherit;
        transform: translateY(-1px);
        border-color: var(--mobile-primary-soft-border);
        box-shadow: var(--shadow-md);
    }
}
@media (prefers-reduced-motion: reduce) {
    .la-back,
    .la-filterbar,
    .la-card {
        transition: none !important;
        animation: none !important;
    }
    .la-back:hover,
    .la-card:hover,
    .la-card:active {
        transform: none !important;
    }
}
@media (prefers-reduced-motion: no-preference) {
    .la-filterbar {
        animation: la-filter-in 220ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .la-card {
        animation: la-card-in 220ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--la-i, 0) * 18ms);
    }
}
@keyframes la-filter-in {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes la-card-in {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<div class="la-root">
    <?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
        'icon' => 'clipboard-check',
        'title' => $this->params['mobileTitle'],
        'subtitle' => 'ปีงบประมาณ ' . $fiscalLabel,
        'stats' => [
            ['value' => $approvalCount, 'label' => 'รออนุมัติ', 'tone' => 'warning'],
            ['value' => $filterYear, 'label' => 'ปีงบประมาณ', 'tone' => 'primary'],
        ],
        'statsLabel' => 'สรุปรายการอนุมัติใบลา',
    ]) ?>

    <div class="app-scroll has-stats la-scroll">
        <div class="la-body">
            <a href="<?= Html::encode($backUrl) ?>" class="btn btn-outline-secondary la-back">
                <i data-lucide="arrow-left" aria-hidden="true"></i>
                <span>กลับไปบริการ</span>
            </a>

            <div class="la-filterbar">
                <form method="get" action="<?= Html::encode(Url::to(['/mobile/default/leave-approvals'])) ?>" class="mobile-year-filter">
                    <label for="la-year-filter" class="mobile-year-filter-label">
                        <i data-lucide="calendar-days" aria-hidden="true"></i>
                        ปีงบประมาณ
                    </label>
                    <select name="year" id="la-year-filter" class="mobile-year-filter-select" onchange="this.form.submit()" aria-label="กรองปีงบประมาณ">
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
                <div class="la-empty" role="status">
                    <span class="la-empty-icon" aria-hidden="true">
                        <i data-lucide="clipboard-check" class="mi-lg"></i>
                    </span>
                    <p class="la-empty-title">ไม่มีใบลาที่รออนุมัติ</p>
                    <p class="la-empty-text">เมื่อมีคำขอในปีงบประมาณนี้ รายการจะปรากฏที่หน้านี้</p>
                </div>
            <?php else: ?>
                <div class="la-list">
                    <?php foreach ($approvals as $i => $approve): ?>
                        <?php
                        $leave = $approve->leave;
                        if (!$leave) {
                            continue;
                        }
                        $approveData = is_array($approve->data_json ?? null) ? $approve->data_json : [];
                        $detailUrl = Url::to(['/mobile/default/approve-leave', 'id' => $approve->id]);
                        $requesterEmployee = $leave->employee ?? null;
                        $requesterName = $requesterEmployee->fullname ?? '-';
                        $requesterAvatarUrl = '';
                        if ($requesterEmployee && method_exists($requesterEmployee, 'ShowAvatar')) {
                            $requesterAvatarUrl = (string) $requesterEmployee->ShowAvatar();
                        }
                        if ($requesterAvatarUrl === '') {
                            $requesterAvatarUrl = \Yii::getAlias('@web') . '/img/placeholder_cid.png';
                        }
                        $leaveType = $leave->leaveType->title ?? 'ใบลา';
                        $dateRange = trim(preg_replace('/\s+/', ' ', strip_tags((string) $leave->showLeaveDate())));
                        $reason = trim((string) ($leave->data_json['reason'] ?? ''));
                        $levelLabel = $approveData['label'] ?? $approve->title ?? 'ผู้อนุมัติ';
                        ?>
                        <a href="<?= Html::encode($detailUrl) ?>" class="la-card" style="--la-i: <?= (int) min($i, 10) ?>">
                            <span class="la-avatar" aria-hidden="true">
                                <?= Html::img($requesterAvatarUrl, [
                                    'alt' => '',
                                    'loading' => 'lazy',
                                    'decoding' => 'async',
                                ]) ?>
                                <span class="la-avatar-badge">
                                    <i data-lucide="clock-3"></i>
                                </span>
                            </span>
                            <span class="la-content">
                                <span class="la-head">
                                    <span>
                                        <span class="la-title"><?= Html::encode($requesterName) ?></span>
                                        <span class="la-sub"><?= Html::encode($levelLabel) ?></span>
                                    </span>
                                    <span class="la-pill">รออนุมัติ</span>
                                </span>

                                <span class="la-grid">
                                    <span class="la-field">
                                        <span class="la-label">ประเภทการลา</span>
                                        <span class="la-value"><?= Html::encode($leaveType) ?></span>
                                    </span>
                                    <span class="la-field">
                                        <span class="la-label">จำนวนวัน</span>
                                        <span class="la-value"><?= Html::encode((string) (float) $leave->total_days) ?> วัน</span>
                                    </span>
                                    <span class="la-field is-wide">
                                        <span class="la-label">ช่วงเวลาที่ลา</span>
                                        <span class="la-value"><?= Html::encode($dateRange !== '' ? $dateRange : '-') ?></span>
                                    </span>
                                    <?php if ($reason !== ''): ?>
                                        <span class="la-field is-wide">
                                            <span class="la-label">เหตุผล</span>
                                            <span class="la-value"><?= Html::encode($reason) ?></span>
                                        </span>
                                    <?php endif; ?>
                                </span>

                                <span class="la-foot">
                                    เปิดรายละเอียดเพื่ออนุมัติ
                                    <i data-lucide="chevron-right" aria-hidden="true"></i>
                                </span>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

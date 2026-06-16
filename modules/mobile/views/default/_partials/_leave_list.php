<?php

use app\modules\mobile\services\MobileBookingStatus;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var \app\modules\leave\models\Leave[] $myLeaves */
/** @var array<int,string> $fiscalYears   year => label */
/** @var int $filterYear */

$myLeaves    = $myLeaves ?? [];
$fiscalYears = $fiscalYears ?? [];
$filterYear  = (int) ($filterYear ?? 0);

$baseUrl = Url::to(['/mobile/default/leave-request']);
?>

<section class="bl-mode bl-mode-list" data-mode-section="list">

    <!-- Filter: ปีงบประมาณ + ค้นหา -->
    <div class="bl-list-toolbar">
        <form method="get" action="<?= Html::encode($baseUrl) ?>" class="mobile-year-filter">
            <label for="bl-year-filter" class="mobile-year-filter-label">
                <i data-lucide="calendar-days" aria-hidden="true"></i>
                ปีงบประมาณ
            </label>
            <select name="year" id="bl-year-filter" class="mobile-year-filter-select" onchange="this.form.submit()" aria-label="กรองตามปีงบประมาณ">
                <?php foreach ($fiscalYears as $year => $label): ?>
                    <option value="<?= (int) $year ?>" <?= (int) $year === $filterYear ? 'selected' : '' ?>>
                        <?= Html::encode($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if (empty($myLeaves)): ?>
        <div class="bl-list-empty">
            <span class="bl-list-empty-icon" aria-hidden="true">
                <i data-lucide="calendar-off" class="mi-xl"></i>
            </span>
            <p class="bl-list-empty-title">ยังไม่มีคำขอลาในปีนี้</p>
            <p class="bl-list-empty-text">เริ่มคำขอแรกของคุณ ระบบจะส่งให้ผู้บังคับบัญชาตรวจสอบ</p>
        </div>
    <?php else: ?>
        <div class="bl-list" id="bl-leave-list">
            <?php foreach ($myLeaves as $leave):
                $info     = MobileBookingStatus::info((string) $leave->status);
                $bucket   = $info['bucket'];
                $tone     = $info['tone'];
                $statusLb = $info['label'];

                $typeTitle = '';
                try { $typeTitle = (string) ($leave->leaveType->title ?? ''); } catch (\Throwable $e) {}
                if ($typeTitle === '') $typeTitle = 'การลา';

                $dStart = (string) $leave->date_start;
                $dEnd   = (string) $leave->date_end;
                $dateText = '—';
                try {
                    $tsStart = $dStart ? strtotime($dStart) : 0;
                    $tsEnd   = $dEnd ? strtotime($dEnd) : 0;
                    if ($tsStart && $tsEnd) {
                        $sStr = date('j', $tsStart) . ' ' . \app\components\ThaiDateHelper::formatThaiDate($dStart, 'short');
                        $eStr = date('j', $tsEnd) . ' ' . \app\components\ThaiDateHelper::formatThaiDate($dEnd, 'short');
                        $dateText = ($dStart === $dEnd) ? $sStr : ($sStr . ' ถึง ' . $eStr);
                    }
                } catch (\Throwable $e) {
                    $dateText = $dStart . ($dEnd && $dEnd !== $dStart ? ' → ' . $dEnd : '');
                }

                $totalDays = (float) ($leave->total_days ?? 0);
            ?>
                <a class="bl-list-card"
                   href="<?= Html::encode(Url::to(['/mobile/default/leave-request-view', 'id' => $leave->id])) ?>"
                   data-status="<?= Html::encode($bucket) ?>">
                    <header class="bl-list-card-head">
                        <span class="bl-list-type"><?= Html::encode($typeTitle) ?></span>
                        <span class="bl-list-pill is-<?= Html::encode($tone) ?>"><?= Html::encode($statusLb) ?></span>
                    </header>
                    <div class="bl-list-meta">
                        <span class="bl-list-meta-item">
                            <i data-lucide="calendar" aria-hidden="true"></i>
                            <?= Html::encode($dateText) ?>
                        </span>
                        <?php if ($totalDays > 0): ?>
                            <span class="bl-list-meta-item">
                                <i data-lucide="clock-3" aria-hidden="true"></i>
                                <?= rtrim(rtrim(number_format($totalDays, 1), '0'), '.') ?> วัน
                            </span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

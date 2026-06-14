<?php

use app\modules\mobile\services\MobileBookingStatus;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var \app\modules\booking\models\Meeting[] $myBookings */
/** @var array<string,string> $rooms code => title (เร็วกว่าเรียก $booking->room ต่อแถว) */
/** @var callable $formatThaiDate Closure(?string $d): string */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */

$myBookings     = $myBookings ?? [];
$rooms          = $rooms ?? [];
$formatThaiDate = $formatThaiDate ?? static fn ($d) => (string) $d;
$fiscalYears    = $fiscalYears ?? [];
$filterYear     = (int) ($filterYear ?? 0);
$baseUrl        = Url::to(['/mobile/default/booking-meeting']);
?>

<section class="bm-mode bm-mode-list bm-panel" data-mode-section="list">
    <div class="bm-list-toolbar">
        <form method="get" action="<?= Html::encode($baseUrl) ?>" class="mobile-year-filter">
            <label for="bm-year-filter" class="mobile-year-filter-label">
                <i data-lucide="calendar-days" aria-hidden="true"></i>
                ปีงบประมาณ
            </label>
            <select name="year" id="bm-year-filter" class="mobile-year-filter-select" onchange="this.form.submit()" aria-label="กรองปีงบประมาณ">
                <?php foreach ($fiscalYears as $year => $label): ?>
                    <?php $year = (int) $year; ?>
                    <option value="<?= $year ?>" <?= $filterYear === $year ? 'selected' : '' ?>>
                        <?= Html::encode($label) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
        <input type="search"
               id="bm-list-search"
               class="bm-search"
               placeholder="ค้นหารหัส, ห้อง, หัวข้อประชุม"
               autocomplete="off"
               aria-label="ค้นหารายการจองห้องประชุม">
    </div>

    <?php if (empty($myBookings)): ?>
        <div class="bm-list-empty">
            <span class="bm-list-empty-icon" aria-hidden="true">
                <i data-lucide="calendar-check"></i>
            </span>
            <p class="bm-list-empty-title">ยังไม่มีรายการจองห้องประชุม</p>
            <p class="bm-list-empty-text">ยังไม่มีรายการในปีงบประมาณนี้ กดปุ่มจองห้องประชุมเพื่อเริ่มคำขอแรก</p>
        </div>
    <?php else: ?>
        <div class="bm-list" id="bm-booking-list">
            <?php foreach ($myBookings as $booking):
                $info = MobileBookingStatus::info((string) $booking->status);

                $roomTitle = (string) ($rooms[(string) $booking->room_id] ?? '');
                if ($roomTitle === '') {
                    try {
                        $roomTitle = (string) ($booking->room->title ?? $booking->room_id);
                    } catch (\Throwable $e) {
                        $roomTitle = (string) $booking->room_id;
                    }
                }
                $title = trim((string) $booking->title) ?: 'คำขอจองห้องประชุม';

                $startThai = $formatThaiDate((string) $booking->date_start);
                $endThai   = $formatThaiDate((string) $booking->date_end);
                $dateText  = ((string) $booking->date_start === (string) $booking->date_end || !$booking->date_end)
                    ? $startThai
                    : ($startThai . ' ถึง ' . $endThai);
                $timeText  = trim(substr((string) $booking->time_start, 0, 5)) . ' - ' . trim(substr((string) $booking->time_end, 0, 5)) . ' น.';
                $peopleText = $booking->emp_number ? ((int) $booking->emp_number . ' คน') : '';

                $searchText = mb_strtolower(implode(' ', array_filter([
                    (string) $booking->code,
                    $title,
                    $roomTitle,
                    $info['label'],
                    $dateText,
                ])), 'UTF-8');
            ?>
                <a class="bm-list-card"
                   href="<?= Html::encode(Url::to(['/mobile/default/meeting-view', 'id' => $booking->id])) ?>"
                   data-status="<?= Html::encode($info['bucket']) ?>"
                   data-search="<?= Html::encode($searchText) ?>">
                    <header class="bm-list-card-head">
                        <span class="bm-list-code"><?= Html::encode((string) $booking->code) ?></span>
                        <span class="bm-list-pill is-<?= Html::encode($info['tone']) ?>"><?= Html::encode($info['label']) ?></span>
                    </header>
                    <h2 class="bm-list-title"><?= Html::encode($title) ?></h2>
                    <div class="bm-list-meta">
                        <span class="bm-list-meta-item">
                            <i data-lucide="door-open" aria-hidden="true"></i>
                            <?= Html::encode($roomTitle) ?>
                        </span>
                        <span class="bm-list-meta-item">
                            <i data-lucide="calendar" aria-hidden="true"></i>
                            <?= Html::encode($dateText) ?>
                        </span>
                        <span class="bm-list-meta-item">
                            <i data-lucide="clock" aria-hidden="true"></i>
                            <?= Html::encode($timeText) ?>
                        </span>
                        <?php if ($peopleText !== ''): ?>
                            <span class="bm-list-meta-item">
                                <i data-lucide="users" aria-hidden="true"></i>
                                <?= Html::encode($peopleText) ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="bm-list-no-results" id="bm-list-no-results" role="status" hidden>
            ไม่พบรายการที่ตรงกับการค้นหา
        </p>
    <?php endif; ?>
</section>

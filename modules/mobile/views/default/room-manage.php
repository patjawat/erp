<?php

use app\components\ThaiDateHelper;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $current_page */
/** @var \app\modules\booking\models\Meeting[] $meetings */
/** @var string[] $ownedRoomCodes */
/** @var array<string,string> $roomTitles */
/** @var array<string,int> $statsCount */
/** @var array<int,string> $fiscalYears */
/** @var int $filterYear */
/** @var int $currentYear */
/** @var string $filterStatus */
/** @var string $filterRoom */

$this->params['current_page']   = $current_page ?? 'profile';
$this->params['mobileTitle']    = 'จัดการห้องประชุม';
$this->params['mobileSubtitle'] = 'อนุมัติหรือยกเลิกคำขอจองห้องที่คุณดูแล';

$meetings       = $meetings ?? [];
$ownedRoomCodes = $ownedRoomCodes ?? [];
$roomTitles     = $roomTitles ?? [];
$statsCount     = $statsCount ?? ['pending' => 0, 'passed' => 0, 'cancelled' => 0, 'total' => 0];
$fiscalYears    = $fiscalYears ?? [];
$filterYear     = (int) ($filterYear ?? 0);
$currentYear    = (int) ($currentYear ?? 0);
$filterStatus   = in_array($filterStatus ?? 'pending', ['pending', 'passed', 'cancelled', 'all'], true) ? $filterStatus : 'pending';
$filterRoom     = (string) ($filterRoom ?? '');
if (empty($fiscalYears) && $filterYear > 0) {
    $fiscalYears[$filterYear] = 'พ.ศ. ' . $filterYear;
}

$roomManageUrl = static function (array $overrides = []) use ($filterStatus, $filterRoom, $filterYear): string {
    $params = array_merge([
        'status' => $filterStatus,
        'room' => $filterRoom,
        'year' => $filterYear,
    ], $overrides);
    if (($params['room'] ?? '') === '') {
        unset($params['room']);
    }
    return Url::to(array_merge(['/mobile/default/room-manage'], $params));
};

/**
 * Map a meeting status to a presentation bucket so the view doesn't repeat the
 * Pass/Pending/Cancel/Reject branching everywhere.
 */
$statusBucket = static function (?string $code): string {
    $code = (string) $code;
    if (in_array($code, ['Pass', 'Approve'], true)) return 'passed';
    if (in_array($code, ['Cancel', 'Reject'], true)) return 'cancelled';
    if ($code === 'Pending') return 'pending';
    return 'other';
};

/**
 * Group meetings by date bucket — relative to today — for the section dividers.
 */
$dateBucket = static function (?string $date): string {
    if (!$date) return 'older';
    $ts = strtotime($date);
    if (!$ts) return 'older';
    $todayTs    = strtotime('today');
    $tomorrowTs = strtotime('tomorrow');
    $weekEndTs  = strtotime('+7 days');
    if ($ts >= $todayTs && $ts < $tomorrowTs)      return 'today';
    if ($ts >= $tomorrowTs && $ts < strtotime('+2 days')) return 'tomorrow';
    if ($ts >= $tomorrowTs && $ts < $weekEndTs)    return 'this_week';
    if ($ts < $todayTs) return 'past';
    return 'later';
};

$bucketLabels = [
    'today'     => 'วันนี้',
    'tomorrow'  => 'พรุ่งนี้',
    'this_week' => 'ภายในสัปดาห์',
    'later'     => 'อนาคต',
    'past'      => 'ผ่านมาแล้ว',
];

$heroStats = [
    [
        'url' => $roomManageUrl(['status' => 'pending']),
        'value' => $statsCount['pending'] ?? 0,
        'label' => 'รอดำเนินการ',
        'tone' => 'warning',
        'isActive' => $filterStatus === 'pending',
        'data' => ['status-filter' => 'pending'],
    ],
    [
        'url' => $roomManageUrl(['status' => 'passed']),
        'value' => $statsCount['passed'] ?? 0,
        'label' => 'อนุมัติแล้ว',
        'tone' => 'success',
        'isActive' => $filterStatus === 'passed',
        'data' => ['status-filter' => 'passed'],
    ],
    [
        'url' => $roomManageUrl(['status' => 'cancelled']),
        'value' => $statsCount['cancelled'] ?? 0,
        'label' => 'ยกเลิก',
        'tone' => 'danger',
        'isActive' => $filterStatus === 'cancelled',
        'data' => ['status-filter' => 'cancelled'],
    ],
    [
        'url' => $roomManageUrl(['status' => 'all']),
        'value' => $statsCount['total'] ?? 0,
        'label' => 'ทั้งหมด',
        'tone' => 'primary',
        'isActive' => $filterStatus === 'all',
        'data' => ['status-filter' => 'all'],
    ],
];
$heroSubtitle = 'ปีงบประมาณ ' . ($fiscalYears[$filterYear] ?? ('พ.ศ. ' . $filterYear));
?>

<style>
.rm-scroll {
    padding-bottom: calc(env(safe-area-inset-bottom, 0px) + 6rem);
}
.rm-stack {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}
.rm-filter {
    position: sticky;
    top: var(--shell-h, 13rem);
    z-index: 5;
    background: color-mix(in oklch, #f8fafc 88%, transparent);
    backdrop-filter: blur(14px);
    padding: var(--space-sm) var(--space-md);
    margin: calc(-1 * var(--space-xs)) calc(-1 * var(--space-md)) 0;
    box-shadow: 0 1px 0 var(--ink-line), 0 10px 24px rgba(15, 23, 42, 0.04);
}
.rm-filter-form {
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
    margin: 0;
}
.rm-filter-top {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}
.rm-filter-label {
    display: inline-flex;
    align-items: center;
    gap: var(--space-2xs);
    flex-shrink: 0;
    margin: 0;
    color: var(--ink-3);
    font-size: var(--fs-xs);
    font-weight: 700;
}
.rm-filter-year-wrap {
    position: relative;
    flex: 1;
    min-width: 0;
}
.rm-filter-year,
.rm-filter-room {
    width: 100%;
    min-height: 2.75rem;
    border: 1px solid var(--ink-line);
    border-radius: 12px;
    background: #fff;
    color: var(--ink);
    font-size: var(--fs-sm);
    font-weight: 600;
    padding: 0 var(--space-md);
    box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.8);
    transition: border-color 160ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 160ms cubic-bezier(0.16, 1, 0.3, 1);
}
.rm-filter-year:focus,
.rm-filter-room:focus {
    border-color: var(--mobile-primary);
    box-shadow: 0 0 0 3px var(--mobile-primary-soft);
    outline: 0;
}
.rm-filter-row {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
}
.rm-filter-row .pill-group {
    flex: 1;
    min-width: 0;
    overflow-x: auto;
    scrollbar-width: none;
}
.rm-filter-row .pill-group::-webkit-scrollbar {
    display: none;
}
.rm-filter-room {
    flex-shrink: 0;
    max-width: 9.75rem;
}

.rm-bucket {
    font-size: var(--fs-sm); font-weight: 700; color: var(--ink-3);
    letter-spacing: -0.005em;
    margin: var(--space-xs) 0 var(--space-xs);
    display: flex; align-items: center; gap: var(--space-xs);
}
.rm-bucket::after { content: ''; flex: 1; height: 1px; background: var(--ink-line); }

.rm-card {
    background: #fff;
    border: 1px solid color-mix(in oklch, var(--ink-line) 75%, transparent);
    border-radius: 16px;
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.05), 0 1px 2px rgba(15, 23, 42, 0.04);
    padding: var(--space-md);
    margin-bottom: var(--space-xs);
    min-height: 5.25rem;
    transition: transform 180ms cubic-bezier(0.16, 1, 0.3, 1), box-shadow 180ms cubic-bezier(0.16, 1, 0.3, 1), border-color 180ms cubic-bezier(0.16, 1, 0.3, 1), opacity 180ms ease;
}
.rm-card[hidden] { display: none !important; }
.rm-card-head { display: flex; align-items: center; gap: var(--space-sm); }
.rm-medal {
    width: 2.5rem; height: 2.5rem; border-radius: 12px;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.rm-medal svg { width: 1.125rem; height: 1.125rem; }
.rm-medal.is-pending   { background: var(--warning-soft);  color: var(--warning); }
.rm-medal.is-passed    { background: var(--success-soft);   color: var(--success); }
.rm-medal.is-cancelled { background: var(--danger-soft);   color: var(--danger-strong); }
.rm-medal.is-other     { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }

.rm-card-body { flex-grow: 1; min-width: 0; }
.rm-card-title {
    font-size: var(--fs-md); font-weight: 600; color: var(--ink);
    margin: 0; line-height: 1.3;
    overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.rm-card-meta {
    font-size: var(--fs-xs); color: var(--ink-3);
    margin: 2px 0 0; line-height: 1.4;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.rm-card-time { color: var(--mobile-primary); font-weight: 500; }
.rm-card-pill {
    flex-shrink: 0;
    font-size: var(--fs-2xs); font-weight: 600;
    padding: 4px 10px; border-radius: 999px;
}
.rm-card-pill.is-pending   { background: var(--warning-soft);  color: var(--warning); }
.rm-card-pill.is-passed    { background: var(--success-soft);   color: var(--success); }
.rm-card-pill.is-cancelled { background: var(--danger-soft);   color: var(--danger-strong); }
.rm-card-pill.is-other     { background: rgba(100, 116, 139, 0.13); color: var(--ink-3); }

.rm-card-detail {
    font-size: var(--fs-xs); color: var(--ink-4);
    margin: var(--space-sm) 0 0;
    padding-top: var(--space-sm);
    border-top: 1px solid var(--ink-line);
    display: flex; flex-wrap: wrap; gap: var(--space-2xs) var(--space-md);
}
.rm-card-detail strong { color: var(--ink-2); font-weight: 600; }

a.rm-card { display: block; text-decoration: none; color: inherit; cursor: pointer; }
.rm-card:hover {
    border-color: color-mix(in oklch, var(--mobile-primary) 18%, var(--ink-line));
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08), 0 2px 5px rgba(15, 23, 42, 0.05);
}
.rm-card:active { transform: scale(0.992); }
.rm-card:focus-visible { outline: 2px solid var(--mobile-primary); outline-offset: 2px; }

/* Main modal — fullscreen overrides for mobile booking details */
#main-modal.modal .modal-dialog.modal-fullscreen .modal-content { background: #f5f7fa; }
#main-modal.modal .modal-header { background: #fff; }
#main-modal.modal .modal-footer {
    background: #fff;
    padding: var(--space-sm) var(--space-md) calc(env(safe-area-inset-bottom, 0px) + var(--space-sm));
    gap: var(--space-xs);
    flex-wrap: nowrap;
}
#main-modal.modal .modal-footer .btn {
    min-height: 3rem; border-radius: 12px;
    font-weight: 600; font-size: var(--fs-md);
}

/* ── Empty + no-results states ────────────────────────────────────────── */
.rm-empty {
    text-align: center; padding: var(--space-2xl) var(--space-md);
    color: var(--ink-4);
}
.rm-empty .rm-empty-icon {
    width: 4rem; height: 4rem; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--mobile-primary-soft);
    color: var(--mobile-primary);
    margin-bottom: var(--space-md);
}
.rm-empty-title { font-size: var(--fs-lg); font-weight: 700; color: var(--ink); margin: 0 0 var(--space-2xs); }
.rm-empty-text { font-size: var(--fs-sm); color: var(--ink-4); margin: 0; line-height: 1.5; }

@media (prefers-reduced-motion: reduce) {
    .rm-filter,
    .rm-filter-year,
    .rm-filter-room,
    .rm-card { transition: none !important; animation: none !important; }
    .rm-card:hover,
    .rm-card:active { transform: none !important; }
}
@media (prefers-reduced-motion: no-preference) {
    .rm-filter {
        animation: rm-soft-drop 220ms cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .rm-card {
        animation: rm-card-in 240ms cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: calc(var(--rm-i, 0) * 18ms);
    }
}
@keyframes rm-soft-drop {
    from { opacity: 0; transform: translateY(-6px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes rm-card-in {
    from { opacity: 0; transform: translateY(8px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

<?= $this->render('@app/modules/mobile/views/layouts/_partials/_hero_shell', [
    'icon' => 'calendar-check',
    'title' => $this->params['mobileTitle'],
    'subtitle' => $heroSubtitle,
    'stats' => $heroStats,
    'statsLabel' => 'สรุปรายการจัดห้องประชุม',
]) ?>

<div class="app-scroll has-stats rm-scroll">
<div class="mobile-stack-tight rm-stack">

    <?php if (empty($ownedRoomCodes)): ?>
        <div class="rm-empty">
            <span class="rm-empty-icon"><i data-lucide="shield-question" class="mi-lg"></i></span>
            <p class="rm-empty-title">ไม่พบห้องที่คุณดูแล</p>
            <p class="rm-empty-text">ระบบจะแสดงรายการคำขอจองห้องที่คุณเป็นผู้ดูแล กรุณาติดต่อผู้ดูแลระบบเพื่อกำหนดสิทธิ์</p>
        </div>
    <?php else: ?>

        <div class="rm-filter">
            <form method="get" action="<?= Html::encode(Url::to(['/mobile/default/room-manage'])) ?>" class="rm-filter-form">
                <div class="rm-filter-top">
                    <label class="rm-filter-label" for="rm-year-filter">
                        <i data-lucide="calendar-days" class="mi-sm" aria-hidden="true"></i>
                        ปีงบประมาณ
                    </label>
                    <div class="rm-filter-year-wrap">
                        <select class="rm-filter-year" id="rm-year-filter" name="year" aria-label="กรองปีงบประมาณ" onchange="this.form.submit()">
                            <?php foreach ($fiscalYears as $year => $label): ?>
                                <?php $year = (int) $year; ?>
                                <option value="<?= $year ?>" <?= $filterYear === $year ? 'selected' : '' ?>>
                                    <?= Html::encode($label) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="rm-filter-row">
                    <div class="pill-group" role="radiogroup" aria-label="กรองตามสถานะ">
                        <?php
                        $statusOptions = [
                            'pending'   => 'รอ',
                            'passed'    => 'อนุมัติ',
                            'cancelled' => 'ยกเลิก',
                            'all'       => 'ทั้งหมด',
                        ];
                        foreach ($statusOptions as $key => $label):
                            $checked = $filterStatus === $key;
                        ?>
                            <label class="pill-option <?= $checked ? 'is-active' : '' ?>">
                                <input type="radio" name="status" value="<?= Html::encode($key) ?>" <?= $checked ? 'checked' : '' ?>>
                                <?= Html::encode($label) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?php if (count($roomTitles) > 1): ?>
                        <select class="rm-filter-room" id="rm-room-filter" name="room" aria-label="กรองตามห้อง">
                            <option value="">ทุกห้อง</option>
                            <?php foreach ($roomTitles as $code => $title): ?>
                                <option value="<?= Html::encode($code) ?>" <?= $filterRoom === $code ? 'selected' : '' ?>>
                                    <?= Html::encode($title) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <?php if (empty($meetings)): ?>
            <div class="rm-empty">
                <span class="rm-empty-icon"><i data-lucide="calendar-check" class="mi-lg"></i></span>
                <p class="rm-empty-title">ยังไม่มีคำขอจอง</p>
                <p class="rm-empty-text">คำขอจองห้องที่คุณดูแลในปีงบประมาณนี้จะปรากฏที่นี่</p>
            </div>
        <?php else: ?>

            <?php
            // Bucket meetings by date for visual grouping.
            $bucketedMeetings = [];
            foreach ($meetings as $m) {
                $bucketedMeetings[$dateBucket((string) $m->date_start)][] = $m;
            }
            $bucketOrder = ['today', 'tomorrow', 'this_week', 'later', 'past'];
            $visibleCount = 0;
            ?>

            <div id="rm-list">
                <?php foreach ($bucketOrder as $bucketKey): ?>
                    <?php if (empty($bucketedMeetings[$bucketKey])) continue; ?>
                    <?php $bucketCount = count($bucketedMeetings[$bucketKey]); ?>

                    <div class="rm-bucket" data-bucket="<?= Html::encode($bucketKey) ?>">
                        <i data-lucide="<?= $bucketKey === 'past' ? 'history' : 'calendar' ?>" class="mi-sm"></i>
                        <?= Html::encode($bucketLabels[$bucketKey]) ?>
                        <span class="text-body-secondary fw-normal small">(<?= $bucketCount ?>)</span>
                    </div>

                    <?php foreach ($bucketedMeetings[$bucketKey] as $m): ?>
                        <?php
                        $bucket    = $statusBucket($m->status);
                        $isPending = $bucket === 'pending';

                        try {
                            $statusInfo  = $m->getStatus($m->status);
                            $statusTitle = $statusInfo['title'] ?? $m->status;
                        } catch (\Throwable $e) {
                            $statusTitle = (string) $m->status;
                        }

                        $roomTitle = $m->room ? $m->room->title : ($roomTitles[$m->room_id] ?? $m->room_id);
                        $dateStr   = $m->date_start ? ThaiDateHelper::formatThaiDate($m->date_start) : '—';
                        $timeStr   = trim(substr($m->time_start ?? '', 0, 5) . ' - ' . substr($m->time_end ?? '', 0, 5), ' -');

                        $requesterName = '—';
                        try {
                            if ($m->employee) {
                                $requesterName = $m->employee->fullname ?? $m->emp_id;
                            }
                        } catch (\Throwable $e) {}

                        $statusIcon = [
                            'pending'   => 'clock',
                            'passed'    => 'check-circle-2',
                            'cancelled' => 'x-circle',
                            'other'     => 'circle',
                        ][$bucket];

                        $visibleCount++;
                        ?>
                        <a class="rm-card open-modal"
                           href="<?= Html::encode(Url::to(['/mobile/default/meeting-detail', 'id' => $m->id])) ?>"
                           style="--rm-i: <?= (int) min($visibleCount, 10) ?>"
                           data-size="modal-fullscreen"
                           aria-label="ดูรายละเอียดการจอง <?= Html::encode($m->title ?: $m->code) ?>"
                           data-meeting-id="<?= (int) $m->id ?>"
                           data-status="<?= Html::encode($bucket) ?>"
                           data-room="<?= Html::encode((string) $m->room_id) ?>">
                            <div class="rm-card-head">
                                <span class="rm-medal is-<?= Html::encode($bucket) ?>" aria-hidden="true">
                                    <i data-lucide="<?= Html::encode($statusIcon) ?>"></i>
                                </span>
                                <div class="rm-card-body">
                                    <h3 class="rm-card-title"><?= Html::encode($m->title ?: $m->code) ?></h3>
                                    <p class="rm-card-meta">
                                        <?= Html::encode($roomTitle) ?>
                                        · <?= Html::encode($dateStr) ?>
                                        <?php if ($timeStr): ?>
                                            · <span class="rm-card-time"><?= Html::encode($timeStr) ?> น.</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <span class="rm-card-pill is-<?= Html::encode($bucket) ?>"><?= Html::encode($statusTitle) ?></span>
                            </div>

                            <div class="rm-card-detail">
                                <span><strong>รหัส</strong> <?= Html::encode($m->code) ?></span>
                                <span><strong>ผู้ขอ</strong> <?= Html::encode($requesterName) ?></span>
                                <?php if (!empty($m->emp_number)): ?>
                                    <span><strong>ผู้เข้าร่วม</strong> <?= Html::encode((string) $m->emp_number) ?> คน</span>
                                <?php endif; ?>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php endforeach; ?>

                <!-- Client-side filter empty state -->
                <div id="rm-no-results" class="rm-empty d-none">
                    <span class="rm-empty-icon"><i data-lucide="search-x" class="mi-lg"></i></span>
                    <p class="rm-empty-title">ไม่มีรายการตามตัวกรอง</p>
                    <p class="rm-empty-text">ลองเปลี่ยนสถานะ ห้อง หรือปีงบประมาณ</p>
                </div>
            </div>

        <?php endif; ?>
    <?php endif; ?>
</div>
</div>

<?php if (!empty($meetings)): ?>
<?php
$confirmUrl = Url::to(['/mobile/default/meeting-confirm']);
$csrfParam  = \yii\helpers\Json::encode(Yii::$app->request->csrfParam);
$csrfToken  = \yii\helpers\Json::encode(Yii::$app->request->csrfToken);
$initialStatusFilter = \yii\helpers\Json::encode($filterStatus);
$initialRoomFilter   = \yii\helpers\Json::encode($filterRoom);
$js = <<<JS
(function() {
    var confirmUrl = "{$confirmUrl}";
    var csrfParam  = {$csrfParam};
    var csrfToken  = {$csrfToken};
    var listEl     = document.getElementById('rm-list');
    var noResults  = document.getElementById('rm-no-results');
    if (!listEl) return;

    var state = {
        status: {$initialStatusFilter},
        room:   {$initialRoomFilter},
    };

    // Apply filter (status + room), then toggle rows and date headers.
    function applyFilter() {
        var cards = listEl.querySelectorAll('.rm-card');
        var visibleByBucket = {};
        cards.forEach(function(card) {
            var status = card.dataset.status;
            var room   = card.dataset.room;
            var matchStatus = state.status === 'all' || state.status === status;
            var matchRoom   = !state.room || state.room === room;
            var visible = matchStatus && matchRoom;
            card.hidden = !visible;
            if (visible) {
                // Track the closest preceding date bucket for this card.
                var bucketHeader = card.previousElementSibling;
                while (bucketHeader && !bucketHeader.classList.contains('rm-bucket')) {
                    bucketHeader = bucketHeader.previousElementSibling;
                }
                if (bucketHeader) {
                    var key = bucketHeader.dataset.bucket;
                    visibleByBucket[key] = (visibleByBucket[key] || 0) + 1;
                }
            }
        });

        listEl.querySelectorAll('.rm-bucket').forEach(function(hdr) {
            hdr.hidden = !visibleByBucket[hdr.dataset.bucket];
        });

        var totalVisible = Object.values(visibleByBucket).reduce(function(a, b) { return a + b; }, 0);
        if (noResults) noResults.classList.toggle('d-none', totalVisible > 0);
    }

    document.querySelectorAll('input[name="status"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            if (!this.checked) return;
            state.status = this.value;
            this.closest('.pill-group').querySelectorAll('.pill-option').forEach(function(opt) {
                opt.classList.remove('is-active');
            });
            this.closest('label').classList.add('is-active');
            applyFilter();
            syncHeroStats();
        });
    });

    function syncHeroStats() {
        document.querySelectorAll('.app-stat[data-status-filter]').forEach(function(chip) {
            chip.classList.toggle('is-active', chip.dataset.statusFilter === state.status);
        });
    }

    document.querySelectorAll('.app-stat[data-status-filter]').forEach(function(chip) {
        chip.addEventListener('click', function(e) {
            var status = this.dataset.statusFilter;
            if (!status) return;
            e.preventDefault();
            var pill = document.querySelector('input[name="status"][value="' + status + '"]');
            if (pill) {
                pill.checked = true;
                pill.dispatchEvent(new Event('change', { bubbles: true }));
                return;
            }
            state.status = status;
            applyFilter();
            syncHeroStats();
        });
    });

    var roomFilter = document.getElementById('rm-room-filter');
    if (roomFilter) {
        roomFilter.addEventListener('change', function() {
            state.room = this.value || '';
            applyFilter();
        });
    }

    // Called by .open-modal initCallback after the modal body is injected.
    window.lucideRefresh = function() {
        if (typeof lucide !== 'undefined' && lucide.createIcons) lucide.createIcons();
    };

    // Action buttons use delegated handlers because modal footer buttons share the same flow.
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.rm-action');
        if (!btn) return;
        // Scope: room-manage list OR the shared #main-modal (footer of the booking detail).
        if (!btn.closest('#rm-list') && !btn.closest('#main-modal')) return;

        var id     = btn.dataset.id;
        var status = btn.dataset.status;
        var title  = btn.dataset.confirmTitle || 'ยืนยันการดำเนินการ?';
        var text   = btn.dataset.confirmText  || '';

        if (!id) {
            if (typeof Swal !== 'undefined') Swal.fire({ icon: 'error', title: 'ไม่พบรหัสรายการจอง' });
            return;
        }

        var doRequest = function() {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'กำลังบันทึก...', allowOutsideClick: false, didOpen: function(){ Swal.showLoading(); } });
            }
            var formData = new FormData();
            formData.append('id', id);
            formData.append('status', status);
            formData.append(csrfParam, csrfToken);
            fetch(confirmUrl, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            }).then(function(r) { return r.json(); }).then(function(res) {
                if (typeof Swal !== 'undefined') Swal.close();
                if (res && res.ok) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'success', title: 'สำเร็จ', text: res.message || 'บันทึกแล้ว', timer: 1500, showConfirmButton: false })
                            .then(function() { window.location.reload(); });
                    } else {
                        window.location.reload();
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'ไม่สำเร็จ', text: (res && res.message) || 'เกิดข้อผิดพลาด' });
                    } else {
                        alert((res && res.message) || 'เกิดข้อผิดพลาด');
                    }
                }
            }).catch(function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'เชื่อมต่อไม่สำเร็จ' });
                } else {
                    alert('เกิดข้อผิดพลาด');
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: title,
                text: text,
                icon: status === 'Pass' ? 'question' : 'warning',
                showCancelButton: true,
                confirmButtonText: status === 'Pass' ? 'อนุมัติ' : 'ยกเลิกการจอง',
                cancelButtonText: 'ปิด',
                confirmButtonColor: status === 'Pass' ? 'var(--success)' : 'var(--danger)',
                reverseButtons: true,
            }).then(function(r) { if (r.isConfirmed) doRequest(); });
        } else if (confirm(title)) {
            doRequest();
        }
    });

    applyFilter();
    syncHeroStats();
})();
JS;
$this->registerJs($js, \yii\web\View::POS_READY);
?>
<?php endif; ?>

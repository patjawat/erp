<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\ThaiDateHelper;

/** @var yii\web\View $this */
/** @var array[] $rows */
/** @var string $targetIso */
/** @var string $todayIso */
/** @var string $tomorrowIso */
/** @var int $timelineStartMin */
/** @var int $timelineEndMin */
/** @var string|null $activeType */
/** @var array<string,string> $assetImages map: ref => image URL */

$this->title = 'ตารางการใช้รถยนต์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/vehicle-dashboard.css');

$isToday = $targetIso === $todayIso;
$isTomorrow = $targetIso === $tomorrowIso;
$targetLabel = ThaiDateHelper::formatThaiDate($targetIso, 'medium');

$fmtHour = fn(int $min) => sprintf('%02d:%02d', intdiv($min, 60), $min % 60);
$headingId = 'schedule-heading';

// ป้ายชั่วโมงสำหรับ scale (ทุก 2 ชม.)
$tickStep = 120;
$ticks = [];
for ($m = $timelineStartMin; $m <= $timelineEndMin; $m += $tickStep) {
    $ticks[] = [
        'label' => $fmtHour($m),
        'left' => ($m - $timelineStartMin) / ($timelineEndMin - $timelineStartMin) * 100,
    ];
}
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-clock fs-x1" aria-hidden="true"></i> <?= $this->title ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?= Html::encode($targetLabel) ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/booking/vehicle_menu', ['active' => 'schedule']) ?>
<?php $this->endBlock(); ?>

<section class="card schedule-card" aria-labelledby="<?= $headingId ?>">
    <div class="card-body">
        <header class="schedule-header mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 id="<?= $headingId ?>" class="h6 card-title mb-1 d-flex align-items-center gap-2">
                        <span class="schedule-heading-dot text-primary" aria-hidden="true">
                            <i class="fa-regular fa-clock"></i>
                        </span>
                        ตารางการใช้รถแต่ละคัน
                    </h2>
                    <div class="text-muted small">
                        <?= Html::encode($targetLabel) ?>
                        · ช่วงเวลา <?= $fmtHour($timelineStartMin) ?>–<?= $fmtHour($timelineEndMin) ?>
                    </div>
                </div>
                <div class="btn-group schedule-day-toggle" role="group" aria-label="เลือกวันที่ของตาราง">
                    <a href="<?= Url::current(['date' => $todayIso]) ?>"
                        class="btn btn-sm rounded-pill px-3 <?= $isToday ? 'btn-primary' : 'btn-outline-secondary' ?>"
                        aria-pressed="<?= $isToday ? 'true' : 'false' ?>">
                        วันนี้
                    </a>
                    <a href="<?= Url::current(['date' => $tomorrowIso]) ?>"
                        class="btn btn-sm rounded-pill px-3 <?= $isTomorrow ? 'btn-primary' : 'btn-outline-secondary' ?>"
                        aria-pressed="<?= $isTomorrow ? 'true' : 'false' ?>">
                        พรุ่งนี้
                    </a>
                </div>
            </div>
            <?php
            $typeOptions = [
                ['key' => null, 'label' => 'ทั้งหมด', 'icon' => 'fa-layer-group'],
                ['key' => 'official', 'label' => 'รถยนต์ทั่วไป', 'icon' => 'fa-car'],
                ['key' => 'ambulance', 'label' => 'รถฉุกเฉิน', 'icon' => 'fa-truck-medical'],
            ];
            ?>
            <div class="schedule-type-filter" role="group" aria-label="กรองประเภทรถ">
                <?php foreach ($typeOptions as $opt): ?>
                    <?php
                    $isActive = ($activeType ?? null) === $opt['key'];
                    $url = Url::current(['type' => $opt['key']]);
                    ?>
                    <a href="<?= $url ?>"
                        class="btn btn-sm rounded-pill px-3 <?= $isActive ? 'btn-primary' : 'btn-outline-secondary' ?>"
                        aria-pressed="<?= $isActive ? 'true' : 'false' ?>">
                        <i class="fa-solid <?= $opt['icon'] ?> me-1" aria-hidden="true"></i>
                        <?= Html::encode($opt['label']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </header>

        <?php if (empty($rows)): ?>
            <div class="text-center text-muted py-5" role="status">
                <i class="fa-regular fa-folder-open fs-3 d-block mb-2" aria-hidden="true"></i>
                <?php
                $emptyMsg = match ($activeType) {
                    'official'  => 'ยังไม่มีรถยนต์ทั่วไปในระบบ',
                    'ambulance' => 'ยังไม่มีรถฉุกเฉินในระบบ',
                    default     => 'ยังไม่มีรถยนต์ในระบบ',
                };
                echo Html::encode($emptyMsg);
                ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle schedule-table mb-0">
                    <thead>
                        <tr>
                            <th class="schedule-col-vehicle">รถ</th>
                            <th class="schedule-col-timeline">
                                ช่วงเวลา (<?= $fmtHour($timelineStartMin) ?>–<?= $fmtHour($timelineEndMin) ?>)
                            </th>
                            <th class="schedule-col-status text-end">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rows as $row): ?>
                            <?php
                            /** @var app\modules\am\models\Asset $vehicle */
                            $vehicle = $row['asset'];
                            $isMaintenance = $row['is_maintenance'];
                            $bookingCount = $row['booking_count'];
                            $blocks = $row['blocks'];

                            if ($isMaintenance) {
                                $statusKey = 'maintenance';
                                $statusLabel = 'ซ่อมบำรุง';
                            } elseif ($bookingCount > 0) {
                                $statusKey = 'booked';
                                $statusLabel = 'มีจอง ' . $bookingCount;
                            } else {
                                $statusKey = 'free';
                                $statusLabel = 'ว่าง';
                            }

                            $deptTag = trim((string) ($vehicle->department_name ?: ''));
                            $assetName = $vehicle->asset_name ?: '-';

                            // รูปรถ: ใช้ assetImages map ที่ controller preload ไว้ (แก้ N+1)
                            $imgUrl = !empty($vehicle->ref) ? ($assetImages[$vehicle->ref] ?? '') : '';
                            $hasRealImg = $imgUrl !== '';
                            ?>
                            <tr>
                                <td class="schedule-col-vehicle">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if ($hasRealImg): ?>
                                            <span class="schedule-vehicle-thumb" aria-hidden="true">
                                                <img src="<?= Html::encode($imgUrl) ?>"
                                                    alt=""
                                                    width="56"
                                                    height="56"
                                                    loading="lazy"
                                                    onerror="const p=this.parentNode;p.classList.add('schedule-vehicle-thumb-fallback');p.innerHTML='<i class=\'fa-solid fa-bus\'></i>';">
                                            </span>
                                        <?php else: ?>
                                            <span class="schedule-vehicle-thumb schedule-vehicle-thumb-fallback" aria-hidden="true">
                                                <i class="fa-solid fa-bus"></i>
                                            </span>
                                        <?php endif; ?>
                                        <div class="schedule-vehicle-text">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span class="schedule-vehicle-plate">
                                                    <?= Html::encode($vehicle->license_plate) ?>
                                                </span>
                                                <?php if ($deptTag !== ''): ?>
                                                    <span class="schedule-vehicle-tag">
                                                        <?= Html::encode($deptTag) ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="text-muted small text-truncate" title="<?= Html::encode($assetName) ?>">
                                                <?= Html::encode($assetName) ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="schedule-col-timeline">
                                    <div class="schedule-timeline">
                                        <?php if ($isMaintenance): ?>
                                            <div class="schedule-track schedule-track-maintenance">
                                                <span>
                                                    <i class="fa-solid fa-screwdriver-wrench me-1" aria-hidden="true"></i>
                                                    ซ่อมบำรุง
                                                </span>
                                            </div>
                                        <?php elseif (empty($blocks)): ?>
                                            <div class="schedule-track schedule-track-free">
                                                <span>ว่างทั้งวัน</span>
                                            </div>
                                        <?php else: ?>
                                            <div class="schedule-track schedule-track-with-blocks">
                                                <?php foreach ($blocks as $j => $b): ?>
                                                    <?php
                                                    $colorIndex = $j % 4;
                                                    $reasonText = $b['reason'] ?: '(ไม่ระบุวัตถุประสงค์)';
                                                    $driverText = $b['driver'] ?: 'ยังไม่จัดสรร พขร.';
                                                    $blockUrl = Url::to([
                                                        '/booking/vehicle/view',
                                                        'id' => $b['vehicle_id'],
                                                        'title' => '<i class="fa-solid fa-car"></i> รายละเอียดการขอใช้ยานพาหนะ',
                                                    ]);
                                                    $tooltipParts = array_filter([
                                                        $b['label'],
                                                        $b['reason'] ?: null,
                                                        $b['location'] ?: null,
                                                        $b['driver'] ? 'พขร. ' . $b['driver'] : null,
                                                    ]);
                                                    $tooltip = implode(' · ', $tooltipParts);
                                                    ?>
                                                    <a href="<?= $blockUrl ?>"
                                                        data-size="modal-lg"
                                                        class="schedule-block schedule-block-<?= $colorIndex ?> open-modal"
                                                        style="left:<?= $b['left'] ?>%;width:<?= $b['width'] ?>%;"
                                                        title="<?= Html::encode($tooltip) ?>"
                                                        aria-label="ดูรายละเอียดการจอง <?= Html::encode($b['label']) ?> · <?= Html::encode($reasonText) ?> · พขร. <?= Html::encode($driverText) ?>">
                                                        <span class="schedule-block-time" aria-hidden="true"><?= Html::encode($b['label']) ?></span>
                                                        <span class="schedule-block-reason" aria-hidden="true"><?= Html::encode($reasonText) ?></span>
                                                        <span class="schedule-block-driver" aria-hidden="true">
                                                            <i class="fa-solid fa-user-tie"></i> <?= Html::encode($driverText) ?>
                                                        </span>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                            <ul class="schedule-block-summary" aria-label="รายการจอง">
                                                <?php foreach ($blocks as $j => $b): ?>
                                                    <?php
                                                    $colorIndex = $j % 4;
                                                    $reasonText = $b['reason'] ?: '(ไม่ระบุวัตถุประสงค์)';
                                                    $driverText = $b['driver'] ?: 'ยังไม่จัดสรร พขร.';
                                                    ?>
                                                    <li class="schedule-block-summary-item">
                                                        <span class="schedule-block-summary-dot schedule-block-<?= $colorIndex ?>" aria-hidden="true"></span>
                                                        <span class="schedule-block-summary-time"><?= Html::encode($b['label']) ?></span>
                                                        <span class="schedule-block-summary-reason text-truncate" title="<?= Html::encode($reasonText) ?>">
                                                            <?= Html::encode($reasonText) ?>
                                                        </span>
                                                        <span class="schedule-block-summary-driver text-truncate <?= empty($b['driver']) ? 'text-muted fst-italic' : '' ?>"
                                                            title="พขร. <?= Html::encode($driverText) ?>">
                                                            <i class="fa-solid fa-user-tie me-1" aria-hidden="true"></i><?= Html::encode($driverText) ?>
                                                        </span>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                        <div class="schedule-ticks" aria-hidden="true">
                                            <?php foreach ($ticks as $tick): ?>
                                                <span class="schedule-tick" style="left:<?= $tick['left'] ?>%;">
                                                    <?= $tick['label'] ?>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="schedule-col-status text-end">
                                    <span class="schedule-status schedule-status-<?= $statusKey ?>">
                                        <span class="schedule-status-dot" aria-hidden="true"></span>
                                        <?= Html::encode($statusLabel) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php

use app\modules\mobile\services\MobileBookingStatus;
use yii\bootstrap5\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var \app\modules\booking\models\Vehicle[] $myBookings */
/** @var callable $formatThaiDate Closure(?string $d): string */

$myBookings     = $myBookings ?? [];
$formatThaiDate = $formatThaiDate ?? static fn ($d) => (string) $d;
?>

<section class="bv-mode bv-mode-list" data-mode-section="list">

    <?php if (!empty($myBookings)): ?>
        <div class="bv-list-toolbar rounded-3 mx-3 mt-4 mb-0">
            <input type="search"
                   id="bv-list-search"
                   class="bv-search"
                   placeholder="ค้นหารหัส, สถานที่, วัตถุประสงค์"
                   autocomplete="off"
                   aria-label="ค้นหารายการคำขอ">
        </div>
    <?php endif; ?>

    <?php if (empty($myBookings)): ?>
        <div class="bv-list-empty">
            <span class="bv-list-empty-icon" aria-hidden="true">
                <i data-lucide="car" class="mi-xl"></i>
            </span>
            <p class="bv-list-empty-title">ยังไม่มีคำขอจองรถ</p>
            <p class="bv-list-empty-text">เริ่มคำขอแรกของคุณ เจ้าหน้าที่จะตรวจสอบและจัดสรรรถให้ตามเวลาที่ระบุ</p>
        </div>
    <?php else: ?>
        <div class="bv-list" id="bv-list">
            <?php foreach ($myBookings as $b):
                $info       = MobileBookingStatus::info((string) $b->status);
                $bucket     = $info['bucket'];
                $tone       = $info['tone'];
                $statusLbl  = $info['label'];

                $locTitle = '';
                try {
                    if ($b->locationOrg && !empty($b->locationOrg->title)) {
                        $locTitle = (string) $b->locationOrg->title;
                    }
                } catch (\Throwable $e) {
                    $locTitle = '';
                }
                if ($locTitle === '') $locTitle = (string) $b->location;

                $reasonTxt = trim((string) $b->reason);
                $title     = $locTitle !== ''
                    ? 'ไป ' . $locTitle
                    : ($reasonTxt !== '' ? $reasonTxt : 'คำขอจองรถ');
                $startThai = $formatThaiDate((string) $b->date_start);
                $endThai   = $formatThaiDate((string) $b->date_end);
                $datesTxt  = ($b->date_start === $b->date_end || !$b->date_end)
                    ? $startThai
                    : ($startThai . ' → ' . $endThai);
                $timeTxt   = trim(substr((string) $b->time_start, 0, 5));
                $isUrgent  = in_array((string) $b->urgent, ['ด่วน', 'ด่วนที่สุด'], true);

                $search = mb_strtolower(implode(' ', array_filter([
                    (string) $b->code,
                    $locTitle,
                    $reasonTxt,
                    $statusLbl,
                    $datesTxt,
                ])), 'UTF-8');
            ?>
                <a class="bv-list-card"
                   href="<?= Html::encode(Url::to(['/mobile/default/vehicle-view', 'id' => $b->id])) ?>"
                   data-status="<?= Html::encode($bucket) ?>"
                   data-search="<?= Html::encode($search) ?>">
                    <header class="bv-list-card-head">
                        <span class="bv-list-code"><?= Html::encode((string) $b->code) ?></span>
                        <span class="bv-list-pill is-<?= Html::encode($tone) ?>"><?= Html::encode($statusLbl) ?></span>
                    </header>
                    <h3 class="bv-list-title"><?= Html::encode($title) ?></h3>
                    <div class="bv-list-meta">
                        <span class="bv-list-meta-item">
                            <i data-lucide="calendar" aria-hidden="true"></i>
                            <?= Html::encode($datesTxt) ?>
                        </span>
                        <?php if ($timeTxt !== ''): ?>
                            <span class="bv-list-meta-item">
                                <i data-lucide="clock" aria-hidden="true"></i>
                                <?= Html::encode($timeTxt) ?> น.
                            </span>
                        <?php endif; ?>
                        <?php if ($isUrgent): ?>
                            <span class="bv-list-urgent"><?= Html::encode((string) $b->urgent) ?></span>
                        <?php endif; ?>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="bv-list-no-results" id="bv-list-no-results" role="status" hidden>
            ไม่พบรายการที่ตรงกับการค้นหา
        </p>
    <?php endif; ?>
</section>

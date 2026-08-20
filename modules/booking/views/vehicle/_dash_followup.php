<?php

use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\booking\models\Vehicle;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var array $summary */

$unlogged = (int) $summary['unlogged'];
$anomalies = (int) $summary['distance_anomalies'];
$trips = (int) $summary['trips'];
$logged = (int) $summary['logged'];
$loggedPct = $trips > 0 ? (int) round(($logged / $trips) * 100) : 0;

$workUrl = Url::to([
    '/booking/vehicle/work-official',
    'VehicleDetailSearch' => array_filter([
        'thai_year' => $searchModel->thai_year,
    ], static fn($v) => $v !== null && $v !== ''),
]);

$rows = [];
if ($unlogged > 0) {
    $rows[] = [
        'icon' => 'bi-journal-x',
        'tone' => 'warning',
        'title' => 'ยังไม่บันทึกการเดินทาง',
        'meta' => 'ไม่มีเลขไมล์ ระยะทาง หรือค่าน้ำมัน',
        'value' => number_format($unlogged) . ' รายการ',
        'url' => $workUrl,
    ];
}
if ($anomalies > 0) {
    $rows[] = [
        'icon' => 'bi-exclamation-triangle',
        'tone' => 'danger',
        'title' => 'เลขไมล์ผิดปกติ',
        'meta' => 'ระยะทางเกิน ' . number_format(Vehicle::DISTANCE_SANITY_MAX) . ' กม. ต่อวัน ไม่ถูกนำไปรวมยอด',
        'value' => number_format($anomalies) . ' รายการ',
        'url' => $workUrl,
    ];
}
?>

<section class="card border-0 shadow-sm vd-card" aria-labelledby="vd-followup-heading">
    <div class="card-header bg-primary-gradient text-white">
        <h4 id="vd-followup-heading" class="h6 text-white mb-0">
            <i class="bi bi-clipboard-check me-1" aria-hidden="true"></i>ความครบถ้วนของข้อมูล
            <span class="badge rounded-pill bg-white bg-opacity-25 text-white fw-medium ms-1 vd-num">
                <?= $loggedPct ?>%
            </span>
        </h4>
        <p class="small text-white-50 mb-0">
            บันทึกแล้ว <span class="vd-num"><?= number_format($logged) ?></span>
            จาก <span class="vd-num"><?= number_format($trips) ?></span> ภารกิจ
        </p>
    </div>

    <div class="card-body">
        <div class="progress mb-3" role="progressbar" aria-label="สัดส่วนภารกิจที่บันทึกข้อมูลแล้ว"
            aria-valuenow="<?= $loggedPct ?>" aria-valuemin="0" aria-valuemax="100" style="height:6px">
            <div class="progress-bar <?= $loggedPct >= 90 ? 'bg-success' : 'bg-warning' ?>" style="width:<?= $loggedPct ?>%"></div>
        </div>

        <?php if (empty($rows)): ?>
            <div class="text-center text-body-secondary py-3">
                <i class="bi bi-check2-all fs-4 d-block mb-1 text-success" aria-hidden="true"></i>
                <div class="small">ข้อมูลการเดินทางครบถ้วน ไม่มีรายการต้องตามแก้</div>
            </div>
        <?php else: ?>
            <div class="d-flex flex-column gap-1">
                <?php foreach ($rows as $row): ?>
                    <a class="vd-task" href="<?= $row['url'] ?>">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-<?= $row['tone'] ?>-subtle text-<?= $row['tone'] ?>-emphasis flex-shrink-0"
                            style="width:36px;height:36px" aria-hidden="true">
                            <i class="bi <?= $row['icon'] ?>"></i>
                        </span>
                        <span class="vd-task__body">
                            <span class="vd-task__title d-block"><?= Html::encode($row['title']) ?></span>
                            <span class="vd-task__meta d-block text-body-secondary"><?= Html::encode($row['meta']) ?></span>
                        </span>
                        <span class="fw-semibold text-<?= $row['tone'] ?>-emphasis vd-num flex-shrink-0"><?= $row['value'] ?></span>
                        <i class="bi bi-chevron-right text-body-tertiary flex-shrink-0" aria-hidden="true"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

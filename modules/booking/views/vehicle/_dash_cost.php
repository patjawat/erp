<?php

use yii\helpers\Json;
use app\modules\booking\models\Vehicle;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var array $summary */

$cost = $searchModel->monthlyCostSummary();
$oilTotal = (float) $summary['oil_price'];
$distanceTotal = (float) $summary['distance'];
$anomalies = (int) $summary['distance_anomalies'];
$costPerKm = $distanceTotal > 0 ? $oilTotal / $distanceTotal : 0;

$config = [
    'kind' => 'area',
    'height' => 200,
    'unit' => 'บาท',
    'digits' => 0,
    'colors' => ['--bs-primary'],
    'categories' => $cost['labels'],
    'series' => [
        ['name' => 'ค่าน้ำมัน', 'data' => array_map(static fn($v) => round($v, 2), $cost['oil_price'])],
    ],
];
?>

<section class="card border-0 shadow-sm vd-card" aria-labelledby="vd-cost-heading">
    <div class="card-header bg-primary-gradient text-white">
        <h4 id="vd-cost-heading" class="h6 text-white mb-0">
            <i class="bi bi-fuel-pump me-1" aria-hidden="true"></i>ค่าน้ำมันและระยะทาง
        </h4>
        <p class="small text-white-50 mb-0">รวมทั้งปีงบประมาณ</p>
    </div>

    <div class="card-body">
        <div class="vd-metric">
            <span class="text-body-secondary small">ค่าน้ำมันรวม</span>
            <span class="vd-metric__value"><?= number_format($oilTotal) ?> <span class="fs-6 fw-normal text-body-secondary">บาท</span></span>
        </div>
        <div class="vd-metric">
            <span class="text-body-secondary small">ระยะทางรวม</span>
            <span class="vd-metric__value"><?= number_format($distanceTotal) ?> <span class="fs-6 fw-normal text-body-secondary">กม.</span></span>
        </div>
        <div class="vd-metric">
            <span class="text-body-secondary small">เฉลี่ยต่อกิโลเมตร</span>
            <span class="vd-metric__value">
                <?= $costPerKm > 0 ? number_format($costPerKm, 2) : '-' ?>
                <span class="fs-6 fw-normal text-body-secondary">บาท</span>
            </span>
        </div>

        <?php if ($anomalies > 0): ?>
            <p class="small text-body-tertiary mt-2 mb-0">
                ไม่รวม <span class="vd-num"><?= number_format($anomalies) ?></span> รายการที่ระยะทางเกิน
                <span class="vd-num"><?= number_format(Vehicle::DISTANCE_SANITY_MAX) ?></span> กม. ต่อวัน
            </p>
        <?php endif; ?>

        <div class="mt-2" data-vehicle-chart>
            <script type="application/json"><?= Json::encode($config) ?></script>
            <div class="vd-chart__canvas" data-vehicle-chart-canvas></div>
            <div class="vd-chart__empty text-body-secondary d-none" data-vehicle-chart-empty>
                <i class="bi bi-cash-coin fs-4" aria-hidden="true"></i>
                <span class="small">ยังไม่มีการบันทึกค่าน้ำมัน</span>
            </div>
        </div>
    </div>
</section>

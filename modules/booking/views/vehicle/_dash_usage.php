<?php

use yii\helpers\Json;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */

$usage = $searchModel->monthlyUsageSummary();

$generalTotal = array_sum($usage['official']) + array_sum($usage['personal']);
$ambulanceTotal = array_sum($usage['ambulance']);

$config = [
    'kind' => 'bar',
    'stacked' => true,
    'height' => 320,
    'unit' => 'ครั้ง',
    'categories' => $usage['labels'],
    'defaultGroup' => 'general',
    'groups' => [
        'general' => [
            'colors' => ['--bs-primary', '--bs-cyan'],
            'series' => [
                ['name' => 'รถยนต์ราชการ', 'data' => $usage['official']],
                ['name' => 'รถยนต์ส่วนตัว', 'data' => $usage['personal']],
            ],
        ],
        'ambulance' => [
            'colors' => ['--bs-orange', '--bs-teal', '--bs-indigo', '--bs-gray-500'],
            'series' => array_values(array_filter([
                ['name' => 'ส่งต่อ (REFER)', 'data' => $usage['refer']],
                ['name' => 'EMS', 'data' => $usage['ems']],
                ['name' => 'รับ-ส่ง ไม่ฉุกเฉิน', 'data' => $usage['normal']],
                array_sum($usage['other']) > 0
                    ? ['name' => 'ไม่ระบุประเภท', 'data' => $usage['other']]
                    : null,
            ])),
        ],
    ],
];

$tabs = [
    'general' => 'รถทั่วไป · ' . number_format($generalTotal) . ' ครั้ง',
    'ambulance' => 'รถฉุกเฉิน · ' . number_format($ambulanceTotal) . ' ครั้ง',
];
?>

<section class="card border-0 shadow-sm vd-card" aria-labelledby="vd-usage-heading">
    <div class="card-header bg-primary-gradient text-white">
        <h4 id="vd-usage-heading" class="h6 text-white mb-0">
            <i class="bi bi-bar-chart-line me-1" aria-hidden="true"></i>การใช้รถรายเดือน
        </h4>
        <p class="small text-white-50 mb-0">นับตามการจัดสรรรายวัน เรียงตามปีงบประมาณ ต.ค. ถึง ก.ย.</p>
    </div>

    <div class="card-body">
        <div class="btn-group btn-group-sm mb-2" role="tablist" aria-label="เลือกประเภทรถที่จะแสดง">
            <?php foreach ($tabs as $key => $label): ?>
                <button type="button" role="tab"
                    class="btn btn-outline-secondary<?= $key === 'general' ? ' active' : '' ?>"
                    data-vehicle-chart-group="<?= $key ?>"
                    aria-selected="<?= $key === 'general' ? 'true' : 'false' ?>">
                    <?= Html::encode($label) ?>
                </button>
            <?php endforeach; ?>
        </div>

        <div data-vehicle-chart>
            <script type="application/json"><?= Json::encode($config) ?></script>
            <div class="vd-chart__canvas" data-vehicle-chart-canvas></div>
            <div class="vd-chart__empty text-body-secondary d-none" data-vehicle-chart-empty>
                <i class="bi bi-bar-chart fs-3" aria-hidden="true"></i>
                <span class="small">ไม่มีการใช้รถในปีงบประมาณที่เลือก</span>
            </div>
        </div>
    </div>
</section>

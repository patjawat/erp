<?php

use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Pjax;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */

$this->title = 'ภาพรวมยานพาหนะ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/vehicle-dashboard.css');
$this->registerJsFile('@web/js/vehicle-dashboard.js', ['depends' => \app\assets\AppAsset::class, 'position' => View::POS_END]);

$summary = $searchModel->dashboardSummary();
$year = $searchModel->thai_year;

// map ข้อมูลอันดับให้อยู่รูปเดียวกันก่อนส่งเข้า _dash_rank
$departmentItems = array_map(static fn($row) => [
    'label' => (string) $row['name'],
    'value' => (int) $row['total'],
], $searchModel->departmentSummary(8));

$carItems = array_map(static fn($row) => [
    'label' => (string) $row['license_plate'],
    'value' => (int) $row['total'],
], $searchModel->carSummary(8));

$driverItems = array_map(static function ($row) use ($year) {
    $name = trim((string) ($row['fullname'] ?? ''));
    $name = $name !== '' ? $name : 'ไม่ระบุชื่อ (#' . $row['driver_id'] . ')';

    return [
        'label' => $name,
        'value' => (int) $row['total'],
        'modal' => true,
        'url' => Url::to([
            '/booking/vehicle/driver-work',
            'driver_id' => $row['driver_id'],
            'thai_year' => $year,
        ]),
        'aria' => 'ดูรายการภารกิจของ ' . $name . ' ' . number_format((int) $row['total']) . ' งาน',
    ];
}, $searchModel->driverSummary(8));
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-speedometer2" aria-hidden="true"></i> <?= $this->title ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
สรุปคำขอ การใช้งาน และคุณภาพบริการยานพาหนะ
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/booking/vehicle_menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'vd-container', 'timeout' => 500000, 'enablePushState' => true]); ?>
<div class="vd-page">

    <?= $this->render('_dash_toolbar', ['searchModel' => $searchModel]) ?>

    <!-- โซน 1 · สถานะคำขอ ตอบคำถามแรกว่าตอนนี้งานอยู่ตรงไหน -->
    <?= $this->render('_dash_status', ['searchModel' => $searchModel, 'summary' => $summary]) ?>

    <!-- โซน 2 · สิ่งที่ต้องดำเนินการ -->
    <h3 class="h6 text-body-secondary mb-2">ต้องดำเนินการ</h3>
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-6">
            <?= $this->render('_dash_pending', [
                'searchModel' => $searchModel,
                'pendingTotal' => $summary['pending'],
            ]) ?>
        </div>
        <div class="col-12 col-xl-6">
            <div class="d-flex flex-column gap-3">
                <?= $this->render('_expiring_insurance') ?>
                <?= $this->render('_dash_followup', ['searchModel' => $searchModel, 'summary' => $summary]) ?>
            </div>
        </div>
    </div>

    <!-- โซน 3 · แนวโน้มการใช้งานและค่าใช้จ่าย -->
    <h3 class="h6 text-body-secondary mb-2">แนวโน้มการใช้งาน</h3>
    <div class="row g-3 mb-4">
        <div class="col-12 col-xl-8">
            <?= $this->render('_dash_usage', ['searchModel' => $searchModel]) ?>
        </div>
        <div class="col-12 col-xl-4">
            <?= $this->render('_dash_cost', ['searchModel' => $searchModel, 'summary' => $summary]) ?>
        </div>
    </div>

    <!-- โซน 4 · ใครใช้ ใช้รถคันไหน และบริการเป็นอย่างไร -->
    <h3 class="h6 text-body-secondary mb-2">การใช้ทรัพยากรและคุณภาพบริการ</h3>
    <div class="row g-3">
        <div class="col-12 col-lg-6 col-xl-4">
            <?= $this->render('_dash_rank', [
                'id' => 'vd-department',
                'icon' => 'bi-building',
                'title' => 'หน่วยงานที่ขอใช้รถ',
                'note' => 'สูงสุด 8 อันดับแรก',
                'unit' => 'คำขอ',
                'items' => $departmentItems,
                'emptyText' => 'ยังไม่มีคำขอที่ผ่านการอนุมัติในปีนี้',
            ]) ?>
        </div>
        <div class="col-12 col-lg-6 col-xl-4">
            <?= $this->render('_dash_rank', [
                'id' => 'vd-car',
                'icon' => 'bi-car-front',
                'title' => 'รถที่ถูกขอใช้บ่อย',
                'note' => 'สูงสุด 8 อันดับแรก',
                'unit' => 'คำขอ',
                'items' => $carItems,
                'emptyText' => 'ยังไม่มีการระบุทะเบียนรถในปีนี้',
            ]) ?>
        </div>
        <div class="col-12 col-lg-6 col-xl-4">
            <?= $this->render('view_rating', ['searchModel' => $searchModel]) ?>
        </div>
        <div class="col-12 col-lg-6 col-xl-6">
            <?= $this->render('_dash_rank', [
                'id' => 'vd-driver',
                'icon' => 'bi-person-badge',
                'title' => 'ปริมาณงานพนักงานขับรถ',
                'note' => 'นับตามการจัดสรรรายวัน · กดเพื่อดูรายการภารกิจ',
                'unit' => 'งาน',
                'items' => $driverItems,
                'emptyText' => 'ยังไม่มีการจัดสรรพนักงานขับรถในปีนี้',
            ]) ?>
        </div>
        <div class="col-12 col-xl-6">
            <?= $this->render('_dash_feedback', ['searchModel' => $searchModel]) ?>
        </div>
    </div>

</div>
<?php Pjax::end(); ?>

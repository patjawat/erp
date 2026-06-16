<?php

/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCssFile('@web/css/vehicle-dashboard.css');
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-gauge-high fs-x1" aria-hidden="true"></i> <?= $this->title ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
Dashboard
<?php $this->endBlock(); ?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/booking/vehicle_menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>

<div class="row g-3" role="region" aria-label="แดชบอร์ดยานพาหนะ">
    <div class="col-12 col-lg-6">
        <?= $this->render('chart_drivers', ['searchModel' => $searchModel]) ?>
    </div>
    <div class="col-12 col-lg-6">
        <?= $this->render('_expiring_insurance') ?>
    </div>
    <div class="col-12 col-lg-6">
        <?= $this->render('chart_general_type', ['searchModel' => $searchModel]) ?>
        <?= $this->render('chart_ambulance_type', ['searchModel' => $searchModel]) ?>
        <?= $this->render('chart_car', ['searchModel' => $searchModel]) ?>
    </div>
    <div class="col-12 col-lg-6">
        <?= $this->render('chart_department', ['searchModel' => $searchModel]) ?>
        <?= $this->render('chart_price_summary', ['searchModel' => $searchModel]) ?>
    </div>
</div>


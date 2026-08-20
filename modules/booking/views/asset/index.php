<?php

use yii\helpers\Html;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ทะเบียนการขอใช้รถยนต์';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
    <i class="fa-solid fa-car fs-x1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>


<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/booking/vehicle_menu',['active' => 'asset']) ?>
<?php $this->endBlock(); ?>


<?= $this->render('_driver_vehicles', [
    'driverGroups' => $driverGroups,
    'idleDrivers' => $idleDrivers,
]) ?>

<div class="card">
    <div class="card-header bg-primary-gradient text-white">
        <h6 class="text-white mt-2"><i class="fa-solid fa-magnifying-glass"></i> การค้นหา</h6>
    </div>
    <div class="card-body">
        <?=$this->render('@app/modules/helpdesk2/views/service/_search_asset', ['model' => $searchModel,'listAssetType'=> $listAssetType])?>
    </div>
</div>

<div class="card">
        <div class="card-header bg-primary-gradient text-white">
        <div class="d-flex justify-content-between">
            <h6 class="text-white mt-2">
                <i class="bi bi-ui-checks"></i> <?=$this->title?>
                <span class="badge text-bg-light">
                    <?php echo number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
            </h6>
            <div class="d-flex justify-content-between gap-3">

            </div>
        </div>
    </div>
    <div class="card-body">

<?=$this->render('@app/modules/am/views/equip/_list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);?>

    </div>
</div>
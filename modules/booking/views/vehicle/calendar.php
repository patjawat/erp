<?php
use yii\helpers\Html;
use app\models\Categorise;

$this->title = 'ปฏิทินการใช้รถ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
$vehicleStatus = Categorise::find()->where(['name' => 'vehicle_status'])->all();
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-calendar fx-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
ปฏิทินการใช้รถยนต์
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?= $this->render('menu', ['active' => 'calendar']) ?>
<?php $this->endBlock(); ?>

<style>
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

</style>

<div class="row">
    <div class="col-lg-9 col-md-12 col-sm-12" id="calender-container">
       
        <?=$this->render('carlendar_item',['vehicle_type' => $vehicle_type])?>
    </div>
    <div class="col-lg-3 col-md-12 col-sm-12" id="manual-container">

        <div class="card">
            <div class="card-header  bg-primary-gradient">
                <div class="d-flex justify-content-between align-items-center align-self-center">
                    <h5 class="mb-0 text-white"><i class="fa-solid fa-book"></i> สถานะการขอใช้รถ</h5>
                    <?= html::a('<i class="fa-solid fa-gear"></i>', ['/booking/vehicle-status/index'], ['class' => 'btn btn-sm btn-light open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex flex-column gap-2 mb-3">
                    <?php foreach ($vehicleStatus as $_vehicleStatus): ?>
                        <div class="d-flex justify-content-between">
                            <div>
                                <span class="status-indicator <?= $_vehicleStatus->code ?>" style="background-color:<?= $_vehicleStatus->data_json['color'] ?? 'var(--bs-primary)' ?>"></span><?= $_vehicleStatus->title ?>
                            </div>
                            <div>
                                <span class="badge text-bg-light status_summary" id="status<?= $_vehicleStatus->code ?>">0</span>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
</div>

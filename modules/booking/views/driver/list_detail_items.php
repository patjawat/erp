<?php
use yii\helpers\Url;
use yii\helpers\Html;
use app\modules\am\models\Asset;
use app\modules\hr\models\Employees;
$listCars = Asset::find()
->andWhere(['IS NOT', 'license_plate', null])
// ->andWhere(['car_type' => $model->car_type])
->all();
?>
<?php if($type=='car'):?>
<div id="car-container">
    <h5>รายการรถยนต์</h5>
<?php foreach($listCars as $item):?>
        <a href="<?php echo Url::to(['/booking/driver/list-detail-items','id' => $model->id,'license_plate' => $item->license_plate,'type' => 'car'])?>" class="select-item"
        data-id="<?php echo $item->id?>" 
        data-type="car-<?php echo $model->id?>"
        data-booking_id="<?php echo $model->booking_id?>"
        data-license_plate="<?php echo $item->license_plate?>"
        data-driver_id="<?php echo $model->driver_id?>"
        >
        <div class="card mb-3 hover-card">
            <div class="row g-0">
                <div class="col-md-3">
                        <?php  echo  Html::img($item->showImg(),['class' => 'img-fluid rounded'])?>
                </div>
                <div class="col-md-9">
                <div class="card-body">
                    <h5 class="card-title"><?php  echo $item->license_plate?></h5>
                    <p class="card-text"><small class="text-muted">จำนวนที่นั่ง <?php echo $item->data_json['seat_size'] ?? '-'?></small></p>
                </div>
                </div>
            </div>
            </div>
        </a>
    <?php endforeach;?>

    </div>
    <?php endif?>

    <?php if($type=='driver'):?>
    <?php

$listDrivers = Employees::find()
                ->from('employees e')
                ->leftJoin('auth_assignment a', 'e.user_id = a.user_id')
                ->where(['a.item_name' => 'driver'])
                ->all();
?>
<h5>รายการพนักงานขับ</h5>
<div class="d-flex flex-column">
    <?php foreach($listDrivers as $item):?>
        <a href="<?php echo Url::to(['/booking/driver/list-detail-items','id' => $model->id,'license_plate' => $model->license_plate,'type' => 'driver'])?>" class="select-item"
        data-id="<?php echo $item->id?>" 
        data-type="driver-<?php echo $model->id?>"
        data-booking_id="<?php echo $model->booking_id?>"
        data-license_plate="<?php echo $model->license_plate?>"
        data-driver_id="<?php echo $item->id?>"
        >
        <div class="card mb-1 hover-card">
            <div class="card-body">
                <div class="d-flex">
                    <?php echo Html::img($item->ShowAvatar(),['class' => 'avatar'])?>
                    <div class="avatar-detail">
                        <h6 class="mb-1 fs-15"><?php echo $item->fullname?>
                        </h6>
                        <p class="text-muted mb-0 fs-13"><?php echo $item->positionName()?></p>
                    </div>
                </div>

            </div>
        </div>

    </a>
    <?php endforeach;?>
</div>
<?php endif?>
    
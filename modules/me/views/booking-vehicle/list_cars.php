<?php
use yii\helpers\Html;
use app\modules\am\models\Asset;
// $listCars = Asset::find()
// ->andWhere(['IS NOT', 'license_plate', null])
// ->andWhere(['car_type' => $car_type_id])
// ->all();

?>
<h6>เลือกรถหากต้องการ</h6>
<div id="car-container">
<?php foreach($dataProvider->getModels() as $item):?>
        <a href="#" data-license_plate="<?php  echo $item->license_plate?>" class="select-car">
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

    
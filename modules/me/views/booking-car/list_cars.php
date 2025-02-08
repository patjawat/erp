<?php
use yii\helpers\Html;
use app\modules\am\models\Asset;
$listCars = Asset::find()
->andWhere(['IS NOT', 'license_plate', null])
->andWhere(['car_type' => $model->car_type])
->all();
?>


<div class="row row-cols-1 row-cols-sm-3 row-cols-md-2 g-3 mt-1" id="car-container">
<?php foreach($listCars as $item):?>
    <div class="col mt-1">
        <a href="#" data-license_plate="<?php  echo $item->license_plate?>" class="select-car">
            <div class="card  position-relative shadow-sm bg-light">
                <div class="d-flex justify-content-center align-items-center opacity-75 p-3 rounded-top">
                    <?php  echo  Html::img($item->showImg(),['class' => 'rounded-3','style' => 'max-width: 130px;max-height: 104px;min-width: 130px;min-height: 104px;'])?>
                </div>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill checked">
                    <!-- <i class="fa-solid fa-circle-check text-success fs-4"></i> -->
                </span>
                <div class="card-body p-1">

                    <p class="text-center fw-semibold mb-0"><?php  echo $item->license_plate?></p>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach;?>
</div>

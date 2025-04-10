<?php
use yii\helpers\Html;
use app\modules\am\models\Asset;
$listCars = Asset::find()
->andWhere(['IS NOT', 'license_plate', null])
// ->andWhere(['car_type' => $model->car_type])
->all();
?>

<div class="row row-cols-1 row-cols-sm-4 row-cols-md-4 g-3 mt-2">
<?php foreach($listCars as $item):?>
    <div class="col mt-1">
        <a href="/me/repair">
            <div class="card border-0 shadow-sm hover-card">
                <div class="d-flex justify-content-center align-items-center p-0 rounded-top">
                <?php // echo Html::img($item->showImg(),['class' => 'rounded-3','style' => 'max-width: 200px;max-height: 200px;'])?>
                <?php echo Html::img($item->showImg(),['class' => 'rounded-3','style' => 'max-width: 200px;max-height: 180px;min-height: 180px;'])?>
                </div>
                <div class="card-body p-1">

                    <p class="text-center fw-semibold mb-0">แจ้งซ่อม</p>
                </div>
            </div>
        </a>
    </div>
    <?php endforeach;?>

</div>

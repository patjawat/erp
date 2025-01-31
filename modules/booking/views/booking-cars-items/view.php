<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCarsItems $model */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="booking-cars-items-view">
<div class="row">
    <div class="col-2">
        <?php echo $model->AvatarXl()?>
    </div>
    <div class="col-10">
        <div class="card">
            <div class="card-body">
            <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
            <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'car_type',
            'asset_item_id',
            'license_plate',
            'active',
        ],
    ]) ?>
            
            </div>
        </div>
        
    </div>
</div>



   

</div>

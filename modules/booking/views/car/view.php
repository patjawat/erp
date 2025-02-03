<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Booking $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Bookings', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="booking-view">

    <h1><?= Html::encode($this->title) ?></h1>

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
            'id',
            'ref',
            'name',
            'thai_year',
            'car_type',
            'document_id',
            'urgent',
            'license_plate',
            'conference_room_id',
            'location',
            'reason',
            'status',
            'date_start',
            'time_start',
            'date_end',
            'time_end',
            'driver_id',
            'leader_id',
            'data_json',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
            'deleted_at',
            'deleted_by',
        ],
    ]) ?>

</div>

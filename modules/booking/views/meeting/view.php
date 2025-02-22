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
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'reason',
            'thai_year',
            'car_type',
            'document_id',
            'urgent',
            'license_plate',
            'room_id',
            'location'
        ],
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCarItems $model */

$this->title = 'Update Booking Car Items: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Booking Car Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="booking-car-items-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

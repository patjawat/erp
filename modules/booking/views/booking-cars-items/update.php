<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCarsItems $model */

$this->title = 'Update Booking Cars Items: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="booking-cars-items-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

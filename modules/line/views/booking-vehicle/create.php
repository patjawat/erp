<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCar $model */

$this->title = 'Create Booking Car';
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="booking-car-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

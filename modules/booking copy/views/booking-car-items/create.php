<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCarItems $model */

$this->title = 'Create Booking Car Items';
$this->params['breadcrumbs'][] = ['label' => 'Booking Car Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="booking-car-items-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

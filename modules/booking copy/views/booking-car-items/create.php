<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCarsItems $model */

$this->title = 'Create Booking Cars Items';
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="booking-cars-items-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

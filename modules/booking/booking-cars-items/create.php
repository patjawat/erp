<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingCarsItem $model */

$this->title = 'Create Booking Cars Item';
$this->params['breadcrumbs'][] = ['label' => 'Booking Cars Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="booking-cars-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

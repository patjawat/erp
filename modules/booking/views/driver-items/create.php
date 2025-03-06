<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingDetail $model */

$this->title = 'Create Booking Detail';
$this->params['breadcrumbs'][] = ['label' => 'Booking Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="booking-detail-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

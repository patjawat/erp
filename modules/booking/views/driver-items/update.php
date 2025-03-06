<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\BookingDetail $model */

$this->title = 'Update Booking Detail: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Booking Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="booking-detail-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

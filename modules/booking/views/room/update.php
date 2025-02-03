<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Room $model */

$this->title = 'Update Room: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Rooms', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="room-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

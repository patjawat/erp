<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\RoomDevice $model */

$this->title = 'Create Room Device';
$this->params['breadcrumbs'][] = ['label' => 'Room Devices', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="room-device-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

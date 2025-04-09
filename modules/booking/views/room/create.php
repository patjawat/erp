<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\Room $model */

$this->title = 'Create Room';
$this->params['breadcrumbs'][] = ['label' => 'Rooms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="room-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

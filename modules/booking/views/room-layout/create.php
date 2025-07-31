<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\booking\models\RoomType $model */

$this->title = 'Create Room Type';
$this->params['breadcrumbs'][] = ['label' => 'Room Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="room-type-create">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

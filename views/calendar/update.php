<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Calendar $model */

$this->title = 'Update Calendar: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Calendars', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="calendar-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

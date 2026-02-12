<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\health\models\HealthScreen $model */

$this->title = 'Update Health Screen: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Health Screens', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="health-screen-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

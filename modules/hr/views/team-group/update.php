<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\TeamGroup $model */

$this->title = 'Update Team Group: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Team Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="team-group-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

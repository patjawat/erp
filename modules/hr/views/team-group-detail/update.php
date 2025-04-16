<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\TeamGroupDetail $model */

$this->title = 'Update Team Group Detail: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Team Group Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="team-group-detail-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

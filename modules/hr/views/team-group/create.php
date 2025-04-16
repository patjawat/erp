<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\TeamGroup $model */

$this->title = 'Create Team Group';
$this->params['breadcrumbs'][] = ['label' => 'Team Groups', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="team-group-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

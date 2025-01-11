<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\Projects $model */

$this->title = 'Update Projects: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Projects', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="projects-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

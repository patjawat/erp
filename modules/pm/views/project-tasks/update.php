<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\ProjectTasks $model */

$this->title = 'Update Project Tasks: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Project Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="project-tasks-update">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\ProjectTasks $model */

$this->title = 'Create Project Tasks';
$this->params['breadcrumbs'][] = ['label' => 'Project Tasks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-tasks-create">
    
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

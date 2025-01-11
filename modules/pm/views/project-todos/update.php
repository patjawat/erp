<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\ProjectTodos $model */

$this->title = 'Update Project Todos: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Project Todos', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="project-todos-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

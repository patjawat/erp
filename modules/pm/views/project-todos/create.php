<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\pm\models\ProjectTodos $model */

$this->title = 'Create Project Todos';
$this->params['breadcrumbs'][] = ['label' => 'Project Todos', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="project-todos-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

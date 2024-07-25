<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\Categorise $model */

$this->title = 'Create Categorise';
$this->params['breadcrumbs'][] = ['label' => 'Categorises', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="categorise-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

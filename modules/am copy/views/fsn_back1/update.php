<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Fsn $model */

$this->title = 'Update Fsn: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Fsns', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="fsn-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

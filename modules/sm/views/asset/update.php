<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'Update Asset: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="asset-update">

<div class="card">
    <div class="card-body d-flex ">
        <h1 class="card-title"><?= Html::encode($this->title) ?></h1>

    </div>
</div>

    <?= $this->render('_form', [
        'model' => $model,
        'ref' => $ref
    ]) ?>

</div>

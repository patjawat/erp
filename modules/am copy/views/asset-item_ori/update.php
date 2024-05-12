<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Assetitem $model */

$this->title = 'Update Asset Item: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Asset Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="asset-item-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

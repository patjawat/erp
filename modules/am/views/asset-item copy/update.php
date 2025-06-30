<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItem $model */

$this->title = 'Update Asset Item: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Asset Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="asset-item-update">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

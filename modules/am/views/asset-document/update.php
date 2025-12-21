<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */

$this->title = 'Update Asset Detail: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Asset Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="asset-detail-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetType $model */

$this->title = 'Update Asset Type: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Asset Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="asset-type-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

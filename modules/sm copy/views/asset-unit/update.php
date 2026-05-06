<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetUnit $model */

$this->title = 'Update Asset Unit: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Asset Units', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="asset-unit-update">

    <?= $this->render('_form_unit', [
        'model' => $model,
    ]) ?>

</div>

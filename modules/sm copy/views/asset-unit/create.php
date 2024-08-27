<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetUnit $model */

$this->title = 'Create Asset Unit';
$this->params['breadcrumbs'][] = ['label' => 'Asset Units', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="asset-unit-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

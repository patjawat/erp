<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\AssetType $model */
$this->title = 'Create Asset Type';
$this->params['breadcrumbs'][] = ['label' => 'Asset Types', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="asset-type-create">

    <?= $this->render('_form', [
        'model' => $model,
        'ref' => $ref
    ]) ?>

</div>

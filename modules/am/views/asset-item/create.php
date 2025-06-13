<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItem $model */

$this->title = 'Create Asset Item';
$this->params['breadcrumbs'][] = ['label' => 'Asset Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="asset-item-create">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\Asset $model */

$this->title = 'Create Asset';
$this->params['breadcrumbs'][] = ['label' => 'Assets', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="asset-create">

    
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

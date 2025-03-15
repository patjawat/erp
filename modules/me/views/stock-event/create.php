<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StockEvent $model */

$this->title = 'Create Stock Event';
$this->params['breadcrumbs'][] = ['label' => 'Stock Events', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="stock-event-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

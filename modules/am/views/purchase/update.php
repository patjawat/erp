<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListPurchase $model */

$this->title = 'แก้ไขวิธีการได้มา: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'วิธีการได้มา', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="list-purchase-update">
    <h4 class="mb-3"><?= Html::encode($this->title) ?></h4>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>

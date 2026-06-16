<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListMethodget $model */

$this->title = 'แก้ไขวิธีได้มา: ' . $model->title;
$this->params['breadcrumbs'][] = ['label' => 'วิธีได้มา', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'แก้ไข';
?>
<div class="list-methodget-update">
    <h4 class="mb-3"><?= Html::encode($this->title) ?></h4>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>

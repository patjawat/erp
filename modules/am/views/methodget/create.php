<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListMethodget $model */

$this->title = 'เพิ่มวิธีได้มา';
$this->params['breadcrumbs'][] = ['label' => 'วิธีได้มา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-methodget-create">
    <h4 class="mb-3"><?= Html::encode($this->title) ?></h4>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>

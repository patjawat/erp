<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListPurchase $model */

$this->title = 'เพิ่มวิธีการได้มา';
$this->params['breadcrumbs'][] = ['label' => 'วิธีการได้มา', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-purchase-create">
    <h4 class="mb-3"><?= Html::encode($this->title) ?></h4>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>

<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\ListBudgetdetail $model */

$this->title = 'เพิ่มประเภทเงิน';
$this->params['breadcrumbs'][] = ['label' => 'ประเภทเงิน', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="list-budgetdetail-create">
    <h4 class="mb-3"><?= Html::encode($this->title) ?></h4>
    <?= $this->render('_form', ['model' => $model]) ?>
</div>

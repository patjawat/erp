<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\sm\models\Order $model */
$this->title = 'สร้างใบสั่งซื้อสินค้า';
$this->params['breadcrumbs'][] = ['label' => 'Orders', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>


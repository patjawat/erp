<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItem $model */

$this->title = 'แก้ไขข้อมูลที่ดิน';
$this->params['breadcrumbs'][] = ['label' => 'Asset Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="asset-item-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

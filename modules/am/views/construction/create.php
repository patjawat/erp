<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItem $model */

$this->title = 'ฟอร์มบันทึกข้อมูลที่ดิน';
$this->params['breadcrumbs'][] = ['label' => 'Asset Items', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<div class="asset-item-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

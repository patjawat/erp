<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetItem $model */


$this->title = 'ฟอร์มบันทึกข้อมูลที่ดิน';
$this->params['breadcrumbs'][] = ['label' => 'ระบบบริหารทรัพย์สิน', 'url' => ['/am']];
$this->params['breadcrumbs'][] = ['label' => 'ที่ดิน', 'url' => ['/am/land']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="asset-item-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

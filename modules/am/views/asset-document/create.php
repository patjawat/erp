<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */

$this->title = 'สร้างเอกสารที่เกี่ยวข้อง';
$this->params['breadcrumbs'][] = ['label' => 'Asset Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="asset-detail-create">
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

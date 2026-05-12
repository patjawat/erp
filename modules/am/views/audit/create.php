<?php

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetAudit $model */

$this->title = 'สร้างใบตรวจนับครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ตรวจนับพัสดุประจำปี', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="audit-create">
    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
        'conditionOptions' => $conditionOptions,
        'statusOptions' => $statusOptions,
    ]) ?>
</div>

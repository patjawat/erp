<?php

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetAudit $model */

$this->title = 'แก้ไขใบตรวจนับครุภัณฑ์';
$this->params['breadcrumbs'][] = ['label' => 'ตรวจนับพัสดุประจำปี', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->audit_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="audit-update">
    <?= $this->render('_form', [
        'model' => $model,
        'items' => $items,
        'conditionOptions' => $conditionOptions,
        'statusOptions' => $statusOptions,
    ]) ?>
</div>

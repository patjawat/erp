<?php

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDisposal $model */
/** @var app\modules\am\models\AssetDisposalItem[] $items */

$this->title = 'แก้ไขใบขอจำหน่าย';
$this->params['breadcrumbs'][] = ['label' => 'ใบขอจำหน่ายครุภัณฑ์', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->disposal_no, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_form', [
    'model' => $model,
    'items' => $items,
    'conditionOptions' => $conditionOptions,
    'departmentOptions' => $departmentOptions,
    'assetTypeOptions' => $assetTypeOptions,
    'statusOptions' => $statusOptions,
]);

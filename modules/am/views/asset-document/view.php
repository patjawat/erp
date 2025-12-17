<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */

$this->title = $model->data_json['title'] ?? 'เอกสารที่เกี่ยวข้อง';
$this->params['breadcrumbs'][] = ['label' => 'Asset Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="asset-detail-view">
    
    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'ชื่อเอกสาร',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->data_json['title'] ?? '-';
                },
            ],
            [
                'label' => 'ประเภท',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->data_json['asset_document_type'] ?? '-';
                },
            ],
            [
                'label' => 'ผู้อัปโหลด',
                'format' => 'raw',
                'value' => function ($model) {
                    return $model->createdBy?->employee?->fullname ?? '-';
                },
            ],
        ],
    ]) ?>

</div>

<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Asset Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
$maintenanceData = static function ($model): array {
    if (is_array($model->data_json)) {
        return $model->data_json;
    }

    if (is_string($model->data_json)) {
        return json_decode($model->data_json, true) ?: [];
    }

    return [];
};
$operatorName = static function ($model): string {
    $user = $model->createdBy;

    return $user?->employee?->fullname
        ?? $user?->username
        ?? '-';
};
?>


    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'label' => 'หมายเลขทรัพย์สิน/ครุภัณฑ์',
                'value' => function($model){
                    return $model->code;
                }
            ],
            [
                'label' => 'หน่วยงานที่รับผิดชอบ',
                'value' => function($model){
                    return $model->asset?->departmentName();
                }
            ],
           
             [
                'label' => 'วันที่ตามแผน',
                'value' => function($model){
                    return Yii::$app->thaiDate->toThaiDate($model->date_start, true, false);
                }
            ],
            [
                'label' => 'วันที่ดำเนินการ',
                'value' => function($model){
                    return Yii::$app->thaiDate->toThaiDate($model->date_end, true, false);
                }
            ],
             [
                'label' => 'รายละเอียด/หมายเหตุ',
                'value' => function($model) use ($maintenanceData) {
                    $data = $maintenanceData($model);
                    $remark = trim((string) ($data['remark'] ?? ''));

                    return $remark !== '' ? $remark : '-';
                }
            ],
            [
                'label' => 'ผู้ดำเนินการ',
                'value' => function($model) use ($operatorName) {
                    return $operatorName($model);
                }
            ],
           
        ],
    ]) ?>

     <label class="form-label fw-bold">รูปภาพหรือไฟล์ที่เกี่ยวข้อง</label>

 <?= $model->Upload(['view' => true]) ?>

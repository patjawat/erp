<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var app\modules\am\models\AssetDetail $model */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Asset Details', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
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
                'label' => 'ผู้ให้บริการสอบเทียบ ',
                'value' => function($model){
                    return $model->provider_type == 'external' ? 'หน่วยงานภายนอก (Outsource)' : 'ดำเนินการเอง (In-house)';
                }
            ],
             [
                'label' => 'ผลการตรวจสอล',
                'format' => 'raw',
                'value' => function($model){
                    return $model->viewResult();
                },
            ],
             [
                'label' => 'รายละเอียด/หมายเหตุ',
                'value' => function($model){
                    return $model->data_json['remark'] ?? '-';
                }
            ],
            [
                'label' => 'ผู้ดำเนินการ',
                'value' => function($model){
                    return $model->createdBy->employee->fullname;
                }
            ],
           
        ],
    ]) ?>

     <label class="form-label fw-bold">แนบใบสอบเทียบ (Calibration Certificate)</label>

 <?= $model->Upload(['view' => true]) ?>


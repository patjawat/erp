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
                'label' => 'หัวข้อการบำรุงรักษา',
                'value' => function($model){
                    return $model->data_json['title'] ?? '-';
                }
            ],
             [
                'label' => 'วันที่กำหนดแผน',
                'value' => function($model){
                    return Yii::$app->thaiDate->toThaiDate($model->plan_date, true, false);
                }
            ],
            [
                'label' => 'วันที่ดำเนินการ',
                'value' => function($model){
                    return Yii::$app->thaiDate->toThaiDate($model->actual_date, true, false);
                }
            ],
            [
                'label' => 'ผู้ดำเนินการ',
                'value' => function($model){
                    return $model->emp_id;
                }
            ],
            [
                'label' => 'ผลการตรวจสอล',
                'value' => function($model){
                    return $model->emp_id;
                }
            ],
        ],
    ]) ?>
 <?= $model->Upload(['view' => true]) ?>


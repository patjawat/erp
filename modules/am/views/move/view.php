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


<div class="asset-detail-view">

    <p>
        <?= Html::a('ตรวจสอบ', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

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
                'label' => 'วันที่',
                'value' => function($model){
                    return Yii::$app->thaiDate->toThaiDate($model->created_at, true, false);
                }
            ],
        ],
    ]) ?>
<?= $model->listImages() ?>
</div>

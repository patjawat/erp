<?php
use yii\data\ActiveDataProvider;
use app\modules\sm\models\AssetType;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use kartik\select2\Select2;
use yii\helpers\ArrayHelper;
use app\models\Categorise;
use app\modules\am\models\AssetItem;
use app\modules\am\models\AssetItemSearch;

$this->title = 'Asset Types';
$this->params['breadcrumbs'][] = $this->title;

$query = AssetItem::find()->where(['name' => 'asset_item','category_id' => $model->code]);

$dataProvider = new ActiveDataProvider([
    'query' => $query,
    'pagination' => [
        'pageSize' => 10,
    ],
]);
?>


    <?php echo  GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'hover' => true,
        'layout' => '{items}',
        'columns' => [
            [
                'class' => 'kartik\grid\SerialColumn',
                'contentOptions' => ['class' => 'kartik-sheet-style'],
                'width' => '36px',
                'pageSummary' => 'Total',
                'pageSummaryOptions' => ['colspan' => 6],
                'header' => '',
                'headerOptions' => ['class' => 'kartik-sheet-style'],
            ],
          
            [
                'attribute' => 'title',
                'format' => 'raw',
                'header' => 'ชื่อ',
                'value' => function($model){
                        return $this->render('view_item',['model' => $model]);
                }
            ],
            [
                'attribute' => 'code',
                'format' => 'raw',
                'header' => 'รหัส',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'value' => function($model){
                        return $model->code;
                }
            ],
            [
                'attribute' => 'data_json[service_life]',
                'format' => 'raw',
                'header' => 'อายุการใช้งาน (ปี)',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '150px',
                'value' => function($model){
                        return  isset($model->data_json["service_life"]) ? $model->data_json["service_life"] : '';
                }
            ],
            [
                'attribute' => 'data_json[depreciation]',
                'format' => 'raw',
                'header' => 'อัตราค่าเสื่อม',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '150px',
                'value' => function($model){
                        return isset($model->data_json["depreciation"]) ? $model->data_json["depreciation"] : '';
                }
            ],
            [
                'attribute' => 'category_id',
                'format' => 'raw',
                'header' => 'ประเภท',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '150px',
                'value' => function($model){
                        return $model->id;
                },
                'filter' => ArrayHelper::map(Categorise::find()->where(['name' => 'asset_group'])->asArray()->all(), 'code', 'title'),
                'filterType' => GridView::FILTER_SELECT2,
                'filterWidgetOptions' => [
                    'options' => ['prompt' => ''],
                    'pluginOptions' => ['allowClear' => true],
                ],
                ],
        ],
    ]); ?>


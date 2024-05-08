<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\Pjax;
use kartik\grid\GridView;

?>
<div class="card">
            <div class="card-body">
                <div class="mb-3">
                    <?=app\components\AppHelper::Btn([
                        'title' => "<i class='fa-solid fa-circle-plus'></i> สร้างใหม่",
                        'url' => ['/am/setting/create', 'name' => 'asset_item', 'id' => $model->id],
                        'modal' => true, 'size' => 'lg'])?>
                </div>
                <?php echo GridView::widget([
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
            'value' => function ($model) {
               return Html::a($model->title,['/am/setting/view-item','id' => $model->id],['class' =>  'open-modal', 'data' => ['size' => 'modal-lg']]);
            },
        ],
        [
            'attribute' => 'code',
            'format' => 'raw',
            'header' => 'รหัส FSN',
            'hAlign' => 'center',
            'vAlign' => 'middle',
            'value' => function ($model) {
                return '<code>'.$model->code.'</code>';
            },
        ],
       
    ],
]); ?>

            </div>
        </div>
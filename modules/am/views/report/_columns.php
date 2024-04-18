<?php
use kartik\grid\GridView;
return [
    [
        'class' => 'kartik\grid\SerialColumn',
        'contentOptions' => ['class' => 'kartik-sheet-style'],
        'width' => '36px',
        'pageSummary' => 'รวมทั้งสิ้น',
        'pageSummaryOptions' => ['colspan' => 4],
        'header' => '',
        'headerOptions' => ['class' => 'kartik-sheet-style'],
    ],
    [
        'class' => 'kartik\grid\ExpandRowColumn',
        'width' => '50px',
        'value' => function ($model, $key, $index, $column) {
            return GridView::ROW_COLLAPSED;
        },
        // uncomment below and comment detail if you need to render via ajax
        // 'detailUrl' => Url::to(['/site/book-details']),
        'detail' => function ($model, $key, $index, $column) {
            return Yii::$app->controller->renderPartial('_expand-row-details', ['model' => $model]);
        },
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'expandOneOnly' => true,
    ],

    [
        'attribute' => 'type_name',
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">ประเภททรัพย์สิน</span>',
    ],
    [
        'attribute' => 'sum_price',
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">ราคาทุนทรัพย์สิน</span>',
        'width' => '150px',
        'vAlign' => 'middle',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'mergeHeader' => true,
        'pageSummary' => true,
        'footer' => true,

    ],
    [
        'attribute' => 'total',
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">ค่าเสื่อมยกมา</span>',
        'width' => '150px',
        'vAlign' => 'middle',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'mergeHeader' => true,
        'pageSummary' => true,
        'footer' => true,

    ],
    [
        'attribute' => 'total',
        'class' => 'kartik\grid\FormulaColumn',
        'header' => '<span class="fw-semibold">ราคาทุนสุทธิทรัพย์สิน<span> <br>(ราคาทุนทรัพย์สิน - ค่าเสื่อมยกมา)',
        'width' => '250px',
        'vAlign' => 'middle',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'mergeHeader' => true,
        'pageSummary' => true,
        'footer' => true,

    ],
    [
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">อัตรา</span>',
        'width' => '80px',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'value' => function($model){
            return $model['depreciation'];
        },
        'format' => ['decimal', 2],
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'mergeHeader' => true,
        'pageSummary' => true,
        'footer' => true,

    ],
    [
        // 'attribute' => 'total',
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">จำนวน</span>',
        'width' => '80px',
        'vAlign' => 'middle',
        'hAlign' => 'center',
        'value' => function(){
            return 1;
        },
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'mergeHeader' => true,
        'pageSummary' => true,
        'footer' => true,

    ],
    [
        'attribute' => 'sum_total_month_price',
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">ค่าเสื่อราคาเดือนนี้</span>',
        'width' => '150px',
        'vAlign' => 'middle',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'mergeHeader' => true,
        'pageSummary' => true,
        'footer' => true,

    ],
    [
        'attribute' => 'total',
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">ค่าเสื่อมทรัพย์สินยกไป</span>',
        'width' => '200px',
        'vAlign' => 'middle',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'mergeHeader' => true,
        'pageSummary' => true,
        'footer' => true,

    ],
    [
        'attribute' => 'total',
        'class' => 'kartik\grid\FormulaColumn',
        'header' => '<span class="fw-semibold">ราคาทุนสุทธิทรัพย์สิน<span> <br>(ราคาทุนทรัพย์สิน - ค่าเสื่อมยกไป)',
        'width' => '250px',
        'vAlign' => 'middle',
        'hAlign' => 'right',
        'format' => ['decimal', 2],
        'headerOptions' => ['class' => 'kartik-sheet-style'],
        'mergeHeader' => true,
        'pageSummary' => true,
        'footer' => true,

    ],

];

<?php
use yii\helpers\html;
use kartik\grid\GridView;
return [


    [
        'attribute' => 'code',
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">รหัสทรัพย์สิน</span>',
    ],
    [
        'attribute' => 'date',
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">วันที่ส่งมอบ</span>',
        'value' => function($model){
            return Yii::$app->thaiFormatter->asDate($model['date'], 'medium');
        }
    ],
    [
        'attribute' => 'title',
        'format' => 'raw',
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">รายการทรัพย์สิน</span>',
        'value' => function($model){
            return Html::a($model['title'],['/am/asset/depreciation','id'=> $model['id']],['class' => 'text-truncate open-modal text-primary','data' => ['size' => 'modal-lg']]);
        } 
    ],
    [
        'attribute' => 'price',
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
        'attribute' => 'total_month_price',
        'vAlign' => 'middle',
        'header' => '<span class="fw-semibold">ค่าเสื่อมาคาเดือนนี้</span>',
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

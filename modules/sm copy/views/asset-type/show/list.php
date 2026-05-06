<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
use app\models\Categorise;
use kartik\select2\Select2;

?>

<div class="card">
    <div class="card-body p-0">


<?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'layout' => '{items}',
        'columns' => [
            [
                'class'=>'kartik\grid\SerialColumn',
                'contentOptions'=>['class'=>'kartik-sheet-style'],
                'width'=>'36px',
                'pageSummary'=>'Total',
                'pageSummaryOptions' => ['colspan' => 6],
                'header'=>'',
                'headerOptions'=>['class'=>'kartik-sheet-style']
            ],

            [
                'attribute' => 'title',
                'header' => 'รายการครุภัณฑ์',
                'format' => 'raw',
                'value' => function($model){
                        return $this->render('item',['model' => $model]);
                }
            ],
            
            [
                'attribute' => 'category_id',
                'format' => 'raw',
                'header' => 'ทรัพย์สิน',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'value' => function($model){
                    // return Categorise::findOne(['name' => 'asset_type','code'=>$model->category_id])->title;
                }
            ],

            [
                'attribute' => 'data_json',
                'format' => 'raw',
                'header' => 'ประเภท',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '150px',
                'filter' => Select2::widget([
                    'model' => $searchModel,
                    'attribute' => 'data_json',
                    'data' => ['3' => 'ครุภัณฑ์', '4' => 'วัสดุ'],
                    'options' => ['placeholder' => 'เลือกประเภท...'],
                    'pluginOptions' => [
                        'allowClear' => true,
                    ],
                ]),
                'value' => function($model){
                    // return $model->data_json["asset_type"]["category_id"] == 3 ? 'ครุภัณฑ์' : "วัสดุ";
                }
            ],
/*             [
                'format' => 'raw',
                'header' => 'ดำเนินการ',
                'hAlign' => 'center',
                'vAlign' => 'middle',
                'width' => '90px',
                'value' => function($model){
                    return '<div clas="d-flex gap-3">'.Html::a('<i class="fa-regular fa-pen-to-square"></i>', ['/sm/asset-item/view','id' => $model->id],['class' => 'btn btn-sm btn-primary open-modal', 'data' => ['size' => 'modal-md']]);
                }
            ], */

            // [
            //     'header' => 'ดำเนินการ',
            //     'width' => '100px',
            //     'vAlign' => 'middle',
            //     'hAlign' => 'center',
            //     'format' => 'raw',
            //     'value' => function($model){
            //             return '<div class="dropdown">
            //             <button type="button" class="btn p-0 dropdown-toggle hide-arrow"
            //                 data-bs-toggle="dropdown" aria-expanded="false"><i
            //                     class="bx bx-dots-vertical-rounded fw-bold"></i></button>
            //             <div class="dropdown-menu">
            //                 '.Html::a('<i class="fa-regular fa-pen-to-square me-1"></i>แก้ไข', ['/hr/position/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]).'

            //                 '.Html::a('<i class="fa-solid fa-trash me-1"></i>ลบ', ['/hr/position/delete', 'id' => $model->id], [
            //             'class' => 'dropdown-item delete-item',
            //             ]).'
            //             </div>
            //         </div>';
            //     }
            // ]
           
           
        ],
    ]); ?>


</div>
</div>


<?php
use yii\helpers\Html;
use kartik\grid\GridView;
use yii\widgets\Pjax;
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
                'attribute' => 'code',
                'header' => 'รหัส',
                'width' => '200px',
                'vAlign' => 'middle',
                'value' => function($model){
                        return $model->code;
                }
            ],
            [
                'attribute' => 'category_id',
                'header' => 'ประเภท',
                'width' => '200px',
                'value' => function($model){
                        return $model->FsnGroupName();
                }
            ],
            [
                'attribute' => 'asset_type',
                'header' => 'ประเภท',
                'width' => '200px',
                'value' => function($model){
                        return $model->fsnTypeName();
                }
            ],
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
            //             <div class="dropdown-menu" style="">
            //                 '.Html::a('<i class="bx bx-edit-alt me-1"></i>แก้ไข', ['/hr/position/update', 'id' => $model->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> แก้ไข'], ['class' => 'dropdown-item open-modal', 'data' => ['size' => 'modal-md']]).'

            //                 '.Html::a('<i class="bi bi-trash"></i>ลบ', ['/hr/position/delete', 'id' => $model->id], [
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


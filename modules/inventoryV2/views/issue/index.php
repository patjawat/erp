<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'รายการจ่ายพัสดุ (Stock Issue)';
?>

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-box-seam"></i> รายการใบเบิกจากคลังย่อย (รอจ่าย)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-hover align-middle'],
                    'summary' => false,
                    'columns' => [
                        [
                            'attribute' => 'order_date',
                            'label' => 'วันที่เบิก',
                            'format' => ['date', 'php:d/m/Y']
                        ],
                        [
                            'attribute' => 'order_no',
                            'label' => 'เลขที่ใบเบิก',
                            'contentOptions' => ['class' => 'fw-bold'],
                        ],
                        [
                            'label' => 'หน่วยงานที่เบิก',
                            'value' => function($model) {
                                return $model->subWarehouse ? $model->subWarehouse->warehouse_name : '-';
                            }
                        ],
                        [
                            'label' => 'สถานะ',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'text-center'],
                            'value' => function($model) {
                                if ($model->status === 'APPROVED') {
                                    return '<span class="badge rounded-pill bg-primary">รอคลังจ่าย</span>';
                                }
                                if ($model->status === 'CONFIRMED') {
                                    return '<span class="badge rounded-pill bg-success">จ่ายพัสดุแล้ว</span>';
                                }
                                return $model->status;
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'จัดการ',
                            'headerOptions' => ['class' => 'text-end'],
                            'contentOptions' => ['class' => 'text-end'],
                            'template' => '{process}',
                            'buttons' => [
                                'process' => function($url, $model) {
                                    if ($model->status === 'APPROVED') {
                                        return Html::a('<i class="bi bi-box-seam"></i> ดำเนินการจ่าย', ['process', 'id' => $model->id], [
                                            'class' => 'btn btn-primary btn-sm'
                                        ]);
                                    }
                                    return Html::a('<i class="bi bi-file-earmark-text"></i> ดูรายละเอียด', ['process', 'id' => $model->id], [
                                        'class' => 'btn btn-outline-secondary btn-sm'
                                    ]);
                                }
                            ]
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>
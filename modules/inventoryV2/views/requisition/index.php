<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'รายการใบขอเบิกวัสดุ';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="requisition-index">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary fw-bold">
                    <i class="bi bi-file-earmark-text me-2"></i><?= Html::encode($this->title) ?>
                </h5>
                <?= Html::a('<i class="bi bi-plus-circle me-1"></i> สร้างใบขอเบิกใหม่', ['create'], ['class' => 'btn btn-success rounded-pill']) ?>
            </div>
        </div>

        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'summaryOptions' => ['class' => 'p-3 text-muted'],
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    
                    [
                        'attribute' => 'order_no',
                        'label' => 'เลขที่เอกสาร',
                        'format' => 'raw',
                        'value' => function($model) {
                            return Html::a($model->order_no, ['view', 'id' => $model->id], ['class' => 'fw-bold']);
                        }
                    ],
                    [
                        'attribute' => 'main_warehouse_id',
                        'label' => 'คลังที่จ่ายของ',
                        'value' => function ($model) {
                            return $model->mainWarehouse->warehouse_name ?? '-';
                        }
                    ],
                    [
                        'attribute' => 'sub_warehouse_id',
                        'label' => 'หน่วยงานที่รับของ',
                        'value' => function ($model) {
                            return $model->subWarehouse->warehouse_name ?? '-';
                        }
                    ],
                    [
                        'attribute' => 'order_date',
                        'label' => 'วันที่ขอเบิก',
                        'format' => ['date', 'php:d/m/Y'],
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'สถานะ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $statusMap = [
                                'DRAFT'     => ['class' => 'bg-warning text-dark', 'label' => 'รออนุมัติ'],
                                'CONFIRMED' => ['class' => 'bg-success', 'label' => 'จ่ายแล้ว'],
                                'CANCELLED' => ['class' => 'bg-danger', 'label' => 'ยกเลิก'],
                            ];
                            $s = $statusMap[$model->status] ?? ['class' => 'bg-secondary', 'label' => $model->status];
                            return "<span class='badge {$s['class']}'>{$s['label']}</span>";
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {approve}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="bi bi-search"></i>', $url, [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'title' => 'ดูรายละเอียด',
                                ]);
                            },
                            'approve' => function ($url, $model) {
                                if ($model->status === 'DRAFT') {
                                    return Html::a('<i class="bi bi-check-circle"></i> อนุมัติ', ['approve', 'id' => $model->id], [
                                        'class' => 'btn btn-sm btn-success',
                                        'data' => [
                                            'confirm' => 'ยืนยันการอนุมัติและตัดสต็อกคลังหลัก?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                }
                                return '';
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
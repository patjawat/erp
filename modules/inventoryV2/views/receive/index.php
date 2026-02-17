<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

$this->title = 'รายการรับเข้าวัสดุ (Receiving)';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventoryV2']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="receive-index">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-primary fw-bold">
                    <i class="bi bi-box-arrow-in-right me-2"></i><?= Html::encode($this->title) ?>
                </h5>
                <?= Html::a('<i class="bi bi-plus-circle me-1"></i> สร้างใบรับเข้า', ['create'], ['class' => 'btn btn-success px-4 rounded-pill shadow-sm']) ?>
            </div>
        </div>
        
        <div class="card-body p-0">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'tableOptions' => ['class' => 'table table-hover align-middle mb-0'],
                'summaryOptions' => ['class' => 'p-3 text-muted'],
                'columns' => [
                    [
                        'class' => 'yii\grid\SerialColumn',
                        'headerOptions' => ['style' => 'width:50px;', 'class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'order_no',
                        'label' => 'เลขที่เอกสาร',
                        'format' => 'raw',
                        'value' => function($model) {
                            return Html::a($model->order_no, ['view', 'id' => $model->id], ['class' => 'fw-bold text-decoration-none']);
                        }
                    ],
                    [
                        'attribute' => 'order_date',
                        'label' => 'วันที่รับเข้า',
                        'format' => ['datetime', 'php:d/m/Y H:i'],
                    ],
                    [
                        'label' => 'คลังที่รับเข้า',
                        'attribute' => 'sub_warehouse_id',
                        'value' => function($model) {
                            $labelClass = ($model->sub_warehouse_id == 1) ? 'bg-info' : 'bg-secondary'; // สมมติ 1 คือคลังหลัก
                            return '<span class="badge ' . $labelClass . '">' . $model->mainWarehouse->warehouse_name . '</span>';
                        },
                        'format' => 'raw',
                    ],
                    [
                        'label' => 'จำนวนรายการ',
                        'value' => function($model) {
                            return count($model->stockDetails) . ' รายการ';
                        },
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'สถานะ',
                        'format' => 'raw',
                        'value' => function($model) {
                            $status = $model->status;
                            $class = $status === 'CONFIRMED' ? 'success' : ($status === 'CANCELLED' ? 'danger' : 'warning');
                            $text = $status === 'CONFIRMED' ? 'บันทึกแล้ว' : ($status === 'CANCELLED' ? 'ยกเลิก' : 'ร่าง');
                            return "<span class='badge rounded-pill bg-light text-{$class} border border-{$class}' style='width: 80px;'>{$text}</span>";
                        },
                        'contentOptions' => ['class' => 'text-center'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'header' => 'จัดการ',
                        'headerOptions' => ['style' => 'width:120px;', 'class' => 'text-center'],
                        'contentOptions' => ['class' => 'text-center'],
                        'template' => '{view} {update} {delete}',
                        'buttons' => [
                            'view' => function($url, $model) {
                                return Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary border-0']);
                            },
                            'update' => function($url, $model) {
                                if ($model->status === 'CANCELLED') return '';
                                return Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-warning border-0']);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
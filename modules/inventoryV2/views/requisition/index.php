<?php
use yii\helpers\Html;
use yii\grid\GridView;

$this->title = 'รายการใบขอเบิกวัสดุ';
$this->params['breadcrumbs'][] = $this->title;

$currentWarehouseId = Yii::$app->request->get('warehouse_id');
$currentWarehouseId = is_numeric($currentWarehouseId) ? (int) $currentWarehouseId : null;
?>

<?= $this->render('@app/modules/inventoryV2/views/sub-stock/_menu_sub_stock', [
    'active' => 'requisition',
    'currentWarehouseId' => $currentWarehouseId,
]) ?>

<div class="requisition-index">
    <div class="card shadow-sm">
        <div class="card-header bg-white py-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="mb-0 text-primary fw-bold">
                    <i class="bi bi-file-earmark-text me-2"></i><?= Html::encode($this->title) ?>
                </h5>
                <div class="d-flex gap-2">
                    <?= Html::a('<i class="bi bi-arrow-left me-1"></i> ย้อนกลับ', ['/inventory-v2/default/index'], ['class' => 'btn btn-outline-secondary rounded-pill']) ?>
                    <?= Html::a('<i class="bi bi-plus-circle me-1"></i> สร้างใบขอเบิกใหม่', ['create'], ['class' => 'btn btn-success rounded-pill']) ?>
                </div>
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
                        'label' => 'แผนก/ฝ่ายที่รับของ',
                        'value' => function ($model) {
                            return $model->subWarehouse->warehouse_name ?? '-';
                        }
                    ],
                    [
                        'attribute' => 'order_date',
                        'label' => 'วันที่ขอเบิก',
                        'format' => 'raw',
                        'value' => function ($model) {
                            return $model->order_date ? \app\components\ThaiDateHelper::formatThaiDate($model->order_date) : '-';
                        },
                    ],
                    [
                        'attribute' => 'status',
                        'label' => 'สถานะการอนุมัติ',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $s = \app\modules\inventoryV2\models\StockOrder::getStatusBadgeConfigFor($model->status);
                            $icon = !empty($s['icon']) ? '<i data-lucide="' . Html::encode($s['icon']) . '" class="me-1" style="width:14px;height:14px;vertical-align:-0.2em"></i>' : '';
                            return '<span class="' . $s['class'] . '">' . $icon . Html::encode($s['label']) . '</span>';
                        }
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view} {update} {approve}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="bi bi-search"></i>', $url, [
                                    'class' => 'btn btn-sm btn-outline-primary',
                                    'title' => 'ดูรายละเอียด',
                                ]);
                            },
                            'update' => function ($url, $model) {
                                if (!$model->canEdit()) return '';
                                return Html::a('<i class="bi bi-pencil"></i>', $url, [
                                    'class' => 'btn btn-sm btn-outline-secondary',
                                    'title' => 'แก้ไข',
                                ]);
                            },
                            'approve' => function ($url, $model) {
                                if (!in_array($model->status, ['DRAFT', 'PENDING'])) return '';
                                $approverEmpId = $model->getIssueSignatureEmpId('approver');
                                $isApprover = false;
                                if ($approverEmpId && !\Yii::$app->user->isGuest) {
                                    $approverEmp = \app\modules\hr\models\Employees::findOne($approverEmpId);
                                    $isApprover = $approverEmp && (int)$approverEmp->user_id === (int)\Yii::$app->user->id;
                                }
                                if (!$isApprover && !\Yii::$app->user->can('inventory')) return '';
                                return Html::a('<i class="bi bi-check-circle"></i> อนุมัติ', ['approve', 'id' => $model->id], [
                                    'class' => 'btn btn-sm btn-success',
                                    'data' => [
                                        'confirm' => 'ยืนยันอนุมัติใบขอเบิก? (ยังไม่ตัดสต็อก — คลังจะจ่ายที่เมนู "รายการจ่ายพัสดุ")',
                                        'method' => 'post',
                                    ],
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>
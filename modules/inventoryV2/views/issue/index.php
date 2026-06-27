<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use app\components\ThaiDateHelper;
use app\modules\inventoryV2\models\StockOrder;

$this->title = 'รายการจ่ายพัสดุ (Stock Issue)';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$searchModel = $searchModel ?? null;
$mainWarehouses = $mainWarehouses ?? ['' => 'ทุกคลัง'];
$subWarehouses = $subWarehouses ?? ['' => 'ทุกแผนก/ฝ่าย'];
$statusLabels = $statusLabels ?? ['' => 'ทุกสถานะ'];
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-box-seam erp-icon-box fs-4 text-primary"></i>
        <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted mb-0">รายการใบเบิกจากคลังย่อย (รอจ่าย / ดำเนินการจ่าย)</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'issue']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid">
    <?php if ($searchModel): ?>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header py-2 px-3">
            <h6 class="mb-0 text-muted fw-normal"><i class="bi bi-funnel me-1"></i> ค้นหา / กรอง</h6>
        </div>
        <div class="card-body py-3">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => Url::to(['index']),
                'options' => ['class' => 'row g-3 align-items-end'],
                'enableClientValidation' => false,
            ]); ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">เลขที่ใบเบิก</label>
                <?= $form->field($searchModel, 'order_no')->textInput(['class' => 'form-control form-control-sm', 'placeholder' => 'ค้นหา...'])->label(false) ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <label class="form-label small text-muted mb-1">วันที่เบิก</label>
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
                    <span class="text-muted small">–</span>
                    <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <label class="form-label small text-muted mb-1">วันที่จ่าย</label>
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <?= $form->field($searchModel, 'confirmed_date_start')->widget(\app\widgets\datepicker\DatepickerThai::class, ['options' => ['id' => 'issueConfirmedDateStart', 'placeholder' => 'ตั้งแต่']])->label(false) ?>
                    <span class="text-muted small">–</span>
                    <?= $form->field($searchModel, 'confirmed_date_end')->widget(\app\widgets\datepicker\DatepickerThai::class, ['options' => ['id' => 'issueConfirmedDateEnd', 'placeholder' => 'ถึง']])->label(false) ?>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">แผนก/ฝ่ายที่เบิก</label>
                <?= Html::activeDropDownList($searchModel, 'sub_warehouse_id', $subWarehouses, ['class' => 'form-select form-select-sm']) ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">คลังที่จ่าย</label>
                <?= Html::activeDropDownList($searchModel, 'main_warehouse_id', $mainWarehouses, ['class' => 'form-select form-select-sm']) ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">สถานะ</label>
                <?= Html::activeDropDownList($searchModel, 'status', $statusLabels, ['class' => 'form-select form-select-sm']) ?>
            </div>
            <div class="col-12 col-sm-auto d-flex gap-1 flex-wrap">
                <?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหา', ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('ล้าง', Url::to(['index']), ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 text-primary fw-bold"><i class="bi bi-box-seam erp-icon-box"></i> รายการใบเบิกจากคลังย่อย (รอจ่าย)</h5>
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
                            'format' => 'raw',
                            'value' => function ($model) {
                                return $model->order_date ? Html::encode(ThaiDateHelper::formatThaiDate($model->order_date)) : '-';
                            }
                        ],
                        [
                            'attribute' => 'order_no',
                            'label' => 'เลขที่ใบเบิก',
                            'contentOptions' => ['class' => 'fw-bold'],
                        ],
                        [
                            'label' => 'เหตุผลในการเบิก',
                            'format' => 'raw',
                            'value' => function ($model) {
                                $reason = trim((string) $model->getIssueReason());
                                if ($reason === '') {
                                    return '<span class="text-muted">-</span>';
                                }
                                return Html::tag('span', Html::encode($reason), [
                                    'class' => 'd-inline-block',
                                    'style' => 'max-width: 280px; white-space: normal;',
                                    'title' => $reason,
                                ]);
                            },
                            'contentOptions' => ['class' => 'small text-muted'],
                        ],
                        [
                            'label' => 'แผนก/ฝ่ายที่เบิก',
                            'value' => function($model) {
                                return $model->subWarehouse ? Html::encode($model->subWarehouse->warehouse_name) : '-';
                            }
                        ],
                        [
                            'label' => 'คลังที่จ่าย',
                            'value' => function($model) {
                                return $model->mainWarehouse ? Html::encode($model->mainWarehouse->warehouse_name) : '-';
                            }
                        ],
                        [
                            'label' => 'วันที่จ่าย',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'text-nowrap'],
                            'value' => function($model) {
                                if ($model->status !== 'CONFIRMED') {
                                    return '<span class="text-muted">ยังไม่จ่าย</span>';
                                }
                                $ts = $model->getDisbursementDate();
                                if (!$ts) return '<span class="text-muted">—</span>';
                                return Html::encode(ThaiDateHelper::formatThaiDate($ts));
                            }
                        ],
                        [
                            'label' => 'สถานะการอนุมัติ',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'text-center'],
                            'value' => function($model) {
                                $s = \app\modules\inventoryV2\models\StockOrder::getStatusBadgeConfigFor($model->status);
                                $icon = !empty($s['icon']) ? '<i data-lucide="' . Html::encode($s['icon']) . '" class="me-1" style="width:14px;height:14px;vertical-align:-0.2em"></i>' : '';
                                return '<span class="' . $s['class'] . '">' . $icon . Html::encode($s['label']) . '</span>';
                            }
                        ],
                        [
                            'class' => 'yii\grid\ActionColumn',
                            'header' => 'จัดการ',
                            'headerOptions' => ['class' => 'text-end'],
                            'contentOptions' => ['class' => 'text-end'],
                            'template' => '{approve} {process} {print} {printdoc}',
                            'buttons' => [
                                'approve' => function($url, $model) {
                                    if ($model->status !== StockOrder::STATUS_PENDING || !Yii::$app->user->can('inventory')) {
                                        return '';
                                    }
                                    return Html::a('<i class="bi bi-check-circle"></i> อนุมัติแทน', ['/inventory-v2/requisition/approve', 'id' => $model->id, 'returnUrl' => Url::current()], [
                                        'class' => 'btn btn-success btn-sm',
                                        'title' => 'อนุมัติแทนหัวหน้า',
                                        'data' => [
                                            'confirm' => 'ยืนยันอนุมัติใบเบิกนี้แทนหัวหน้า?',
                                            'method' => 'post',
                                        ],
                                    ]);
                                },
                                'process' => function($url, $model) {
                                    if ($model->status === StockOrder::STATUS_APPROVED) {
                                        return Html::a('<i class="bi bi-box-seam"></i> ดำเนินการจ่าย', ['process', 'id' => $model->id], [
                                            'class' => 'btn btn-primary btn-sm'
                                        ]);
                                    }
                                    if ($model->status === StockOrder::STATUS_PENDING) {
                                        return Html::a('<i class="bi bi-file-earmark-text"></i> ดูใบเบิก', ['/inventory-v2/requisition/view', 'id' => $model->id], [
                                            'class' => 'btn btn-outline-secondary btn-sm'
                                        ]);
                                    }
                                    return Html::a('<i class="bi bi-file-earmark-text"></i> ดูรายละเอียด', ['process', 'id' => $model->id], [
                                        'class' => 'btn btn-outline-secondary btn-sm'
                                    ]);
                                },
                                'print' => function($url, $model) {
                                    return Html::a('<i class="bi bi-printer"></i>', ['print', 'id' => $model->id], [
                                        'class' => 'btn btn-outline-secondary btn-sm border-0',
                                        'title' => 'พิมพ์ใบเบิกวัสดุ',
                                        'target' => '_blank',
                                    ]);
                                },
                                // 'printdoc' => function($url, $model) {
                                //     return Html::a('<i class="bi bi-printer"></i>', ['/inventory-v2/document/print-issu', 'id' => $model->id], [
                                //         'class' => 'btn btn-outline-secondary btn-sm border-0',
                                //         'title' => 'พิมพ์ใบเบิกวัสดุ',
                                //         'target' => '_blank',
                                //     ]);
                                // },
                            ]
                        ],
                    ],
                ]); ?>
            </div>
        </div>
    </div>
</div>

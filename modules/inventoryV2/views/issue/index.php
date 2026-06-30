<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;
use app\components\ThaiDateHelper;
use app\modules\hr\models\Employees;
use app\modules\inventoryV2\models\StockOrder;

$this->title = 'รายการจ่ายพัสดุ (Stock Issue)';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$searchModel = $searchModel ?? null;
$mainWarehouses = $mainWarehouses ?? ['' => 'ทุกคลัง'];
$subWarehouses = $subWarehouses ?? ['' => 'ทุกแผนก/ฝ่าย'];
$statusLabels = $statusLabels ?? ['' => 'ทุกสถานะ'];

/* prefetch ผู้ขอเบิก — ตัด N+1 query */
$models = $dataProvider->getModels();
$empIds = [];
$userIds = [];
foreach ($models as $m) {
    $eid = $m->getIssueSignatureEmpId('requester');
    if ($eid) {
        $empIds[] = (int) $eid;
    } elseif (!empty($m->created_by)) {
        $userIds[] = (int) $m->created_by;
    }
}
$empsById = $empIds
    ? Employees::find()->where(['id' => array_values(array_unique($empIds))])->indexBy('id')->all()
    : [];
$empsByUserId = $userIds
    ? Employees::find()->where(['user_id' => array_values(array_unique($userIds))])->indexBy('user_id')->all()
    : [];

$resolveRequester = function (StockOrder $model) use ($empsById, $empsByUserId) {
    $eid = $model->getIssueSignatureEmpId('requester');
    if ($eid && isset($empsById[$eid])) {
        return $empsById[$eid];
    }
    if (!empty($model->created_by) && isset($empsByUserId[$model->created_by])) {
        return $empsByUserId[$model->created_by];
    }
    return null;
};

$renderPerson = function ($emp, $fallbackName, $fallbackPosition) {
    $name = $fallbackName ?: ($emp ? trim($emp->fname . ' ' . $emp->lname) : '');
    $position = $fallbackPosition;
    if (!$position && $emp && method_exists($emp, 'positionName')) {
        $position = (string) $emp->positionName();
    }
    if ($name === '' && $position === '') {
        return '<span class="issue-empty">—</span>';
    }
    if ($emp && method_exists($emp, 'showAvatar')) {
        $img = Html::img('@web/img/loading.gif', [
            'class' => 'issue-person__avatar lazyload',
            'data' => ['src' => $emp->showAvatar(), 'expand' => '-20', 'sizes' => 'auto'],
            'alt' => '',
        ]);
    } else {
        $initial = $name !== '' ? mb_substr($name, 0, 1, 'UTF-8') : '?';
        $img = '<span class="issue-person__avatar issue-person__avatar--placeholder" aria-hidden="true">' . Html::encode($initial) . '</span>';
    }
    $out = '<div class="issue-person">' . $img . '<div class="issue-person__meta">';
    if ($name !== '') {
        $out .= '<div class="issue-person__name" title="' . Html::encode($name) . '">' . Html::encode($name) . '</div>';
    }
    if ($position !== '') {
        $out .= '<div class="issue-person__position" title="' . Html::encode($position) . '">' . Html::encode($position) . '</div>';
    }
    return $out . '</div></div>';
};
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
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">แหล่งที่มา</label>
                <?= Html::activeDropDownList($searchModel, 'source_v1', [
                    '' => 'ทั้งหมด',
                    'v2' => 'สร้างใน V2',
                    'v1' => 'ย้ายจาก V1',
                ], ['class' => 'form-select form-select-sm']) ?>
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
                            'attribute' => 'order_no',
                            'label' => 'เลขที่ใบเบิก / วันที่ขอ',
                            'format' => 'raw',
                            'contentOptions' => ['class' => 'text-nowrap'],
                            'value' => function ($model) {
                                $no = Html::encode($model->order_no);
                                if ($model->isMigratedFromV1()) {
                                    $no .= ' <span class="badge bg-secondary fw-normal" title="ย้ายมาจาก Inventory V1">V1</span>';
                                }
                                $date = $model->order_date
                                    ? Html::encode(ThaiDateHelper::formatThaiDate($model->order_date))
                                    : '<span class="text-muted">—</span>';
                                return '<div class="fw-bold">' . $no . '</div>'
                                    . '<div class="small text-muted">' . $date . '</div>';
                            },
                        ],
                        [
                            'label' => 'ผู้ขอเบิก',
                            'format' => 'raw',
                            'value' => function ($model) use ($resolveRequester, $renderPerson) {
                                $emp = $resolveRequester($model);
                                $sig = $model->getIssueSignature('requester');
                                return $renderPerson($emp, $sig['name'] ?? '', $sig['position'] ?? '');
                            },
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

<style>
.issue-person {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    min-width: 0;
}
.issue-person__avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    background: #eef2f7;
    flex-shrink: 0;
    border: 1px solid rgba(15, 23, 42, 0.08);
}
.issue-person__avatar--placeholder {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #4a5568;
    font-weight: 700;
    font-size: 0.82rem;
}
.issue-person__meta {
    min-width: 0;
    line-height: 1.25;
}
.issue-person__name {
    color: #1a202c;
    font-weight: 600;
    font-size: 0.88rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 14rem;
}
.issue-person__position {
    color: #718096;
    font-size: 0.76rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 14rem;
    margin-top: 1px;
}
.issue-empty {
    color: #a0aec0;
}
</style>

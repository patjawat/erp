<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use yii\grid\GridView;
use app\components\ThaiDateHelper;
use app\components\widgets\DataSummaryWidget;
use app\modules\hr\models\Employees;
use app\modules\inventoryV2\models\StockOrder;

$this->title = 'รายการจ่ายวัสดุ (Stock Issue)';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2/default/index']];
$this->params['breadcrumbs'][] = $this->title;

$searchModel = $searchModel ?? null;
$mainWarehouses = $mainWarehouses ?? ['' => 'ทุกคลัง'];
$subWarehouses = $subWarehouses ?? ['' => 'ทุกแผนก/ฝ่าย'];
$statusLabels = $statusLabels ?? ['' => 'ทุกสถานะ'];

/* ยอดใบเบิกที่ยังรอ (รออนุมัติ/รอจ่าย) — controller คำนวณตาม scope สิทธิ์ + คลังที่จ่ายที่เลือกมาให้แล้ว
   ใช้ทั้ง badge ในเมนู และ sync กลับหลัง pjax (เมนูอยู่นอก container #issue-pjax จึงไม่ refresh เอง) */
$issuePendingCount = (int) ($issuePendingCount ?? 0);
$issueFilterWarehouseId = ($searchModel && $searchModel->main_warehouse_id) ? (int) $searchModel->main_warehouse_id : null;

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
<?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', [
    'active' => 'issue',
    'mainWarehouseId' => $issueFilterWarehouseId,
    'issuePendingCount' => $issuePendingCount,
]) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid">
    <?php Pjax::begin(['id' => 'issue-pjax', 'timeout' => 5000, 'enablePushState' => true]); ?>
    <span id="issuePendingCountData" data-count="<?= $issuePendingCount ?>" hidden></span>
    <?php if ($searchModel): ?>
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-header py-2 px-3">
            <h6 class="mb-0 text-muted fw-normal"><i class="bi bi-funnel me-1"></i> ค้นหา / กรอง</h6>
        </div>
        <div class="card-body py-3">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => Url::to(['index']),
                'options' => ['class' => 'row g-3 align-items-end', 'id' => 'issue-search-form', 'data-pjax' => 1],
                'enableClientValidation' => false,
            ]); ?>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small text-muted mb-1">เลขที่ใบเบิก</label>
                <?= $form->field($searchModel, 'order_no')->textInput(['class' => 'form-control form-control', 'placeholder' => 'ค้นหา...'])->label(false) ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small text-muted mb-1">วันที่เบิก</label>
                <div class="issue-daterange">
                    <?= $form->field($searchModel, 'date_start')->widget(\app\widgets\datepicker\DatepickerThai::class, ['options' => ['class' => 'form-control', 'id' => 'issueOrderDateStart', 'placeholder' => 'ตั้งแต่']])->label(false) ?>
                    <span class="issue-daterange__sep" aria-hidden="true">–</span>
                    <?= $form->field($searchModel, 'date_end')->widget(\app\widgets\datepicker\DatepickerThai::class, ['options' => ['class' => 'form-control', 'id' => 'issueOrderDateEnd', 'placeholder' => 'ถึง']])->label(false) ?>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small text-muted mb-1">วันที่จ่าย</label>
                <div class="issue-daterange">
                    <?= $form->field($searchModel, 'confirmed_date_start')->widget(\app\widgets\datepicker\DatepickerThai::class, ['options' => ['class' => 'form-control', 'id' => 'issueConfirmedDateStart', 'placeholder' => 'ตั้งแต่']])->label(false) ?>
                    <span class="issue-daterange__sep" aria-hidden="true">–</span>
                    <?= $form->field($searchModel, 'confirmed_date_end')->widget(\app\widgets\datepicker\DatepickerThai::class, ['options' => ['class' => 'form-control', 'id' => 'issueConfirmedDateEnd', 'placeholder' => 'ถึง']])->label(false) ?>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small text-muted mb-1">แผนก/ฝ่ายที่เบิก</label>
                <?= Html::activeDropDownList($searchModel, 'sub_warehouse_id', $subWarehouses, ['class' => 'form-select form-select']) ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small text-muted mb-1">คลังที่จ่าย</label>
                <?= Html::activeDropDownList($searchModel, 'main_warehouse_id', $mainWarehouses, ['class' => 'form-select form-select']) ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small text-muted mb-1">สถานะ</label>
                <?= Html::activeDropDownList($searchModel, 'status', $statusLabels, ['class' => 'form-select form-select']) ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <label class="form-label small text-muted mb-1">แหล่งที่มา</label>
                <?= Html::activeDropDownList($searchModel, 'source_v1', [
                    '' => 'ทั้งหมด',
                    'v2' => 'สร้างใน V2',
                    'v1' => 'ย้ายจาก V1',
                ], ['class' => 'form-select form-select']) ?>
            </div>
            <div class="col-12 col-sm-6 col-lg-3 d-flex align-items-end gap-2">
                <?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหา', ['class' => 'btn btn-primary btn-sm flex-fill']) ?>
                <?= Html::a('ล้าง', Url::to(['index']), ['class' => 'btn btn-outline-secondary btn-sm', 'data' => ['pjax' => 0]]) ?>
            </div>
            <?php ActiveForm::end(); ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex align-items-center">
            <h5 class="mb-0 text-primary fw-bold d-flex align-items-center gap-2 text-nowrap"><i class="bi bi-box-seam erp-icon-box"></i> รายการใบเบิกจากคลังย่อย (รอจ่าย)</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'tableOptions' => ['class' => 'table table-hover align-middle'],
                    'summary' => false,
                    'layout' => '{items}',
                    'columns' => [
                        [
                            'class' => 'yii\grid\SerialColumn',
                            'header' => 'ลำดับ',
                            'headerOptions' => ['class' => 'text-center', 'style' => 'width: 3rem;'],
                            'contentOptions' => ['class' => 'text-center text-muted'],
                        ],
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
                                            'pjax' => 0,
                                        ],
                                    ]);
                                },
                                'process' => function($url, $model) {
                                    if ($model->status === StockOrder::STATUS_APPROVED) {
                                        return Html::a('<i class="bi bi-box-seam"></i> ดำเนินการจ่าย', ['process', 'id' => $model->id], [
                                            'class' => 'btn btn-primary btn-sm',
                                            'data' => ['pjax' => 0],
                                        ]);
                                    }
                                    if ($model->status === StockOrder::STATUS_PENDING) {
                                        return Html::a('<i class="bi bi-file-earmark-text"></i> ดูใบเบิก', ['/inventory-v2/requisition/view', 'id' => $model->id], [
                                            'class' => 'btn btn-outline-secondary btn-sm',
                                            'data' => ['pjax' => 0],
                                        ]);
                                    }
                                    return Html::a('<i class="bi bi-file-earmark-text"></i> ดูรายละเอียด', ['process', 'id' => $model->id], [
                                        'class' => 'btn btn-outline-secondary btn-sm',
                                        'data' => ['pjax' => 0],
                                    ]);
                                },
                                'print' => function($url, $model) {
                                    return Html::a('<i class="bi bi-printer"></i>', ['print', 'id' => $model->id], [
                                        'class' => 'btn btn-outline-secondary btn-sm border-0',
                                        'title' => 'พิมพ์ใบเบิกวัสดุ',
                                        'target' => '_blank',
                                        'data' => ['pjax' => 0],
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
        <div class="card-footer bg-white py-2 px-3">
            <?= DataSummaryWidget::widget([
                'dataProvider' => $dataProvider,
            ]) ?>
        </div>
    </div>
    <?php Pjax::end(); ?>
</div>

<?php
/* sync badge "จ่ายวัสดุ" ในเมนู (อยู่นอก #issue-pjax) ให้ตรงกับคลังที่กรอง หลัง pjax reload */
$this->registerJs(<<<'JS'
$(document).off('pjax:end.issueBadge').on('pjax:end.issueBadge', '#issue-pjax', function () {
    var data = document.getElementById('issuePendingCountData');
    var link = document.querySelector('[data-issue-nav-link]');
    if (!data || !link) return;
    var count = parseInt(data.getAttribute('data-count'), 10) || 0;
    var badge = link.querySelector('[data-issue-pending-badge]');
    if (count > 0) {
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'badge text-bg-danger ms-1';
            badge.setAttribute('data-issue-pending-badge', '');
            badge.title = 'รออนุมัติ/รอจ่าย';
            link.appendChild(badge);
        }
        badge.textContent = String(count);
    } else if (badge) {
        badge.remove();
    }
});
JS);
?>

<style>
/* ─── ช่วงวันที่ (date range) ในฟอร์มค้นหา — สอง input + คั่นกลาง ───
   จัดให้ input เริ่ม/สิ้นสุด แบ่งความกว้างเท่ากันเสมอ และสูงเท่ากับ dropdown อื่น */
.issue-daterange {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}
.issue-daterange > .mb-3 {           /* Yii ActiveField wrapper — ให้ยืดเท่ากัน ไม่มี margin ล่าง */
    flex: 1 1 0;
    min-width: 0;
    margin-bottom: 0;
}
.issue-daterange .form-control {
    width: 100%;
}
.issue-daterange__sep {
    flex: 0 0 auto;
    color: #a0aec0;
    font-size: 0.85rem;
    line-height: 1;
    user-select: none;
}
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

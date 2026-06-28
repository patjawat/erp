<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\components\ThaiDateHelper;
use app\modules\inventoryV2\models\StockOrder;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var string $filterStatus */
/** @var \yii\base\DynamicModel $searchModel */
/** @var array $mainWarehouses */

$this->title = 'ขออนุมัติเบิกวัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'ระบบการอนุมัติ', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;

$filterStatus = $filterStatus ?? 'all';
$searchParams = array_filter([
    'order_no' => $searchModel->order_no ?? '',
    'main_warehouse_id' => $searchModel->main_warehouse_id ?? '',
    'date_start' => $searchModel->date_start ?? '',
    'date_end' => $searchModel->date_end ?? '',
], function ($v) { return $v !== '' && $v !== null; });
$baseUrl = array_merge(['/approve-v2/main-stock/requisition-v2'], $searchParams);
$statusFilters = [
    'all'       => ['label' => 'ทั้งหมด', 'url' => array_merge($baseUrl, ['status' => 'all'])],
    'pending'   => ['label' => 'รออนุมัติ', 'url' => array_merge($baseUrl, ['status' => 'pending'])],
    'APPROVED'  => ['label' => 'อนุมัติแล้ว', 'url' => array_merge($baseUrl, ['status' => 'APPROVED'])],
    'CONFIRMED' => ['label' => 'จ่ายแล้ว', 'url' => array_merge($baseUrl, ['status' => 'CONFIRMED'])],
    'CANCELLED' => ['label' => 'ยกเลิก', 'url' => array_merge($baseUrl, ['status' => 'CANCELLED'])],
];
$mainWarehouses = $mainWarehouses ?? [];
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-list">
            <path d="M12 5v14" /><path d="M19 7v14" /><path d="M5 5v14" /><path d="M5 5h14" />
        </svg>
        เบิกวัสดุv2 — <?= Html::encode($this->title) ?>
    </h4>
    <p class="text-muted small mb-0">รายการใบขอเบิกวัสดุ — ดูย้อนหลังได้ (ระบบคลัง v2)</p>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/components/ui/btnReturn') ?>
<?php $this->endBlock(); ?>

<?= $this->render('@app/modules/approveV2/tab_menu', [
    'menu' => 'main-stock-requisition-v2'
]) ?>

<div class="card approve-v2-card">
    <div class="card-body">
        <?php $form = ActiveForm::begin([
            'action' => ['/approve-v2/main-stock/requisition-v2'],
            'method' => 'get',
            'options' => ['class' => 'mb-3'],
        ]); ?>
        <?= Html::hiddenInput('status', $filterStatus) ?>
        <div class="row g-2 align-items-end flex-wrap">
            <div class="col-auto">
                <label class="form-label small text-muted mb-0">เลขที่เอกสาร</label>
                <?= Html::textInput('order_no', $searchModel->order_no ?? '', [
                    'class' => 'form-control form-control-sm',
                    'placeholder' => 'เลขที่เอกสาร',
                    'style' => 'min-width: 120px;',
                ]) ?>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-0">คลังที่จ่ายของ</label>
                <?= Html::dropDownList('main_warehouse_id', $searchModel->main_warehouse_id ?? '', ['' => '-- ทั้งหมด --'] + $mainWarehouses, [
                    'class' => 'form-select form-select-sm',
                    'style' => 'min-width: 160px;',
                ]) ?>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-0">วันที่ขอเบิก (จาก)</label>
                <?= $form->field($searchModel, 'date_start', ['options' => ['class' => 'mb-0']])->widget(\app\widgets\datepicker\DatepickerThai::class, [
                    'options' => ['class' => 'form-control form-control-sm', 'placeholder' => 'ว/ด/พ.ศ.', 'style' => 'min-width: 110px;'],
                ])->label(false) ?>
            </div>
            <div class="col-auto">
                <label class="form-label small text-muted mb-0">ถึง</label>
                <?= $form->field($searchModel, 'date_end', ['options' => ['class' => 'mb-0']])->widget(\app\widgets\datepicker\DatepickerThai::class, [
                    'options' => ['class' => 'form-control form-control-sm', 'placeholder' => 'ว/ด/พ.ศ.', 'style' => 'min-width: 110px;'],
                ])->label(false) ?>
            </div>
            <div class="col-auto">
                <?= Html::submitButton('<i class="bi bi-search"></i> ค้นหา', ['class' => 'btn btn-primary btn-sm']) ?>
                <?= Html::a('ล้าง', ['/approve-v2/main-stock/requisition-v2', 'status' => $filterStatus], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
            </div>
        </div>
        <?php ActiveForm::end(); ?>

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h6 class="mb-0"><i class="bi bi-ui-checks"></i> รายการขออนุมัติเบิกวัสดุ</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1"><?= number_format($dataProvider->getTotalCount(), 0) ?></span>
                <div class="btn-group btn-group-sm ms-2" role="group">
                    <?php foreach ($statusFilters as $key => $opt): ?>
                        <?= Html::a($opt['label'], $opt['url'], [
                            'class' => 'btn ' . ($filterStatus === $key ? 'btn-primary' : 'btn-outline-secondary'),
                        ]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <?= Html::a('<i class="bi bi-box-seam me-1"></i> ไปที่คลัง v2', ['/inventory-v2/requisition/index'], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
        </div>
        <div class="table-responsive" style="max-height: 600px; min-height: 300px; overflow: auto;">
            <table class="table table-striped table-hover mb-0">
                <thead style="position: sticky; top: 0; z-index: 10; background: #fff;">
                    <tr>
                        <th class="text-center" style="width: 40px;">ลำดับ</th>
                        <th class="text-start" style="min-width: 120px;">เลขที่เอกสาร</th>
                        <th class="text-start">คลังที่จ่ายของ</th>
                        <th class="text-start">คลังที่ที่รับของ</th>
                        <th class="text-center" style="width: 100px;">วันที่ขอเบิก</th>
                        <th class="text-center" style="width: 110px;">วันที่อนุมัติ</th>
                        <th class="text-center" style="width: 140px;">สถานะ</th>
                        <th class="text-center" style="width: 160px;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                    <?php
                    $statusMap = [
                        'DRAFT'    => ['class' => 'badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill fw-medium px-2 py-1', 'label' => 'ฉบับร่าง', 'icon' => 'file-edit'],
                        'PENDING'  => ['class' => 'badge bg-info bg-opacity-10 text-info border border-info-subtle rounded-pill fw-medium px-2 py-1', 'label' => 'รอหัวหน้าอนุมัติ', 'icon' => 'clock'],
                        'APPROVED' => ['class' => 'badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1', 'label' => 'อนุมัติแล้ว — รอคลังจ่าย', 'icon' => 'circle-check'],
                        'CONFIRMED'=> ['class' => 'badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill fw-medium px-2 py-1', 'label' => 'จ่ายแล้ว', 'icon' => 'package-check'],
                        'CANCELLED'=> ['class' => 'badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1', 'label' => 'ยกเลิก', 'icon' => 'x-circle'],
                    ];
                    foreach ($dataProvider->getModels() as $key => $model):
                        /** @var StockOrder $model */
                        $s = $statusMap[$model->status] ?? ['class' => 'badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1', 'label' => $model->status, 'icon' => 'circle'];
                        $statusIcon = !empty($s['icon']) ? '<i data-lucide="' . Html::encode($s['icon']) . '" class="me-1" style="width:14px;height:14px;vertical-align:-0.2em"></i>' : '';
                        $orderDate = $model->order_date ? ThaiDateHelper::formatThaiDate($model->order_date) : '-';
                        $approverSig = $model->getIssueSignature('approver');
                        $approvalDate = '';
                        if (in_array($model->status, [StockOrder::STATUS_APPROVED, StockOrder::STATUS_CONFIRMED], true) && !empty($approverSig['date'])) {
                            $approvalDate = ThaiDateHelper::formatThaiDate($approverSig['date']);
                        } else {
                            $approvalDate = '—';
                        }
                    ?>
                        <tr>
                            <td class="text-center"><?= ($dataProvider->pagination->offset + 1) + $key ?></td>
                            <td>
                                <?= Html::a(Html::encode($model->order_no), ['/inventory-v2/requisition/view', 'id' => $model->id], ['class' => 'fw-bold open-modal', 'data' => ['size' => 'modal-xl'], 'title' => 'ดูรายละเอียด']) ?>
                            </td>
                            <td><?= Html::encode($model->mainWarehouse->warehouse_name ?? '-') ?></td>
                            <td><?= Html::encode($model->subWarehouse->warehouse_name ?? '-') ?></td>
                            <td class="text-center"><?= Html::encode($orderDate) ?></td>
                            <td class="text-center text-muted small"><?= Html::encode($approvalDate) ?></td>
                            <td class="text-center">
                                <span class="<?= $s['class'] ?>"><?= $statusIcon . Html::encode($s['label']) ?></span>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center flex-wrap">
                                    <?= Html::a('<i class="bi bi-search"></i> ดู', ['/inventory-v2/requisition/view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data' => ['size' => 'modal-xl'], 'title' => 'ดูรายละเอียด']) ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if ($dataProvider->getTotalCount() > 0): ?>
        <div class="d-flex justify-content-center mt-3">
            <?= \yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'firstPageLabel' => 'หน้าแรก',
                'lastPageLabel' => 'หน้าสุดท้าย',
                'options' => ['class' => 'pagination pagination-sm mb-0'],
            ]) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($dataProvider->getTotalCount() === 0): ?>
<div class="alert alert-info mb-0">
    <i class="bi bi-info-circle me-2"></i>
    <?= $filterStatus === 'pending' ? 'ไม่มีรายการรออนุมัติในขณะนี้' : 'ไม่มีรายการในเงื่อนไขที่เลือก' ?>
</div>
<?php endif; ?>

<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;
use app\components\StatusBadgeHelper;
use app\components\widgets\DataSummaryWidget;

$totalCount = (int) $dataProvider->getTotalCount();
$pagination = $dataProvider->getPagination();

$this->title = 'รับเข้าวัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventory-v2']];
$this->params['breadcrumbs'][] = $this->title;

$statusLabels = [
    '' => 'ทุกสถานะ',
    'DRAFT' => 'ร่าง',
    'CONFIRMED' => 'บันทึกแล้ว',
    'CANCELLED' => 'ยกเลิก',
];
$statusSummary = $statusSummary ?? [];
$cntDraft = (int) ($statusSummary['DRAFT'] ?? 0);
$cntConfirmed = (int) ($statusSummary['CONFIRMED'] ?? 0);
$cntCancelled = (int) ($statusSummary['CANCELLED'] ?? 0);
$cntAll = $cntDraft + $cntConfirmed + $cntCancelled;
$currentStatus = $searchModel->status ?? '';

$editTooltip = '<div class="rv-tip rv-tip--warning">'
    . '<div class="rv-tip__head"><i class="bi bi-pencil"></i> แก้ไขใบรับเข้า</div>'
    . '<div class="rv-tip__body">แก้ไขได้เฉพาะตอนที่ยังไม่มีการเบิกจ่ายวัสดุจาก Lot นี้เลย เพราะการแก้ไขจะย้อนยอดสต็อกเดิมออกก่อนแล้วรับเข้าใหม่ตามข้อมูลที่แก้ทั้งหมด</div>'
    . '</div>';
$cancelTooltip = '<div class="rv-tip rv-tip--secondary">'
    . '<div class="rv-tip__head"><i class="bi bi-x-circle"></i> ยกเลิกใบรับเข้า</div>'
    . '<div class="rv-tip__body">ยกเลิกได้เกือบทุกกรณี เอกสารยังอยู่ในระบบแต่ไม่มีผลอีกต่อไป เหมาะกับกรณีต้องการเก็บประวัติไว้ตรวจสอบย้อนหลัง</div>'
    . '</div>';
$deleteTooltip = '<div class="rv-tip rv-tip--danger">'
    . '<div class="rv-tip__head"><i class="bi bi-trash"></i> ลบใบรับเข้า</div>'
    . '<div class="rv-tip__body">ลบถาวร กู้คืนไม่ได้ กดได้เฉพาะตอนที่ยังไม่มีการเบิกจ่ายวัสดุจาก Lot นี้เลย เหมาะกับกรณีรับเข้าผิดทั้งใบ</div>'
    . '</div>';
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 15V3" />
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
            <path d="m7 10 5 5 5-5" />
        </svg>
        <?= Html::encode($this->title) ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/inventoryV2/views/default/_menu_main', ['active' => 'receive']) ?>
<?php $this->endBlock(); ?>


<div class="receive-index">
    <?= $this->render('_summary_cards', [
        'cntAll' => $cntAll,
        'cntDraft' => $cntDraft,
        'cntConfirmed' => $cntConfirmed,
        'cntCancelled' => $cntCancelled,
        'currentStatus' => $currentStatus,
    ]) ?>

    <!-- รายการ -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary-gradient text-white py-2 px-3">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="text-white mb-0 small fw-normal">
                    <i class="bi bi-ui-checks me-1"></i> ทะเบียน<?= Html::encode($this->title) ?>
                    <span class="badge text-bg-light ms-1"><?= number_format($dataProvider->getTotalCount(), 0) ?></span> รายการ
                </h6>
                <div class="d-flex flex-wrap gap-2">
                    <?= Html::a('<i class="bi bi-file-earmark-excel me-1"></i> ส่งออก Excel', Url::to(array_merge(['export-excel-list'], \Yii::$app->request->queryParams)), [
                        'class' => 'btn btn-success btn-sm',
                        'title' => 'ส่งออกรายการตามตัวกรองปัจจุบันทั้งหมดเป็น Excel',
                        'target' => '_blank',
                        'data' => ['pjax' => 0],
                    ]) ?>
                    <?= Html::a('<i class="bi bi-plus-circle me-1"></i> สร้างใบรับเข้า', ['create'], ['class' => 'btn btn-light btn-sm', 'data' => ['pjax' => 0]]) ?>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <?php Pjax::begin(['id' => 'receive-pjax', 'timeout' => 5000, 'enablePushState' => true]); ?>
            <div class="p-3">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => Url::to(['index']),
                'options' => ['class' => 'row g-3 align-items-end w-100 m-0', 'id' => 'receive-search-form', 'data-pjax' => 1],
                'enableClientValidation' => false,
            ]); ?>
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <label class="form-label small text-muted mb-1">เลขที่เอกสาร</label>
                <?= $form->field($searchModel, 'order_no')
                    ->textInput(['class' => 'form-control w-100', 'placeholder' => 'ค้นหาเลขที่...'])
                    ->label(false) ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <label class="form-label small text-muted mb-1">ช่วงวันที่</label>
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <?= $this->render('@app/components/ui/_date_start', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
                    <span class="text-muted small flex-shrink-0">–</span>
                    <?= $this->render('@app/components/ui/_date_end', ['form' => $form, 'model' => $searchModel, 'label' => false]) ?>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">คลัง</label>
                <?= Html::activeDropDownList($searchModel, 'main_warehouse_id', $warehouses ?? ['' => 'ทุกคลัง'], [
                    'class' => 'form-select w-100',
                ]) ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">ประเภทวัสดุ</label>
                <?= Html::activeDropDownList($searchModel, 'category_id', $listItemType ?? ['' => 'ทุกประเภท'], [
                    'class' => 'form-select w-100',
                ]) ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                <label class="form-label small text-muted mb-1">สถานะ</label>
                <?= Html::activeDropDownList($searchModel, 'status', $statusLabels, [
                    'class' => 'form-select w-100',
                ]) ?>
            </div>
            <div class="col-12 col-sm-6 col-md-4 col-lg">
                <label class="form-label small text-muted mb-1">ช่วงมูลค่า (บาท)</label>
                <div class="d-flex align-items-center gap-1 flex-wrap">
                    <?= Html::activeInput('number', $searchModel, 'total_from', ['class' => 'form-control flex-grow-1 min-w-0', 'placeholder' => 'ตั้งแต่', 'step' => '0.01', 'min' => '0']) ?>
                    <span class="text-muted small flex-shrink-0">–</span>
                    <?= Html::activeInput('number', $searchModel, 'total_to', ['class' => 'form-control flex-grow-1 min-w-0', 'placeholder' => 'ถึง', 'step' => '0.01', 'min' => '0']) ?>
                </div>
            </div>
            <div class="col-12 col-sm-12 col-md-auto d-flex gap-1 flex-wrap">
                <?= Html::submitButton('<i class="bi bi-search me-1"></i> ค้นหา', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('ล้าง', Url::to(['index']), ['class' => 'btn btn-outline-secondary', 'title' => 'ล้างตัวกรอง']) ?>
            </div>
            <?php ActiveForm::end(); ?>
            </div>
            <div class="table-receive-grid">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center text-nowrap bg-light bg-opacity-50" style="width: 42px;">#</th>
                        <th class="text-nowrap bg-light bg-opacity-50">เลขที่เอกสาร</th>
                        <th class="text-nowrap bg-light bg-opacity-50">วันที่</th>
                        <th class="text-nowrap bg-light bg-opacity-50">คลัง</th>
                        <th class="text-nowrap bg-light bg-opacity-50">ประเภทวัสดุ</th>
                        <th class="text-end text-nowrap bg-light bg-opacity-50">รายการ</th>
                        <th class="text-end text-nowrap bg-light bg-opacity-50">มูลค่าที่รับเข้า</th>
                        <th class="text-center text-nowrap bg-light bg-opacity-50">สถานะ</th>
                        <th style="width: 145px" class="text-end bg-light bg-opacity-50">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                <?php
                $pageTotalValue = 0.0;
                $models = $dataProvider->getModels();
                $rowNo = ($pagination ? $pagination->getOffset() : 0) + 1;
                ?>
                <?php if (empty($models)): ?>
                    <tr>
                        <td colspan="9" class="text-center">
                            <div class="text-muted py-5"><i class="bi bi-inbox display-6 d-block mb-2"></i>ยังไม่มีใบรับเข้า</div>
                            <div class="pb-3"><?= Html::a('สร้างใบรับเข้า', ['create'], ['class' => 'btn btn-success btn-sm', 'data' => ['pjax' => 0]]) ?></div>
                        </td>
                    </tr>
                <?php else: ?>
                <?php foreach ($models as $item): ?>
                    <?php
                    $wh = $item->main_warehouse_id ? $item->mainWarehouse : null;
                    $warehouseName = $wh ? Html::encode($wh->warehouse_name) : '-';
                    $detailCount = number_format(count($item->stockDetails));
                    $rowTotal = 0;
                    $typeNames = [];
                    foreach ($item->stockDetails as $d) {
                        $rowTotal += (float) $d->qty * (float) ($d->unit_price ?? 0);
                        if ($d->item && $d->item->categoryType) {
                            $typeNames[$d->item->categoryType->code] = $d->item->categoryType->title;
                        }
                    }
                    $pageTotalValue += $rowTotal;
                    ?>
                    <?php
                    $isDraft = $item->status === 'DRAFT';
                    $itemUndeletableReason = $item->getUndeletableReason();
                    $itemCanDelete = $itemUndeletableReason === null;
                    ?>
                    <tr class="<?= $isDraft ? 'table-warning' : '' ?>" <?= $isDraft ? 'title="ฉบับร่าง — ยังไม่อัปเดตยอดคลัง คลิกเพื่อแก้ไขแล้วบันทึกรับเข้า"' : '' ?>>
                        <td class="text-center text-muted" style="font-variant-numeric: tabular-nums;"><?= $rowNo++ ?></td>
                        <td>
                            <?php if ($isDraft): ?>
                                <i class="bi bi-exclamation-triangle-fill text-warning me-1" data-bs-toggle="tooltip" title="ฉบับร่าง — ยังไม่เข้าคลัง"></i>
                            <?php endif; ?>
                            <?= Html::a(Html::encode($item->order_no), ['view', 'id' => $item->id], ['class' => 'fw-semibold text-decoration-none', 'data' => ['pjax' => 0]]) ?>
                        </td>
                        <td class="text-secondary"><?= $item->order_date ? \app\components\ThaiDateHelper::formatThaiDate($item->order_date) : '-' ?></td>
                        <td><?= $item->main_warehouse_id ? $warehouseName : '<span class="text-muted">-</span>' ?></td>
                        <td class="text-secondary small"><?= !empty($typeNames) ? Html::encode(implode(', ', $typeNames)) : '<span class="text-muted">-</span>' ?></td>
                        <td class="text-end text-muted"><?= $detailCount ?></td>
                        <td class="text-end"><span class="fw-semibold"><?= number_format($rowTotal, 2) ?></span> <span class="text-muted small">บาท</span></td>
                        <td class="text-center"><?= StatusBadgeHelper::renderStatusBadge($item->status, ['tooltip' => StatusBadgeHelper::getLabel($item->status)]) ?></td>
                        <td class="text-end">
                            <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $item->id], ['class' => 'btn btn-sm btn-info', 'title' => 'ดู', 'data' => ['pjax' => 0]]) ?>
                            <?php if ($itemCanDelete): ?>
                                <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-warning',
                                    'title' => $editTooltip,
                                    'data' => [
                                        'pjax' => 0,
                                        'bs-toggle' => 'tooltip',
                                        'bs-placement' => 'top',
                                        'bs-html' => 'true',
                                        'bs-custom-class' => 'rv-tip-pop',
                                    ],
                                ]) ?>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-warning" disabled title="<?= Html::encode($itemUndeletableReason) ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            <?php endif; ?>
                            <?php if ($item->status !== 'CANCELLED'): ?>
                                <?= Html::a('<i class="bi bi-x-circle"></i>', ['cancel', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-secondary',
                                    'title' => $cancelTooltip,
                                    'data' => [
                                        'method' => 'post',
                                        'confirm' => 'ยืนยันยกเลิกใบรับเข้านี้? ระบบจะหักยอดสต็อกคืน',
                                        'pjax' => 0,
                                        'bs-toggle' => 'tooltip',
                                        'bs-placement' => 'top',
                                        'bs-html' => 'true',
                                        'bs-custom-class' => 'rv-tip-pop',
                                    ],
                                ]) ?>
                            <?php endif; ?>
                            <?php if ($itemCanDelete): ?>
                                <?= Html::a('<i class="bi bi-trash"></i>', ['delete', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-danger',
                                    'title' => $deleteTooltip,
                                    'data' => [
                                        'method' => 'post',
                                        'confirm' => 'ยืนยันลบใบรับเข้านี้? ระบบจะลบรายการและยอดคงเหลือที่เกี่ยวข้องทั้งหมด และไม่สามารถกู้คืนได้',
                                        'pjax' => 0,
                                        'bs-toggle' => 'tooltip',
                                        'bs-placement' => 'top',
                                        'bs-html' => 'true',
                                        'bs-custom-class' => 'rv-tip-pop',
                                    ],
                                ]) ?>
                            <?php else: ?>
                                <button type="button" class="btn btn-sm btn-danger" disabled title="<?= Html::encode($itemUndeletableReason) ?>">
                                    <i class="bi bi-trash"></i>
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <div class="px-3 py-2 border-top bg-light bg-opacity-50">
                <?php if ($totalCount > 0): ?>
                    <div class="d-flex flex-wrap gap-2 gap-md-3 mb-2">
                        <span class="text-muted small">ผลรวมหน้านี้: <strong class="text-body"><?= number_format($pageTotalValue, 2) ?></strong> บาท</span>
                        <span class="text-muted small">รวมยอดเงินทั้งหมด: <strong class="text-primary"><?= number_format($totalAmount ?? 0, 2) ?></strong> บาท</span>
                    </div>
                <?php endif; ?>
                <?= DataSummaryWidget::widget([
                    'dataProvider' => $dataProvider,
                    'pagerOptions' => [
                        'options' => ['class' => 'pagination pagination-sm mb-0 justify-content-end'],
                        'prevPageLabel' => '<i class="bi bi-chevron-left"></i>',
                        'nextPageLabel' => '<i class="bi bi-chevron-right"></i>',
                        'maxButtonCount' => 3,
                    ],
                ]) ?>
            </div>
            </div>
            <?php Pjax::end(); ?>
        </div>
    </div>
</div>
<?php
$this->registerJs(
    <<<'JS'
function initReceiveTooltips() {
    document.querySelectorAll('#receive-pjax [data-bs-toggle="tooltip"]').forEach(function(el) {
        new bootstrap.Tooltip(el);
    });
}
initReceiveTooltips();
$(document).on('pjax:end', '#receive-pjax', function() {
    initReceiveTooltips();
});
JS,
    \yii\web\View::POS_READY
);
$this->registerJs(
    <<<'JS'
yii.confirm = function (message, ok, cancel) {
    Swal.fire({
        title: 'ยืนยันการทำรายการ',
        html: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'ยืนยัน',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#b91c1c',
        cancelButtonColor: '#6c757d',
        reverseButtons: false
    }).then(function (result) {
        if (result.isConfirmed) {
            ok && ok();
        } else {
            cancel && cancel();
        }
    });
    return false;
};
JS,
    \yii\web\View::POS_READY
);
$this->registerCss(<<<CSS
.tooltip.rv-tip-pop .tooltip-arrow {
    display: none;
}
.tooltip.rv-tip-pop .tooltip-inner {
    max-width: 260px;
    background: #ffffff;
    color: #1a202c;
    border: 1px solid rgba(15, 23, 42, .08);
    border-radius: 8px;
    box-shadow: 0 6px 18px rgba(15, 23, 42, .06), 0 2px 4px rgba(15, 23, 42, .04);
    padding: 0;
    text-align: left;
    overflow: hidden;
}
.rv-tip__head {
    display: flex;
    align-items: center;
    gap: .4rem;
    font-size: .82rem;
    font-weight: 600;
    padding: .5rem .7rem .35rem;
}
.rv-tip__body {
    font-size: .78rem;
    font-weight: 400;
    color: #4a5568;
    line-height: 1.45;
    padding: 0 .7rem .6rem;
}
.rv-tip--warning .rv-tip__head {
    color: #b45309;
    background: rgba(180, 83, 9, .08);
}
.rv-tip--secondary .rv-tip__head {
    color: #4a5568;
    background: rgba(15, 23, 42, .05);
}
.rv-tip--danger .rv-tip__head {
    color: #b91c1c;
    background: rgba(185, 28, 28, .08);
}
CSS
);

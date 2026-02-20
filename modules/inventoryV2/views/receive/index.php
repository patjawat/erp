<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use yii\widgets\LinkPager;
use app\components\StatusBadgeHelper;

$totalCount = (int) $dataProvider->getTotalCount();
$pagination = $dataProvider->getPagination();
if ($pagination) {
    $summaryBegin = $totalCount > 0 ? $pagination->getOffset() + 1 : 0;
    $summaryEnd = $totalCount > 0 ? min($pagination->getOffset() + count($dataProvider->getModels()), $totalCount) : 0;
} else {
    $summaryBegin = $totalCount > 0 ? 1 : 0;
    $summaryEnd = $totalCount;
}

$this->title = 'รับเข้าวัสดุ';
$this->params['breadcrumbs'][] = ['label' => 'คลังสินค้า', 'url' => ['/inventoryV2']];
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
                <?= Html::a('<i class="bi bi-plus-circle me-1"></i> สร้างใบรับเข้า', ['create'], ['class' => 'btn btn-light btn-sm']) ?>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="p-3">
            <?php $form = ActiveForm::begin([
                'method' => 'get',
                'action' => Url::to(['index']),
                'options' => ['class' => 'row g-3 align-items-end w-100 m-0', 'id' => 'receive-search-form'],
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
                        <th class="text-nowrap bg-light bg-opacity-50">เลขที่เอกสาร</th>
                        <th class="text-nowrap bg-light bg-opacity-50">วันที่</th>
                        <th class="text-nowrap bg-light bg-opacity-50">คลัง</th>
                        <th class="text-end text-nowrap bg-light bg-opacity-50">รายการ</th>
                        <th class="text-end text-nowrap bg-light bg-opacity-50">มูลค่าที่รับเข้า</th>
                        <th class="text-center text-nowrap bg-light bg-opacity-50">สถานะ</th>
                        <th style="width: 110px" class="text-end bg-light bg-opacity-50"></th>
                    </tr>
                </thead>
                <tbody class="align-middle table-group-divider">
                <?php
                $pageTotalValue = 0.0;
                $models = $dataProvider->getModels();
                ?>
                <?php if (empty($models)): ?>
                    <tr>
                        <td colspan="7" class="text-center">
                            <div class="text-muted py-5"><i class="bi bi-inbox display-6 d-block mb-2"></i>ยังไม่มีใบรับเข้า</div>
                            <div class="pb-3"><?= Html::a('สร้างใบรับเข้า', ['create'], ['class' => 'btn btn-success btn-sm']) ?></div>
                        </td>
                    </tr>
                <?php else: ?>
                <?php foreach ($models as $item): ?>
                    <?php
                    $wh = $item->main_warehouse_id ? $item->mainWarehouse : null;
                    $warehouseName = $wh ? Html::encode($wh->warehouse_name) : '-';
                    $detailCount = number_format(count($item->stockDetails));
                    $rowTotal = 0;
                    foreach ($item->stockDetails as $d) {
                        $rowTotal += (float) $d->qty * (float) ($d->unit_price ?? 0);
                    }
                    $pageTotalValue += $rowTotal;
                    ?>
                    <tr>
                        <td><?= Html::a(Html::encode($item->order_no), ['view', 'id' => $item->id], ['class' => 'fw-semibold text-decoration-none']) ?></td>
                        <td class="text-secondary"><?= Yii::$app->formatter->asDate($item->order_date, 'php:d/m/Y') ?></td>
                        <td><?= $item->main_warehouse_id ? $warehouseName : '<span class="text-muted">-</span>' ?></td>
                        <td class="text-end text-muted"><?= $detailCount ?></td>
                        <td class="text-end"><span class="fw-semibold"><?= number_format($rowTotal, 2) ?></span> <span class="text-muted small">บาท</span></td>
                        <td class="text-center"><?= StatusBadgeHelper::renderStatusBadge($item->status, ['tooltip' => StatusBadgeHelper::getLabel($item->status)]) ?></td>
                        <td class="text-end">
                            <?= Html::a('<i class="bi bi-eye"></i>', ['view', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary border-0', 'title' => 'ดู']) ?>
                            <?php if ($item->status !== 'CANCELLED'): ?>
                                <?= Html::a('<i class="bi bi-pencil"></i>', ['update', 'id' => $item->id], ['class' => 'btn btn-sm btn-outline-secondary border-0', 'title' => 'แก้ไข']) ?>
                            <?php endif; ?>
                            <?php if ($item->status === 'CONFIRMED'): ?>
                                <?= Html::a('<i class="bi bi-x-circle"></i>', ['cancel', 'id' => $item->id], [
                                    'class' => 'btn btn-sm btn-outline-danger border-0',
                                    'title' => 'ยกเลิกใบรับเข้า',
                                    'data' => ['method' => 'post', 'confirm' => 'ยืนยันยกเลิกใบรับเข้านี้? ระบบจะหักยอดสต็อกคืน'],
                                ]) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 px-3 py-2 border-top bg-light bg-opacity-50">
                <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3">
                    <span class="text-muted small">แสดง <?= $summaryBegin ?>–<?= $summaryEnd ?> จาก <?= $totalCount ?> รายการ</span>
                    <?php if ($totalCount > 0): ?>
                        <span class="text-muted small">ผลรวมมูลค่าที่รับเข้า: <strong class="text-body"><?= number_format($pageTotalValue, 2) ?></strong> บาท</span>
                    <?php endif; ?>
                </div>
                <?= LinkPager::widget([
                    'pagination' => $dataProvider->getPagination(),
                    'options' => ['class' => 'pagination pagination-sm mb-0 justify-content-end'],
                    'prevPageLabel' => '<i class="bi bi-chevron-left"></i>',
                    'nextPageLabel' => '<i class="bi bi-chevron-right"></i>',
                    'maxButtonCount' => 3,
                ]) ?>
            </div>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerJs(
    <<<'JS'
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
    new bootstrap.Tooltip(el);
});
JS,
    \yii\web\View::POS_READY
);

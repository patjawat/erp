<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\components\ThaiDateHelper;
use app\modules\booking\models\Vehicle;
/** @var yii\web\View $this */
/** @var app\modules\booking\models\VehicleSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ทะเบียนใช้รถพยาบาล';
$this->params['breadcrumbs'][] = ['label' => 'ระบบงานยานพาหนะ', 'url' => ['/booking/vehicle/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-truck-medical"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php $this->beginBlock('sub-title'); ?>
ทะเบียนใช้รถพยาบาล
<?php $this->endBlock(); ?>
<?php echo $this->render('menu')?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('menu',['active' => 'ambulance'])?>
<?php $this->endBlock(); ?>


<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <h6 class="mb-3 fw-semibold"><i class="fa-solid fa-magnifying-glass me-2 text-primary"></i> การค้นหา</h6>
        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
</div>


<div class="card shadow-sm border-0 mb-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="m-0 fw-semibold">
                <i class="fa-solid fa-truck-medical me-2 text-danger"></i> คำขอรอจัดสรร
                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1 ms-1">
                    <?= number_format($dataProvider->getTotalCount(), 0) ?>
                </span>
                <span class="text-muted small">รายการ</span>
            </h6>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 vehicle-list-table">
            <thead class="vehicle-list-thead">
                <tr class="small text-muted">
                    <th scope="col" class="ps-3" style="width: 96px;">ลำดับ</th>
                    <th scope="col" style="width: 130px;">สถานะ</th>
                    <th scope="col" style="min-width: 220px;">ผู้ขอใช้รถ</th>
                    <th scope="col" class="d-none d-md-table-cell" style="min-width: 220px;">เหตุผล / ปลายทาง</th>
                    <th scope="col" class="d-none d-sm-table-cell" style="min-width: 180px;">วันเวลาที่ใช้รถ</th>
                    <th scope="col" class="d-none d-lg-table-cell" style="width: 110px;">ความเร่งด่วน</th>
                    <th scope="col" class="pe-3 text-center vehicle-actions-th">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                    <?php $queueNo = ($dataProvider->pagination->offset + 1) + $key; ?>
                    <tr>
                        <td class="ps-3">
                            <div class="text-primary fw-bold lh-sm">#<?= (int) $queueNo ?></div>
                            <div class="vlt-primary lh-sm" title="เลขที่คำขอ"><?= Html::encode($item->code) ?></div>
                        </td>

                        <td>
                            <?= $item->viewStatus()['view'] ?? '-' ?>
                        </td>

                        <td>
                            <div class="d-flex align-items-center">
                                <?= $item->userRequest()['avatar'] ?>
                            </div>
                        </td>

                        <td class="d-none d-md-table-cell">
                            <div class="vlt-primary text-truncate" style="max-width: 260px;" title="<?= Html::encode($item->reason) ?>">
                                <i class="fa-solid fa-circle-info text-primary me-1"></i><?= Html::encode($item->reason) ?>
                            </div>
                            <div class="vlt-secondary text-truncate mt-1" style="max-width: 260px;">
                                <i class="fa-solid fa-location-dot text-danger me-1"></i>
                                <?= $item->viewGoType() ?> · <?= Html::encode($item->locationOrg?->title ?? '-') ?>
                            </div>
                        </td>

                        <td class="d-none d-sm-table-cell">
                            <div class="vlt-primary">
                                <i class="fa-solid fa-calendar-day me-1 text-secondary"></i><?= $item->showDateRange() ?>
                            </div>
                            <div class="vlt-secondary">เวลา <?= $item->viewTime()['full'] ?></div>
                        </td>

                        <td class="d-none d-lg-table-cell">
                            <?= $item->viewUrgent() ?>
                        </td>

                        <td class="text-center align-middle vehicle-actions-cell px-2 px-md-3">
                            <div class="vehicle-actions-inner d-flex flex-row flex-wrap justify-content-center align-items-center gap-2">
                                <?= Html::a('<i class="fa-regular fa-pen-to-square"></i>',
                                    ['/booking/vehicle/approve', 'id' => $item->id, 'title' => '<i class="fa-regular fa-pen-to-square me-1"></i> แก้ไขข้มูลขอใช้รถ'],
                                    ['class' => 'btn btn-sm btn-warning open-modal', 'data' => ['size' => 'modal-lg'], 'title' => 'แก้ไข / อนุมัติ', 'aria-label' => 'แก้ไข / อนุมัติ']) ?>

                                <div class="dropdown flex-grow-1 flex-sm-grow-0">
                                    <button class="btn btn-sm btn-secondary dropdown-toggle action-dropdown-toggle w-100 w-sm-auto"
                                            type="button"
                                            data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            aria-label="เมนูเพิ่มเติม">
                                        <i class="fa-solid fa-angle-down"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                                        <li>
                                            <?= Html::a('<i class="fa-regular fa-circle-xmark me-2"></i>ยกเลิกรายการ',
                                                ['/booking/vehicle/cancel', 'id' => $item->id],
                                                ['class' => 'dropdown-item text-danger cancel-order', 'data' => ['size' => 'modal-lg']]) ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fa-solid fa-inbox fs-3 d-block mb-2"></i>
                            ไม่พบรายการขอใช้รถพยาบาล
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="body-footer p-2 border-top">
        <div class="d-flex justify-content-center">
            <?= yii\bootstrap5\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'class' => 'pagination pagination-sm mb-0',
                    ],
                ]); ?>
        </div>
    </div>
</div>

<style>
.vehicle-list-table thead.vehicle-list-thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background-color: var(--bs-tertiary-bg, #f8f9fa);
    color: var(--bs-secondary-color);
    font-size: 0.8125rem;
    font-weight: 600;
    letter-spacing: 0.04em;
    border-bottom: 1px solid var(--bs-border-color, #dee2e6);
    padding: .9rem 1rem;
    white-space: nowrap;
}
.vehicle-list-table tbody td {
    vertical-align: middle;
    padding: 1rem 1rem;
    font-size: 1rem;
    color: var(--bs-body-color);
    border-bottom: 1px solid var(--bs-border-color, #dee2e6);
}
.vehicle-list-table tbody tr:last-child td { border-bottom: none; }
.vehicle-list-table .vlt-primary { color: var(--bs-emphasis-color, #212529); font-weight: 600; }
.vehicle-list-table .vlt-secondary { font-size: .8125rem; color: var(--bs-secondary-color); }
.vehicle-list-table .vlt-meta { font-size: .75rem; color: var(--bs-secondary-color); }
.vehicle-list-table tbody tr {
    transition: background-color 150ms cubic-bezier(.16,1,.3,1);
}
.vehicle-list-table th.vehicle-actions-th,
.vehicle-list-table td.vehicle-actions-cell { width: 130px; white-space: nowrap; }

.vehicle-list-table .vehicle-actions-inner .btn {
    flex-shrink: 0;
    min-width: 2.375rem;
    min-height: 2.375rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}
.vehicle-list-table .vehicle-actions-inner .action-dropdown-toggle::after {
    display: none;
}
.vehicle-list-table .dropdown-menu {
    font-size: .9rem;
    min-width: 12rem;
    z-index: 1055;
}
.vehicle-list-table .dropdown-item { padding: .45rem .85rem; }
.vehicle-list-table .vehicle-actions-cell { position: relative; }
@media (prefers-reduced-motion: reduce) {
    .vehicle-list-table tbody tr { transition: none; }
}
</style>
<?php
$js = <<< JS
function initVehicleAmbulanceDropdowns() {
    if (!window.bootstrap || !window.bootstrap.Dropdown) return;
    document.querySelectorAll('.vehicle-list-table .action-dropdown-toggle').forEach(function (el) {
        var existing = bootstrap.Dropdown.getInstance(el);
        if (existing) {
            try { existing.dispose(); } catch (e) {}
        }
        new bootstrap.Dropdown(el, { popperConfig: { strategy: 'fixed' } });
    });
}
initVehicleAmbulanceDropdowns();
\$(document).off('pjax:success.vehicleAmbulanceDropdowns').on('pjax:success.vehicleAmbulanceDropdowns', initVehicleAmbulanceDropdowns);
JS;
$this->registerJS($js, \yii\web\View::POS_READY);
?>

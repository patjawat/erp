<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
?>

<?php Pjax::begin(['id' => $container, 'enablePushState' => false, 'timeout' => 50000]); ?>
<div class="card shadow-sm border-0">
    <div class="card-body pb-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="m-0 fw-semibold">
                <i class="fa-solid fa-calendar-days me-2 text-primary"></i><?= $title ?? '-' ?>
            </h6>
            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                <?= $showDate ?>
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 vehicle-events-table">
            <thead class="vehicle-events-thead">
                <tr class="small text-muted">
                    <th scope="col" class="ps-3" style="min-width: 220px;">เวลา / สถานที่</th>
                    <th scope="col" style="width: 72px;">พนักงานขับ</th>
                    <th scope="col" class="pe-3" style="min-width: 180px;">รถที่จัดสรร</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dataProvider->getModels() as $item): ?>
                    <tr>
                        <td class="ps-3">
                            <a href="<?= Url::to(['view', 'id' => $item->vehicle->id, 'title' => 'เลขที่#' . $item->vehicle->code]) ?>"
                               class="open-modal text-decoration-none" data-size="modal-lg">
                                <div class="fw-semibold small text-dark">
                                    <span class="text-danger me-1"><?= ($item->vehicle->viewTime()['full'] ?? '-') ?></span>
                                    <?php echo $item->vehicle->locationOrg?->title ?? '-' ?>
                                </div>
                                <div class="small text-muted text-truncate" style="max-width: 280px;">
                                    <?php echo $item->vehicle->reason ?? '-' ?>
                                </div>
                            </a>
                        </td>
                        <td>
                            <?= Html::img('@web/img/loading.gif', [
                                'class' => 'avatar-sm rounded-circle shadow-sm lazyload',
                                'data' => [
                                    'expand' => '-20',
                                    'sizes' => 'auto',
                                    'src' => $item->showDriver()['photo']
                                ]
                            ]); ?>
                        </td>
                        <td class="pe-3">
                            <div class="d-flex align-items-center gap-2">
                                <?php
                                try {
                                    echo $item->car
                                        ? Html::img($item->car?->ShowImg()['image'], ['class' => 'avatar rounded border-secondary'])
                                        : '';
                                } catch (\Throwable $th) {
                                    //throw $th;
                                }
                                ?>
                                <div class="d-flex flex-column">
                                    <span class="small text-dark"><?= $item->car?->data_json['brand']; ?></span>
                                    <span class="small fw-semibold text-primary"><?= $item->license_plate ?></span>
                                </div>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>

                <?php if (empty($dataProvider->getModels())): ?>
                    <tr>
                        <td colspan="3" class="text-center text-muted py-4">
                            <i class="fa-solid fa-inbox d-block mb-1"></i>
                            ไม่มีรายการในวันนี้
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="body-footer border-top">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap gap-2">
            <?= Html::a('<i class="fa-solid fa-list me-1"></i> ทะเบียนการจองทั้งหมด', ['//booking/vehicle/index'],
                ['class' => 'btn btn-primary btn-sm']) ?>
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'maxButtonCount' => 5,
                'options' => [
                    'class' => 'pagination pagination-sm mb-0',
                ],
            ]); ?>
        </div>
    </div>
</div>

<style>
.vehicle-events-table thead.vehicle-events-thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background-color: var(--bs-tertiary-bg, #f8f9fa);
    font-weight: 600;
    letter-spacing: 0.04em;
    border-bottom: 1px solid var(--bs-border-color, #dee2e6);
    padding-top: .55rem;
    padding-bottom: .55rem;
    white-space: nowrap;
}
.vehicle-events-table tbody td {
    vertical-align: middle;
    padding-top: .65rem;
    padding-bottom: .65rem;
    border-bottom: 1px solid var(--bs-border-color-translucent, rgba(0,0,0,.075));
}
.vehicle-events-table tbody tr {
    transition: background-color 150ms cubic-bezier(.16,1,.3,1);
}
@media (prefers-reduced-motion: reduce) {
    .vehicle-events-table tbody tr { transition: none; }
}
</style>
<?php Pjax::end(); ?>

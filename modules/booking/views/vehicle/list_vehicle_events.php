<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
?>

<?php Pjax::begin(['id' => $container, 'enablePushState' => false, 'timeout' => 50000]); ?>
<div class="card shadow-sm border-0 vehicle-events-card">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h6 class="m-0 fw-semibold small">
                <i class="bi bi-calendar-check me-1 text-primary"></i><?= $title ?? '-' ?>
            </h6>
            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill fw-medium px-2 py-1">
                <?= $showDate ?>
            </span>
        </div>
    </div>

    <ul class="list-group list-group-flush vehicle-events-list">
        <?php foreach ($dataProvider->getModels() as $item): ?>
            <?php
            $driver = $item->showDriver();
            $carImg = null;
            try {
                $carImg = $item->car?->ShowImg()['image'];
            } catch (\Throwable $th) {
                //throw $th;
            }
            ?>
            <li class="list-group-item px-3 py-2">
                <a href="<?= Url::to(['view', 'id' => $item->vehicle->id, 'title' => 'เลขที่#' . $item->vehicle->code]) ?>"
                   class="open-modal text-decoration-none d-block" data-size="modal-lg">
                    <div class="d-flex align-items-center gap-2">
                        <span class="text-danger fw-semibold fs-12 text-nowrap"><?= $item->vehicle->viewTime()['full'] ?? '-' ?></span>
                        <span class="small fw-semibold text-dark text-truncate">
                            <?= $item->vehicle->locationOrg?->title ?? '-' ?>
                        </span>
                    </div>

                    <div class="fs-12 text-muted text-truncate" title="<?= Html::encode($item->vehicle->reason ?? '') ?>">
                        <?= $item->vehicle->reason ?? '-' ?>
                    </div>

                    <div class="d-flex align-items-center gap-3 mt-1">
                        <span class="d-inline-flex align-items-center gap-1 text-truncate">
                            <?= Html::img('@web/img/loading.gif', [
                                'class' => 'vehicle-events-avatar rounded-circle lazyload flex-shrink-0',
                                'data' => [
                                    'expand' => '-20',
                                    'sizes' => 'auto',
                                    'src' => $driver['photo'],
                                ],
                                'alt' => $driver['fullname'],
                            ]) ?>
                            <span class="fs-12 text-muted text-truncate"><?= Html::encode($driver['fullname'] ?: 'ยังไม่ระบุ พขร.') ?></span>
                        </span>

                        <span class="d-inline-flex align-items-center gap-1 ms-auto text-nowrap"
                              title="<?= Html::encode($item->car?->data_json['brand'] ?? '') ?>">
                            <?php if ($carImg): ?>
                                <?= Html::img($carImg, ['class' => 'vehicle-events-car rounded flex-shrink-0', 'alt' => 'รถยนต์']) ?>
                            <?php else: ?>
                                <i class="bi bi-car-front text-muted"></i>
                            <?php endif; ?>
                            <span class="fs-12 fw-semibold text-primary"><?= $item->license_plate ?: '-' ?></span>
                        </span>
                    </div>
                </a>
            </li>
        <?php endforeach; ?>

        <?php if (empty($dataProvider->getModels())): ?>
            <li class="list-group-item text-center text-muted small py-3">
                <i class="bi bi-inbox me-1"></i>
                ไม่มีรายการ
            </li>
        <?php endif; ?>
    </ul>

    <div class="body-footer border-top">
        <div class="d-flex justify-content-between align-items-center p-2 flex-wrap gap-2">
            <?= Html::a('<i class="bi bi-list-ul me-1"></i> ทะเบียนการจองทั้งหมด', ['//booking/vehicle/index'],
                ['class' => 'btn btn-primary btn-sm']) ?>
            <?= yii\bootstrap5\LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'maxButtonCount' => 3,
                'options' => [
                    'class' => 'pagination pagination-sm mb-0',
                ],
            ]); ?>
        </div>
    </div>
</div>

<style>
.vehicle-events-list .list-group-item {
    transition: background-color 150ms cubic-bezier(.16,1,.3,1);
}
.vehicle-events-list .list-group-item:hover {
    background-color: var(--bs-tertiary-bg, #f8f9fa);
}
/* ให้ข้อความยาวถูกตัดแทนที่จะดันแถวจนล้นออกนอกการ์ด */
.vehicle-events-list .text-truncate {
    min-width: 0;
}
.vehicle-events-avatar {
    width: 20px;
    height: 20px;
    object-fit: cover;
}
.vehicle-events-car {
    width: 26px;
    height: 20px;
    object-fit: cover;
}
@media (prefers-reduced-motion: reduce) {
    .vehicle-events-list .list-group-item { transition: none; }
}
</style>
<?php Pjax::end(); ?>

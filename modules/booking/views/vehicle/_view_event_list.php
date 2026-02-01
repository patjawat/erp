<?php

/** @var app\models\Vehicle[] $models */

use yii\helpers\Html;
use yii\helpers\Url;
?>

<div class="view-event-list px-2">
    <?php if (empty($models)): ?>
        <div class="text-center py-5">
            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
            <p class="mt-3 text-muted fw-bold">ไม่พบข้อมูลการใช้รถในวันที่เลือก</p>
        </div>
    <?php else: ?>
        <div class="row g-2"> 
            <?php foreach ($models as $model): ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm hover-card" style="border-radius: 12px; background: #f8f9fa;">
                        <div class="card-body p-3">

                            <div class="mb-2 d-flex justify-content-start">
                                <span class="badge bg-secondary bg-opacity-10 text-secondary border-0 fw-normal" style="font-size: 0.7rem;">
                                    <i class="bi bi-hash"></i> <?= Html::encode($model->code ?? $model->id) ?>
                                </span>
                            </div>

                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div class="d-flex align-items-center">
                                    <div class="time-badge me-2 text-center">
                                        <div class="fw-black text-primary" style="font-size: 1rem; line-height: 1;">
                                            <?= Html::encode($model->time_start) ?>
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.65rem;">- <?= Html::encode($model->time_end) ?></div>
                                    </div>
                                    <div class="d-flex align-items-center border-start ps-2">
                                        <img src="<?php echo $model->userRequest()['photo']; ?>"
                                            class="rounded-circle me-2 border shadow-sm"
                                            style="width: 32px; height: 32px; object-fit: cover;">
                                        <div>
                                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.85rem;"><?php echo $model->userRequest()['fullname']; ?></h6>
                                            <small class="text-primary" style="font-size: 0.75rem;"><i class="bi bi-telephone-fill me-1"></i><?php echo $model->data_json['phone'] ?? '-'; ?></small>
                                        </div>
                                    </div>
                                </div>
                                <div class="status-wrapper scale-85">
                                    <?= $model->viewStatus()['view'] ?>
                                </div>
                            </div>

                            <div class="p-2 bg-white rounded-3 border mb-2">
                                <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?= Html::encode($model->locationOrg->title ?? 'ไม่ระบุสถานที่') ?>
                                </div>
                                <div class="text-muted" style="font-size: 0.8rem; padding-left: 1.2rem;">
                                    <?= Html::encode($model->reason) ?>
                                </div>
                            </div>
                            
                            <?php if (($model->created_by == Yii::$app->user->id) || Yii::$app->user->can('driver')): ?>
                                <div class="btn-group border rounded bg-white shadow-sm" role="group">
                                    <?= Html::a('<i class="bi bi-pencil-square text-warning"></i> แก้ไขการจอง', ['update', 'id' => $model->id,'title' => 'แก้ไขการจอง'], [
                                        'class' => 'btn btn-sm btn-action open-modal',
                                        'title' => 'แก้ไขการจอง',
                                        'data-size' => 'modal-lg'
                                        ]) ?>
                                    <?= Html::a('<i class="fa-regular fa-circle-xmark"></i> ยกเลิกการจอง', ['/booking/vehicle/cancel', 'id' => $model->id], [
                                        'class' => 'btn btn-sm btn-cancel-booking',
                                        'title' => 'ยกเลิกการจอง',
                                        'style' => 'text-decoration: none; padding: 0.25rem 0.5rem; color: #dc3545;',
                                        'data' => [
                                            'confirm' => 'คุณแน่ใจหรือไม่ว่าต้องการยกเลิกการนี้?',
                                            'method' => 'post',
                                            ],
                                            ]) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="card-footer" style="background: #f8f9fa;">
                            <?php foreach ($model->vehicleDetails ?? [] as $index => $detail): ?>
                                <div class="row align-items-center <?= $index > 0 ? 'mt-3 pt-3 border-top' : '' ?>">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center">
                                            <div class="me-3">
                                                <?= Html::img($detail->driver?->showAvatar(), ['class' => 'rounded-circle border', 'style' => 'width: 40px; height: 40px; object-fit: cover;']) ?>
                                            </div>
                                            <div>
                                                <div class="d-flex flex-row gap-2 align-items-center">
                                                    <small class="text-muted d-block" style="font-size: 0.7rem;">พนักงานขับรถที่ได้รับจัดสรร</small> <?= $detail->viewStatus()['view'] ?>
                                                </div>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-bold" style="font-size: 0.85rem;"><?= $detail->driver?->fullname ?? 'ยังไม่ได้จัดสรร พขร.' ?></span>
                                                    <small class="text-muted" style="font-size: 0.75rem;">โทร : <?= $detail->driver?->phone ?? '-' ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3 border-start">
                                        <div class="d-flex align-items-center gap-2">
                                            <?php
                                            try {
                                                echo $detail->car ? Html::img($detail->car?->ShowImg()['image'], ['class' => 'rounded border', 'style' => 'width: 40px; height: 30px; object-fit: cover;']) : '';
                                            } catch (\Throwable $th) {}
                                            ?>
                                            <div class="avatar-detail">
                                                <div class="d-flex flex-column">
                                                    <p class="mb-0 fw-bold" style="font-size: 0.75rem;"><?= $detail->car?->data_json['brand'] ?? '-'; ?></p>
                                                    <p class="mb-0 text-primary fw-bold" style="font-size: 0.75rem;"><?= $detail->license_plate ?></p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-3 text-end">
                                        <?php if (Yii::$app->user->can('driver')): ?>
                                            <?= Html::a('<i class="fa-solid fa-key"></i> บันทึกภารกิจ', ['/booking/vehicle/work-update', 'id' => $detail->id, 'title' => '<i class="fa-regular fa-pen-to-square"></i> บันทึกภาระกิจการใช้รถยนต์'], ['class' => 'btn btn-outline-warning btn-sm rounded-pill px-3 shadow-sm open-modal', 'data' => ['size' => 'modal-lg']]) ?>
                                        <?php else: ?>
                                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" disabled><i class="fa-solid fa-key"></i> บันทึกภารกิจ</button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .hover-card { transition: all 0.2s ease; border: 1px solid transparent !important; }
    .hover-card:hover { border-color: var(--bs-primary) !important; background: #fff !important; }
    .time-badge { min-width: 65px; }
    .fw-black { font-weight: 800; }
    .btn-action { padding: 0.25rem 0.5rem; color: #6c757d; border: none; text-decoration: none; }
    .btn-action:hover { background: #f8f9fa; color: #333; }
    .btn-cancel-booking:hover { background: #fff5f5; }
    .scale-85 { transform: scale(0.85); transform-origin: right top; }
    @media (max-width: 576px) { .view-event-list .card-body { padding: 0.75rem; } }
</style>
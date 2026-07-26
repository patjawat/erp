<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\housing\models\Building;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'ทะเบียนบ้านพักและแฟลต';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'building']) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3">
    <?php foreach (['success', 'warning', 'error'] as $type): if (Yii::$app->session->hasFlash($type)): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div>
    <?php endif; endforeach; ?>
    <?php if (($responsibleAttentionCount ?? 0) > 0): ?>
        <div class="alert alert-warning d-flex gap-2 align-items-start" role="alert">
            <i data-lucide="triangle-alert" class="flex-shrink-0 mt-1" style="width:18px;height:18px"></i>
            <div>
                <div class="fw-semibold">มี <?= number_format($responsibleAttentionCount) ?> รายการที่ต้องกำหนดผู้รับผิดชอบ</div>
                <div class="small">บ้านพักที่ยังไม่มีผู้รับผิดชอบ หรือผู้รับผิดชอบย้าย ลาออก หรือสิ้นสุดการปฏิบัติงาน จะแสดงคำเตือนในรายการ</div>
            </div>
        </div>
    <?php endif; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-body d-flex align-items-center justify-content-between gap-2">
            <div><div class="fw-semibold">บ้านพักและแฟลต</div><div class="small text-body-secondary">ข้อมูลอาคารหลักก่อนแบ่งชั้นและยูนิต</div></div>
            <?= Html::a('<i data-lucide="plus"></i> เพิ่มรายการ', ['create', 'title' => 'เพิ่มบ้านพัก/แฟลต'], ['class' => 'btn btn-primary btn-sm open-modal', 'data-size' => 'modal-lg']) ?>
        </div>
        <?php Pjax::begin(['id' => 'housing-building-container', 'enablePushState' => false]); ?>
        <div class="card-body p-0">
            <div class="d-none d-lg-block">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th style="width:72px">รูปภาพ</th><th>รหัส</th><th>ชื่อ</th><th>ผู้รับผิดชอบ</th><th>ประเภท</th><th class="text-end">ชั้น</th><th class="text-end">ยูนิต</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
                    <tbody>
                    <?php foreach ($dataProvider->models as $model): ?>
                        <tr>
                            <td>
                                <?php $buildingImage = $buildingImages[$model->ref] ?? null; ?>
                                <?php if ($buildingImage !== null): ?>
                                    <?= Html::img(FileManagerHelper::getImg($buildingImage->id), [
                                        'class' => 'rounded-2 border object-fit-cover',
                                        'style' => 'width:52px;height:40px',
                                        'alt' => 'รูป ' . $model->name,
                                        'loading' => 'lazy',
                                    ]) ?>
                                <?php else: ?>
                                    <div class="rounded-2 bg-body-tertiary d-flex align-items-center justify-content-center text-secondary" style="width:52px;height:40px" aria-label="ยังไม่มีรูปภาพ">
                                        <i data-lucide="image" style="width:18px;height:18px"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="fw-semibold"><?= Html::encode($model->code) ?></td>
                            <td>
                                <?= Html::encode($model->name) ?>
                                <?php if ($model->floors !== []): ?>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <?php foreach ($model->floors as $floor): ?>
                                            <?= Html::a(
                                                '<i data-lucide="pencil" style="width:12px;height:12px"></i> ' . Html::encode($floor->name),
                                                ['update-floor', 'id' => $floor->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-secondary open-modal',
                                                    'data-size' => 'modal-lg',
                                                    'title' => 'แก้ไข ' . $floor->name,
                                                ]
                                            ) ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($model->hasActiveResponsibleEmployee()): ?>
                                    <div class="fw-semibold"><?= Html::encode($model->responsibleEmployee->fullname()) ?></div>
                                    <div class="small text-body-secondary"><?= Html::encode($model->responsibleEmployee->positionName()) ?></div>
                                <?php else: ?>
                                    <div class="text-warning-emphasis fw-semibold">
                                        <?= Html::encode($model->responsibleStatusLabel()) ?>
                                    </div>
                                    <?php if ($model->responsibleEmployee !== null): ?>
                                        <div class="small text-body-secondary"><?= Html::encode($model->responsibleEmployee->fullname()) ?></div>
                                    <?php endif; ?>
                                    <?= Html::a('กำหนดใหม่', ['update', 'id' => $model->id, 'title' => 'กำหนดผู้รับผิดชอบ'], [
                                        'class' => 'btn btn-sm btn-outline-warning open-modal mt-1',
                                        'data-size' => 'modal-lg',
                                    ]) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= Html::encode(Building::typeOptions()[$model->building_type] ?? $model->building_type) ?></td>
                            <td class="text-end"><?= number_format(count($model->floors)) ?></td>
                            <td class="text-end"><?= number_format(count($model->units)) ?></td>
                            <td><span class="badge <?= $model->status === Building::STATUS_ACTIVE ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>"><?= Html::encode(Building::statusOptions()[$model->status] ?? $model->status) ?></span></td>
                            <td class="text-end">
                                <?= Html::a('เพิ่มชั้น', ['create-floor', 'building_id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?>
                                <?= Html::a('แก้ไข', ['update', 'id' => $model->id, 'title' => 'แก้ไขบ้านพัก/แฟลต'], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data-size' => 'modal-lg']) ?>
                                <?= Html::a('<i data-lucide="eye" style="width:16px;height:16px"></i><span class="visually-hidden">รายละเอียด</span>', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-info', 'title' => 'ดูรายละเอียด', 'aria-label' => 'ดูรายละเอียด ' . $model->name]) ?>
                                <?= Html::a('ลบ', ['delete', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการลบรายการนี้?']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <ul class="list-group list-group-flush d-lg-none" role="list">
                <?php foreach ($dataProvider->models as $model): ?>
                    <li class="list-group-item py-3">
                        <div class="d-flex gap-3">
                            <?php $buildingImage = $buildingImages[$model->ref] ?? null; ?>
                            <?php if ($buildingImage !== null): ?>
                                <?= Html::img(FileManagerHelper::getImg($buildingImage->id), [
                                    'class' => 'rounded-2 border object-fit-cover flex-shrink-0',
                                    'style' => 'width:72px;height:54px',
                                    'alt' => 'รูป ' . $model->name,
                                    'loading' => 'lazy',
                                ]) ?>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between gap-2"><strong><?= Html::encode($model->code . ' · ' . $model->name) ?></strong><span><?= Html::encode(Building::typeOptions()[$model->building_type] ?? '') ?></span></div>
                                <div class="small text-body-secondary mt-1"><?= number_format(count($model->floors)) ?> ชั้น · <?= number_format(count($model->units)) ?> ยูนิต</div>
                                <?php if ($model->hasActiveResponsibleEmployee()): ?>
                                    <div class="small mt-1">ผู้รับผิดชอบ: <?= Html::encode($model->responsibleEmployee->fullname()) ?></div>
                                <?php else: ?>
                                    <div class="small text-warning-emphasis fw-semibold mt-1"><?= Html::encode($model->responsibleStatusLabel()) ?></div>
                                    <?= Html::a('กำหนดผู้รับผิดชอบใหม่', ['update', 'id' => $model->id, 'title' => 'กำหนดผู้รับผิดชอบ'], [
                                        'class' => 'btn btn-sm btn-outline-warning open-modal mt-2',
                                        'data-size' => 'modal-lg',
                                    ]) ?>
                                <?php endif; ?>
                                <?php if ($model->floors !== []): ?>
                                    <div class="d-flex flex-wrap gap-1 mt-2">
                                        <?php foreach ($model->floors as $floor): ?>
                                            <?= Html::a(
                                                'แก้ไข ' . Html::encode($floor->name),
                                                ['update-floor', 'id' => $floor->id],
                                                [
                                                    'class' => 'btn btn-sm btn-outline-secondary open-modal',
                                                    'data-size' => 'modal-lg',
                                                ]
                                            ) ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex flex-wrap gap-2 mt-3">
                                    <?= Html::a('เพิ่มชั้น', ['create-floor', 'building_id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?>
                                    <?= Html::a('แก้ไข', ['update', 'id' => $model->id, 'title' => 'แก้ไขบ้านพัก/แฟลต'], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data-size' => 'modal-lg']) ?>
                                    <?= Html::a('<i data-lucide="eye" style="width:16px;height:16px"></i> รายละเอียด', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
                                </div>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($dataProvider->totalCount === 0): ?><div class="text-center py-5"><div class="fw-semibold">ยังไม่มีข้อมูลบ้านพักหรือแฟลต</div><div class="text-body-secondary small mt-1">เพิ่มอาคารแรกเพื่อเริ่มจัดวางยูนิตและห้อง</div></div><?php endif; ?>
        </div>
        <div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
        <?php Pjax::end(); ?>
    </div>
</div>

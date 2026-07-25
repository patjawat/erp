<?php

use app\components\widgets\DataSummaryWidget;
use app\modules\housing\models\Building;
use yii\helpers\Html;
use yii\widgets\Pjax;

$this->title = 'ทะเบียนบ้านพักและแฟลต';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'building']) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3">
    <?php foreach (['success', 'error'] as $type): if (Yii::$app->session->hasFlash($type)): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?>"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div>
    <?php endif; endforeach; ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-body d-flex align-items-center justify-content-between gap-2">
            <div><div class="fw-semibold">บ้านพักและแฟลต</div><div class="small text-muted">ข้อมูลอาคารหลักก่อนแบ่งชั้นและยูนิต</div></div>
            <?= Html::a('<i data-lucide="plus"></i> เพิ่มรายการ', ['create', 'title' => 'เพิ่มบ้านพัก/แฟลต'], ['class' => 'btn btn-primary btn-sm open-modal', 'data-size' => 'modal-lg']) ?>
        </div>
        <?php Pjax::begin(['id' => 'housing-building-container', 'enablePushState' => false]); ?>
        <div class="card-body p-0">
            <div class="d-none d-lg-block">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>รหัส</th><th>ชื่อ</th><th>ประเภท</th><th class="text-end">ชั้น</th><th class="text-end">ยูนิต</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead>
                    <tbody>
                    <?php foreach ($dataProvider->models as $model): ?>
                        <tr>
                            <td class="fw-semibold"><?= Html::encode($model->code) ?></td>
                            <td><?= Html::encode($model->name) ?></td>
                            <td><?= Html::encode(Building::typeOptions()[$model->building_type] ?? $model->building_type) ?></td>
                            <td class="text-end"><?= number_format(count($model->floors)) ?></td>
                            <td class="text-end"><?= number_format(count($model->units)) ?></td>
                            <td><span class="badge <?= $model->status === Building::STATUS_ACTIVE ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>"><?= Html::encode(Building::statusOptions()[$model->status] ?? $model->status) ?></span></td>
                            <td class="text-end">
                                <?= Html::a('เพิ่มชั้น', ['create-floor', 'building_id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?>
                                <?= Html::a('แก้ไข', ['update', 'id' => $model->id, 'title' => 'แก้ไขบ้านพัก/แฟลต'], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data-size' => 'modal-lg']) ?>
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
                        <div class="d-flex justify-content-between gap-2"><strong><?= Html::encode($model->code . ' · ' . $model->name) ?></strong><span><?= Html::encode(Building::typeOptions()[$model->building_type] ?? '') ?></span></div>
                        <div class="small text-muted mt-1"><?= number_format(count($model->floors)) ?> ชั้น · <?= number_format(count($model->units)) ?> ยูนิต</div>
                    </li>
                <?php endforeach; ?>
            </ul>
            <?php if ($dataProvider->totalCount === 0): ?><div class="text-center py-5"><div class="fw-semibold">ยังไม่มีข้อมูลบ้านพักหรือแฟลต</div><div class="text-muted small mt-1">เพิ่มอาคารแรกเพื่อเริ่มจัดวางยูนิตและห้อง</div></div><?php endif; ?>
        </div>
        <div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
        <?php Pjax::end(); ?>
    </div>
</div>

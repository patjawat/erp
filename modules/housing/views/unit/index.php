<?php
use app\components\widgets\DataSummaryWidget;
use app\modules\housing\models\Unit;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'ยูนิตและห้องพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'unit']) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3">
<div class="card border-0 shadow-sm">
<div class="card-header bg-body d-flex justify-content-between align-items-center"><div><div class="fw-semibold">ยูนิตและห้อง</div><div class="small text-muted">รองรับบ้านพักทั้งหลัง แฟลตครอบครัว และแฟลตโสดสองห้อง</div></div><?= Html::a('<i data-lucide="plus"></i> เพิ่มยูนิต', ['create', 'title' => 'เพิ่มยูนิต'], ['class' => 'btn btn-primary btn-sm open-modal', 'data-size' => 'modal-xl']) ?></div>
<?php Pjax::begin(['id' => 'housing-unit-container', 'enablePushState' => false]); ?>
<div class="card-body p-0">
<div class="d-none d-lg-block"><table class="table table-hover align-middle mb-0"><thead><tr><th>ยูนิต</th><th>อาคาร/ชั้น</th><th>รูปแบบ</th><th>ห้อง</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead><tbody>
<?php foreach ($dataProvider->models as $model): ?><tr>
<td><strong><?= Html::encode($model->code) ?></strong><div class="small text-muted"><?= Html::encode($model->name) ?></div></td>
<td><?= Html::encode($model->building->name ?? '—') ?><div class="small text-muted"><?= Html::encode($model->floor->name ?? 'ไม่ระบุชั้น') ?></div></td>
<td><?= Html::encode(Unit::modeOptions()[$model->occupancy_mode] ?? $model->occupancy_mode) ?></td>
<td><?php if ($model->rooms): foreach ($model->rooms as $room): ?><span class="badge bg-body-secondary text-body me-1"><?= Html::encode($room->code) ?></span><?php endforeach; else: ?><span class="text-muted">—</span><?php endif; ?></td>
<td><span class="badge bg-body-secondary text-body"><?= Html::encode(Unit::statusOptions()[$model->status] ?? $model->status) ?></span></td>
<td class="text-end"><?= Html::a('เพิ่มห้อง', ['create-room', 'unit_id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?> <?= Html::a('แก้ไข', ['update', 'id' => $model->id, 'title' => 'แก้ไขยูนิต'], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data-size' => 'modal-xl']) ?></td>
</tr><?php endforeach; ?></tbody></table></div>
<ul class="list-group list-group-flush d-lg-none"><?php foreach ($dataProvider->models as $model): ?><li class="list-group-item py-3"><div class="d-flex justify-content-between"><strong><?= Html::encode($model->code . ' · ' . $model->name) ?></strong><span><?= Html::encode(Unit::statusOptions()[$model->status] ?? '') ?></span></div><div class="small text-muted mt-1"><?= Html::encode($model->building->name ?? '') ?> · <?= count($model->rooms) ?> ห้อง</div></li><?php endforeach; ?></ul>
<?php if (!$dataProvider->totalCount): ?><div class="text-center py-5"><div class="fw-semibold">ยังไม่มียูนิต</div><div class="small text-muted mt-1">เพิ่มยูนิตภายในบ้านพักหรือแฟลตเพื่อเริ่มจัดห้อง</div></div><?php endif; ?>
</div><div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
<?php Pjax::end(); ?></div></div>

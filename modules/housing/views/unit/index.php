<?php
use app\components\widgets\DataSummaryWidget;
use app\modules\housing\models\Occupancy;
use app\modules\housing\models\Unit;
use yii\helpers\Html;
use yii\widgets\Pjax;
$this->title = 'ห้องพักและห้องย่อย';

/**
 * รวมรายชื่อผู้พักของห้อง (ทั้งห้อง + รายห้องย่อย) เป็นบรรทัดพร้อมป้ายกำกับ
 * @return list<array{label:string,occ:Occupancy}>
 */
$occupantLines = static function (Unit $unit) use ($occupants): array {
    $map = $occupants[$unit->id] ?? [];
    $lines = [];
    foreach ($map[0] ?? [] as $occ) {
        $lines[] = ['label' => '', 'occ' => $occ];
    }
    foreach ($unit->rooms as $room) {
        foreach ($map[$room->id] ?? [] as $occ) {
            $lines[] = ['label' => $room->code, 'occ' => $occ];
        }
    }
    return $lines;
};
$occupantName = static fn(Occupancy $occ): string => $occ->employee?->fullname() ?: 'บุคลากร #' . $occ->emp_id;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'unit']) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3">
<?php foreach (['success', 'warning', 'error'] as $type): if (Yii::$app->session->hasFlash($type)): ?>
<div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>" role="alert"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div>
<?php endif; endforeach; ?>
<div class="card border-0 shadow-sm">
<div class="card-header bg-body d-flex justify-content-between align-items-center"><div><div class="fw-semibold">ห้องพักและห้องย่อย</div><div class="small text-body-secondary">รองรับบ้านพักทั้งหลัง แฟลตครอบครัว และแฟลตโสดสองห้องย่อย</div></div><?= Html::a('<i data-lucide="plus"></i> เพิ่มห้อง', ['create', 'title' => 'เพิ่มห้อง'], ['class' => 'btn btn-primary btn-sm open-modal', 'data-size' => 'modal-xl']) ?></div>
<?php Pjax::begin(['id' => 'housing-unit-container', 'enablePushState' => false]); ?>
<div class="card-body p-0">
<div class="d-none d-lg-block"><table class="table table-hover align-middle mb-0"><thead><tr><th>ห้อง</th><th>อาคาร/ชั้น</th><th>รูปแบบ</th><th>ห้องย่อย</th><th>ผู้พัก</th><th>สถานะ</th><th class="text-end">จัดการ</th></tr></thead><tbody>
<?php foreach ($dataProvider->models as $model): ?><tr>
<td><strong><?= Html::encode($model->code) ?></strong><div class="small text-body-secondary"><?= Html::encode($model->name) ?></div></td>
<td><?= Html::encode($model->building->name ?? '—') ?><div class="small text-body-secondary"><?= Html::encode($model->floor->name ?? 'ไม่ระบุชั้น') ?></div></td>
<td><?= Html::encode(Unit::modeOptions()[$model->occupancy_mode] ?? $model->occupancy_mode) ?></td>
<td><?php if ($model->rooms): foreach ($model->rooms as $room): ?><?= Html::a(Html::encode($room->code), ['view', 'id' => $model->id, 'room_id' => $room->id], ['class' => 'badge bg-body-secondary text-body text-decoration-none me-1', 'title' => 'ดูรายละเอียดห้องย่อย']) ?><?php endforeach; else: ?><span class="text-body-secondary">—</span><?php endif; ?></td>
<td><?php $lines = $occupantLines($model); if ($lines): foreach ($lines as $line): $occ = $line['occ']; ?><div class="small text-nowrap"><?php if ($line['label']): ?><span class="badge bg-body-secondary text-body me-1"><?= Html::encode($line['label']) ?></span><?php endif; ?><i data-lucide="user" style="width:14px;height:14px" class="text-body-secondary me-1"></i><?= Html::encode($occupantName($occ)) ?><?php if ($occ->status === Occupancy::STATUS_ALLOCATED): ?> <span class="badge bg-warning-subtle text-warning-emphasis">รอเข้าอยู่</span><?php endif; ?></div><?php endforeach; else: ?><span class="text-body-secondary">—</span><?php endif; ?></td>
<td><span class="badge bg-body-secondary text-body"><?= Html::encode(Unit::statusOptions()[$model->status] ?? $model->status) ?></span></td>
<td class="text-end"><?= Html::a('รายละเอียด', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-info']) ?> <?= Html::a('เพิ่มห้องย่อย', ['create-room', 'unit_id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?> <?= Html::a('แก้ไข', ['update', 'id' => $model->id, 'title' => 'แก้ไขห้อง'], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'data-size' => 'modal-xl']) ?></td>
</tr><?php endforeach; ?></tbody></table></div>
<ul class="list-group list-group-flush d-lg-none"><?php foreach ($dataProvider->models as $model): ?><li class="list-group-item py-3"><div class="d-flex justify-content-between"><strong><?= Html::encode($model->code . ' · ' . $model->name) ?></strong><span><?= Html::encode(Unit::statusOptions()[$model->status] ?? '') ?></span></div><div class="small text-body-secondary mt-1"><?= Html::encode($model->building->name ?? '') ?> · <?= count($model->rooms) ?> ห้องย่อย</div><?php $lines = $occupantLines($model); if ($lines): ?><div class="small mt-1"><?php foreach ($lines as $line): $occ = $line['occ']; ?><div><i data-lucide="user" style="width:14px;height:14px" class="text-body-secondary me-1"></i><?php if ($line['label']): ?><span class="text-body-secondary"><?= Html::encode($line['label']) ?>:</span> <?php endif; ?><?= Html::encode($occupantName($occ)) ?><?php if ($occ->status === Occupancy::STATUS_ALLOCATED): ?> <span class="text-warning-emphasis">(รอเข้าอยู่)</span><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?><div class="mt-2"><?= Html::a('ดูรายละเอียด', ['view', 'id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?></div></li><?php endforeach; ?></ul>
<?php if (!$dataProvider->totalCount): ?><div class="text-center py-5"><div class="fw-semibold">ยังไม่มีห้อง</div><div class="small text-body-secondary mt-1">เพิ่มห้องภายในบ้านพักหรือแฟลตเพื่อเริ่มจัดห้องย่อย</div></div><?php endif; ?>
</div><div class="card-footer bg-body"><?= DataSummaryWidget::widget(['dataProvider' => $dataProvider]) ?></div>
<?php Pjax::end(); ?></div></div>

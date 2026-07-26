<?php

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\housing\models\AssetAssignment;
use app\modules\housing\models\Handover;
use yii\helpers\Html;

$occupancy = $model->occupancy;
$requestId = $occupancy?->request_id;
$locationName = implode(' / ', array_filter([
    $occupancy?->unit?->building?->name,
    $occupancy?->unit?->floor?->name,
    $occupancy?->unit?->name,
    $occupancy?->room?->name,
]));
$this->title = 'เอกสารรับมอบ ' . $model->handover_no;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'request']) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3" style="max-width:1120px">
<?php foreach (['success', 'warning', 'error'] as $type): if (Yii::$app->session->hasFlash($type)): ?><div class="alert alert-<?= $type === 'error' ? 'danger' : $type ?>"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div><?php endif; endforeach; ?>
<div class="d-flex flex-wrap justify-content-between gap-2 mb-3">
    <?= Html::a('<i data-lucide="arrow-left"></i> กลับไปคำขอ', ['/housing/request/view', 'id' => $requestId], ['class' => 'btn btn-outline-secondary']) ?>
    <div class="d-flex gap-2">
        <?php if ($model->status === Handover::STATUS_DRAFT && !$model->handed_over_signed_at): ?><?= Html::a('<i data-lucide="pencil"></i> แก้ไขข้อมูลตรวจรับ', ['prepare', 'request_id' => $requestId], ['class' => 'btn btn-outline-primary']) ?><?php endif; ?>
        <?= Html::a('<i data-lucide="printer"></i> พิมพ์เอกสาร', ['print', 'id' => $model->id], ['class' => 'btn btn-outline-secondary', 'target' => '_blank']) ?>
    </div>
</div>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-body d-flex justify-content-between align-items-center"><strong>ข้อมูลเอกสาร</strong><span class="badge <?= $model->status === Handover::STATUS_CONFIRMED ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis' ?>"><?= Html::encode(Handover::statusOptions()[$model->status]) ?></span></div>
    <div class="card-body"><dl class="row mb-0">
        <dt class="col-sm-3">เลขที่เอกสาร</dt><dd class="col-sm-9"><strong><?= Html::encode($model->handover_no) ?></strong></dd>
        <dt class="col-sm-3">บ้านพัก/ห้องพัก</dt><dd class="col-sm-9"><?= Html::encode($locationName) ?></dd>
        <dt class="col-sm-3">ผู้รับมอบ</dt><dd class="col-sm-9"><?= Html::encode($model->received_by_name) ?></dd>
        <dt class="col-sm-3">วันที่รับมอบ</dt><dd class="col-sm-9"><?= Yii::$app->formatter->asDate($model->handover_date, 'php:d/m/Y') ?></dd>
        <dt class="col-sm-3">เลขมิเตอร์ไฟฟ้า</dt><dd class="col-sm-9"><?= $model->electric_meter_value === null ? 'ไม่ระบุ' : Yii::$app->formatter->asDecimal($model->electric_meter_value, 2) ?></dd>
        <dt class="col-sm-3">เลขมิเตอร์น้ำ</dt><dd class="col-sm-9"><?= $model->water_meter_value === null ? 'ไม่ระบุ' : Yii::$app->formatter->asDecimal($model->water_meter_value, 2) ?></dd>
    </dl></div>
</div>
<div class="row g-3">
<div class="col-lg-8">
    <div class="card border-0 shadow-sm mb-3"><div class="card-header bg-body fw-semibold">อุปกรณ์และของใช้ที่ตรวจรับ</div>
    <?php if ($model->assetItems() === []): ?><div class="card-body text-muted">ไม่มีรายการอุปกรณ์</div><?php else: ?><div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>รายการ</th><th>จำนวน</th><th>สภาพ</th><th>หมายเหตุ</th><th>ตรวจแล้ว</th></tr></thead><tbody>
    <?php foreach ($model->assetItems() as $item): ?><tr><td><strong><?= Html::encode($item['item_name']) ?></strong></td><td><?= Html::encode(Yii::$app->formatter->asDecimal($item['quantity'], 2) . ' ' . $item['unit_name']) ?></td><td><?= Html::encode(AssetAssignment::conditionOptions()[$item['condition']] ?? $item['condition']) ?></td><td><?= Html::encode($item['note'] ?: '—') ?></td><td><?= $item['acknowledged'] ? '<span class="text-success">ตรวจแล้ว</span>' : '<span class="text-danger">ยังไม่ตรวจ</span>' ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php endif; ?></div>
    <div class="card border-0 shadow-sm"><div class="card-header bg-body fw-semibold">สภาพห้องและรูปถ่าย</div><div class="card-body">
        <p><?= nl2br(Html::encode($model->condition_note ?: 'ไม่มีหมายเหตุเพิ่มเติม')) ?></p>
        <?php if ($photos !== []): ?><div class="row g-2"><?php foreach ($photos as $photo): ?><div class="col-6 col-md-4"><img src="<?= Html::encode(FileManagerHelper::getImg($photo->id)) ?>" class="w-100 rounded border" style="aspect-ratio:4/3;object-fit:cover" alt="สภาพห้องวันรับมอบ"></div><?php endforeach; ?></div><?php else: ?><div class="small text-muted">ยังไม่มีรูปถ่ายสภาพห้อง</div><?php endif; ?>
    </div></div>
</div>
<div class="col-lg-4">
    <div class="card border-0 shadow-sm"><div class="card-header bg-body fw-semibold">การลงนามรับมอบ</div><div class="card-body">
        <div class="mb-3"><div class="small text-muted">ผู้ส่งมอบ</div><strong><?= Html::encode($model->handed_over_by_name) ?></strong><?php if ($model->handed_over_signed_at): ?><div class="small text-success mt-1">ลงนามแล้ว <?= Yii::$app->formatter->asDatetime($model->handed_over_signed_at, 'php:d/m/Y H:i') ?></div><?php endif; ?></div>
        <div class="mb-3"><div class="small text-muted">ผู้รับมอบ</div><strong><?= Html::encode($model->received_by_name) ?></strong><?php if ($model->received_signed_at): ?><div class="small text-success mt-1">ลงนามแล้ว <?= Yii::$app->formatter->asDatetime($model->received_signed_at, 'php:d/m/Y H:i') ?></div><?php endif; ?></div>
        <?php if ($model->status === Handover::STATUS_DRAFT && !$model->handed_over_signed_at): ?>
        <div class="alert alert-info small">ผู้ดูแลลงนามในฐานะผู้ส่งมอบก่อน จากนั้นผู้รับมอบจะได้รับแจ้งให้ลงนามผ่านเมนูบ้านพักของตนเอง</div>
        <?= Html::beginForm(['sign-sender', 'id' => $model->id], 'post', ['class' => 'd-grid gap-3']) ?>
        <label class="form-check"><input class="form-check-input" type="checkbox" name="handed_over_ack" value="1" required><span class="form-check-label">ข้าพเจ้าตรวจข้อมูลและยืนยันลงนามส่งมอบ</span></label>
        <button class="btn btn-primary">ลงนามส่งมอบ</button>
        <?= Html::endForm() ?>
        <?php elseif ($model->status === Handover::STATUS_DRAFT): ?>
        <div class="alert alert-warning mb-0">รอ <?= Html::encode($model->received_by_name) ?> ตรวจเอกสารและลงนามรับมอบผ่านเมนูบ้านพักของตนเอง</div>
        <?php else: ?><div class="alert alert-success mb-0">เอกสารได้รับการยืนยันและเปิดสถานะเข้าพักแล้ว</div><?php endif; ?>
    </div></div>
</div>
</div></div>

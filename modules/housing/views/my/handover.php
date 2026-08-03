<?php

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\housing\models\AssetAssignment;
use app\modules\housing\models\Handover;
use yii\helpers\Html;

$occupancy = $model->occupancy;
$locationName = implode(' / ', array_filter([
    $occupancy?->unit?->building?->name,
    $occupancy?->unit?->floor?->name,
    $occupancy?->unit?->name,
    $occupancy?->room?->name,
]));
$this->title = 'ตรวจและลงนามรับมอบ';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
?>
<style>
.my-handover{max-width:980px;margin:0 auto;--line:var(--bs-border-color-translucent);--muted:var(--bs-secondary-color)}.my-handover .surface{background:var(--bs-body-bg);border:1px solid var(--line);border-radius:10px;box-shadow:0 1px 2px var(--bs-border-color-translucent);overflow:hidden}.my-handover .surface-head{padding:.9rem 1.1rem;background:var(--bs-tertiary-bg);border-bottom:1px solid var(--line);font-weight:600}.my-handover .surface-body{padding:1.1rem}.my-handover .signature-state{display:flex;align-items:flex-start;gap:.65rem;padding:.8rem;border:1px solid var(--line);border-radius:8px}.my-handover .signature-state.is-done{background:var(--bs-success-bg-subtle);color:var(--bs-success-text-emphasis)}.my-handover .signature-state.is-wait{background:var(--bs-warning-bg-subtle);color:var(--bs-warning-text-emphasis)}
</style>
<div class="container-fluid py-3"><div class="my-handover">
<?= Html::a('<i class="bi bi-arrow-left"></i> กลับไปบ้านพักของฉัน', ['/profile', 'name' => 'housing'], ['class' => 'btn btn-outline-secondary mb-3']) ?>
<?php foreach (['success', 'error'] as $type): if (Yii::$app->session->hasFlash($type)): ?><div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?>"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div><?php endif; endforeach; ?>
<section class="surface mb-3">
    <div class="surface-head d-flex justify-content-between align-items-center"><span>เอกสาร <?= Html::encode($model->handover_no) ?></span><span class="badge <?= $model->status === Handover::STATUS_CONFIRMED ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis' ?>"><?= Html::encode(Handover::statusOptions()[$model->status]) ?></span></div>
    <div class="surface-body"><dl class="row mb-0">
        <dt class="col-sm-4">บ้านพัก/ห้องพัก</dt><dd class="col-sm-8"><?= Html::encode($locationName) ?></dd>
        <dt class="col-sm-4">วันที่รับมอบ</dt><dd class="col-sm-8"><?= Yii::$app->formatter->asDate($model->handover_date, 'php:d/m/Y') ?></dd>
        <dt class="col-sm-4">เลขมิเตอร์ไฟฟ้าเริ่มต้น</dt><dd class="col-sm-8"><?= $model->electric_meter_value === null ? 'ไม่ระบุ' : Yii::$app->formatter->asDecimal($model->electric_meter_value, 2) ?></dd>
        <dt class="col-sm-4">เลขมิเตอร์น้ำเริ่มต้น</dt><dd class="col-sm-8"><?= $model->water_meter_value === null ? 'ไม่ระบุ' : Yii::$app->formatter->asDecimal($model->water_meter_value, 2) ?></dd>
        <dt class="col-sm-4">สภาพห้อง/หมายเหตุ</dt><dd class="col-sm-8"><?= nl2br(Html::encode($model->condition_note ?: 'ไม่มีหมายเหตุเพิ่มเติม')) ?></dd>
    </dl></div>
</section>
<section class="surface mb-3"><div class="surface-head">อุปกรณ์และของใช้</div>
<?php if ($model->assetItems() === []): ?><div class="surface-body text-body-secondary">ไม่มีรายการอุปกรณ์ในเอกสารนี้</div><?php else: ?><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>รายการ</th><th>จำนวน</th><th>สภาพ</th><th>หมายเหตุ</th></tr></thead><tbody><?php foreach ($model->assetItems() as $item): ?><tr><td><strong><?= Html::encode($item['item_name']) ?></strong></td><td><?= Html::encode(Yii::$app->formatter->asDecimal($item['quantity'], 2) . ' ' . $item['unit_name']) ?></td><td><?= Html::encode(AssetAssignment::conditionOptions()[$item['condition']] ?? $item['condition']) ?></td><td><?= Html::encode($item['note'] ?: '—') ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
</section>
<?php if ($photos !== []): ?><section class="surface mb-3"><div class="surface-head">รูปถ่ายสภาพห้อง</div><div class="surface-body"><div class="row g-2"><?php foreach ($photos as $photo): ?><div class="col-6 col-md-4"><img src="<?= Html::encode(FileManagerHelper::getImg($photo->id)) ?>" class="w-100 rounded border" style="aspect-ratio:4/3;object-fit:cover" alt="สภาพห้องวันรับมอบ"></div><?php endforeach; ?></div></div></section><?php endif; ?>
<section class="surface"><div class="surface-head">การลงนาม</div><div class="surface-body">
    <div class="signature-state <?= $model->handed_over_signed_at ? 'is-done' : 'is-wait' ?> mb-2"><i class="bi <?= $model->handed_over_signed_at ? 'bi-check-circle' : 'bi-clock' ?>"></i><div><strong>ผู้ส่งมอบ: <?= Html::encode($model->handed_over_by_name) ?></strong><div class="small"><?= $model->handed_over_signed_at ? 'ลงนามแล้ว ' . Yii::$app->formatter->asDatetime($model->handed_over_signed_at, 'php:d/m/Y H:i') : 'ยังไม่ได้ลงนามส่งมอบ' ?></div></div></div>
    <div class="signature-state <?= $model->received_signed_at ? 'is-done' : 'is-wait' ?> mb-3"><i class="bi <?= $model->received_signed_at ? 'bi-check-circle' : 'bi-pencil' ?>"></i><div><strong>ผู้รับมอบ: <?= Html::encode($model->received_by_name) ?></strong><div class="small"><?= $model->received_signed_at ? 'ลงนามแล้ว ' . Yii::$app->formatter->asDatetime($model->received_signed_at, 'php:d/m/Y H:i') : 'รอคุณตรวจและลงนาม' ?></div></div></div>
    <?php if ($model->status === Handover::STATUS_DRAFT && $model->handed_over_signed_at && !$model->received_signed_at): ?>
    <?= Html::beginForm(['sign-handover', 'id' => $model->id], 'post', ['class' => 'd-grid gap-3']) ?>
    <label class="form-check"><input class="form-check-input" type="checkbox" name="received_ack" value="1" required><span class="form-check-label">ข้าพเจ้าตรวจข้อมูล สภาพห้อง อุปกรณ์ และยืนยันรับมอบตามเอกสารนี้</span></label>
    <button class="btn btn-success btn-lg">ลงนามรับมอบและเริ่มเข้าพัก</button>
    <?= Html::endForm() ?>
    <?php elseif (!$model->handed_over_signed_at): ?><div class="alert alert-warning mb-0">ยังลงนามไม่ได้ กรุณารอผู้ดูแลลงนามส่งมอบก่อน</div>
    <?php elseif ($model->status === Handover::STATUS_CONFIRMED): ?><div class="alert alert-success mb-0">ลงนามครบแล้วและเปิดสถานะเข้าพักเรียบร้อย</div><?php endif; ?>
</div></section>
</div></div>

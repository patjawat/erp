<?php

use app\modules\housing\models\CommitteeDecision;
use app\modules\housing\models\HousingRequest;
use yii\helpers\Html;

$this->title = 'คำขอ ' . $model->request_no;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'request']) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3">
<?php foreach (['success', 'error'] as $type): if (Yii::$app->session->hasFlash($type)): ?><div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?>"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div><?php endif; endforeach; ?>
<div class="row g-3">
<div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-header bg-body d-flex justify-content-between"><strong>รายละเอียดคำขอ</strong><span class="badge bg-warning-subtle text-warning"><?= Html::encode(HousingRequest::statusOptions()[$model->status] ?? $model->status) ?></span></div><div class="card-body"><dl class="row mb-0"><dt class="col-sm-4">ผู้ยื่น</dt><dd class="col-sm-8"><?= Html::encode($employeeName) ?></dd><dt class="col-sm-4">ประเภท</dt><dd class="col-sm-8"><?= Html::encode(HousingRequest::typeOptions()[$model->request_type] ?? '') ?></dd><dt class="col-sm-4">เหตุผล</dt><dd class="col-sm-8"><?= nl2br(Html::encode($model->reason ?: '—')) ?></dd></dl></div></div>
<div class="card border-0 shadow-sm mt-3"><div class="card-header bg-body fw-semibold">ประวัติสถานะ</div><ul class="list-group list-group-flush"><?php foreach ($model->logs as $log): ?><li class="list-group-item"><strong><?= Html::encode(HousingRequest::statusOptions()[$log->to_status] ?? $log->to_status) ?></strong><div class="small text-muted"><?= Html::encode($log->acted_at) ?><?= $log->comment ? ' · ' . Html::encode($log->comment) : '' ?></div></li><?php endforeach; ?></ul></div></div>
<div class="col-lg-4">
<div class="card border-0 shadow-sm"><div class="card-header bg-body fw-semibold">การดำเนินการ</div><div class="card-body d-grid gap-2">
<?php if ($model->status === HousingRequest::STATUS_SUBMITTED): ?><?= Html::a('รับตรวจสอบ', ['transition', 'id' => $model->id, 'to' => HousingRequest::STATUS_STAFF_REVIEW], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?><?php endif; ?>
<?php if ($model->status === HousingRequest::STATUS_STAFF_REVIEW): ?><?= Html::a('ส่งเข้าพิจารณา', ['transition', 'id' => $model->id, 'to' => HousingRequest::STATUS_COMMITTEE_REVIEW], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?><?php endif; ?>
<?php if ($model->status === HousingRequest::STATUS_COMMITTEE_REVIEW): ?>
<?= Html::beginForm(['decision', 'id' => $model->id], 'post', ['class' => 'd-grid gap-2']) ?><label class="form-label">วันที่มีมติ</label><input type="date" name="decision_date" value="<?= date('Y-m-d') ?>" class="form-control" required><input name="meeting_reference" class="form-control" placeholder="เลขอ้างอิงการประชุม"><textarea name="note" class="form-control" placeholder="หมายเหตุ"></textarea><button name="decision" value="<?= CommitteeDecision::APPROVED ?>" class="btn btn-success">อนุมัติ</button><button name="decision" value="<?= CommitteeDecision::REJECTED ?>" class="btn btn-outline-danger">ไม่อนุมัติ</button><?= Html::endForm() ?>
<?php endif; ?>
<?php if ($model->status === HousingRequest::STATUS_APPROVED): ?>
<?= Html::beginForm(['allocate', 'id' => $model->id], 'post', ['class' => 'd-grid gap-2']) ?><label class="form-label">ยูนิต</label><?= Html::dropDownList('unit_id', null, $unitOptions, ['class' => 'form-select', 'prompt' => 'เลือกยูนิต', 'required' => true]) ?><label class="form-label">ห้อง (แฟลตโสด)</label><?= Html::dropDownList('room_id', null, $roomOptions, ['class' => 'form-select', 'prompt' => 'จัดสรรทั้งยูนิต']) ?><button class="btn btn-primary">ยืนยันจัดสรร</button><?= Html::endForm() ?>
<?php endif; ?>
<?php if ($model->status === HousingRequest::STATUS_ALLOCATED): ?><?= Html::beginForm(['activate', 'id' => $model->id], 'post', ['class' => 'd-grid gap-2']) ?><label class="form-label">วันที่เข้าอยู่จริง</label><input type="date" name="start_date" value="<?= date('Y-m-d') ?>" class="form-control" required><button class="btn btn-primary">ยืนยันเข้าอยู่</button><?= Html::endForm() ?><?php endif; ?>
</div></div></div>
</div></div>

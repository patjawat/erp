<?php

use app\modules\housing\models\CommitteeDecision;
use app\modules\housing\models\HousingRequest;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'คำขอ ' . $model->request_no;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'request']) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3">
<?php foreach (['success', 'error'] as $type): if (Yii::$app->session->hasFlash($type)): ?><div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?>"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div><?php endif; endforeach; ?>
<div class="row g-3">
<div class="col-lg-8"><div class="card border-0 shadow-sm"><div class="card-header bg-body d-flex justify-content-between"><strong>รายละเอียดคำขอ</strong><span class="badge bg-warning-subtle text-warning"><?= Html::encode(HousingRequest::statusOptions()[$model->status] ?? $model->status) ?></span></div><div class="card-body">
<div class="d-flex flex-column flex-sm-row gap-3 align-items-sm-center mb-4 pb-3 border-bottom">
<?= Html::img($employee?->ShowAvatar() ?: '@web/img/placeholder_cid.png', [
    'alt' => 'รูปถ่าย ' . $employeeName,
    'class' => 'rounded-3 object-fit-cover border',
    'style' => 'width:96px;height:112px',
    'onerror' => "this.onerror=null;this.src='" . Url::to('@web/img/placeholder_cid.png') . "'",
]) ?>
<div><div class="small text-body-secondary">ผู้ยื่นคำขอ</div><div class="h5 mb-1"><?= Html::encode($employeeName) ?></div><div class="text-body-secondary"><?= Html::encode($employee?->positionName() ?: 'ไม่ระบุตำแหน่ง') ?> · <?= Html::encode($employee?->departmentName() ?: 'ไม่ระบุหน่วยงาน') ?></div></div>
</div>
<dl class="row mb-0">
<dt class="col-sm-4">ชื่อผู้ยื่น</dt><dd class="col-sm-8"><strong><?= Html::encode($employeeName) ?></strong></dd>
<dt class="col-sm-4">เพศ</dt><dd class="col-sm-8"><?= Html::encode($employee?->gender ?: 'ไม่ระบุ') ?></dd>
<dt class="col-sm-4">ตำแหน่ง</dt><dd class="col-sm-8"><?= Html::encode($employee?->positionName() ?: 'ไม่ระบุ') ?></dd>
<dt class="col-sm-4">หน่วยงาน</dt><dd class="col-sm-8"><?= Html::encode($employee?->departmentName() ?: 'ไม่ระบุ') ?></dd>
<dt class="col-sm-4">ประเภทคำขอ</dt><dd class="col-sm-8"><?= Html::encode(HousingRequest::typeOptions()[$model->request_type] ?? '') ?></dd>
<dt class="col-sm-4">ประเภทที่พักที่ต้องการ</dt><dd class="col-sm-8"><?= Html::encode(['house' => 'บ้านพัก', 'flat' => 'แฟลต', 'any' => 'บ้านพักหรือแฟลต'][$model->preferred_building_type] ?? 'ไม่ระบุ') ?></dd>
<dt class="col-sm-4">เหตุผล</dt><dd class="col-sm-8"><?= nl2br(Html::encode($model->reason ?: '—')) ?></dd></dl></div></div>
<?php
$employee = \app\modules\hr\models\Employees::findOne($model->emp_id);
$employeeActive = $employee && (string)$employee->status === '1';
$activeOccupancy = \app\modules\housing\models\Occupancy::find()
    ->where(['emp_id' => $model->emp_id, 'status' => \app\modules\housing\models\Occupancy::STATUS_ACTIVE])
    ->exists();
$eligible = $employeeActive && !($model->request_type === HousingRequest::TYPE_MOVE_IN && $activeOccupancy);
?>
<div class="card border-0 shadow-sm mt-3">
<div class="card-header bg-body fw-semibold">ผลตรวจสอบเบื้องต้น</div>
<div class="card-body">
<div class="d-flex align-items-center gap-2 mb-2"><i data-lucide="<?= $employeeActive ? 'check-circle-2' : 'alert-circle' ?>" class="text-<?= $employeeActive ? 'success' : 'danger' ?>"></i><span>สถานะบุคลากร: <strong><?= $employeeActive ? 'ยังปฏิบัติงาน' : 'ไม่ได้ปฏิบัติงาน' ?></strong></span></div>
<div class="d-flex align-items-center gap-2"><i data-lucide="<?= $activeOccupancy ? 'home' : 'circle-check' ?>" class="text-<?= $activeOccupancy ? 'warning' : 'success' ?>"></i><span>ที่พักปัจจุบัน: <strong><?= $activeOccupancy ? 'มีข้อมูลเข้าพักอยู่' : 'ไม่พบการเข้าพักปัจจุบัน' ?></strong></span></div>
<?php if (!$eligible): ?><div class="alert alert-warning mt-3 mb-0">คำขอนี้ยังไม่ผ่านเงื่อนไขเบื้องต้น กรุณาตรวจสอบประเภทคำขอหรือสถานะบุคลากรก่อนดำเนินการต่อ</div><?php endif; ?>
</div></div>
<div class="card border-0 shadow-sm mt-3"><div class="card-header bg-body fw-semibold">ประวัติสถานะ</div><ul class="list-group list-group-flush"><?php foreach ($model->logs as $log): ?><li class="list-group-item"><strong><?= Html::encode(HousingRequest::statusOptions()[$log->to_status] ?? $log->to_status) ?></strong><div class="small text-body-secondary"><?= Html::encode($log->acted_at) ?><?= $log->comment ? ' · ' . Html::encode($log->comment) : '' ?></div></li><?php endforeach; ?></ul></div></div>
<div class="col-lg-4">
<div class="card border-0 shadow-sm"><div class="card-header bg-body fw-semibold">การดำเนินการ</div><div class="card-body d-grid gap-2">
<?php if ($model->status === HousingRequest::STATUS_DRAFT): ?>
<?= Html::a('แก้ไขร่างคำขอ', ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']) ?>
<?= Html::a('ยื่นคำขอ', ['transition', 'id' => $model->id, 'to' => HousingRequest::STATUS_SUBMITTED], ['class' => 'btn btn-primary', 'data-method' => 'post', 'data-confirm' => 'ยืนยันการยื่นคำขอนี้หรือไม่?']) ?>
<?= Html::a('ลบร่างคำขอ', ['delete', 'id' => $model->id], ['class' => 'btn btn-link text-danger', 'data-method' => 'post', 'data-confirm' => 'ลบร่างคำขอนี้หรือไม่?']) ?>
<?php endif; ?>
<?php if ($model->status === HousingRequest::STATUS_SUBMITTED): ?><?= Html::a('รับตรวจสอบ', ['transition', 'id' => $model->id, 'to' => HousingRequest::STATUS_STAFF_REVIEW], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?><?php endif; ?>
<?php if ($model->status === HousingRequest::STATUS_STAFF_REVIEW && $eligible): ?><?= Html::a('ส่งเข้าพิจารณา', ['transition', 'id' => $model->id, 'to' => HousingRequest::STATUS_COMMITTEE_REVIEW], ['class' => 'btn btn-primary', 'data-method' => 'post', 'data-confirm' => 'ยืนยันว่าตรวจสอบข้อมูลและคุณสมบัติแล้วหรือไม่?']) ?><?php endif; ?>
<?php if ($model->status === HousingRequest::STATUS_COMMITTEE_REVIEW): ?>
<?= Html::beginForm(['decision', 'id' => $model->id], 'post', ['class' => 'd-grid gap-2', 'id' => 'committee-decision-form']) ?>
<label class="form-label mb-0">วันที่มีมติ</label>
<input type="date" name="decision_date" value="<?= date('Y-m-d') ?>" class="form-control" required>
<input name="meeting_reference" class="form-control" placeholder="เลขอ้างอิงการประชุม">
<label class="form-label mb-0 mt-1">จัดสรรบ้านพักหรือห้อง</label>
<?= Html::dropDownList('allocation_target', null, $allocationOptions, [
    'class' => 'form-select',
    'prompt' => $allocationOptions ? 'เลือกที่พักว่างเมื่ออนุมัติ' : 'ยังไม่มีบ้านพักหรือห้องว่าง',
    'id' => 'allocation-target',
]) ?>
<div class="form-text">ต้องเลือกที่พักเมื่ออนุมัติ หากไม่อนุมัติไม่ต้องเลือก</div>
<textarea name="note" class="form-control" placeholder="เหตุผลหรือหมายเหตุประกอบมติ"></textarea>
<button name="decision" value="<?= CommitteeDecision::APPROVED ?>" class="btn btn-success" data-confirm-allocation="1">อนุมัติและจัดสรร</button>
<button name="decision" value="<?= CommitteeDecision::REJECTED ?>" class="btn btn-outline-danger">ไม่อนุมัติ</button>
<?= Html::endForm() ?>
<?php
$this->registerJs(<<<'JS'
document.querySelector('[data-confirm-allocation="1"]')?.addEventListener('click', function(event){
    const target = document.getElementById('allocation-target');
    if (!target || !target.value) {
        event.preventDefault();
        alert('กรุณาเลือกบ้านพักหรือห้องที่จะจัดสรร ก่อนกดอนุมัติ');
    }
});
JS);
?>
<?php endif; ?>
<?php if ($model->status === HousingRequest::STATUS_APPROVED): ?>
<?= Html::beginForm(['allocate', 'id' => $model->id], 'post', ['class' => 'd-grid gap-2']) ?>
<label class="form-label">บ้านพักหรือห้องว่าง</label>
<?= Html::dropDownList('allocation_target', null, $allocationOptions, ['class' => 'form-select', 'prompt' => 'เลือกที่พักที่จะจัดสรร', 'required' => true]) ?>
<button class="btn btn-primary">ยืนยันจัดสรร</button>
<?= Html::endForm() ?>
<?php endif; ?>
<?php if ($model->status === HousingRequest::STATUS_ALLOCATED): ?>
<div class="alert alert-info mb-2">จัดสรรที่พักแล้ว ขั้นตอนต่อไปคือตรวจสภาพ อุปกรณ์ ค่ามิเตอร์ และลงนามรับมอบ</div>
<?= Html::a(
    $model->occupancy?->handover ? 'เปิดเอกสารรับมอบ' : 'จัดทำเอกสารรับมอบ',
    $model->occupancy?->handover
        ? ['/housing/handover/view', 'id' => $model->occupancy->handover->id]
        : ['/housing/handover/prepare', 'request_id' => $model->id],
    ['class' => 'btn btn-primary']
) ?>
<?php endif; ?>
<?php if ($model->status === HousingRequest::STATUS_ACTIVE && $model->occupancy?->handover): ?>
<?= Html::a('ดูและพิมพ์เอกสารรับมอบ', ['/housing/handover/view', 'id' => $model->occupancy->handover->id], ['class' => 'btn btn-outline-primary']) ?>
<?php endif; ?>
</div></div></div>
</div></div>

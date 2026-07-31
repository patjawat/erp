<?php

use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Unit;
use yii\helpers\Html;

$this->title = 'บ้านพัก';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
?>
<div class="container-fluid py-3">
    <?php foreach (['success', 'error'] as $type): if (Yii::$app->session->hasFlash($type)): ?>
        <div class="alert alert-<?= $type === 'error' ? 'danger' : 'success' ?>"><?= Html::encode(Yii::$app->session->getFlash($type)) ?></div>
    <?php endif; endforeach; ?>

    <?php if ($context['mode'] === 'unavailable'): ?>
        <div class="card border-0 shadow-sm"><div class="card-body text-center py-5"><h2 class="h5 fw-semibold">ไม่พบข้อมูลบุคลากร</h2><p class="text-body-secondary mb-0">กรุณาติดต่อผู้ดูแลระบบเพื่อเชื่อมบัญชีกับข้อมูลบุคลากร</p></div></div>
    <?php elseif ($context['mode'] === 'applicant'): ?>
        <div class="card border-0 shadow-sm"><div class="card-body py-5 text-center">
            <h2 class="h5 fw-semibold">ยังไม่มีคำขอเข้าพัก</h2>
            <p class="text-body-secondary">ยื่นคำขอเพื่อให้เจ้าหน้าที่ตรวจสอบและนำเข้าพิจารณาโดยคณะกรรมการบ้านพัก</p>
            <?= Html::a('ยื่นคำขอเข้าพัก', ['create-request'], ['class' => 'btn btn-primary']) ?>
        </div></div>
    <?php elseif ($context['mode'] === 'request'): $request = $context['request']; ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-body d-flex justify-content-between align-items-center">
                <div><div class="fw-semibold">สถานะคำขอ</div><div class="small text-body-secondary"><?= Html::encode($request->request_no) ?></div></div>
                <span class="badge bg-warning-subtle text-warning"><?= Html::encode(HousingRequest::statusOptions()[$request->status] ?? $request->status) ?></span>
            </div>
            <div class="card-body">
                <dl class="row mb-0"><dt class="col-sm-3">ประเภทคำขอ</dt><dd class="col-sm-9"><?= Html::encode(HousingRequest::typeOptions()[$request->request_type] ?? '') ?></dd><dt class="col-sm-3">เหตุผล</dt><dd class="col-sm-9"><?= nl2br(Html::encode($request->reason ?: '—')) ?></dd></dl>
                <?php if ($request->status === HousingRequest::STATUS_DRAFT): ?><div class="mt-3"><?= Html::a('ส่งคำขอ', ['submit', 'id' => $request->id], ['class' => 'btn btn-primary', 'data-method' => 'post', 'data-confirm' => 'ยืนยันส่งคำขอ?']) ?></div><?php endif; ?>
            </div>
        </div>
    <?php else: $occupancy = $context['occupancy']; ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-body d-flex justify-content-between"><div><div class="fw-semibold">ที่พักของฉัน</div><div class="small text-body-secondary"><?= Html::encode($occupancy->unit->code . ' · ' . $occupancy->unit->name) ?></div></div><span class="badge bg-primary-subtle text-primary"><?= $context['mode'] === 'resident' ? 'เข้าอยู่แล้ว' : 'รอเข้าอยู่' ?></span></div>
            <div class="card-body"><div class="row g-3"><div class="col-md-4"><div class="small text-body-secondary">รูปแบบ</div><div class="fw-semibold"><?= Html::encode(Unit::modeOptions()[$occupancy->occupancy_type] ?? '') ?></div></div><div class="col-md-4"><div class="small text-body-secondary">ห้องย่อย</div><div class="fw-semibold"><?= Html::encode($occupancy->room->name ?? 'ทั้งห้อง') ?></div></div><div class="col-md-4"><div class="small text-body-secondary">วันที่เข้าอยู่</div><div class="fw-semibold"><?= Html::encode($occupancy->start_date ?: 'รอยืนยัน') ?></div></div></div></div>
        </div>
        <?php if ($context['mode'] === 'resident'): ?><div class="mt-3"><?= Html::a('แจ้งและดูบุคคลภายนอก', ['/housing/guest/mine'], ['class' => 'btn btn-outline-primary']) ?></div><?php endif; ?>
    <?php endif; ?>
</div>

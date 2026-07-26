<?php

use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Unit;
use yii\helpers\Html;

$mode = $context['mode'];
$request = $context['request'];
$occupancy = $context['occupancy'];
?>
<style>
.my-housing{--mh-border:#dce6f0;--mh-soft:#f5f8fb;--mh-ink:#26384a;--mh-muted:#60758a;color:var(--mh-ink)}
.my-housing .housing-panel{background:#fff;border:1px solid var(--mh-border);border-radius:.85rem;overflow:hidden}
.my-housing .housing-head{display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;padding:1.1rem 1.25rem;border-bottom:1px solid var(--mh-border)}
.my-housing .housing-body{padding:1.25rem}
.my-housing .housing-intro{display:grid;grid-template-columns:72px 1fr;gap:1rem;align-items:center;background:var(--mh-soft);border:1px solid var(--mh-border);border-radius:.75rem;padding:1rem}
.my-housing .housing-icon{display:grid;place-items:center;width:72px;height:72px;border-radius:1rem;background:#e6f0fb;color:#356b9d}
.my-housing .housing-icon svg{width:34px;height:34px}
.my-housing .housing-details{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));border-top:1px solid var(--mh-border);border-left:1px solid var(--mh-border)}
.my-housing .housing-detail{min-height:82px;padding:.9rem 1rem;border-right:1px solid var(--mh-border);border-bottom:1px solid var(--mh-border)}
.my-housing .housing-label{color:var(--mh-muted);font-size:.78rem;margin-bottom:.25rem}
.my-housing .request-flow{display:flex;gap:.45rem;flex-wrap:wrap;margin-top:1rem}
.my-housing .flow-step{padding:.35rem .65rem;border-radius:999px;background:#eef2f6;color:#63778b;font-size:.78rem}
.my-housing .flow-step.is-current{background:#e6f0fb;color:#285f96;font-weight:600}
.my-housing .vacancy-summary{display:flex;align-items:center;justify-content:space-between;gap:1rem;margin-bottom:.75rem}
.my-housing .vacancy-list{border:1px solid var(--mh-border);border-radius:.75rem;overflow:hidden}
.my-housing .vacancy-item{display:grid;grid-template-columns:minmax(170px,1.25fr) minmax(130px,1fr) auto;gap:1rem;align-items:center;padding:.85rem 1rem;border-bottom:1px solid var(--mh-border)}
.my-housing .vacancy-item:last-child{border-bottom:0}
.my-housing .vacancy-item:hover{background:#f8fbfe}
.my-housing .vacancy-type{display:inline-flex;align-items:center;gap:.3rem;color:#356b9d;font-size:.78rem}
.my-housing .vacancy-status{display:inline-flex;padding:.28rem .6rem;border-radius:999px;background:#e8f5ee;color:#287a51;font-size:.78rem;font-weight:600}
@media(max-width:575.98px){.my-housing .housing-intro{grid-template-columns:1fr}.my-housing .housing-details{grid-template-columns:1fr}}
@media(max-width:767.98px){.my-housing .vacancy-item{grid-template-columns:1fr auto}.my-housing .vacancy-location{grid-column:1/-1;grid-row:2}}
</style>
<section class="my-housing">
<div class="housing-panel">
    <div class="housing-head">
        <div><h1 class="h5 mb-1">บ้านพักของฉัน</h1><div class="small text-muted">คำร้อง สถานะการจัดสรร และข้อมูลที่พัก</div></div>
        <?php if ($mode === 'applicant'): ?>
            <?= Html::a('<i data-lucide="file-pen-line"></i> เขียนคำร้อง', ['/housing/my/create-request'], [
                'class' => 'btn btn-primary open-modal',
                'data-size' => 'modal-lg',
            ]) ?>
        <?php endif; ?>
    </div>
    <div class="housing-body">
    <?php if ($mode === 'unavailable'): ?>
        <div class="alert alert-warning mb-0">ไม่พบข้อมูลบุคลากรที่เชื่อมกับบัญชีผู้ใช้ กรุณาติดต่อผู้ดูแลระบบ</div>
    <?php elseif ($mode === 'applicant'): ?>
        <div class="vacancy-summary">
            <div><h2 class="h6 mb-1">กระดานบ้านพักว่าง</h2><div class="small text-muted">ข้อมูลบ้านพัก แฟลต และห้องที่ว่างในปัจจุบัน</div></div>
            <span class="badge bg-primary-subtle text-primary-emphasis"><?= count($vacancies ?? []) ?> รายการว่าง</span>
        </div>
        <?php if (!empty($vacancies)): ?>
        <div class="vacancy-list">
            <?php foreach ($vacancies as $vacancy):
                $building = $vacancy['building'];
                $unit = $vacancy['unit'];
                $room = $vacancy['room'];
            ?>
            <div class="vacancy-item">
                <div>
                    <div class="vacancy-type"><i data-lucide="<?= $building->building_type === 'house' ? 'house' : 'building-2' ?>"></i><?= Html::encode($building->building_type === 'house' ? 'บ้านพัก' : 'แฟลต') ?></div>
                    <div class="fw-semibold mt-1"><?= Html::encode($building->name) ?></div>
                </div>
                <div class="vacancy-location">
                    <div><?= Html::encode($unit
                        ? implode(' / ', array_filter([$unit->floor?->name, $unit->name, $room?->name]))
                        : 'ว่างทั้งหลัง') ?></div>
                    <div class="small text-muted"><?= Html::encode($building->address ?: 'ยังไม่ระบุที่ตั้ง') ?></div>
                </div>
                <span class="vacancy-status">ว่าง</span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="housing-intro">
            <div class="housing-icon"><i data-lucide="house"></i></div>
            <div><h2 class="h6 mb-1">ยังไม่มีที่พักว่าง</h2><p class="text-muted mb-0">ขณะนี้ยังไม่มีบ้านพักหรือห้องว่าง สามารถกลับมาตรวจสอบข้อมูลได้ภายหลัง</p></div>
        </div>
        <?php endif; ?>
    <?php elseif ($mode === 'request'): ?>
        <div class="d-flex flex-wrap justify-content-between gap-3">
            <div><div class="small text-muted">เลขคำขอ</div><div class="fw-semibold fs-5"><?= Html::encode($request->request_no) ?></div></div>
            <span class="badge bg-warning-subtle text-warning-emphasis align-self-start"><?= Html::encode(HousingRequest::statusOptions()[$request->status] ?? $request->status) ?></span>
        </div>
        <div class="mt-3"><div class="small text-muted">เหตุผล</div><div><?= nl2br(Html::encode($request->reason ?: 'ไม่ได้ระบุ')) ?></div></div>
        <?php
        $steps = [
            HousingRequest::STATUS_SUBMITTED => 'ส่งคำร้อง',
            HousingRequest::STATUS_STAFF_REVIEW => 'ตรวจสอบ',
            HousingRequest::STATUS_COMMITTEE_REVIEW => 'พิจารณา',
            HousingRequest::STATUS_APPROVED => 'อนุมัติ',
            HousingRequest::STATUS_ALLOCATED => 'จัดสรร',
        ];
        ?>
        <div class="request-flow"><?php foreach ($steps as $status => $label): ?><span class="flow-step <?= $request->status === $status ? 'is-current' : '' ?>"><?= Html::encode($label) ?></span><?php endforeach; ?></div>
        <?php if ($request->status === HousingRequest::STATUS_DRAFT): ?>
            <div class="mt-3"><?= Html::a('ส่งคำร้อง', ['/housing/my/submit', 'id' => $request->id], ['class' => 'btn btn-primary', 'data-method' => 'post', 'data-confirm' => 'ยืนยันส่งคำร้องนี้หรือไม่?']) ?></div>
        <?php endif; ?>
    <?php else:
        $unit = $occupancy->unit;
        $room = $occupancy->room;
        $building = $unit?->building;
    ?>
        <div class="housing-intro mb-3">
            <div class="housing-icon"><i data-lucide="<?= $room ? 'door-open' : 'house' ?>"></i></div>
            <div><div class="small text-muted"><?= $room ? 'ห้องพักที่ได้รับจัดสรร' : 'บ้านพักที่ได้รับจัดสรร' ?></div><h2 class="h5 mb-1"><?= Html::encode($building?->name ?: $unit?->name ?: 'ที่พัก') ?></h2><div class="text-muted"><?= Html::encode(implode(' / ', array_filter([$unit?->name, $room?->name]))) ?></div></div>
        </div>
        <div class="housing-details">
            <div class="housing-detail"><div class="housing-label">สถานะ</div><strong><?= $mode === 'resident' ? 'เข้าอยู่อาศัยแล้ว' : 'รอยืนยันเข้าอยู่' ?></strong></div>
            <div class="housing-detail"><div class="housing-label">รูปแบบการพัก</div><strong><?= Html::encode(Unit::modeOptions()[$occupancy->occupancy_type] ?? $occupancy->occupancy_type) ?></strong></div>
            <div class="housing-detail"><div class="housing-label">วันที่เริ่มพัก</div><strong><?= Html::encode($occupancy->start_date ?: 'รอยืนยัน') ?></strong></div>
            <div class="housing-detail"><div class="housing-label">ที่อยู่</div><strong><?= Html::encode($building?->address ?: 'ยังไม่ระบุ') ?></strong></div>
        </div>
    <?php endif; ?>
    </div>
</div>
</section>

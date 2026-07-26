<?php

use app\modules\housing\models\HousingRequest;
use app\modules\housing\models\Unit;
use app\modules\housing\models\AssetAssignment;
use app\modules\housing\models\MaintenanceRequest;
use app\modules\housing\models\MonthlyAccount;
use app\modules\filemanager\components\FileManagerHelper;
use app\components\widgets\DataSummaryWidget;
use yii\helpers\Html;

$mode = $context['mode'];
$request = $context['request'];
$occupancy = $context['occupancy'];
$assets = $context['assets'] ?? [];
$photos = $context['photos'] ?? [];
$activeTab = $context['tab'] ?? 'overview';
$recentExpenses = $context['recentExpenses'] ?? [];
$recentMaintenance = $context['recentMaintenance'] ?? [];
$expenseProvider = $context['expenseProvider'] ?? null;
$maintenanceProvider = $context['maintenanceProvider'] ?? null;
$summary = $context['summary'] ?? [];
$maintenancePhotos = $context['maintenancePhotos'] ?? [];
$yearOptions = [];
for ($year = (int)date('Y'); $year >= (int)date('Y') - 9; $year--) {
    $yearOptions[$year] = (string)($year + 543);
}
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
.my-housing .resident-section{margin-top:1rem;border:1px solid var(--mh-border);border-radius:.75rem;overflow:hidden}
.my-housing .resident-section__head{display:flex;align-items-center;justify-content:space-between;gap:1rem;padding:.8rem 1rem;background:var(--mh-soft);border-bottom:1px solid var(--mh-border)}
.my-housing .resident-section__body{padding:1rem}.my-housing .housing-photo-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.65rem}
.my-housing .housing-photo{width:100%;aspect-ratio:4/3;object-fit:cover;border:1px solid var(--mh-border);border-radius:.5rem}
.my-housing .asset-row{display:grid;grid-template-columns:minmax(180px,1fr) 100px 150px;gap:1rem;padding:.7rem 0;border-bottom:1px solid var(--mh-border);align-items:center}.my-housing .asset-row:last-child{border-bottom:0}
.my-housing .history-summary{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:.65rem;margin-bottom:1rem}.my-housing .history-metric{padding:.75rem .85rem;background:var(--mh-soft);border:1px solid var(--mh-border);border-radius:.6rem}.my-housing .history-metric strong{display:block;margin-top:.2rem;font-variant-numeric:tabular-nums}
.my-housing .history-row{padding:.8rem 0;border-bottom:1px solid var(--mh-border)}.my-housing .history-row:last-child{border-bottom:0}.my-housing .history-row summary{display:grid;grid-template-columns:minmax(160px,1fr) repeat(3,110px) auto;gap:.75rem;align-items:center;cursor:pointer;list-style:none}.my-housing .history-row summary::-webkit-details-marker{display:none}
.my-housing .history-detail{padding:.8rem;margin-top:.65rem;background:var(--mh-soft);border-radius:.55rem}.my-housing .charge-row{display:flex;justify-content:space-between;gap:1rem;padding:.35rem 0}.my-housing .money{text-align:right;font-variant-numeric:tabular-nums}.my-housing .repair-row{display:grid;grid-template-columns:minmax(170px,1fr) 130px 130px;gap:.75rem;padding:.75rem 0;border-bottom:1px solid var(--mh-border)}.my-housing .repair-row:last-child{border-bottom:0}.my-housing .repair-photos{display:flex;flex-wrap:wrap;gap:.4rem;margin-top:.55rem}.my-housing .repair-photo{width:64px;height:52px;object-fit:cover;border:1px solid var(--mh-border);border-radius:.4rem}
.my-housing .housing-tabs{display:flex;gap:.25rem;margin-top:1rem;padding:.3rem;background:#eef2f7;border-radius:.65rem;overflow-x:auto}.my-housing .housing-tab{display:inline-flex;align-items:center;justify-content:center;min-height:40px;gap:.4rem;padding:.45rem .75rem;color:#4a5568;border-radius:.5rem;text-decoration:none;white-space:nowrap}.my-housing .housing-tab:hover{color:#1a202c;background:#fff}.my-housing .housing-tab.is-active{color:#0a58ca;background:#fff;box-shadow:0 1px 2px rgba(15,23,42,.08);font-weight:600}.my-housing .housing-tab:focus-visible{outline:3px solid rgba(13,110,253,.18)}
.my-housing .overview-strip{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));margin-top:1rem;border:1px solid var(--mh-border);border-radius:.75rem;overflow:hidden}.my-housing .overview-cell{padding:.85rem 1rem;border-right:1px solid var(--mh-border)}.my-housing .overview-cell:last-child{border-right:0}.my-housing .overview-cell strong{display:block;margin-top:.15rem;font-variant-numeric:tabular-nums}.my-housing .filter-bar{display:flex;flex-wrap:wrap;gap:.65rem;align-items:end;padding:1rem;background:var(--mh-soft);border-bottom:1px solid var(--mh-border)}.my-housing .filter-field{min-width:150px}.my-housing .filter-field label{display:block;margin-bottom:.25rem;color:var(--mh-muted);font-size:.76rem;font-weight:600}.my-housing .filter-field .form-control,.my-housing .filter-field .form-select{min-height:40px}
@media(max-width:575.98px){.my-housing .housing-intro{grid-template-columns:1fr}.my-housing .housing-details{grid-template-columns:1fr}}
@media(max-width:767.98px){.my-housing .vacancy-item{grid-template-columns:1fr auto}.my-housing .vacancy-location{grid-column:1/-1;grid-row:2}.my-housing .asset-row{grid-template-columns:1fr auto}.my-housing .asset-condition{grid-column:1/-1}.my-housing .history-summary,.my-housing .overview-strip{grid-template-columns:1fr}.my-housing .overview-cell{border-right:0;border-bottom:1px solid var(--mh-border)}.my-housing .overview-cell:last-child{border-bottom:0}.my-housing .history-row summary{grid-template-columns:1fr auto}.my-housing .history-row .history-mobile-full{grid-column:1/-1}.my-housing .repair-row{grid-template-columns:1fr}.my-housing .repair-row>*{grid-column:1}.my-housing .filter-field{min-width:100%;flex:1}}
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
        $handover = $occupancy->handover;
    ?>
        <?php if ($mode === 'allocated' && !$handover): ?>
            <div class="alert alert-info d-flex align-items-start gap-2">
                <i data-lucide="clock-3"></i>
                <div><strong>ผู้ดูแลกำลังจัดเตรียมเอกสารรับมอบ</strong><div class="small mt-1">เมื่อเอกสารพร้อม ระบบจะแสดงปุ่มให้ตรวจและลงนามในหน้านี้</div></div>
            </div>
        <?php elseif ($mode === 'allocated' && !$handover->handed_over_signed_at): ?>
            <div class="alert alert-warning d-flex align-items-start gap-2">
                <i data-lucide="file-clock"></i>
                <div><strong>เอกสารรับมอบกำลังรอผู้ดูแลลงนามส่งมอบ</strong><div class="small mt-1">ยังไม่ต้องดำเนินการ ระบบจะแจ้งอีกครั้งเมื่อถึงขั้นตอนของคุณ</div></div>
            </div>
        <?php elseif ($mode === 'allocated' && !$handover->received_signed_at): ?>
            <div class="alert alert-danger d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-start gap-2"><i data-lucide="bell-ring"></i><div><strong>มีเอกสารรอลงนามรับมอบ</strong><div class="small mt-1">กรุณาตรวจสภาพห้อง อุปกรณ์ และค่ามิเตอร์ก่อนลงนาม</div></div></div>
                <?= Html::a('ตรวจและลงนามรับมอบ', ['/housing/my/handover', 'id' => $handover->id], ['class' => 'btn btn-danger']) ?>
            </div>
        <?php endif; ?>
        <div class="housing-intro mb-3">
            <div class="housing-icon"><i data-lucide="<?= $room ? 'door-open' : 'house' ?>"></i></div>
            <div><div class="small text-muted"><?= $room ? 'ห้องพักที่ได้รับจัดสรร' : 'บ้านพักที่ได้รับจัดสรร' ?></div><h2 class="h5 mb-1"><?= Html::encode($building?->name ?: $unit?->name ?: 'ที่พัก') ?></h2><div class="text-muted"><?= Html::encode(implode(' / ', array_filter([$unit?->name, $room?->name]))) ?></div></div>
        </div>
        <div class="housing-details">
            <div class="housing-detail"><div class="housing-label">สถานะ</div><strong><?= $mode === 'resident' ? 'เข้าอยู่อาศัยแล้ว' : 'รอลงนามรับมอบ' ?></strong></div>
            <div class="housing-detail"><div class="housing-label">รูปแบบการพัก</div><strong><?= Html::encode(Unit::modeOptions()[$occupancy->occupancy_type] ?? $occupancy->occupancy_type) ?></strong></div>
            <div class="housing-detail"><div class="housing-label">วันที่เริ่มพัก</div><strong><?= $occupancy->start_date ? Yii::$app->formatter->asDate($occupancy->start_date, 'php:d/m/Y') : 'รอยืนยัน' ?></strong></div>
            <div class="housing-detail"><div class="housing-label">ที่อยู่</div><strong><?= Html::encode($building?->address ?: 'ยังไม่ระบุ') ?></strong></div>
        </div>
        <?php if ($mode === 'resident'): ?>
            <?php
            $tabs = [
                'overview' => ['label' => 'ภาพรวม', 'icon' => 'layout-dashboard'],
                'expenses' => ['label' => 'ค่าใช้จ่าย', 'icon' => 'receipt-text'],
                'maintenance' => ['label' => 'แจ้งซ่อม', 'icon' => 'wrench'],
                'assets' => ['label' => 'ข้อมูลที่พัก', 'icon' => 'house'],
                'documents' => ['label' => 'เอกสาร', 'icon' => 'file-text'],
            ];
            ?>
            <nav class="housing-tabs" aria-label="ข้อมูลบ้านพักของฉัน">
                <?php foreach ($tabs as $tabKey => $tab): ?>
                    <?= Html::a(
                        '<i data-lucide="' . $tab['icon'] . '"></i> ' . Html::encode($tab['label']),
                        ['/profile', 'name' => 'housing', 'housing_tab' => $tabKey],
                        ['class' => 'housing-tab ' . ($activeTab === $tabKey ? 'is-active' : ''), 'aria-current' => $activeTab === $tabKey ? 'page' : null]
                    ) ?>
                <?php endforeach; ?>
            </nav>
            <?php if ($activeTab === 'overview'): ?>
                <div class="overview-strip">
                    <div class="overview-cell"><span class="small text-muted">ยอดค้างชำระ</span><strong class="<?= ($summary['balanceTotal'] ?? 0) > 0 ? 'text-danger' : 'text-success' ?>"><?= Yii::$app->formatter->asDecimal($summary['balanceTotal'] ?? 0, 2) ?> บาท</strong></div>
                    <div class="overview-cell"><span class="small text-muted">งานซ่อมที่ยังดำเนินการ</span><strong><?= number_format((int)($summary['openMaintenance'] ?? 0)) ?> รายการ</strong></div>
                    <div class="overview-cell"><span class="small text-muted">วันที่เริ่มพัก</span><strong><?= Yii::$app->formatter->asDate($occupancy->start_date, 'php:d/m/Y') ?></strong></div>
                </div>
                <section class="resident-section">
                    <div class="resident-section__head"><div><h3 class="h6 mb-1">ค่าใช้จ่ายล่าสุด</h3><div class="small text-muted">แสดง 3 เดือนล่าสุด</div></div><?= Html::a('ดูประวัติทั้งหมด', ['/profile', 'name' => 'housing', 'housing_tab' => 'expenses'], ['class' => 'btn btn-sm btn-outline-primary']) ?></div>
                    <div class="resident-section__body">
                        <?php if ($recentExpenses === []): ?><div class="text-muted">ยังไม่มีค่าใช้จ่ายที่ปิดรอบแล้ว</div>
                        <?php else: foreach ($recentExpenses as $account): ?><div class="d-flex justify-content-between gap-3 py-2 border-bottom"><div><strong><?= Html::encode($account->period?->name ?: 'ไม่ระบุเดือน') ?></strong><div class="small text-muted"><?= Html::encode([MonthlyAccount::PAYMENT_PAID => 'ชำระแล้ว', MonthlyAccount::PAYMENT_PARTIAL => 'ชำระบางส่วน', MonthlyAccount::PAYMENT_UNPAID => 'ยังไม่ชำระ'][$account->payment_status] ?? $account->payment_status) ?></div></div><div class="money"><strong><?= Yii::$app->formatter->asDecimal($account->total_amount, 2) ?> บาท</strong><div class="small <?= (float)$account->balance_amount > 0 ? 'text-danger' : 'text-success' ?>">คงเหลือ <?= Yii::$app->formatter->asDecimal($account->balance_amount, 2) ?></div></div></div><?php endforeach; endif; ?>
                    </div>
                </section>
                <section class="resident-section">
                    <div class="resident-section__head"><div><h3 class="h6 mb-1">การแจ้งซ่อมล่าสุด</h3><div class="small text-muted">แสดง 3 รายการล่าสุด</div></div><div class="d-flex gap-2"><?= Html::a('ดูประวัติทั้งหมด', ['/profile', 'name' => 'housing', 'housing_tab' => 'maintenance'], ['class' => 'btn btn-sm btn-outline-primary']) ?><?= Html::a('แจ้งปัญหา', ['/housing/my/create-maintenance'], ['class' => 'btn btn-sm btn-primary open-modal', 'data-size' => 'modal-lg']) ?></div></div>
                    <div class="resident-section__body">
                        <?php if ($recentMaintenance === []): ?><div class="text-muted">ยังไม่มีประวัติแจ้งซ่อม</div>
                        <?php else: foreach ($recentMaintenance as $repair): ?><div class="d-flex justify-content-between gap-3 py-2 border-bottom"><div><strong><?= Html::encode($repair->title) ?></strong><div class="small text-muted"><?= Html::encode($repair->ticket_no) ?> · <?= Yii::$app->formatter->asDate($repair->reported_at, 'php:d/m/Y') ?></div></div><span class="badge align-self-start <?= $repair->status === MaintenanceRequest::STATUS_COMPLETED ? 'bg-success-subtle text-success-emphasis' : 'bg-warning-subtle text-warning-emphasis' ?>"><?= Html::encode(MaintenanceRequest::statusOptions()[$repair->status] ?? $repair->status) ?></span></div><?php endforeach; endif; ?>
                    </div>
                </section>
            <?php elseif ($activeTab === 'assets'): ?>
            <section class="resident-section">
                <div class="resident-section__head"><div><h3 class="h6 mb-1">รายละเอียดที่พักของฉัน</h3><div class="small text-muted">ข้อมูลเฉพาะบ้านพักหรือห้องที่คุณกำลังพักอาศัย</div></div>
                <?php if ($handover): ?><?= Html::a('ดูเอกสารรับมอบ', ['/housing/my/handover', 'id' => $handover->id], ['class' => 'btn btn-sm btn-outline-primary']) ?><?php endif; ?></div>
                <div class="resident-section__body"><dl class="row mb-0">
                    <dt class="col-sm-4">ประเภทที่พัก</dt><dd class="col-sm-8"><?= Html::encode($building?->building_type === 'house' ? 'บ้านพัก' : 'แฟลต') ?></dd>
                    <dt class="col-sm-4">อาคาร/บ้านพัก</dt><dd class="col-sm-8"><?= Html::encode($building?->name ?: 'ไม่ระบุ') ?></dd>
                    <dt class="col-sm-4">ชั้น</dt><dd class="col-sm-8"><?= Html::encode($unit?->floor?->name ?: 'ไม่ระบุ') ?></dd>
                    <dt class="col-sm-4">ยูนิต/ห้อง</dt><dd class="col-sm-8"><?= Html::encode(implode(' / ', array_filter([$unit?->name, $room?->name])) ?: 'ไม่ระบุ') ?></dd>
                    <dt class="col-sm-4">รหัสที่พัก</dt><dd class="col-sm-8"><?= Html::encode($room?->code ?: $unit?->code ?: 'ไม่ระบุ') ?></dd>
                    <dt class="col-sm-4">รายละเอียด</dt><dd class="col-sm-8"><?= nl2br(Html::encode($room?->description ?: $unit?->description ?: $building?->description ?: 'ไม่มีรายละเอียดเพิ่มเติม')) ?></dd>
                </dl></div>
            </section>
            <?php if ($photos !== []): ?>
            <section class="resident-section"><div class="resident-section__head"><h3 class="h6 mb-0">รูปภาพที่พัก</h3><span class="small text-muted"><?= count($photos) ?> ภาพ</span></div><div class="resident-section__body"><div class="housing-photo-grid">
                <?php foreach ($photos as $photo): ?><img class="housing-photo" src="<?= Html::encode(FileManagerHelper::getImg($photo->upload_id)) ?>" alt="<?= Html::encode($photo->caption ?: 'รูปภาพที่พัก') ?>"><?php endforeach; ?>
            </div></div></section>
            <?php endif; ?>
            <section class="resident-section"><div class="resident-section__head"><h3 class="h6 mb-0">อุปกรณ์และของใช้ประจำที่พัก</h3><span class="small text-muted"><?= count($assets) ?> รายการ</span></div><div class="resident-section__body">
                <?php if ($assets === []): ?><div class="text-muted">ยังไม่มีรายการอุปกรณ์หรือของใช้</div>
                <?php else: foreach ($assets as $asset): ?><div class="asset-row"><div><strong><?= Html::encode($asset->item_name) ?></strong><div class="small text-muted"><?= Html::encode($asset->category ?: 'ไม่ระบุหมวด') ?></div></div><div><?= Html::encode(Yii::$app->formatter->asDecimal($asset->quantity, 2) . ' ' . $asset->unit_name) ?></div><div class="asset-condition"><?= Html::encode(AssetAssignment::conditionOptions()[$asset->condition_status] ?? $asset->condition_status) ?></div></div><?php endforeach; endif; ?>
            </div></section>
            <?php elseif ($activeTab === 'expenses'):
            $expenses = $expenseProvider?->getModels() ?? [];
            $expenseTotal = (float)($summary['expenseTotal'] ?? 0);
            $paidTotal = (float)($summary['paidTotal'] ?? 0);
            $balanceTotal = (float)($summary['balanceTotal'] ?? 0);
            ?>
            <section class="resident-section">
                <div class="resident-section__head"><div><h3 class="h6 mb-1">ค่าใช้จ่ายและการชำระเงิน</h3><div class="small text-muted">แสดงเฉพาะรอบที่ปิดบัญชีของที่พักนี้</div></div></div>
                <?= Html::beginForm(['/profile'], 'get', ['class' => 'filter-bar']) ?>
                    <?= Html::hiddenInput('name', 'housing') ?><?= Html::hiddenInput('housing_tab', 'expenses') ?>
                    <div class="filter-field"><label for="expense-year">ปี</label><?= Html::dropDownList('expense_year', Yii::$app->request->get('expense_year'), $yearOptions, ['id' => 'expense-year', 'class' => 'form-select', 'prompt' => 'ทุกปี']) ?></div>
                    <button class="btn btn-primary">กรองข้อมูล</button>
                    <?= Html::a('ล้างตัวกรอง', ['/profile', 'name' => 'housing', 'housing_tab' => 'expenses'], ['class' => 'btn btn-light']) ?>
                <?= Html::endForm() ?>
                <div class="resident-section__body">
                    <div class="history-summary">
                        <div class="history-metric"><span class="small text-muted">ค่าใช้จ่ายรวม</span><strong><?= Yii::$app->formatter->asDecimal($expenseTotal, 2) ?> บาท</strong></div>
                        <div class="history-metric"><span class="small text-muted">ชำระแล้ว</span><strong class="text-success"><?= Yii::$app->formatter->asDecimal($paidTotal, 2) ?> บาท</strong></div>
                        <div class="history-metric"><span class="small text-muted">คงเหลือ</span><strong class="<?= $balanceTotal > 0 ? 'text-danger' : 'text-success' ?>"><?= Yii::$app->formatter->asDecimal($balanceTotal, 2) ?> บาท</strong></div>
                    </div>
                    <?php if ($expenses === []): ?><div class="text-muted">ยังไม่มีประวัติค่าใช้จ่ายที่ปิดรอบแล้ว</div>
                    <?php else: foreach ($expenses as $account): ?>
                    <details class="history-row">
                        <summary>
                            <div><strong><?= Html::encode($account->period?->name ?: 'ไม่ระบุเดือน') ?></strong><div class="small text-muted">กำหนดชำระ <?= $account->period?->due_date ? Yii::$app->formatter->asDate($account->period->due_date, 'php:d/m/Y') : 'ไม่ระบุ' ?></div></div>
                            <div class="money history-mobile-full"><span class="small text-muted">ค่าใช้จ่าย</span><br><?= Yii::$app->formatter->asDecimal($account->total_amount, 2) ?></div>
                            <div class="money"><span class="small text-muted">ชำระแล้ว</span><br><?= Yii::$app->formatter->asDecimal($account->paid_amount, 2) ?></div>
                            <div class="money"><span class="small text-muted">คงเหลือ</span><br><strong class="<?= (float)$account->balance_amount > 0 ? 'text-danger' : 'text-success' ?>"><?= Yii::$app->formatter->asDecimal($account->balance_amount, 2) ?></strong></div>
                            <span class="badge <?= $account->payment_status === MonthlyAccount::PAYMENT_PAID ? 'bg-success-subtle text-success-emphasis' : ($account->payment_status === MonthlyAccount::PAYMENT_PARTIAL ? 'bg-warning-subtle text-warning-emphasis' : 'bg-danger-subtle text-danger-emphasis') ?>"><?= Html::encode([MonthlyAccount::PAYMENT_PAID => 'ชำระแล้ว', MonthlyAccount::PAYMENT_PARTIAL => 'ชำระบางส่วน', MonthlyAccount::PAYMENT_UNPAID => 'ยังไม่ชำระ'][$account->payment_status] ?? $account->payment_status) ?></span>
                        </summary>
                        <div class="history-detail">
                            <?php foreach ($account->items as $item): ?><div class="charge-row"><span><?= Html::encode($item->description) ?></span><strong class="money"><?= Yii::$app->formatter->asDecimal($item->amount, 2) ?> บาท</strong></div><?php endforeach; ?>
                            <?php if ($account->note): ?><div class="small text-muted mt-2">หมายเหตุ: <?= Html::encode($account->note) ?></div><?php endif; ?>
                        </div>
                    </details>
                    <?php endforeach; endif; ?>
                    <?php if ($expenseProvider && $expenseProvider->getTotalCount() > 0): ?><div class="mt-3 pt-3 border-top"><?= DataSummaryWidget::widget(['dataProvider' => $expenseProvider]) ?></div><?php endif; ?>
                </div>
            </section>
            <?php elseif ($activeTab === 'maintenance'):
            $maintenance = $maintenanceProvider?->getModels() ?? [];
            ?>
            <section class="resident-section">
                <div class="resident-section__head"><div><h3 class="h6 mb-1">การแจ้งซ่อมและประวัติการซ่อม</h3><div class="small text-muted">รายการที่คุณแจ้งและงานส่วนกลางที่เกี่ยวข้องกับอาคารนี้</div></div>
                <?= Html::a('<i data-lucide="wrench"></i> แจ้งปัญหา', ['/housing/my/create-maintenance'], ['class' => 'btn btn-sm btn-primary open-modal', 'data-size' => 'modal-lg']) ?></div>
                <?= Html::beginForm(['/profile'], 'get', ['class' => 'filter-bar']) ?>
                    <?= Html::hiddenInput('name', 'housing') ?><?= Html::hiddenInput('housing_tab', 'maintenance') ?>
                    <div class="filter-field"><label for="maintenance-status">สถานะ</label><?= Html::dropDownList('maintenance_status', Yii::$app->request->get('maintenance_status', 'all'), ['all' => 'ทั้งหมด', 'open' => 'ยังดำเนินการ'] + MaintenanceRequest::statusOptions(), ['id' => 'maintenance-status', 'class' => 'form-select']) ?></div>
                    <div class="filter-field"><label for="maintenance-year">ปี</label><?= Html::dropDownList('maintenance_year', Yii::$app->request->get('maintenance_year'), $yearOptions, ['id' => 'maintenance-year', 'class' => 'form-select', 'prompt' => 'ทุกปี']) ?></div>
                    <button class="btn btn-primary">กรองข้อมูล</button>
                    <?= Html::a('ล้างตัวกรอง', ['/profile', 'name' => 'housing', 'housing_tab' => 'maintenance'], ['class' => 'btn btn-light']) ?>
                <?= Html::endForm() ?>
                <div class="resident-section__body">
                    <?php if ($maintenance === []): ?><div class="text-muted">ยังไม่มีประวัติแจ้งซ่อมในช่วงที่เข้าพัก</div>
                    <?php else: foreach ($maintenance as $repair): ?>
                    <div class="repair-row">
                        <div><strong><?= Html::encode($repair->title) ?></strong><div class="small text-muted"><?= Html::encode($repair->ticket_no) ?> · <?= Html::encode($repair->location_note ?: MaintenanceRequest::scopeOptions()[$repair->problem_scope] ?? '') ?></div><div class="small mt-1"><?= Html::encode($repair->description) ?></div><?php if ($repair->resolution): ?><div class="small text-success mt-1">ผลการซ่อม: <?= Html::encode($repair->resolution) ?></div><?php endif; ?><?php if (!empty($maintenancePhotos[$repair->ref])): ?><div class="repair-photos"><?php foreach (array_slice($maintenancePhotos[$repair->ref], 0, 4) as $repairPhoto): ?><img class="repair-photo" src="<?= Html::encode(FileManagerHelper::getImg($repairPhoto->id)) ?>" alt="ภาพประกอบการแจ้งซ่อม"><?php endforeach; ?></div><?php endif; ?></div>
                        <div><span class="small text-muted">วันที่แจ้ง</span><br><?= Yii::$app->formatter->asDatetime($repair->reported_at, 'php:d/m/Y H:i') ?></div>
                        <div><span class="badge <?= $repair->status === MaintenanceRequest::STATUS_COMPLETED ? 'bg-success-subtle text-success-emphasis' : ($repair->status === MaintenanceRequest::STATUS_NEW ? 'bg-danger-subtle text-danger-emphasis' : 'bg-warning-subtle text-warning-emphasis') ?>"><?= Html::encode(MaintenanceRequest::statusOptions()[$repair->status] ?? $repair->status) ?></span><?php if ((float)$repair->expense_amount > 0): ?><div class="small mt-2">ค่าใช้จ่าย <?= Yii::$app->formatter->asDecimal($repair->expense_amount, 2) ?> บาท</div><?php endif; ?></div>
                    </div>
                    <?php endforeach; endif; ?>
                    <?php if ($maintenanceProvider && $maintenanceProvider->getTotalCount() > 0): ?><div class="mt-3 pt-3 border-top"><?= DataSummaryWidget::widget(['dataProvider' => $maintenanceProvider]) ?></div><?php endif; ?>
                </div>
            </section>
            <?php elseif ($activeTab === 'documents'): ?>
                <section class="resident-section">
                    <div class="resident-section__head"><div><h3 class="h6 mb-1">เอกสารบ้านพัก</h3><div class="small text-muted">เอกสารที่เกี่ยวข้องกับการเข้าพักของคุณ</div></div></div>
                    <div class="resident-section__body">
                        <?php if ($handover): ?><div class="d-flex flex-wrap justify-content-between align-items-center gap-3"><div><strong>เอกสารรับมอบ <?= Html::encode($handover->handover_no) ?></strong><div class="small text-muted">วันที่รับมอบ <?= Yii::$app->formatter->asDate($handover->handover_date, 'php:d/m/Y') ?></div></div><?= Html::a('เปิดเอกสารรับมอบ', ['/housing/my/handover', 'id' => $handover->id], ['class' => 'btn btn-outline-primary']) ?></div><?php else: ?><div class="text-muted">ยังไม่มีเอกสารที่เกี่ยวข้อง</div><?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>
    </div>
</div>
</section>

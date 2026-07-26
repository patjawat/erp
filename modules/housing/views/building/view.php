<?php

use app\modules\filemanager\components\FileManagerHelper;
use app\modules\housing\models\Building;
use app\modules\housing\models\MaintenanceRequest;
use app\modules\housing\models\Unit;
use yii\helpers\Html;

$this->title = $model->name;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'building']) ?><?php $this->endBlock();

$isFlat = $model->building_type === Building::TYPE_FLAT;
$occupiedUnits = count(array_filter($units, static fn(Unit $unit): bool => $unit->status === Unit::STATUS_OCCUPIED));
$residentCount = count($occupancies) + array_sum(array_map(static fn($occupancy): int => count($occupancy->residents), $occupancies));
?>
<style>
.building-detail{--h-bg:#f7fafc;--h-soft:#eef5ff;--h-border:#dce6f0;--h-ink:#26384a;--h-muted:#60758a;--h-primary:#4c84c6;color:var(--h-ink)}
.building-detail .detail-hero,.building-detail .detail-section{background:#fff;border:1px solid var(--h-border);border-radius:.85rem}
.building-detail .hero-photo{width:180px;aspect-ratio:4/3;object-fit:cover;border-radius:.7rem}
.building-detail .metric-band{display:flex;gap:2rem;flex-wrap:wrap;padding:1rem 1.25rem;background:var(--h-bg);border-top:1px solid var(--h-border)}
.building-detail .metric-value{font-size:1.15rem;font-weight:700}
.building-detail .section-title{font-size:1rem;font-weight:700;margin:0}
.building-detail .unit-row+.unit-row{border-top:1px solid var(--h-border)}
.building-detail .status-pill{display:inline-flex;padding:.25rem .6rem;border-radius:999px;font-size:.8rem;font-weight:600;background:#eef5ff;color:#356b9d}
.building-detail .empty-note{padding:2rem;text-align:center;color:var(--h-muted)}
.building-detail .btn-primary{background:var(--h-primary);border-color:var(--h-primary)}
@media(max-width:767.98px){.building-detail .hero-photo{width:100%;max-height:230px}.building-detail .hero-actions{width:100%}.building-detail .hero-actions .btn{flex:1;min-height:44px}.building-detail .metric-band{gap:1rem}.building-detail .metric-band>div{min-width:42%}}
</style>
<div class="container-fluid py-3 building-detail">
    <div class="detail-hero overflow-hidden mb-3">
        <div class="p-3 p-lg-4 d-flex flex-column flex-md-row gap-4">
            <?php if ($buildingImage !== null): ?>
                <?= Html::img(FileManagerHelper::getImg($buildingImage->id), ['class' => 'hero-photo flex-shrink-0', 'alt' => 'รูป ' . $model->name]) ?>
            <?php else: ?>
                <div class="hero-photo flex-shrink-0 d-flex align-items-center justify-content-center" style="background:var(--h-soft);color:var(--h-muted)"><i data-lucide="building-2" style="width:42px;height:42px"></i></div>
            <?php endif; ?>
            <div class="flex-grow-1">
                <div class="d-flex flex-wrap justify-content-between gap-3">
                    <div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2"><span class="status-pill"><?= Html::encode(Building::typeOptions()[$model->building_type] ?? '') ?></span><span class="text-muted"><?= Html::encode($model->code) ?></span></div>
                        <h1 class="h4 mb-2"><?= Html::encode($model->name) ?></h1>
                        <div class="text-muted"><?= Html::encode($model->address ?: 'ยังไม่ได้ระบุที่ตั้ง') ?></div>
                    </div>
                    <div class="d-flex gap-2 align-self-start hero-actions">
                        <?= Html::a('<i data-lucide="pencil"></i> แก้ไข', ['update', 'id' => $model->id, 'title' => 'แก้ไขบ้านพัก/แฟลต'], ['class' => 'btn btn-outline-secondary open-modal', 'data-size' => 'modal-lg']) ?>
                        <?= Html::a('<i data-lucide="plus"></i> แจ้งซ่อม', ['/housing/maintenance/create', 'building_id' => $model->id], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-xl']) ?>
                    </div>
                </div>
                <p class="mt-3 mb-0" style="max-width:72ch"><?= nl2br(Html::encode($model->description ?: 'ยังไม่มีรายละเอียดเพิ่มเติม')) ?></p>
            </div>
        </div>
        <div class="metric-band">
            <?php if ($isFlat): ?><div><div class="small text-muted">จำนวนชั้น</div><div class="metric-value"><?= number_format(count($model->floors)) ?></div></div><?php endif; ?>
            <div><div class="small text-muted"><?= $isFlat ? 'ยูนิตทั้งหมด' : 'พื้นที่พักอาศัย' ?></div><div class="metric-value"><?= number_format(count($units)) ?></div></div>
            <div><div class="small text-muted">ยูนิตที่มีผู้พัก</div><div class="metric-value"><?= number_format($occupiedUnits) ?></div></div>
            <div><div class="small text-muted">ผู้พักอาศัยปัจจุบัน</div><div class="metric-value"><?= number_format($residentCount) ?> คน</div></div>
            <div><div class="small text-muted">ผู้รับผิดชอบดูแล</div><div class="metric-value fs-6"><?= Html::encode($model->responsibleEmployee?->fullname() ?: 'ยังไม่ได้กำหนด') ?></div></div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <section class="detail-section overflow-hidden">
                <div class="p-3 d-flex justify-content-between align-items-center gap-2">
                    <h2 class="section-title"><?= $isFlat ? 'ชั้น ยูนิต และห้องพัก' : 'รายละเอียดพื้นที่บ้านพัก' ?></h2>
                    <?= Html::a('เปิดกระดานห้องพัก', ['/housing/unit/index', 'building_id' => $model->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                </div>
                <?php if ($units === []): ?>
                    <div class="empty-note">ยังไม่มียูนิตหรือพื้นที่พักอาศัยในรายการนี้</div>
                <?php else: foreach ($units as $unit): ?>
                    <div class="unit-row p-3">
                        <div class="d-flex justify-content-between gap-3">
                            <div><strong><?= Html::encode($unit->name) ?></strong><div class="small text-muted"><?= Html::encode($unit->floor?->name ?: ($isFlat ? 'ยังไม่ระบุชั้น' : 'บ้านพัก')) ?> · <?= number_format(count($unit->rooms)) ?> ห้อง</div></div>
                            <div class="text-end"><span class="status-pill"><?= Html::encode(Unit::statusOptions()[$unit->status] ?? $unit->status) ?></span><div class="small text-muted mt-1"><?= Yii::$app->formatter->asDecimal($unit->monthly_base_fee ?: 0, 2) ?> บาท/เดือน</div></div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 mt-2">
                            <?= Html::a('<i data-lucide="eye" style="width:15px;height:15px"></i> รายละเอียด', ['/housing/unit/view', 'id' => $unit->id, 'return_building_id' => $model->id], ['class' => 'btn btn-sm btn-outline-info']) ?>
                            <span class="small text-muted align-self-center"><?= number_format(count($unit->assets)) ?> รายการของใช้ · <?= number_format(count($unit->photos)) ?> รูปภาพ</span>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </section>
            <section class="detail-section overflow-hidden mt-3">
                <div class="p-3"><h2 class="section-title">ประวัติค่าใช้จ่ายที่ปิดรอบแล้ว</h2></div>
                <?php if ($expenseHistory === []): ?><div class="empty-note">ยังไม่มีประวัติค่าใช้จ่ายที่ปิดรอบ</div>
                <?php else: foreach ($expenseHistory as $expense): ?><div class="unit-row p-3 d-flex justify-content-between gap-3"><div><strong><?= Html::encode($expense->period->name) ?></strong><div class="small text-muted"><?= Html::encode($expense->payer_name ?: 'ห้องว่าง') ?> · <?= Html::encode($expense->unit_name ?: 'ทั้งหลัง') ?></div></div><div class="text-end"><strong><?= Yii::$app->formatter->asDecimal($expense->total_amount, 2) ?> บาท</strong><div class="small text-muted">คงเหลือ <?= Yii::$app->formatter->asDecimal($expense->balance_amount, 2) ?></div></div></div><?php endforeach; endif; ?>
            </section>
        </div>
        <div class="col-xl-5">
            <section class="detail-section overflow-hidden mb-3">
                <div class="p-3"><h2 class="section-title">ผู้พักอาศัยปัจจุบัน</h2></div>
                <?php if ($occupancies === []): ?>
                    <div class="empty-note">ยังไม่มีผู้พักอาศัย บ้านพักว่างก็สามารถแจ้งซ่อมโดยผู้ดูแลได้</div>
                <?php else: foreach ($occupancies as $occupancy): ?>
                    <div class="unit-row p-3">
                        <strong><?= Html::encode($occupancy->employee?->fullname() ?: 'รหัสบุคลากร ' . $occupancy->emp_id) ?></strong>
                        <div class="small text-muted"><?= Html::encode($occupancy->unit?->name ?: '') ?><?= $occupancy->room ? ' · ' . Html::encode($occupancy->room->name) : '' ?></div>
                        <?php if ($occupancy->residents !== []): ?><div class="small mt-2">ผู้พักร่วม: <?= Html::encode(implode(', ', array_map(static fn($resident): string => trim($resident->first_name . ' ' . $resident->last_name), $occupancy->residents))) ?></div><?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </section>
            <section class="detail-section overflow-hidden">
                <div class="p-3 d-flex justify-content-between align-items-center gap-2"><h2 class="section-title">ประวัติซ่อมล่าสุด</h2><?= Html::a('ดูทั้งหมด', ['/housing/maintenance/index', 'building_id' => $model->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?></div>
                <?php if ($maintenanceRequests === []): ?>
                    <div class="empty-note">ยังไม่มีประวัติการแจ้งซ่อม</div>
                <?php else: foreach ($maintenanceRequests as $request): ?>
                    <div class="unit-row p-3">
                        <div class="d-flex justify-content-between gap-2"><strong><?= Html::a(Html::encode($request->title), ['/housing/maintenance/view', 'id' => $request->id], ['class' => 'text-decoration-none']) ?></strong><span class="small text-muted"><?= Yii::$app->formatter->asDate($request->reported_at, 'php:d/m/Y') ?></span></div>
                        <div class="small text-muted mt-1"><?= Html::encode(MaintenanceRequest::reporterTypeOptions()[$request->reporter_type] ?? 'ผู้ดูแลบ้านพักแจ้ง') ?> · <?= Html::encode(MaintenanceRequest::scopeOptions()[$request->problem_scope] ?? 'โครงสร้าง/ภายนอกอาคาร') ?></div>
                    </div>
                <?php endforeach; endif; ?>
            </section>
        </div>
    </div>
</div>

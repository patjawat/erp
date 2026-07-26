<?php
use app\modules\filemanager\components\FileManagerHelper;
use app\modules\housing\models\AssetAssignment;
use app\modules\housing\models\Unit;
use yii\helpers\Html;

$location = $room ?? $unit;
$locationName = $room ? $room->code . ' · ' . $room->name : $unit->code . ' · ' . $unit->name;
$locationSub = implode(' · ', array_filter([$unit->building->name ?? null, $unit->floor->name ?? null]));
$backUrl = $returnBuildingId
    ? ['/housing/building/view', 'id' => $returnBuildingId]
    : ['index'];
$backLabel = $returnBuildingId ? 'รายละเอียด ' . ($unit->building->name ?? 'บ้านพัก/แฟลต') : 'ยูนิตและห้อง';
$totalValue = array_sum(array_map(static fn(AssetAssignment $asset) => $asset->totalValue(), $assets));
$monthlyRent = array_sum(array_map(static fn(AssetAssignment $asset) => $asset->totalMonthlyRent(), $assets));
$normalCount = count(array_filter($assets, static fn(AssetAssignment $asset) => $asset->condition_status === AssetAssignment::CONDITION_NORMAL));
$primaryPhoto = $photos[0] ?? null;
$this->title = $locationName;
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'unit']) ?><?php $this->endBlock();
?>
<style>
.housing-detail{--h-bg:#f7fafc;--h-soft:#eef5ff;--h-border:#dce6f0;--h-ink:#26384a;--h-muted:#60758a;--h-primary:#4c84c6;color:var(--h-ink)}
.housing-detail .soft-panel{background:#fff;border:1px solid var(--h-border);border-radius:.8rem}
.housing-detail .detail-muted{color:var(--h-muted)}
.housing-detail .nav-tabs{border-color:var(--h-border)}
.housing-detail .nav-tabs .nav-link{color:var(--h-muted);min-height:44px}
.housing-detail .nav-tabs .nav-link.active{color:var(--h-primary)!important;background:#fff!important;border-color:transparent transparent var(--h-primary)!important;border-bottom-width:2px;font-weight:600}
.housing-summary{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:.75rem}
.housing-summary__item{padding:.9rem 1rem;background:var(--h-bg);border:1px solid var(--h-border);border-radius:.7rem}
.housing-summary__value{font-weight:700;font-size:1.1rem;margin-top:.2rem}
.housing-photo-main{aspect-ratio:4/3;width:100%;object-fit:cover;border-radius:.7rem;background:var(--h-bg);border:1px solid var(--h-border)}
.housing-photo-empty{aspect-ratio:4/3;border:1px dashed #b8c7d6;border-radius:.7rem;background:var(--h-bg);display:flex;align-items:center;justify-content:center;text-align:center;color:var(--h-muted)}
.housing-photo-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:.5rem}
.housing-photo-thumb{aspect-ratio:1;width:100%;object-fit:cover;border-radius:.5rem;border:2px solid transparent}
.housing-photo-thumb.is-primary{border-color:var(--h-primary)}
.housing-asset-thumb{width:52px;height:52px;object-fit:cover;border-radius:.5rem;background:var(--h-bg);border:1px solid var(--h-border)}
.housing-condition{display:inline-flex;align-items:center;min-height:28px;padding:.2rem .55rem;border-radius:999px;font-size:.8rem;font-weight:600}
.housing-condition--normal{color:#287a51;background:#e8f5ee}.housing-condition--repair{color:#8a6415;background:#fff4dc}
.housing-condition--damaged,.housing-condition--lost{color:#a23a43;background:#fcebec}
.housing-detail .btn-primary{background:var(--h-primary);border-color:var(--h-primary)}
@media(max-width:1199.98px){.housing-summary{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:767.98px){.housing-summary{grid-template-columns:repeat(2,minmax(0,1fr))}.housing-detail .table-responsive table{min-width:980px}.page-heading-actions{width:100%}.page-heading-actions .btn{flex:1;min-height:44px}}
@media(prefers-reduced-motion:reduce){.housing-detail *{scroll-behavior:auto!important;transition:none!important}}
</style>
<div class="container-fluid py-3 housing-detail">
    <nav aria-label="เส้นทางนำทาง" class="small mb-2">
        <?= Html::a(Html::encode($backLabel), $backUrl, ['class' => 'text-decoration-none']) ?><span class="mx-2 detail-muted">/</span><span aria-current="page"><?= Html::encode($locationName) ?></span>
    </nav>
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
        <div>
            <div class="d-flex align-items-center gap-2 flex-wrap"><h1 class="h3 mb-0"><?= Html::encode($locationName) ?></h1><span class="badge bg-success-subtle text-success-emphasis"><?= Html::encode(Unit::statusOptions()[$location->status] ?? $location->status) ?></span></div>
            <div class="detail-muted mt-1"><?= Html::encode($locationSub) ?></div>
        </div>
        <div class="d-flex gap-2 page-heading-actions">
            <?= Html::a('<i data-lucide="arrow-left"></i> ' . ($returnBuildingId ? 'กลับหน้ารายละเอียดแฟลต' : 'กลับรายการ'), $backUrl, ['class' => 'btn btn-outline-secondary']) ?>
            <?php if (!$room): ?><?= Html::a('<i data-lucide="pencil"></i> แก้ไขข้อมูล', ['update', 'id' => $unit->id, 'title' => 'แก้ไขยูนิต'], ['class' => 'btn btn-outline-primary open-modal', 'data-size' => 'modal-xl']) ?><?php endif; ?>
        </div>
    </div>
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#housing-general" type="button">ข้อมูลทั่วไป</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#housing-photos" type="button">รูปภาพ <span class="badge text-bg-light ms-1"><?= count($photos) ?></span></button></li>
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#housing-assets" type="button">อุปกรณ์และของใช้ <span class="badge text-bg-light ms-1"><?= count($assets) ?></span></button></li>
    </ul>
    <div class="tab-content">
        <section class="tab-pane fade" id="housing-general">
            <div class="soft-panel p-3">
                <dl class="row mb-0">
                    <dt class="col-sm-3">รหัส</dt><dd class="col-sm-9"><?= Html::encode($location->code) ?></dd>
                    <dt class="col-sm-3">ชื่อ</dt><dd class="col-sm-9"><?= Html::encode($location->name) ?></dd>
                    <dt class="col-sm-3">ความจุ</dt><dd class="col-sm-9"><?= Html::encode((string) ($location->capacity ?: 'ไม่ระบุ')) ?></dd>
                    <dt class="col-sm-3">รายละเอียด</dt><dd class="col-sm-9"><?= nl2br(Html::encode($location->description ?: 'ไม่มีรายละเอียดเพิ่มเติม')) ?></dd>
                </dl>
                <hr><h2 class="h6">ประวัติค่าใช้จ่ายที่ปิดรอบแล้ว</h2>
                <?php if ($expenseHistory === []): ?><div class="small detail-muted">ยังไม่มีประวัติค่าใช้จ่าย</div><?php else: ?><div class="table-responsive"><table class="table table-sm align-middle mb-0"><thead><tr><th>เดือน</th><th>ผู้รับผิดชอบ</th><th class="text-end">ค่าใช้จ่าย</th><th class="text-end">ชำระแล้ว</th><th class="text-end">คงเหลือ</th></tr></thead><tbody><?php foreach ($expenseHistory as $expense): ?><tr><td><?= Html::encode($expense->period->name) ?></td><td><?= Html::encode($expense->payer_name ?: 'ห้องว่าง') ?></td><td class="text-end"><?= Yii::$app->formatter->asDecimal($expense->total_amount, 2) ?></td><td class="text-end"><?= Yii::$app->formatter->asDecimal($expense->paid_amount, 2) ?></td><td class="text-end fw-semibold"><?= Yii::$app->formatter->asDecimal($expense->balance_amount, 2) ?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?>
            </div>
        </section>
        <section class="tab-pane fade" id="housing-photos">
            <div class="soft-panel p-3">
                <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">รูปภาพ<?= $room ? 'ห้องพัก' : 'บ้านพัก/ยูนิต' ?></h2><?= Html::a('<i data-lucide="image-plus"></i> เพิ่มรูปภาพ', ['upload-photo', 'unit_id' => $unit->id, 'room_id' => $room?->id], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-lg']) ?></div>
                <?php if ($photos): ?><div class="row g-3">
                    <?php foreach ($photos as $photo): ?><div class="col-6 col-md-4 col-xl-3">
                        <img src="<?= Html::encode(FileManagerHelper::getImg($photo->upload_id)) ?>" class="w-100 rounded border" style="aspect-ratio:4/3;object-fit:cover" alt="<?= Html::encode($photo->caption ?: $locationName) ?>">
                        <div class="small mt-2"><?= Html::encode($photo->caption ?: 'ไม่มีคำอธิบาย') ?></div>
                        <div class="d-flex gap-1 mt-2"><?php if (!$photo->is_primary): ?><?= Html::a('ใช้เป็นภาพหลัก', ['set-primary-photo', 'id' => $photo->id], ['class' => 'btn btn-sm btn-outline-primary', 'data-method' => 'post']) ?><?php else: ?><span class="badge bg-primary-subtle text-primary-emphasis align-self-center">ภาพหลัก</span><?php endif; ?><?= Html::a('ลบ', ['delete-photo', 'id' => $photo->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ลบรูปภาพนี้หรือไม่?']) ?></div>
                    </div><?php endforeach; ?>
                </div><?php else: ?><div class="housing-photo-empty"><div><i data-lucide="images"></i><div class="fw-semibold mt-2">ยังไม่มีรูปภาพ</div><div class="small">เพิ่มภาพเพื่อบันทึกสภาพและรายละเอียดของสถานที่</div></div></div><?php endif; ?>
            </div>
        </section>
        <section class="tab-pane fade show active" id="housing-assets">
            <div class="row g-3 align-items-start">
                <div class="col-xl-9">
                    <div class="housing-summary mb-3">
                        <div class="housing-summary__item"><div class="small detail-muted">รายการทั้งหมด</div><div class="housing-summary__value"><?= count($assets) ?> รายการ</div></div>
                        <div class="housing-summary__item"><div class="small detail-muted">สภาพดี</div><div class="housing-summary__value text-success"><?= $normalCount ?> รายการ</div></div>
                        <div class="housing-summary__item"><div class="small detail-muted">ต้องติดตาม</div><div class="housing-summary__value text-warning"><?= count($assets) - $normalCount ?> รายการ</div></div>
                        <div class="housing-summary__item"><div class="small detail-muted">มูลค่ารวม</div><div class="housing-summary__value"><?= Yii::$app->formatter->asDecimal($totalValue, 2) ?> บาท</div></div>
                        <div class="housing-summary__item"><div class="small detail-muted">ค่าเช่ารวม/เดือน</div><div class="housing-summary__value"><?= Yii::$app->formatter->asDecimal($monthlyRent, 2) ?> บาท</div></div>
                    </div>
                    <div class="soft-panel overflow-hidden">
                        <div class="p-3 d-flex flex-wrap justify-content-between gap-2 align-items-center border-bottom"><div><h2 class="h5 mb-0">อุปกรณ์และของใช้</h2><div class="small detail-muted">ราคาทรัพย์สินและค่าเช่ารายเดือนแยกจากกัน</div></div><?= Html::a('<i data-lucide="plus"></i> เพิ่มรายการ', ['create-asset', 'unit_id' => $unit->id, 'room_id' => $room?->id], ['class' => 'btn btn-primary open-modal', 'data-size' => 'modal-xl']) ?></div>
                        <?php if ($assets): ?><div class="table-responsive"><table class="table table-hover align-middle mb-0">
                            <thead><tr><th>รูป</th><th>รายการ</th><th class="text-center">จำนวน</th><th>สภาพ</th><th class="text-end">ราคาต่อหน่วย</th><th class="text-end">มูลค่ารวม</th><th class="text-end">ค่าเช่า/เดือน</th><th>วันที่นำเข้า</th><th class="text-end">จัดการ</th></tr></thead>
                            <tbody><?php foreach ($assets as $asset): $assetImage = $assetImages[$asset->ref] ?? null; ?><tr>
                                <td><?php if ($assetImage): ?><img class="housing-asset-thumb" src="<?= Html::encode(FileManagerHelper::getImg($assetImage->id)) ?>" alt="<?= Html::encode($asset->item_name) ?>"><?php else: ?><div class="housing-asset-thumb d-flex align-items-center justify-content-center detail-muted"><i data-lucide="package"></i></div><?php endif; ?></td>
                                <td><strong><?= Html::encode($asset->item_name) ?></strong><div class="small detail-muted"><?= Html::encode($asset->category ?: 'ไม่ระบุหมวด') ?></div></td>
                                <td class="text-center"><?= Html::encode(Yii::$app->formatter->asDecimal($asset->quantity, 2) . ' ' . $asset->unit_name) ?></td>
                                <td><span class="housing-condition housing-condition--<?= Html::encode($asset->condition_status) ?>"><?= Html::encode(AssetAssignment::conditionOptions()[$asset->condition_status] ?? $asset->condition_status) ?></span></td>
                                <td class="text-end"><?= Yii::$app->formatter->asDecimal($asset->unit_price, 2) ?></td><td class="text-end fw-semibold"><?= Yii::$app->formatter->asDecimal($asset->totalValue(), 2) ?></td><td class="text-end"><?= Yii::$app->formatter->asDecimal($asset->monthly_rent, 2) ?></td>
                                <td><?= $asset->assigned_at ? Yii::$app->formatter->asDate($asset->assigned_at, 'php:d/m/Y') : '—' ?></td>
                                <td class="text-end text-nowrap"><?= Html::a('<i data-lucide="pencil"></i>', ['update-asset', 'id' => $asset->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-xl', 'aria-label' => 'แก้ไข ' . $asset->item_name]) ?> <?= Html::a('<i data-lucide="trash-2"></i>', ['delete-asset', 'id' => $asset->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ลบรายการ ' . $asset->item_name . ' หรือไม่?', 'aria-label' => 'ลบ ' . $asset->item_name]) ?></td>
                            </tr><?php endforeach; ?></tbody>
                        </table></div><?php else: ?><div class="text-center py-5 px-3"><i data-lucide="package-open"></i><div class="fw-semibold mt-2">ยังไม่มีอุปกรณ์หรือของใช้</div><div class="small detail-muted mt-1">เพิ่มรายการเพื่อบันทึกจำนวน สภาพ ราคา และค่าเช่ารายเดือน</div></div><?php endif; ?>
                    </div>
                </div>
                <aside class="col-xl-3"><div class="soft-panel p-3 position-sticky" style="top:1rem">
                    <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">รูปภาพ</h2><?= Html::a('<i data-lucide="image-plus"></i> เพิ่ม', ['upload-photo', 'unit_id' => $unit->id, 'room_id' => $room?->id], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?></div>
                    <?php if ($primaryPhoto): ?><img class="housing-photo-main" src="<?= Html::encode(FileManagerHelper::getImg($primaryPhoto->upload_id)) ?>" alt="<?= Html::encode($primaryPhoto->caption ?: $locationName) ?>"><div class="small mt-2"><?= Html::encode($primaryPhoto->caption ?: 'ภาพหลัก') ?></div>
                    <?php if (count($photos) > 1): ?><div class="housing-photo-grid mt-3"><?php foreach (array_slice($photos, 0, 4) as $photo): ?><button class="border-0 bg-transparent p-0" type="button" data-bs-toggle="tab" data-bs-target="#housing-photos" aria-label="ดูรูปภาพทั้งหมด"><img class="housing-photo-thumb<?= $photo->is_primary ? ' is-primary' : '' ?>" src="<?= Html::encode(FileManagerHelper::getImg($photo->upload_id)) ?>" alt=""></button><?php endforeach; ?></div><?php endif; ?>
                    <?php else: ?><div class="housing-photo-empty"><div><i data-lucide="image"></i><div class="small mt-2">ยังไม่มีรูปภาพ</div></div></div><?php endif; ?>
                    <hr><dl class="row small mb-0"><dt class="col-5 detail-muted">รหัส</dt><dd class="col-7"><?= Html::encode($location->code) ?></dd><dt class="col-5 detail-muted">อาคาร</dt><dd class="col-7"><?= Html::encode($unit->building->name ?? '—') ?></dd><dt class="col-5 detail-muted">ชั้น</dt><dd class="col-7"><?= Html::encode($unit->floor->name ?? 'ไม่ระบุ') ?></dd><dt class="col-5 detail-muted">ความจุ</dt><dd class="col-7"><?= Html::encode((string) ($location->capacity ?: 'ไม่ระบุ')) ?></dd></dl>
                </div></aside>
            </div>
        </section>
    </div>
</div>

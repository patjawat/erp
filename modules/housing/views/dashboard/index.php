<?php

use app\modules\housing\models\Building;
use app\modules\housing\models\Unit;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ภาพรวม';
$this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'dashboard']) ?><?php $this->endBlock();

$statusMeta = [
    Unit::STATUS_VACANT => ['label' => 'ว่าง', 'class' => 'is-vacant'],
    Unit::STATUS_OCCUPIED => ['label' => 'มีผู้พัก', 'class' => 'is-occupied'],
    Unit::STATUS_RESERVED => ['label' => 'รอเข้า', 'class' => 'is-reserved'],
    Unit::STATUS_MOVE_OUT => ['label' => 'รอย้ายออก', 'class' => 'is-move-out'],
    Unit::STATUS_MAINTENANCE => ['label' => 'ปิดซ่อม', 'class' => 'is-maintenance'],
    Unit::STATUS_INACTIVE => ['label' => 'งดใช้งาน', 'class' => 'is-inactive'],
];
$this->registerCss(<<<CSS
.housing-board{--hb-line:var(--bs-border-color-translucent);--hb-ink:var(--bs-emphasis-color);--hb-muted:var(--bs-secondary-color)}
.housing-toolbar,.housing-room-board{background:var(--bs-body-bg);border:1px solid var(--hb-line);border-radius:10px;box-shadow:0 1px 2px var(--bs-border-color-translucent)}
.housing-status-strip{display:flex;gap:.5rem;overflow-x:auto;padding:.25rem 0 .5rem;scrollbar-width:thin}
.housing-status-filter{display:inline-flex;align-items:center;gap:.45rem;min-height:40px;padding:.45rem .75rem;border:1px solid var(--hb-line);border-radius:8px;background:var(--bs-body-bg);color:var(--hb-ink);text-decoration:none;white-space:nowrap}
.housing-status-filter:hover,.housing-status-filter:focus-visible{border-color:var(--bs-primary-border-subtle);color:var(--bs-primary-text-emphasis)}
.housing-status-filter.is-active{border-color:var(--bs-primary);box-shadow:0 0 0 3px var(--bs-primary-bg-subtle)}
.housing-status-filter__count{font-variant-numeric:tabular-nums;font-weight:700}
.housing-building+.housing-building{border-top:1px solid var(--hb-line)}
.housing-building__head{padding:1rem 1.1rem;background:var(--bs-tertiary-bg);display:flex;align-items:center;justify-content:space-between;gap:1rem}
.housing-floor{padding:1rem 1.1rem}
.housing-floor+.housing-floor{border-top:1px dashed var(--hb-line)}
.housing-floor__label{font-size:.8rem;font-weight:600;color:var(--bs-secondary-color);margin-bottom:.65rem}
.housing-unit-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:.75rem}
.housing-unit{position:relative;display:flex;flex-direction:column;min-height:145px;padding:.85rem;border:1px solid var(--hb-line);border-left:4px solid var(--bs-secondary-color);border-radius:8px;color:var(--hb-ink);background:var(--bs-body-bg);text-decoration:none;transition:border-color 120ms ease,box-shadow 120ms ease}
.housing-unit:hover,.housing-unit:focus-within{box-shadow:0 6px 18px var(--bs-border-color-translucent);color:var(--hb-ink)}
.housing-unit__code a{color:inherit;text-decoration:none}
.housing-unit__code a:hover{color:var(--bs-primary-text-emphasis)}
.housing-unit.is-vacant{border-left-color:var(--bs-success)}
.housing-unit.is-occupied{border-left-color:var(--bs-primary)}
.housing-unit.is-reserved,.housing-unit.is-move-out{border-left-color:var(--bs-warning)}
.housing-unit.is-maintenance{border-left-color:var(--bs-danger)}
.housing-unit.is-inactive{border-left-color:var(--bs-secondary-color)}
.housing-unit__head{display:flex;align-items:flex-start;justify-content:space-between;gap:.5rem}
.housing-unit__code{font-weight:700}.housing-unit__type{font-size:.75rem;color:var(--hb-muted);margin-top:.15rem}
.housing-unit__status{font-size:.72rem;font-weight:600;border-radius:999px;padding:.25rem .5rem}
.housing-unit__status.is-vacant{background:var(--bs-success-bg-subtle);color:var(--bs-success-text-emphasis)}
.housing-unit__status.is-occupied{background:var(--bs-primary-bg-subtle);color:var(--bs-primary-text-emphasis)}
.housing-unit__status.is-reserved,.housing-unit__status.is-move-out{background:var(--bs-warning-bg-subtle);color:var(--bs-warning-text-emphasis)}
.housing-unit__status.is-maintenance{background:var(--bs-danger-bg-subtle);color:var(--bs-danger-text-emphasis)}
.housing-unit__status.is-inactive{background:var(--bs-tertiary-bg);color:var(--bs-secondary-color)}
.housing-unit__rooms{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.4rem;margin-top:.8rem}
.housing-room{position:relative;z-index:2;display:block;padding:.45rem .5rem .45rem .6rem;border-radius:6px;border-left:3px solid var(--bs-secondary-color);background:var(--bs-tertiary-bg);color:var(--hb-ink);text-decoration:none;font-size:.76rem;min-width:0;transition:box-shadow 120ms ease}
.housing-room:hover,.housing-room:focus-visible{box-shadow:0 0 0 2px var(--bs-border-color)}
.housing-room strong,.housing-room span{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.housing-room span{color:var(--hb-muted);margin-top:.1rem}
.housing-room.is-vacant{background:var(--bs-success-bg-subtle);border-left-color:var(--bs-success)}
.housing-room.is-vacant span{color:var(--bs-success-text-emphasis)}
.housing-room.is-occupied{background:var(--bs-primary-bg-subtle);border-left-color:var(--bs-primary)}
.housing-room.is-occupied span{color:var(--bs-primary-text-emphasis)}
.housing-room.is-reserved,.housing-room.is-move-out{background:var(--bs-warning-bg-subtle);border-left-color:var(--bs-warning)}
.housing-room.is-reserved span,.housing-room.is-move-out span{color:var(--bs-warning-text-emphasis)}
.housing-room.is-maintenance{background:var(--bs-danger-bg-subtle);border-left-color:var(--bs-danger)}
.housing-room.is-maintenance span{color:var(--bs-danger-text-emphasis)}
.housing-room.is-inactive{background:var(--bs-tertiary-bg);border-left-color:var(--bs-secondary-color)}
.housing-unit__empty{margin:auto 0;color:var(--hb-muted);font-size:.82rem}
.housing-empty{padding:4rem 1.5rem;text-align:center}
@media(max-width:767.98px){.housing-unit-grid{grid-template-columns:1fr}.housing-toolbar{border-radius:0;margin-inline:-.75rem}.housing-room-board{border-radius:0;margin-inline:-.75rem}.housing-building__head,.housing-floor{padding:.85rem}.housing-unit{min-height:0}}
@media(prefers-reduced-motion:reduce){.housing-unit{transition:none}}
CSS);
?>
<div class="container-fluid py-3 housing-board">
    <?php if (($responsibleAttentionCount ?? 0) > 0): ?>
        <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2" role="alert">
            <div class="d-flex gap-2 align-items-start">
                <i data-lucide="triangle-alert" class="flex-shrink-0 mt-1" style="width:18px;height:18px"></i>
                <div>
                    <div class="fw-semibold">มี <?= number_format($responsibleAttentionCount) ?> รายการที่ต้องกำหนดผู้รับผิดชอบ</div>
                    <div class="small">กรุณาตรวจสอบบ้านพักที่ยังไม่มีผู้รับผิดชอบ หรือผู้รับผิดชอบไม่ได้ปฏิบัติงานแล้ว</div>
                </div>
            </div>
            <?= Html::a('ตรวจสอบผู้รับผิดชอบ', ['/housing/building/index'], ['class' => 'btn btn-sm btn-outline-warning']) ?>
        </div>
    <?php endif; ?>
    <form class="housing-toolbar p-3 mb-3" method="get" action="<?= Url::to(['index']) ?>">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-lg-4">
                <label for="housing-q" class="form-label small fw-semibold">ค้นหาห้องหรือห้องย่อย</label>
                <input id="housing-q" name="q" class="form-control" value="<?= Html::encode($filters['q'] ?? '') ?>" placeholder="เช่น A101 หรือ บ้านพัก 01">
            </div>
            <div class="col-8 col-lg-4">
                <label for="housing-building" class="form-label small fw-semibold">อาคาร</label>
                <?= Html::dropDownList('building_id', $filters['building_id'], $buildingOptions, ['id' => 'housing-building', 'class' => 'form-select', 'prompt' => 'ทุกอาคาร']) ?>
            </div>
            <div class="col-4 col-lg-auto"><button class="btn btn-primary w-100">ค้นหา</button></div>
            <div class="col-12 col-lg-auto"><?= Html::a('ล้างตัวกรอง', ['index'], ['class' => 'btn btn-outline-secondary w-100']) ?></div>
        </div>
    </form>

    <div class="housing-status-strip mb-2" aria-label="กรองสถานะห้อง">
        <?= Html::a('<span>ทั้งหมด</span><span class="housing-status-filter__count">' . array_sum(array_column($counts, 'total')) . '</span>', ['index', 'building_id' => $filters['building_id'], 'q' => $filters['q']], ['class' => 'housing-status-filter ' . (empty($filters['status']) ? 'is-active' : '')]) ?>
        <?php foreach ($statusMeta as $status => $meta): ?>
            <?= Html::a(
                '<span>' . Html::encode($meta['label']) . '</span><span class="housing-status-filter__count">' . (int)($counts[$status]['total'] ?? 0) . '</span>',
                ['index', 'status' => $status, 'building_id' => $filters['building_id'], 'q' => $filters['q']],
                ['class' => 'housing-status-filter ' . (($filters['status'] ?? '') === $status ? 'is-active' : '')]
            ) ?>
        <?php endforeach; ?>
    </div>

    <div class="housing-room-board overflow-hidden">
        <?php $rendered = 0; foreach ($buildings as $building): ?>
            <?php
            $floorGroups = [];
            foreach ($building->units as $unit) {
                if (!in_array((int)$unit->id, $visibleUnitIds, true)) {
                    continue;
                }
                $floorGroups[$unit->floor_id ?: 0][] = $unit;
            }
            if (!$floorGroups) {
                continue;
            }
            $rendered++;
            ?>
            <section class="housing-building">
                <header class="housing-building__head">
                    <div>
                        <h2 class="h6 fw-semibold mb-1"><?= Html::encode($building->name) ?></h2>
                        <div class="small text-body-secondary"><?= Html::encode(Building::typeOptions()[$building->building_type] ?? $building->building_type) ?></div>
                    </div>
                    <span class="small text-body-secondary"><?= array_sum(array_map('count', $floorGroups)) ?> ห้อง</span>
                </header>
                <?php foreach ($floorGroups as $floorId => $units): ?>
                    <?php $floorName = $floorId && $units[0]->floor ? $units[0]->floor->name : ($building->building_type === Building::TYPE_HOUSE ? 'บ้านพัก' : 'ไม่ระบุชั้น'); ?>
                    <div class="housing-floor">
                        <div class="housing-floor__label"><?= Html::encode($floorName) ?></div>
                        <div class="housing-unit-grid">
                            <?php foreach ($units as $unit): $meta = $statusMeta[$unit->status] ?? ['label' => $unit->status, 'class' => 'is-inactive']; ?>
                                <div class="housing-unit <?= $meta['class'] ?>">
                                    <div class="housing-unit__head">
                                        <div><div class="housing-unit__code"><?= Html::a(Html::encode($unit->code), ['/housing/unit/view', 'id' => $unit->id], ['class' => 'stretched-link', 'title' => 'ดูรายละเอียดห้อง']) ?></div><div class="housing-unit__type"><?= Html::encode(Unit::modeOptions()[$unit->occupancy_mode] ?? '') ?></div></div>
                                        <span class="housing-unit__status <?= $meta['class'] ?>"><?= Html::encode($meta['label']) ?></span>
                                    </div>
                                    <?php if ($unit->rooms): ?>
                                        <div class="housing-unit__rooms">
                                            <?php foreach ($unit->rooms as $room): $roomMeta = $statusMeta[$room->status] ?? ['label' => Unit::statusOptions()[$room->status] ?? $room->status, 'class' => 'is-inactive']; ?>
                                                <?= Html::a('<strong>' . Html::encode($room->code) . '</strong><span>' . Html::encode($roomMeta['label']) . '</span>', ['/housing/unit/view', 'id' => $unit->id, 'room_id' => $room->id], ['class' => 'housing-room ' . $roomMeta['class'], 'title' => 'ดูรายละเอียดห้องย่อย']) ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="housing-unit__empty"><?= Html::encode($unit->name) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php endforeach; ?>
        <?php if (!$rendered): ?>
            <div class="housing-empty"><div class="fw-semibold">ไม่พบห้องตามตัวกรอง</div><div class="small text-body-secondary mt-1">ลองเปลี่ยนอาคาร สถานะ หรือคำค้นหา</div></div>
        <?php endif; ?>
    </div>
</div>

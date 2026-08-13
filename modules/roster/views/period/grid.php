<?php

use app\modules\roster\models\Period;
use app\modules\roster\models\Request as RosterRequest;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Period $period */
/** @var array $employees */
/** @var app\modules\roster\models\ShiftType[] $types */
/** @var array $unitShifts */
/** @var array $grid */
/** @var array $counts */
/** @var array $holidays */
/** @var array $weekends */
/** @var array $leaves */
/** @var array $trips */
/** @var array $requests */
/** @var array $swaps */
/** @var bool $canEdit */
/** @var bool $canManage */
/** @var bool $canReview */
/** @var bool $canApprove */
/** @var bool $reviewerIsApprover */
/** @var array $chain */
/** @var int $pendingCount */

// ประกาศแล้ว = แก้กริดไม่ได้ แต่หัวหน้าหน่วยยังเปลี่ยนตัวฉุกเฉินได้ผ่านใบขอ
$canReplace = $canManage && $period->allowsSwap();

$this->title = 'จัดเวร ' . $period->unitName();
$this->params['breadcrumbs'][] = ['label' => 'ตารางเวร', 'url' => ['index']];
$this->params['breadcrumbs'][] = $period->monthLabel();

$days = $period->daysInMonth();
$dowNames = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];

// เวรที่หน่วยนี้ตั้งไว้ — ชื่อและอัตราค่าตอบแทนมาจากหน่วยงาน ไม่ใช่ชนิดกลาง
$unitShiftList = array_values($unitShifts);

$totalNeededPerDay = 0;
foreach ($unitShifts as $unitShift) {
    $totalNeededPerDay += (int) $unitShift->required_staff;
}

// สรุปท้ายแถวรายคน — วันหยุดไม่นับเป็นเวรทำงานและไม่คิดเงิน
$empTotals = [];
foreach ($grid as $empId => $byDay) {
    $work = 0;
    $off = 0;
    $ot = 0;
    $pay = 0.0;
    foreach ($byDay as $items) {
        foreach ($items as $item) {
            if ($item->isOff()) {
                $off++;
                continue;
            }
            $work++;
            if ($item->isOt()) {
                $ot++;
            }
            $pay += $item->payAmount();
        }
    }
    $empTotals[(int) $empId] = ['work' => $work, 'off' => $off, 'ot' => $ot, 'pay' => $pay];
}
$totalNeeded = $totalNeededPerDay * $days;
$totalAssigned = 0;
foreach ($grid as $byDay) {
    foreach ($byDay as $items) {
        $totalAssigned += count($items);
    }
}
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0 flex-wrap justify-content-center justify-content-lg-start">
        <i class="bi bi-grid-3x3"></i>
        <?= Html::encode($period->title) ?>
        <span class="badge bg-<?= $period->getStatusColor() ?>-subtle text-<?= $period->getStatusColor() ?>-emphasis">
            <?= Html::encode($period->getStatusLabel()) ?>
        </span>
    </h4>
    <div class="text-body-secondary small">
        <?= Html::encode($period->unitName()) ?> · <?= Html::encode($period->monthLabel()) ?>
        <?php if ($period->shiftIds()): ?>
            · ครอบ <?= count($unitShifts) ?> เวร
        <?php endif; ?>
    </div>
    <div class="text-body-secondary small" id="roster-summary">
        <?php if ($totalNeeded > 0): ?>
            จัดแล้ว <strong id="summary-assigned"><?= number_format($totalAssigned) ?></strong>/<span id="summary-needed"><?= number_format($totalNeeded) ?></span> ช่องเวร
        <?php else: ?>
            จัดแล้ว <strong id="summary-assigned"><?= number_format($totalAssigned) ?></strong> ช่องเวร
        <?php endif; ?>
    </div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/roster/menu', ['active' => 'period', 'pendingCount' => $pendingCount]) ?>
<?php $this->endBlock(); ?>

<?php if ($chain): ?>
    <div class="card border shadow-sm mb-3">
        <div class="card-body py-2 d-flex flex-wrap align-items-center gap-2 small">
            <span class="text-body-secondary">สายอนุมัติ:</span>
            <?php foreach ($chain as $i => $step): ?>
                <?php if ($i > 0): ?><i class="bi bi-chevron-right text-body-secondary"></i><?php endif; ?>
                <span class="badge bg-body-tertiary text-body border">
                    <?= Html::encode($step['label']) ?>
                    <?php if ($step['name']): ?>
                        · <?= Html::encode($step['name']) ?>
                    <?php elseif ($step['org']): ?>
                        · <?= Html::encode($step['org']) ?>
                    <?php endif; ?>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($period->isLive()): ?>
    <div class="alert alert-success border-0 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
        <div>
            <i class="bi bi-megaphone"></i>
            ตารางเวรนี้<?= Html::encode($period->getStatusLabel()) ?>แล้ว —
            <strong>แก้ในกริดไม่ได้</strong> ทุกการเปลี่ยนตัวต้องผ่านใบเปลี่ยนตัวเวร เพื่อให้ตรวจสอบย้อนหลังได้
            <?php if ($canReplace): ?>
                <br><span class="small">กรณีฉุกเฉิน: คลิกที่ช่องเวรเพื่อเปลี่ยนตัวทันที (ต้องระบุเหตุผล)</span>
            <?php endif; ?>
        </div>
        <?= Html::a('<i class="bi bi-arrow-left-right"></i> ดูใบเปลี่ยนตัว', ['swaps', 'id' => $period->id], [
            'class' => 'btn btn-sm btn-outline-success text-nowrap',
        ]) ?>
    </div>
<?php endif; ?>

<?php if (empty($unitShifts)): ?>
    <div class="alert alert-warning border-0 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2">
        <div>
            <i class="bi bi-exclamation-triangle"></i>
            หน่วยงานนี้ยังไม่ได้ตั้ง<strong>เวลาเวรและจำนวนคนที่ต้องการ</strong> —
            จัดเวรได้ แต่ระบบยังบอกไม่ได้ว่าจัดครบหรือยัง และตรวจเวลาพักระหว่างเวรไม่ได้
        </div>
        <?= Html::a('<i class="bi bi-gear"></i> ตั้งค่าเลย', ['/roster/setting/unit', 'unit_id' => $period->unit_id], [
            'class' => 'btn btn-sm btn-warning text-nowrap',
        ]) ?>
    </div>
<?php endif; ?>

<?php if (empty($employees)): ?>
    <div class="alert alert-info border-0">
        <i class="bi bi-info-circle"></i> ไม่พบเจ้าหน้าที่ในหน่วยงานนี้
    </div>
    <?php return; ?>
<?php endif; ?>

<!-- แถบเครื่องมือ -->
<div class="card border shadow-sm mb-3">
    <div class="card-body d-flex flex-column flex-lg-row gap-3 justify-content-between align-items-lg-center">
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <span class="text-body-secondary small me-1">เลือกเวรแล้วคลิกช่อง:</span>
            <?php foreach ($unitShiftList as $i => $unitShift): ?>
                <input type="radio" class="btn-check" name="shift-pen" id="pen-<?= $unitShift->id ?>"
                       value="<?= $unitShift->id ?>" <?= $i === 0 ? 'checked' : '' ?> autocomplete="off">
                <label class="btn btn-sm rounded-pill <?= $unitShift->cellClass() ?> border" for="pen-<?= $unitShift->id ?>">
                    <strong><?= Html::encode($unitShift->displayShort()) ?></strong>
                    <span class="d-none d-md-inline"><?= Html::encode($unitShift->displayName()) ?></span>
                    <span class="d-none d-lg-inline opacity-75 small"><?= Html::encode($unitShift->timeRangeLabel()) ?></span>
                    <?php if ($unitShift->is_standby): ?>
                        <i class="bi bi-telephone" title="เวรรอเรียก/นอกหน่วย"></i>
                    <?php endif; ?>
                </label>
            <?php endforeach; ?>
            <input type="radio" class="btn-check" name="shift-pen" id="pen-erase" value="erase" autocomplete="off">
            <label class="btn btn-sm rounded-pill btn-outline-secondary" for="pen-erase">
                <i class="bi bi-eraser"></i> ลบ
            </label>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-copy-previous">
                    <i class="bi bi-clipboard-check"></i> คัดลอกเดือนก่อน
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" id="btn-clear">
                    <i class="bi bi-x-circle"></i> ล้างทั้งเดือน
                </button>
                <button type="button" class="btn btn-sm btn-primary" data-transition="<?= Period::STATUS_SUBMITTED ?>">
                    <i class="bi bi-send"></i> ส่งตรวจสอบ
                </button>
            <?php endif; ?>

            <?php if ($period->status === Period::STATUS_SUBMITTED && $reviewerIsApprover): ?>
                <button type="button" class="btn btn-sm btn-success" id="btn-review-approve">
                    <i class="bi bi-check2-all"></i> ตรวจสอบและอนุมัติ
                </button>
            <?php elseif ($period->status === Period::STATUS_SUBMITTED && $canReview): ?>
                <button type="button" class="btn btn-sm btn-info" data-transition="<?= Period::STATUS_REVIEWED ?>">
                    <i class="bi bi-check2-circle"></i> ตรวจสอบแล้ว
                </button>
            <?php endif; ?>

            <?php if ($period->status === Period::STATUS_REVIEWED && $canApprove): ?>
                <button type="button" class="btn btn-sm btn-success" data-transition="<?= Period::STATUS_PUBLISHED ?>">
                    <i class="bi bi-megaphone"></i> อนุมัติและประกาศ
                </button>
            <?php endif; ?>

            <?php if (in_array($period->status, [Period::STATUS_SUBMITTED, Period::STATUS_REVIEWED], true) && ($canReview || $canApprove)): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-transition="<?= Period::STATUS_DRAFT ?>">
                    <i class="bi bi-arrow-counterclockwise"></i> ส่งกลับแก้
                </button>
            <?php endif; ?>

            <?php if ($period->status === Period::STATUS_PUBLISHED): ?>
                <?= Html::a('<i class="bi bi-arrow-left-right"></i> ใบเปลี่ยนตัวเวร', ['swaps', 'id' => $period->id], [
                    'class' => 'btn btn-sm btn-outline-primary',
                ]) ?>
                <?php if ($canApprove || $canReview): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-transition="<?= Period::STATUS_CLOSED ?>">
                        <i class="bi bi-lock"></i> ปิดรอบ
                    </button>
                <?php endif; ?>
            <?php endif; ?>

            <?= Html::a('<i class="bi bi-printer"></i> พิมพ์', ['print', 'id' => $period->id], [
                'class' => 'btn btn-sm btn-outline-secondary', 'target' => '_blank',
            ]) ?>
            <?= Html::a('<i class="bi bi-file-earmark-excel"></i> Excel', ['export', 'id' => $period->id], [
                'class' => 'btn btn-sm btn-outline-success',
            ]) ?>
        </div>
    </div>
</div>

<!-- กริดจัดเวร -->
<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary text-center py-2">
        <div class="fw-bold"><?= Html::encode($period->unitName()) ?></div>
        <div class="small text-body-secondary">
            <?= Html::encode($period->title) ?> · ประจำเดือน<?= Html::encode($period->monthLabel()) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive roster-scroll">
            <table class="table table-bordered table-sm align-middle mb-0 roster-grid">
                <thead class="bg-body-tertiary">
                    <tr>
                        <th class="roster-sticky-col bg-body-tertiary" style="min-width:200px">เจ้าหน้าที่</th>
                        <?php for ($d = 1; $d <= $days; $d++): ?>
                            <?php
                            $ts = strtotime($period->dateOfDay($d));
                            $isHoliday = isset($holidays[$d]);
                            $isWeekend = !empty($weekends[$d]);
                            $headClass = $isHoliday ? 'bg-danger-subtle text-danger-emphasis'
                                : ($isWeekend ? 'bg-secondary-subtle text-secondary-emphasis' : '');
                            ?>
                            <th class="text-center p-1 <?= $headClass ?>" style="min-width:42px"
                                <?= $isHoliday ? 'title="' . Html::encode($holidays[$d]) . '"' : '' ?>>
                                <div class="small fw-bold"><?= $d ?></div>
                                <div class="small opacity-75"><?= $dowNames[(int) date('w', $ts)] ?></div>
                            </th>
                        <?php endfor; ?>
                        <th class="text-center bg-body-tertiary" style="min-width:56px">รวมเวร</th>
                        <th class="text-center bg-body-tertiary" style="min-width:48px">วันหยุด</th>
                        <th class="text-center bg-body-tertiary" style="min-width:48px">OT</th>
                        <th class="text-end bg-body-tertiary" style="min-width:90px">ค่าตอบแทน</th>
                    </tr>
                </thead>

                <tbody class="table-group-divider">
                    <?php foreach ($employees as $emp): ?>
                        <?php
                        $empId = (int) $emp['id'];
                        $empTotal = 0;
                        foreach ($grid[$empId] ?? [] as $items) {
                            $empTotal += count($items);
                        }
                        ?>
                        <tr>
                            <td class="roster-sticky-col bg-body">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-truncate" style="max-width:160px">
                                        <?= Html::encode(trim(($emp['prefix'] ?? '') . $emp['fname'] . ' ' . $emp['lname'])) ?>
                                    </span>
                                    <?php if (($emp['work_shift'] ?? '') === 'shift'): ?>
                                        <span class="badge bg-info-subtle text-info-emphasis" title="ขึ้นเวร 8">8</span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <?php for ($d = 1; $d <= $days; $d++): ?>
                                <?php
                                $items = $grid[$empId][$d] ?? [];
                                $leave = $leaves[$empId][$d] ?? null;
                                $trip = $trips[$empId][$d] ?? null;
                                $reqs = $requests[$empId][$d] ?? [];
                                $isHoliday = isset($holidays[$d]);
                                $isWeekend = !empty($weekends[$d]);

                                $cellClasses = ['roster-cell', 'text-center', 'p-0'];
                                if ($isHoliday) {
                                    $cellClasses[] = 'roster-holiday';
                                } elseif ($isWeekend) {
                                    $cellClasses[] = 'roster-weekend';
                                }

                                // เหตุผลที่ไม่ควรจัดเวรวันนี้ — แสดงเป็นพื้นหลัง + tooltip
                                $blockers = [];
                                if ($leave) {
                                    $blockers[] = 'ลา: ' . $leave['title'];
                                }
                                if ($trip) {
                                    $blockers[] = 'ไปราชการ: ' . ($trip[0]['title'] ?? '');
                                }
                                foreach ($reqs as $req) {
                                    if ($req->type === RosterRequest::TYPE_OFF) {
                                        $blockers[] = 'ขอหยุด' . ($req->reason ? ' (' . $req->reason . ')' : '');
                                    } else {
                                        $blockers[] = 'ขออยู่เวร' . ($req->shiftType ? ' ' . $req->shiftType->short_name : '');
                                    }
                                }
                                ?>
                                <td class="<?= implode(' ', $cellClasses) ?>"
                                    data-emp="<?= $empId ?>" data-day="<?= $d ?>"
                                    <?= $blockers ? 'title="' . Html::encode(implode(' · ', $blockers)) . '"' : '' ?>>

                                    <div class="roster-cell-inner">
                                        <?php foreach ($items as $item): ?>
                                            <?php $itemSwaps = $swaps[(int) $item->id] ?? []; ?>
                                            <span class="roster-chip <?= $item->shiftCellClass() ?><?= $itemSwaps ? ' roster-chip-swapped' : '' ?>"
                                                  data-shift="<?= (int) $item->unit_shift_id ?>"
                                                  data-item="<?= (int) $item->id ?>"
                                                  title="<?= Html::encode($item->shiftName() . ($itemSwaps ? ' · เปลี่ยนตัวแล้ว ' . count($itemSwaps) . ' ครั้ง' : '')) ?>">
                                                <?= Html::encode($item->shiftShort()) ?>
                                            </span>
                                        <?php endforeach; ?>

                                        <?php if ($leave): ?>
                                            <span class="roster-flag text-danger-emphasis"><?= Html::encode($leave['ab']) ?></span>
                                        <?php elseif ($trip): ?>
                                            <span class="roster-flag text-info-emphasis">ร</span>
                                        <?php endif; ?>

                                        <?php foreach ($reqs as $req): ?>
                                            <?php if ($req->type === RosterRequest::TYPE_OFF): ?>
                                                <span class="roster-req roster-req-off" title="ขอหยุด">×</span>
                                            <?php else: ?>
                                                <span class="roster-req roster-req-on" title="ขออยู่เวร">✓</span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                            <?php endfor; ?>

                            <?php $tot = $empTotals[$empId] ?? ['work' => 0, 'off' => 0, 'ot' => 0, 'pay' => 0.0]; ?>
                            <td class="text-center fw-semibold emp-total" data-emp="<?= $empId ?>"><?= $tot['work'] ?></td>
                            <td class="text-center emp-off text-body-secondary" data-emp="<?= $empId ?>"><?= $tot['off'] ?></td>
                            <td class="text-center emp-ot <?= $tot['ot'] ? 'text-warning-emphasis fw-semibold' : 'text-body-secondary' ?>"
                                data-emp="<?= $empId ?>"><?= $tot['ot'] ?></td>
                            <td class="text-end emp-pay small <?= $tot['pay'] > 0 ? 'fw-semibold' : 'text-body-secondary' ?>"
                                data-emp="<?= $empId ?>"><?= $tot['pay'] > 0 ? number_format($tot['pay'], 2) : '–' ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

                <!-- ตัวนับความครบต่อวัน — หัวใจของกริด บอกทันทีว่าวันไหนคนไม่พอ -->
                <tfoot class="bg-body-tertiary">
                    <?php foreach ($unitShiftList as $unitShift): ?>
                        <?php $need = (int) $unitShift->required_staff; ?>
                        <tr>
                            <td class="roster-sticky-col bg-body-tertiary small">
                                <span class="badge rounded-pill px-2 <?= $unitShift->cellClass() ?>">
                                    <?= Html::encode($unitShift->displayShort()) ?>
                                </span>
                                <span class="ms-1 text-body-secondary">
                                    <?= Html::encode($unitShift->displayName()) ?>
                                    <?= $need > 0 ? '· ต้องการ ' . $need : '· ไม่ระบุจำนวน' ?>
                                </span>
                            </td>
                            <?php for ($d = 1; $d <= $days; $d++): ?>
                                <?php
                                $have = $counts[$d][(int) $unitShift->id] ?? 0;
                                if ($need <= 0) {
                                    $stateClass = 'text-body-secondary';
                                } elseif ($have < $need) {
                                    $stateClass = 'text-danger-emphasis fw-bold';
                                } elseif ($have > $need) {
                                    $stateClass = 'text-warning-emphasis fw-semibold';
                                } else {
                                    $stateClass = 'text-success-emphasis';
                                }
                                ?>
                                <td class="text-center small roster-count <?= $stateClass ?>"
                                    data-day="<?= $d ?>" data-shift="<?= (int) $unitShift->id ?>" data-need="<?= $need ?>">
                                    <?= $need > 0 ? $have . '/' . $need : $have ?>
                                </td>
                            <?php endfor; ?>
                            <td colspan="4" class="small text-body-secondary">
                                <?php if ($unitShift->positionName()): ?>
                                    <?= Html::encode($unitShift->positionName()) ?>
                                <?php endif; ?>
                                <?php if ($unitShift->pay_rate): ?>
                                    · <?= Html::encode($unitShift->payLabel()) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="card-footer bg-body-tertiary d-flex flex-wrap gap-3 align-items-center small text-body-secondary">
        <span><span class="roster-req roster-req-off">×</span> ขอหยุด</span>
        <span><span class="roster-req roster-req-on">✓</span> ขออยู่เวร</span>
        <span><span class="roster-flag text-danger-emphasis">ป</span> ลา</span>
        <span><span class="roster-flag text-info-emphasis">ร</span> ไปราชการ</span>
        <span class="ms-auto">
            <i class="bi bi-info-circle"></i>
            คำเตือนจากกฎจะขึ้นตอนคลิก แต่ระบบยังบันทึกให้เสมอ
        </span>
    </div>
</div>

<?php
$this->registerCss(<<<'CSS'
.roster-scroll { max-height: 70vh; overflow: auto; }
.roster-grid thead th { position: sticky; top: 0; z-index: 3; }
.roster-sticky-col { position: sticky; left: 0; z-index: 2; }
.roster-grid thead th.roster-sticky-col { z-index: 4; }
.roster-cell { cursor: pointer; height: 38px; }
.roster-cell:hover { outline: 2px solid var(--bs-primary); outline-offset: -2px; }
.roster-cell-inner { display: flex; flex-wrap: wrap; gap: 1px; justify-content: center; align-items: center; min-height: 34px; padding: 1px; }
.roster-chip { display: inline-block; min-width: 18px; padding: 0 3px; border-radius: var(--bs-border-radius-sm); font-size: .75rem; font-weight: 600; line-height: 1.4; }
.roster-chip-swapped { outline: 2px dotted var(--bs-warning); outline-offset: -2px; }
.roster-flag { font-size: .7rem; opacity: .85; }
.roster-req { font-size: .7rem; font-weight: 700; line-height: 1; }
.roster-req-off { color: var(--bs-danger); }
.roster-req-on { color: var(--bs-success); }
.roster-holiday { background-color: var(--bs-danger-bg-subtle); }
.roster-weekend { background-color: var(--bs-secondary-bg-subtle); }
.roster-cell.is-saving { opacity: .5; }
CSS);

$assignUrl = Url::to(['assign']);
$copyUrl = Url::to(['copy-previous', 'id' => $period->id]);
$clearUrl = Url::to(['clear', 'id' => $period->id]);
$transitionUrl = Url::to(['transition', 'id' => $period->id]);
$periodId = (int) $period->id;
$canEditJs = $canEdit ? 'true' : 'false';
$canReplaceJs = $canReplace ? 'true' : 'false';
$replaceFormUrl = Url::to(['replace-form']);
$reviewApproveUrl = Url::to(['review-and-approve', 'id' => $period->id]);

$shiftMeta = [];
foreach ($unitShiftList as $unitShift) {
    $shiftMeta[(int) $unitShift->id] = [
        's' => $unitShift->displayShort(),
        'c' => $unitShift->cellClass(),
        'n' => $unitShift->displayName(),
    ];
}
$shiftMetaJson = json_encode($shiftMeta, JSON_UNESCAPED_UNICODE);

$js = <<<JS
(function () {
    var canEdit = {$canEditJs};
    var canReplace = {$canReplaceJs};
    var periodId = {$periodId};
    var shiftMeta = {$shiftMetaJson};

    function notify(kind, message) {
        if (kind === 'ok' && typeof success === 'function') { success(message); return; }
        if (kind !== 'ok' && typeof warning === 'function') { warning(message); return; }
        alert(message);
    }

    function currentPen() {
        return jQuery('input[name="shift-pen"]:checked').val();
    }

    function repaintCell(\$cell, items) {
        var \$inner = \$cell.find('.roster-cell-inner');
        \$inner.find('.roster-chip').remove();
        items.forEach(function (shiftId) {
            var meta = shiftMeta[shiftId];
            if (!meta) { return; }
            \$inner.prepend('<span class="roster-chip ' + meta.c + '" data-shift="' + shiftId +
                '" title="' + meta.n + '">' + meta.s + '</span>');
        });
    }

    function updateCounts(day, counts) {
        jQuery('.roster-count[data-day="' + day + '"]').each(function () {
            var \$td = jQuery(this);
            var shiftId = \$td.data('shift');
            var need = parseInt(\$td.data('need'), 10) || 0;
            var have = counts[shiftId] || 0;
            \$td.text(need > 0 ? have + '/' + need : have);
            \$td.removeClass('text-danger-emphasis fw-bold text-warning-emphasis fw-semibold text-success-emphasis text-body-secondary');
            if (need <= 0) { \$td.addClass('text-body-secondary'); }
            else if (have < need) { \$td.addClass('text-danger-emphasis fw-bold'); }
            else if (have > need) { \$td.addClass('text-warning-emphasis fw-semibold'); }
            else { \$td.addClass('text-success-emphasis'); }
        });
    }

    // ตัวเลขท้ายแถวคำนวณฝั่งเซิร์ฟเวอร์ เพราะต้องรู้ว่าเวรไหนเป็นวันหยุด/นอกเวลา และอัตราเท่าไร
    function updateEmpTotal(empId, totals) {
        if (!totals) { return; }
        jQuery('.emp-total[data-emp="' + empId + '"]').text(totals.work);
        jQuery('.emp-off[data-emp="' + empId + '"]').text(totals.off);
        var \$ot = jQuery('.emp-ot[data-emp="' + empId + '"]').text(totals.ot);
        \$ot.toggleClass('text-warning-emphasis fw-semibold', totals.ot > 0)
           .toggleClass('text-body-secondary', totals.ot === 0);
        var \$pay = jQuery('.emp-pay[data-emp="' + empId + '"]');
        \$pay.text(totals.pay > 0 ? totals.pay.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '–')
            .toggleClass('fw-semibold', totals.pay > 0)
            .toggleClass('text-body-secondary', totals.pay <= 0);
    }

    jQuery('body').on('click', '.roster-cell', function () {
        var \$cell = jQuery(this);

        // ประกาศแล้ว: คลิกช่องที่มีเวร = เปิดฟอร์มเปลี่ยนตัวฉุกเฉิน แทนการแก้กริด
        if (!canEdit && canReplace) {
            var \$chip = \$cell.find('.roster-chip').first();
            if (!\$chip.length) {
                notify('warn', 'ช่องนี้ยังไม่มีเวร — เพิ่มเวรใหม่หลังประกาศไม่ได้');
                return;
            }
            jQuery('<a>').attr('href', '{$replaceFormUrl}?item_id=' + \$chip.data('item'))
                .addClass('open-modal').attr('data-size', 'modal-lg')
                .appendTo('body').trigger('click').remove();
            return;
        }
        if (!canEdit) {
            notify('warn', 'รอบเวรนี้แก้ไขไม่ได้แล้ว');
            return;
        }
        if (\$cell.hasClass('is-saving')) { return; }

        var pen = currentPen();
        var shiftId;
        if (pen === 'erase') {
            // ลบ = สลับสถานะของชิปตัวแรกในช่อง
            var \$first = \$cell.find('.roster-chip').first();
            if (!\$first.length) { return; }
            shiftId = \$first.data('shift');
        } else {
            shiftId = parseInt(pen, 10);
        }
        if (!shiftId) { return; }

        \$cell.addClass('is-saving');
        jQuery.post('{$assignUrl}', {
            period_id: periodId,
            emp_id: \$cell.data('emp'),
            day: \$cell.data('day'),
            unit_shift_id: shiftId
        }, function (res) {
            \$cell.removeClass('is-saving');
            if (res.status !== 'success') {
                notify('warn', res.message || 'บันทึกไม่สำเร็จ');
                return;
            }

            var items = [];
            \$cell.find('.roster-chip').each(function () { items.push(jQuery(this).data('shift')); });
            if (res.action === 'added') {
                items.push(shiftId);
            } else {
                items = items.filter(function (id) { return id !== shiftId; });
            }
            repaintCell(\$cell, items);
            updateCounts(\$cell.data('day'), res.counts || {});
            updateEmpTotal(\$cell.data('emp'), res.empTotals);

            if (res.summary) {
                jQuery('#summary-assigned').text(res.summary.assigned.toLocaleString());
            }
            // กฎเป็นคำเตือน — บันทึกไปแล้ว แค่บอกให้หัวหน้ารู้ตัว
            if (res.warnings && res.warnings.length) {
                notify('warn', res.warnings.join(' · '));
            }
        }).fail(function () {
            \$cell.removeClass('is-saving');
            notify('warn', 'เชื่อมต่อไม่สำเร็จ');
        });
    });

    jQuery('#btn-copy-previous').on('click', function () {
        if (!window.confirm('คัดลอกเวรจากเดือนก่อนหน้า? ระบบจะจับคู่ตามวันในสัปดาห์ ไม่ใช่เลขวันที่')) { return; }
        var \$btn = jQuery(this).prop('disabled', true);
        jQuery.post('{$copyUrl}', function (res) {
            \$btn.prop('disabled', false);
            if (res.status === 'success') { notify('ok', res.message); window.location.reload(); }
            else { notify('warn', res.message); }
        });
    });

    jQuery('#btn-clear').on('click', function () {
        if (!window.confirm('ล้างเวรทั้งเดือนของหน่วยนี้? ย้อนกลับไม่ได้')) { return; }
        jQuery.post('{$clearUrl}', function (res) {
            if (res.status === 'success') { notify('ok', res.message); window.location.reload(); }
            else { notify('warn', res.message); }
        });
    });

    jQuery('body').on('click', '[data-transition]', function () {
        var to = jQuery(this).data('transition');
        var labels = {
            submitted: 'ส่งตารางเวรนี้ให้หัวหน้ากลุ่มงานตรวจสอบ? หลังส่งแล้วจะแก้ไม่ได้จนกว่าจะถูกส่งกลับ',
            reviewed: 'ยืนยันว่าตรวจสอบตารางเวรนี้แล้ว และส่งต่อให้ผู้อำนวยการอนุมัติ?',
            published: 'อนุมัติและประกาศตารางเวรนี้? หลังประกาศจะแก้ในกริดไม่ได้ ต้องเปลี่ยนตัวผ่านใบขอเท่านั้น',
            closed: 'ปิดรอบนี้? จะเปลี่ยนตัวเวรไม่ได้อีก',
            draft: 'ดึงกลับมาให้หัวหน้าหน่วยแก้ใหม่?'
        };
        if (!window.confirm(labels[to] || 'ยืนยัน?')) { return; }
        jQuery.post('{$transitionUrl}&to=' + to, function (res) {
            if (res.status === 'success') { notify('ok', res.message); window.location.reload(); }
            else { notify('warn', res.message); }
        });
    });

    jQuery('#btn-review-approve').on('click', function () {
        if (!window.confirm('ตรวจสอบ อนุมัติ และประกาศตารางเวรนี้ในขั้นตอนเดียว?')) { return; }
        jQuery.post('{$reviewApproveUrl}', function (res) {
            if (res.status === 'success') { notify('ok', res.message); window.location.reload(); }
            else { notify('warn', res.message); }
        });
    });
})();
JS;
$this->registerJs($js);
?>

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

// ยอดที่ต้องจัดทั้งเดือน — คิดรายวันเพราะเสาร์/อาทิตย์/นักขัตฤกษ์ ใช้คนไม่เท่าวันธรรมดา
$totalNeeded = 0;
for ($d = 1; $d <= $days; $d++) {
    $dow = (int) date('w', strtotime($period->dateOfDay($d)));
    foreach ($unitShifts as $unitShift) {
        $totalNeeded += $unitShift->requiredFor(isset($holidays[$d]), $dow);
    }
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
        <span id="period-title"><?= Html::encode($period->title) ?></span>
        <?php if ($canEdit): ?>
            <button type="button" class="btn btn-sm btn-link p-0 text-body-secondary" id="btn-rename"
                    title="เปลี่ยนชื่อตารางเวร">
                <i class="bi bi-pencil-square"></i>
            </button>
        <?php endif; ?>
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
        <?php
        // ตัวเลือกเวร — ออกแบบให้รองรับเวรจำนวนมากและชื่อยาว
        //   แถบด่วน = ตัวย่ออย่างเดียว กว้างเท่ากันทุกปุ่ม เลื่อนแนวนอนได้ วางได้ 20 เวรในบรรทัดเดียว
        //   ดรอปดาวน์ = ชื่อเต็ม เวลา วิชาชีพ อัตรา พร้อมช่องค้นหา สำหรับตอนจำตัวย่อไม่ได้
        // ไม่ใช้ radio + label เพราะปุ่มชื่อยาวทำให้แถบเครื่องมือสูงจนกริดถูกดันตกจอ
        $penInitial = $unitShiftList ? (int) $unitShiftList[0]->id : 0;
        ?>
        <div class="pen-bar">
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                <span class="text-body-secondary small">เลือกเวรแล้วคลิกช่อง — กำลังใส่</span>
                <span class="pen-current-chip roster-chip" id="pen-current-chip">—</span>
                <strong class="text-body small" id="pen-current">—</strong>

                <div class="dropdown ms-auto">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle" type="button"
                            data-bs-toggle="dropdown" data-bs-auto-close="outside" id="pen-dropdown">
                        <i class="bi bi-list-ul"></i> เวรทั้งหมด (<?= count($unitShiftList) ?>)
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end pen-menu shadow">
                        <?php if (count($unitShiftList) > 6): ?>
                            <li class="px-2 pb-2">
                                <input type="search" class="form-control form-control-sm" id="pen-search"
                                       placeholder="ค้นหาชื่อเวร / ตัวย่อ" autocomplete="off">
                            </li>
                            <li><hr class="dropdown-divider"></li>
                        <?php endif; ?>
                        <?php foreach ($unitShiftList as $i => $unitShift): ?>
                            <li class="pen-option-row"
                                data-search="<?= Html::encode(mb_strtolower(
                                    $unitShift->displayName() . ' ' . $unitShift->displayShort()
                                    . ' ' . $unitShift->positionName()
                                )) ?>">
                                <button type="button" class="dropdown-item d-flex align-items-center gap-2 pen-option"
                                        data-pen="<?= (int) $unitShift->id ?>">
                                    <span class="roster-chip <?= $unitShift->cellClass() ?>">
                                        <?= Html::encode($unitShift->displayShort()) ?>
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-block"><?= Html::encode($unitShift->displayName()) ?></span>
                                        <span class="d-block small text-body-secondary">
                                            <?= Html::encode($unitShift->timeRangeLabel()) ?>
                                            <?php if ($unitShift->positionName()): ?>
                                                · <?= Html::encode($unitShift->positionName()) ?>
                                            <?php endif; ?>
                                            <?php if ($unitShift->pay_rate): ?>
                                                · <?= Html::encode($unitShift->payLabel()) ?>
                                            <?php endif; ?>
                                        </span>
                                    </span>
                                    <?php if ($unitShift->is_standby): ?>
                                        <i class="bi bi-telephone text-body-secondary" title="เวรรอเรียก/นอกหน่วย"></i>
                                    <?php endif; ?>
                                    <?php if ($i < 9): ?>
                                        <kbd class="pen-kbd"><?= $i + 1 ?></kbd>
                                    <?php endif; ?>
                                </button>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 pen-option"
                                    data-pen="erase">
                                <span class="roster-chip bg-body-tertiary text-body border"><i class="bi bi-eraser"></i></span>
                                <span class="flex-grow-1">ลบเวรออกจากช่อง</span>
                                <kbd class="pen-kbd">0</kbd>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="pen-quick">
                <?php foreach ($unitShiftList as $unitShift): ?>
                    <button type="button" class="pen-chip <?= $unitShift->cellClass() ?>"
                            data-pen="<?= (int) $unitShift->id ?>"
                            title="<?= Html::encode(
                                $unitShift->displayName() . ' ' . $unitShift->timeRangeLabel()
                                . ($unitShift->positionName() ? ' · ' . $unitShift->positionName() : '')
                            ) ?>">
                        <?= Html::encode($unitShift->displayShort()) ?>
                    </button>
                <?php endforeach; ?>
                <button type="button" class="pen-chip pen-chip-erase bg-body-tertiary text-body"
                        data-pen="erase" title="ลบเวรออกจากช่อง">
                    <i class="bi bi-eraser"></i>
                </button>
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <?php if ($canEdit): ?>
                <button type="button" class="btn btn-sm btn-success" id="btn-auto-fill">
                    <i class="bi bi-magic"></i> จัดเวรอัตโนมัติ
                </button>
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
            <span class="period-title-echo"><?= Html::encode($period->title) ?></span>
            · ประจำเดือน<?= Html::encode($period->monthLabel()) ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive roster-scroll">
            <table class="table table-bordered table-sm align-middle mb-0 roster-grid">
                <thead class="bg-body-tertiary">
                    <tr>
                        <th class="roster-sticky-col bg-body-tertiary">เจ้าหน้าที่</th>
                        <!-- ค่าตอบแทนติดกับชื่อและตรึงไว้ ไม่งั้นต้องเลื่อนผ่าน 31 วันถึงจะเห็นเงิน -->
                        <th class="roster-sticky-pay bg-body-tertiary text-end">ค่าตอบแทน</th>
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
                        <?php $tot = $empTotals[$empId] ?? ['work' => 0, 'off' => 0, 'ot' => 0, 'pay' => 0.0]; ?>
                        <tr class="roster-row" data-position="<?= (int) ($emp['employee_position_id'] ?? 0) ?>">
                            <td class="roster-sticky-col bg-body">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-truncate" style="max-width:150px">
                                        <?= Html::encode(trim(($emp['prefix'] ?? '') . $emp['fname'] . ' ' . $emp['lname'])) ?>
                                    </span>
                                    <?php if (($emp['work_shift'] ?? '') === 'shift'): ?>
                                        <span class="badge bg-info-subtle text-info-emphasis" title="ขึ้นเวร 8">8</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-body-secondary text-truncate" style="max-width:190px">
                                    <?= Html::encode($emp['position_name'] ?: 'ไม่ระบุตำแหน่ง') ?>
                                </div>
                            </td>

                            <td class="roster-sticky-pay bg-body text-end emp-pay <?= $tot['pay'] > 0 ? 'fw-semibold' : 'text-body-secondary' ?>"
                                data-emp="<?= $empId ?>"><?= $tot['pay'] > 0 ? number_format($tot['pay'], 2) : '–' ?></td>

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

                            <td class="text-center fw-semibold emp-total" data-emp="<?= $empId ?>"><?= $tot['work'] ?></td>
                            <td class="text-center emp-off text-body-secondary" data-emp="<?= $empId ?>"><?= $tot['off'] ?></td>
                            <td class="text-center emp-ot <?= $tot['ot'] ? 'text-warning-emphasis fw-semibold' : 'text-body-secondary' ?>"
                                data-emp="<?= $empId ?>"><?= $tot['ot'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

                <!-- ตัวนับความครบต่อวัน — หัวใจของกริด บอกทันทีว่าวันไหนคนไม่พอ -->
                <tfoot class="bg-body-tertiary">
                    <?php foreach ($unitShiftList as $unitShift): ?>
                        <tr>
                            <td class="roster-sticky-col bg-body-tertiary small">
                                <span class="badge rounded-pill px-2 <?= $unitShift->cellClass() ?>">
                                    <?= Html::encode($unitShift->displayShort()) ?>
                                </span>
                                <span class="ms-1 text-body-secondary">
                                    <?= Html::encode($unitShift->displayName()) ?>
                                    <?= $unitShift->hasRequirement()
                                        ? '· ต้องการ ' . Html::encode($unitShift->requiredLabel())
                                        : '· ไม่ระบุจำนวน' ?>
                                </span>
                            </td>
                            <td class="roster-sticky-pay bg-body-tertiary text-end small text-body-secondary">
                                <?= $unitShift->pay_rate ? Html::encode($unitShift->payLabel()) : '–' ?>
                            </td>
                            <?php for ($d = 1; $d <= $days; $d++): ?>
                                <?php
                                // จำนวนที่ต้องการต่างกันตามประเภทวัน — เสาร์/อาทิตย์/นักขัตฤกษ์
                                $need = $unitShift->requiredFor(
                                    isset($holidays[$d]),
                                    (int) date('w', strtotime($period->dateOfDay($d)))
                                );
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
                            <td colspan="3" class="small text-body-secondary text-truncate" style="max-width:150px">
                                <?= Html::encode($unitShift->positionName() ?: 'ทุกวิชาชีพ') ?>
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
        <?php if ($canEdit): ?>
            <span><kbd>1</kbd>–<kbd>9</kbd> สลับเวร · <kbd>0</kbd> ลบ</span>
        <?php endif; ?>
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

/* สองคอลัมน์ซ้ายตรึงไว้: ชื่อเจ้าหน้าที่ แล้วต่อด้วยค่าตอบแทน
   ต้องกำหนดความกว้างตายตัวทั้งคู่ เพราะคอลัมน์ที่สองต้องรู้ว่าจะไปเกาะที่ left เท่าไร */
.roster-sticky-col {
    position: sticky; left: 0; z-index: 2;
    width: 200px; min-width: 200px; max-width: 200px;
}
.roster-sticky-pay {
    position: sticky; left: 200px; z-index: 2;
    width: 92px; min-width: 92px; max-width: 92px;
    font-variant-numeric: tabular-nums;
    /* border-collapse กินเส้นขอบของ sticky cell — ใช้เงาแทนเพื่อให้เห็นรอยต่อกับวันที่ */
    box-shadow: 2px 0 0 -1px var(--bs-border-color);
}
.roster-grid thead th.roster-sticky-col,
.roster-grid thead th.roster-sticky-pay { z-index: 4; }

/* ช่องเวร — ขยายให้กดง่ายและอ่านตัวย่อออกจากระยะปกติ */
.roster-cell { cursor: pointer; height: 48px; }
.roster-cell:hover { outline: 2px solid var(--bs-primary); outline-offset: -2px; }
.roster-cell-inner { display: flex; flex-wrap: wrap; gap: 2px; justify-content: center; align-items: center; min-height: 44px; padding: 2px; }
.roster-chip {
    display: inline-block; min-width: 26px; padding: 2px 6px;
    border-radius: var(--bs-border-radius); font-size: .9rem; font-weight: 700; line-height: 1.35;
}
.roster-chip-swapped { outline: 2px dotted var(--bs-warning); outline-offset: -2px; }
.roster-flag { font-size: .78rem; opacity: .85; }
.roster-req { font-size: .78rem; font-weight: 700; line-height: 1; }
.roster-req-off { color: var(--bs-danger); }
.roster-req-on { color: var(--bs-success); }
.roster-holiday { background-color: var(--bs-danger-bg-subtle); }
.roster-weekend { background-color: var(--bs-secondary-bg-subtle); }
.roster-cell.is-saving { opacity: .5; }
/* เวรที่ระบุวิชาชีพ — หรี่แถวคนที่วิชาชีพไม่ตรง เพื่อไม่ต้องจำว่าใครเป็นอะไร */
.roster-row.is-dimmed { opacity: .32; }
.roster-row.is-dimmed .roster-cell { cursor: not-allowed; }

/* ── ตัวเลือกเวร ──────────────────────────────────────────────────────────
   แถบด่วนใช้ตัวย่ออย่างเดียวและกว้างเท่ากันทุกปุ่ม ชื่อเวรจะยาวแค่ไหนก็ไม่ดันแถว
   ถ้าเวรเยอะจนเกินความกว้าง แถบจะเลื่อนแนวนอนแทนที่จะขึ้นบรรทัดใหม่เรื่อยๆ */
.pen-quick {
    display: flex; gap: .35rem; overflow-x: auto; padding-bottom: .25rem;
    scrollbar-width: thin;
}
.pen-chip {
    flex: 0 0 auto;
    min-width: 46px; height: 38px; padding: 0 .5rem;
    border: 2px solid transparent; border-radius: var(--bs-border-radius);
    font-weight: 700; font-size: .95rem; line-height: 1;
    opacity: .5; filter: grayscale(.4);
    transition: opacity .15s ease-out, transform .15s ease-out, box-shadow .15s ease-out;
}
.pen-chip:hover { opacity: .9; filter: none; }
.pen-chip.is-active {
    opacity: 1; filter: none;
    border-color: var(--bs-emphasis-color);
    box-shadow: 0 0 0 .2rem var(--bs-primary-bg-subtle), 0 2px 6px rgba(0, 0, 0, .18);
    transform: translateY(-1px);
}
.pen-chip-erase { min-width: 40px; }
.pen-current-chip { min-width: 32px; text-align: center; }
.pen-menu { max-height: 60vh; overflow-y: auto; min-width: 320px; padding-top: .5rem; }
.pen-menu .pen-option.is-active { background-color: var(--bs-primary-bg-subtle); font-weight: 600; }
.pen-kbd { font-size: .7rem; opacity: .6; }
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
$autoFillUrl = Url::to(['auto-fill', 'id' => $period->id]);
$renameUrl = Url::to(['rename', 'id' => $period->id]);

$shiftMeta = [];
foreach ($unitShiftList as $unitShift) {
    $shiftMeta[(int) $unitShift->id] = [
        's' => $unitShift->displayShort(),
        'c' => $unitShift->cellClass(),
        'n' => $unitShift->displayName(),
        'p' => (int) $unitShift->position_id, // 0 = ไม่จำกัดวิชาชีพ
        'o' => (int) $unitShift->sort_order,  // ลำดับการเรียงชิปในช่อง
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

    // ── ปากกาเลือกเวร ────────────────────────────────────────────────────
    // เก็บสถานะไว้ในตัวแปรตัวเดียว แทนที่จะอ่านจาก radio ที่ถูกเช็ค
    // เพราะตอนนี้มีตัวควบคุม 3 ทาง (แถบด่วน · ดรอปดาวน์ · แป้นตัวเลข) ที่ต้องตรงกันเสมอ
    var pen = '{$penInitial}';

    function currentPen() {
        return pen;
    }

    function setPen(value) {
        pen = String(value);
        var meta = shiftMeta[pen];

        jQuery('.pen-chip').removeClass('is-active')
            .filter('[data-pen="' + pen + '"]').addClass('is-active');
        jQuery('.pen-option').removeClass('is-active')
            .filter('[data-pen="' + pen + '"]').addClass('is-active');

        // บอกเป็นตัวหนังสือด้วยว่ากำลังถือเวรอะไร สีกับตัวย่ออย่างเดียวไม่พอถ้าเวรเยอะ
        var \$chip = jQuery('#pen-current-chip');
        if (pen === 'erase') {
            jQuery('#pen-current').text('ลบเวรออกจากช่อง');
            \$chip.attr('class', 'pen-current-chip roster-chip bg-body-tertiary text-body border')
                 .html('<i class="bi bi-eraser"></i>');
        } else if (meta) {
            jQuery('#pen-current').text(meta.n + (meta.p ? '' : ' (ทุกวิชาชีพ)'));
            \$chip.attr('class', 'pen-current-chip roster-chip ' + meta.c).text(meta.s);
        } else {
            jQuery('#pen-current').text('—');
            \$chip.attr('class', 'pen-current-chip roster-chip bg-body-tertiary text-body border').text('—');
        }

        applyPositionFilter();
    }

    // เวรที่ระบุวิชาชีพ — หรี่แถวคนที่ไม่ตรง เพื่อให้หัวหน้าเห็นทันทีว่าคลิกใครได้
    // (หน่วยหนึ่งมีถึง 4 วิชาชีพ 25 คน จำไม่ไหวว่าใครเป็นพยาบาลใครเป็นผู้ช่วย)
    function applyPositionFilter() {
        var meta = shiftMeta[currentPen()];
        var wanted = meta ? meta.p : 0;
        if (!wanted) {
            jQuery('.roster-row').removeClass('is-dimmed');
            return;
        }
        jQuery('.roster-row').each(function () {
            var \$row = jQuery(this);
            \$row.toggleClass('is-dimmed', parseInt(\$row.data('position'), 10) !== wanted);
        });
    }

    jQuery('body').on('click', '.pen-chip, .pen-option', function () {
        setPen(jQuery(this).data('pen'));
        // เลือกจากดรอปดาวน์แล้วปิดเอง — ตั้ง autoClose ไว้ outside เพื่อให้ช่องค้นหาใช้ได้
        var dd = jQuery(this).closest('.dropdown').find('[data-bs-toggle="dropdown"]')[0];
        if (dd && typeof bootstrap !== 'undefined') {
            var inst = bootstrap.Dropdown.getInstance(dd);
            if (inst) { inst.hide(); }
        }
    });

    jQuery('#pen-search').on('input', function () {
        var q = jQuery(this).val().toLowerCase().trim();
        jQuery('.pen-option-row').each(function () {
            var \$row = jQuery(this);
            \$row.toggle(!q || String(\$row.data('search')).indexOf(q) !== -1);
        });
    });
    jQuery('.pen-menu').on('click', '#pen-search', function (e) { e.stopPropagation(); });

    // แป้น 1-9 สลับเวร · 0 = ลบ — จัดทั้งเดือนต้องคลิกหลายร้อยครั้ง
    // ถ้าต้องเลื่อนเมาส์ไปกดปุ่มเวรทุกครั้งจะช้ามาก
    jQuery(document).on('keydown', function (e) {
        if (!canEdit || e.ctrlKey || e.altKey || e.metaKey) { return; }
        if (jQuery(e.target).is('input, textarea, select')) { return; }
        if (e.key === '0') { setPen('erase'); return; }
        var idx = parseInt(e.key, 10);
        if (!idx || idx < 1 || idx > 9) { return; }
        var \$chip = jQuery('.pen-chip').not('.pen-chip-erase').eq(idx - 1);
        if (\$chip.length) { setPen(\$chip.data('pen')); }
    });

    setPen(pen);

    // คอลัมน์ค่าตอบแทนเกาะอยู่ทางขวาของคอลัมน์ชื่อ ซึ่งเบราว์เซอร์อาจไม่เคารพ max-width
    // ในตาราง auto layout จึงวัดความกว้างจริงแล้วตั้ง left เอง ไม่ยึดค่าใน CSS
    function alignStickyPay() {
        var w = jQuery('.roster-grid thead th.roster-sticky-col').outerWidth();
        if (w) { jQuery('.roster-sticky-pay').css('left', w + 'px'); }
    }
    alignStickyPay();
    jQuery(window).on('resize', alignStickyPay);

    function repaintCell(\$cell, items) {
        var \$inner = \$cell.find('.roster-cell-inner');
        \$inner.find('.roster-chip').remove();
        // เรียงตามลำดับเวรที่ตั้งไว้ (เช้า→บ่าย→ดึก) ให้ตรงกับที่เซิร์ฟเวอร์วาด
        // ไม่ใช่ตามลำดับที่คลิก — คลิกบ่ายก่อนเช้าก็ต้องขึ้น "ช/บ"
        var html = items.slice().sort(function (a, b) {
            var oa = shiftMeta[a] ? shiftMeta[a].o : 0;
            var ob = shiftMeta[b] ? shiftMeta[b].o : 0;
            return oa - ob || a - b;
        }).map(function (shiftId) {
            var meta = shiftMeta[shiftId];
            if (!meta) { return ''; }
            return '<span class="roster-chip ' + meta.c + '" data-shift="' + shiftId +
                '" title="' + meta.n + '">' + meta.s + '</span>';
        }).join('');
        // ใส่ไว้หน้าสุด เพื่อให้ชิปเวรมาก่อนสัญลักษณ์ลา/ไปราชการ/คำขอ
        \$inner.prepend(html);
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

        var activePen = currentPen();
        var shiftId;
        if (activePen === 'erase') {
            // ลบ = สลับสถานะของชิปตัวแรกในช่อง
            var \$first = \$cell.find('.roster-chip').first();
            if (!\$first.length) { return; }
            shiftId = \$first.data('shift');
        } else {
            shiftId = parseInt(activePen, 10);
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

    jQuery('#btn-rename').on('click', function () {
        Swal.fire({
            title: 'เปลี่ยนชื่อตารางเวร',
            input: 'text',
            inputValue: jQuery('#period-title').text().trim(),
            inputLabel: 'ชื่อนี้ใช้แยกแผ่นในเดือนเดียวกัน เช่น บ่ายดึก / Refer / On call',
            inputAttributes: { maxlength: 255 },
            showCancelButton: true,
            confirmButtonText: 'บันทึก',
            cancelButtonText: 'ยกเลิก',
            inputValidator: function (v) {
                if (!v || !v.trim()) { return 'กรุณาระบุชื่อตารางเวร'; }
            }
        }).then(function (r) {
            if (!r.isConfirmed) { return; }
            jQuery.post('{$renameUrl}', { title: r.value.trim() }, function (res) {
                if (res.status !== 'success') { notify('warn', res.message); return; }
                jQuery('#period-title').text(res.title);
                jQuery('.period-title-echo').text(res.title);
                notify('ok', res.message);
            }).fail(function () { notify('warn', 'เชื่อมต่อไม่สำเร็จ'); });
        });
    });

    jQuery('#btn-auto-fill').on('click', function () {
        var \$btn = jQuery(this);

        Swal.fire({
            icon: 'question',
            title: 'จัดเวรอัตโนมัติ',
            html: '<div class="text-start small">' +
                '<ul class="ps-3 mb-2">' +
                '<li>เติมเฉพาะช่องที่ยังขาดตามอัตรากำลัง — เวรที่จัดไว้แล้วจะไม่ถูกแตะ</li>' +
                '<li>เลี่ยงวันลา วันไปราชการ และวันที่อนุมัติให้หยุด</li>' +
                '<li>กระจายภาระงานให้คนที่ได้เวรน้อยก่อน · กดซ้ำได้ ผลจะต่างจากเดิม</li>' +
                '</ul>' +
                '<div class="text-body-secondary">เมื่อคนไม่พอจนต้องเลือก จะให้ระบบทำอย่างไร</div>' +
                '</div>',
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: 'เติมให้ครบ (ผ่อนกฎได้)',
            denyButtonText: 'เฉพาะที่ไม่ผิดกฎ',
            cancelButtonText: 'ยกเลิก',
        }).then(function (r) {
            if (r.isConfirmed) { runAutoFill(\$btn, '1'); }
            else if (r.isDenied) { runAutoFill(\$btn, '0'); }
        });
    });

    function runAutoFill(\$btn, relax) {
        \$btn.prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm"></span> กำลังจัด...');

        jQuery.post('{$autoFillUrl}', { relax: relax }, function (res) {
            \$btn.prop('disabled', false).html('<i class="bi bi-magic"></i> จัดเวรอัตโนมัติ');
            if (res.status !== 'success') {
                notify('warn', res.message);
                return;
            }

            var msg = 'เติมเวรให้ ' + res.placed + ' ช่อง';
            if (res.relaxed > 0) { msg += ' (ผ่อนกฎ ' + res.relaxed + ' ช่อง)'; }

            var detail = '';
            if (res.shortageTotal > 0) {
                detail += '<div class="fw-semibold text-danger-emphasis mt-2">ยังขาดคน ' +
                    res.shortageTotal + ' จุด</div><ul class="small mb-0 ps-3">';
                res.shortages.slice(0, 8).forEach(function (s) {
                    detail += '<li>วันที่ ' + s.day + ' · ' + s.shift + ' ' + s.have + '/' + s.need + '</li>';
                });
                if (res.shortageTotal > 8) { detail += '<li>… อีก ' + (res.shortageTotal - 8) + ' จุด</li>'; }
                detail += '</ul>';
            }
            if (res.warningTotal > 0) {
                detail += '<div class="fw-semibold text-warning-emphasis mt-2">คำเตือนจากกฎ ' +
                    res.warningTotal + ' รายการ</div><ul class="small mb-0 ps-3">';
                res.warnings.slice(0, 8).forEach(function (w) { detail += '<li>' + w + '</li>'; });
                if (res.warningTotal > 8) { detail += '<li>… อีก ' + (res.warningTotal - 8) + ' รายการ</li>'; }
                detail += '</ul>';
            }

            if (detail && typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: res.shortageTotal > 0 ? 'warning' : 'success',
                    title: msg,
                    html: '<div class="text-start">' + detail +
                        '<div class="small text-body-secondary mt-2">ตรวจตารางก่อนส่งตรวจสอบเสมอ</div></div>',
                    confirmButtonText: 'ดูตาราง',
                }).then(function () { window.location.reload(); });
            } else {
                notify('ok', msg);
                window.location.reload();
            }
        }).fail(function () {
            \$btn.prop('disabled', false).html('<i class="bi bi-magic"></i> จัดเวรอัตโนมัติ');
            notify('warn', 'เชื่อมต่อไม่สำเร็จ');
        });
    }

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

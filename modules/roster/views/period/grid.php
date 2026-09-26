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
        <?php if ($canEdit): ?>
            <?= Html::a('<i class="bi bi-person-plus"></i> เพิ่มบุคลากรจากหน่วยงานอื่น', ['add-employee', 'id' => $period->id], [
                'class' => 'btn btn-sm btn-primary open-modal ms-2',
                'data' => ['size' => 'modal-lg'],
            ]) ?>
        <?php endif; ?>
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
            <?php if ($canEdit): ?>
                <?php
                // วิธีลงเวร — แต่ละหน่วยจัดเวรต่างกัน จึงให้เลือกเอง ระบบจำไว้แยกตามหน่วยงาน
                //   ปากกา     เลือกเวรแล้วคลิกหลายช่อง เหมาะกับลงเวรเดียวกันจำนวนมาก
                //   พิมพ์รหัส  เลื่อนช่องด้วยลูกศรแล้วพิมพ์รหัส แบบ Excel เหมาะกับคนเยอะ/เวรหลากหลาย
                //   กล่องเลือก คลิกช่องแล้วเลือกจากรายการที่ค้นหาได้ ไม่ต้องจำรหัส
                //   เลือกช่วง  ลากเลือกหลายช่องแล้วใส่เวร/เติมรูปแบบวนซ้ำ เหมาะกับเวรหมุนเวียน
                $modes = [
                    'pen' => ['bi-brush', 'ปากกา'],
                    'type' => ['bi-keyboard', 'พิมพ์รหัส'],
                    'picker' => ['bi-ui-radios-grid', 'กล่องเลือก'],
                    'range' => ['bi-bounding-box', 'เลือกช่วง'],
                ];
                ?>
                <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                    <span class="text-body-secondary small">วิธีลงเวร</span>
                    <div class="btn-group btn-group-sm roster-mode" role="group" aria-label="วิธีลงเวร">
                        <?php foreach ($modes as $key => [$icon, $label]): ?>
                            <button type="button" class="btn btn-outline-primary" data-mode="<?= $key ?>">
                                <i class="bi <?= $icon ?>"></i> <?= $label ?>
                            </button>
                        <?php endforeach; ?>
                    </div>
                    <span class="type-buffer d-none" id="type-buffer" aria-live="polite"></span>
                </div>
            <?php endif; ?>
            <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                <span class="text-body-secondary small" id="pen-hint">เลือกเวรแล้วคลิกช่อง — กำลังใส่</span>
                <span class="d-inline-flex align-items-center gap-2" id="pen-current-wrap">
                    <span class="pen-current-chip roster-chip" id="pen-current-chip">—</span>
                    <strong class="text-body small" id="pen-current">—</strong>
                </span>

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
                                </button>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button type="button" class="dropdown-item d-flex align-items-center gap-2 pen-option"
                                    data-pen="erase">
                                <span class="roster-chip bg-body-tertiary text-body border"><i class="bi bi-eraser"></i></span>
                                <span class="flex-grow-1">ลบเวรออกจากช่อง</span>
                                <kbd class="pen-kbd">Del</kbd>
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
                <?= Html::a('<i class="bi bi-person-plus"></i> เพิ่มบุคลากรต่างหน่วย', ['add-employee', 'id' => $period->id], [
                    'class' => 'btn btn-sm btn-outline-primary open-modal',
                    'data' => ['size' => 'modal-lg'],
                ]) ?>
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

            <?php
            // ดึงกลับมาแก้ — เงื่อนไขต้องตรงกับ transitionDenialReason() ฝั่งเซิร์ฟเวอร์เป๊ะ
            // เดิมแสดงเฉพาะผู้ตรวจ/ผอ. ทั้งที่เซิร์ฟเวอร์ยอมให้หัวหน้าหน่วยดึงของตัวเองกลับได้
            // หัวหน้าหน่วยที่ส่งผิดจึงแก้เองไม่ได้ ต้องรอคนอื่นกดให้
            $pullBack = null;
            if ($period->status === Period::STATUS_SUBMITTED) {
                if ($canReview || $canApprove) {
                    $pullBack = 'ส่งกลับแก้';
                } elseif ($canManage) {
                    // เจ้าของหน่วยดึงของตัวเองกลับ ไม่ใช่การ "ส่งกลับ" ให้ใคร
                    $pullBack = 'ดึงกลับมาแก้';
                }
            } elseif ($period->status === Period::STATUS_REVIEWED && ($canReview || $canApprove)) {
                $pullBack = 'ส่งกลับแก้';
            }
            ?>
            <?php if ($pullBack !== null): ?>
                <button type="button" class="btn btn-sm btn-outline-secondary"
                        data-transition="<?= Period::STATUS_DRAFT ?>"
                        data-confirm="<?= $pullBack === 'ดึงกลับมาแก้'
                            ? 'ดึงตารางเวรนี้กลับมาแก้เอง? หัวหน้ากลุ่มงานจะยังไม่ได้ตรวจ และต้องส่งใหม่อีกครั้ง'
                            : 'ส่งตารางเวรนี้กลับให้หัวหน้าหน่วยแก้ใหม่?' ?>">
                    <i class="bi bi-arrow-counterclockwise"></i> <?= $pullBack ?>
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
            <?php // ซ่อนยอดกำลังคนไว้ก่อน — กินพื้นที่มากเมื่อหน่วยมีหลายเวร จุดแดงที่หัววันยังเตือนวันที่คนขาดให้ ?>
            <table class="table table-bordered table-sm align-middle mb-0 roster-grid counts-hidden" id="roster-grid-table">
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
                            <th class="text-center p-1 roster-day-head <?= $headClass ?>" style="min-width:42px"
                                data-day="<?= $d ?>"
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
                        <?php $tot = $empTotals[$empId] ?? ['work' => 0, 'off' => 0, 'ot' => 0, 'pay' => 0.0]; ?>
                        <tr class="roster-row" data-position="<?= (int) ($emp['employee_position_id'] ?? 0) ?>">
                            <td class="roster-sticky-col bg-body">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-truncate" style="max-width:150px">
                                        <?= Html::encode(trim(($emp['prefix'] ?? '') . $emp['fname'] . ' ' . $emp['lname'])) ?>
                                    </span>
                                    <?php if (!empty($emp['is_external'])): ?>
                                        <span class="badge bg-warning-subtle text-warning-emphasis" title="บุคลากรจากหน่วยงานอื่น">ต่างหน่วย</span>
                                        <?php if ($canEdit): ?>
                                            <button type="button" class="btn btn-sm btn-link text-danger p-0 remove-external-employee"
                                                    data-emp="<?= $empId ?>"
                                                    aria-label="นำ <?= Html::encode(trim(($emp['prefix'] ?? '') . $emp['fname'] . ' ' . $emp['lname'])) ?> ออกจากแผ่นเวร"
                                                    title="นำออกจากแผ่นเวร">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php if (($emp['work_shift'] ?? '') === 'shift'): ?>
                                        <span class="badge bg-info-subtle text-info-emphasis" title="ขึ้นเวร 8">8</span>
                                    <?php endif; ?>
                                </div>
                                <div class="small text-body-secondary text-truncate" style="max-width:190px">
                                    <?= Html::encode($emp['position_name'] ?: 'ไม่ระบุตำแหน่ง') ?>
                                    <?php if (!empty($emp['is_external']) && !empty($emp['department_name'])): ?>
                                        · <?= Html::encode($emp['department_name']) ?>
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
                                    data-day="<?= $d ?>" data-shift="<?= (int) $unitShift->id ?>" data-need="<?= $need ?>"
                                    data-have="<?= $have ?>">
                                    <?= $need > 0 ? $have . '/' . $need : $have ?>
                                </td>
                            <?php endfor; ?>
                            <td colspan="4" class="small text-body-secondary">
                                <?= Html::encode($unitShift->positionName() ?: 'ทุกวิชาชีพ') ?>
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
        <?php if ($canEdit): ?>
            <span id="mode-hint"></span>
        <?php endif; ?>
        <?php if ($unitShiftList): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-toggle-counts"
                    aria-controls="roster-grid-table">
                <i class="bi bi-bar-chart-steps"></i> <span>แสดงยอดกำลังคน</span>
            </button>
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

.roster-sticky-col { position: sticky; left: 0; z-index: 2; }
.roster-grid thead th.roster-sticky-col { z-index: 4; }

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

/* ── วิธีลงเวร ─────────────────────────────────────────────────────────── */
.type-buffer {
    min-width: 2.5rem; padding: .15rem .5rem; border-radius: var(--bs-border-radius);
    background: var(--bs-primary-bg-subtle); color: var(--bs-primary-text-emphasis);
    font-weight: 700; text-align: center;
}
.type-buffer.is-invalid { background: var(--bs-danger-bg-subtle); color: var(--bs-danger-text-emphasis); }
.roster-cell.is-cursor { outline: 3px solid var(--bs-primary) !important; outline-offset: -3px; }
.roster-cell.is-selected { box-shadow: inset 0 0 0 999px rgba(var(--bs-primary-rgb), .16); }
.roster-grid.mode-range .roster-cell { user-select: none; cursor: cell; }
.roster-grid.mode-type .roster-cell { cursor: text; }

/* แป้นรหัสเวรแบบเครื่องคิดเลข — ปุ่มใหญ่ ตำแหน่งคงที่ มีแค่รหัส */
.cell-picker {
    position: absolute; z-index: 1060; width: max-content; max-width: calc(100vw - 16px);
    background: var(--bs-body-bg); border: 1px solid var(--bs-border-color);
    border-radius: var(--bs-border-radius-lg); box-shadow: 0 .5rem 1.5rem rgba(0, 0, 0, .18);
    padding: .5rem;
}
.kp-head { font-size: .78rem; color: var(--bs-secondary-color); margin-bottom: .4rem; max-width: 300px; }
.kp-grid { display: grid; gap: .35rem; }
.kp-key {
    min-width: 52px; height: 44px; padding: 0 .4rem;
    border: 2px solid transparent; border-radius: var(--bs-border-radius);
    font-size: 1.05rem; font-weight: 700; line-height: 1;
    transition: transform .1s ease-out, box-shadow .1s ease-out;
}
.kp-key:hover, .kp-key:focus-visible { box-shadow: 0 0 0 .2rem var(--bs-primary-border-subtle); transform: translateY(-1px); }
.kp-key:active { transform: translateY(1px); }
.kp-key.is-on { border-color: var(--bs-emphasis-color); }
.kp-key.is-other { opacity: .4; }
.kp-clear { background: var(--bs-tertiary-bg); color: var(--bs-danger-text-emphasis); border-color: var(--bs-border-color); }
.kp-pattern { margin-top: .6rem; padding-top: .5rem; border-top: 1px solid var(--bs-border-color); max-width: 300px; }
.kp-pattern label { display: block; margin-bottom: .25rem; }
/* ยอดกำลังคน — ซ่อนได้ ตอนซ่อนใช้จุดแดงที่หัววันแทน */
.roster-grid.counts-hidden tfoot { display: none; }
.roster-grid.counts-hidden .roster-day-head.has-shortage::after {
    content: ''; position: absolute; top: 3px; right: 3px;
    width: 7px; height: 7px; border-radius: 50%; background: var(--bs-danger);
}
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
$removeEmployeeUrl = Url::to(['remove-employee']);

$shiftMeta = [];
foreach ($unitShiftList as $unitShift) {
    $shiftMeta[(int) $unitShift->id] = [
        's' => $unitShift->displayShort(),
        'c' => $unitShift->cellClass(),
        'n' => $unitShift->displayName(),
        'p' => (int) $unitShift->position_id, // 0 = ไม่จำกัดวิชาชีพ
        'o' => (int) $unitShift->sort_order,  // ลำดับการเรียงชิปในช่อง
        't' => $unitShift->timeRangeLabel(),
    ];
}
$shiftMetaJson = json_encode($shiftMeta, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
$assignBatchUrl = Url::to(['assign-batch']);
$unitId = (int) $period->unit_id;
$daysInMonth = (int) $days;

$js = <<<JS
(function () {
    var canEdit = {$canEditJs};
    var canReplace = {$canReplaceJs};
    var periodId = {$periodId};
    var shiftMeta = {$shiftMetaJson};
    var unitId = {$unitId};
    var daysInMonth = {$daysInMonth};

    // localStorage ใช้จำค่าที่ผู้ใช้เลือกเท่านั้น — เปิดโหมดส่วนตัว/ถูกบล็อกก็ต้องทำงานต่อได้
    function store(key, value) {
        try {
            if (value === undefined) { return window.localStorage.getItem(key); }
            window.localStorage.setItem(key, value);
        } catch (e) { /* ไม่มีที่เก็บก็ใช้ค่าเริ่มต้น */ }
        return null;
    }

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
        // โหมดพิมพ์รหัส/กล่องเลือกไม่ได้ถือปากกา การหรี่แถวจะทำให้เข้าใจผิดว่าลงไม่ได้
        var wanted = meta && mode === 'pen' ? meta.p : 0;
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

    // ── พิมพ์รหัสเวร ─────────────────────────────────────────────────────
    // จับคู่ตาม "ตัวย่อเวร" ไม่ใช่ลำดับปุ่ม — รหัสสองหลัก (10, 20) หรือตัวอักษร (F, ช) ก็พิมพ์ได้
    // ถ้ารหัสที่พิมพ์เป็นส่วนต้นของรหัสอื่น (1 กับ 10) รอสั้นๆ ก่อนตัดสิน หรือกด Enter เพื่อยืนยัน
    var codeIndex = {};
    Object.keys(shiftMeta).forEach(function (id) {
        var code = String(shiftMeta[id].s || '').trim().toLowerCase();
        if (code && !codeIndex[code]) { codeIndex[code] = id; }
    });
    var codes = Object.keys(codeIndex);
    var typeBuf = '';
    var typeTimer = null;
    var typeResolve = null;

    function showBuffer(text, invalid) {
        var \$b = jQuery('#type-buffer');
        \$b.text(text).toggleClass('d-none', !text).toggleClass('is-invalid', !!invalid);
    }
    function resetBuffer() {
        clearTimeout(typeTimer);
        typeBuf = '';
        typeResolve = null;
        showBuffer('');
    }
    function flushBuffer() {
        var id = codeIndex[typeBuf.toLowerCase()];
        var cb = typeResolve;
        resetBuffer();
        if (id && cb) { cb(id); }
        return !!id;
    }
    // คืน true ถ้าแป้นนี้ถูกใช้เป็นส่วนหนึ่งของรหัส
    function feedKey(ch, onResolve) {
        var next = (typeBuf + ch).toLowerCase();
        var longer = codes.some(function (c) { return c.length > next.length && c.indexOf(next) === 0; });
        var exact = codeIndex[next];
        if (!exact && !longer) {
            if (!typeBuf) { return false; }
            // พิมพ์ต่อแล้วไม่ตรงรหัสไหน — แจ้งให้เห็น ไม่ลงเวรมั่ว
            clearTimeout(typeTimer);
            typeBuf = '';
            typeResolve = null;
            showBuffer(next.toUpperCase() + ' ?', true);
            typeTimer = setTimeout(function () { showBuffer(''); }, 900);
            return true;
        }
        clearTimeout(typeTimer);
        typeBuf = next;
        typeResolve = onResolve;
        showBuffer(typeBuf.toUpperCase());
        if (exact && !longer) { flushBuffer(); return true; }
        typeTimer = setTimeout(function () {
            if (!flushBuffer()) { showBuffer(''); }
        }, 700);
        return true;
    }

    // ── วิธีลงเวร (จำแยกตามหน่วยงาน) ──────────────────────────────────────
    var modeHints = {
        pen: {
            bar: 'เลือกเวรแล้วคลิกช่อง (คลิกซ้ำ = เอาออก) — กำลังใส่',
            foot: 'พิมพ์รหัสเวรเพื่อเปลี่ยนปากกา · <kbd>Del</kbd> ยางลบ'
        },
        type: {
            bar: 'คลิกช่องแล้วพิมพ์รหัสเวร ระบบลงให้แล้วเลื่อนไปวันถัดไป',
            foot: '<kbd>←</kbd><kbd>→</kbd><kbd>↑</kbd><kbd>↓</kbd> เลื่อน · พิมพ์รหัส = ลงเวร (ทับของเดิม) · ' +
                '<kbd>+</kbd> ตามด้วยรหัส = เพิ่มเวรที่สอง · <kbd>Del</kbd> ล้างช่อง · <kbd>Enter</kbd> แถวถัดไป'
        },
        picker: {
            bar: 'คลิกช่อง แล้วกดรหัสเวรบนแป้นที่เด้งขึ้น',
            foot: 'พิมพ์รหัสบนคีย์บอร์ดได้เลยขณะแป้นเปิด · <kbd>Shift</kbd>+คลิกรหัส = เพิ่มเวรที่สอง · <kbd>Esc</kbd> ปิด'
        },
        range: {
            bar: '① กดค้างแล้วลากคลุมช่อง ② ปล่อยเมาส์ ③ กดรหัสเวรบนแป้นที่เด้งขึ้น — ทุกช่องที่คลุมจะได้เวรนั้น',
            foot: '<kbd>Shift</kbd>+คลิก = ขยายช่วงจากช่องแรก · พิมพ์รหัสบนคีย์บอร์ดได้ · <kbd>Esc</kbd> ยกเลิก'
        }
    };
    var modeKey = 'roster.mode.' + unitId;
    var mode = store(modeKey);
    if (!modeHints[mode]) { mode = 'pen'; }

    function setMode(value) {
        if (!modeHints[value]) { return; }
        mode = value;
        store(modeKey, value);
        jQuery('.roster-mode [data-mode]').each(function () {
            var on = jQuery(this).data('mode') === value;
            jQuery(this).toggleClass('active', on).attr('aria-pressed', on ? 'true' : 'false');
        });
        jQuery('#roster-grid-table')
            .removeClass('mode-pen mode-type mode-picker mode-range')
            .addClass('mode-' + value);
        jQuery('#pen-hint').text(modeHints[value].bar);
        jQuery('#pen-current-wrap').toggleClass('d-none', value !== 'pen');
        jQuery('#mode-hint').html(modeHints[value].foot);
        resetBuffer();
        setCursor(null);
        clearSelection();
        closePicker();
        applyPositionFilter();
    }

    jQuery('.roster-mode').on('click', '[data-mode]', function () {
        setMode(jQuery(this).data('mode'));
    });

    // ── เคอร์เซอร์ช่อง (โหมดพิมพ์รหัส) ────────────────────────────────────
    var \$cursor = null;
    var addNext = false; // กด + แล้ว รหัสถัดไปจะ "เพิ่ม" แทน "ทับ"
    var advanceAfterType = true; // ลงเวรแล้วเลื่อนไปวันถัดไป (ปิดชั่วคราวตอนกดลูกศร)

    function setCursor(\$cell) {
        if (\$cursor) { \$cursor.removeClass('is-cursor'); }
        \$cursor = \$cell && \$cell.length ? \$cell : null;
        addNext = false;
        if (!\$cursor) { return; }
        \$cursor.addClass('is-cursor');
        var el = \$cursor[0];
        if (el.scrollIntoView) { el.scrollIntoView({ block: 'nearest', inline: 'nearest' }); }
    }

    function cellAt(rowIdx, day) {
        var \$row = jQuery('.roster-row').eq(rowIdx);
        return rowIdx >= 0 && \$row.length ? \$row.find('.roster-cell[data-day="' + day + '"]') : jQuery();
    }

    function moveCursor(dRow, dDay, wrap) {
        if (!\$cursor) { return; }
        var rowIdx = jQuery('.roster-row').index(\$cursor.closest('.roster-row'));
        var day = parseInt(\$cursor.data('day'), 10) + dDay;
        var row = rowIdx + dRow;
        if (wrap && day > daysInMonth) { day = 1; row++; }
        if (wrap && day < 1) { day = daysInMonth; row--; }
        day = Math.max(1, Math.min(daysInMonth, day));
        var \$next = cellAt(row, day);
        if (\$next.length) { setCursor(\$next); }
    }

    // ── ลงเวรหนึ่งช่อง — ใช้ร่วมทุกโหมด ─────────────────────────────────────
    function assignCell(\$cell, shiftId, assignMode) {
        if (\$cell.hasClass('is-saving')) { return; }
        \$cell.addClass('is-saving');
        jQuery.post('{$assignUrl}', {
            period_id: periodId,
            emp_id: \$cell.data('emp'),
            day: \$cell.data('day'),
            unit_shift_id: shiftId || 0,
            mode: assignMode
        }, function (res) {
            \$cell.removeClass('is-saving');
            if (res.status !== 'success') {
                // ชนกับอีกแผ่น — ช่องตรงหน้าว่าง คำเตือนลอย ๆ จะงงมาก ต้องพาไปแผ่นนั้นได้เลย
                if (res.conflictUrl) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'เวรนี้ถูกจัดไว้ในอีกแผ่นแล้ว',
                        text: res.message,
                        showCancelButton: true,
                        confirmButtonText: 'ไปที่แผ่นนั้น',
                        cancelButtonText: 'ปิด',
                    }).then(function (r) {
                        if (r.isConfirmed) { window.location.href = res.conflictUrl; }
                    });
                    return;
                }
                notify('warn', res.message || 'บันทึกไม่สำเร็จ');
                return;
            }
            repaintCell(\$cell, res.items || []);
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
    }

    // ── แป้นรหัสเวร (แบบเครื่องคิดเลข) ─────────────────────────────────────
    // ผู้จัดเวรจำรหัสได้อยู่แล้ว จึงแสดงแค่ปุ่มรหัส ตำแหน่งคงที่ตามลำดับเวร (ไม่สลับตามที่ใช้ล่าสุด)
    // ให้กดตามความเคยชินได้เหมือนแป้นตัวเลข · ชื่อเต็มดูได้จาก tooltip
    var \$picker = null;
    var pickerKind = null; // 'cell' = ช่องเดียว · 'range' = หลายช่อง

    function closePicker() {
        if (\$picker) { \$picker.remove(); \$picker = null; }
        pickerKind = null;
        if (mode === 'picker') {
            jQuery('.roster-cell.is-cursor').removeClass('is-cursor');
            \$cursor = null;
        }
    }

    function esc(text) {
        return String(text == null ? '' : text).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    var orderedIds = Object.keys(shiftMeta).sort(function (a, b) {
        return (shiftMeta[a].o - shiftMeta[b].o) || (a - b);
    });

    // rowPos = วิชาชีพของแถว (0 = ไม่สนใจ) · inCell = เวรที่อยู่ในช่องแล้ว
    function keypadHtml(rowPos, inCell) {
        var cols = Math.min(5, Math.max(3, Math.ceil(Math.sqrt(orderedIds.length + 1))));
        var html = '<div class="kp-grid" style="grid-template-columns:repeat(' + cols + ',1fr)">';
        orderedIds.forEach(function (id) {
            var m = shiftMeta[id];
            var other = rowPos && m.p && m.p !== rowPos;
            html += '<button type="button" class="kp-key roster-chip ' + esc(m.c) +
                (other ? ' is-other' : '') + (inCell && inCell[id] ? ' is-on' : '') + '"' +
                ' data-shift="' + id + '" title="' + esc(m.n + (m.t ? ' ' + m.t : '')) + '">' +
                esc(m.s) + '</button>';
        });
        html += '<button type="button" class="kp-key kp-clear" data-shift="0" title="ล้างช่อง">' +
            '<i class="bi bi-eraser"></i></button>';
        return html + '</div>';
    }

    function placePanel(\$anchor) {
        var off = \$anchor.offset();
        var w = \$picker.outerWidth();
        var h = \$picker.outerHeight();
        var \$w = jQuery(window);
        var left = Math.min(off.left, \$w.scrollLeft() + \$w.width() - w - 8);
        var top = off.top + \$anchor.outerHeight() + 4;
        // ล้นจอด้านล่าง — เปิดขึ้นด้านบนของช่องแทน
        if (top + h > \$w.scrollTop() + \$w.height()) {
            top = Math.max(\$w.scrollTop() + 8, off.top - h - 4);
        }
        \$picker.css({ left: Math.max(8, left), top: top });
    }

    function openPicker(\$cell) {
        closePicker();
        \$cursor = \$cell.addClass('is-cursor');
        pickerKind = 'cell';
        var rowPos = parseInt(\$cell.closest('.roster-row').data('position'), 10) || 0;
        var inCell = {};
        \$cell.find('.roster-chip').each(function () { inCell[jQuery(this).data('shift')] = true; });
        var who = jQuery.trim(\$cell.closest('.roster-row').find('.roster-sticky-col .text-truncate').first().text());

        \$picker = jQuery(
            '<div class="cell-picker" role="dialog" aria-label="เลือกเวร">' +
            '<div class="kp-head text-truncate">' + esc(who) + ' · ' + esc(\$cell.data('day')) + '</div>' +
            keypadHtml(rowPos, inCell) +
            '</div>'
        ).appendTo('body');
        placePanel(\$cell);
    }

    // เลือกช่วงเสร็จ (ปล่อยเมาส์) — เปิดแป้นเดียวกันข้างช่องสุดท้าย กดรหัส = ใส่ทุกช่องที่เลือก
    function openRangePanel(\$anchor) {
        if (\$picker) { \$picker.remove(); }
        var n = jQuery('.roster-cell.is-selected').length;
        if (!n) { \$picker = null; return; }
        pickerKind = 'range';
        \$picker = jQuery(
            '<div class="cell-picker" role="dialog" aria-label="ใส่เวรหลายช่อง">' +
            '<div class="kp-head">เลือก ' + n + ' ช่อง — กดรหัสเพื่อใส่ทุกช่อง</div>' +
            keypadHtml(0, null) +
            '<div class="kp-pattern">' +
            '<label class="small text-body-secondary" for="kp-pattern-input">หรือใส่เป็นชุดวนซ้ำ (ทีละคน เริ่มจากวันแรกที่เลือก)</label>' +
            '<div class="input-group input-group-sm">' +
            '<input type="text" class="form-control" id="kp-pattern-input" placeholder="เช่น 1 1 4 F" autocomplete="off">' +
            '<button type="button" class="btn btn-outline-primary kp-pattern-apply">ใส่</button>' +
            '</div></div></div>'
        ).appendTo('body');
        placePanel(\$anchor);
    }

    jQuery('body').on('click', '.kp-key', function (e) {
        var id = parseInt(jQuery(this).data('shift'), 10) || 0;
        if (pickerKind === 'range') {
            if (!id && !window.confirm('ล้างเวรใน ' + jQuery('.roster-cell.is-selected').length + ' ช่องที่เลือก?')) { return; }
            fillSelection(function () { return id; });
            return;
        }
        var \$cell = \$cursor;
        closePicker();
        if (!\$cell) { return; }
        if (!id) { assignCell(\$cell, 0, 'clear'); return; }
        assignCell(\$cell, id, e.shiftKey ? 'add' : 'replace');
    });
    jQuery('body').on('click', '.kp-pattern-apply', function () { applyPattern(); });
    jQuery('body').on('keydown', '#kp-pattern-input', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); applyPattern(); }
        if (e.key === 'Escape') { e.preventDefault(); closePicker(); clearSelection(); }
    });
    jQuery(document).on('mousedown', function (e) {
        if (\$picker && !jQuery(e.target).closest('.cell-picker, .roster-cell').length) {
            closePicker();
            if (mode === 'range') { clearSelection(); }
        }
    });
    jQuery('.roster-scroll').on('scroll', function () {
        if (\$picker && pickerKind === 'cell') { closePicker(); }
        if (\$picker && pickerKind === 'range') { closePicker(); clearSelection(); }
    });

    // ── เลือกช่วง (โหมดเลือกช่วง) ──────────────────────────────────────────
    var anchor = null;   // {row, day}
    var dragging = false;
    var \$lastHover = null;

    function cellPos(\$cell) {
        return {
            row: jQuery('.roster-row').index(\$cell.closest('.roster-row')),
            day: parseInt(\$cell.data('day'), 10)
        };
    }
    function selectRect(a, b) {
        jQuery('.roster-cell.is-selected').removeClass('is-selected');
        var r1 = Math.min(a.row, b.row), r2 = Math.max(a.row, b.row);
        var d1 = Math.min(a.day, b.day), d2 = Math.max(a.day, b.day);
        jQuery('.roster-row').slice(r1, r2 + 1).each(function () {
            jQuery(this).find('.roster-cell').each(function () {
                var d = parseInt(jQuery(this).data('day'), 10);
                if (d >= d1 && d <= d2) { jQuery(this).addClass('is-selected'); }
            });
        });
    }
    function clearSelection() {
        anchor = null;
        dragging = false;
        jQuery('.roster-cell.is-selected').removeClass('is-selected');
    }

    jQuery('body').on('mousedown', '.roster-cell', function (e) {
        if (!canEdit || mode !== 'range' || e.button !== 0) { return; }
        e.preventDefault();
        var \$c = jQuery(this);
        var pos = cellPos(\$c);
        if (\$picker) { \$picker.remove(); \$picker = null; pickerKind = null; }
        if (!(e.shiftKey && anchor)) { anchor = pos; }
        dragging = true;
        \$lastHover = \$c;
        selectRect(anchor, pos);
    });
    jQuery('body').on('mouseenter', '.roster-cell', function () {
        if (dragging && mode === 'range') {
            \$lastHover = jQuery(this);
            selectRect(anchor, cellPos(\$lastHover));
        }
    });
    jQuery(document).on('mouseup', function () {
        if (!dragging) { return; }
        dragging = false;
        if (mode === 'range' && \$lastHover) { openRangePanel(\$lastHover); }
    });

    function runBatch(cells) {
        if (!cells.length) { return; }
        var \$targets = jQuery('.roster-cell.is-selected').addClass('is-saving');
        closePicker();
        jQuery.post('{$assignBatchUrl}', { period_id: periodId, cells: cells }, function (res) {
            \$targets.removeClass('is-saving');
            if (res.status !== 'success') { notify('warn', res.message || 'บันทึกไม่สำเร็จ'); return; }
            (res.cells || []).forEach(function (c) {
                if (c.status !== 'success') { return; }
                var \$cell = jQuery('.roster-cell[data-emp="' + c.emp + '"][data-day="' + c.day + '"]');
                repaintCell(\$cell, c.items || []);
            });
            Object.keys(res.counts || {}).forEach(function (day) { updateCounts(day, res.counts[day]); });
            Object.keys(res.empTotals || {}).forEach(function (emp) { updateEmpTotal(emp, res.empTotals[emp]); });
            if (res.summary) { jQuery('#summary-assigned').text(res.summary.assigned.toLocaleString()); }

            var detail = '';
            if (res.errorTotal > 0) {
                detail += '<div class="fw-semibold text-danger-emphasis">ลงไม่ได้ ' + res.errorTotal + ' ช่อง</div>' +
                    '<ul class="small ps-3">' + res.errors.map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>';
            }
            if (res.warningTotal > 0) {
                detail += '<div class="fw-semibold text-warning-emphasis">คำเตือนจากกฎ ' + res.warningTotal + ' รายการ</div>' +
                    '<ul class="small ps-3 mb-0">' + res.warnings.map(function (m) { return '<li>' + esc(m) + '</li>'; }).join('') + '</ul>';
            }
            if (detail && typeof Swal !== 'undefined') {
                Swal.fire({ icon: res.errorTotal ? 'warning' : 'info', title: 'บันทึกแล้ว', html: '<div class="text-start">' + detail + '</div>' });
            } else {
                notify('ok', 'บันทึก ' + cells.length + ' ช่องแล้ว');
            }
            clearSelection();
        }).fail(function () {
            \$targets.removeClass('is-saving');
            notify('warn', 'เชื่อมต่อไม่สำเร็จ');
        });
    }

    // ใส่ทุกช่องที่เลือก — shiftFor(ลำดับช่องในแถวของคนนั้น) คืน id เวร (0 = ล้างช่อง)
    function fillSelection(shiftFor) {
        var cells = [];
        jQuery('.roster-row').each(function () {
            jQuery(this).find('.roster-cell.is-selected').toArray()
                .map(function (el) { return { emp: jQuery(el).data('emp'), day: parseInt(jQuery(el).data('day'), 10) }; })
                .sort(function (a, b) { return a.day - b.day; })
                .forEach(function (c, i) { cells.push({ emp: c.emp, day: c.day, shift: shiftFor(i) }); });
        });
        runBatch(cells);
    }

    // ชุดวนซ้ำ เช่น "1 1 4 F" — แต่ละคนเริ่มชุดใหม่จากวันแรกที่เลือก · "-" = เว้นว่าง (ล้างช่อง)
    function applyPattern() {
        var tokens = String(jQuery('#kp-pattern-input').val()).split(/[\s,]+/).filter(Boolean);
        if (!tokens.length) { notify('warn', 'กรอกชุดรหัส เช่น 1 1 4 F'); return; }
        var pattern = [];
        var bad = [];
        tokens.forEach(function (t) {
            if (t === '-') { pattern.push(0); return; }
            var id = codeIndex[t.toLowerCase()];
            if (id) { pattern.push(parseInt(id, 10)); } else { bad.push(t); }
        });
        if (bad.length) { notify('warn', 'ไม่พบรหัสเวร: ' + bad.join(', ')); return; }
        fillSelection(function (i) { return pattern[i % pattern.length]; });
    }

    // ── แป้นพิมพ์ ─────────────────────────────────────────────────────────
    jQuery(document).on('keydown', function (e) {
        if (!canEdit || e.ctrlKey || e.altKey || e.metaKey) { return; }
        if (jQuery(e.target).is('input, textarea, select, [contenteditable]')) { return; }
        if (jQuery('.modal.show, .swal2-container').length) { return; }

        if (e.key === 'Escape') {
            resetBuffer();
            clearSelection();
            closePicker();
            setCursor(null);
            return;
        }

        // แป้นรหัสเปิดอยู่ — พิมพ์รหัสบนคีย์บอร์ดแทนการคลิกได้
        if (\$picker) {
            if (e.key === 'Delete' || e.key === 'Backspace') {
                e.preventDefault();
                \$picker.find('.kp-clear').trigger('click');
                return;
            }
            if (e.key === 'Enter' && typeBuf) { e.preventDefault(); flushBuffer(); return; }
            if (e.key.length === 1 && e.key !== ' ') {
                var usedKp = feedKey(e.key, function (id) {
                    if (\$picker) { \$picker.find('.kp-key[data-shift="' + id + '"]').first().trigger('click'); }
                });
                if (usedKp) { e.preventDefault(); }
            }
            return;
        }

        if (mode === 'type' && \$cursor) {
            var arrows ={ ArrowLeft: [0, -1], ArrowRight: [0, 1], ArrowUp: [-1, 0], ArrowDown: [1, 0] };
            if (arrows[e.key]) {
                e.preventDefault();
                // รหัสที่พิมพ์ค้างอยู่ (เช่น 1 ที่รอดูว่าจะเป็น 10 ไหม) — ยืนยันลงช่องปัจจุบันก่อนเลื่อน
                if (typeBuf) {
                    advanceAfterType = false;
                    flushBuffer();
                    advanceAfterType = true;
                }
                moveCursor(arrows[e.key][0], arrows[e.key][1], false);
                return;
            }
            if (e.key === 'Enter') {
                e.preventDefault();
                // มีรหัสค้างอยู่ = ยืนยันรหัสนั้น ไม่งั้นลงแถวถัดไป
                if (!typeBuf) { moveCursor(1, 0, false); } else { flushBuffer(); }
                return;
            }
            if (e.key === 'Delete' || e.key === 'Backspace') {
                e.preventDefault();
                resetBuffer();
                if (\$cursor.find('.roster-chip').length) { assignCell(\$cursor, 0, 'clear'); }
                return;
            }
            if (e.key === '+') {
                e.preventDefault();
                addNext = true;
                showBuffer('+');
                return;
            }
            if (e.key.length === 1 && e.key !== ' ') {
                var used = feedKey(e.key, function (id) {
                    var \$target = \$cursor;
                    var m = addNext ? 'add' : 'replace';
                    addNext = false;
                    assignCell(\$target, id, m);
                    if (advanceAfterType) { moveCursor(0, 1, true); }
                });
                if (used) { e.preventDefault(); }
            }
            return;
        }

        if (mode === 'pen') {
            if (e.key === 'Delete' || e.key === 'Backspace') { e.preventDefault(); setPen('erase'); return; }
            if (e.key.length === 1 && e.key !== ' ') {
                var usedPen = feedKey(e.key, function (id) { setPen(id); });
                // "0" เป็นยางลบถ้าไม่มีรหัสเวรใดขึ้นต้นด้วย 0 (คงพฤติกรรมเดิม)
                if (!usedPen && e.key === '0') { setPen('erase'); }
            }
        }
    });

    // ── ยอดกำลังคน: ซ่อน/แสดง + จุดแดงที่หัววันที่คนขาด ─────────────────────
    var countsKey = 'roster.showCounts';
    function refreshShortage() {
        var short = {};
        jQuery('.roster-count').each(function () {
            var need = parseInt(jQuery(this).attr('data-need'), 10) || 0;
            var have = parseInt(jQuery(this).attr('data-have'), 10) || 0;
            if (need > 0 && have < need) { short[jQuery(this).data('day')] = true; }
        });
        jQuery('.roster-day-head').each(function () {
            var d = jQuery(this).data('day');
            jQuery(this).toggleClass('has-shortage', !!short[d])
                .attr('aria-label', short[d] ? 'วันที่ ' + d + ' คนยังไม่ครบ' : null);
        });
    }
    function setShowCounts(show) {
        jQuery('#roster-grid-table').toggleClass('counts-hidden', !show);
        jQuery('#btn-toggle-counts').attr('aria-expanded', show ? 'true' : 'false')
            .find('span').text(show ? 'ซ่อนยอดกำลังคน' : 'แสดงยอดกำลังคน');
        store(countsKey, show ? '1' : '0');
    }
    jQuery('#btn-toggle-counts').on('click', function () {
        setShowCounts(jQuery('#roster-grid-table').hasClass('counts-hidden'));
    });
    setShowCounts(store(countsKey) === '1');
    refreshShortage();

    setPen(pen);
    if (canEdit) { setMode(mode); }


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
            \$td.text(need > 0 ? have + '/' + need : have).attr('data-have', have);
            \$td.removeClass('text-danger-emphasis fw-bold text-warning-emphasis fw-semibold text-success-emphasis text-body-secondary');
            if (need <= 0) { \$td.addClass('text-body-secondary'); }
            else if (have < need) { \$td.addClass('text-danger-emphasis fw-bold'); }
            else if (have > need) { \$td.addClass('text-warning-emphasis fw-semibold'); }
            else { \$td.addClass('text-success-emphasis'); }
        });
        refreshShortage();
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

        if (mode === 'type') { resetBuffer(); setCursor(\$cell); return; }
        if (mode === 'picker') { openPicker(\$cell); return; }
        if (mode === 'range') { return; } // เลือกด้วย mousedown/ลาก

        // โหมดปากกา — คลิกซ้ำ = เอาออก ใส่ได้หลายเวรต่อช่อง (ช/บ)
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
        assignCell(\$cell, shiftId, 'toggle');
    });

    // โหมดพิมพ์รหัส: คลิกปุ่มเวรบนแถบ = ลงเวรนั้นในช่องที่เลือกไว้ แล้วเลื่อนไปวันถัดไป
    jQuery('body').on('click', '.pen-chip', function () {
        if (mode !== 'type' || !\$cursor) { return; }
        var p = jQuery(this).data('pen');
        if (p === 'erase') { assignCell(\$cursor, 0, 'clear'); return; }
        assignCell(\$cursor, p, 'replace');
        moveCursor(0, 1, true);
    });

    jQuery('body').on('click', '.remove-external-employee', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var \$button = jQuery(this);
        \$button.prop('disabled', true);
        jQuery.post('{$removeEmployeeUrl}', {
            period_id: periodId,
            emp_id: \$button.data('emp')
        }).done(function (res) {
            if (res.status === 'success') {
                if (typeof success === 'function') { success(res.message); }
                \$button.closest('tr').remove();
            } else {
                notify('warn', res.message || 'นำบุคลากรออกไม่สำเร็จ');
            }
        }).fail(function () {
            notify('warn', 'เชื่อมต่อระบบไม่สำเร็จ กรุณาลองใหม่');
        }).always(function () { \$button.prop('disabled', false); });
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
        // ปุ่มเดียวกันมีความหมายต่างกันตามคนกด (ส่งกลับ vs ดึงกลับ) จึงให้ปุ่มพกข้อความมาเองได้
        if (!window.confirm(jQuery(this).data('confirm') || labels[to] || 'ยืนยัน?')) { return; }
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

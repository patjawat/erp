<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\modules\roster\models\Period $period */
/** @var array $employees */
/** @var app\modules\roster\models\ShiftType[] $types */
/** @var array $unitShifts */
/** @var array $grid */
/** @var array $counts */
/** @var array $holidays */
/** @var array $weekends */
/** @var array $leaves */

$this->title = 'ตารางเวร ' . $period->unitName() . ' ' . $period->monthLabel();

$days = $period->daysInMonth();
$dowNames = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];

$unitShiftList = array_values($unitShifts);
?>
<div class="roster-print">
    <div class="text-center mb-2">
        <h5 class="mb-1 fw-bold"><?= Html::encode($period->unitName()) ?></h5>
        <div><?= Html::encode($period->title) ?> ประจำเดือน<?= Html::encode($period->monthLabel()) ?></div>
    </div>

    <div class="d-flex flex-wrap gap-3 mb-2 small">
        <?php foreach ($unitShiftList as $unitShift): ?>
            <span>
                <span class="print-chip <?= $unitShift->cellClass() ?>"><?= Html::encode($unitShift->displayShort()) ?></span>
                <?= Html::encode($unitShift->displayName()) ?>
                <?= Html::encode($unitShift->timeRangeLabel()) ?>
            </span>
        <?php endforeach; ?>
    </div>

    <table class="table table-bordered table-sm align-middle print-grid">
        <thead>
            <tr>
                <th style="width:26px">#</th>
                <th style="min-width:140px">ชื่อ-นามสกุล</th>
                <th style="min-width:90px">ตำแหน่ง</th>
                <?php for ($d = 1; $d <= $days; $d++): ?>
                    <?php
                    $ts = strtotime($period->dateOfDay($d));
                    $cls = isset($holidays[$d]) ? 'bg-danger-subtle' : (!empty($weekends[$d]) ? 'bg-secondary-subtle' : '');
                    ?>
                    <th class="text-center p-0 <?= $cls ?>">
                        <div><?= $d ?></div>
                        <div class="fw-normal"><?= $dowNames[(int) date('w', $ts)] ?></div>
                    </th>
                <?php endfor; ?>
                <th class="text-center" style="width:32px">รวม</th>
                <th class="text-center" style="width:32px">หยุด</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $index => $emp): ?>
                <?php
                $empId = (int) $emp['id'];
                $total = 0;
                $offDays = 0;
                foreach ($grid[$empId] ?? [] as $items) {
                    foreach ($items as $it) {
                        if ($it->isOff()) {
                            $offDays++;
                        } else {
                            $total++;
                        }
                    }
                }
                ?>
                <tr>
                    <td class="text-center"><?= $index + 1 ?></td>
                    <td><?= Html::encode(trim(($emp['prefix'] ?? '') . $emp['fname'] . ' ' . $emp['lname'])) ?></td>
                    <td><?= Html::encode($emp['position_name'] ?? '') ?></td>
                    <?php for ($d = 1; $d <= $days; $d++): ?>
                        <?php
                        $items = $grid[$empId][$d] ?? [];
                        $leave = $leaves[$empId][$d] ?? null;
                        $cls = isset($holidays[$d]) ? 'bg-danger-subtle' : (!empty($weekends[$d]) ? 'bg-secondary-subtle' : '');
                        ?>
                        <td class="text-center p-0 <?= $cls ?>">
                            <?php foreach ($items as $item): ?>
                                <span class="print-chip <?= $item->shiftCellClass() ?>">
                                    <?= Html::encode($item->shiftShort()) ?>
                                </span>
                            <?php endforeach; ?>
                            <?php if (empty($items) && $leave): ?>
                                <span class="text-danger-emphasis"><?= Html::encode($leave['ab']) ?></span>
                            <?php endif; ?>
                        </td>
                    <?php endfor; ?>
                    <td class="text-center fw-semibold"><?= $total ?></td>
                    <td class="text-center"><?= $offDays ?: '' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <?php foreach ($unitShiftList as $unitShift): ?>
                <tr>
                    <td colspan="3" class="fw-semibold">
                        <?= Html::encode($unitShift->displayName()) ?>
                        <?php $label = $unitShift->requiredLabel(); ?>
                        <?= $label !== '' ? ' (ต้องการ ' . Html::encode($label) . ')' : '' ?>
                    </td>
                    <?php for ($d = 1; $d <= $days; $d++): ?>
                        <?php
                        // อัตรากำลังต่างกันตามประเภทวัน — เสาร์/อาทิตย์/นักขัตฤกษ์ ใช้ค่าของวันนั้น
                        $need = $unitShift->requiredFor(
                            isset($holidays[$d]),
                            (int) date('w', strtotime($period->dateOfDay($d)))
                        );
                        $have = $counts[$d][(int) $unitShift->id] ?? 0;
                        ?>
                        <td class="text-center p-0 <?= $need > 0 && $have < $need ? 'text-danger-emphasis fw-bold' : '' ?>">
                            <?= $need > 0 ? $have . '/' . $need : $have ?>
                        </td>
                    <?php endfor; ?>
                    <td colspan="2"></td>
                </tr>
            <?php endforeach; ?>
        </tfoot>
    </table>

    <div class="d-flex justify-content-end gap-5 mt-4 pt-4 small">
        <div class="text-center">
            <div>ลงชื่อ ..............................................</div>
            <div class="mt-1">( .............................................. )</div>
            <div class="mt-1">ผู้จัดตารางเวร</div>
        </div>
        <div class="text-center">
            <div>ลงชื่อ ..............................................</div>
            <div class="mt-1">( .............................................. )</div>
            <div class="mt-1">ผู้อนุมัติ</div>
        </div>
    </div>
</div>

<?php
$this->registerCss(<<<'CSS'
@page { size: A4 landscape; margin: 8mm; }
.roster-print { font-size: 10px; }
.print-grid td, .print-grid th { padding: 1px 2px !important; font-size: 9px; line-height: 1.3; }
.print-chip { display: inline-block; min-width: 13px; padding: 0 2px; border-radius: 2px; font-weight: 700; }
@media print {
    .print-grid { page-break-inside: auto; }
    .print-grid tr { page-break-inside: avoid; }
}
CSS);
$this->registerJs('window.print();');
?>

<?php

use app\modules\roster\models\Swap;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var Swap $swap */
/** @var app\modules\roster\models\Period $period */
/** @var string|null $orgName */
/** @var app\modules\hr\models\Employees|null $approver */

$this->title = 'ใบเปลี่ยนตัวเวร #' . $swap->id;

$item = $swap->item;
$counter = $swap->counterItem;
$warnings = $swap->warningList();

$thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

/** วันที่แบบไทย เช่น 13 สิงหาคม 2569 — คืน '-' ถ้ายังไม่มีค่า */
$thaiDate = static function (?string $date) use ($thaiMonths): string {
    if (!$date) {
        return '-';
    }
    $ts = strtotime($date);
    return date('j', $ts) . ' ' . $thaiMonths[(int) date('n', $ts)] . ' ' . (date('Y', $ts) + 543);
};

/** ชื่อ + ตำแหน่ง ของเจ้าหน้าที่ */
$who = static function ($emp): array {
    if (!$emp) {
        return ['name' => '-', 'position' => ''];
    }
    return [
        'name' => trim(($emp->prefix ?? '') . $emp->fname . ' ' . $emp->lname),
        'position' => (string) ($emp->employeePosition->title ?? $emp->position_name ?? ''),
    ];
};

$from = $who($swap->fromEmployee);
$to = $who($swap->toEmployee);
$boss = $who($approver);
?>
<div class="swap-doc">
    <div class="text-center mb-3">
        <?php if (!empty($orgName)): ?>
            <div class="fw-bold"><?= Html::encode($orgName) ?></div>
        <?php endif; ?>
        <h5 class="fw-bold mt-1 mb-1">ใบขอเปลี่ยนตัวเวร</h5>
        <div class="small">
            <?= Html::encode($period->unitName()) ?> ·
            <?= Html::encode($period->title) ?> ประจำเดือน<?= Html::encode($period->monthLabel()) ?>
        </div>
    </div>

    <table class="table table-bordered table-sm align-middle mb-3">
        <tbody>
            <tr>
                <th style="width:26%">เลขที่ใบ</th>
                <td style="width:24%">#<?= (int) $swap->id ?></td>
                <th style="width:26%">วันที่ยื่น</th>
                <td><?= Html::encode($thaiDate($swap->created_at)) ?></td>
            </tr>
            <tr>
                <th>ประเภท</th>
                <td><?= Html::encode($swap->getTypeLabel()) ?></td>
                <th>สถานะ</th>
                <td><?= Html::encode($swap->getStatusLabel()) ?></td>
            </tr>
        </tbody>
    </table>

    <table class="table table-bordered table-sm align-middle mb-3">
        <thead class="text-center">
            <tr>
                <th style="width:16%">ฝ่าย</th>
                <th style="width:34%">ชื่อ-นามสกุล</th>
                <th style="width:22%">ตำแหน่ง</th>
                <th style="width:28%">เวรที่เกี่ยวข้อง</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th>ผู้ขอ</th>
                <td><?= Html::encode($from['name']) ?></td>
                <td><?= Html::encode($from['position']) ?></td>
                <td>
                    <?php if ($item): ?>
                        <?= Html::encode($item->shiftName()) ?><br>
                        <span class="small">วันที่ <?= Html::encode($thaiDate($item->work_date)) ?></span>
                    <?php else: ?>-<?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>ผู้รับ</th>
                <td><?= Html::encode($to['name']) ?></td>
                <td><?= Html::encode($to['position']) ?></td>
                <td>
                    <?php if ($counter): ?>
                        <?= Html::encode($counter->shiftName()) ?><br>
                        <span class="small">วันที่ <?= Html::encode($thaiDate($counter->work_date)) ?></span>
                    <?php else: ?>
                        <span class="small">— รับเวรของผู้ขอ ไม่มีเวรแลกคืน —</span>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <table class="table table-bordered table-sm mb-3">
        <tbody>
            <tr>
                <th style="width:26%">เหตุผล</th>
                <td><?= Html::encode((string) $swap->reason) ?: '&nbsp;' ?></td>
            </tr>
            <?php if ($warnings): ?>
                <tr>
                    <th>ข้อสังเกตจากกฎการจัดเวร</th>
                    <td>
                        <?php // บันทึกไว้เป็นหลักฐานว่าอนุมัติทั้งที่รู้ว่าผิดกฎอะไร ?>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($warnings as $warning): ?>
                                <li><?= Html::encode($warning) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php
    // เวรถูกเปลี่ยนมือหลังตารางประกาศแล้ว จึงต้องมีลายเซ็นครบสามฝ่ายไว้อ้างอิงตอนคิดค่าตอบแทน
    $signatures = [
        ['role' => 'ผู้ขอเปลี่ยนตัว', 'who' => $from, 'at' => $swap->created_at],
        ['role' => 'ผู้รับเวร', 'who' => $to, 'at' => $swap->responded_at],
        ['role' => 'หัวหน้าหน่วยงาน (ผู้อนุมัติ)', 'who' => $boss, 'at' => $swap->approved_at],
    ];
    ?>
    <div class="row text-center mt-5 pt-3">
        <?php foreach ($signatures as $sign): ?>
            <div class="col-4">
                <div>ลงชื่อ ................................</div>
                <div class="mt-1">
                    (<?= $sign['who']['name'] !== '-'
                        ? ' ' . Html::encode($sign['who']['name']) . ' '
                        : ' ................................ ' ?>)
                </div>
                <?php if ($sign['who']['position'] !== ''): ?>
                    <div class="small"><?= Html::encode($sign['who']['position']) ?></div>
                <?php endif; ?>
                <div class="mt-1"><?= $sign['role'] ?></div>
                <div class="small text-body-secondary">
                    วันที่ <?= Html::encode($thaiDate($sign['at'])) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php
$this->registerCss(<<<'CSS'
@page { size: A5 landscape; margin: 10mm; }
.swap-doc { font-size: 12px; }
.swap-doc th { background-color: var(--bs-tertiary-bg); font-weight: 600; }
.swap-doc .table td, .swap-doc .table th { padding: 3px 6px; }
CSS);
$this->registerJs('window.print();');
?>

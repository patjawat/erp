<?php

use app\modules\roster\models\Period;
use app\modules\roster\models\Request as RosterRequest;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $employee */
/** @var int $month */
/** @var int $year */
/** @var array $byDay */
/** @var array $reqByDay */
/** @var Period|null $period */
/** @var array $unitShifts */
/** @var array $types */
/** @var int $totalShifts */
/** @var app\modules\roster\models\Swap[] $incomingSwaps */
/** @var app\modules\roster\models\Swap[] $mySwaps */

use app\modules\roster\models\Swap;

// เวรที่มีใบขอเปลี่ยนตัวค้างอยู่ — ห้ามยื่นซ้ำ
$openSwapItemIds = [];
foreach (array_merge($incomingSwaps, $mySwaps) as $s) {
    $openSwapItemIds[(int) $s->item_id] = true;
    if ($s->counter_item_id) {
        $openSwapItemIds[(int) $s->counter_item_id] = true;
    }
}

$this->title = 'เวรของฉัน';
$this->params['breadcrumbs'][] = $this->title;

$days = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
$dowNames = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
$today = date('Y-m-d');

$prev = ['month' => $month === 1 ? 12 : $month - 1, 'year' => $month === 1 ? $year - 1 : $year];
$next = ['month' => $month === 12 ? 1 : $month + 1, 'year' => $month === 12 ? $year + 1 : $year];

$isPublished = $period && in_array($period->status, [Period::STATUS_PUBLISHED, Period::STATUS_CLOSED], true);
$canRequest = !$period || $period->status === Period::STATUS_DRAFT;
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-calendar-week"></i> <?= Html::encode($this->title) ?>
    </h4>
    <div class="text-body-secondary small">
        <?= Html::encode(Period::monthNames()[$month]) ?> <?= $year + 543 ?>
        <?php if ($isPublished): ?>
            · รวม <strong><?= $totalShifts ?></strong> เวร
        <?php endif; ?>
    </div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/me/menu', ['active' => 'roster']) ?>
<?php $this->endBlock(); ?>

<div class="card border shadow-sm mb-3">
    <div class="card-body d-flex align-items-center justify-content-between gap-2">
        <?= Html::a('<i class="bi bi-chevron-left"></i>', ['index', 'month' => $prev['month'], 'year' => $prev['year']], [
            'class' => 'btn btn-outline-secondary btn-sm',
        ]) ?>
        <div class="fw-semibold">
            <?= Html::encode(Period::monthNames()[$month]) ?> <?= $year + 543 ?>
        </div>
        <?= Html::a('<i class="bi bi-chevron-right"></i>', ['index', 'month' => $next['month'], 'year' => $next['year']], [
            'class' => 'btn btn-outline-secondary btn-sm',
        ]) ?>
    </div>
</div>

<?php if (!$isPublished): ?>
    <div class="alert alert-info border-0">
        <i class="bi bi-info-circle"></i>
        <?php if (!$period): ?>
            หัวหน้ายังไม่ได้เปิดรอบเวรของเดือนนี้ — คุณยื่นคำขอหยุด/ขออยู่ล่วงหน้าได้เลย
        <?php elseif ($period->status === Period::STATUS_DRAFT): ?>
            หัวหน้ากำลังจัดตารางเวรอยู่ — ยื่นคำขอได้จนกว่าจะส่งอนุมัติ
        <?php else: ?>
            ตารางเวรเดือนนี้<?= Html::encode($period->getStatusLabel()) ?> รอประกาศ
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($incomingSwaps): ?>
    <div class="card border border-warning shadow-sm mb-3">
        <div class="card-header bg-warning-subtle text-warning-emphasis">
            <h6 class="mb-0">
                <i class="bi bi-hand-index"></i> มีเพื่อนขอแลกเวรกับคุณ
                <span class="badge rounded-pill bg-warning text-dark"><?= count($incomingSwaps) ?></span>
            </h6>
        </div>
        <div class="list-group list-group-flush">
            <?php foreach ($incomingSwaps as $swap): ?>
                <?php
                $swapItem = $swap->item;
                $counter = $swap->counter_item_id ? $swap->counterItem : null;
                ?>
                <div class="list-group-item d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                    <div class="flex-grow-1">
                        <div>
                            <span class="fw-semibold"><?= Html::encode($swap->fromEmployee ? $swap->fromEmployee->fullname : '-') ?></span>
                            <span class="text-body-secondary"><?= Html::encode($swap->getTypeLabel()) ?></span>
                        </div>
                        <div class="small">
                            คุณจะได้รับ:
                            <?php if ($swapItem): ?>
                                <strong><?= Html::encode(date('d/m', strtotime($swapItem->work_date))) ?></strong>
                                <span class="badge rounded-pill px-2 <?= $swapItem->shiftCellClass() ?>">
                                    <?= Html::encode($swapItem->shiftShort()) ?>
                                </span>
                            <?php endif; ?>
                            <?php if ($counter): ?>
                                · เขาจะรับเวรคุณวันที่ <strong><?= Html::encode(date('d/m', strtotime($counter->work_date))) ?></strong>
                            <?php endif; ?>
                        </div>
                        <?php if ($swap->reason): ?>
                            <div class="small text-body-secondary">เหตุผล: <?= Html::encode($swap->reason) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-success swap-respond"
                                data-id="<?= $swap->id ?>" data-decision="accept">
                            <i class="bi bi-check-lg"></i> รับ
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger swap-respond"
                                data-id="<?= $swap->id ?>" data-decision="reject">
                            <i class="bi bi-x-lg"></i> ไม่รับ
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($mySwaps): ?>
    <div class="card border shadow-sm mb-3">
        <div class="card-header bg-body-tertiary">
            <h6 class="mb-0"><i class="bi bi-send"></i> คำขอแลกเวรที่คุณยื่นไว้</h6>
        </div>
        <div class="list-group list-group-flush">
            <?php foreach ($mySwaps as $swap): ?>
                <?php $swapItem = $swap->item; ?>
                <div class="list-group-item d-flex flex-column flex-sm-row align-items-sm-center gap-2">
                    <div class="flex-grow-1 small">
                        <?php if ($swapItem): ?>
                            <strong><?= Html::encode(date('d/m', strtotime($swapItem->work_date))) ?></strong>
                        <?php endif; ?>
                        <i class="bi bi-arrow-right text-body-secondary mx-1"></i>
                        <?= Html::encode($swap->toEmployee ? $swap->toEmployee->fullname : '-') ?>
                        <span class="badge bg-<?= $swap->getStatusColor() ?>-subtle text-<?= $swap->getStatusColor() ?>-emphasis ms-1">
                            <?= Html::encode($swap->getStatusLabel()) ?>
                        </span>
                    </div>
                    <?php if ($swap->status === Swap::STATUS_PENDING): ?>
                        <button type="button" class="btn btn-sm btn-outline-secondary swap-cancel" data-id="<?= $swap->id ?>">
                            <i class="bi bi-x"></i> ยกเลิก
                        </button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>

<div class="card border shadow-sm">
    <div class="card-header bg-body-tertiary d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <h6 class="mb-0"><i class="bi bi-list-check"></i> รายวัน</h6>
        <?php if ($canRequest): ?>
            <span class="text-body-secondary small">
                <i class="bi bi-hand-index"></i> กดปุ่มขอหยุด/ขออยู่ในวันที่ต้องการ
            </span>
        <?php endif; ?>
    </div>
    <div class="card-body p-0">
        <div class="list-group list-group-flush">
            <?php for ($d = 1; $d <= $days; $d++): ?>
                <?php
                $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $dow = (int) date('w', strtotime($date));
                $isWeekend = ($dow === 0 || $dow === 6);
                $items = $byDay[$d] ?? [];
                $reqs = $reqByDay[$d] ?? [];
                $isPast = $date < $today;
                $isToday = $date === $today;

                $reqOff = null;
                $reqOn = null;
                foreach ($reqs as $r) {
                    if ($r->type === RosterRequest::TYPE_OFF) {
                        $reqOff = $r;
                    } else {
                        $reqOn = $r;
                    }
                }
                ?>
                <div class="list-group-item d-flex flex-column flex-sm-row align-items-sm-center gap-2 <?= $isToday ? 'bg-primary-subtle' : ($isWeekend ? 'bg-body-tertiary' : '') ?>">
                    <div style="min-width:130px" class="d-flex align-items-center gap-2">
                        <span class="fw-semibold"><?= $d ?></span>
                        <span class="text-body-secondary small"><?= $dowNames[$dow] ?></span>
                        <?php if ($isToday): ?>
                            <span class="badge bg-primary-subtle text-primary-emphasis">วันนี้</span>
                        <?php endif; ?>
                    </div>

                    <div class="flex-grow-1 d-flex flex-wrap gap-1 align-items-center">
                        <?php if ($isPublished && $items): ?>
                            <?php foreach ($items as $item): ?>
                                <span class="badge rounded-pill px-3 <?= $item->shiftCellClass() ?>">
                                    <?= Html::encode($item->shiftName()) ?>
                                    <?php if ($item->unitShift): ?>
                                        <span class="opacity-75 ms-1"><?= Html::encode($item->unitShift->timeRangeLabel()) ?></span>
                                    <?php endif; ?>
                                </span>
                            <?php endforeach; ?>
                        <?php elseif ($isPublished): ?>
                            <span class="text-body-secondary small">ไม่มีเวร</span>
                        <?php endif; ?>

                        <?php if ($reqOff): ?>
                            <span class="badge bg-danger-subtle text-danger-emphasis">
                                ขอหยุด · <?= Html::encode($reqOff->getStatusLabel()) ?>
                            </span>
                        <?php endif; ?>
                        <?php if ($reqOn): ?>
                            <span class="badge bg-success-subtle text-success-emphasis">
                                ขออยู่เวร · <?= Html::encode($reqOn->getStatusLabel()) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($canRequest && !$isPast): ?>
                        <div class="d-flex gap-1">
                            <button type="button"
                                    class="btn btn-sm <?= $reqOff ? 'btn-danger' : 'btn-outline-danger' ?> req-btn"
                                    data-date="<?= $date ?>" data-type="off"
                                    <?= $reqOff && $reqOff->status !== RosterRequest::STATUS_PENDING ? 'disabled' : '' ?>>
                                <i class="bi bi-x-lg"></i> ขอหยุด
                            </button>
                            <button type="button"
                                    class="btn btn-sm <?= $reqOn ? 'btn-success' : 'btn-outline-success' ?> req-btn"
                                    data-date="<?= $date ?>" data-type="on"
                                    <?= $reqOn && $reqOn->status !== RosterRequest::STATUS_PENDING ? 'disabled' : '' ?>>
                                <i class="bi bi-check-lg"></i> ขออยู่
                            </button>
                        </div>
                    <?php elseif ($period && $period->allowsSwap() && $items && !$isPast): ?>
                        <div class="d-flex gap-1">
                            <?php foreach ($items as $item): ?>
                                <?php if (isset($openSwapItemIds[(int) $item->id])): ?>
                                    <span class="badge bg-warning-subtle text-warning-emphasis align-self-center">
                                        มีคำขอค้างอยู่
                                    </span>
                                <?php else: ?>
                                    <?= Html::a('<i class="bi bi-arrow-left-right"></i> ขอแลก',
                                        ['swap-form', 'item_id' => $item->id], [
                                            'class' => 'btn btn-sm btn-outline-primary open-modal',
                                            'data' => ['size' => 'modal-lg'],
                                        ]) ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<?php
$requestUrl = Url::to(['request']);
$respondUrl = Url::to(['swap-respond']);
$cancelUrl = Url::to(['swap-cancel']);
$js = <<<JS
function rosterPost(url, data, okText) {
    jQuery.post(url, data, function (res) {
        if (res.status === 'success') {
            if (typeof success === 'function') { success(res.message || okText); }
            window.location.reload();
        } else if (typeof warning === 'function') {
            warning(res.message);
        } else {
            alert(res.message);
        }
    });
}

jQuery('body').on('click', '.swap-respond', function () {
    var \$btn = jQuery(this);
    var decision = \$btn.data('decision');
    var text = decision === 'accept'
        ? 'รับเวรนี้แทนเพื่อน? หัวหน้าจะเป็นผู้อนุมัติขั้นสุดท้าย'
        : 'ปฏิเสธคำขอนี้?';
    if (!window.confirm(text)) { return; }
    rosterPost('{$respondUrl}', { swap_id: \$btn.data('id'), decision: decision }, 'บันทึกแล้ว');
});

jQuery('body').on('click', '.swap-cancel', function () {
    if (!window.confirm('ยกเลิกคำขอแลกเวรนี้?')) { return; }
    rosterPost('{$cancelUrl}', { swap_id: jQuery(this).data('id') }, 'ยกเลิกแล้ว');
});

jQuery('body').on('click', '.req-btn', function () {
    var \$btn = jQuery(this).prop('disabled', true);
    jQuery.post('{$requestUrl}', {
        work_date: \$btn.data('date'),
        type: \$btn.data('type')
    }, function (res) {
        \$btn.prop('disabled', false);
        if (res.status === 'success') {
            if (typeof success === 'function') { success(res.action === 'added' ? 'ยื่นคำขอแล้ว' : 'ยกเลิกคำขอแล้ว'); }
            window.location.reload();
        } else if (typeof warning === 'function') {
            warning(res.message);
        } else {
            alert(res.message);
        }
    }).fail(function () {
        \$btn.prop('disabled', false);
        if (typeof warning === 'function') { warning('เชื่อมต่อไม่สำเร็จ'); }
    });
});
JS;
$this->registerJs($js);
?>

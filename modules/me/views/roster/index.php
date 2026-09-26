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

<?php
// ปฏิทินรายเดือน — ทั้งเดือนอยู่ในจอเดียว แทนรายการ 31 แถวที่ต้องเลื่อนยาว
// ยื่นคำขอแบบ "ปากกา" เหมือนหน้าจัดเวร: เลือก ขอหยุด/ขออยู่ แล้วแตะวัน · แตะซ้ำ = ยกเลิก
$shortDow = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];
$firstDow = (int) date('w', mktime(0, 0, 0, $month, 1, $year));
$canSwap = $period && $period->allowsSwap();
?>
<div class="card border shadow-sm my-cal">
    <div class="card-header bg-body-tertiary d-flex align-items-center gap-2 flex-wrap">
        <?= Html::a('<i class="bi bi-chevron-left"></i>', ['index', 'month' => $prev['month'], 'year' => $prev['year']], [
            'class' => 'btn btn-sm btn-outline-secondary', 'aria-label' => 'เดือนก่อน',
        ]) ?>
        <span class="fw-semibold text-center" style="min-width:120px">
            <?= Html::encode(Period::monthNames()[$month]) ?> <?= $year + 543 ?>
        </span>
        <?= Html::a('<i class="bi bi-chevron-right"></i>', ['index', 'month' => $next['month'], 'year' => $next['year']], [
            'class' => 'btn btn-sm btn-outline-secondary', 'aria-label' => 'เดือนถัดไป',
        ]) ?>

        <span class="small text-body-secondary ms-sm-2">
            <?php if (!$period): ?>
                <i class="bi bi-info-circle"></i> ยังไม่เปิดรอบเวร — ยื่นคำขอล่วงหน้าได้
            <?php elseif ($period->status === Period::STATUS_DRAFT): ?>
                <i class="bi bi-info-circle"></i> หัวหน้ากำลังจัดเวร — ยื่นคำขอได้จนกว่าจะส่งอนุมัติ
            <?php elseif (!$isPublished): ?>
                <i class="bi bi-info-circle"></i> <?= Html::encode($period->getStatusLabel()) ?> รอประกาศ
            <?php elseif ($canSwap): ?>
                <i class="bi bi-hand-index"></i> แตะเวรเพื่อขอแลกกับเพื่อน
            <?php endif; ?>
        </span>

        <?php if ($canRequest): ?>
            <div class="btn-group btn-group-sm ms-auto req-tool" role="group" aria-label="ประเภทคำขอ">
                <button type="button" class="btn btn-outline-danger active" data-tool="off" aria-pressed="true">
                    <i class="bi bi-x-lg"></i> ขอหยุด
                </button>
                <button type="button" class="btn btn-outline-success" data-tool="on" aria-pressed="false">
                    <i class="bi bi-check-lg"></i> ขออยู่
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($canRequest): ?>
        <div class="px-3 pt-2 small text-body-secondary">
            เลือก <strong>ขอหยุด</strong> หรือ <strong>ขออยู่</strong> แล้วแตะวันที่ต้องการ · แตะซ้ำเพื่อยกเลิก
        </div>
    <?php endif; ?>

    <div class="card-body p-2">
        <div class="cal-grid" role="grid">
            <?php foreach ($shortDow as $i => $name): ?>
                <div class="cal-dow <?= $i === 0 || $i === 6 ? 'text-danger-emphasis' : '' ?>"><?= $name ?></div>
            <?php endforeach; ?>

            <?php for ($i = 0; $i < $firstDow; $i++): ?>
                <div class="cal-cell cal-empty" aria-hidden="true"></div>
            <?php endfor; ?>

            <?php for ($d = 1; $d <= $days; $d++): ?>
                <?php
                $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
                $dow = (int) date('w', strtotime($date));
                $isWeekend = ($dow === 0 || $dow === 6);
                $items = $byDay[$d] ?? [];
                $isPast = $date < $today;
                $isToday = $date === $today;

                $reqOff = null;
                $reqOn = null;
                foreach ($reqByDay[$d] ?? [] as $r) {
                    if ($r->type === RosterRequest::TYPE_OFF) {
                        $reqOff = $r;
                    } else {
                        $reqOn = $r;
                    }
                }
                $tappable = $canRequest && !$isPast;
                $classes = ['cal-cell'];
                if ($isWeekend) {
                    $classes[] = 'is-weekend';
                }
                if ($isToday) {
                    $classes[] = 'is-today';
                }
                if ($isPast) {
                    $classes[] = 'is-past';
                }
                if ($tappable) {
                    $classes[] = 'is-tappable';
                }
                // คำขอที่หัวหน้าพิจารณาแล้วยกเลิกเองไม่ได้ — ล็อกไว้ฝั่งหน้าจอด้วย
                $lockOff = $reqOff && $reqOff->status !== RosterRequest::STATUS_PENDING;
                $lockOn = $reqOn && $reqOn->status !== RosterRequest::STATUS_PENDING;
                ?>
                <div class="<?= implode(' ', $classes) ?>" data-date="<?= $date ?>"
                     <?= $tappable ? 'role="button" tabindex="0"' : '' ?>
                     data-off="<?= $reqOff ? 1 : 0 ?>" data-on="<?= $reqOn ? 1 : 0 ?>"
                     data-lock-off="<?= $lockOff ? 1 : 0 ?>" data-lock-on="<?= $lockOn ? 1 : 0 ?>"
                     aria-label="วันที่ <?= $d ?> <?= $dowNames[$dow] ?>">
                    <div class="cal-day"><?= $d ?></div>

                    <?php if ($isPublished): ?>
                        <?php foreach ($items as $item): ?>
                            <?php
                            $title = $item->shiftName() . ($item->unitShift ? ' ' . $item->unitShift->timeRangeLabel() : '');
                            $pendingSwap = isset($openSwapItemIds[(int) $item->id]);
                            ?>
                            <?php if ($canSwap && !$isPast && !$pendingSwap): ?>
                                <?= Html::a(Html::encode($item->shiftShort()), ['swap-form', 'item_id' => $item->id], [
                                    'class' => 'cal-chip open-modal ' . $item->shiftCellClass(),
                                    'data' => ['size' => 'modal-lg'],
                                    'title' => $title . ' · แตะเพื่อขอแลก',
                                ]) ?>
                            <?php else: ?>
                                <span class="cal-chip <?= $item->shiftCellClass() ?><?= $pendingSwap ? ' is-swap-pending' : '' ?>"
                                      title="<?= Html::encode($title . ($pendingSwap ? ' · มีคำขอแลกค้างอยู่' : '')) ?>">
                                    <?= Html::encode($item->shiftShort()) ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <span class="cal-req cal-req-off<?= $reqOff ? '' : ' d-none' ?><?= $reqOff && $reqOff->status === RosterRequest::STATUS_REJECTED ? ' is-rejected' : '' ?>"
                          title="ขอหยุด · <?= $reqOff ? Html::encode($reqOff->getStatusLabel()) : 'รอพิจารณา' ?>">
                        <i class="bi bi-x-lg"></i> หยุด
                    </span>
                    <span class="cal-req cal-req-on<?= $reqOn ? '' : ' d-none' ?><?= $reqOn && $reqOn->status === RosterRequest::STATUS_REJECTED ? ' is-rejected' : '' ?>"
                          title="ขออยู่เวร · <?= $reqOn ? Html::encode($reqOn->getStatusLabel()) : 'รอพิจารณา' ?>">
                        <i class="bi bi-check-lg"></i> อยู่
                    </span>
                </div>
            <?php endfor; ?>
        </div>

        <?php if ($isPublished && $unitShifts): ?>
            <div class="cal-legend">
                <?php foreach ($unitShifts as $unitShift): ?>
                    <span>
                        <span class="cal-chip <?= $unitShift->cellClass() ?>"><?= Html::encode($unitShift->displayShort()) ?></span>
                        <?= Html::encode($unitShift->displayName()) ?>
                        <span class="text-body-secondary"><?= Html::encode($unitShift->timeRangeLabel()) ?></span>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php
$this->registerCss(<<<'CSS'
.cal-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: 4px; }
.cal-dow { text-align: center; font-size: .78rem; font-weight: 600; color: var(--bs-secondary-color); padding: 2px 0; }
.cal-cell {
    min-height: 68px; padding: 3px 4px; border: 1px solid var(--bs-border-color-translucent);
    border-radius: var(--bs-border-radius); background: var(--bs-body-bg);
    display: flex; flex-wrap: wrap; align-content: flex-start; gap: 2px;
}
.cal-empty { border: 0; background: none; }
.cal-cell.is-weekend { background: var(--bs-tertiary-bg); }
.cal-cell.is-past { opacity: .55; }
.cal-cell.is-today { border: 2px solid var(--bs-primary); }
.cal-cell.is-tappable { cursor: pointer; transition: box-shadow .12s ease-out; }
.cal-cell.is-tappable:hover, .cal-cell.is-tappable:focus-visible { box-shadow: 0 0 0 .2rem var(--bs-primary-border-subtle); outline: 0; }
.cal-cell.is-saving { opacity: .5; pointer-events: none; }
.cal-day { width: 100%; font-size: .8rem; font-weight: 700; line-height: 1.2; }
.cal-cell.is-today .cal-day { color: var(--bs-primary-text-emphasis); }
.cal-chip {
    display: inline-block; min-width: 24px; padding: 1px 5px; border-radius: var(--bs-border-radius-sm);
    font-size: .8rem; font-weight: 700; line-height: 1.35; text-align: center; text-decoration: none;
}
a.cal-chip:hover { outline: 2px solid var(--bs-primary); outline-offset: 1px; }
.cal-chip.is-swap-pending { outline: 2px dotted var(--bs-warning); outline-offset: -2px; }
.cal-req { font-size: .72rem; font-weight: 600; line-height: 1.3; padding: 0 4px; border-radius: var(--bs-border-radius-sm); white-space: nowrap; }
.cal-req-off { background: var(--bs-danger-bg-subtle); color: var(--bs-danger-text-emphasis); }
.cal-req-on { background: var(--bs-success-bg-subtle); color: var(--bs-success-text-emphasis); }
.cal-req.is-rejected { text-decoration: line-through; opacity: .6; }
.cal-legend { display: flex; flex-wrap: wrap; gap: .35rem 1rem; margin-top: .6rem; font-size: .8rem; }
@media (max-width: 575.98px) {
    .cal-cell { min-height: 56px; padding: 2px; }
    .cal-req { font-size: 0; padding: 0 3px; }
    .cal-req .bi { font-size: .72rem; }
}
CSS);
?>
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

// ── คำขอหยุด/ขออยู่: เลือกเครื่องมือแล้วแตะวัน (แตะซ้ำ = ยกเลิก) ──────────
var reqTool = 'off';
jQuery('.req-tool').on('click', '[data-tool]', function () {
    reqTool = jQuery(this).data('tool');
    jQuery('.req-tool [data-tool]').each(function () {
        var on = jQuery(this).data('tool') === reqTool;
        jQuery(this).toggleClass('active', on).attr('aria-pressed', on ? 'true' : 'false');
    });
});

function reqWarn(message) {
    if (typeof warning === 'function') { warning(message); } else { alert(message); }
}

// สลับคำขอหนึ่งประเภทของวันนั้น — อัปเดตช่องในที่ ไม่ต้องโหลดหน้าใหม่
function toggleRequest(\$cell, type) {
    return jQuery.post('{$requestUrl}', { work_date: \$cell.data('date'), type: type }).then(function (res) {
        if (res.status !== 'success') { return jQuery.Deferred().reject(res.message || 'บันทึกไม่สำเร็จ').promise(); }
        var has = res.action === 'added';
        \$cell.attr('data-' + type, has ? 1 : 0);
        \$cell.find('.cal-req-' + type).toggleClass('d-none', !has).removeClass('is-rejected')
            .attr('title', (type === 'off' ? 'ขอหยุด' : 'ขออยู่เวร') + ' · รอพิจารณา');
        return res;
    });
}

function tapDay(\$cell) {
    if (\$cell.hasClass('is-saving')) { return; }
    var type = reqTool;
    var other = type === 'off' ? 'on' : 'off';
    if (String(\$cell.attr('data-lock-' + type)) === '1') {
        reqWarn('หัวหน้าพิจารณาคำขอนี้แล้ว ยกเลิกเองไม่ได้');
        return;
    }
    // ขอหยุดกับขออยู่วันเดียวกันขัดกันเอง — ยื่นแบบใหม่ให้ถอนแบบเดิมออกก่อน
    var clearOther = String(\$cell.attr('data-' + other)) === '1' && String(\$cell.attr('data-lock-' + other)) !== '1'
        && String(\$cell.attr('data-' + type)) !== '1';

    \$cell.addClass('is-saving');
    var chain = clearOther ? toggleRequest(\$cell, other) : jQuery.Deferred().resolve().promise();
    chain.then(function () { return toggleRequest(\$cell, type); })
        .done(function (res) {
            if (typeof success === 'function') { success(res.action === 'added' ? 'ยื่นคำขอแล้ว' : 'ยกเลิกคำขอแล้ว'); }
        })
        .fail(function (message) {
            reqWarn(typeof message === 'string' ? message : 'เชื่อมต่อไม่สำเร็จ');
        })
        .always(function () { \$cell.removeClass('is-saving'); });
}

jQuery('body').on('click', '.cal-cell.is-tappable', function (e) {
    if (jQuery(e.target).closest('a').length) { return; }
    tapDay(jQuery(this));
});
jQuery('body').on('keydown', '.cal-cell.is-tappable', function (e) {
    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); tapDay(jQuery(this)); }
});
JS;
$this->registerJs($js);
?>

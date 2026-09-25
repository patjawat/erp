<?php
use yii\helpers\Html;
use yii\helpers\Url;
use app\components\AppHelper;
use app\components\ThaiDate;
use app\widgets\datepicker\DatepickerThai;

/** @var string $date วันที่ที่ดู (Y-m-d) */
/** @var string $today */
/** @var array $day ผลจาก AttendanceDashboard::daily() */
/** @var \app\modules\attendance\models\CheckinRecord[] $exceptions */
/** @var int $pendingCount */
/** @var int $orphanCount */
$this->title = 'ภาพรวมระบบลงเวลา';
$this->params['breadcrumbs'][] = ['label' => 'ระบบลงเวลา', 'url' => ['/attendance/default/index']];
$this->params['breadcrumbs'][] = 'ภาพรวม';

$isToday = $date === $today;
$prev = date('Y-m-d', strtotime($date . ' -1 day'));
$next = date('Y-m-d', strtotime($date . ' +1 day'));
$pct = fn($n, $d) => $d > 0 ? round($n * 100 / $d) : 0;
$dash = '<span class="text-body-tertiary">–</span>';
// แถวบน: ทุกคนอยู่สถานะเดียว (รวมกันเท่ากับบุคลากรทั้งหมด) — [ป้าย, ค่า, ไอคอน, สี, คำอธิบาย, ลิงก์]
$kpis = [
    ['บุคลากรทั้งหมด', $day['total'], 'bi-people', 'secondary', 'สถานะปฏิบัติงาน', null],
    ['ลงเวลาแล้ว', $day['present'], 'bi-person-check', 'success', $pct($day['present'], $day['total']) . '% ของบุคลากร', null],
    ['ลา / ไปราชการ', $day['leave'] + $day['trip'], 'bi-calendar-x', 'info', 'ลา ' . (int)$day['leave'] . ' · ไปราชการ ' . (int)$day['trip'], null],
    ['ยังไม่ลงเวลา', $day['missing'], 'bi-person-dash', 'danger', 'มีเวร/วันทำงาน แต่ยังไม่ลงเวลา', null],
    ['หยุด', $day['off'], 'bi-moon', 'secondary', 'หยุดตามเวร/วันหยุด', null],
    ['ยังไม่ตั้งเวลาทำงาน', $day['unset'], 'bi-question-circle', 'warning', 'ไม่มีตารางเวร/ชุดเวลา จึงประเมินการขาดไม่ได้', ['/attendance/schedule/index']],
];
// แถวล่าง: ความผิดปกติที่ต้องติดตาม
$flags = [
    ['มาสาย', $day['late'], 'bi-alarm', 'warning', 'ออกก่อน ' . (int)$day['early'] . ' คน', null],
    ['นอกพื้นที่', $day['outside'], 'bi-geo', 'warning', 'ลงเวลานอกจุดที่กำหนด', null],
];
$badge = function ($r) {
    $map = ['approved' => ['success', 'ยืนยันแล้ว'], 'rejected' => ['danger', 'ไม่ยืนยัน'], 'pending' => ['warning', 'รอยืนยัน']];
    [$c, $l] = $map[$r->status] ?? ['secondary', $r->getStatusLabel()];
    return '<span class="badge bg-' . $c . '-subtle text-' . $c . '-emphasis">' . Html::encode($l) . '</span>';
};
?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/attendance/menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-title'); ?>
<h4 class="fw-semibold text-body mb-1"><i class="bi bi-clock-history me-2" aria-hidden="true"></i>ระบบลงเวลาเข้างาน</h4>
<p class="small text-body-secondary mb-0">ภาพรวมการลงเวลาของทั้งโรงพยาบาลรายวัน สำหรับผู้ดูแลระบบลงเวลา</p>
<?php $this->endBlock(); ?>

<div class="att-overview d-flex flex-column gap-3">

    <!-- เลือกวัน -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
        <h2 class="h5 fw-semibold mb-0">
            <?= Html::encode(ThaiDate::toThaiDate($date, false)) ?>
            <?php if ($isToday): ?><span class="badge bg-primary-subtle text-primary-emphasis align-middle ms-1">วันนี้</span><?php endif; ?>
        </h2>
        <?= Html::beginForm(['index'], 'get', ['class' => 'd-flex flex-wrap align-items-center gap-2', 'id' => 'att-date-form']) ?>
            <?= Html::a('<i class="bi bi-chevron-left" aria-hidden="true"></i>', ['index', 'date' => $prev], ['class' => 'btn btn-outline-secondary', 'title' => 'วันก่อนหน้า', 'aria-label' => 'วันก่อนหน้า']) ?>
            <div class="att-date-input">
                <?= DatepickerThai::widget(['name' => 'd', 'value' => AppHelper::convertToThai($date), 'options' => ['id' => 'att-date', 'aria-label' => 'เลือกวันที่']]) ?>
            </div>
            <?php if (!$isToday): ?>
                <?= Html::a('<i class="bi bi-chevron-right" aria-hidden="true"></i>', ['index', 'date' => $next], ['class' => 'btn btn-outline-secondary', 'title' => 'วันถัดไป', 'aria-label' => 'วันถัดไป']) ?>
                <?= Html::a('วันนี้', ['index'], ['class' => 'btn btn-outline-primary']) ?>
            <?php endif; ?>
        <?= Html::endForm() ?>
    </div>

    <?php if ($orphanCount > 0): ?>
    <div class="alert alert-warning d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0" role="status">
        <span><i class="bi bi-exclamation-triangle me-1" aria-hidden="true"></i>
            มี <strong><?= (int)$orphanCount ?> รายการ</strong> รอยืนยันที่ยังไม่มีหัวหน้าเป็นผู้ยืนยัน (ไม่มีหัวหน้าในผัง หรือหัวหน้าถัดไปเป็น ผอ.)</span>
        <?= Html::a('ตั้งผู้ยืนยันแทน', ['/attendance/schedule/reviewer'], ['class' => 'btn btn-sm btn-warning']) ?>
    </div>
    <?php endif; ?>

    <!-- KPI (นับเป็นคน) -->
    <?php
    $card = function ($label, $value, $icon, $color, $hint, $url, $unit = 'คน') {
        $tag = $url ? 'a' : 'div';
        $attr = $url ? ' href="' . Html::encode(Url::to($url)) . '"' : '';
        return '<' . $tag . ' class="card bg-body border h-100 text-decoration-none' . ($url ? ' att-kpi-link' : '') . '"' . $attr . '>'
            . '<div class="card-body d-flex align-items-start gap-3 py-3">'
            . '<span class="att-kpi-icon bg-' . $color . '-subtle text-' . $color . '-emphasis"><i class="bi ' . $icon . '" aria-hidden="true"></i></span>'
            . '<div class="min-w-0 flex-grow-1">'
            . '<div class="small text-body-secondary">' . Html::encode($label) . '</div>'
            . '<div class="att-num fs-4 fw-bold text-body lh-sm">' . number_format((int)$value) . ' <span class="fs-6 fw-normal text-body-secondary">' . $unit . '</span></div>'
            . '<div class="small ' . ($url ? 'text-primary' : 'text-body-secondary') . '">' . Html::encode($hint) . ($url ? ' <i class="bi bi-arrow-right" aria-hidden="true"></i>' : '') . '</div>'
            . '</div></div></' . $tag . '>';
    };
    ?>
    <section aria-label="สถานะบุคลากรวันนี้">
        <div class="att-kpi-grid att-kpi-grid--6">
            <?php foreach ($kpis as [$label, $value, $icon, $color, $hint, $url]) echo $card($label, $value, $icon, $color, $hint, $url); ?>
        </div>
    </section>

    <?php if ($day['unset'] > 0 && $day['unset'] >= $day['total'] / 2): ?>
    <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2 mb-0" role="status">
        <span><i class="bi bi-info-circle me-1" aria-hidden="true"></i>
            บุคลากร <strong><?= number_format($day['unset']) ?> คน</strong> ยังไม่มีตารางเวรหรือชุดเวลาทำงาน ระบบจึงนับการขาดและมาสายให้ไม่ได้ กำหนดเวลาทำงานรายหน่วยงานเพื่อให้ภาพรวมครบ</span>
        <?= Html::a('ตั้งค่าเวลาทำงาน', ['/attendance/schedule/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
    </div>
    <?php endif; ?>

    <section aria-label="รายการผิดปกติ">
        <div class="att-kpi-grid att-kpi-grid--3">
            <?php foreach ($flags as [$label, $value, $icon, $color, $hint, $url]) echo $card($label, $value, $icon, $color, $hint, $url); ?>
            <?= $card('รอยืนยัน (ทุกวัน)', $pendingCount, 'bi-hourglass-split', 'warning', 'ไปหน้าตรวจสอบ', ['/attendance/checkin/confirm'], 'รายการ') ?>
        </div>
    </section>

    <div class="row g-3">
        <!-- รายหน่วยงาน -->
        <div class="col-12 col-xl-7">
            <section class="card bg-body border h-100">
                <div class="card-header bg-body d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                    <div>
                        <h2 class="h6 fw-semibold mb-0">แยกตามหน่วยงาน</h2>
                        <span class="small text-body-secondary">เรียงจากหน่วยที่ยังไม่ลงเวลามากที่สุด</span>
                    </div>
                    <?= Html::a('สรุปรายเดือน', ['/attendance/checkin/monthly'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($day['units'])): ?>
                        <p class="text-center text-body-secondary py-4 mb-0">ไม่มีข้อมูลบุคลากร</p>
                    <?php else: ?>
                    <div class="att-scroll">
                        <table class="table table-sm table-hover align-middle mb-0 att-table">
                            <thead>
                                <tr>
                                    <th scope="col">หน่วยงาน</th>
                                    <th scope="col" class="text-end">บุคลากร</th>
                                    <th scope="col" class="text-end">ลงเวลาแล้ว</th>
                                    <th scope="col" class="text-end">ลา/ราชการ</th>
                                    <th scope="col" class="text-end">ยังไม่ลงเวลา</th>
                                    <th scope="col" class="text-end d-none d-md-table-cell">หยุด</th>
                                    <th scope="col" class="text-end d-none d-md-table-cell" title="ยังไม่มีตารางเวร/ชุดเวลาทำงาน">ไม่ตั้งเวลา</th>
                                    <th scope="col" class="text-end d-none d-md-table-cell">สาย</th>
                                    <th scope="col" class="text-end d-none d-md-table-cell">นอกพื้นที่</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($day['units'] as $u): $rate = $pct($u['present'], $u['staff']); ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-body"><?= Html::encode($u['name']) ?></div>
                                        <div class="progress att-progress" role="progressbar" aria-label="สัดส่วนที่ลงเวลาแล้ว" aria-valuenow="<?= $rate ?>" aria-valuemin="0" aria-valuemax="100">
                                            <div class="progress-bar bg-success" style="width: <?= $rate ?>%"></div>
                                        </div>
                                    </td>
                                    <td class="text-end att-num"><?= $u['staff'] ?></td>
                                    <td class="text-end att-num"><?= $u['present'] ?></td>
                                    <td class="text-end att-num"><?= ($u['leave'] + $u['trip']) ?: $dash ?></td>
                                    <td class="text-end att-num"><?= $u['missing'] ? '<span class="fw-semibold text-danger-emphasis">' . $u['missing'] . '</span>' : $dash ?></td>
                                    <td class="text-end att-num d-none d-md-table-cell"><?= $u['off'] ?: $dash ?></td>
                                    <td class="text-end att-num d-none d-md-table-cell"><?= $u['unset'] ? '<span class="text-body-secondary">' . $u['unset'] . '</span>' : $dash ?></td>
                                    <td class="text-end att-num d-none d-md-table-cell"><?= $u['late'] ? '<span class="text-warning-emphasis">' . $u['late'] . '</span>' : '<span class="text-body-tertiary">–</span>' ?></td>
                                    <td class="text-end att-num d-none d-md-table-cell"><?= $u['outside'] ? '<span class="text-warning-emphasis">' . $u['outside'] . '</span>' : '<span class="text-body-tertiary">–</span>' ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <!-- รายการที่ต้องติดตาม (ดูอย่างเดียว) -->
        <div class="col-12 col-xl-5">
            <section class="card bg-body border h-100">
                <div class="card-header bg-body d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                    <div>
                        <h2 class="h6 fw-semibold mb-0">รายการที่ต้องติดตาม</h2>
                        <span class="small text-body-secondary">สาย ออกก่อน นอกพื้นที่ ไม่ตรงเวร หรือไม่ยืนยัน ของวันที่เลือก</span>
                    </div>
                    <?= Html::a('ทั้งหน่วยงาน', ['/attendance/checkin/report'], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($exceptions)): ?>
                        <div class="text-center text-body-secondary py-5">
                            <i class="bi bi-check2-circle fs-3 d-block mb-2 text-success" aria-hidden="true"></i>
                            ไม่มีรายการผิดปกติในวันนี้
                        </div>
                    <?php else: ?>
                    <ul class="list-group list-group-flush att-scroll">
                        <?php foreach ($exceptions as $r): $td = $r->timeDetail(); ?>
                        <li class="list-group-item d-flex align-items-start gap-2 py-2">
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="fw-semibold text-body"><?= Html::encode($r->employee ? $r->employee->fname . ' ' . $r->employee->lname : '—') ?></span>
                                    <?= $badge($r) ?>
                                </div>
                                <div class="small text-body-secondary">
                                    <span class="att-num"><?= Yii::$app->formatter->asDatetime($r->checkin_at, 'php:H:i') ?></span>
                                    · <?= Html::encode($r->getCheckTypeLabel()) ?>
                                    <?php if ($td['expected'] !== ''): ?> · เวร <?= Html::encode($td['expected']) ?><?php endif; ?>
                                </div>
                                <div class="mt-1">
                                    <?php foreach ($td['badges'] as $b): if ($b[0] === 'ok') continue; $bs = $b[0] === 'no' ? 'danger' : 'warning'; ?>
                                        <span class="badge bg-<?= $bs ?>-subtle text-<?= $bs ?>-emphasis me-1"><?= Html::encode($b[1]) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?= Html::a('<i class="bi bi-eye" aria-hidden="true"></i>', ['/attendance/checkin/view', 'id' => $r->id], ['class' => 'btn btn-sm btn-outline-secondary open-modal', 'title' => 'ดูรายละเอียด', 'aria-label' => 'ดูรายละเอียด', 'data' => ['size' => 'modal-lg']]) ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
.att-overview .att-kpi-grid { display: grid; gap: .75rem; }
.att-overview .att-kpi-grid--6 { grid-template-columns: repeat(6, minmax(0, 1fr)); }
.att-overview .att-kpi-grid--3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
@media (max-width: 1399.98px) { .att-overview .att-kpi-grid--6 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 767.98px)  { .att-overview .att-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
.att-overview .card { border-radius: 10px; border-color: var(--bs-border-color-translucent) !important; }
.att-overview .att-kpi-icon { display: inline-flex; align-items: center; justify-content: center; flex: none; width: 38px; height: 38px; border-radius: 8px; font-size: 1.1rem; }
.att-overview .att-kpi-link { transition: border-color 140ms cubic-bezier(0.16, 1, 0.3, 1); }
.att-overview .att-kpi-link:hover { border-color: var(--bs-primary) !important; }
.att-overview .att-kpi-link:focus-visible { outline: 0; box-shadow: 0 0 0 3px rgba(var(--bs-primary-rgb), .25); }
.att-overview .att-num { font-variant-numeric: tabular-nums; }
.att-overview .min-w-0 { min-width: 0; }
.att-overview .att-date-input { width: 150px; }
.att-overview .att-scroll { max-height: 520px; overflow-y: auto; }
.att-overview .att-table thead th { position: sticky; top: 0; z-index: 1; background: var(--bs-tertiary-bg); font-size: .8rem; font-weight: 600; color: var(--bs-secondary-color); white-space: nowrap; }
.att-overview .att-table td, .att-overview .att-table th { padding: .5rem .75rem; }
.att-overview .att-progress { height: 4px; max-width: 220px; margin-top: .3rem; background: var(--bs-secondary-bg); }
@media (prefers-reduced-motion: reduce) { .att-overview .att-kpi-link { transition: none; } }
</style>
<?php
$this->registerJs(<<<JS
$('#att-date').on('change', function () { if (this.value && this.value.indexOf('_') === -1) $('#att-date-form').trigger('submit'); });
JS
);

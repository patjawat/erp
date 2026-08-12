<?php

use app\modules\roster\models\Period;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var int $month */
/** @var int $year */
/** @var array $units */
/** @var Period[] $periods */
/** @var array $statusMatrix */
/** @var array $coverage */
/** @var array $violations */
/** @var array $fairness */
/** @var array $swapCounts */
/** @var array $types */
/** @var int $pendingCount */

$this->title = 'ภาพรวมตารางเวร';
$this->params['breadcrumbs'][] = $this->title;

// เดือนหนึ่งมีได้หลายแผ่นต่อหน่วย
$periodsByUnit = [];
foreach ($periods as $p) {
    $periodsByUnit[(int) $p->unit_id][] = $p;
}
$daysInMonth = (int) date('t', mktime(0, 0, 0, $month, 1, $year));
$dowNames = ['อา', 'จ', 'อ', 'พ', 'พฤ', 'ศ', 'ส'];

$prev = ['month' => $month === 1 ? 12 : $month - 1, 'year' => $month === 1 ? $year - 1 : $year];
$next = ['month' => $month === 12 ? 1 : $month + 1, 'year' => $month === 12 ? $year + 1 : $year];

$totalViolations = 0;
foreach ($violations as $v) {
    $totalViolations += $v['total'];
}
// หน่วยที่ยังไม่ส่ง = ไม่มีแผ่นเลย หรือมีแผ่นที่ยังเป็นร่างอยู่
$notSubmitted = 0;
foreach ($units as $unitId => $name) {
    $sheets = $periodsByUnit[$unitId] ?? [];
    if (empty($sheets)) {
        $notSubmitted++;
        continue;
    }
    foreach ($sheets as $p) {
        if ($p->status === Period::STATUS_DRAFT) {
            $notSubmitted++;
            break;
        }
    }
}
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-clipboard-data"></i> <?= Html::encode($this->title) ?>
    </h4>
    <div class="text-body-secondary small">
        ตรวจสอบตารางเวรของทุกหน่วยงานที่อยู่ในความรับผิดชอบ · <?= count($units) ?> หน่วย
    </div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/roster/menu', ['active' => 'overview', 'pendingCount' => $pendingCount]) ?>
<?php $this->endBlock(); ?>

<?php if (empty($units)): ?>
    <div class="alert alert-info border-0">
        <i class="bi bi-info-circle"></i> ไม่พบหน่วยงานที่มีเจ้าหน้าที่ขึ้นเวรในความรับผิดชอบของคุณ
    </div>
    <?php return; ?>
<?php endif; ?>

<!-- สรุปหัวหน้า -->
<div class="row g-3 mb-3">
    <div class="col-6 col-lg-3">
        <div class="card border shadow-sm h-100">
            <div class="card-body">
                <div class="text-body-secondary small">หน่วยที่ยังไม่ส่งตรวจ</div>
                <div class="fs-3 fw-semibold <?= $notSubmitted ? 'text-danger-emphasis' : 'text-success-emphasis' ?>">
                    <?= $notSubmitted ?><span class="fs-6 text-body-secondary">/<?= count($units) ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border shadow-sm h-100">
            <div class="card-body">
                <div class="text-body-secondary small">รอคุณดำเนินการ</div>
                <div class="fs-3 fw-semibold <?= $pendingCount ? 'text-warning-emphasis' : 'text-body-secondary' ?>">
                    <?= $pendingCount ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border shadow-sm h-100">
            <div class="card-body">
                <div class="text-body-secondary small">การละเมิดกฎรวม</div>
                <div class="fs-3 fw-semibold <?= $totalViolations ? 'text-warning-emphasis' : 'text-success-emphasis' ?>">
                    <?= number_format($totalViolations) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-lg-3">
        <div class="card border shadow-sm h-100">
            <div class="card-body">
                <div class="text-body-secondary small">เดือนที่ดู</div>
                <div class="d-flex align-items-center justify-content-between gap-1 mt-1">
                    <?= Html::a('<i class="bi bi-chevron-left"></i>', ['index', 'month' => $prev['month'], 'year' => $prev['year']], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                    <span class="fw-semibold small text-center">
                        <?= Html::encode(Period::monthNames()[$month]) ?> <?= $year + 543 ?>
                    </span>
                    <?= Html::a('<i class="bi bi-chevron-right"></i>', ['index', 'month' => $next['month'], 'year' => $next['year']], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- A · สถานะรอบเวร หน่วย × เดือน -->
<div class="card border shadow-sm mb-3">
    <div class="card-header bg-body-tertiary">
        <h6 class="mb-0"><i class="bi bi-calendar-check"></i> สถานะรอบเวรของแต่ละหน่วยงาน</h6>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead class="bg-body-tertiary">
                    <tr>
                        <th style="min-width:220px">หน่วยงาน</th>
                        <?php foreach ($statusMatrix['months'] as $m): ?>
                            <th class="text-center" style="min-width:110px">
                                <?= Html::encode(mb_substr(Period::monthNames()[$m['m']], 0, 3)) ?> <?= ($m['y'] + 543) % 100 ?>
                            </th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($units as $unitId => $name): ?>
                        <tr>
                            <td class="fw-semibold small"><?= Html::encode($name) ?></td>
                            <?php foreach ($statusMatrix['months'] as $m): ?>
                                <?php $sheets = $statusMatrix['matrix'][$unitId][$m['key']] ?? []; ?>
                                <td class="text-center">
                                    <?php if (empty($sheets)): ?>
                                        <span class="badge bg-body-tertiary text-body-secondary border">ยังไม่เปิดรอบ</span>
                                    <?php else: ?>
                                        <div class="d-flex flex-column gap-1 align-items-center">
                                            <?php foreach ($sheets as $p): ?>
                                                <?= Html::a(
                                                    Html::encode($p->title) . ' · ' . Html::encode($p->getStatusLabel()),
                                                    ['/roster/period/grid', 'id' => $p->id],
                                                    [
                                                        'class' => 'badge text-decoration-none text-wrap bg-' . $p->getStatusColor()
                                                            . '-subtle text-' . $p->getStatusColor() . '-emphasis',
                                                        'title' => $p->title,
                                                    ]
                                                ) ?>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-body-tertiary text-body-secondary small">
        <i class="bi bi-info-circle"></i> คลิกที่สถานะเพื่อเปิดตารางเวรของหน่วยนั้น
    </div>
</div>

<!-- B · Heatmap ความครบกำลังคน -->
<div class="card border shadow-sm mb-3">
    <div class="card-header bg-body-tertiary d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <h6 class="mb-0"><i class="bi bi-thermometer-half"></i> ความครบของกำลังคนรายวัน</h6>
        <div class="d-flex gap-2 align-items-center small text-body-secondary">
            <span class="badge bg-success-subtle text-success-emphasis">ครบ</span>
            <span class="badge bg-warning-subtle text-warning-emphasis">ขาด 1</span>
            <span class="badge bg-danger-subtle text-danger-emphasis">ขาด ≥2</span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle mb-0">
                <thead class="bg-body-tertiary">
                    <tr>
                        <th style="min-width:200px">หน่วยงาน</th>
                        <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                            <?php $dow = (int) date('w', mktime(0, 0, 0, $month, $d, $year)); ?>
                            <th class="text-center p-1 <?= in_array($dow, [0, 6], true) ? 'bg-secondary-subtle' : '' ?>"
                                style="min-width:30px">
                                <div class="small fw-bold"><?= $d ?></div>
                                <div class="small opacity-75"><?= $dowNames[$dow] ?></div>
                            </th>
                        <?php endfor; ?>
                    </tr>
                </thead>
                <tbody class="table-group-divider">
                    <?php foreach ($units as $unitId => $name): ?>
                        <?php $p = !empty($periodsByUnit[$unitId]); ?>
                        <tr>
                            <td class="small fw-semibold"><?= Html::encode($name) ?></td>
                            <?php for ($d = 1; $d <= $daysInMonth; $d++): ?>
                                <?php
                                $cell = $coverage[$unitId][$d] ?? null;
                                if (!$p) {
                                    $cls = 'bg-body-tertiary';
                                    $text = '';
                                    $title = 'ยังไม่เปิดรอบเวร';
                                } elseif (!$cell || empty($cell['configured'])) {
                                    $cls = 'bg-body-tertiary';
                                    $text = '?';
                                    $title = 'ยังไม่ได้ตั้งจำนวนคนที่ต้องการ';
                                } elseif ($cell['short'] === 0) {
                                    $cls = 'bg-success-subtle';
                                    $text = '';
                                    $title = 'ครบ';
                                } elseif ($cell['short'] === 1) {
                                    $cls = 'bg-warning-subtle text-warning-emphasis';
                                    $text = '1';
                                    $title = 'ขาด 1 คน';
                                } else {
                                    $cls = 'bg-danger-subtle text-danger-emphasis fw-bold';
                                    $text = (string) $cell['short'];
                                    $title = 'ขาด ' . $cell['short'] . ' คน';
                                }
                                ?>
                                <td class="text-center p-0 small <?= $cls ?>" title="<?= Html::encode($title) ?>"
                                    style="height:28px"><?= $text ?></td>
                            <?php endfor; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-body-tertiary text-body-secondary small">
        <i class="bi bi-info-circle"></i>
        ตัวเลขในช่อง = จำนวนคนที่ยังขาดของวันนั้น (รวมทุกผลัด) · ช่องเทา = ยังไม่เปิดรอบ หรือยังไม่ได้ตั้งจำนวนคนที่ต้องการ
    </div>
</div>

<!-- C+D · การละเมิดกฎ และความเป็นธรรม -->
<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card border shadow-sm h-100">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0"><i class="bi bi-shield-exclamation"></i> การละเมิดกฎการจัดเวร</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th>หน่วยงาน</th>
                                <th class="text-center" style="width:90px">รวม</th>
                                <th>กฎที่ถูกฝืนบ่อยสุด</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            <?php foreach ($units as $unitId => $name): ?>
                                <?php
                                $v = $violations[$unitId] ?? null;
                                if (empty($periodsByUnit[$unitId])) {
                                    continue;
                                }
                                $topRule = $v && $v['byRule'] ? array_key_first($v['byRule']) : null;
                                ?>
                                <tr>
                                    <td class="small"><?= Html::encode($name) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= ($v['total'] ?? 0) > 0 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success-emphasis' ?>">
                                            <?= (int) ($v['total'] ?? 0) ?>
                                        </span>
                                    </td>
                                    <td class="small text-body-secondary">
                                        <?php if ($topRule): ?>
                                            <?= Html::encode($topRule) ?>
                                            <span class="text-body-tertiary">(<?= $v['byRule'][$topRule] ?>)</span>
                                        <?php else: ?>
                                            —
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-body-tertiary text-body-secondary small">
                <i class="bi bi-info-circle"></i>
                คำนวณสดจากตารางปัจจุบันและกฎที่ตั้งไว้ตอนนี้ — ถ้าแก้กฎภายหลัง ตัวเลขย้อนหลังจะเปลี่ยนตาม
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card border shadow-sm h-100">
            <div class="card-header bg-body-tertiary">
                <h6 class="mb-0"><i class="bi bi-people"></i> ความเป็นธรรม (ส่วนต่างมากสุด–น้อยสุด)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="bg-body-tertiary">
                            <tr>
                                <th>หน่วยงาน</th>
                                <th class="text-center" style="width:120px">เวรดึก</th>
                                <th class="text-center" style="width:140px">เวรวันหยุด</th>
                                <th class="text-center" style="width:90px">แลกเวร</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider">
                            <?php foreach ($units as $unitId => $name): ?>
                                <?php
                                $f = $fairness[$unitId] ?? null;
                                if (!$f) {
                                    continue;
                                }
                                $swapTotal = array_sum($swapCounts[$unitId] ?? []);
                                ?>
                                <tr>
                                    <td class="small"><?= Html::encode($name) ?></td>
                                    <td class="text-center small">
                                        <?= $f['nightMin'] ?>–<?= $f['nightMax'] ?>
                                        <span class="badge <?= $f['nightSpread'] >= 4 ? 'bg-danger-subtle text-danger-emphasis' : 'bg-body-tertiary text-body-secondary' ?>">
                                            ต่าง <?= $f['nightSpread'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center small">
                                        <?= $f['offdayMin'] ?>–<?= $f['offdayMax'] ?>
                                        <span class="badge <?= $f['offdaySpread'] >= 4 ? 'bg-danger-subtle text-danger-emphasis' : 'bg-body-tertiary text-body-secondary' ?>">
                                            ต่าง <?= $f['offdaySpread'] ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-body-tertiary text-body-secondary"><?= $swapTotal ?></span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-body-tertiary text-body-secondary small">
                <i class="bi bi-info-circle"></i>
                ส่วนต่าง ≥ 4 เวร หมายถึงมีคนถูกจัดหนักกว่าคนอื่นมาก ควรตรวจก่อนอนุมัติ
            </div>
        </div>
    </div>
</div>

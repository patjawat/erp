<?php

use app\modules\kpi\models\KpiCycle;
use app\modules\kpi\models\KpiEntry;
use app\modules\kpi\models\KpiItem;
use app\modules\kpi\services\KpiService;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\Employees $employee */
/** @var KpiCycle[] $cycles */
/** @var KpiCycle|null $cycle */
/** @var int $fiscalYear */
/** @var int $currentFy */
/** @var KpiItem[] $items */
/** @var array<int, array<int, KpiEntry>> $entries */
/** @var bool $canManage */
/** @var bool $canRecord */

$this->title = 'KPI · ' . $employee->fullname;
$this->params['breadcrumbs'][] = ['label' => 'งานบุคลากร', 'url' => ['/hr/workforce/index', 'section' => 'kpi']];
$this->params['breadcrumbs'][] = ['label' => $employee->fullname, 'url' => ['/hr/employees/view', 'id' => $employee->id]];
$this->params['breadcrumbs'][] = 'KPI';

$this->registerCss('.tnum{font-variant-numeric:tabular-nums}.min-w-0{min-width:0}.kpi-tbl td,.kpi-tbl th{white-space:nowrap}.kpi-tbl .kpi-name-col{position:sticky;left:0;z-index:2;background:var(--bs-body-bg);min-width:13rem;max-width:22rem;white-space:normal}.kpi-tbl .mcol{min-width:3.25rem}.kpi-mcell{flex:1 0 5rem;min-width:5rem}.kpi-chevron{transition:transform .18s ease}[aria-expanded="true"] .kpi-chevron{transform:rotate(90deg)}');
// กัน Enter ในช่องกรอกรายเดือนไม่ให้ submit ฟอร์ม (บันทึกเฉพาะกดปุ่มบันทึก)
$this->registerJs('$(document).on("keydown", ".kpi-months input", function(e){ if(e.key==="Enter"){ e.preventDefault(); } });');

$statusTone = [
    KpiCycle::STATUS_DRAFT => 'warning',
    KpiCycle::STATUS_PENDING => 'info',
    KpiCycle::STATUS_ACTIVE => 'success',
    KpiCycle::STATUS_CLOSED => 'secondary',
];
$statusLabels = KpiCycle::statusLabels();

$years = array_map(static fn(KpiCycle $c): int => (int) $c->fiscal_year, $cycles);
$selectableYears = $years;
if (!in_array($currentFy, $selectableYears, true)) {
    array_unshift($selectableYears, $currentFy);
}
rsort($selectableYears);

$typeOptions = [KpiItem::TYPE_NUMERIC => 'ตัวเลข', KpiItem::TYPE_QUALITATIVE => 'คุณภาพ/ข้อความ'];
$freqOptions = [KpiItem::FREQ_MONTHLY => 'รายเดือน', KpiItem::FREQ_QUARTERLY => 'รายไตรมาส', KpiItem::FREQ_YEARLY => 'รายปี'];
$aggOptions = KpiItem::aggregationLabels();
$dirOptions = [KpiItem::DIR_ASC => 'มากขึ้น = ดี', KpiItem::DIR_DESC => 'น้อยลง = ดี'];

$fmt = static fn($n): string => rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
$isActive = $cycle && $cycle->status === KpiCycle::STATUS_ACTIVE;
$isHr = KpiService::isHrOrAdmin();
$canRecordNow = $canRecord && ($isActive || $isHr);
$csrf = Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken);

$levelTone = static function (?int $lv): string {
    if ($lv === null) {
        return 'secondary';
    }
    if ($lv >= 4) {
        return 'success';
    }
    if ($lv === 3) {
        return 'warning';
    }
    if ($lv >= 1) {
        return 'secondary';
    }
    return 'danger';
};

// ---- pre-pass: สรุปผล/ระดับ/คะแนน ต่อ KPI + คะแนนรวมของชุด ----
$rows = [];
$sumC = 0.0;
$sumWeightActive = 0.0;
foreach ($items as $it) {
    $ie = $entries[$it->id] ?? [];
    $summary = $it->summarize($ie);
    $level = $summary['level'];
    $scoreC = $level !== null ? $level * (float) $it->weight : null;
    $filled = 0;
    foreach ($ie as $e) {
        if ($e->value_num !== null || $e->value_text !== null) {
            $filled++;
        }
    }
    if ($it->status === KpiItem::STATUS_ACTIVE) {
        $sumWeightActive += (float) $it->weight;
        if ($scoreC !== null) {
            $sumC += $scoreC;
        }
    }
    $rows[$it->id] = compact('summary', 'level', 'scoreC', 'filled') + ['thr' => $it->levelThresholds()];
}
$maxC = 5 * $sumWeightActive;
$base100 = $maxC > 0 ? $sumC / $maxC * 100 : 0;
$component50 = $base100 * 0.5;
$weightOk = abs($sumWeightActive - 100) < 0.01;
?>

<?php foreach (['success' => 'success', 'error' => 'danger', 'info' => 'info', 'warning' => 'warning'] as $flash => $tone): ?>
    <?php if (Yii::$app->session->hasFlash($flash)): ?>
        <div class="alert alert-<?= $tone ?> alert-dismissible fade show"><?= Html::encode(Yii::$app->session->getFlash($flash)) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
<?php endforeach; ?>

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
    <div class="min-w-0">
        <h5 class="mb-1 fw-semibold text-truncate">ตัวชี้วัด KPI · <?= Html::encode($employee->fullname) ?></h5>
        <div class="text-body-secondary small">
            <?= Html::encode(strip_tags((string) $employee->positionName())) ?> · <?= Html::encode($employee->departmentName()) ?>
            <?php if ($cycle): ?> · <?= Html::encode(KpiService::fiscalRangeLabel($cycle->fiscal_year)) ?><?php endif; ?>
        </div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <?= Html::a('<i class="bi bi-arrow-left me-1"></i>ภาพรวม KPI', ['/hr/workforce/index', 'section' => 'kpi'], ['class' => 'btn btn-outline-secondary']) ?>
        <?php if ($canManage && !$cycle): ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i>สร้างชุด KPI ปี ' . $fiscalYear, ['create-cycle', 'emp_id' => $employee->id, 'fiscal_year' => $fiscalYear], ['class' => 'btn btn-primary', 'data-method' => 'post']) ?>
        <?php endif; ?>
        <?php if ($canManage && $cycle && $cycle->status === KpiCycle::STATUS_DRAFT): ?>
            <?= Html::a('<i class="bi bi-check2-circle me-1"></i>อนุมัติเริ่มบันทึก', ['approve', 'cycle_id' => $cycle->id], ['class' => 'btn btn-success', 'data-method' => 'post', 'data-confirm' => 'อนุมัติชุด KPI นี้เพื่อให้เจ้าหน้าที่เริ่มบันทึกผลงานได้ ยืนยันหรือไม่?']) ?>
        <?php endif; ?>
    </div>
</div>

<ul class="nav nav-pills gap-1 mb-3">
    <?php foreach ($selectableYears as $fy): ?>
        <li class="nav-item">
            <a class="nav-link py-1 px-3 <?= $fy === $fiscalYear ? 'active' : 'text-body' ?>" href="<?= Url::to(['view', 'emp_id' => $employee->id, 'fiscal_year' => $fy]) ?>">
                ปี <?= $fy ?><?= $fy === $currentFy ? ' <span class="badge bg-secondary-subtle text-secondary-emphasis ms-1">ปัจจุบัน</span>' : '' ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<?php if (!$cycle): ?>
    <div class="card bg-body border">
        <div class="card-body text-center py-5">
            <i class="bi bi-bullseye fs-1 text-body-secondary d-block mb-2"></i>
            <h6 class="fw-semibold">ยังไม่มีชุด KPI ของปีงบประมาณ <?= $fiscalYear ?></h6>
            <p class="text-body-secondary mb-0"><?= $canManage ? 'กด “สร้างชุด KPI” เพื่อดึงตัวชี้วัดตั้งต้นจาก JD ฉบับปัจจุบัน' : 'หัวหน้าหน่วยงานหรือ HR จะเป็นผู้สร้างชุด KPI ให้' ?></p>
        </div>
    </div>
<?php else: ?>

    <!-- ===== สกอร์บอร์ด: สถานะ + คะแนนองค์ประกอบที่ 1 (เห็นภายใน 3 วินาที) ===== -->
    <div class="card bg-body border mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <span class="badge rounded-pill bg-<?= $statusTone[$cycle->status] ?? 'secondary' ?>-subtle text-<?= $statusTone[$cycle->status] ?? 'secondary' ?>-emphasis"><?= Html::encode($statusLabels[$cycle->status] ?? $cycle->status) ?></span>
                <?php if ($cycle->jd_employee_id): ?><span class="text-body-secondary small"><i class="bi bi-link-45deg"></i> อ้างอิง JD Revision <?= (int) ($cycle->jdEmployee->revision_no ?? 0) ?></span><?php endif; ?>
                <?php if (!$isActive): ?>
                    <span class="badge bg-warning-subtle text-warning-emphasis"><i class="bi bi-info-circle me-1"></i><?= $isHr ? 'ยังไม่อนุมัติ — HR ลงข้อมูลได้' : 'ต้องอนุมัติก่อนบันทึกผล' ?></span>
                <?php endif; ?>
            </div>
            <div class="row g-3 text-center">
                <div class="col-6 col-lg-3">
                    <div class="rounded-3 py-3 h-100 border border-primary-subtle bg-primary-subtle">
                        <div class="small text-primary-emphasis">องค์ประกอบที่ 1</div>
                        <div class="fs-3 fw-bold text-primary-emphasis tnum"><?= $fmt($component50) ?><span class="fs-6"> / 50</span></div>
                        <div class="small text-body-secondary">ผลสัมฤทธิ์ของงาน</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="rounded-3 py-3 h-100 border border-info-subtle bg-info-subtle">
                        <div class="small text-info-emphasis">ผลสัมฤทธิ์ (ฐาน 100)</div>
                        <div class="fs-3 fw-bold text-info-emphasis tnum"><?= $fmt($base100) ?></div>
                        <div class="small text-body-secondary">คะแนนรวม ÷ เต็ม × 100</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <div class="rounded-3 py-3 h-100 border bg-body-tertiary">
                        <div class="small text-body-secondary">คะแนนรวม (ค)</div>
                        <div class="fs-3 fw-bold tnum"><?= $fmt($sumC) ?><span class="fs-6 text-body-secondary"> / <?= $fmt($maxC) ?></span></div>
                        <div class="small text-body-secondary">Σ(ระดับ × น้ำหนัก)</div>
                    </div>
                </div>
                <div class="col-6 col-lg-3">
                    <?php $wTone = $weightOk ? 'success' : 'danger'; ?>
                    <div class="rounded-3 py-3 h-100 border border-<?= $wTone ?>-subtle bg-<?= $wTone ?>-subtle">
                        <div class="small text-<?= $wTone ?>-emphasis">น้ำหนักรวม</div>
                        <div class="fs-3 fw-bold tnum text-<?= $wTone ?>-emphasis"><?= $fmt($sumWeightActive) ?>%</div>
                        <div class="small text-body-secondary"><?= $weightOk ? 'ครบ 100%' : 'ควรเท่ากับ 100%' ?></div>
                    </div>
                </div>
            </div>
            <p class="text-body-secondary small mb-0 mt-3"><i class="bi bi-info-circle me-1"></i>องค์ประกอบที่ 2 (สมรรถนะ) อีก 50 คะแนน จะเพิ่มในเฟสถัดไป รวมเป็น 100 คะแนน</p>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center gap-2 mb-2 flex-wrap">
        <h6 class="fw-semibold mb-0">ตัวชี้วัด <span class="text-body-secondary fw-normal">(<?= count($items) ?>)</span></h6>
        <?php if ($canManage): ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i>เพิ่ม KPI', ['add-item', 'cycle_id' => $cycle->id], ['class' => 'btn btn-sm btn-outline-primary', 'data-method' => 'post']) ?>
        <?php endif; ?>
    </div>

    <?php if (!$items): ?>
        <div class="card bg-body border"><div class="card-body text-center text-body-secondary py-4">ยังไม่มี KPI ในชุดนี้<?= $canManage ? ' — กด “เพิ่ม KPI”' : '' ?></div></div>
    <?php else: ?>
        <!-- ===== ตารางแสดงผล (อ่านอย่างเดียว) — ภาพรวมทุก KPI ===== -->
        <div class="card bg-body border">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0 small kpi-tbl">
                    <thead>
                        <tr class="text-body-secondary">
                            <th class="kpi-name-col">ชื่อ KPI</th>
                            <?php foreach (KpiService::FISCAL_MONTHS as $cm): ?><th class="text-center mcol"><?= KpiService::MONTH_LABELS_TH[$cm] ?></th><?php endforeach; ?>
                            <th class="text-center">สรุป</th>
                            <th class="text-center">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider align-middle">
                    <?php $no = 0; foreach ($items as $it): ?>
                        <?php
                        $removed = $it->status === KpiItem::STATUS_REMOVED;
                        $no++;
                        $r = $rows[$it->id];
                        $level = $r['level'];
                        $scoreC = $r['scoreC'];
                        $thr = $r['thr'];
                        $tone = $levelTone($level);
                        $isNum = $it->value_type === KpiItem::TYPE_NUMERIC;
                        ?>
                        <tr class="<?= $removed ? 'opacity-75' : '' ?>">
                            <td class="kpi-name-col">
                                <?php if ($canRecordNow && !$removed): ?>
                                    <button type="button" class="btn btn-sm btn-link text-body text-decoration-none p-0 text-start" data-bs-toggle="collapse" data-bs-target="#rec-<?= $it->id ?>" aria-expanded="false" aria-controls="rec-<?= $it->id ?>" title="คลิกเพื่อกรอก/แก้ไขผลรายเดือน">
                                        <i class="bi bi-chevron-right kpi-chevron me-1"></i><span class="badge bg-primary-subtle text-primary-emphasis">KPI <?= $no ?></span> <span class="fw-semibold"><?= Html::encode($it->indicator) ?></span><?php if ($it->source_type === KpiItem::SOURCE_JD): ?> <span class="badge bg-secondary-subtle text-secondary-emphasis fw-normal">JD</span><?php endif; ?>
                                    </button>
                                <?php else: ?>
                                    <span class="badge bg-primary-subtle text-primary-emphasis">KPI <?= $no ?></span>
                                    <span class="fw-semibold"><?= Html::encode($it->indicator) ?></span>
                                    <?php if ($it->source_type === KpiItem::SOURCE_JD): ?><span class="badge bg-secondary-subtle text-secondary-emphasis fw-normal">JD</span><?php endif; ?>
                                    <?php if ($removed): ?><span class="badge bg-secondary-subtle text-secondary-emphasis fw-normal">ยกเลิก</span><?php endif; ?>
                                <?php endif; ?>
                                <div class="text-body-secondary fs-13 mt-1">
                                    <i class="bi bi-bullseye me-1"></i>เป้า <?= Html::encode($it->target_text ?: ($it->target_value !== null ? $fmt($it->target_value) . ($it->unit ? ' ' . $it->unit : '') : '—')) ?>
                                    <span class="mx-1">·</span>น้ำหนัก <?= $it->weight > 0 ? $fmt($it->weight) . '%' : '—' ?>
                                </div>
                            </td>
                            <?php for ($fi = 1; $fi <= 12; $fi++): ?>
                                <?php $e = $entries[$it->id][$fi] ?? null; $val = $e ? ($e->value_num !== null ? $fmt($e->value_num) : ($e->value_text ?: '')) : ''; ?>
                                <td class="text-center mcol"><span class="tnum" title="<?= Html::encode($val) ?>"><?= $val !== '' ? Html::encode($val) : '<span class="text-body-secondary">·</span>' ?></span></td>
                            <?php endfor; ?>
                            <td class="text-center text-nowrap">
                                <?php if (!$isNum): ?>
                                    <span class="badge bg-info-subtle text-info-emphasis">เชิงคุณภาพ</span>
                                <?php elseif ($thr === []): ?>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis fw-normal">ไม่มีเกณฑ์</span>
                                <?php elseif ($level === null): ?>
                                    <span class="text-body-secondary">·</span>
                                <?php else: ?>
                                    <span class="badge bg-<?= $tone ?>-subtle text-<?= $tone ?>-emphasis">ระดับ <?= $level ?>/5</span>
                                    <span class="tnum ms-1">(<?= $fmt($scoreC) ?>)</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($canManage): ?>
                                    <div class="dropdown d-inline-block">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">จัดการ</button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><a class="dropdown-item open-modal" href="<?= Url::to(['edit-item', 'id' => $it->id]) ?>" data-size="modal-lg"><i class="bi bi-pencil-square me-2"></i>แก้ไข</a></li>
                                            <?php if (!$removed): ?>
                                                <li><?= Html::a('<i class="bi bi-slash-circle me-2"></i>ยกเลิก', ['remove-item', 'id' => $it->id], ['class' => 'dropdown-item', 'data-method' => 'post', 'data-confirm' => 'ยกเลิก KPI นี้? (เก็บผลงานเดิมไว้ แต่ไม่คิดคะแนน)']) ?></li>
                                            <?php endif; ?>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><?= Html::a('<i class="bi bi-trash me-2"></i>ลบ', ['delete-item', 'id' => $it->id], ['class' => 'dropdown-item text-danger', 'data-method' => 'post', 'data-confirm' => 'ลบ KPI นี้ถาวร? ผลงานรายเดือนและคะแนนจะถูกลบทั้งหมด กู้คืนไม่ได้']) ?></li>
                                        </ul>
                                    </div>
                                <?php elseif ($removed): ?>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis">ยกเลิก</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php if ($canRecordNow && !$removed): ?>
                            <tr class="kpi-rec-detail">
                                <td colspan="15" class="p-0 border-0">
                                    <div class="collapse" id="rec-<?= $it->id ?>">
                                        <div class="bg-primary-subtle border-top border-bottom border-primary-subtle p-3">
                                            <form method="post" action="<?= Url::to(['save-entries', 'kpi_item_id' => $it->id]) ?>" class="kpi-months">
                                                <?= $csrf ?>
                                                <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                                                    <span class="small fw-semibold"><i class="bi bi-calendar3 me-1"></i>กรอกผลงานรายเดือน (ต.ค.–ก.ย.) · <?= Html::encode($it->indicator) ?></span>
                                                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-save me-1"></i>บันทึก</button>
                                                </div>
                                                <div class="overflow-x-auto"><div class="d-flex gap-2 pb-1">
                                                    <?php foreach (KpiService::FISCAL_MONTHS as $idx => $cm): ?>
                                                        <?php $fi = $idx + 1; $e = $entries[$it->id][$fi] ?? null; ?>
                                                        <div class="kpi-mcell">
                                                            <label class="form-label small text-body-secondary mb-1 d-block text-center"><?= KpiService::MONTH_LABELS_TH[$cm] ?></label>
                                                            <?php if ($isNum): ?>
                                                                <input type="number" step="any" name="m[<?= $fi ?>]" value="<?= $e && $e->value_num !== null ? Html::encode($fmt($e->value_num)) : '' ?>" class="form-control form-control-sm text-center tnum">
                                                            <?php else: ?>
                                                                <input type="text" name="mt[<?= $fi ?>]" value="<?= $e ? Html::encode($e->value_text) : '' ?>" class="form-control form-control-sm text-center">
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div></div>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if ($canRecordNow): ?>
            <p class="text-body-secondary small mt-2 mb-0"><i class="bi bi-info-circle me-1"></i>คลิกที่ชื่อตัวชี้วัดเพื่อกรอก/แก้ไขผลงานรายเดือน</p>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>

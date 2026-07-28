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

$this->registerCss('.tnum{font-variant-numeric:tabular-nums}.min-w-0{min-width:0}.kpi-months .form-control{text-align:center}');

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

    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="fw-semibold mb-0">ตัวชี้วัด <span class="text-body-secondary fw-normal">(<?= count($items) ?>)</span></h6>
        <?php if ($canManage): ?>
            <?= Html::a('<i class="bi bi-plus-lg me-1"></i>เพิ่ม KPI', ['add-item', 'cycle_id' => $cycle->id], ['class' => 'btn btn-sm btn-outline-primary', 'data-method' => 'post']) ?>
        <?php endif; ?>
    </div>

    <?php if (!$items): ?>
        <div class="card bg-body border"><div class="card-body text-center text-body-secondary py-4">ยังไม่มี KPI ในชุดนี้<?= $canManage ? ' — กด “เพิ่ม KPI”' : '' ?></div></div>
    <?php else: ?>
        <?php $no = 0; foreach ($items as $it): ?>
            <?php
            $removed = $it->status === KpiItem::STATUS_REMOVED;
            $no++;
            $r = $rows[$it->id];
            $level = $r['level'];
            $scoreC = $r['scoreC'];
            $summary = $r['summary'];
            $thr = $r['thr'];
            $filled = $r['filled'];
            $tone = $levelTone($level);
            ?>
            <div class="card bg-body border mb-3 <?= $removed ? 'opacity-75' : '' ?>">
                <div class="card-body">
                    <!-- แถวที่ 1: ตัวชี้วัด + ผลลัพธ์ (ระดับ/คะแนน) -->
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                        <div class="min-w-0 flex-grow-1">
                            <div class="fw-semibold">
                                <span class="badge bg-primary-subtle text-primary-emphasis me-1">KPI <?= $no ?></span><?= Html::encode($it->indicator) ?>
                                <?php if ($it->source_type === KpiItem::SOURCE_JD): ?><span class="badge bg-secondary-subtle text-secondary-emphasis ms-1 fw-normal">จาก JD</span><?php endif; ?>
                                <?php if ($removed): ?><span class="badge bg-secondary-subtle text-secondary-emphasis ms-1 fw-normal">ยกเลิกกลางปี</span><?php endif; ?>
                            </div>
                            <div class="small text-body-secondary mt-1 d-flex flex-wrap gap-3">
                                <span><i class="bi bi-bullseye me-1"></i>เป้า <span class="text-body"><?= Html::encode($it->target_text ?: ($it->target_value !== null ? $fmt($it->target_value) . ($it->unit ? ' ' . $it->unit : '') : '—')) ?></span></span>
                                <span><i class="bi bi-percent me-1"></i>น้ำหนัก <span class="text-body tnum"><?= $it->weight > 0 ? $fmt($it->weight) . '%' : '—' ?></span></span>
                                <span><i class="bi bi-sigma me-1"></i><?= Html::encode($aggOptions[$it->aggregation] ?? $it->aggregation) ?> · <i class="bi bi-arrow-<?= $it->direction === KpiItem::DIR_DESC ? 'down' : 'up' ?>-short"></i><?= $it->direction === KpiItem::DIR_DESC ? 'น้อยดี' : 'มากดี' ?></span>
                                <span><i class="bi bi-calendar-check me-1"></i>บันทึก <span class="tnum"><?= $filled ?>/12</span></span>
                            </div>
                        </div>
                        <?php $resultTinted = ($it->value_type === KpiItem::TYPE_NUMERIC && $level !== null); ?>
                        <div class="text-end rounded-3 px-3 py-2 <?= $resultTinted ? 'bg-' . $tone . '-subtle border border-' . $tone . '-subtle' : '' ?>">
                            <?php if ($it->value_type === KpiItem::TYPE_NUMERIC): ?>
                                <div class="small text-body-secondary">ผลงานสรุป</div>
                                <div class="fs-5 fw-bold tnum <?= $summary['value'] === null ? 'text-body-secondary' : ($resultTinted ? 'text-' . $tone . '-emphasis' : 'text-body') ?>"><?= $summary['value'] === null ? '—' : $fmt($summary['value']) . ($it->unit ? ' ' . Html::encode($it->unit) : '') ?></div>
                                <?php if ($resultTinted): ?>
                                    <div class="small fw-semibold text-<?= $tone ?>-emphasis">ระดับ <?= $level ?>/5 · คะแนน <span class="tnum"><?= $fmt($scoreC) ?></span></div>
                                <?php elseif ($thr === []): ?>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis fw-normal">ยังไม่ตั้งเกณฑ์ระดับ</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-info-subtle text-info-emphasis">เชิงคุณภาพ</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- แถบระดับ 1–5 (ไฮไลต์ระดับที่ได้) -->
                    <?php if ($thr !== []): ?>
                        <div class="row row-cols-5 g-1 mt-3">
                            <?php foreach ([1, 2, 3, 4, 5] as $l): ?>
                                <?php $on = ($level !== null && $l === $level); ?>
                                <div class="col">
                                    <div class="text-center rounded-2 py-1 px-1 border <?= $on ? 'border-primary bg-primary-subtle text-primary-emphasis fw-semibold' : 'bg-body-tertiary text-body-secondary' ?>">
                                        <div class="small">ระดับ <?= $l ?></div>
                                        <div class="small tnum"><?= isset($thr[$l]) ? $fmt($thr[$l]) : '–' ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($canManage && !$removed): ?>
                        <div class="mt-2 d-flex gap-2">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#edit-<?= $it->id ?>"><i class="bi bi-sliders me-1"></i>ตั้งค่าเกณฑ์ / น้ำหนัก</button>
                            <?= Html::a('<i class="bi bi-trash me-1"></i>ยกเลิก', ['remove-item', 'id' => $it->id], ['class' => 'btn btn-sm btn-outline-danger', 'data-method' => 'post', 'data-confirm' => 'ยกเลิก KPI นี้? (ผลงานเดิมจะยังถูกเก็บไว้)']) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($canManage && !$removed): ?>
                    <!-- ฟอร์มตั้งค่า (ซ่อน) -->
                    <div class="collapse" id="edit-<?= $it->id ?>">
                        <form method="post" action="<?= Url::to(['update-item', 'id' => $it->id]) ?>" class="card-body border-top bg-body-tertiary">
                            <?= $csrf ?>
                            <div class="row g-3">
                                <div class="col-12 col-lg-6"><label class="form-label small fw-semibold mb-1">ชื่อตัวชี้วัด</label><input type="text" name="indicator" value="<?= Html::encode($it->indicator) ?>" class="form-control form-control-sm"></div>
                                <div class="col-6 col-lg-3"><label class="form-label small fw-semibold mb-1">เป้าหมาย (ข้อความ)</label><input type="text" name="target_text" value="<?= Html::encode($it->target_text) ?>" class="form-control form-control-sm" placeholder="เช่น ≥90%"></div>
                                <div class="col-3 col-lg-2"><label class="form-label small fw-semibold mb-1">เป้า (ตัวเลข)</label><input type="number" step="any" name="target_value" value="<?= $it->target_value !== null ? Html::encode($fmt($it->target_value)) : '' ?>" class="form-control form-control-sm"></div>
                                <div class="col-3 col-lg-1"><label class="form-label small fw-semibold mb-1">หน่วย</label><input type="text" name="unit" value="<?= Html::encode($it->unit) ?>" class="form-control form-control-sm"></div>

                                <div class="col-12">
                                    <label class="form-label small fw-semibold mb-1">คะแนนตามระดับค่าเป้าหมาย <span class="text-body-secondary fw-normal">— ผลงานถึงเกณฑ์ระดับใด ได้คะแนนระดับนั้น</span></label>
                                    <div class="row row-cols-5 g-1">
                                        <?php foreach ([1, 2, 3, 4, 5] as $l): ?>
                                            <div class="col">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">ร.<?= $l ?></span>
                                                    <input type="number" step="any" name="level<?= $l ?>" value="<?= $it->{'level' . $l} !== null ? Html::encode($fmt($it->{'level' . $l})) : '' ?>" class="form-control">
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <div class="col-6 col-lg-3">
                                    <label class="form-label small fw-semibold mb-1">ชนิดผล</label>
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <?php foreach ($typeOptions as $val => $lbl): ?>
                                            <input type="radio" class="btn-check" name="value_type" id="vt-<?= $it->id ?>-<?= $val ?>" value="<?= $val ?>" autocomplete="off" <?= $it->value_type === $val ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-secondary" for="vt-<?= $it->id ?>-<?= $val ?>"><?= $lbl ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-3">
                                    <label class="form-label small fw-semibold mb-1">ทิศทาง</label>
                                    <div class="btn-group btn-group-sm w-100" role="group">
                                        <?php foreach ($dirOptions as $val => $lbl): ?>
                                            <input type="radio" class="btn-check" name="direction" id="dir-<?= $it->id ?>-<?= $val ?>" value="<?= $val ?>" autocomplete="off" <?= $it->direction === $val ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-secondary" for="dir-<?= $it->id ?>-<?= $val ?>"><?= $lbl ?></label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-6 col-lg-2"><label class="form-label small fw-semibold mb-1">วิธีสรุป</label><?= Html::dropDownList('aggregation', $it->aggregation, $aggOptions, ['class' => 'form-select form-select-sm']) ?></div>
                                <div class="col-3 col-lg-2"><label class="form-label small fw-semibold mb-1">ความถี่</label><?= Html::dropDownList('frequency', $it->frequency, $freqOptions, ['class' => 'form-select form-select-sm']) ?></div>
                                <div class="col-3 col-lg-2"><label class="form-label small fw-semibold mb-1">น้ำหนัก %</label><input type="number" step="any" name="weight" value="<?= $fmt($it->weight) ?>" class="form-control form-control-sm text-end tnum"></div>

                                <div class="col-12 text-end"><button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-save me-1"></i>บันทึกการตั้งค่า</button></div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>

                <!-- ผลงานรายเดือน ต.ค.–ก.ย. -->
                <div class="card-body border-top">
                    <?php if ($canRecordNow && !$removed): ?>
                        <form method="post" action="<?= Url::to(['save-entries', 'kpi_item_id' => $it->id]) ?>" class="kpi-months">
                            <?= $csrf ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="small fw-semibold"><i class="bi bi-calendar3 me-1"></i>ผลงานรายเดือน (ต.ค.–ก.ย.)</span>
                                <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-save me-1"></i>บันทึกผลงาน</button>
                            </div>
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-2">
                                <?php foreach (KpiService::FISCAL_MONTHS as $idx => $cm): ?>
                                    <?php $fi = $idx + 1; $e = $entries[$it->id][$fi] ?? null; ?>
                                    <div class="col">
                                        <label class="form-label small text-body-secondary mb-1"><?= KpiService::MONTH_LABELS_TH[$cm] ?></label>
                                        <?php if ($it->value_type === KpiItem::TYPE_NUMERIC): ?>
                                            <input type="number" step="any" name="m[<?= $fi ?>]" value="<?= $e && $e->value_num !== null ? Html::encode($fmt($e->value_num)) : '' ?>" class="form-control form-control-sm tnum">
                                        <?php else: ?>
                                            <input type="text" name="mt[<?= $fi ?>]" value="<?= $e ? Html::encode($e->value_text) : '' ?>" class="form-control form-control-sm">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="small fw-semibold mb-2 text-body-secondary"><i class="bi bi-calendar3 me-1"></i>ผลงานรายเดือน (ต.ค.–ก.ย.)</div>
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-2">
                            <?php foreach (KpiService::FISCAL_MONTHS as $idx => $cm): ?>
                                <?php $fi = $idx + 1; $e = $entries[$it->id][$fi] ?? null; $val = $e ? ($e->value_num !== null ? $fmt($e->value_num) : ($e->value_text ?: '')) : ''; ?>
                                <div class="col">
                                    <div class="border rounded-2 px-2 py-1 h-100">
                                        <div class="small text-body-secondary"><?= KpiService::MONTH_LABELS_TH[$cm] ?></div>
                                        <div class="fw-semibold text-truncate tnum" title="<?= Html::encode($val) ?>"><?= $val !== '' ? Html::encode($val) : '<span class="text-body-secondary">·</span>' ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php endif; ?>

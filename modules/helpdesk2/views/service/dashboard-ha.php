<?php

use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $title */
/** @var string $icon */
/** @var string $active */
/** @var array $dashboardParams */
/** @var string $haContext 'medical' = เครื่องมือแพทย์ / 'utility' = ระบบสาธารณูปโภค */
/** @var string $drillRoute route ปลายทางของ offcanvas drill-down (AJAX) */
/** @var string $reportRoute route หน้าพิมพ์รายงาน (สัมพัทธ์กับ controller นี้) */

$haContext = $haContext ?? 'medical';
$drillRoute = $drillRoute ?? '/helpdesk/general/drilldown';
$reportRoute = $reportRoute ?? 'report';

$p = $dashboardParams;
$kpi = $p['kpi'];
$filters = $p['filters'];
$opts = $p['filterOptions'];
$range = $p['dateRange'];
$ha = $p['haMetrics'] ?? [];
$targets = $ha['targets'] ?? ['ready' => 95, 'calibration' => 100, 'pm' => 90, 'sla' => 90];
$slaTarget = (int) $targets['sla'];

$isMedical = $haContext === 'medical';
$stdCaption = $isMedical
    ? 'มาตรฐาน HA ฉบับ 6 หมวด II-3 · เครื่องมือและอุปกรณ์การแพทย์ (ความพร้อมใช้ · สอบเทียบ · บำรุงรักษาเชิงป้องกัน)'
    : 'มาตรฐาน HA ฉบับ 6 หมวด II-3 · ระบบสาธารณูปโภคและสิ่งแวดล้อมกายภาพ (ความต่อเนื่อง · ระยะเวลาซ่อม · บำรุงรักษาเชิงป้องกัน)';

$this->title = 'แดชบอร์ด HA — ' . $title;
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = $title;
$this->params['breadcrumbs'][] = 'แดชบอร์ด HA';

// ---- ตัวช่วยจัดรูปแบบ ----
$fmtDuration = static function (?int $s): string {
    if ($s === null) {
        return '—';
    }
    if ($s < 3600) {
        return round($s / 60) . ' นาที';
    }
    if ($s < 86400) {
        return round($s / 3600, 1) . ' ชม.';
    }
    return round($s / 86400, 1) . ' วัน';
};
$nf = static fn($n) => number_format((int) $n);
$pctColor = static function (?float $v, int $target): string {
    if ($v === null) {
        return 'secondary';
    }
    if ($v >= $target) {
        return 'success';
    }
    return $v >= $target - 10 ? 'warning' : 'danger';
};
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <?= $icon ?> <?= Html::encode($title) ?> — แดชบอร์ด HA
    </h4>
    <span class="small text-muted"><?= Html::encode($stdCaption) ?></span>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/helpdesk2/menu', ['active' => $active]) ?>
<?php $this->endBlock(); ?>

<!-- ===== แถบเลือกมุมมองผู้ใช้ (กรองการแสดงผล — ถ้า JS ไม่ทำงานจะเห็นครบทุกส่วน) ===== -->
<div class="d-flex flex-wrap align-items-center gap-2 mb-3">
    <span class="small text-muted me-1"><i class="bi bi-people me-1"></i>มุมมอง:</span>
    <div class="btn-group btn-group-sm" role="group" aria-label="เลือกมุมมองผู้ใช้">
        <button type="button" class="btn btn-primary" data-ha-pill="">ทั้งหมด</button>
        <button type="button" class="btn btn-outline-primary" data-ha-pill="tech"><i class="bi bi-tools me-1"></i>ช่าง</button>
        <button type="button" class="btn btn-outline-primary" data-ha-pill="exec"><i class="bi bi-briefcase me-1"></i>ผู้บริหาร</button>
        <button type="button" class="btn btn-outline-primary" data-ha-pill="quality"><i class="bi bi-patch-check me-1"></i>คุณภาพ (HA)</button>
    </div>
</div>
<div id="ha-view-note" class="alert border-0 shadow-sm small py-2 px-3 mb-3 d-none" role="status"></div>

<!-- ===== แถบกรอง ===== -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="get" class="row g-2 align-items-end" id="ha-filter">
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small text-muted mb-1">ปีงบประมาณ</label>
                <select name="year" class="form-select form-select-sm">
                    <?php foreach ($opts['years'] as $y): ?>
                        <option value="<?= $y ?>" <?= ((string) $filters['year'] === (string) $y || ($filters['year'] === null && (int) $y === (int) \app\components\AppHelper::YearBudget())) ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small text-muted mb-1">ประเภทอุปกรณ์</label>
                <select name="device_type_id" class="form-select form-select-sm">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($opts['deviceTypes'] as $code => $label): ?>
                        <option value="<?= Html::encode($code) ?>" <?= (string) $filters['device_type_id'] === (string) $code ? 'selected' : '' ?>><?= Html::encode($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small text-muted mb-1">ความเร่งด่วน</label>
                <select name="urgency" class="form-select form-select-sm">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($opts['urgencies'] as $code => $label): ?>
                        <option value="<?= $code ?>" <?= (string) $filters['urgency'] === (string) $code ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-3 col-xl-2">
                <label class="form-label small text-muted mb-1">ช่างผู้รับผิดชอบ</label>
                <select name="technician" class="form-select form-select-sm">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($opts['technicians'] as $id => $name): ?>
                        <option value="<?= $id ?>" <?= (string) $filters['technician'] === (string) $id ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-6 col-xl-2">
                <label class="form-label small text-muted mb-1">หน่วยงานผู้แจ้ง</label>
                <select name="department" class="form-select form-select-sm">
                    <option value="">ทั้งหมด</option>
                    <?php foreach ($opts['departments'] as $id => $name): ?>
                        <option value="<?= $id ?>" <?= (string) $filters['department'] === (string) $id ? 'selected' : '' ?>><?= Html::encode($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-6 col-xl-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill"><i class="bi bi-funnel me-1"></i>กรอง</button>
                <?= Html::a('<i class="bi bi-arrow-counterclockwise"></i>', ['dashboard-v2'], ['class' => 'btn btn-sm btn-outline-secondary', 'title' => 'ล้างตัวกรอง']) ?>
            </div>
            <div class="col-12 d-flex flex-wrap align-items-center justify-content-between gap-2">
                <span class="small text-muted"><i class="bi bi-calendar-range me-1"></i>ช่วงข้อมูล: <?= Html::encode($range['start']) ?> ถึง <?= Html::encode($range['end']) ?></span>
                <?php
                $reportFilters = array_filter([
                    'year' => $filters['year'],
                    'device_type_id' => $filters['device_type_id'],
                    'urgency' => $filters['urgency'],
                    'technician' => $filters['technician'],
                    'department' => $filters['department'],
                ], static fn($v) => $v !== null && $v !== '');
                ?>
                <?= Html::a('<i class="bi bi-printer me-1"></i>พิมพ์รายงาน HA', array_merge([$reportRoute], $reportFilters), [
                    'class' => 'btn btn-sm btn-outline-primary',
                    'target' => '_blank',
                    'encode' => false,
                ]) ?>
            </div>
        </form>
    </div>
</div>

<!-- ===== HA II-3: ความพร้อมใช้ / สอบเทียบ / บำรุงรักษาเชิงป้องกัน ===== -->
<?php
$cal = $ha['calibration'] ?? null;
$pm = $ha['pm'] ?? null;
$rd = $ha['readiness'] ?? null;
?>
<div class="row g-3 mb-3">
    <?php if ($rd !== null): ?>
        <?php $rdColor = $pctColor($rd['ready_pct'], (int) $targets['ready']); ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-<?= $rdColor ?>-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small text-uppercase text-secondary fw-semibold mb-1"><i class="bi bi-check2-circle me-1"></i>เครื่องมือพร้อมใช้</div>
                            <div class="d-flex align-items-end gap-2">
                                <span class="fw-bold fs-2 lh-1 text-<?= $rdColor ?>"><?= $rd['ready_pct'] === null ? '—' : $rd['ready_pct'] . '%' ?></span>
                                <span class="small text-muted mb-1">เป้า ≥ <?= (int) $targets['ready'] ?>%</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-heart-pulse text-<?= $rdColor ?> opacity-25 fs-2"></i>
                    </div>
                    <div class="small text-muted mt-2">พร้อมใช้ <?= $nf($rd['ready']) ?> / <?= $nf($rd['total']) ?> รายการ</div>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning-subtle rounded-pill">รอซ่อม <?= $nf($rd['repairing']) ?></span>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill">ชำรุด <?= $nf($rd['damaged']) ?></span>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill">รอจำหน่าย <?= $nf($rd['wait_dispose']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($cal !== null): $calColor = $pctColor($cal['compliance_pct'], (int) $targets['calibration']); ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-<?= $calColor ?>-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small text-uppercase text-secondary fw-semibold mb-1"><i class="bi bi-rulers me-1"></i>สอบเทียบตามแผน</div>
                            <div class="d-flex align-items-end gap-2">
                                <span class="fw-bold fs-2 lh-1 text-<?= $calColor ?>"><?= $cal['compliance_pct'] === null ? '—' : $cal['compliance_pct'] . '%' ?></span>
                                <span class="small text-muted mb-1">เป้า <?= (int) $targets['calibration'] ?>%</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-ruler-combined text-<?= $calColor ?> opacity-25 fs-2"></i>
                    </div>
                    <div class="small text-muted mt-2">ทำจริงในช่วง <?= $nf($cal['performed'] ?? 0) ?> รายการ · ตามแผน <?= $nf($cal['planned']) ?> (ทำแล้ว <?= $nf($cal['done']) ?>) · <span class="text-danger">เกินกำหนด <?= $nf($cal['overdue']) ?></span></div>
                    <div class="d-flex flex-wrap gap-1 mt-2">
                        <span class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill">ผ่าน <?= $nf($cal['pass'] ?? 0) ?></span>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill">ไม่ผ่าน <?= $nf($cal['fail'] ?? 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($pm !== null): $pmColor = $pctColor($pm['compliance_pct'], (int) $targets['pm']); ?>
        <div class="col-12 col-md-6 col-xl-4">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-<?= $pmColor ?>-subtle">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="small text-uppercase text-secondary fw-semibold mb-1"><i class="bi bi-clipboard2-pulse me-1"></i>บำรุงรักษาเชิงป้องกัน (PM)</div>
                            <div class="d-flex align-items-end gap-2">
                                <span class="fw-bold fs-2 lh-1 text-<?= $pmColor ?>"><?= $pm['compliance_pct'] === null ? '—' : $pm['compliance_pct'] . '%' ?></span>
                                <span class="small text-muted mb-1">เป้า ≥ <?= (int) $targets['pm'] ?>%</span>
                            </div>
                        </div>
                        <i class="fa-solid fa-screwdriver-wrench text-<?= $pmColor ?> opacity-25 fs-2"></i>
                    </div>
                    <div class="small text-muted mt-2">ทำจริงในช่วง <?= $nf($pm['performed'] ?? 0) ?> รายการ · ตามแผน <?= $nf($pm['planned']) ?> (ทำแล้ว <?= $nf($pm['done']) ?>) · <span class="text-danger">เกินกำหนด <?= $nf($pm['overdue']) ?></span></div>
                    <div class="progress mt-2" style="height:6px;">
                        <div class="progress-bar bg-<?= $pmColor ?>" role="progressbar" style="width: <?= $pm['compliance_pct'] === null ? 0 : (int) $pm['compliance_pct'] ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($rd === null && $cal === null && $pm === null): ?>
        <div class="col-12">
            <div class="alert alert-light border shadow-sm mb-0 small">
                <i class="bi bi-info-circle me-1"></i>ยังไม่มีข้อมูลความพร้อมใช้/สอบเทียบ/บำรุงรักษาในช่วงเวลาที่เลือก — ตัวเลขจะปรากฏเมื่อมีการบันทึกงานสอบเทียบและบำรุงรักษาในทะเบียนครุภัณฑ์
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- ===== คำเตือนคุณภาพข้อมูลเชิงเวลา ===== -->
<div class="alert alert-warning border-0 shadow-sm d-flex gap-2 py-2 px-3 small mb-3" role="alert">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        ตัวชี้วัดเชิงเวลา (MTTA / MTTR / %SLA) แสดงด้วย <strong>ค่ามัธยฐาน (median)</strong> เพื่อลดผลจากงานค้างนาน
        — ความแม่นยำขึ้นกับการบันทึกรับเรื่อง/ปิดงานตรงเวลาจริง ส่วน %สอบเทียบ/PM นับจากงานที่กำหนดแผนไว้ในช่วงปีงบ
    </div>
</div>

<!-- ===== KPI 6 ใบ (คลิกดูรายละเอียด) ===== -->
<div class="row g-3 mb-3">
    <?php
    $slaColor = ($kpi['sla_pct'] === null ? 'secondary' : ($kpi['sla_pct'] >= $slaTarget ? 'success' : 'danger'));
    $cards = [
        [
            'scope' => 'total', 'label' => 'งานซ่อมทั้งหมด', 'icon' => 'fa-solid fa-list-check',
            'color' => 'secondary', 'value' => $nf($kpi['total']), 'unit' => 'รายการ',
            'sub' => 'ปิดแล้ว ' . $nf($kpi['closed_total']) . ' รายการ',
        ],
        [
            'scope' => 'open', 'label' => 'เปิดค้าง', 'icon' => 'fa-solid fa-inbox',
            'color' => 'warning', 'value' => $nf($kpi['open']), 'unit' => 'รายการ',
            'sub' => 'รอรับเรื่อง ' . $nf($kpi['pending']) . ' · กำลังทำ ' . $nf($kpi['in_progress']),
        ],
        [
            'scope' => 'sla_breached', 'label' => '% ทำได้ตาม SLA', 'icon' => 'fa-solid fa-gauge-high',
            'color' => $slaColor,
            'value' => ($kpi['sla_pct'] === null ? '—' : $kpi['sla_pct'] . '%'), 'unit' => '',
            'sub' => 'เกิน SLA (เปิดค้าง) ' . $nf($kpi['sla_breached_open']) . ' รายการ',
        ],
        [
            'scope' => 'mtta', 'label' => 'เวลารับเรื่อง (MTTA)', 'icon' => 'fa-solid fa-user-check',
            'color' => 'info', 'value' => $fmtDuration($kpi['mtta_median_seconds']), 'unit' => '',
            'sub' => 'เฉลี่ย ' . $fmtDuration($kpi['mtta_seconds']) . ' · ' . $nf($kpi['mtta_count']) . '/' . $nf($kpi['total']) . ' ใบ',
        ],
        [
            'scope' => 'mttr', 'label' => 'เวลาซ่อมเสร็จ (MTTR)', 'icon' => 'fa-solid fa-screwdriver-wrench',
            'color' => 'primary', 'value' => $fmtDuration($kpi['mttr_median_seconds']), 'unit' => '',
            'sub' => 'เฉลี่ย ' . $fmtDuration($kpi['mttr_seconds']) . ' · ' . $nf($kpi['mttr_count']) . '/' . $nf($kpi['total']) . ' ใบ',
        ],
        [
            'scope' => 'rating', 'label' => 'ความพึงพอใจ', 'icon' => 'fa-solid fa-star',
            'color' => 'success', 'value' => ($kpi['rating_avg'] === null ? '—' : $kpi['rating_avg']), 'unit' => ($kpi['rating_avg'] === null ? '' : '/5'),
            'sub' => 'ประเมิน ' . $nf($kpi['rating_count']) . '/' . $nf($kpi['closed_total']) . ' ใบที่ปิด',
        ],
    ];
    foreach ($cards as $c): ?>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 ha-kpi" role="button" tabindex="0"
                 data-drill="<?= Html::encode($c['scope']) ?>" data-drill-title="<?= Html::encode($c['label']) ?>">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="small text-uppercase text-secondary fw-semibold"><?= $c['label'] ?></div>
                        <i class="<?= $c['icon'] ?> text-<?= $c['color'] ?> opacity-50 fs-4"></i>
                    </div>
                    <div class="d-flex align-items-end gap-1">
                        <span class="fw-bold text-<?= $c['color'] ?> lh-1 fs-3"><?= $c['value'] ?></span>
                        <?php if ($c['unit'] !== ''): ?><span class="small text-muted mb-1"><?= $c['unit'] ?></span><?php endif; ?>
                    </div>
                    <div class="small text-muted mt-1 text-truncate" title="<?= Html::encode($c['sub']) ?>"><?= $c['sub'] ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ===== SLA รายบริการ (ผู้บริหาร/คุณภาพ) ===== -->
<div class="ha-sec v-exec v-quality">
<div class="card border-0 shadow-sm mb-3">
    <?php
    $slaRows = $p['slaBySystem'] ?? [];
    // โฟกัสเฉพาะระบบงานของศูนย์ (เช่น แพทย์ = CAL-01) — งานนอกระบบยุบเป็นแถวหมายเหตุคลิกได้
    $homeCodes = \app\modules\helpdesk2\helpers\RepairDashboardV2Helper::homeSystemCodes($p['repairGroup'] ?? null);
    $homeRows = $slaRows;
    $offAgg = null;
    if ($homeCodes !== null) {
        $homeRows = [];
        $off = ['count' => 0, 'met' => 0, 'breached' => 0];
        foreach ($slaRows as $s) {
            if (in_array($s['code'], $homeCodes, true)) {
                $homeRows[] = $s;
            } else {
                $off['count'] += (int) $s['count'];
                $off['met'] += (int) $s['met'];
                $off['breached'] += (int) $s['breached'];
            }
        }
        if ($off['count'] > 0) {
            $off['pct'] = round($off['met'] / $off['count'] * 100, 1);
            $offAgg = $off;
        }
    }
    ?>
    <div class="card-header border-bottom d-flex align-items-center gap-2">
        <div class="erp-icon-box bg-primary bg-opacity-10"><i class="bi bi-clipboard-check"></i></div>
        <div>
            <h6 class="text-uppercase text-secondary m-0">ผล SLA ตามระบบงาน<?= $homeCodes !== null ? ' <span class="small text-muted fw-normal">(เฉพาะระบบงานของศูนย์)</span>' : '' ?></h6>
            <p class="small text-muted mb-0 d-none d-md-block">เป้าหมายความสำเร็จ ≥ <?= $slaTarget ?>% — คลิกแถวเพื่อดูงานของระบบงานนั้น</p>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($slaRows)): ?>
            <p class="text-muted mb-0">ยังไม่มีข้อมูลที่ประเมิน SLA ได้</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ระบบงาน</th>
                            <th class="text-end">จำนวน</th>
                            <th class="text-end">ทำได้ตามเวลา</th>
                            <th class="text-end">% สำเร็จ</th>
                            <th class="text-end text-nowrap">เร็วสุด</th>
                            <th class="text-end text-nowrap">ช้าสุด</th>
                            <th class="text-end text-nowrap">เฉลี่ย</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($homeRows as $s): ?>
                            <?php $pct = $s['pct']; $ok = $pct !== null && $pct >= $slaTarget; $sysClickable = ($s['code'] ?? '') !== ''; ?>
                            <tr <?= $sysClickable ? 'role="button" data-drill="device_type:' . Html::encode($s['code']) . '" data-drill-title="ระบบงาน: ' . Html::encode($s['title']) . '" style="cursor:pointer;"' : '' ?>>
                                <td class="text-break"><?= Html::encode($s['title']) ?></td>
                                <td class="text-end"><?= $nf($s['count']) ?></td>
                                <td class="text-end"><?= $nf($s['met']) ?></td>
                                <td class="text-end">
                                    <span class="badge rounded-pill px-2 py-1 bg-<?= $pct === null ? 'secondary' : ($ok ? 'success' : 'danger') ?> bg-opacity-10 text-<?= $pct === null ? 'secondary' : ($ok ? 'success' : 'danger') ?> border border-<?= $pct === null ? 'secondary' : ($ok ? 'success' : 'danger') ?>-subtle fw-medium">
                                        <?= $pct === null ? '—' : $pct . '%' ?>
                                    </span>
                                </td>
                                <td class="text-end small text-nowrap"><?= $fmtDuration($s['min_secs']) ?></td>
                                <td class="text-end small text-nowrap"><?= $fmtDuration($s['max_secs']) ?></td>
                                <td class="text-end small text-nowrap"><?= $fmtDuration($s['avg_secs']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($offAgg !== null): ?>
                            <tr class="table-light" role="button" data-drill="off_system" data-drill-title="งานนอกระบบงานของศูนย์" style="cursor:pointer;">
                                <td class="text-break text-muted"><i class="bi bi-three-dots me-1"></i>งานนอกระบบงานของศูนย์ <span class="small">(ระบบงานอื่นที่หลุดเข้ามา — คลิกดู/ตรวจ tag)</span></td>
                                <td class="text-end text-muted"><?= $nf($offAgg['count']) ?></td>
                                <td class="text-end text-muted"><?= $nf($offAgg['met']) ?></td>
                                <td class="text-end">
                                    <span class="badge rounded-pill px-2 py-1 bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle fw-medium"><?= $offAgg['pct'] ?>%</span>
                                </td>
                                <td class="text-end small text-muted">—</td>
                                <td class="text-end small text-muted">—</td>
                                <td class="text-end small text-muted">—</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== Pareto 2 มุม ===== -->
<?php
// แกนที่ 1: เครื่องมือแพทย์ = "ชนิดครุภัณฑ์" จากทะเบียนครุภัณฑ์ (รหัส GSN) / กลุ่มอื่น = "ตามระบบงาน"
$atp = $ha['assetTypePareto'] ?? null;
if ($isMedical && $atp && !empty($atp['rows'])) {
    $covCap = 'ผูกครุภัณฑ์ ' . $nf($atp['linked']) . '/' . $nf($atp['total']) . ' ใบ'
        . ($atp['total'] > 0 ? ' (' . round($atp['linked'] / $atp['total'] * 100) . '%)' : '')
        . ' · แสดงเฉพาะใบที่ผูกครุภัณฑ์';
    $firstBlock = [
        'title' => 'งานซ่อมตามชนิดเครื่องมือ', 'icon' => 'bi-hospital',
        'rows' => array_slice($atp['rows'], 0, 8), 'scopePrefix' => 'asset_prefix', 'idKey' => 'prefix',
        'caption' => $covCap,
    ];
} else {
    $firstBlock = [
        'title' => $isMedical ? 'งานซ่อมตามระบบงาน' : 'งานซ่อมตามประเภทงาน/ระบบ', 'icon' => 'bi-tags',
        'rows' => array_slice($p['paretoDevice'], 0, 8), 'scopePrefix' => 'device_type', 'idKey' => 'code',
        'caption' => null,
    ];
}
?>
<div class="row g-3 mb-3">
    <?php
    $paretoBlocks = [
        $firstBlock,
        ['title' => 'งานซ่อมตามหน่วยงาน/สถานที่', 'icon' => 'bi-diagram-3', 'rows' => array_slice($p['paretoDepartment'], 0, 8), 'scopePrefix' => 'department', 'idKey' => 'id', 'caption' => null],
    ];
    foreach ($paretoBlocks as $blk):
        $maxCnt = 1;
        foreach ($blk['rows'] as $r) { $maxCnt = max($maxCnt, (int) $r['cnt']); }
    ?>
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom d-flex align-items-center gap-2">
                    <div class="erp-icon-box bg-secondary bg-opacity-10"><i class="bi <?= $blk['icon'] ?>"></i></div>
                    <div>
                        <h6 class="text-uppercase text-secondary m-0"><?= $blk['title'] ?></h6>
                        <?php if (!empty($blk['caption'])): ?>
                            <p class="small text-muted mb-0 d-none d-md-block"><i class="bi bi-link-45deg me-1"></i><?= Html::encode($blk['caption']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($blk['rows'])): ?>
                        <p class="text-muted mb-0">ยังไม่มีข้อมูล</p>
                    <?php else: ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($blk['rows'] as $r): ?>
                                <?php
                                $cnt = (int) $r['cnt'];
                                $pctBar = (int) round(($cnt / $maxCnt) * 100);
                                $idVal = $r[$blk['idKey']] ?? '';
                                $clickable = ($idVal !== '' && $idVal !== 0);
                                ?>
                                <li class="list-group-item px-0 <?= $clickable ? '' : 'opacity-75' ?>"
                                    <?= $clickable ? 'role="button" style="cursor:pointer;" data-drill="' . $blk['scopePrefix'] . ':' . Html::encode((string) $idVal) . '" data-drill-title="' . Html::encode($r['title']) . '"' : '' ?>>
                                    <div class="d-flex justify-content-between align-items-center gap-2">
                                        <span class="text-break small"><?= Html::encode($r['title']) ?></span>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill fw-medium px-2 py-1"><?= $nf($cnt) ?></span>
                                    </div>
                                    <div class="progress mt-2 mb-0" style="height:6px;">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: <?= $pctBar ?>%"></div>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- ===== แนวโน้มรายเดือน + Problem Management ===== -->
<div class="row g-3 mb-3">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-info bg-opacity-10"><i class="bi bi-graph-up"></i></div>
                <div>
                    <h6 class="text-uppercase text-secondary m-0">แนวโน้มงานซ่อมรายเดือน</h6>
                    <p class="small text-muted mb-0 d-none d-md-block">จำนวนงาน (แท่ง) เทียบ %ทำได้ตาม SLA (เส้น) — คลิกแท่งเพื่อดูงานของเดือนนั้น</p>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($p['monthlyTrend'])): ?>
                    <p class="text-muted mb-0">ยังไม่มีข้อมูลแนวโน้ม</p>
                <?php else: ?>
                    <div style="position:relative; height:300px;">
                        <canvas id="haTrendChart"></canvas>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-12 col-xl-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-danger bg-opacity-10"><i class="bi bi-exclamation-diamond"></i></div>
                <div>
                    <h6 class="text-uppercase text-secondary m-0">สาเหตุรากเหง้ายอดนิยม</h6>
                    <p class="small text-muted mb-0 d-none d-md-block">Problem Management — จุดที่ควรจัดการเชิงรุก</p>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($p['rootCauses'])): ?>
                    <p class="text-muted mb-0">ยังไม่มีการบันทึกสาเหตุ</p>
                <?php else: ?>
                    <?php $maxRc = max(array_map(static fn($r) => (int) $r['cnt'], $p['rootCauses'])); ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($p['rootCauses'] as $rc): ?>
                            <?php $pctRc = $maxRc > 0 ? (int) round(($rc['cnt'] / $maxRc) * 100) : 0; ?>
                            <li class="list-group-item px-0">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <span class="text-break small"><?= Html::encode($rc['cause']) ?></span>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill fw-medium px-2 py-1"><?= $nf($rc['cnt']) ?></span>
                                </div>
                                <div class="progress mt-1 mb-0" style="height:5px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $pctRc ?>%"></div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</div><!-- /.ha-sec exec+quality -->

<!-- ===== ส่วนปฏิบัติการ (สถานะ, ภาระงานช่าง, รายการล่าสุด) — มุมมองช่าง/คุณภาพ ===== -->
<div class="ha-sec v-tech v-quality">
<?= $this->render('@app/modules/helpdesk2/views/dashboard/index', array_merge($p, [
    'pageTitle' => $this->title,
    'skipDashboardBreadcrumbs' => true,
    'hideKpiRow' => true,
])) ?>
</div><!-- /.ha-sec tech+quality -->

<!-- ===== Offcanvas drill-down ===== -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="haDrill" style="width:min(92vw,720px);" aria-labelledby="haDrillLabel">
    <div class="offcanvas-header border-bottom">
        <h6 class="offcanvas-title" id="haDrillLabel"><i class="bi bi-list-ul me-1"></i>รายละเอียด</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="ปิด"></button>
    </div>
    <div class="offcanvas-body" id="haDrillBody">
        <div class="text-center text-muted py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 small">กำลังโหลด…</div>
        </div>
    </div>
</div>

<?php
// ---- กราฟแนวโน้มรายเดือน ----
$trend = $p['monthlyTrend'] ?? [];
if (!empty($trend)) {
    $this->registerJsFile('@web/libs/chartjs/chart.umd.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
    $trendLabels = json_encode(array_map(static fn($m) => $m['month'], $trend), JSON_UNESCAPED_UNICODE);
    $trendCounts = json_encode(array_map(static fn($m) => (int) $m['count'], $trend));
    $trendSla = json_encode(array_map(static fn($m) => $m['sla_pct'], $trend));
    $trendJs = <<<JS
(function(){
  var el = document.getElementById('haTrendChart');
  if (!el || typeof Chart === 'undefined') return;
  var labels = {$trendLabels};
  var chart = new Chart(el, {
    type: 'bar',
    data: {
      labels: labels,
      datasets: [
        { type:'bar', label:'จำนวนงาน', data:{$trendCounts}, yAxisID:'y', backgroundColor:'rgba(13,110,253,.5)', borderRadius:4 },
        { type:'line', label:'% ตาม SLA', data:{$trendSla}, yAxisID:'y1', borderColor:'#dc3545', backgroundColor:'#dc3545', tension:.3, spanGaps:true }
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins:{ legend:{position:'bottom'} },
      scales:{
        y:{ beginAtZero:true, position:'left', title:{display:true,text:'จำนวน'} },
        y1:{ beginAtZero:true, max:100, position:'right', grid:{drawOnChartArea:false}, title:{display:true,text:'% SLA'} }
      },
      onClick:function(evt, els){
        if(!els.length) return;
        var m = labels[els[0].index];
        if(window.haDrill) window.haDrill('month:'+m, 'เดือน '+m);
      }
    }
  });
})();
JS;
    $this->registerJs($trendJs);
}

$drillUrl = Url::to([$drillRoute]);
$js = <<<JS
(function(){
  var offEl = document.getElementById('haDrill');
  if (!offEl) return;
  var off = bootstrap.Offcanvas.getOrCreateInstance(offEl);
  var body = document.getElementById('haDrillBody');
  var labelEl = document.getElementById('haDrillLabel');

  function currentFilterParams(){
    var p = new URLSearchParams(window.location.search);
    p.delete('scope');
    return p;
  }

  function openDrill(scope, title){
    labelEl.innerHTML = '<i class="bi bi-list-ul me-1"></i>' + (title || 'รายละเอียด');
    body.innerHTML = '<div class="text-center text-muted py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 small">กำลังโหลด…</div></div>';
    off.show();
    var p = currentFilterParams();
    p.set('scope', scope);
    fetch('{$drillUrl}?' + p.toString(), {headers: {'X-Requested-With':'XMLHttpRequest'}})
      .then(function(r){ return r.text(); })
      .then(function(html){ body.innerHTML = html; })
      .catch(function(){ body.innerHTML = '<div class="alert alert-danger">โหลดข้อมูลไม่สำเร็จ กรุณาลองใหม่</div>'; });
  }

  window.haDrill = openDrill;

  document.querySelectorAll('[data-drill]').forEach(function(el){
    el.addEventListener('click', function(){
      openDrill(el.getAttribute('data-drill'), el.getAttribute('data-drill-title'));
    });
    el.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); el.click(); }
    });
  });

  // ---- แถบมุมมองผู้ใช้: กรองการแสดงผลแบบซ่อน (ค่าเริ่มต้นเห็นครบ) ----
  var haNotes = {
    '':        { cls: '', text: '' },
    'tech':    { cls: 'alert-warning', text: '👷 มุมมองช่าง — เน้นความพร้อมใช้ · งานสอบเทียบ/PM เกินกำหนด · คิวงานค้าง · ภาระงานช่าง (ซ่อนตารางวิเคราะห์ระดับบริหาร)' },
    'exec':    { cls: 'alert-primary', text: '📊 มุมมองผู้บริหาร — เน้นตัวชี้วัดสรุป %พร้อมใช้/สอบเทียบ/PM/SLA และแนวโน้ม (ซ่อนรายการงานปฏิบัติการ)' },
    'quality': { cls: 'alert-success', text: '✔️ มุมมองคุณภาพ (HA) — แสดงหลักฐานครบตามมาตรฐาน HA II-3 พร้อมพิมพ์รายงานเข้าเล่มได้' }
  };
  var noteEl = document.getElementById('ha-view-note');
  function setNote(v){
    if (!noteEl) return;
    var n = haNotes[v] || haNotes[''];
    noteEl.className = 'alert border-0 shadow-sm small py-2 px-3 mb-3';
    if (n.text) { noteEl.classList.add(n.cls); noteEl.textContent = n.text; }
    else { noteEl.classList.add('d-none'); }
  }
  document.querySelectorAll('[data-ha-pill]').forEach(function(btn){
    btn.addEventListener('click', function(){
      var v = btn.getAttribute('data-ha-pill');
      if (v) { document.body.setAttribute('data-ha-view', v); }
      else { document.body.removeAttribute('data-ha-view'); }
      document.querySelectorAll('[data-ha-pill]').forEach(function(x){
        x.classList.remove('btn-primary'); x.classList.add('btn-outline-primary');
      });
      btn.classList.remove('btn-outline-primary'); btn.classList.add('btn-primary');
      setNote(v);
    });
  });
})();
JS;
$this->registerJs($js);
?>

<style>
.ha-kpi { transition: transform .12s ease, box-shadow .12s ease; cursor: pointer; }
.ha-kpi:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
.ha-kpi:focus-visible { outline: 2px solid var(--bs-primary); outline-offset: 2px; }
body[data-ha-view="tech"] .ha-sec:not(.v-tech) { display: none; }
body[data-ha-view="exec"] .ha-sec:not(.v-exec) { display: none; }
body[data-ha-view="quality"] .ha-sec:not(.v-quality) { display: none; }
</style>

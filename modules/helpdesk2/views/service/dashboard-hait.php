<?php

use yii\helpers\Url;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $title */
/** @var string $icon */
/** @var string $active */
/** @var array $dashboardParams */

$p = $dashboardParams;
$kpi = $p['kpi'];
$filters = $p['filters'];
$opts = $p['filterOptions'];
$range = $p['dateRange'];

$this->title = 'แดชบอร์ด HAIT — ' . $title;
$this->params['breadcrumbs'][] = 'ระบบงานซ่อม';
$this->params['breadcrumbs'][] = $title;
$this->params['breadcrumbs'][] = 'แดชบอร์ด HAIT';

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
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <?= $icon ?> <?= Html::encode($title) ?> — แดชบอร์ด HAIT
    </h4>
    <span class="small text-muted">มาตรฐานคุณภาพระบบเทคโนโลยีสารสนเทศโรงพยาบาล หมวด 4: Service Desk / SLA / Incident Management</span>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/helpdesk2/menu', ['active' => $active]) ?>
<?php $this->endBlock(); ?>

<!-- ===== แถบกรอง ===== -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="get" class="row g-2 align-items-end" id="hait-filter">
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
                <?= Html::a('<i class="bi bi-printer me-1"></i>พิมพ์รายงาน HAIT', array_merge(['report'], $reportFilters), [
                    'class' => 'btn btn-sm btn-outline-primary',
                    'target' => '_blank',
                    'encode' => false,
                ]) ?>
            </div>
        </form>
    </div>
</div>

<!-- ===== คำเตือนคุณภาพข้อมูลเชิงเวลา ===== -->
<div class="alert alert-warning border-0 shadow-sm d-flex gap-2 py-2 px-3 small mb-3" role="alert">
    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
    <div>
        ตัวชี้วัดเชิงเวลา (MTTA / MTTR / %SLA) คำนวณจากส่วนต่างระหว่างเวลาแจ้งกับหมุดเวลาที่บันทึกในระบบ
        และแสดงด้วย <strong>ค่ามัธยฐาน (median)</strong> เพื่อลดผลจากงานค้างนาน — ค่าจะแม่นยำขึ้นเมื่อเจ้าหน้าที่บันทึกการรับเรื่อง/ปิดงานตรงเวลาจริง
    </div>
</div>

<!-- ===== KPI 6 ใบ (คลิกดูรายละเอียด) ===== -->
<div class="row g-3 mb-3">
    <?php
    $cards = [
        [
            'scope' => 'total', 'label' => 'อุบัติการณ์ทั้งหมด', 'icon' => 'fa-solid fa-list-check',
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
            'color' => ($kpi['sla_pct'] === null ? 'secondary' : ($kpi['sla_pct'] >= 80 ? 'success' : 'danger')),
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
            <div class="card border-0 shadow-sm h-100 hait-kpi" role="button" tabindex="0"
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

<!-- ===== รายงาน HAIT ฉบับ 1: ผลการดำเนินการตาม SLA รายบริการ ===== -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-bottom d-flex align-items-center gap-2">
        <div class="erp-icon-box bg-primary bg-opacity-10"><i class="bi bi-clipboard-check"></i></div>
        <div>
            <h6 class="text-uppercase text-secondary m-0">ผลการดำเนินการตามข้อตกลงระดับบริการ (SLA)</h6>
            <p class="small text-muted mb-0 d-none d-md-block">รายงานตามรูปแบบคู่มือ TMI บทที่ 4 — เป้าหมายความสำเร็จ ≥ 80%</p>
        </div>
    </div>
    <div class="card-body">
        <?php if (empty($p['slaByService'])): ?>
            <p class="text-muted mb-0">ยังไม่มีข้อมูลที่ประเมิน SLA ได้</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>รายการบริการ</th>
                            <th class="text-end">จำนวน</th>
                            <th class="text-end">ทำได้ตามเวลา</th>
                            <th class="text-end">% สำเร็จ</th>
                            <th class="text-end text-nowrap">เร็วสุด</th>
                            <th class="text-end text-nowrap">ช้าสุด</th>
                            <th class="text-end text-nowrap">เฉลี่ย</th>
                        </tr>
                    </thead>
                    <tbody class="table-group-divider">
                        <?php foreach ($p['slaByService'] as $s): ?>
                            <?php $pct = $s['pct']; $ok = $pct !== null && $pct >= 80; ?>
                            <tr role="button" data-drill="service:<?= Html::encode($s['code']) ?>" data-drill-title="บริการ: <?= Html::encode($s['title']) ?>" style="cursor:pointer;">
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
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ===== รายงาน HAIT ฉบับ 2: Pareto อุบัติการณ์ 2 มุม ===== -->
<div class="row g-3 mb-3">
    <?php
    $paretoBlocks = [
        ['title' => 'อุบัติการณ์ตามประเภทอุปกรณ์', 'icon' => 'bi-tags', 'rows' => array_slice($p['paretoDevice'], 0, 8), 'scopePrefix' => 'device_type', 'idKey' => 'code'],
        ['title' => 'อุบัติการณ์ตามหน่วยงาน/สถานที่', 'icon' => 'bi-diagram-3', 'rows' => array_slice($p['paretoDepartment'], 0, 8), 'scopePrefix' => 'department', 'idKey' => 'id'],
    ];
    foreach ($paretoBlocks as $blk):
        $maxCnt = 1;
        foreach ($blk['rows'] as $r) { $maxCnt = max($maxCnt, (int) $r['cnt']); }
    ?>
        <div class="col-12 col-xl-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header border-bottom d-flex align-items-center gap-2">
                    <div class="erp-icon-box bg-secondary bg-opacity-10"><i class="bi <?= $blk['icon'] ?>"></i></div>
                    <h6 class="text-uppercase text-secondary m-0"><?= $blk['title'] ?></h6>
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

<!-- ===== เฟส 3: แนวโน้มรายเดือน + Problem Management ===== -->
<div class="row g-3 mb-3">
    <div class="col-12 col-xl-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header border-bottom d-flex align-items-center gap-2">
                <div class="erp-icon-box bg-info bg-opacity-10"><i class="bi bi-graph-up"></i></div>
                <div>
                    <h6 class="text-uppercase text-secondary m-0">แนวโน้มอุบัติการณ์รายเดือน</h6>
                    <p class="small text-muted mb-0 d-none d-md-block">จำนวนงาน (แท่ง) เทียบ %ทำได้ตาม SLA (เส้น) — คลิกแท่งเพื่อดูงานของเดือนนั้น</p>
                </div>
            </div>
            <div class="card-body">
                <?php if (empty($p['monthlyTrend'])): ?>
                    <p class="text-muted mb-0">ยังไม่มีข้อมูลแนวโน้ม</p>
                <?php else: ?>
                    <div style="position:relative; height:300px;">
                        <canvas id="haitTrendChart"></canvas>
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

<!-- ===== เฟส 4: Capacity — อายุครุภัณฑ์คอมพิวเตอร์ (HAIT 7) ===== -->
<?php if (!empty($p['assetCapacity'])): $cap = $p['assetCapacity']; ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header border-bottom d-flex align-items-center justify-content-between gap-2 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-warning bg-opacity-10"><i class="bi bi-pc-display"></i></div>
            <div>
                <h6 class="text-uppercase text-secondary m-0">ศักยภาพครุภัณฑ์คอมพิวเตอร์ (Capacity — HAIT 7)</h6>
                <p class="small text-muted mb-0 d-none d-md-block">ช่วงอายุการใช้งาน เพื่อวางแผนจัดหาทดแทน</p>
            </div>
        </div>
        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary-subtle rounded-pill px-2 py-1">รวม <?= $nf($cap['total']) ?> เครื่อง</span>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?php
            $bandColor = ['0-3 ปี' => 'success', '4-7 ปี' => 'info', 'มากกว่า 7 ปี' => 'danger', 'ไม่ทราบอายุ' => 'secondary'];
            foreach ($cap['bands'] as $b):
                $color = $bandColor[$b['band']] ?? 'secondary';
                $pctBand = $cap['total'] > 0 ? round(($b['assets'] / $cap['total']) * 100) : 0;
            ?>
                <div class="col-6 col-xl-3">
                    <div class="border rounded-3 p-3 h-100 border-<?= $color ?>-subtle bg-<?= $color ?> bg-opacity-10">
                        <div class="small text-uppercase text-<?= $color ?> fw-semibold mb-1"><?= Html::encode($b['band']) ?></div>
                        <div class="d-flex align-items-end gap-1">
                            <span class="fw-bold fs-3 lh-1 text-<?= $color ?>"><?= $nf($b['assets']) ?></span>
                            <span class="small text-muted mb-1">เครื่อง (<?= $pctBand ?>%)</span>
                        </div>
                        <div class="small text-muted mt-1">ผูกงานซ่อม <?= $nf($b['repairs']) ?> รายการ</div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <p class="small text-muted mt-3 mb-0">
            <i class="bi bi-info-circle me-1"></i>
            เครื่องอายุ <strong>มากกว่า 7 ปี</strong> ควรพิจารณาจัดหาทดแทนตามข้อเสนอแนะมาตรฐาน HAIT
            — ตัวเลข "ผูกงานซ่อม" นับเฉพาะใบที่ระบุหมายเลขครุภัณฑ์ตรงกับทะเบียน (<?= $nf($cap['linked']) ?> ใบ) จึงยังต่ำกว่างานจริง
        </p>
    </div>
</div>
<?php endif; ?>

<!-- ===== ส่วนกราฟ/ตารางเดิม (สถานะ, SLA, ช่าง, รายการล่าสุด, หมวดปัญหา) ===== -->
<?= $this->render('@app/modules/helpdesk2/views/dashboard/index', array_merge($p, [
    'pageTitle' => $this->title,
    'skipDashboardBreadcrumbs' => true,
    'hideKpiRow' => true,
])) ?>

<!-- ===== Offcanvas drill-down ===== -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="haitDrill" style="width:min(92vw,720px);" aria-labelledby="haitDrillLabel">
    <div class="offcanvas-header border-bottom">
        <h6 class="offcanvas-title" id="haitDrillLabel"><i class="bi bi-list-ul me-1"></i>รายละเอียด</h6>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="ปิด"></button>
    </div>
    <div class="offcanvas-body" id="haitDrillBody">
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
  var el = document.getElementById('haitTrendChart');
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
        if(window.haitDrill) window.haitDrill('month:'+m, 'เดือน '+m);
      }
    }
  });
})();
JS;
    $this->registerJs($trendJs);
}

$drillUrl = Url::to(['/helpdesk/computer/drilldown']);
$js = <<<JS
(function(){
  var offEl = document.getElementById('haitDrill');
  if (!offEl) return;
  var off = bootstrap.Offcanvas.getOrCreateInstance(offEl);
  var body = document.getElementById('haitDrillBody');
  var labelEl = document.getElementById('haitDrillLabel');

  function currentFilterParams(){
    // ใช้ตัวกรองที่ส่งมาใน URL ปัจจุบัน (หลังกดกรอง) เพื่อให้ drill-down สืบทอดบริบทเดียวกัน
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

  window.haitDrill = openDrill; // ให้กราฟ (Chart.js onClick) เรียกใช้ได้

  document.querySelectorAll('[data-drill]').forEach(function(el){
    el.addEventListener('click', function(){
      openDrill(el.getAttribute('data-drill'), el.getAttribute('data-drill-title'));
    });
    el.addEventListener('keydown', function(e){
      if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); el.click(); }
    });
  });
})();
JS;
$this->registerJs($js);
?>

<style>
.hait-kpi { transition: transform .12s ease, box-shadow .12s ease; cursor: pointer; }
.hait-kpi:hover { transform: translateY(-2px); box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important; }
.hait-kpi:focus-visible { outline: 2px solid var(--bs-primary); outline-offset: 2px; }
</style>

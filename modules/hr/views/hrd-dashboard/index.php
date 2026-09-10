<?php

/**
 * HRD Dashboard V2 — ภาพรวมการพัฒนาบุคลากร
 *
 * @var yii\web\View $this
 * @var int   $fy               ปีงบประมาณที่เลือก (พ.ศ.)
 * @var int   $currentFy
 * @var array $fyOptions
 * @var array $kpis             จาก HrdMetricsService::kpis()
 * @var array $trend            จาก HrdMetricsService::developmentTrend()
 * @var float $coverageThreshold
 */

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'ภาพรวมการพัฒนาบุคลากร (HRD)';

// ---- เตรียมค่าการ์ด KPI ให้ view อ่านง่าย ----
$fmtPct = function ($v) {
    return $v === null ? '—' : rtrim(rtrim(number_format((float) $v, 1), '0'), '.') . '%';
};
$hc = $kpis['headcount'];
$sc = $kpis['skillCoverage'];
$tc = $kpis['trainingCompletion'];
$idp = $kpis['idpSuccess'];
$su = $kpis['successionReady'];

$cards = [
    [
        'label' => 'จำนวนพนักงาน', 'icon' => 'bi-people-fill', 'color' => 'primary',
        'value' => number_format((int) $hc['value']), 'unit' => 'คน',
        'sub' => 'ปฏิบัติงานจริง (ณ ปัจจุบัน)',
        'url' => Url::to(['/hr/employees']),
    ],
    [
        'label' => 'ความครอบคลุมทักษะ', 'icon' => 'bi-bullseye', 'color' => 'success',
        'value' => $fmtPct($sc['percent']), 'unit' => '',
        'sub' => $sc['total'] > 0 ? "ผ่านเกณฑ์ {$sc['pass']} / {$sc['total']} คน" : 'ยังไม่มีผลประเมินในปีนี้',
        'url' => Url::to(['/hr/competency']),
    ],
    [
        'label' => 'ช่องว่างทักษะสำคัญ', 'icon' => 'bi-graph-down-arrow', 'color' => 'danger',
        'value' => $fmtPct($sc['gap_percent']), 'unit' => '',
        'sub' => $sc['total'] > 0 ? 'ยังต่ำกว่าเกณฑ์ ' . ($sc['total'] - $sc['pass']) . ' คน' : 'ยังไม่มีผลประเมินในปีนี้',
        'url' => Url::to(['/hr/competency']),
    ],
    [
        'label' => 'การอบรมเสร็จสิ้น', 'icon' => 'bi-mortarboard-fill', 'color' => 'info',
        'value' => $fmtPct($tc['percent']), 'unit' => '',
        'sub' => $tc['total'] > 0 ? "สำเร็จ {$tc['done']} / {$tc['total']} แผน" : 'ยังไม่มีแผนพัฒนาในปีนี้',
        'url' => Url::to(['/hr/training-roadmap/index']),
    ],
    [
        'label' => 'IDP สำเร็จ', 'icon' => 'bi-clipboard2-check-fill', 'color' => 'warning',
        'value' => $fmtPct($idp['percent']), 'unit' => '',
        'sub' => $idp['total'] > 0 ? "ปิดรอบ {$idp['done']} / {$idp['total']} แผน" : 'ยังไม่มีรอบ IDP ในปีนี้',
        'url' => Url::to(['/hr/idp/index']),
    ],
    [
        'label' => 'ผู้สืบทอดพร้อม', 'icon' => 'bi-people', 'color' => 'secondary',
        'value' => $fmtPct($su['percent']), 'unit' => '',
        'sub' => $su['total'] > 0 ? "High Potential {$su['ready']} / {$su['total']} คน" : 'ยังไม่ได้จัด 9-Box ปีนี้',
        'url' => Url::to(['/hr/talent-grid']),
    ],
];

$this->registerCss(<<<CSS
.hrd-dash .hrd-kpi{display:block;border:0;border-radius:14px;background:var(--bs-body-bg);text-decoration:none;transition:transform .12s ease,box-shadow .12s ease}
.hrd-dash .hrd-kpi:hover{transform:translateY(-2px);box-shadow:0 .5rem 1.25rem rgba(2,6,23,.08)!important}
.hrd-dash .hrd-kpi .card-body{padding:1.05rem 1.1rem}
.hrd-dash .hrd-kpi__icon{width:48px;height:48px;border-radius:12px;display:grid;place-items:center;font-size:1.35rem;flex:none}
.hrd-dash .hrd-kpi__label{font-size:.82rem;font-weight:600;color:var(--bs-secondary-color);line-height:1.2}
.hrd-dash .hrd-kpi__value{font-size:1.85rem;font-weight:800;line-height:1.05;font-variant-numeric:tabular-nums;color:var(--bs-body-color)}
.hrd-dash .hrd-kpi__unit{font-size:.9rem;font-weight:600;color:var(--bs-secondary-color);margin-inline-start:.15rem}
.hrd-dash .hrd-kpi__sub{font-size:.74rem;color:var(--bs-tertiary-color,#94a3b8);line-height:1.3;margin-top:.35rem;min-height:1.9em}
@media(max-width:575.98px){.hrd-dash .hrd-kpi__value{font-size:1.5rem}}
CSS, [], 'hrd-dashboard');
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-1 mb-2 text-center text-lg-start">
    <h4 class="fw-semibold text-body d-flex align-items-center gap-2 mb-0">
        <i class="bi bi-easel2-fill" aria-hidden="true"></i>
        <span><?= Html::encode($this->title) ?></span>
    </h4>
    <div class="small text-body-secondary">พัฒนาคน พัฒนาองค์กร สู่การเติบโตอย่างยั่งยืน</div>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'hrd']) ?>
<?php $this->endBlock(); ?>

<section class="hrd-dash">

    <!-- แถบเลือกปีงบประมาณ -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center gap-2">
            <span class="small fw-semibold text-body d-flex align-items-center gap-1">
                <i class="bi bi-calendar3" aria-hidden="true"></i>ปีงบประมาณ
            </span>
            <form method="get" action="<?= Url::to(['/hr/hrd-dashboard/index']) ?>" class="d-flex align-items-center gap-2 mb-0">
                <select name="fy" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                    <?php foreach ($fyOptions as $y): ?>
                        <option value="<?= $y ?>" <?= $y === $fy ? 'selected' : '' ?>>
                            <?= $y ?><?= $y === $currentFy ? ' (ปีปัจจุบัน)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <span class="small text-body-secondary ms-auto">ตัวชี้วัดทุกใบอิงปีงบประมาณที่เลือก · เกณฑ์ผ่านทักษะ ≥ <?= (int) $coverageThreshold ?>%</span>
        </div>
    </div>

    <!-- KPI 6 ใบ -->
    <div class="row g-3 mb-3">
        <?php foreach ($cards as $c): $color = $c['color']; ?>
            <div class="col-6 col-md-4 col-xl-2">
                <a class="card hrd-kpi shadow-sm h-100" href="<?= $c['url'] ?>" data-pjax="0" aria-label="<?= Html::encode($c['label']) ?>">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="hrd-kpi__icon bg-<?= $color ?>-subtle text-<?= $color ?>-emphasis">
                                <i class="bi <?= $c['icon'] ?>" aria-hidden="true"></i>
                            </span>
                            <span class="hrd-kpi__label flex-grow-1"><?= Html::encode($c['label']) ?></span>
                        </div>
                        <div class="hrd-kpi__value">
                            <?= $c['value'] ?><?php if ($c['unit'] !== ''): ?><span class="hrd-kpi__unit"><?= $c['unit'] ?></span><?php endif; ?>
                        </div>
                        <div class="hrd-kpi__sub"><?= Html::encode($c['sub']) ?></div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="row g-3">
        <!-- แนวโน้มการพัฒนา -->
        <div class="col-12 col-xl-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="bi bi-graph-up-arrow text-primary" aria-hidden="true"></i>
                        <h2 class="h6 fw-bold mb-0">แนวโน้มการพัฒนาบุคลากร</h2>
                    </div>
                    <p class="small text-body-secondary mb-2">จำนวนกิจกรรมพัฒนารายเดือน ตลอดปีงบประมาณ <?= $fy ?></p>
                    <div id="hrdTrendChart" style="min-height:320px"></div>
                </div>
            </div>
        </div>

        <!-- ทางลัดโมดูล -->
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-grid-3x3-gap-fill text-primary" aria-hidden="true"></i>
                        <h2 class="h6 fw-bold mb-0">ลงลึกรายโมดูล</h2>
                    </div>
                    <div class="list-group list-group-flush">
                        <?php
                        $links = [
                            ['bi-bullseye', 'ประเมินสมรรถนะ', '/hr/competency', 'ผล Core / Functional รายบุคคล'],
                            ['bi-mortarboard-fill', 'เส้นทางฝึกอบรม', '/hr/training-roadmap/index', 'แผนพัฒนา & ผลการอบรม'],
                            ['bi-clipboard2-check-fill', 'IDP', '/hr/idp/index', 'แผนพัฒนารายบุคคลรายรอบ'],
                            ['bi-diagram-3-fill', 'Talent 9-Box', '/hr/talent-grid', 'ศักยภาพ × ผลงาน'],
                            ['bi-cash-coin', 'รายงานพัฒนาบุคลากร', '/hr/development/report', 'งบแผน-ผล P42 / P70'],
                        ];
                        foreach ($links as $l): ?>
                            <a href="<?= Url::to([$l[2]]) ?>" data-pjax="0" class="list-group-item list-group-item-action d-flex align-items-center gap-3 px-1">
                                <span class="text-primary"><i class="bi <?= $l[0] ?> fs-5" aria-hidden="true"></i></span>
                                <span class="flex-grow-1">
                                    <span class="d-block fw-semibold small"><?= $l[1] ?></span>
                                    <span class="d-block text-body-secondary" style="font-size:.72rem"><?= $l[3] ?></span>
                                </span>
                                <i class="bi bi-chevron-right text-body-tertiary small" aria-hidden="true"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</section>

<?php
$trendJson = json_encode($trend, JSON_UNESCAPED_UNICODE);
$this->registerJs(<<<JS
(function(){
    if (typeof ApexCharts === 'undefined') return;
    var trend = {$trendJson};
    var el = document.getElementById('hrdTrendChart');
    if (!el) return;
    var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    var chart = new ApexCharts(el, {
        chart: { type: 'line', height: 340, toolbar: { show: false }, fontFamily: 'inherit', foreColor: isDark ? '#94a3b8' : '#475569' },
        series: trend.series,
        colors: ['#2563eb', '#f59e0b', '#10b981'],
        stroke: { curve: 'smooth', width: 3 },
        markers: { size: 4, hover: { size: 6 } },
        dataLabels: { enabled: false },
        xaxis: { categories: trend.months, tooltip: { enabled: false } },
        yaxis: { min: 0, forceNiceScale: true, labels: { formatter: function(v){ return Math.round(v); } } },
        legend: { position: 'top', horizontalAlign: 'left' },
        grid: { borderColor: isDark ? 'rgba(148,163,184,.15)' : 'rgba(2,6,23,.06)', strokeDashArray: 4 },
        tooltip: { theme: isDark ? 'dark' : 'light' }
    });
    chart.render();
})();
JS);
?>

<?php

/**
 * แท็บ "ภาพรวม" ของงาน HRD — รวมภาพรวมการพัฒนาบุคลากร (เดิมคือหน้า Dashboard V2)
 *
 * @var yii\web\View $this
 * @var array $metrics        ตัวชี้วัดปฏิบัติการจาก WorkforceController (employees/jd/idp/trm)
 * @var object|null $activeCycle รอบ IDP ที่กำลังใช้งาน
 * @var array $hrd            ['kpis','trend','activity','inbox','deadlines','coverageThreshold']
 * @var int   $hrdFy          ปีงบประมาณที่เลือก (พ.ศ.)
 * @var array $hrdFyOptions
 */

use app\components\ThaiDateHelper;
use yii\helpers\Html;
use yii\helpers\Url;

$k = $hrd['kpis'];
$sc = $k['skillCoverage'];
$tc = $k['trainingCompletion'];
$idp = $k['idpSuccess'];
$su = $k['successionReady'];
$fmtPct = fn ($v) => $v === null ? '—' : rtrim(rtrim(number_format((float) $v, 1), '0'), '.') . '%';

// การ์ดผลลัพธ์ (hero) — headcount ใช้ตัวเลขของหน้านี้ (นับผู้ปฏิบัติราชการทั้งหมด) ให้ตรงกับที่เคยแสดง
// คลิกการ์ด = เปิด modal รายชื่อเบื้องหลังตัวเลข (drill-down ผ่าน ?detail=)
$detailUrl = fn ($kpi) => Url::to(['/hr/workforce/index', 'section' => 'overview', 'detail' => $kpi, 'fy' => $hrdFy]);
$cards = [
    ['label' => 'บุคลากรที่ปฏิบัติงาน', 'icon' => 'bi-people-fill', 'color' => 'primary',
     'value' => number_format((int) $k['headcount']['value']), 'unit' => 'คน', 'sub' => 'สถานะปฏิบัติราชการ',
     'detail' => 'headcount'],
    ['label' => 'ความครอบคลุมทักษะ', 'icon' => 'bi-bullseye', 'color' => 'success',
     'value' => $fmtPct($sc['percent']), 'unit' => '',
     'sub' => $sc['total'] > 0 ? "ผ่านเกณฑ์ {$sc['pass']} / {$sc['total']} คน" : 'ยังไม่มีผลประเมินในปีนี้',
     'detail' => 'coverage'],
    ['label' => 'ช่องว่างทักษะสำคัญ', 'icon' => 'bi-graph-down-arrow', 'color' => 'danger',
     'value' => $fmtPct($sc['gap_percent']), 'unit' => '',
     'sub' => $sc['total'] > 0 ? 'ยังต่ำกว่าเกณฑ์ ' . ($sc['total'] - $sc['pass']) . ' คน' : 'ยังไม่มีผลประเมินในปีนี้',
     'detail' => 'gap'],
    ['label' => 'การอบรมเสร็จสิ้น', 'icon' => 'bi-mortarboard-fill', 'color' => 'info',
     'value' => $fmtPct($tc['percent']), 'unit' => '',
     'sub' => $tc['total'] > 0 ? "สำเร็จ {$tc['done']} / {$tc['total']} แผน" : 'ยังไม่มีแผนพัฒนาในปีนี้',
     'detail' => 'training'],
    ['label' => 'IDP สำเร็จ', 'icon' => 'bi-clipboard2-check-fill', 'color' => 'warning',
     'value' => $fmtPct($idp['percent']), 'unit' => '',
     'sub' => $idp['total'] > 0 ? "ปิดรอบ {$idp['done']} / {$idp['total']} แผน" : 'ยังไม่มีรอบ IDP ในปีนี้',
     'detail' => 'idp'],
    ['label' => 'ผู้สืบทอดพร้อม', 'icon' => 'bi-people', 'color' => 'secondary',
     'value' => $fmtPct($su['percent']), 'unit' => '',
     'sub' => $su['total'] > 0 ? "High Potential {$su['ready']} / {$su['total']} คน" : 'ยังไม่ได้จัด 9-Box ปีนี้',
     'detail' => 'succession'],
];

$this->registerCss(<<<CSS
.hrd-ov .hrd-kpi{display:block;border:1px solid #e4e7ec;border-radius:14px;background:#fff;text-decoration:none;transition:transform .12s,box-shadow .12s}
.hrd-ov .hrd-kpi:hover{transform:translateY(-2px);box-shadow:0 .5rem 1.25rem rgba(2,6,23,.08)}
.hrd-ov .hrd-kpi .card-body{padding:1rem 1.05rem}
.hrd-ov .hrd-kpi__icon{width:46px;height:46px;border-radius:12px;display:grid;place-items:center;font-size:1.3rem;flex:none}
.hrd-ov .hrd-kpi__label{font-size:.8rem;font-weight:600;color:#667085;line-height:1.2}
.hrd-ov .hrd-kpi__value{font-size:1.75rem;font-weight:800;line-height:1.05;font-variant-numeric:tabular-nums;color:#1d2939}
.hrd-ov .hrd-kpi__unit{font-size:.85rem;font-weight:600;color:#667085;margin-inline-start:.15rem}
.hrd-ov .hrd-kpi__sub{font-size:.73rem;color:#98a2b3;line-height:1.3;margin-top:.3rem;min-height:1.9em}
.hrd-ov .hrd-kpi__more{display:inline-flex;align-items:center;gap:.25rem;margin-top:.45rem;font-size:.72rem;font-weight:600;opacity:0;transition:opacity .12s}
.hrd-ov .hrd-kpi:hover .hrd-kpi__more,.hrd-ov .hrd-kpi:focus-within .hrd-kpi__more{opacity:1}
@media(hover:none){.hrd-ov .hrd-kpi__more{opacity:.85}}
.hrd-ov .hrd-card{background:#fff;border:1px solid #e4e7ec;border-radius:14px}
.hrd-ov .hrd-card h2{font-size:1rem;margin:0;color:#1d2939}
.hrd-ov .hrd-li{display:flex;align-items:center;gap:.75rem;padding:.6rem 0;border-bottom:1px solid #eef1f5;text-decoration:none;color:#1d2939}
.hrd-ov .hrd-li:last-child{border-bottom:0}
.hrd-ov .hrd-li:hover{color:#2457a7}
[data-bs-theme="dark"] .hrd-ov .hrd-kpi,[data-bs-theme="dark"] .hrd-ov .hrd-card{background:var(--bs-body-bg);border-color:var(--bs-border-color)}
[data-bs-theme="dark"] .hrd-ov .hrd-kpi__value{color:var(--bs-body-color)}
CSS);
?>
<section class="hrd-ov">

    <!-- แถบเลือกปีงบประมาณ -->
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <form method="get" action="<?= Url::to(['/hr/workforce/index']) ?>" class="d-flex align-items-center gap-2 mb-0">
            <input type="hidden" name="section" value="overview">
            <span class="small fw-semibold d-flex align-items-center gap-1"><i class="bi bi-calendar3"></i>ปีงบประมาณ</span>
            <select name="fy" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <?php foreach ($hrdFyOptions as $y): ?>
                    <option value="<?= $y ?>" <?= $y === $hrdFy ? 'selected' : '' ?>><?= $y ?><?= $y === (int) $hrdFyOptions[0] ? ' (ปีปัจจุบัน)' : '' ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <span class="small text-muted ms-auto">ตัวชี้วัดอิงปีงบที่เลือก · เกณฑ์ผ่านทักษะ ≥ <?= (int) $hrd['coverageThreshold'] ?>%</span>
    </div>

    <!-- KPI ผลลัพธ์ 6 ใบ -->
    <div class="row g-3 mb-3">
        <?php foreach ($cards as $c): $color = $c['color']; ?>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card hrd-kpi h-100 position-relative">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="hrd-kpi__icon bg-<?= $color ?>-subtle text-<?= $color ?>-emphasis"><i class="bi <?= $c['icon'] ?>"></i></span>
                            <span class="hrd-kpi__label flex-grow-1"><?= Html::encode($c['label']) ?></span>
                        </div>
                        <div class="hrd-kpi__value"><?= $c['value'] ?><?php if ($c['unit'] !== ''): ?><span class="hrd-kpi__unit"><?= $c['unit'] ?></span><?php endif; ?></div>
                        <div class="hrd-kpi__sub"><?= Html::encode($c['sub']) ?></div>
                        <span class="hrd-kpi__more small text-<?= $color ?>-emphasis"><i class="bi bi-list-ul"></i> ดูรายชื่อ</span>
                    </div>
                    <a class="stretched-link open-modal" href="<?= $detailUrl($c['detail']) ?>" data-size="modal-lg" aria-label="ดูรายชื่อ<?= Html::encode($c['label']) ?>"></a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- แนวโน้ม + รอบที่ใช้งาน -->
    <div class="row g-3 mb-3">
        <div class="col-12 col-xl-8">
            <div class="hrd-card p-3 h-100">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="bi bi-graph-up-arrow text-primary"></i>
                    <h2>แนวโน้มการพัฒนาบุคลากร</h2>
                </div>
                <p class="small text-muted mb-2">จำนวนกิจกรรมพัฒนารายเดือน ปีงบประมาณ <?= $hrdFy ?></p>
                <div id="hrdTrendChart" style="min-height:300px"></div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="hrd-card p-3 h-100">
                <div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-arrow-repeat text-primary"></i><h2>รอบที่กำลังใช้งาน</h2></div>
                <?php if ($activeCycle): ?>
                    <div class="fw-semibold"><?= Html::encode($activeCycle->title) ?></div>
                    <div class="text-muted small mt-1">IDP · <?= Html::encode($activeCycle->start_date) ?> – <?= Html::encode($activeCycle->end_date) ?></div>
                <?php else: ?>
                    <p class="text-muted small mb-2">ยังไม่มีรอบ IDP ที่กำลังใช้งาน</p>
                    <?= Html::a('ตั้งค่ารอบ IDP', ['/hr/idp/cycle'], ['class' => 'btn btn-sm btn-outline-primary open-modal', 'data-size' => 'modal-lg']) ?>
                <?php endif ?>
                <hr class="my-3">
                <div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-grid-3x3-gap-fill text-primary"></i><h2>ลงลึกรายโมดูล</h2></div>
                <?php foreach ([
                    ['bi-bullseye', 'ประเมินสมรรถนะ', ['/hr/competency']],
                    ['bi-mortarboard-fill', 'เส้นทางฝึกอบรม (TRM)', ['/hr/training-roadmap/index']],
                    ['bi-clipboard2-check-fill', 'IDP', ['/hr/idp/index']],
                    ['bi-diagram-3-fill', 'Talent 9-Box', ['/hr/talent-grid']],
                    ['bi-cash-coin', 'รายงานพัฒนาบุคลากร', ['/hr/development/report']],
                ] as $l): ?>
                    <a href="<?= Url::to($l[2]) ?>" data-pjax="0" class="hrd-li">
                        <span class="text-primary"><i class="bi <?= $l[0] ?>"></i></span>
                        <span class="flex-grow-1 small fw-semibold"><?= $l[1] ?></span>
                        <i class="bi bi-chevron-right text-muted small"></i>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- งานที่ต้องดูแล + กิจกรรมล่าสุด + กำหนดการ -->
    <div class="row g-3">
        <div class="col-12 col-lg-4">
            <div class="hrd-card p-3 h-100">
                <div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-inbox-fill text-primary"></i><h2>งานที่ต้องดูแล</h2></div>
                <?php
                $jdPending = (int) $metrics['jd_pending'];
                $hasWork = $jdPending > 0 || !empty($hrd['inbox']);
                ?>
                <?php if (!$hasWork): ?>
                    <div class="text-center text-muted py-4"><i class="bi bi-check2-circle fs-3 d-block mb-1 text-success"></i><span class="small">ไม่มีงานค้างดำเนินการ</span></div>
                <?php else: ?>
                    <?php if ($jdPending > 0): ?>
                        <a href="<?= Url::to(['/hr/workforce/index', 'section' => 'jd']) ?>" data-pjax="0" class="hrd-li">
                            <span class="hrd-kpi__icon bg-warning-subtle text-warning-emphasis" style="width:36px;height:36px;font-size:.95rem"><i class="bi bi-pen"></i></span>
                            <span class="flex-grow-1 small fw-semibold">JD รอลงนามรับทราบ</span>
                            <span class="badge bg-warning rounded-pill"><?= number_format($jdPending) ?></span>
                        </a>
                    <?php endif; ?>
                    <?php foreach ($hrd['inbox'] as $i): ?>
                        <a href="<?= Url::to($i['route']) ?>" data-pjax="0" class="hrd-li">
                            <span class="hrd-kpi__icon bg-<?= $i['color'] ?>-subtle text-<?= $i['color'] ?>-emphasis" style="width:36px;height:36px;font-size:.95rem"><i class="bi <?= $i['icon'] ?>"></i></span>
                            <span class="flex-grow-1 small fw-semibold"><?= Html::encode($i['label']) ?></span>
                            <span class="badge bg-<?= $i['color'] ?> rounded-pill"><?= number_format($i['count']) ?></span>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="hrd-card p-3 h-100">
                <div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-clock-history text-primary"></i><h2>กิจกรรมล่าสุด</h2></div>
                <?php if (empty($hrd['activity'])): ?>
                    <div class="text-center text-muted py-4"><i class="bi bi-clock fs-3 d-block mb-1"></i><span class="small">ยังไม่มีกิจกรรมพัฒนาในระบบ</span></div>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($hrd['activity'] as $a): ?>
                            <li class="d-flex align-items-start gap-2 py-2 border-bottom">
                                <span class="text-<?= $a['color'] ?> mt-1"><i class="bi <?= $a['icon'] ?>"></i></span>
                                <span class="flex-grow-1">
                                    <span class="d-block small"><span class="fw-semibold"><?= Html::encode($a['name']) ?></span> — <?= Html::encode($a['text']) ?></span>
                                    <span class="d-block text-muted" style="font-size:.72rem"><?= $a['at'] ? Html::encode(ThaiDateHelper::formatThaiDate(substr((string) $a['at'], 0, 10))) : '' ?></span>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="hrd-card p-3 h-100">
                <div class="d-flex align-items-center gap-2 mb-2"><i class="bi bi-calendar-event text-primary"></i><h2>กำหนดการที่จะถึง (60 วัน)</h2></div>
                <?php if (empty($hrd['deadlines'])): ?>
                    <div class="text-center text-muted py-4"><i class="bi bi-calendar-check fs-3 d-block mb-1 text-success"></i><span class="small">ไม่มีกำหนดการใกล้ครบใน 60 วัน</span></div>
                <?php else: ?>
                    <ul class="list-unstyled mb-0">
                        <?php foreach ($hrd['deadlines'] as $d): ?>
                            <li class="d-flex align-items-start gap-2 py-2 border-bottom">
                                <span class="text-<?= $d['color'] ?> mt-1"><i class="bi <?= $d['icon'] ?>"></i></span>
                                <span class="flex-grow-1">
                                    <span class="d-block small fw-semibold"><?= Html::encode(ThaiDateHelper::formatThaiDate($d['date'])) ?></span>
                                    <span class="d-block text-muted" style="font-size:.72rem"><?= Html::encode($d['label']) ?><?= $d['name'] ? ' · ' . Html::encode($d['name']) : '' ?></span>
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

</section>

<?php
$trendJson = json_encode($hrd['trend'], JSON_UNESCAPED_UNICODE);
$this->registerJs(<<<JS
(function(){
    if (typeof ApexCharts === 'undefined') return;
    var trend = {$trendJson};
    var el = document.getElementById('hrdTrendChart');
    if (!el) return;
    var isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    new ApexCharts(el, {
        chart: { type: 'line', height: 320, toolbar: { show: false }, fontFamily: 'inherit', foreColor: isDark ? '#94a3b8' : '#475569' },
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
    }).render();
})();
JS);
?>

<?php

use yii\helpers\Html;
use yii\helpers\Json;
use app\components\RichText;
use app\modules\pm\components\KpiStatus;

/** @var yii\web\View $this */
/** @var app\modules\pm\components\KpiRow[] $rows */
/** @var array $summary @var array $byGroup @var int $year @var int $defaultYear */
/** @var int|null $group @var int|null $unit @var string $q */
/** @var array $groupItems @var array $unitNames */

$this->title = 'ภาพรวมตัวชี้วัด';
$this->beginBlock('page-title'); ?>
<div><h4 class="fw-semibold mb-1">ภาพรวมตัวชี้วัด</h4><div class="text-muted small">สรุปผลตัวชี้วัดทุกกลุ่ม ปีงบประมาณ <?= (int) $year ?></div></div>
<?php $this->endBlock();
$this->beginBlock('page-action'); ?><?= $this->render('../_menu', ['active' => 'overview']) ?><?php $this->endBlock();

$fmt = Yii::$app->formatter;
$num = static fn ($v) => $v === null ? '-' : rtrim(rtrim(number_format((float) $v, 4, '.', ''), '0'), '.');

/** เส้นแนวโน้ม inline SVG จากค่าจริงรายปี (เบา ไม่ผูก JS) */
$spark = static function (array $series): string {
    $series = array_filter($series, static fn ($v) => $v !== null);
    if (count($series) < 2) return '<span class="text-body-secondary small">—</span>';
    ksort($series);
    $vals = array_values($series);
    $min = min($vals); $max = max($vals); $span = ($max - $min) ?: 1;
    $w = 84; $h = 24; $n = count($vals); $step = $w / ($n - 1);
    $pts = [];
    foreach ($vals as $i => $v) {
        $x = round($i * $step, 1);
        $y = round($h - (($v - $min) / $span) * ($h - 4) - 2, 1);
        $pts[] = "$x,$y";
    }
    return '<svg width="' . $w . '" height="' . $h . '" viewBox="0 0 ' . $w . ' ' . $h . '" fill="none">'
        . '<polyline points="' . implode(' ', $pts) . '" stroke="#4f46e5" stroke-width="1.6" stroke-linejoin="round" stroke-linecap="round"/></svg>';
};

$cards = [
    ['label' => 'ตัวชี้วัดทั้งหมด', 'value' => $summary['total'], 'icon' => 'bi-clipboard2-data', 'color' => 'primary'],
    ['label' => 'ผ่าน (PASS)', 'value' => $summary['pass'], 'icon' => 'bi-check2-circle', 'color' => 'success'],
    ['label' => 'ต้องพัฒนา (GAP)', 'value' => $summary['gap'], 'icon' => 'bi-exclamation-triangle', 'color' => 'danger'],
    ['label' => 'อัตราสำเร็จ', 'value' => $summary['successRate'] . '%', 'icon' => 'bi-graph-up-arrow', 'color' => 'info'],
];

$yearOpts = range($defaultYear + 1, $defaultYear - 4);
?>

<div class="row g-2 g-md-3 mb-3">
    <?php foreach ($cards as $c): ?>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100"><div class="card-body d-flex align-items-center gap-3 py-3">
                <span class="d-inline-flex align-items-center justify-content-center rounded-3 bg-<?= $c['color'] ?>-subtle text-<?= $c['color'] ?>" style="width:44px;height:44px"><i class="bi <?= $c['icon'] ?> fs-5"></i></span>
                <div>
                    <div class="small text-muted"><?= Html::encode($c['label']) ?></div>
                    <div class="fs-4 fw-bold" style="font-variant-numeric:tabular-nums"><?= is_string($c['value']) ? Html::encode($c['value']) : (int) $c['value'] ?></div>
                </div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

<div class="card border-0 shadow-sm mb-3"><div class="card-body">
    <?= Html::beginForm(['index'], 'get', ['class' => 'row g-3 align-items-end']) ?>
    <div class="col-6 col-md-2">
        <label class="form-label fw-semibold small">ปีงบประมาณ</label>
        <?= Html::dropDownList('year', $year, array_combine($yearOpts, $yearOpts), ['class' => 'form-select']) ?>
    </div>
    <div class="col-6 col-md-3">
        <label class="form-label fw-semibold small">กลุ่ม</label>
        <?= Html::dropDownList('group', $group, $groupItems, ['class' => 'form-select', 'prompt' => 'ทุกกลุ่ม']) ?>
    </div>
    <div class="col-6 col-md-3">
        <label class="form-label fw-semibold small">หน่วยงาน</label>
        <?= Html::dropDownList('unit', $unit, $unitNames, ['class' => 'form-select', 'prompt' => 'ทุกหน่วยงาน']) ?>
    </div>
    <div class="col-6 col-md-2">
        <label class="form-label fw-semibold small">ค้นหา</label>
        <?= Html::textInput('q', $q, ['class' => 'form-control', 'placeholder' => 'ชื่อตัวชี้วัด']) ?>
    </div>
    <div class="col-12 col-md-auto d-flex gap-2">
        <?= Html::submitButton('<i class="bi bi-search"></i>', ['class' => 'btn btn-primary']) ?>
        <?= Html::a('ล้าง', ['index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?= Html::endForm() ?>
</div></div>

<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-bold mb-2">ภาพรวมสถานะ</h6>
            <?php if ($summary['total']): ?>
                <div id="kpi-status-pie"></div>
            <?php else: ?>
                <div class="text-center text-muted py-5">ยังไม่มีข้อมูล</div>
            <?php endif; ?>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100"><div class="card-body">
            <h6 class="fw-bold mb-3">สรุปตามกลุ่ม</h6>
            <?php if (!$byGroup): ?>
                <div class="text-center text-muted py-4">ยังไม่มีข้อมูล</div>
            <?php else: foreach ($byGroup as $name => $g): ?>
                <?php $pct = $g['total'] ? round(($g['pass']) / $g['total'] * 100) : 0; ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-semibold text-truncate" style="max-width:60%"><span class="d-inline-block rounded-circle me-2" style="width:9px;height:9px;background:<?= Html::encode($g['color']) ?>"></span><?= Html::encode($name) ?></span>
                        <span class="small text-muted">ผ่าน <?= $g['pass'] ?>/<?= $g['total'] ?> · GAP <?= $g['gap'] ?><?= $g['nodata'] ? ' · ไม่มีข้อมูล ' . $g['nodata'] : '' ?></span>
                    </div>
                    <div class="progress" style="height:8px"><div class="progress-bar bg-success" style="width:<?= $pct ?>%"></div></div>
                </div>
            <?php endforeach; endif; ?>
        </div></div>
    </div>
</div>

<div class="card border-0 shadow-sm overflow-hidden"><div class="card-body p-0">
    <div class="table-responsive"><table class="table align-middle mb-0">
        <thead class="table-light"><tr>
            <th class="ps-4">ตัวชี้วัด</th><th>กลุ่ม</th><th>หน่วยงาน</th><th class="text-center">หน่วย</th>
            <th class="text-end">เป้า</th><th class="text-end">ผลจริง</th><th class="text-center">แนวโน้ม</th><th class="text-center pe-4">สถานะ</th>
        </tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td class="ps-4"><?php $nm = Html::encode(RichText::plain($r->name, 160)); ?><?= $r->detailUrl ? Html::a($nm, $r->detailUrl, ['class' => 'fw-semibold text-decoration-none']) : '<span class="fw-semibold">' . $nm . '</span>' ?></td>
                <td class="small text-muted"><?= Html::encode($r->groupName) ?></td>
                <td class="small"><?= Html::encode($r->orgUnitId ? ($unitNames[$r->orgUnitId] ?? '-') : '-') ?></td>
                <td class="text-center small"><?= Html::encode($r->unit ?: '-') ?></td>
                <td class="text-end" style="font-variant-numeric:tabular-nums"><?= Html::encode($num($r->target)) ?></td>
                <td class="text-end" style="font-variant-numeric:tabular-nums"><?= Html::encode($num($r->actual)) ?></td>
                <td class="text-center"><?= $spark($r->series) ?></td>
                <td class="text-center pe-4"><span class="badge <?= KpiStatus::badgeClass($r->status) ?>"><?= Html::encode(KpiStatus::label($r->status)) ?></span></td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$rows): ?>
            <tr><td colspan="8" class="text-center text-muted py-5">ยังไม่มีตัวชี้วัดในเงื่อนไขนี้</td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</div></div>

<?php if ($summary['total']): ?>
<?php
$pieData = Json::encode([
    'series' => [$summary['pass'], $summary['gap'], $summary['nodata']],
    'labels' => ['ผ่าน (PASS)', 'ต้องพัฒนา (GAP)', 'ยังไม่มีข้อมูล'],
]);
$this->registerJs(<<<JS
(function(){
    if (typeof ApexCharts === 'undefined') return;
    var d = $pieData;
    var el = document.getElementById('kpi-status-pie');
    if (!el) return;
    new ApexCharts(el, {
        chart: { type: 'donut', height: 260 },
        series: d.series, labels: d.labels,
        colors: ['#198754', '#dc3545', '#adb5bd'],
        legend: { position: 'bottom' },
        dataLabels: { enabled: true, formatter: function(val, o){ return o.w.globals.series[o.seriesIndex]; } },
        plotOptions: { pie: { donut: { labels: { show: true, total: { show: true, label: 'ทั้งหมด' } } } } }
    }).render();
})();
JS);
?>
<?php endif; ?>

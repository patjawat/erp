<?php

use app\modules\complaint\models\Complaint;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;

/** @var View $this */
/** @var array $m ผลจาก ComplaintKpiService::metrics */
/** @var int $fiscalYear */
/** @var int[] $years */

$this->title = 'ตัวชี้วัดเรื่องร้องเรียน';
$this->registerJsFile('@web/apexcharts/apexcharts.min.js', ['position' => View::POS_HEAD]);

$kpi = $m['kpi'];
// การ์ด KPI (โทนสี + ไอคอน)
$tiles = [
    'CC01' => ['icon' => 'bi-people', 'tone' => 'primary'],
    'CC02' => ['icon' => 'bi-diagram-3', 'tone' => 'info'],
    'CC03' => ['icon' => 'bi-play-circle', 'tone' => 'secondary'],
    'CC04' => ['icon' => 'bi-search', 'tone' => 'secondary'],
    'CC05' => ['icon' => 'bi-reply', 'tone' => 'secondary'],
    'CC06' => ['icon' => 'bi-flag', 'tone' => 'success'],
    'CC07' => ['icon' => 'bi-emoji-smile', 'tone' => 'warning'],
];

$statusLabels = Complaint::statusLabels();
$statusData = [];
$statusCats = [];
foreach ($m['statusCount'] as $st => $n) {
    $statusCats[] = $statusLabels[$st] ?? $st;
    $statusData[] = $n;
}
$levelData = array_values($m['levelCount']);
$typeLabels = array_keys($m['typeCount']);
$typeData = array_values($m['typeCount']);
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($this->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ปีงบ <?= $fiscalYear ?> · <?= number_format($m['total']) ?> เรื่อง<?php $this->endBlock(); ?>

<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h4 fw-semibold mb-0"><i class="bi bi-graph-up-arrow me-1"></i> ตัวชี้วัด (KPI)</h1>
        <div class="d-flex gap-2">
            <?= Html::beginForm(['kpi'], 'get', ['class' => 'd-flex align-items-center gap-2 mb-0']) ?>
                <label class="small text-body-secondary mb-0">ปีงบ</label>
                <?= Html::dropDownList('fy', $fiscalYear, array_combine($years, $years), ['class' => 'form-select form-select-sm', 'style' => 'width:auto', 'onchange' => 'this.form.submit()']) ?>
            <?= Html::endForm() ?>
            <?= Html::a('<i class="bi bi-file-earmark-bar-graph me-1"></i> รายงาน', ['report', 'fy' => $fiscalYear], ['class' => 'btn btn-outline-secondary btn-sm']) ?>
        </div>
    </div>

    <div class="mb-3"><?= $this->render('@app/modules/complaint/menu', ['active' => 'kpi']) ?></div>

    <?php if (!$m['visits']): ?>
        <div class="alert alert-warning py-2 small"><i class="bi bi-exclamation-triangle me-1"></i> ยังไม่ได้กรอกจำนวน visit ของปีงบ <?= $fiscalYear ?> — CC01 จะยังคำนวณไม่ได้ (ตั้งค่าที่เมนู <b>จำนวน visit รายปี</b>)</div>
    <?php endif; ?>

    <!-- KPI tiles -->
    <div class="row g-3 mb-3">
        <?php foreach ($kpi as $code => $k): $t = $tiles[$code]; ?>
            <div class="col-6 col-lg-3">
                <div class="card border shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="d-inline-flex align-items-center justify-content-center rounded bg-<?= $t['tone'] ?>-subtle text-<?= $t['tone'] ?>-emphasis" style="width:34px;height:34px;">
                                <i class="bi <?= $t['icon'] ?>"></i>
                            </span>
                            <span class="badge text-bg-light"><?= $code ?></span>
                        </div>
                        <div class="small text-body-secondary" style="min-height:2.4em"><?= Html::encode($k['label']) ?></div>
                        <div class="h4 fw-bold mb-0">
                            <?= $k['value'] !== null ? number_format((float) $k['value'], $k['unit'] === '%' || $k['unit'] === 'วัน' || $k['unit'] === '/5' ? 1 : 2) : '<span class="text-body-secondary fs-6">— ไม่มีข้อมูล —</span>' ?>
                            <?php if ($k['value'] !== null): ?><span class="fs-6 text-body-secondary fw-normal"><?= Html::encode($k['unit']) ?></span><?php endif; ?>
                        </div>
                        <div class="text-body-secondary" style="font-size:.72rem"><?= Html::encode($k['raw']) ?></div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- charts -->
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-bar-chart me-1"></i> แนวโน้มรายเดือน (ตามปีงบ)</div>
                <div class="card-body"><div id="chart-trend"></div></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-pie-chart me-1"></i> ตามสถานะ</div>
                <div class="card-body"><div id="chart-status"></div></div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-exclamation-triangle me-1"></i> ตามระดับความรุนแรง</div>
                <div class="card-body"><div id="chart-level"></div></div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="card border shadow-sm h-100">
                <div class="card-header bg-transparent fw-semibold"><i class="bi bi-tags me-1"></i> ตามประเภทเรื่อง</div>
                <div class="card-body">
                    <?php if ($typeData): ?><div id="chart-type"></div>
                    <?php else: ?><div class="text-body-secondary small">— ไม่มีข้อมูล —</div><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$data = Json::encode([
    'months' => $m['monthLabels'],
    'monthCount' => array_values($m['monthCount']),
    'statusCats' => $statusCats,
    'statusData' => $statusData,
    'levelData' => $levelData,
    'typeLabels' => $typeLabels,
    'typeData' => $typeData,
]);
$js = <<<JS
(function(){
  if (typeof ApexCharts === 'undefined') return;
  var D = $data;
  var baseFont = {fontFamily: 'inherit'};

  new ApexCharts(document.querySelector('#chart-trend'), {
    chart: Object.assign({type:'bar', height:300, toolbar:{show:false}}, baseFont),
    series: [{name:'จำนวนเรื่อง', data: D.monthCount}],
    xaxis: {categories: D.months},
    plotOptions: {bar:{borderRadius:4, columnWidth:'55%'}},
    dataLabels: {enabled:false},
    colors: ['#0d6efd']
  }).render();

  new ApexCharts(document.querySelector('#chart-status'), {
    chart: Object.assign({type:'donut', height:300}, baseFont),
    series: D.statusData,
    labels: D.statusCats,
    legend: {position:'bottom'},
    colors: ['#6c757d','#0dcaf0','#0d6efd','#ffc107','#198754','#343a40']
  }).render();

  new ApexCharts(document.querySelector('#chart-level'), {
    chart: Object.assign({type:'bar', height:300, toolbar:{show:false}}, baseFont),
    series: [{name:'จำนวนเคส', data: D.levelData}],
    xaxis: {categories: ['ระดับ 1','ระดับ 2','ระดับ 3','ระดับ 4']},
    plotOptions: {bar:{borderRadius:4, distributed:true, columnWidth:'55%'}},
    dataLabels: {enabled:true},
    legend: {show:false},
    colors: ['#adb5bd','#ffc107','#fd7e14','#dc3545']
  }).render();

  if (D.typeData && D.typeData.length) {
    new ApexCharts(document.querySelector('#chart-type'), {
      chart: Object.assign({type:'bar', height:300, toolbar:{show:false}}, baseFont),
      series: [{name:'จำนวนเรื่อง', data: D.typeData}],
      xaxis: {categories: D.typeLabels},
      plotOptions: {bar:{borderRadius:4, horizontal:true}},
      dataLabels: {enabled:true},
      colors: ['#20c997']
    }).render();
  }
})();
JS;
$this->registerJs($js, View::POS_END);
?>

<?php

use app\modules\swot\models\SwotBoard;
use app\modules\swot\models\SwotNote;
use yii\helpers\Html;
use yii\helpers\Json;

/** @var yii\web\View $this */
/** @var app\modules\swot\models\SwotBoard $model */
/** @var array $weightByQuadrant */
/** @var array $weightByCategory */

$this->title = $model->title . ' — เรดาร์สรุป';
$quadInfo = SwotNote::QUADRANT_INFO;

// เตรียมข้อมูลเรดาร์ 4 ด้าน
$quadLabels = [];
$quadValues = [];
foreach ($weightByQuadrant as $q => $sum) {
    $info = $quadInfo[$q];
    $quadLabels[] = $info['code'] . ' · ' . $info['short'];
    $quadValues[] = (int) $sum;
}

// เตรียมข้อมูลหมวดหมู่ (เรียงมาก→น้อย เอาสูงสุด 8)
$catLabels = [];
$catValues = [];
foreach (array_slice($weightByCategory, 0, 8, true) as $cat => $sum) {
    $catLabels[] = $cat;
    $catValues[] = (int) $sum;
}

$totalNotes = $model->getNotes()->count();
$totalWeight = array_sum($quadValues);
$strongest = '';
if ($quadValues) {
    $maxIdx = array_keys($quadValues, max($quadValues))[0];
    $strongest = $quadLabels[$maxIdx] ?? '';
}

$chartData = Json::encode([
    'quadLabels' => $quadLabels,
    'quadValues' => $quadValues,
    'catLabels' => $catLabels,
    'catValues' => $catValues,
    'isSoar' => $model->isSoar(),
]);
$this->registerJs("(function(){ if(typeof ApexCharts==='undefined'){return;} const d=$chartData; window.swotRenderRadar(d); })();");
?>
<?php $this->beginBlock('page-title'); ?><?= Html::encode($model->title) ?><?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>ขั้นตอนที่ 3 · เรดาร์สรุปน้ำหนักแต่ละด้าน<?php $this->endBlock(); ?>

<div class="swot-radar-view">

    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <?= Html::a('<i class="bi bi-arrow-left"></i> คลัง', ['index'], ['class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3']) ?>
            <span class="badge rounded-pill text-bg-<?= $model->isSoar() ? 'primary' : 'success' ?>"><?= SwotBoard::frameworkLabel($model->framework) ?></span>
        </div>
        <?= Html::a('<i class="bi bi-printer me-1"></i>พิมพ์', 'javascript:window.print()', ['class' => 'btn btn-sm btn-outline-secondary rounded-pill px-3']) ?>
    </div>

    <?= $this->render('_step_nav', ['model' => $model, 'active' => 'radar']) ?>

    <?php if ($totalNotes === 0): ?>
        <div class="text-center text-muted py-5 border rounded-4 bg-light">
            <i class="bi bi-pentagon d-block mb-2" style="font-size:2.5rem;"></i>
            ยังไม่มีประเด็นให้สรุป — กลับไป<?= Html::a('ระดมประเด็น', ['board', 'id' => $model->id], ['class' => 'fw-semibold']) ?>ก่อน
        </div>
    <?php else: ?>
        <!-- สรุปตัวเลข -->
        <div class="row g-3 mb-3">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
                    <div class="text-muted small">จำนวนประเด็น</div>
                    <div class="fs-4 fw-bold"><?= $totalNotes ?></div>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
                    <div class="text-muted small">น้ำหนักรวม</div>
                    <div class="fs-4 fw-bold"><?= $totalWeight ?></div>
                </div></div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100"><div class="card-body py-3">
                    <div class="text-muted small">ด้านที่มีน้ำหนักสูงสุด</div>
                    <div class="fs-5 fw-bold text-primary"><?= Html::encode($strongest) ?></div>
                </div></div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-pentagon me-1 text-primary"></i>ภาพรวม 4 ด้าน (น้ำหนักรวม)</div>
                    <div class="card-body"><div id="swotRadarQuad"></div></div>
                </div>
            </div>
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-semibold"><i class="bi bi-bar-chart me-1 text-success"></i>น้ำหนักตามหมวดหมู่</div>
                    <div class="card-body">
                        <?php if (empty($catValues)): ?>
                            <div class="text-muted small text-center py-4">ยังไม่ได้จัดหมวดหมู่ — ไปที่<?= Html::a('ขั้นตอนจัดหมวด', ['table', 'id' => $model->id]) ?></div>
                        <?php else: ?>
                            <div id="swotRadarCat"></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
$this->registerJs(<<<'JS'
window.swotRenderRadar = function(d){
  var qEl = document.querySelector('#swotRadarQuad');
  if (qEl) {
    new ApexCharts(qEl, {
      chart: { type: 'radar', height: 360, toolbar: { show: false }, fontFamily: 'inherit' },
      series: [{ name: 'น้ำหนักรวม', data: d.quadValues }],
      labels: d.quadLabels,
      colors: [d.isSoar ? '#0d6efd' : '#198754'],
      stroke: { width: 2 },
      fill: { opacity: 0.25 },
      markers: { size: 4 },
      yaxis: { tickAmount: 4, labels: { formatter: function(v){ return Math.round(v); } } }
    }).render();
  }
  var cEl = document.querySelector('#swotRadarCat');
  if (cEl && d.catValues && d.catValues.length) {
    new ApexCharts(cEl, {
      chart: { type: 'bar', height: 360, toolbar: { show: false }, fontFamily: 'inherit' },
      series: [{ name: 'น้ำหนักรวม', data: d.catValues }],
      plotOptions: { bar: { horizontal: true, borderRadius: 3, barHeight: '65%' } },
      colors: ['#0d9488'],
      dataLabels: { enabled: true },
      xaxis: { categories: d.catLabels }
    }).render();
  }
};
JS
, \yii\web\View::POS_END);
?>

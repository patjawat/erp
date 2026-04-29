<?php

/** @var yii\web\View $this */
/** @var array $dashboard */
/** @var array|null $lifecycleStats */
/** @var app\modules\am\models\AssetDetail[] $recentTransfers */

use yii\web\View;
use yii\helpers\Html;
use yii\helpers\Json;
use app\models\Categorise;
use app\modules\am\models\Asset;
use app\modules\hr\models\Organization;

$this->title = 'ระบบบริหารทรัพย์สิน';
$this->params['breadcrumbs'][] = 'ระบบบริหารทรัพย์สิน';
$this->params['breadcrumbs'][] = ['label' => 'ภาพรวม', 'url' => ['/am']];

$kpis = $dashboard['kpis'] ?? [];
$health = $dashboard['health'] ?? [];
$replacement = $dashboard['replacementForecast'] ?? [];
$categoryDist = $dashboard['categoryDistribution'] ?? [];
$deptDist = $dashboard['departmentDistribution'] ?? [];
$groupDist = $dashboard['groupDistribution'] ?? [];
$riskAlerts = $dashboard['riskAlerts'] ?? [];
$ageAnalysis = $dashboard['ageAnalysis'] ?? [];
$recentActivities = $dashboard['recentActivities'] ?? [];

$categoryLabels = [];
$categoryValues = [];
foreach ($categoryDist as $row) {
    $code = $row['label'] ?? '';
    $cat = Categorise::find()->where(['name' => 'asset_type', 'code' => $code])->one();
    $categoryLabels[] = $cat ? $cat->title : $code;
    $categoryValues[] = (int) ($row['value'] ?? 0);
}

$deptLabels = [];
$deptValues = [];
foreach ($deptDist as $row) {
    $id = (int) ($row['dept_id'] ?? 0);
    $org = $id ? Organization::findOne($id) : null;
    $deptLabels[] = $org ? $org->name : ($id ? "หน่วยงาน #{$id}" : 'ไม่ระบุ');
    $deptValues[] = (int) ($row['value'] ?? 0);
}

$groupLabels = [];
$groupValues = [];
foreach ($groupDist as $row) {
    $groupLabels[] = $row['label'] ?: 'ไม่ระบุ';
    $groupValues[] = (int) ($row['value'] ?? 0);
}

$ageLabels = [];
$ageValues = [];
foreach ($ageAnalysis as $row) {
    $ageLabels[] = $row['age_bucket'] ?? '';
    $ageValues[] = (int) ($row['value'] ?? 0);
}

$healthLabels = ['ใช้งานดี', 'ใกล้ครบอายุ', 'ครบอายุแล้ว', 'ส่งซ่อม', 'รอจำหน่าย'];
$healthValues = [
    $health['healthy'] ?? 0,
    $health['near_eol'] ?? 0,
    $health['expired'] ?? 0,
    $health['under_repair'] ?? 0,
    $health['pending_disposal'] ?? 0,
];

$replacementLabels = $replacement['labels'] ?? [];
$replacementCounts = $replacement['counts'] ?? [];

$recentList = is_array($recentTransfers) ? array_slice($recentTransfers, 0, 5) : [];
$transfers = $recentActivities['transfers'] ?? [];
$repairs = $recentActivities['repairs'] ?? [];
$disposals = $recentActivities['disposals'] ?? [];
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
  <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect width="7" height="9" x="3" y="3" rx="1"></rect>
      <rect width="7" height="5" x="14" y="3" rx="1"></rect>
      <rect width="7" height="9" x="14" y="12" rx="1"></rect>
      <rect width="7" height="5" x="3" y="16" rx="1"></rect>
    </svg>
    ภาพรวม<?= $this->title ?>
  </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/am/menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>

<div class="container-fluid px-2 px-md-3 pb-4">
  <!-- Section 1 — Executive KPI -->
  <section class="mb-4">
    <div class="row g-4 mt-1">
      <div class="col-6 col-md-4 col-lg">
        <div class="card">
          <div class="card-body py-2">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
              <div class="d-flex flex-column gap-3">
                <span class="fw-bold fs-3"><?= (int) ($kpis['total_assets'] ?? 0) ?></span>
                <span class="text-primary">ครุภัณฑ์ทั้งหมด (รายการ)</span>
              </div>
              <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-pill">
                <i data-lucide="package"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <div class="card">
          <div class="card-body py-2">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
              <div class="d-flex flex-column gap-3">
                <span class="fw-bold fs-3"><?= (int) ($kpis['exceeding_useful_life'] ?? 0) ?></span>
                <span class="text-danger">เกินอายุการใช้งาน (รายการ)</span>
              </div>
              <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-pill">
                <i data-lucide="history"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <div class="card">
          <div class="card-body py-2">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
              <div class="d-flex flex-column gap-3">
                <span class="fw-bold fs-3"><?= (int) ($kpis['under_repair'] ?? 0) ?></span>
                <span class="text-warning">ส่งซ่อม (รายการ)</span>
              </div>
              <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-pill">
                <i data-lucide="wrench"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <div class="card">
          <div class="card-body py-2">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
              <div class="d-flex flex-column gap-3">
                <span class="fw-bold fs-3"><?= (int) ($kpis['waiting_disposal'] ?? 0) ?></span>
                <span class="text-secondary">รอจำหน่าย (รายการ)</span>
              </div>
              <div class="bg-secondary bg-opacity-10 text-secondary p-3 rounded-pill">
                <i data-lucide="trash-2"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-6 col-md-4 col-lg">
        <div class="card">
          <div class="card-body py-2">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
              <div class="d-flex flex-column gap-3">
                <span class="fw-bold fs-3"><?= Html::encode(number_format($kpis['estimated_replacement_cost'] ?? 0, 0)) ?></span>
                <span class="text-info">มูลค่าแทนที่ (บาท)</span>
              </div>
              <div class="bg-info bg-opacity-10 text-info p-3 rounded-pill">
                <i data-lucide="banknote"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <?php if (!empty($lifecycleStats)): ?>
  <div class="row g-3 mb-3">
    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
              <i data-lucide="arrow-right-left"></i>
            </div>
            <h6 class="text-uppercase text-secondary m-0">โอนย้ายล่าสุด</h6>
          </div>
          <?= Html::a('โอนย้าย', ['/am/asset/transfer'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        </div>
        <div class="card-body p-0">
          <?php if (!empty($recentList)): ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($recentList as $t): ?>
            <li class="list-group-item d-flex justify-content-between">
              <span><?= Html::encode($t->assetById ? $t->assetById->code : '-') ?></span>
              <small class="text-muted"><?= Yii::$app->formatter->asDatetime($t->created_at) ?></small>
            </li>
            <?php endforeach; ?>
          </ul>
          <?php else: ?>
          <p class="text-muted mb-0 p-3">ยังไม่มีประวัติโอนย้าย</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <div class="col-12 col-md-6">
      <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
          <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
            <i data-lucide="refresh-cw"></i>
          </div>
          <h6 class="text-uppercase text-secondary m-0">วงจรชีวิตครุภัณฑ์</h6>
        </div>
        <div class="card-body">
          <div class="d-flex flex-wrap gap-2 mb-2">
            <?= Html::a('รับครุภัณฑ์หลายเครื่อง', ['/am/asset/bulk-create'], ['class' => 'btn btn-primary']) ?>
            <?= Html::a('โอนย้าย', ['/am/asset/transfer'], ['class' => 'btn btn-outline-secondary']) ?>
            <?= Html::a('ส่งซ่อม', ['/am/asset/repair'], ['class' => 'btn btn-outline-warning']) ?>
            <?= Html::a('จำหน่าย', ['/am/asset/dispose'], ['class' => 'btn btn-outline-danger']) ?>
            <?= Html::a('พิมพ์ QR', ['/am/asset/print-qr'], ['class' => 'btn btn-outline-primary']) ?>
          </div>
          <p class="small text-body-secondary mb-0">รับครุภัณฑ์ทีละหลายเครื่อง → โอนย้าย / ส่งซ่อม / จำหน่าย / พิมพ์สติกเกอร์ QR</p>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <div class="row g-3">
    <!-- Section 2 — Asset Health (Donut) -->
    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
          <div class="erp-icon-box bg-success bg-opacity-10 text-success">
            <i data-lucide="activity"></i>
          </div>
          <h6 class="text-uppercase text-secondary m-0">สถานะสุขภาพครุภัณฑ์</h6>
        </div>
        <div class="card-body">
          <div id="chart-health-donut" style="min-height: 280px;"></div>
        </div>
      </div>
    </div>
    <!-- Section 3 — Replacement Forecast (Stacked Bar) -->
    <div class="col-12 col-lg-8">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
          <div class="erp-icon-box bg-warning bg-opacity-10 text-warning">
            <i data-lucide="bar-chart-2"></i>
          </div>
          <h6 class="text-uppercase text-secondary m-0">คาดการณ์การแทนที่ (ครุภัณฑ์ใกล้ครบอายุ)</h6>
        </div>
        <div class="card-body">
          <div id="chart-replacement-bar" style="min-height: 280px;"></div>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3 mt-0">
    <!-- Section 4c — Group Distribution -->
    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
          <div class="erp-icon-box bg-success bg-opacity-10 text-success">
            <i data-lucide="pie-chart"></i>
          </div>
          <h6 class="text-uppercase text-secondary m-0">สัดส่วนตามกลุ่มทรัพย์สิน</h6>
        </div>
        <div class="card-body">
          <div id="chart-group-donut" style="min-height: 300px;"></div>
        </div>
      </div>
    </div>
    <!-- Section 4a — Category Distribution -->
    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
          <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
            <i data-lucide="layers"></i>
          </div>
          <h6 class="text-uppercase text-secondary m-0">การกระจายตามประเภท</h6>
        </div>
        <div class="card-body">
          <div id="chart-category-hbar" style="min-height: 300px;"></div>
        </div>
      </div>
    </div>
    <!-- Section 4b — Department Distribution -->
    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
          <div class="erp-icon-box bg-info bg-opacity-10 text-info">
            <i data-lucide="building-2"></i>
          </div>
          <h6 class="text-uppercase text-secondary m-0">การกระจายตามหน่วยงาน</h6>
        </div>
        <div class="card-body">
          <div id="chart-department-bar" style="min-height: 300px;"></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Section 5 — Risk Monitoring -->
  <div class="row g-3">
    <div class="col-12">
      <div class="card border-0 shadow-sm">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-danger bg-opacity-10 text-danger">
              <i data-lucide="alert-triangle"></i>
            </div>
            <h6 class="text-uppercase text-secondary m-0">การติดตามความเสี่ยง</h6>
          </div>
          <?= Html::a('ทะเบียนครุภัณฑ์', ['/am/equip/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        </div>
        <div class="card-body">
          <div class="row g-2 mb-3">
            <div class="col-12 col-sm-6 col-md-3">
              <div class="p-3 rounded bg-warning bg-opacity-10 border border-warning border-opacity-25">
                <div class="fw-bold text-warning"><?= number_format($riskAlerts['no_department_count'] ?? 0) ?></div>
                <div class="small text-secondary">ยังไม่กำหนดหน่วยงาน</div>
              </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
              <div class="p-3 rounded bg-info bg-opacity-10 border border-info border-opacity-25">
                <div class="fw-bold text-info"><?= count($riskAlerts['many_transfers'] ?? []) ?></div>
                <div class="small text-secondary">โอนย้ายบ่อย (≥3 ครั้ง)</div>
              </div>
            </div>
          </div>
          <?php $manyTransfers = $riskAlerts['many_transfers'] ?? []; ?>
          <?php if (!empty($manyTransfers)): ?>
          <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0">
              <thead class="table-light"><tr><th>รหัสครุภัณฑ์</th><th>จำนวนครั้งโอน</th><th></th></tr></thead>
              <tbody class="table-group-divider">
                <?php foreach (array_slice($manyTransfers, 0, 5) as $r): $asset = Asset::findOne($r['asset_id'] ?? 0); ?>
                <tr>
                  <td><?= $asset ? Html::a(Html::encode($asset->code), ['/am/equip/view-asset', 'id' => $asset->id], ['class' => 'text-primary']) : '-' ?></td>
                  <td><?= (int) ($r['transfer_count'] ?? 0) ?></td>
                  <td><?= $asset ? Html::a('ดู', ['/am/equip/view-asset', 'id' => $asset->id], ['class' => 'btn btn-sm btn-outline-secondary']) : '' ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php else: ?>
          <p class="text-muted small mb-0">ไม่มีรายการโอนย้ายบ่อยในขณะนี้</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Section 6 — Age Analysis -->
  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom d-flex align-items-center gap-2">
          <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
            <i data-lucide="calendar"></i>
          </div>
          <h6 class="text-uppercase text-secondary m-0">การวิเคราะห์อายุครุภัณฑ์</h6>
        </div>
        <div class="card-body">
          <div id="chart-age-bar" style="min-height: 260px;"></div>
        </div>
      </div>
    </div>
    <!-- Section 7 — Recent Activities -->
    <div class="col-12 col-lg-6">
      <div class="card border-0 shadow-sm h-100">
        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
          <div class="d-flex align-items-center gap-2">
            <div class="erp-icon-box bg-primary bg-opacity-10 text-primary">
              <i data-lucide="list"></i>
            </div>
            <h6 class="text-uppercase text-secondary m-0">กิจกรรมล่าสุด</h6>
          </div>
          <?= Html::a('โอนย้าย', ['/am/asset/transfer'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
        </div>
        <div class="card-body p-0">
          <ul class="nav nav-tabs px-3 pt-2 border-0" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tab-transfers">โอนย้าย</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-repairs">ส่งซ่อม</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tab-disposals">จำหน่าย</a></li>
          </ul>
          <div class="tab-content p-3">
            <div class="tab-pane fade show active" id="tab-transfers">
              <?php if (!empty($transfers)): ?>
              <ul class="list-group list-group-flush list-group-flush">
                <?php foreach (array_slice($transfers, 0, 5) as $r): $a = Asset::findOne($r['asset_id'] ?? 0); ?>
                <li class="list-group-item d-flex justify-content-between px-0">
                  <span><?= $a ? Html::encode($a->code) : '-' ?></span>
                  <small class="text-muted"><?= Yii::$app->formatter->asDatetime($r['created_at'] ?? '') ?></small>
                </li>
                <?php endforeach; ?>
              </ul>
              <?php else: ?>
              <p class="text-muted small mb-0">ยังไม่มีโอนย้ายล่าสุด</p>
              <?php endif; ?>
            </div>
            <div class="tab-pane fade" id="tab-repairs">
              <?php if (!empty($repairs)): ?>
              <ul class="list-group list-group-flush">
                <?php foreach (array_slice($repairs, 0, 5) as $r): $a = Asset::findOne($r['asset_id'] ?? 0); ?>
                <li class="list-group-item d-flex justify-content-between px-0">
                  <span><?= $a ? Html::encode($a->code) : '-' ?></span>
                  <small class="text-muted"><?= Yii::$app->formatter->asDatetime($r['created_at'] ?? '') ?></small>
                </li>
                <?php endforeach; ?>
              </ul>
              <?php else: ?>
              <p class="text-muted small mb-0">ยังไม่มีส่งซ่อมล่าสุด</p>
              <?php endif; ?>
            </div>
            <div class="tab-pane fade" id="tab-disposals">
              <?php if (!empty($disposals)): ?>
              <ul class="list-group list-group-flush">
                <?php foreach (array_slice($disposals, 0, 5) as $r): $a = Asset::findOne($r['asset_id'] ?? 0); ?>
                <li class="list-group-item d-flex justify-content-between px-0">
                  <span><?= $a ? Html::encode($a->code) : '-' ?></span>
                  <small class="text-muted"><?= Yii::$app->formatter->asDatetime($r['created_at'] ?? '') ?></small>
                </li>
                <?php endforeach; ?>
              </ul>
              <?php else: ?>
              <p class="text-muted small mb-0">ยังไม่มีจำหน่ายล่าสุด</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$healthLabelsJs = Json::encode($healthLabels);
$healthValuesJs = Json::encode($healthValues);
$replacementLabelsJs = Json::encode($replacementLabels);
$replacementCountsJs = Json::encode($replacementCounts);
$categoryLabelsJs = Json::encode($categoryLabels);
$categoryValuesJs = Json::encode($categoryValues);
$deptLabelsJs = Json::encode($deptLabels);
$deptValuesJs = Json::encode($deptValues);
$groupLabelsJs = Json::encode($groupLabels);
$groupValuesJs = Json::encode($groupValues);
$ageLabelsJs = Json::encode($ageLabels);
$ageValuesJs = Json::encode($ageValues);
$js = <<<JS
(function() {
  if (typeof ApexCharts === 'undefined') return;

  // Section 2 — Health Donut (Green / Orange / Red / Warning / Secondary)
  var healthOpt = {
    series: $healthValuesJs,
    chart: { type: 'donut', height: 280 },
    labels: $healthLabelsJs,
    colors: ['#22c55e', '#f97316', '#ef4444', '#eab308', '#6b7280'],
    legend: { position: 'bottom' },
    plotOptions: { pie: { donut: { size: '65%' } } },
    dataLabels: { enabled: true }
  };
  var healthEl = document.getElementById('chart-health-donut');
  if (healthEl) { new ApexCharts(healthEl, healthOpt).render(); }

  // Section 3 — Replacement Stacked Bar
  var repOpt = {
    series: [{ name: 'จำนวนรายการ', data: $replacementCountsJs }],
    chart: { type: 'bar', height: 280, stacked: false },
    plotOptions: { bar: { borderRadius: 6, columnWidth: '55%' } },
    xaxis: { categories: $replacementLabelsJs },
    yaxis: { title: { text: 'จำนวน' } },
    colors: ['#3b82f6'],
    dataLabels: { enabled: true }
  };
  var repEl = document.getElementById('chart-replacement-bar');
  if (repEl) { new ApexCharts(repEl, repOpt).render(); }

  // Section 4c — Group Donut
  var groupOpt = {
    series: $groupValuesJs,
    chart: { type: 'donut', height: 300 },
    labels: $groupLabelsJs,
    colors: ['#10b981', '#f59e0b', '#3b82f6', '#8b5cf6', '#64748b'],
    legend: { position: 'bottom' },
    plotOptions: { pie: { donut: { size: '60%' } } },
    dataLabels: { enabled: true }
  };
  var groupEl = document.getElementById('chart-group-donut');
  if (groupEl) { new ApexCharts(groupEl, groupOpt).render(); }

  // Section 4a — Category Horizontal Bar
  var catOpt = {
    series: [{ name: 'จำนวน', data: $categoryValuesJs }],
    chart: { type: 'bar', height: 300 },
    plotOptions: { bar: { borderRadius: 4, barHeight: '70%', horizontal: true } },
    xaxis: { categories: $categoryLabelsJs },
    dataLabels: { enabled: true },
    colors: ['#8b5cf6']
  };
  var catEl = document.getElementById('chart-category-hbar');
  if (catEl) { new ApexCharts(catEl, catOpt).render(); }

  // Section 4b — Department Bar
  var deptOpt = {
    series: [{ name: 'จำนวน', data: $deptValuesJs }],
    chart: { type: 'bar', height: 300 },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
    xaxis: { categories: $deptLabelsJs, labels: { rotate: -45, maxWidth: 120 } },
    dataLabels: { enabled: true },
    colors: ['#0ea5e9']
  };
  var deptEl = document.getElementById('chart-department-bar');
  if (deptEl) { new ApexCharts(deptEl, deptOpt).render(); }

  // Section 6 — Age Bar
  var ageOpt = {
    series: [{ name: 'จำนวน', data: $ageValuesJs }],
    chart: { type: 'bar', height: 260 },
    plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
    xaxis: { categories: $ageLabelsJs },
    yaxis: { title: { text: 'จำนวน' } },
    colors: ['#14b8a6'],
    dataLabels: { enabled: true }
  };
  var ageEl = document.getElementById('chart-age-bar');
  if (ageEl) { new ApexCharts(ageEl, ageOpt).render(); }
})();
JS;
$this->registerJs($js, View::POS_END);
?>

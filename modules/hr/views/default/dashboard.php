<?php
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\View;
use app\modules\hr\models\Organization;
use app\modules\hr\models\TeamGroup;

$this->title = 'Dashboard บุคลากร (มุมมองผู้บริหาร)';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = 'Dashboard';

$totalCount = (int)($totalCount ?? 0);
$countMale = (int)($countMale ?? 0);
$countFemale = (int)($countFemale ?? 0);
$genderRatio = $totalCount > 0 ? (round($countMale / $totalCount * 100) . ' : ' . round($countFemale / $totalCount * 100)) : '—';
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="layout-dashboard"></i>
        <span class="d-block"><?= Html::encode($this->title) ?></span>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>


<?php
$hasFilter = !empty($filterGender) || (isset($filterDepartment) && $filterDepartment !== '') || (isset($filterPositionType) && $filterPositionType !== '') || (isset($filterWorkgroup) && $filterWorkgroup !== '') || !empty($filterGen) || (isset($filterPositionName) && $filterPositionName !== '') || (isset($filterServiceBand) && $filterServiceBand !== '');
?>
<?php if ($hasFilter): ?>
<div class="card border-0 shadow-sm mb-3 border-primary border-start border-3">
    <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center gap-2">
        <span class="small fw-bold text-primary me-1">ตัวกรองปัจจุบัน:</span>
        <?php
        $parts = [];
        if (!empty($filterGender)) $parts[] = 'เพศ ' . Html::encode($filterGender);
        if (!empty($filterDepartment) && !empty($departmentLabels)) {
            $idx = null;
            foreach (($departmentCodes ?? []) as $i => $c) {
                if ((string)$c === (string)$filterDepartment) { $idx = $i; break; }
            }
            $parts[] = 'แผนก ' . ($idx !== null ? Html::encode($departmentLabels[$idx] ?? $filterDepartment) : Html::encode($filterDepartment));
        }
        if (!empty($filterPositionType) && !empty($positionTypeLabels)) {
            $ptIdx = null;
            foreach (($positionTypeCodes ?? []) as $i => $c) {
                if ((string)$c === (string)$filterPositionType) { $ptIdx = $i; break; }
            }
            $parts[] = 'ประเภทการจ้าง ' . ($ptIdx !== null ? Html::encode($positionTypeLabels[$ptIdx] ?? $filterPositionType) : Html::encode($filterPositionType));
        }
        if (!empty($filterWorkgroup) && !empty($workgroupRows)) {
            $wgName = $filterWorkgroup;
            foreach ($workgroupRows as $wr) {
                if (isset($wr['code']) && $wr['code'] == $filterWorkgroup) {
                    $wgName = $wr['name'] ?? $filterWorkgroup;
                    break;
                }
            }
            $parts[] = 'กลุ่มงาน ' . Html::encode($wgName);
        }
        if (!empty($filterGen)) $parts[] = 'ช่วงวัย ' . Html::encode($filterGen);
        if (!empty($filterPositionName) && !empty($positionNameCategories)) {
            $pnIdx = null;
            foreach (($positionNameCodes ?? []) as $i => $c) {
                if ((string)$c === (string)$filterPositionName) { $pnIdx = $i; break; }
            }
            $parts[] = 'ตำแหน่ง ' . ($pnIdx !== null ? Html::encode($positionNameCategories[$pnIdx] ?? $filterPositionName) : Html::encode($filterPositionName));
        }
        if (!empty($filterServiceBand)) $parts[] = 'ช่วงอายุงาน ' . Html::encode($filterServiceBand);
        echo implode(' · ', $parts);
        ?>
        <a href="<?= Url::to(['/hr/default/dashboard']) ?>" class="btn btn-sm btn-primary ms-auto" title="แสดงข้อมูลทั้งหมด (ยกเลิกตัวกรองจากชาร์ต)">
            <i class="bi bi-x-circle me-1"></i>ล้างตัวกรอง
        </a>
    </div>
    <div class="card-footer py-1 px-3 bg-light border-0 small text-muted">
        <i class="bi bi-info-circle me-1"></i>กดปุ่ม "ล้างตัวกรอง" เพื่อกลับไปแสดงข้อมูลทั้งหมดเหมือนก่อนกดที่ชาร์ต
    </div>
</div>
<?php endif; ?>

<!-- มุมมองผู้บริหาร สรุปหนึ่งบรรทัด -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body py-3">
        <p class="text-muted small mb-0">
            <strong>มุมมองผู้บริหาร:</strong>
            องค์กรมีบุคลากรปฏิบัติราชการ ทั้งหมด <strong><?= $totalCount ?></strong> คน
            (ชาย <?= $countMale ?> · หญิง <?= $countFemale ?> · สัดส่วน <?= $genderRatio ?>)
            กระจายใน <strong><?= (int)($numWorkgroups ?? 0) ?></strong> กลุ่มงาน
            และ <strong><?= (int)($numPositionTypes ?? 0) ?></strong> ประเภทการจ้าง
            <?php if (isset($newHiresThisYear) || isset($leftThisYear)): ?>
            · ปีนี้บรรจุใหม่ <strong><?= (int)($newHiresThisYear ?? 0) ?></strong> คน · ลาออก/สิ้นสุด <strong><?= (int)($leftThisYear ?? 0) ?></strong> คน
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted text-uppercase small d-block">จำนวนบุคลากร (ปฏิบัติราชการ)</span>
                        <h2 class="mb-0 mt-1 fw-bold"><?= $totalCount ?></h2>
                        <span class="small text-muted">ชาย : หญิง = <?= $genderRatio ?></span>
                    </div>
                    <div class="flex-shrink-0 text-primary">
                        <span class="erp-icon-box-xl"><i class="bi bi-people fs-1"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted text-uppercase small d-block">ชาย / หญิง</span>
                        <h2 class="mb-0 mt-1 fw-bold"><?= $countMale ?> / <?= $countFemale ?></h2>
                    </div>
                    <div class="flex-shrink-0 text-success opacity-75">
                        <span class="erp-icon-box-xl"><i class="bi bi-gender-ambiguous fs-1"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <a href="<?= Url::to(['/hr/organization/diagram']) ?>" class="text-decoration-none">
                            <span class="text-muted text-uppercase small d-block">ผังองค์กร / กลุ่มงาน</span>
                        </a>
                        <h2 class="mb-0 mt-1 fw-bold"><?= Organization::find()->where(['tb_name' => 'diagram'])->count('id') ?></h2>
                    </div>
                    <div class="flex-shrink-0 text-info opacity-75">
                        <span class="erp-icon-box-xl"><i class="bi bi-diagram-3 fs-1"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted text-uppercase small d-block">กลุ่ม / ทีมประสานงาน</span>
                        <h2 class="mb-0 mt-1 fw-bold"><?= TeamGroup::find()->count('id') ?></h2>
                    </div>
                    <div class="flex-shrink-0 text-warning opacity-75">
                        <span class="erp-icon-box-xl"><i class="bi bi-person-workspace fs-1"></i></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- บรรจุใหม่ / ลาออก ปีนี้ -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm border-start border-primary border-3">
            <div class="card-body">
                <span class="text-muted text-uppercase small d-block">บรรจุใหม่ปีนี้</span>
                <h2 class="mb-0 mt-1 fw-bold text-primary"><?= (int)($newHiresThisYear ?? 0) ?></h2>
                <span class="small text-muted">คน (join_date ปี <?= date('Y') ?>)</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm border-start border-secondary border-3">
            <div class="card-body">
                <span class="text-muted text-uppercase small d-block">ลาออก/สิ้นสุดปีนี้</span>
                <h2 class="mb-0 mt-1 fw-bold text-secondary"><?= (int)($leftThisYear ?? 0) ?></h2>
                <span class="small text-muted">คน (end_date ปี <?= date('Y') ?>)</span>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-6 col-md-3">
        <div class="card h-100 border-0 shadow-sm">
            <div class="card-body">
                <span class="text-muted text-uppercase small d-block">อายุงานเฉลี่ย</span>
                <h2 class="mb-0 mt-1 fw-bold"><?= isset($avgYearsService) && $avgYearsService !== null ? $avgYearsService : '—' ?></h2>
                <span class="small text-muted">ปี</span>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-12">
        <h6 class="text-muted small fw-normal text-uppercase mb-0">โครงสร้างและประชากรบุคลากร</h6>
    </div>
</div>

<!-- แถวที่ 1: สัดส่วน (Donut) — เปรียบเทียบสัดส่วนได้เร็ว -->
<?php $donutCol = !empty($positionTypeLabels) ? 'col-md-4' : 'col-md-6'; ?>
<div class="row g-3 mb-4">
    <div class="col-12 <?= $donutCol ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 small fw-normal text-muted">เพศ</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center py-3">
                <div id="dashboardGenderPie" class="w-100"></div>
            </div>
        </div>
    </div>
    <div class="col-12 <?= $donutCol ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 small fw-normal text-muted">ช่วงวัย (Generation)</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center py-3">
                <div id="dashboardGenerationPie" class="w-100"></div>
            </div>
        </div>
    </div>
    <?php if (!empty($positionTypeLabels)): ?>
    <div class="col-12 <?= $donutCol ?>">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 small fw-normal text-muted">ประเภทการจ้าง</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center py-3">
                <div id="dashboardPositionTypePie" class="w-100"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- แถวที่ 2: กลุ่มงาน × ประเภทการจ้าง — ข้อมูลหลัก ใช้พื้นที่เต็มความกว้าง -->
<?php if (!empty($workgroupRows) && !empty($positionTypeLabels)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-normal">จำแนกตามกลุ่มงานและประเภทการจ้าง</h6>
                <span class="small text-muted">คลิกแท่งเพื่อกรองตามกลุ่มงาน</span>
            </div>
            <div class="card-body pt-3 pb-2 px-3">
                <div id="dashboardWorkgroupChart"></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- แถวที่ 3: ประชากรตามช่วงอายุ (ชาย/หญิง) — ไม่แก้รูปแบบ -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-normal">ประชากรตามช่วงอายุ (ชาย/หญิง)</h6>
            </div>
            <div class="card-body pt-3 pb-2 px-3">
                <div id="dashboardAgeChart"></div>
            </div>
        </div>
    </div>
</div>

<!-- แถวที่ 4: ตำแหน่ง + ช่วงอายุงาน — คู่กัน เหมาะกับข้อมูลแบบหมวดหมู่ -->
<div class="row g-3 mb-4">
    <?php if (!empty($positionNameCategories)): ?>
    <?php $hasPositionOthers = end($positionNameCategories) === 'อื่นๆ'; ?>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-normal">จำนวนคนตามตำแหน่ง</h6>
                <span class="small text-muted">คลิกแท่งเพื่อกรอง</span>
                <?php if ($hasPositionOthers): ?>
                <span class="small text-muted d-block mt-1">แสดง 12 อันดับแรก ที่เหลือรวมเป็น อื่นๆ</span>
                <?php endif; ?>
            </div>
            <div class="card-body pt-3 pb-2 px-3">
                <div id="dashboardPositionNameChart"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <?php if (!empty($serviceBandLabels)): ?>
    <div class="col-12 col-lg-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-normal">กระจายตามช่วงอายุงาน</h6>
                <span class="small text-muted">คลิกแท่งเพื่อกรอง</span>
            </div>
            <div class="card-body pt-3 pb-2 px-3">
                <div id="dashboardServiceBandChart"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- แถวที่ 5: จำนวนบุคลากรแยกตามแผนก — แนวนอน ให้พื้นที่ตามจำนวนแผนก -->
<?php if (!empty($departmentLabels)): ?>
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent border-bottom py-2 px-3">
                <h6 class="mb-0 fw-normal">จำนวนบุคลากรแยกตามแผนก</h6>
                <span class="small text-muted">คลิกแท่งเพื่อกรอง</span>
            </div>
            <div class="card-body pt-3 pb-2 px-3">
                <div id="dashboardDepartmentChart"></div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$positionTypeLabelsJson = Json::encode($positionTypeLabels ?? []);
$positionTypeCountsJson = Json::encode($positionTypeCounts ?? []);
$workgroupRowsJson = Json::encode($workgroupRows ?? []);
$ageCategoriesJson = Json::encode($ageCategories ?? []);
$ageMaleJson = Json::encode($ageMale ?? []);
$ageFemaleJson = Json::encode($ageFemale ?? []);
$positionNameCategoriesJson = Json::encode($positionNameCategories ?? []);
$positionNameValuesJson = Json::encode($positionNameValues ?? []);
$positionNameCodesJson = Json::encode($positionNameCodes ?? []);
$genCounts = $genCounts ?? [];
$genLabelsJson = Json::encode(array_keys($genCounts));
$genSeriesJson = Json::encode(array_values($genCounts));
$departmentLabelsJson = Json::encode($departmentLabels ?? []);
$departmentValuesJson = Json::encode($departmentValues ?? []);
$serviceBandLabelsJson = Json::encode($serviceBandLabels ?? []);
$serviceBandValuesJson = Json::encode($serviceBandValues ?? []);
$dashboardUrl = $dashboardUrl ?? Url::to(['/hr/default/dashboard']);
$positionTypeCodesJson = Json::encode($positionTypeCodes ?? []);
$departmentCodesJson = Json::encode($departmentCodes ?? []);
?>
<script>
window.__hrDashboard = {
  baseUrl: <?= Json::encode($dashboardUrl) ?>,
  filter: {
    gender: <?= Json::encode($filterGender ?? '') ?>,
    department: <?= Json::encode($filterDepartment ?? '') ?>,
    position_type: <?= Json::encode($filterPositionType ?? '') ?>,
    workgroup: <?= Json::encode($filterWorkgroup ?? '') ?>,
    gen: <?= Json::encode($filterGen ?? '') ?>,
    position_name: <?= Json::encode($filterPositionName ?? '') ?>,
    service_band: <?= Json::encode($filterServiceBand ?? '') ?>
  },
  positionTypeCodes: <?= $positionTypeCodesJson ?>,
  departmentCodes: <?= $departmentCodesJson ?>,
  totalCount: <?= (int) $totalCount ?>,
  countMale: <?= (int) $countMale ?>,
  countFemale: <?= (int) $countFemale ?>,
  positionTypeLabels: <?= $positionTypeLabelsJson ?>,
  positionTypeCounts: <?= $positionTypeCountsJson ?>,
  workgroupRows: <?= $workgroupRowsJson ?>,
  ageCategories: <?= $ageCategoriesJson ?>,
  ageMale: <?= $ageMaleJson ?>,
  ageFemale: <?= $ageFemaleJson ?>,
  positionNameCategories: <?= $positionNameCategoriesJson ?>,
  positionNameValues: <?= $positionNameValuesJson ?>,
  positionNameCodes: <?= $positionNameCodesJson ?>,
  genLabels: <?= $genLabelsJson ?>,
  genSeries: <?= $genSeriesJson ?>,
  departmentLabels: <?= $departmentLabelsJson ?>,
  departmentValues: <?= $departmentValuesJson ?>,
  serviceBandLabels: <?= $serviceBandLabelsJson ?>,
  serviceBandValues: <?= $serviceBandValuesJson ?>
};
</script>
<?php
$js = <<<'JS'
(function() {
  var d = window.__hrDashboard;
  if (!d) return;
  var totalCount = Number(d.totalCount) || 0;
  var ensureArr = function(v) { return Array.isArray(v) ? v : []; };
  d.positionTypeLabels = ensureArr(d.positionTypeLabels);
  d.positionTypeCounts = ensureArr(d.positionTypeCounts);
  d.workgroupRows = ensureArr(d.workgroupRows);
  d.ageCategories = ensureArr(d.ageCategories);
  d.ageMale = ensureArr(d.ageMale);
  d.ageFemale = ensureArr(d.ageFemale);
  d.positionNameCategories = ensureArr(d.positionNameCategories);
  d.positionNameValues = ensureArr(d.positionNameValues);
  d.genLabels = ensureArr(d.genLabels);
  d.genSeries = ensureArr(d.genSeries);
  d.departmentLabels = ensureArr(d.departmentLabels);
  d.departmentValues = ensureArr(d.departmentValues);
  d.serviceBandLabels = ensureArr(d.serviceBandLabels);
  d.serviceBandValues = ensureArr(d.serviceBandValues);
  d.positionTypeCodes = ensureArr(d.positionTypeCodes);
  d.departmentCodes = ensureArr(d.departmentCodes);
  d.positionNameCodes = ensureArr(d.positionNameCodes);
  if (typeof d.filter === 'undefined') d.filter = {};

  function applyFilter(key, value) {
    var params = {};
    if (d.filter.gender) params.gender = d.filter.gender;
    if (d.filter.department) params.department = d.filter.department;
    if (d.filter.position_type) params.position_type = d.filter.position_type;
    if (d.filter.workgroup) params.workgroup = d.filter.workgroup;
    if (d.filter.gen) params.gen = d.filter.gen;
    if (d.filter.position_name) params.position_name = d.filter.position_name;
    if (d.filter.service_band) params.service_band = d.filter.service_band;
    if (key && value !== undefined && value !== '') params[key] = value;
    else if (key) delete params[key];
    var q = Object.keys(params).map(function(k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); }).join('&');
    window.location = d.baseUrl + (q ? '?' + q : '');
  }

  var el = document.querySelector("#dashboardWorkgroupChart");
  if (el && d.workgroupRows.length && d.positionTypeLabels.length) {
    var wgSeries = d.workgroupRows.map(function(r) { return { name: r.name || '', data: Array.isArray(r.data) ? r.data : [] }; });
    var wgCodes = d.workgroupRows.map(function(r) { return r.code || ''; });
    new ApexCharts(el, {
      series: wgSeries,
      chart: { type: 'bar', height: 380, events: { dataPointSelection: function(e, chart, opts) {
        var wcode = wgCodes[opts.seriesIndex];
        if (wcode) applyFilter('workgroup', wcode);
      } } },
      plotOptions: { bar: { horizontal: false, columnWidth: '70%', endingShape: 'rounded', borderRadius: 4 } },
      dataLabels: { enabled: true, formatter: function(v) { return v > 0 ? v : ''; } },
      stroke: { show: true, width: 2, colors: ['transparent'] },
      xaxis: { categories: d.positionTypeLabels, labels: { maxWidth: 120, rotate: -45 } },
      yaxis: { title: { text: 'จำนวนคน' }, tickAmount: 6 },
      fill: { opacity: 1 },
      tooltip: { y: { formatter: function(v) { return v + ' คน'; } } },
      legend: { position: 'top', horizontalAlign: 'left' },
      grid: { padding: { left: 8, right: 8 } }
    }).render();
  }

  el = document.querySelector("#dashboardAgeChart");
  if (el) {
    new ApexCharts(el, {
      series: [{ name: 'ชาย', data: d.ageMale }, { name: 'หญิง', data: d.ageFemale }],
      chart: { type: 'bar', height: 340, stacked: true },
      colors: ['#008FFB', '#FF4560'],
      plotOptions: { bar: { borderRadius: 5, horizontal: true, barHeight: '80%' } },
      dataLabels: { enabled: true, formatter: function(v) { return (totalCount ? Math.abs(Math.round(v * 100 / totalCount)) : 0) + '%'; } },
      stroke: { width: 1, colors: ['#fff'] },
      xaxis: { categories: d.ageCategories },
      grid: { xaxis: { lines: { show: false } }, padding: { left: 8, right: 8 } }
    }).render();
  }

  el = document.querySelector("#dashboardPositionNameChart");
  if (el && d.positionNameCategories.length) {
    var pnCount = d.positionNameCategories.length;
    var pnHeight = Math.max(320, Math.min(420, pnCount * 32));
    new ApexCharts(el, {
      series: [{ name: 'จำนวนคน', data: d.positionNameValues }],
      chart: { type: 'bar', height: pnHeight, events: { dataPointSelection: function(e, chart, opts) {
        var code = d.positionNameCodes[opts.dataPointIndex];
        if (code != null && code !== '') applyFilter('position_name', code);
      } } },
      plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '78%', dataLabels: { position: 'top' } } },
      dataLabels: { enabled: true },
      xaxis: { categories: d.positionNameCategories, tickAmount: 6 },
      yaxis: { labels: { maxWidth: 200 } },
      grid: { padding: { left: 8, right: 8 } },
      colors: ['#0d6efd']
    }).render();
  }

  el = document.querySelector("#dashboardGenderPie");
  if (el) {
    new ApexCharts(el, {
      chart: { type: 'donut', height: 260, events: { dataPointSelection: function(e, chart, opts) {
        var g = ['ชาย', 'หญิง'][opts.dataPointIndex];
        if (g) applyFilter('gender', g);
      } } },
      labels: ['ชาย', 'หญิง'],
      series: [Number(d.countMale) || 0, Number(d.countFemale) || 0],
      stroke: { width: 0 },
      legend: { position: 'bottom', horizontalAlign: 'center' },
      plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'ทั้งหมด' } } } } }
    }).render();
  }

  el = document.querySelector("#dashboardGenerationPie");
  if (el && d.genLabels.length && d.genSeries.length) {
    new ApexCharts(el, {
      chart: { type: 'donut', height: 260, events: { dataPointSelection: function(e, chart, opts) {
        var gen = d.genLabels[opts.dataPointIndex];
        if (gen) applyFilter('gen', gen);
      } } },
      labels: d.genLabels,
      series: d.genSeries,
      stroke: { width: 0 },
      legend: { position: 'bottom', horizontalAlign: 'center' },
      plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'ทั้งหมด' } } } } }
    }).render();
  }

  el = document.querySelector("#dashboardPositionTypePie");
  if (el && d.positionTypeLabels.length && d.positionTypeCounts.length) {
    new ApexCharts(el, {
      chart: { type: 'donut', height: 260, events: { dataPointSelection: function(e, chart, opts) {
        var code = d.positionTypeCodes[opts.dataPointIndex];
        if (code) applyFilter('position_type', code);
      } } },
      labels: d.positionTypeLabels,
      series: d.positionTypeCounts,
      stroke: { width: 0 },
      legend: { position: 'bottom', horizontalAlign: 'center' },
      plotOptions: { pie: { donut: { size: '70%', labels: { show: true, total: { show: true, label: 'ทั้งหมด' } } } } }
    }).render();
  }

  el = document.querySelector("#dashboardDepartmentChart");
  if (el && d.departmentLabels.length) {
    var deptCount = d.departmentLabels.length;
    var deptHeight = Math.min(520, Math.max(280, deptCount * 32));
    new ApexCharts(el, {
      series: [{ name: 'จำนวนคน', data: d.departmentValues }],
      chart: { type: 'bar', height: deptHeight, events: { dataPointSelection: function(e, chart, opts) {
        var code = d.departmentCodes[opts.dataPointIndex];
        if (code != null && code !== '') applyFilter('department', code);
      } } },
      plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '75%', dataLabels: { position: 'top' } } },
      dataLabels: { enabled: true },
      xaxis: { categories: d.departmentLabels, tickAmount: 6 },
      yaxis: { labels: { maxWidth: 200 } },
      grid: { padding: { left: 8, right: 8 } },
      colors: ['#0d6efd']
    }).render();
  }

  el = document.querySelector("#dashboardServiceBandChart");
  if (el && d.serviceBandLabels.length) {
    new ApexCharts(el, {
      series: [{ name: 'จำนวนคน', data: d.serviceBandValues }],
      chart: { type: 'bar', height: 300, events: { dataPointSelection: function(e, chart, opts) {
        var label = d.serviceBandLabels[opts.dataPointIndex];
        if (label) applyFilter('service_band', label);
      } } },
      plotOptions: { bar: { borderRadius: 4, columnWidth: '65%', dataLabels: { position: 'top' } } },
      dataLabels: { enabled: true },
      xaxis: { categories: d.serviceBandLabels, labels: { rotate: -25, maxWidth: 100 } },
      yaxis: { tickAmount: 6 },
      grid: { padding: { left: 8, right: 8 } },
      colors: ['#198754']
    }).render();
  }
})();
JS;
$this->registerJS($js, View::POS_END);
?>

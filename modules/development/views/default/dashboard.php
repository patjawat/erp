<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use app\components\AppHelper;
use app\components\ThaiDateHelper;
use app\modules\development\models\Development;

/** @var yii\web\View $this */
/** @var int $thaiYear */
/** @var array $summary */
/** @var array $yearlySummary */
/** @var array $activityType */
/** @var array $monthlyTrend */
/** @var array $budgetByType */
/** @var array $participationByDept */
/** @var array $listSummaryMonth */
/** @var array $yearlyCompare */
/** @var array $statusSummary */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'ภาพรวมอบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('action'); ?>
<?= $this->render('_menu', ['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>
<?php

$listThaiYear = Development::find()->select('thai_year')->distinct()->orderBy(['thai_year' => SORT_DESC])->column();
if (empty($listThaiYear)) {
    $listThaiYear = [(int) date('Y') + 543];
}
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 w-100">
    <h4 class="fw-bold text-body mb-0 d-flex align-items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        <?= Html::encode($this->title) ?>
    </h4>
    <div class="d-flex align-items-center gap-2">
        <a href="<?= Url::to(array_merge(['/development/default/export-excel'], ['thai_year' => $thaiYear])) ?>" class="btn btn-success rounded-3">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                <polyline points="7 10 12 15 17 10"></polyline>
                <line x1="12" x2="12" y1="15" y2="3"></line>
            </svg>
            <span class="d-none d-sm-inline ms-1">Export Excel</span>
        </a>
        <div class="dropdown">
            <button class="btn btn-light border rounded-3 dropdown-toggle" type="button" id="dropdownYear" data-bs-toggle="dropdown" aria-expanded="false">
                ปีงบประมาณ <?= (int) $thaiYear ?>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownYear">
                <?php foreach ($listThaiYear as $y): ?>
                <li>
                    <a class="dropdown-item" href="<?= Url::to(['/development/default/dashboard', 'thai_year' => $y]) ?>">
                        ปี <?= (int) $y ?>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
<?php $this->endBlock(); ?>

<div class="container-fluid py-3">
    <?php
    $isAdmin = Yii::$app->user->can('hr') || Yii::$app->user->can('admin');
    ?>
    <div class="alert border-0 mb-4 <?= $isAdmin ? 'alert-primary bg-primary bg-opacity-10 border-primary border-opacity-25' : 'alert-secondary bg-secondary bg-opacity-10 border-secondary border-opacity-25' ?> rounded-3" role="alert">
        <div class="d-flex align-items-center gap-2">
            <span class="small mb-0">
                <?php if ($isAdmin): ?>
                <strong>ผู้ดูแลระบบ:</strong> ดูภาพรวม สถิติ และลิงก์ไปทะเบียนทั้งหมด / รายการรออนุมัติได้จากเมนูด้านบน
                <?php else: ?>
                <strong>ผู้ใช้งาน:</strong> ดูภาพรวมและสถิติได้ที่นี่ สร้างรายการอบรม/ประชุม/ดูงานจากปุ่ม <strong>สร้างใหม่</strong> หรือ <strong>รายการของฉัน</strong>
                <?php endif; ?>
            </span>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-start border-start border-4 border-primary rounded-start">
                    <div>
                        <p class="text-muted small mb-1">จำนวนการอบรม/ประชุม/ดูงานทั้งหมด</p>
                        <h3 class="fs-2 fw-bold text-body mb-0"><?= number_format($yearlySummary['total_count']) ?></h3>
                        <p class="small mt-1 mb-0"><?= $yearlySummary['price_status'] ?> <?= $yearlySummary['count_percent_change'] ?>% จากปีที่แล้ว</p>
                    </div>
                    <div class="text-primary opacity-75">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-start border-start border-4 border-success rounded-start">
                    <div>
                        <p class="text-muted small mb-1">งบประมาณที่ใช้ (บาท)</p>
                        <h3 class="fs-2 fw-bold text-body mb-0"><?= number_format($yearlySummary['total_price'], 0) ?></h3>
                        <p class="small mt-1 mb-0">คิดเป็น <?= $yearlySummary['price_percent_change'] ?>% ของงบประมาณปีที่แล้ว</p>
                    </div>
                    <div class="text-success opacity-75">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body d-flex justify-content-between align-items-start border-start border-4 border-info rounded-start">
                    <div>
                        <p class="text-muted small mb-1">บุคลากรที่ได้รับการพัฒนา</p>
                        <h3 class="fs-2 fw-bold text-body mb-0"><?= number_format($yearlySummary['emp_count']) ?></h3>
                        <p class="small mt-1 mb-0">คิดเป็น <?= $yearlySummary['emp_percent'] ?>% ของบุคลากรทั้งหมด</p>
                    </div>
                    <div class="text-info opacity-75">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart เทียบรายปี จำนวนการอบรม/ประชุม/ดูงานทั้งหมด -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <h5 class="text-body mb-3">เทียบรายปี จำนวนการอบรม/ประชุม/ดูงานทั้งหมด</h5>
                    <div id="yearlyCompareChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <h5 class="text-body mb-3">สรุปสถานะ</h5>
                    <div id="statusSummaryChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <h5 class="text-body mb-3">สัดส่วนประเภทการอบรม/ประชุม/ดูงาน</h5>
                    <div id="activityTypeChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <h5 class="text-body mb-3">แนวโน้มการอบรม/ประชุม/ดูงานรายเดือน</h5>
                    <div id="monthlyTrendChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-body mb-3">การใช้งบประมาณตามประเภทกิจกรรม</h5>
                    <div id="budgetByTypeChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <h5 class="fw-bold text-body mb-3">การเข้าร่วมกิจกรรมตามหน่วยงาน</h5>
                    <div id="departmentChart" style="height: 320px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- สรุปข้อมูลการอบรมประจำปีงบประมาณ -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-transparent border-0 py-3">
            <h5 class="fw-bold text-body mb-0">สรุปข้อมูลการอบรมประจำปีงบประมาณ <?= (int) $thaiYear ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="small fw-semibold text-start">ประเภทการอบรม</th>
                            <th class="small fw-semibold text-center">ต.ค.</th>
                            <th class="small fw-semibold text-center">พ.ย.</th>
                            <th class="small fw-semibold text-center">ธ.ค.</th>
                            <th class="small fw-semibold text-center">ม.ค.</th>
                            <th class="small fw-semibold text-center">ก.พ.</th>
                            <th class="small fw-semibold text-center">มี.ค.</th>
                            <th class="small fw-semibold text-center">เม.ย.</th>
                            <th class="small fw-semibold text-center">พ.ค.</th>
                            <th class="small fw-semibold text-center">มิ.ย.</th>
                            <th class="small fw-semibold text-center">ก.ค.</th>
                            <th class="small fw-semibold text-center">ส.ค.</th>
                            <th class="small fw-semibold text-center">ก.ย.</th>
                        </tr>
                    </thead>
                    <tbody class="align-middle table-group-divider">
                        <?php foreach ($listSummaryMonth as $row): ?>
                        <tr>
                            <td class="text-start"><?= Html::encode($row['title']) ?></td>
                            <td class="text-center"><?= (int) $row['m10'] ?></td>
                            <td class="text-center"><?= (int) $row['m11'] ?></td>
                            <td class="text-center"><?= (int) $row['m12'] ?></td>
                            <td class="text-center"><?= (int) $row['m1'] ?></td>
                            <td class="text-center"><?= (int) $row['m2'] ?></td>
                            <td class="text-center"><?= (int) $row['m3'] ?></td>
                            <td class="text-center"><?= (int) $row['m4'] ?></td>
                            <td class="text-center"><?= (int) $row['m5'] ?></td>
                            <td class="text-center"><?= (int) $row['m6'] ?></td>
                            <td class="text-center"><?= (int) $row['m7'] ?></td>
                            <td class="text-center"><?= (int) $row['m8'] ?></td>
                            <td class="text-center"><?= (int) $row['m9'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- กิจกรรมล่าสุด (แสดงแบบการ์ดเหมือนรายการทั้งหมด) -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-transparent border-0 py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h5 class="fw-bold text-body mb-0">กิจกรรมล่าสุด</h5>
            <?= Html::a('ดูทั้งหมด', ['/development/default/list', 'thai_year' => $thaiYear], ['class' => 'btn btn-outline-primary rounded-3']) ?>
        </div>
        <div class="card-body">
            <div class="development-activity-cards development-dashboard-cards">
                <?php foreach ($dataProvider->getModels() as $item): ?>
                <?php
                    $emp = $item->emp;
                    $avatarUrl = $emp && method_exists($emp, 'ShowAvatar') ? $emp->ShowAvatar() : null;
                    $requesterName = $emp ? trim(($emp->fname ?? '') . ' ' . ($emp->lname ?? '')) : (string) $item->emp_id;
                    if ($requesterName === '') {
                        $requesterName = '-';
                    }
                    $initials = mb_strlen($requesterName) >= 2 ? mb_substr($requesterName, 0, 2) : ($requesterName !== '-' ? mb_substr($requesterName, 0, 1) : '?');
                    $typeTitle = $item->developmentType ? $item->developmentType->title : '-';
                    $timeAgo = !empty($item->created_at) ? (AppHelper::timeDifference($item->created_at) . 'ที่ผ่านมา') : '';
                ?>
                <div class="card border-0 shadow-sm rounded-4 mb-3 development-activity-card">
                    <div class="card-body p-4">
                        <div class="d-flex gap-4 align-items-start">
                            <div class="development-card-avatar flex-shrink-0">
                                <?php if ($avatarUrl): ?>
                                <img src="<?= Html::encode($avatarUrl) ?>" alt="" class="rounded-4" width="72" height="72" style="object-fit: cover;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="development-card-avatar-initials rounded-4" style="display: none;" aria-hidden="true"><?= Html::encode($initials) ?></div>
                                <?php else: ?>
                                <div class="development-card-avatar-initials rounded-4"><?= Html::encode($initials) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <h5 class="text-primary mb-0 development-card-topic"><?= Html::a(Html::encode($item->topic), ['/development/default/view', 'id' => $item->id], ['class' => 'text-primary text-decoration-none']) ?></h5>
                                    <?php if ($timeAgo !== ''): ?>
                                    <span class="text-muted small flex-shrink-0"><i class="bi bi-clock me-1"></i><?= Html::encode($timeAgo) ?></span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-muted small mb-2"><?= Html::encode($requesterName) ?></p>
                                <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                    <span class="badge bg-light text-body border border-1 border-secondary border-opacity-25 rounded-pill px-3 py-2"><?= Html::encode($typeTitle) ?></span>
                                    <?php echo $item->getStatusHtml(); ?>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
                                    <span><i class="bi bi-calendar3 me-1"></i> <?= ThaiDateHelper::formatThaiDate($item->date_start, 'short') ?> – <?= ThaiDateHelper::formatThaiDate($item->date_end, 'short') ?></span>
                                    <?php $provinceName = isset($item->data_json['province_name']) ? trim((string) $item->data_json['province_name']) : ''; if ($provinceName !== ''): ?>
                                    <span><i class="bi bi-geo-alt me-1"></i> จังหวัดที่ไป: <?= Html::encode($provinceName) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    <?= Html::a('<i class="bi bi-eye me-1"></i> ดูรายละเอียด', ['/development/default/view', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-primary rounded-pill',
                                        'title' => 'ดูรายละเอียด',
                                    ]) ?>
                                    <?= Html::a('<i class="bi bi-printer me-1"></i> พิมพ์ใบขอไปราชการ', ['/development/default/print-official', 'id' => $item->id], [
                                        'class' => 'btn btn-sm btn-outline-secondary rounded-pill',
                                        'target' => '_blank',
                                        'title' => 'พิมพ์ใบขอไปราชการ',
                                    ]) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <?php if ($dataProvider->getCount() === 0): ?>
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body py-5 text-center text-muted">
                        <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                        <p class="mb-0">ยังไม่มีข้อมูลกิจกรรมในปีนี้</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            <?php if ($dataProvider->getCount() > 0): ?>
            <div class="d-flex justify-content-center mt-3">
                <?= \yii\bootstrap5\LinkPager::widget(['pagination' => $dataProvider->pagination]) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.development-dashboard-cards .development-card-avatar {
    width: 72px;
    height: 72px;
}
.development-dashboard-cards .development-card-avatar img,
.development-dashboard-cards .development-card-avatar-initials {
    width: 72px;
    height: 72px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff;
    background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
}
.development-dashboard-cards .development-activity-card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0,0,0,.08) !important;
}
</style>
<?php
$activityTypeLabels = Json::encode($activityType['labels'] ?? []);
$activityTypeSeries = Json::encode($activityType['series'] ?? []);
$monthlyTrendSeries = Json::encode($monthlyTrend['series'] ?? []);
$monthlyTrendCategories = Json::encode($monthlyTrend['categories'] ?? []);
$budgetLabels = Json::encode($budgetByType['labels'] ?? []);
$budgetSeries = Json::encode($budgetByType['series'] ?? []);
$deptLabels = Json::encode($participationByDept['labels'] ?? []);
$deptSeries = Json::encode($participationByDept['series'] ?? []);
$yearlyCompareCategories = Json::encode($yearlyCompare['categories'] ?? []);
$yearlyCompareSeries = Json::encode($yearlyCompare['series'] ?? []);
$statusSummaryLabels = Json::encode($statusSummary['labels'] ?? []);
$statusSummarySeries = Json::encode($statusSummary['series'] ?? []);
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (typeof ApexCharts === 'undefined') return;
    var colors = ['#0d6efd', '#198754', '#6f42c1', '#fd7e14', '#d63384', '#6c757d'];

    if (document.querySelector('#statusSummaryChart') && <?= $statusSummarySeries ?>.length) {
        var statusOpt = {
            series: <?= $statusSummarySeries ?>,
            chart: { type: 'donut', height: 320, fontFamily: 'Sarabun, sans-serif' },
            labels: <?= $statusSummaryLabels ?>,
            colors: ['#0d6efd', '#198754', '#fd7e14', '#dc3545', '#6c757d'],
            plotOptions: { pie: { donut: { size: '55%' } } },
            legend: { position: 'bottom' },
            dataLabels: { enabled: true, formatter: function(v, opts) { var t = opts.w.globals.series[opts.seriesIndex]; return t ? t + ' รายการ' : ''; } }
        };
        new ApexCharts(document.querySelector('#statusSummaryChart'), statusOpt).render();
    }

    if (document.querySelector('#yearlyCompareChart') && <?= $yearlyCompareSeries ?>.length) {
        var yearlyOpt = {
            series: [{ name: 'จำนวนกิจกรรม', data: <?= $yearlyCompareSeries ?> }],
            chart: { type: 'bar', height: 320, fontFamily: 'Sarabun, sans-serif', toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
            xaxis: { categories: <?= $yearlyCompareCategories ?>, title: { text: 'ปีงบประมาณ' } },
            yaxis: { title: { text: 'จำนวน' }, labels: { formatter: function(v) { return Number(v).toLocaleString(); } } },
            colors: ['#0d6efd'],
            dataLabels: { enabled: true }
        };
        new ApexCharts(document.querySelector('#yearlyCompareChart'), yearlyOpt).render();
    }

    if (document.querySelector('#activityTypeChart') && <?= $activityTypeSeries ?>.length) {
        var donutOpt = {
            series: <?= $activityTypeSeries ?>,
            chart: { type: 'donut', height: 320, fontFamily: 'Sarabun, sans-serif' },
            labels: <?= $activityTypeLabels ?>,
            colors: colors,
            plotOptions: { pie: { donut: { size: '55%' } } },
            legend: { position: 'bottom' },
            dataLabels: { enabled: true, formatter: function(v) { return v ? v.toFixed(1) + '%' : ''; } }
        };
        new ApexCharts(document.querySelector('#activityTypeChart'), donutOpt).render();
    }

    if (document.querySelector('#monthlyTrendChart') && <?= $monthlyTrendSeries ?>.length) {
        var lineOpt = {
            series: <?= $monthlyTrendSeries ?>,
            chart: { type: 'area', height: 320, stacked: true, fontFamily: 'Sarabun, sans-serif', toolbar: { show: false } },
            xaxis: { categories: <?= $monthlyTrendCategories ?> },
            colors: colors,
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            fill: { type: 'solid', opacity: 0.6 },
            legend: { position: 'top', horizontalAlign: 'left' }
        };
        new ApexCharts(document.querySelector('#monthlyTrendChart'), lineOpt).render();
    }

    if (document.querySelector('#budgetByTypeChart') && <?= $budgetSeries ?>.length) {
        var barOpt = {
            series: [{ name: 'งบประมาณ (บาท)', data: <?= $budgetSeries ?> }],
            chart: { type: 'bar', height: 320, fontFamily: 'Sarabun, sans-serif', toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, horizontal: false, columnWidth: '60%' } },
            xaxis: { categories: <?= $budgetLabels ?> },
            colors: ['#0d6efd'],
            dataLabels: { enabled: true, formatter: function(v) { return v ? Number(v).toLocaleString() : ''; } },
            yaxis: { labels: { formatter: function(v) { return Number(v).toLocaleString(); } } }
        };
        new ApexCharts(document.querySelector('#budgetByTypeChart'), barOpt).render();
    }

    if (document.querySelector('#departmentChart') && <?= $deptSeries ?>.length) {
        var deptBarOpt = {
            series: [{ name: 'จำนวนคน', data: <?= $deptSeries ?> }],
            chart: { type: 'bar', height: 320, fontFamily: 'Sarabun, sans-serif', toolbar: { show: false } },
            plotOptions: { bar: { borderRadius: 4, horizontal: true, barHeight: '70%' } },
            xaxis: { categories: <?= $deptLabels ?> },
            colors: ['#198754'],
            dataLabels: { enabled: true }
        };
        new ApexCharts(document.querySelector('#departmentChart'), deptBarOpt).render();
    }
});
</script>

<?php

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\Pjax;
use app\components\AppHelper;
use yii\bootstrap5\LinkPager;
use app\components\SiteHelper;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ข้อทูลสุขภาพ';
$this->params['breadcrumbs'][] = ['label' => 'บุคลากร', 'url' => ['/me']];
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <i data-lucide="scan-heart"></i>
        <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?= $this->render('@app/modules/hr/menu', ['active' => 'health'])
?>
<?php $this->endBlock(); ?>

<style>
    .kpi-card {
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
    }
</style>
</head>

<body>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center bg-white p-3 rounded-3 shadow-sm border mb-4">
            <h2 class="h5 mb-0 text-dark">Executive Health Dashboard</h2>
            <div class="d-flex align-items-center gap-1">
                <div class="small text-muted">อัปเดตล่าสุด: 12 ต.ค. 2566</div>
                <?php echo $this->render('_search_dashboard', ['model' => $searchModel]); ?>

            </div>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title">กระจายตัวของ BMI</h3>
                        <div id="bmiPieChart"></div>
                        <div class="row g-2 mt-3">
                            <?php foreach ($searchModel->healthSummary()['details'] as $resultBmi): ?>
                                <div class="col-6 small text-muted">
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge bg-<?= $resultBmi['color'] ?> bg-opacity-10 text-<?= $resultBmi['color'] ?> border border-<?= $resultBmi['color'] ?>-subtle rounded-pill fw-medium px-2 py-1" style="width: 10px; height: 15px; border-radius: 50%; display: inline-block;"></span> <?= $resultBmi['label'] ?> : <?= $resultBmi['count'] ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-body">
                        <h3 class="card-title">แนวโน้มระดับความเสี่ยงรายเดือน</h3>
                        <div id="riskBarChart"></div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        // echo "<pre>";
        // print_r($searchModel->healthSummary());
        // echo "</pre>";

        ?>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">ตัวชี้วัดสุขภาพสำคัญ (KPIs)</h3>
                        <div class="row g-3">
                            <div class="col-sm-6 col-lg-3">
                                <div class="kpi-card" style="background-color: #eff6ff;">
                                    <div class="h3 mb-0" style="color: #1d4ed8;"><?= $searchModel->healthSummary()['percent_screened'] ?>%</div>
                                    <div class="small" style="color: #2563eb;">อัตราการคัดกรอง</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="kpi-card" style="background-color: #f0fdf4;">
                                    <div class="h3 mb-0" style="color: #15803d;"><?php echo $searchModel->healthSummary()['details'][1]['percent'] ?>%</div>
                                    <div class="small" style="color: #16a34a;">BMI ปกติ</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="kpi-card" style="background-color: #fef2f2;">
                                    <div class="h3 mb-0" style="color: #b91c1c;">12%</div>
                                    <div class="small" style="color: #dc2626;">ผู้ป่วย NCD รายใหม่</div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-lg-3">
                                <div class="kpi-card" style="background-color: #fefce8;">
                                    <div class="h3 mb-0" style="color: #a16207;">68%</div>
                                    <div class="small" style="color: #ca8a04;">ออกกำลังกายเพียงพอ</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php

    $js = <<< JS
    // --- BMI Pie Chart ---
    const bmiOptions = {
        series: [450, 300, 200, 50],
        chart: {
            type: 'donut',
            height: 280
        },
        labels: ['ปกติ', 'น้ำหนักเกิน', 'อ้วน', 'น้ำหนักน้อย'],
        colors: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6'],
        legend: {
            show: false
        },
        dataLabels: {
            enabled: false
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%'
                }
            }
        }
    };
    new ApexCharts(document.querySelector("#bmiPieChart"), bmiOptions).render();

    // --- Risk Level Bar Chart ---
    const riskOptions = {
        series: [{
            name: 'เสี่ยงต่ำ',
            data: [400, 420, 380, 450] // คำนวณจากความสูงใน SVG (ม.ค.-เม.ย.)
        }, {
            name: 'เสี่ยงกลาง',
            data: [240, 220, 260, 210]
        }, {
            name: 'เสี่ยงสูง',
            data: [100, 90, 110, 80]
        }],
        chart: {
            type: 'bar',
            height: 250,
            toolbar: { show: false }
        },
        colors: ['#10b981', '#f59e0b', '#ef4444'],
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '55%',
            },
        },
        dataLabels: {
            enabled: false
        },
        xaxis: {
            categories: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.'],
        },
        legend: {
            position: 'bottom'
        },
        grid: {
            strokeDashArray: 3
        }
    };
    new ApexCharts(document.querySelector("#riskBarChart"), riskOptions).render();
JS;
    $this->registerJs($js, View::POS_END);
    ?>
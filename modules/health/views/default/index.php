<?php

use yii\web\View;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\EmployeesSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'ภาพรวม';
$this->params['breadcrumbs'][] = ['label' => 'ข้อมูลสุขภาพ', 'url' => ['/me']];
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
<?= $this->render('@app/modules/health/menu', ['active' => 'dashboard'])
?>
<?php $this->endBlock(); ?>


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
                        <div class="rounded-3 p-3" style="background-color: #eff6ff;">
                            <div class="h3 mb-0" style="color: #1d4ed8;"><?php // $searchModel->healthSummary()['percent_screened'] 
                                                                            ?>%</div>
                            <div class="small" style="color: #2563eb;">อัตราการคัดกรอง</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="rounded-3 p-3" style="background-color: #f0fdf4;">
                            <div class="h3 mb-0" style="color: #15803d;"><?php // $searchModel->healthSummary()['details'][1]['percent'] 
                                                                            ?>%</div>
                            <div class="small" style="color: #16a34a;">BMI ปกติ</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="rounded-3 p-3" style="background-color: #fef2f2;">
                            <div class="h3 mb-0" style="color: #b91c1c;">12%</div>
                            <div class="small" style="color: #dc2626;">ผู้ป่วย NCD รายใหม่</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="rounded-3 p-3" style="background-color: #fefce8;">
                            <div class="h3 mb-0" style="color: #a16207;">68%</div>
                            <div class="small" style="color: #ca8a04;">ออกกำลังกายเพียงพอ</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<div class="row g-4">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4">อัตราการเข้าตรวจสุขภาพแยกตามหน่วยงาน</h5>
                <div id="deptChart"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body">
                <h5 class="fw-bold mb-4">สรุปประวัติการเจ็บป่วย</h5>
                <div id="diseaseChart"></div>
            </div>
        </div>
    </div>
</div>


<?php
$bmiSeries = json_encode($bmiData['series']);
//อัตราการเข้าตรวจสุขภาพแยกตามหน่วยงาน
$deptPending = json_encode($stats['pending']);
$deptSuccess = json_encode($stats['success']);
$deptCategories = json_encode($stats['categories']);

$js = <<< JS
    // --- BMI Pie Chart ---
const bmiOptions = {
    // ดึงค่าจาก PHP มาใส่ใน Series
    series: $bmiSeries,
    chart: {
        type: 'donut',
        height: 280,
        fontFamily: 'Sarabun, sans-serif'
    },
    labels: ['ปกติ', 'ท้วม/เริ่มอ้วน', 'อ้วน (เสี่ยง)', 'น้ำหนักน้อย'],
    colors: ['#10b981', '#f59e0b', '#ef4444', '#3b82f6'],
    legend: {
        position: 'bottom',
        fontSize: '12px'
    },
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    total: {
                        show: true,
                        label: 'รวมทั้งหมด',
                        formatter: function (w) {
                            return w.globals.seriesTotals.reduce((a, b) => a + b, 0) + ' คน'
                        }
                    }
                }
            }
        }
    },
    dataLabels: {
        enabled: true,
        formatter: function (val) {
            return val.toFixed(1) + "%"
        }
    },
    tooltip: {
        y: {
            formatter: function(val) {
                return val + " คน"
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

   // --- Department Examination Status (Vertical Stacked Bar Chart) ---
const deptChartOptions = {
    series: [{
        name: 'ตรวจแล้ว',
        data: $deptSuccess // ห้ามแก้ตัวแปรตามคำสั่ง
    }, {
        name: 'ยังไม่ตรวจ',
        data: $deptPending // ห้ามแก้ตัวแปรตามคำสั่ง
    }],
    chart: {
        type: 'bar',
        height: 500, // เพิ่มความสูงเล็กน้อยเพื่อให้ดูง่ายในแนวตั้ง
        stacked: true,
        toolbar: { 
            show: true, // เปิด toolbar เพื่อให้ user สามารถ Zoom/Pan ดูข้อมูลที่แออัดได้
            tools: {
                download: true,
                selection: true,
                zoom: true,
                zoomin: true,
                zoomout: true,
                pan: true,
            }
        }
    },
    plotOptions: {
        bar: {
            horizontal: false, // ปรับเป็นแนวตั้ง (Vertical)
            borderRadius: 4,
            columnWidth: '60%', // ปรับความกว้างแท่งให้พอดี
        }
    },
    colors: ['#10b981', '#cbd5e1'], 
    xaxis: {
        categories: $deptCategories, // ห้ามแก้ตัวแปรตามคำสั่ง
        labels: { 
            rotate: -45, // สำคัญมาก: เอียงชื่อหน่วยงาน 45 องศาเพื่อไม่ให้ชื่อชนกัน
            rotateAlways: true,
            trim: true,
            maxHeight: 120, // จำกัดความสูงของชื่อหน่วยงานไม่ให้ยาวเกินไป
            style: { 
                fontFamily: 'Sarabun, sans-serif',
                fontSize: '11px'
            } 
        },
        tickPlacement: 'on'
    },
    yaxis: {
        title: {
            text: 'จำนวนบุคลากร (คน)',
            style: { fontFamily: 'Sarabun' }
        }
    },
    legend: {
        position: 'top',
        fontFamily: 'Sarabun'
    },
    tooltip: {
        y: {
            formatter: function(val, { series, seriesIndex, dataPointIndex, w }) {
                let total = w.globals.stackedSeriesTotals[dataPointIndex];
                let percent = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
                return val + " คน (" + percent + "%)";
            }
        }
    }
};

new ApexCharts(document.querySelector("#deptChart"), deptChartOptions).render();
// --- Disease History (Grouped Bar Chart) ---
const diseaseChartOptions = {
    series: [{
        name: 'มีประวัติ',
        data: [12, 18, 5, 25] // จำนวนคนที่มีโรคนั้นๆ
    }, {
        name: 'ไม่มีประวัติ',
        data: [88, 82, 95, 75] // จำนวนคนที่ไม่มี
    }],
    chart: {
        type: 'bar',
        height: 300,
        toolbar: { show: false }
    },
    colors: ['#ef4444', '#34d399'], // แดง (มีโรค) กับ เขียวสว่าง (ไม่มี)
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 5
        }
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: ['โรคความดัน', 'เบาหวาน', 'โรคหัวใจ', 'ไขมันในเลือดสูง'],
        labels: { style: { fontFamily: 'Sarabun' } }
    },
    yaxis: { title: { text: 'จำนวนพนักงาน (คน)' } },
    legend: { position: 'bottom', fontFamily: 'Sarabun' },
    tooltip: {
        shared: true,
        intersect: false,
        y: { formatter: (val) => val + " ราย" }
    }
};

new ApexCharts(document.querySelector("#diseaseChart"), diseaseChartOptions).render();


JS;
$this->registerJs($js, View::POS_END);
?>
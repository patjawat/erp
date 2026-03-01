<?php

use yii\helpers\Url;
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
        <?php echo $this->render('_search_dashboard', ['model' => $searchModel]); ?>
</div>

<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-header bg-primary-gradient text-white d-flex align-items-center gap-2 py-3">
                <i data-lucide="calendar-days"></i>
                <h5 class="mb-0 fw-bold">ปฏิทินการนัดหมายตรวจสุขภาพ</h5>
            </div>
            <div class="card-body p-3 p-md-4">
                <p class="text-muted small mb-3">คลิกวันที่เพื่อดูรายการนัด หรือคลิกรายการเพื่อไปหน้าบันทึก LAB</p>
                <div id="health-appointment-calendar"></div>
            </div>
        </div>
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
                <h3 class="card-title">แนวโน้มระดับความเสี่ยงรายปี</h3>
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
                            <div class="h3 mb-0" style="color: #1d4ed8;"><?= $kpiStats['percent_screened'] ?? 0 ?>%</div>
                            <div class="small" style="color: #2563eb;">อัตราการคัดกรอง</div>
                            <div class="small text-muted mt-1"><?= number_format($kpiStats['screened_count'] ?? 0) ?>/<?= number_format($kpiStats['total_employees'] ?? 0) ?> คน</div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="rounded-3 p-3" style="background-color: #f0fdf4;">
                            <div class="h3 mb-0" style="color: #15803d;"><?= $kpiStats['percent_normal_bmi'] ?? 0 ?>%</div>
                            <div class="small" style="color: #16a34a;">BMI ปกติ</div>
                            <div class="small text-muted mt-1"><?= number_format($kpiStats['normal_bmi_count'] ?? 0) ?>/<?= number_format($kpiStats['total_with_bmi'] ?? 0) ?> คน</div>
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
// สรุปประวัติการเจ็บป่วย
$diseaseHas = json_encode($diseaseStats['has'] ?? [0, 0, 0, 0]);
$diseaseNo = json_encode($diseaseStats['no'] ?? [0, 0, 0, 0]);
// แนวโน้มระดับความเสี่ยงรายปี
$riskYears = json_encode($riskTrend['years'] ?? []);
$riskLow = json_encode($riskTrend['low'] ?? []);
$riskMedium = json_encode($riskTrend['medium'] ?? []);
$riskHigh = json_encode($riskTrend['high'] ?? []);

$calendarEventsUrl = Url::to(['/health/default/calendar-events']);
$calendarThaiYear = (int)($searchModel->thai_year ?? 0);

$this->registerJsFile('https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);
$this->registerJsFile('https://cdn.jsdelivr.net/npm/@fullcalendar/core@6.1.15/locales/th.global.min.js', ['depends' => [\yii\web\JqueryAsset::class]]);

$js = <<< JS
    // --- ปฏิทินการนัดหมายตรวจสุขภาพ (FullCalendar) ---
    (function() {
        var calendarEl = document.getElementById('health-appointment-calendar');
        if (calendarEl) {
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'th',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,listWeek'
                },
                buttonText: {
                    today: 'วันนี้',
                    month: 'เดือน',
                    week: 'สัปดาห์',
                    list: 'รายการ'
                },
                events: {
                    url: '{$calendarEventsUrl}',
                    extraParams: function() {
                        return { thai_year: {$calendarThaiYear} };
                    }
                },
                eventClick: function(info) {
                    if (info.event.url) {
                        info.jsEvent.preventDefault();
                        window.location.href = info.event.url;
                    }
                },
                height: 'auto',
                contentHeight: 380
            });
            calendar.render();
        }
    })();

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

    // --- Risk Level Bar Chart (รายปี) ---
    const riskOptions = {
        series: [{
            name: 'เสี่ยงต่ำ',
            data: $riskLow
        }, {
            name: 'เสี่ยงกลาง',
            data: $riskMedium
        }, {
            name: 'เสี่ยงสูง',
            data: $riskHigh
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
            categories: $riskYears,
            labels: { style: { fontFamily: 'Sarabun' } }
        },
        legend: {
            position: 'bottom',
            fontFamily: 'Sarabun'
        },
        grid: {
            strokeDashArray: 3
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return val + " คน"
                }
            }
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
        data: $diseaseHas // จำนวนคนที่มีโรคนั้นๆ
    }, {
        name: 'ไม่มีประวัติ',
        data: $diseaseNo // จำนวนคนที่ไม่มี
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
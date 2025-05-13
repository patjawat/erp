<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use app\modules\hr\models\Development;

/** @var yii\web\View $this */
/** @var app\modules\hr\models\DevelopmentSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'อบรม/ประชุม/ดูงาน';
$this->params['breadcrumbs'][] = $this->title;
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-briefcase fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/hr/views/development/menu') ?>
<?php $this->endBlock(); ?>



    <div class="min-vh-100">

        <!-- Main Content -->
        <div class="container py-4">
            <!-- Filter Section -->
            <div class="card mb-4">
                <div class="card-body">

                  <?php echo $this->render('_search_dashboard', ['model' => $searchModel]); ?>
                  
                    
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row g-4 mb-4">
                <div class="col-12 col-md-4">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between border-start border-4 border-primary rounded-start pb-0">
                            <div>
                                <p class="mb-0">จำนวนการอบรม/ประชุม/ดูงานทั้งหมด</p>
                                <h3 class="fs-2 fw-semibold mt-2">247</h3>
                                <p class="mt-2 mb-0">เพิ่มขึ้น 12% จากปีที่แล้ว</p>
                            </div>
                            <div class="icon-box align-self-start">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between border-start border-4 border-primary rounded-start pb-0">
                            <div>
                                <p class="mb-0">งบประมาณที่ใช้</p>
                                <h3 class="fs-2 fw-semibold mt-2">฿1,458,750</h3>
                                <p class="mt-2 ">คิดเป็น 78% ของงบประมาณทั้งหมด</p>
                            </div>
                            <div class="icon-box align-self-start">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card h-100">
                        <div class="card-body d-flex justify-content-between border-start border-4 border-primary rounded-start pb-0">
                            <div>
                                <p class="mb-0">บุคลากรที่ได้รับการพัฒนา</p>
                                <h3 class="fs-2 fw-semibold mt-2">189</h3>
                                <p class="mt-2 ">คิดเป็น 94% ของบุคลากรทั้งหมด</p>
                            </div>
                            <div class="icon-box align-self-start">
                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="row g-4 mb-4">
                <!-- Chart 1: Activities by Type -->
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="fw-semibold text-dark mb-3">สัดส่วนประเภทการอบรม/ประชุม/ดูงาน</h5>
                            <div id="activityTypeChart" style="height: 320px;"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Chart 2: Monthly Trend -->
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="fw-semibold text-dark mb-3">แนวโน้มการอบรม/ประชุม/ดูงานรายเดือน</h5>
                            <div id="monthlyTrendChart" style="height: 320px;"></div>
                            <div class="d-flex flex-wrap justify-content-center mt-3">
                                <div class="me-3 mb-2 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle bg-primary me-1" style="width:12px;height:12px;"></span>
                                    <span class="small">ประชุมติดตามงาน/รับนโยบาย</span>
                                </div>
                                <div class="me-3 mb-2 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle bg-success me-1" style="width:12px;height:12px;"></span>
                                    <span class="small">ประชุมวิชาการ/สัมมนา/ฝึกอบรม</span>
                                </div>
                                <div class="me-3 mb-2 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle bg-purple me-1" style="width:12px;height:12px;background-color:#8b5cf6;"></span>
                                    <span class="small">เพื่อเป็นวิทยากร</span>
                                </div>
                                <div class="me-3 mb-2 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle bg-warning me-1" style="width:12px;height:12px;"></span>
                                    <span class="small">นำเสนอผลงาน/จัดนิทรรศการ</span>
                                </div>
                                <div class="me-3 mb-2 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle me-1" style="width:12px;height:12px;background-color:#d63384;"></span>
                                    <span class="small">เพื่อศึกษาดูงาน</span>
                                </div>
                                <div class="mb-2 d-flex align-items-center">
                                    <span class="d-inline-block rounded-circle bg-secondary me-1" style="width:12px;height:12px;"></span>
                                    <span class="small">อื่นๆ</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Charts -->
            <div class="row g-4 mb-4">
                <!-- Chart 3: Budget Allocation -->
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="fw-semibold text-dark mb-3">การใช้งบประมาณตามประเภทกิจกรรม</h5>
                            <div id="budgetChart" style="height: 320px;"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Chart 4: Department Participation -->
                <div class="col-12 col-lg-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="fw-semibold text-dark mb-3">การเข้าร่วมกิจกรรมตามหน่วยงาน</h5>
                            <div id="departmentChart" style="height: 320px;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities Table -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold text-dark">กิจกรรมล่าสุด</h5>
                        <button class="btn btn-sm btn-outline-primary">
                            ดูทั้งหมด
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th scope="col">ชื่อกิจกรรม</th>
                                    <th scope="col">ประเภท</th>
                                    <th scope="col">วันที่</th>
                                    <th scope="col">ผู้เข้าร่วม</th>
                                    <th scope="col">งบประมาณ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="fw-medium">การประชุมติดตามความก้าวหน้าโครงการ Q3</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary">ประชุมติดตามงาน</span>
                                    </td>
                                    <td>15 ส.ค. 2567</td>
                                    <td>24 คน</td>
                                    <td>฿15,000</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="fw-medium">อบรมเชิงปฏิบัติการ: การพัฒนาทักษะดิจิทัล</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">ฝึกอบรม</span>
                                    </td>
                                    <td>10 ส.ค. 2567</td>
                                    <td>35 คน</td>
                                    <td>฿85,000</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="fw-medium">ศึกษาดูงานนวัตกรรมการบริหารจัดการ</span>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: #d63384;">ศึกษาดูงาน</span>
                                    </td>
                                    <td>5 ส.ค. 2567</td>
                                    <td>18 คน</td>
                                    <td>฿120,000</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="fw-medium">การนำเสนอผลงานวิชาการประจำปี</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark">นำเสนอผลงาน</span>
                                    </td>
                                    <td>28 ก.ค. 2567</td>
                                    <td>42 คน</td>
                                    <td>฿95,000</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h4 class="card-title">สรุปข้อมูลการอบรมประจำปีงบประมาณ 2568</h4>
            <?php echo $this->render('_search_year', ['model' => $searchModel]) ?>
        </div>

        <div
            class="table-responsive"
        >
            <table
                class="table table-primary"
            >
                <thead>
                    <tr>
                        <th class="text-start fw-semibold" scope="col">ประเภทการอบรม</th>
                        <th class="text-center fw-semibold" scope="col">ต.ค.</th>
                        <th class="text-center fw-semibold" scope="col">พ.ย.</th>
                        <th class="text-center fw-semibold" scope="col">ธ.ค.</th>
                        <th class="text-center fw-semibold" scope="col">ม.ค.</th>
                        <th class="text-center fw-semibold" scope="col">ก.พ.</th>
                        <th class="text-center fw-semibold" scope="col">มี.ค.</th>
                        <th class="text-center fw-semibold" scope="col">เม.ย.</th>
                        <th class="text-center fw-semibold" scope="col">พ.ค.</th>
                        <th class="text-center fw-semibold" scope="col">มิ.ย.</th>
                        <th class="text-center fw-semibold" scope="col">ก.ค.</th>
                        <th class="text-center fw-semibold" scope="col">ส.ค.</th>
                        <th class="text-center fw-semibold" scope="col">ก.ย.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchModel->listSummaryMonth() as $item): ?>
                    <tr class="">
                        <td scope="row"><?= $item['title'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m10'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m11'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m12'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m1'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m2'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m3'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m4'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m5'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m6'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m7'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m8'] ?></td>
                        <td class="text-center fw-semibold"><?= $item['m9'] ?></td>
                    
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
    </div>
</div>

<?php
$activityTypeLabel = Json::encode($searchModel->getSummary()['activityType']['labels']);

$activityTypeSeries = Json::encode($searchModel->getSummary()['activityType']['series']);
$monthlyTrend = Json::encode($searchModel->getSummary()['monthlyTrend']['series']);


$js = <<<JS
        document.addEventListener('DOMContentLoaded', function() {
            // Activity Type Chart
            const activityTypeOptions = {
                series: $activityTypeSeries,
                chart: {
                    type: 'donut',
                    height: 320,
                },
                labels: $activityTypeLabel,
                colors: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899', '#6b7280'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '50%'
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    fontSize: '14px',
                    fontFamily: 'Sarabun, sans-serif',
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val) {
                        return val.toFixed(1) + "%";
                    },
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 300
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
            const activityTypeChart = new ApexCharts(document.querySelector("#activityTypeChart"), activityTypeOptions);
            activityTypeChart.render();

            // Monthly Trend Chart - Updated to show each activity type
            const monthlyTrendOptions = {
                series: $monthlyTrend,
                // series: [
                //     {
                //         name: 'ประชุมติดตามงาน/รับนโยบาย',
                //         data: [5, 6, 7, 8, 10, 12, 13, 14, 11, 9, 7, 2]
                //     },
                //     {
                //         name: 'ประชุมวิชาการ/สัมมนา/ฝึกอบรม',
                //         data: [3, 4, 5, 7, 8, 9, 10, 11, 8, 6, 4, 1]
                //     },
                //     {
                //         name: 'เพื่อเป็นวิทยากร',
                //         data: [1, 2, 2, 3, 3, 3, 3, 3, 2, 2, 1, 0]
                //     },
                //     {
                //         name: 'นำเสนอผลงาน/จัดนิทรรศการ',
                //         data: [2, 2, 3, 3, 3, 3, 3, 3, 3, 2, 2, 1]
                //     },
                //     {
                //         name: 'เพื่อศึกษาดูงาน',
                //         data: [1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1]
                //     },
                //     {
                //         name: 'อื่นๆ',
                //         data: [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
                //     }
                // ],
                chart: {
                    height: 320,
                    type: 'line',
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    }
                },
                dataLabels: {
                    enabled: false
                },
                stroke: {
                    width: [3, 3, 3, 3, 3, 3],
                    curve: 'smooth',
                    dashArray: [0, 0, 0, 0, 0, 0]
                },
                colors: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899', '#6b7280'],
                xaxis: {
                    categories: ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'],
                    labels: {
                        style: {
                            fontFamily: 'Sarabun, sans-serif',
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'จำนวนกิจกรรม',
                        style: {
                            fontFamily: 'Sarabun, sans-serif',
                        }
                    },
                    labels: {
                        style: {
                            fontFamily: 'Sarabun, sans-serif',
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " ครั้ง"
                        }
                    }
                },
                legend: {
                    show: false
                },
                markers: {
                    size: 4,
                    hover: {
                        size: 6
                    }
                }
            };
            const monthlyTrendChart = new ApexCharts(document.querySelector("#monthlyTrendChart"), monthlyTrendOptions);
            monthlyTrendChart.render();

            // Budget Chart
            const budgetOptions = {
                series: [{
                    name: 'งบประมาณ (พันบาท)',
                    data: [420, 650, 180, 320, 280, 90]
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: false,
                        columnWidth: '60%',
                    }
                },
                colors: ['#10b981'],
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: [
                        'ประชุมติดตามงาน', 
                        'ประชุมวิชาการ/อบรม', 
                        'เป็นวิทยากร', 
                        'นำเสนอผลงาน', 
                        'ศึกษาดูงาน', 
                        'อื่นๆ'
                    ],
                    labels: {
                        style: {
                            fontFamily: 'Sarabun, sans-serif',
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'พันบาท',
                        style: {
                            fontFamily: 'Sarabun, sans-serif',
                        }
                    },
                    labels: {
                        style: {
                            fontFamily: 'Sarabun, sans-serif',
                        }
                    }
                },
                fill: {
                    opacity: 1
                },
                tooltip: {
                    y: {
                        formatter: function (val) {
                            return val + " พันบาท"
                        }
                    }
                }
            };
            const budgetChart = new ApexCharts(document.querySelector("#budgetChart"), budgetOptions);
            budgetChart.render();

            // Department Chart
            const departmentOptions = {
                series: [{
                    name: 'จำนวนผู้เข้าร่วม',
                    data: [65, 42, 38, 24, 18]
                }],
                chart: {
                    type: 'bar',
                    height: 320,
                    toolbar: {
                        show: false
                    }
                },
                plotOptions: {
                    bar: {
                        borderRadius: 4,
                        horizontal: true,
                    }
                },
                colors: ['#8b5cf6'],
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: [
                        'สำนักงานใหญ่', 
                        'สำนักงานภูมิภาค', 
                        'ฝ่ายวิชาการ', 
                        'ฝ่ายบริหาร', 
                        'ฝ่ายวิจัยและพัฒนา'
                    ],
                    labels: {
                        style: {
                            fontFamily: 'Sarabun, sans-serif',
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            fontFamily: 'Sarabun, sans-serif',
                        }
                    }
                }
            };
            const departmentChart = new ApexCharts(document.querySelector("#departmentChart"), departmentOptions);
            departmentChart.render();

            // Filter functionality
            document.getElementById('applyFilter').addEventListener('click', function() {
                const fiscalYear = document.getElementById('fiscalYearFilter').value;
                const department = document.getElementById('departmentFilter').value;
                
                // In a real application, you would fetch new data based on these filters
                // For this demo, we'll update the charts with different data based on fiscal year
                
                    // Update monthly trend chart for 2566
                    monthlyTrendChart.updateSeries([
                        {
                            name: 'ประชุมติดตามงาน/รับนโยบาย',
                            data: [4, 5, 6, 7, 9, 10, 11, 12, 10, 8, 6, 3]
                        },
                        {
                            name: 'ประชุมวิชาการ/สัมมนา/ฝึกอบรม',
                            data: [2, 3, 4, 6, 7, 8, 9, 10, 7, 5, 3, 2]
                        },
                        {
                            name: 'เพื่อเป็นวิทยากร',
                            data: [1, 1, 2, 2, 2, 2, 3, 3, 2, 1, 1, 0]
                        },
                        {
                            name: 'นำเสนอผลงาน/จัดนิทรรศการ',
                            data: [2, 2, 2, 2, 2, 3, 3, 3, 2, 1, 1, 1]
                        },
                        {
                            name: 'เพื่อศึกษาดูงาน',
                            data: [1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 1]
                        },
                        {
                            name: 'อื่นๆ',
                            data: [0, 0, 1, 0, 1, 0, 1, 0, 1, 0, 1, 0]
                        }
                    ]);
               
                
                // You would also update other charts based on the selected filters
                // For example, updating the budget chart and department chart
            });
        });
    // Removed unnecessary or undefined variable reference
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>

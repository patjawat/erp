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
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
       <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        ภาพรวม<?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/hr/views/development/menu',['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>


    <div class="min-vh-100">
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
                                <h3 class="fs-2 mt-2"><?=$searchModel->getYearlyDevelopmentSummary()['total_count']?></h3>
                                <p class="mt-2 mb-0">
                                    
                                    <?=$searchModel->getYearlyDevelopmentSummary()['price_status']?>
                                     <?=$searchModel->getYearlyDevelopmentSummary()['count_percent_change']?>% จากปีที่แล้ว</p>
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
                                <h3 class="fs-2 mt-2"><?=number_format($searchModel->getYearlyDevelopmentSummary()['total_price'],2)?></h3>
                                <p class="mt-2 ">คิดเป็น <?=$searchModel->getYearlyDevelopmentSummary()['price_percent_change']?>% ของงบประมาณปีที่แล้ว</p>
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
                                <h3 class="fs-2 mt-2"><?=$searchModel->getYearlyDevelopmentSummary()['emp_count']?></h3>
                                <p class="mt-2 ">คิดเป็น <?=$searchModel->getYearlyDevelopmentSummary()['emp_percent']?>% ของบุคลากรทั้งหมด</p>
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
                            <h5 class="fw-semibold text-dark mb-3">งบใช้จริงแยกตามหมวดค่าใช้จ่าย</h5>
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

            <!-- Recent Activities Table — ข้อมูลจริง -->
            <?php
            $recent = \app\modules\hr\models\Development::find()
                ->where(['thai_year' => $searchModel->thai_year, 'deleted_at' => null])
                ->andWhere(['not in', 'status', \app\modules\hr\services\DevelopmentReport::EXCLUDED_DEV_STATUSES])
                ->with(['developmentType', 'createdByEmp'])
                ->orderBy(['date_start' => SORT_DESC, 'id' => SORT_DESC])
                ->limit(8)
                ->all();
            ?>
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-semibold text-dark mb-0">กิจกรรมล่าสุด</h5>
                        <?= Html::a('ดูทั้งหมด', ['/hr/development/index'], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">ชื่อกิจกรรม</th>
                                    <th scope="col">ประเภท</th>
                                    <th scope="col">วันที่</th>
                                    <th scope="col" class="text-center">ผู้เข้าร่วม</th>
                                    <th scope="col" class="text-end">งบ (ประมาณ)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent)): ?>
                                    <tr><td colspan="5" class="text-center text-body-secondary py-3">ยังไม่มีข้อมูลในปีงบนี้</td></tr>
                                <?php endif; ?>
                                <?php foreach ($recent as $d): ?>
                                    <tr>
                                        <td><span class="fw-medium"><?= Html::encode($d->topic) ?></span></td>
                                        <td><span class="badge bg-primary-subtle text-primary-emphasis"><?= Html::encode($d->developmentType->title ?? '-') ?></span></td>
                                        <td class="text-nowrap"><?= $d->showDateRange() ?></td>
                                        <td class="text-center"><?= (int) $d->memberText()['count'] + 1 ?> คน</td>
                                        <td class="text-end"><?= number_format($d->totalEstimatedCost(), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
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
            <h4 class="card-title">สรุปข้อมูลการอบรมประจำปีงบประมาณ <?=$searchModel->thai_year?></h4>
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
                        <th class="text-start" scope="col">ประเภทการอบรม</th>
                        <th class="text-center" scope="col">ต.ค.</th>
                        <th class="text-center" scope="col">พ.ย.</th>
                        <th class="text-center" scope="col">ธ.ค.</th>
                        <th class="text-center" scope="col">ม.ค.</th>
                        <th class="text-center" scope="col">ก.พ.</th>
                        <th class="text-center" scope="col">มี.ค.</th>
                        <th class="text-center" scope="col">เม.ย.</th>
                        <th class="text-center" scope="col">พ.ค.</th>
                        <th class="text-center" scope="col">มิ.ย.</th>
                        <th class="text-center" scope="col">ก.ค.</th>
                        <th class="text-center" scope="col">ส.ค.</th>
                        <th class="text-center" scope="col">ก.ย.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($searchModel->listSummaryMonth() as $item): ?>
                    <tr class="">
                        <td scope="row"><?= $item['title'] ?></td>
                        <td class="text-center"><?= $item['m10'] ?></td>
                        <td class="text-center"><?= $item['m11'] ?></td>
                        <td class="text-center"><?= $item['m12'] ?></td>
                        <td class="text-center"><?= $item['m1'] ?></td>
                        <td class="text-center"><?= $item['m2'] ?></td>
                        <td class="text-center"><?= $item['m3'] ?></td>
                        <td class="text-center"><?= $item['m4'] ?></td>
                        <td class="text-center"><?= $item['m5'] ?></td>
                        <td class="text-center"><?= $item['m6'] ?></td>
                        <td class="text-center"><?= $item['m7'] ?></td>
                        <td class="text-center"><?= $item['m8'] ?></td>
                        <td class="text-center"><?= $item['m9'] ?></td>
                    
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

</div>

<?php
$activityTypeLabel = Json::encode($searchModel->getSummary()['activityType']['labels']);

$activityTypeSeries = Json::encode($searchModel->getSummary()['activityType']['series']);
$monthlyTrend = Json::encode($searchModel->getSummary()['monthlyTrend']['series']);

// ── ข้อมูลจริงแทนค่า hardcode เดิม (เฟส 1) ──
$component = $report['actual_by_component'] ?? [];
$budgetLabels = Json::encode(array_map(fn($r) => $r['label'], $component));
$budgetSeries = Json::encode(array_map(fn($r) => round((float) $r['amount'], 2), $component));

$dept = \app\modules\hr\services\DevelopmentReport::participationByDepartment((int) $searchModel->thai_year, 8);
$deptLabels = Json::encode(array_map(fn($r) => $r['name'], $dept));
$deptSeries = Json::encode(array_map(fn($r) => (int) $r['n'], $dept));


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

            // Budget Chart — งบใช้จริงแยกตามหมวดค่าใช้จ่าย (ข้อมูลจริง)
            const budgetOptions = {
                series: [{
                    name: 'ใช้จริง (บาท)',
                    data: $budgetSeries
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
                    categories: $budgetLabels,
                    labels: {
                        style: {
                            fontFamily: 'Sarabun, sans-serif',
                        }
                    }
                },
                yaxis: {
                    title: {
                        text: 'บาท',
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
                            return Number(val).toLocaleString() + " บาท"
                        }
                    }
                }
            };
            const budgetChart = new ApexCharts(document.querySelector("#budgetChart"), budgetOptions);
            budgetChart.render();

            // Department Chart — คน-ครั้งการเข้าร่วมแยกตามหน่วยงาน (ข้อมูลจริง, Top 8)
            const departmentOptions = {
                series: [{
                    name: 'คน-ครั้ง',
                    data: $deptSeries
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
                    categories: $deptLabels,
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
        });
    // Removed unnecessary or undefined variable reference
JS;
$this->registerJs($js, \yii\web\View::POS_END);
?>

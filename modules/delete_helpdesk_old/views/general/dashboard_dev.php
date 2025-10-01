<?php
use yii\web\View;
use yii\helpers\Html;
$this->title = 'งานซ่อมบำรุง';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-screwdriver-wrench fs-1"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('menu') ?>
<?php $this->endBlock(); ?>


    <style>

        .header-logo {
            color: #ff9800;
            font-weight: bold;
        }
        .dashboard-title {
            background-color: #9c73b5;
            color: white;
            border-radius: 25px;
            padding: 10px 20px;
            text-align: center;
            margin-bottom: 15px;
        }
        .section-title {
            background-color: #9c73b5;
            color: white;
            border-radius: 15px;
            padding: 5px 15px;
            text-align: center;
            margin-bottom: 10px;
        }
        .count-box {
            height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border-radius: 5px;
            font-size: 3rem;
            font-weight: bold;
            color: #444;
        }
        .box-blue {
            background-color: #a8e6f1;
            border: 1px solid #7dcfdc;
        }
        .box-green {
            background-color: #c6e6c6;
            border: 1px solid #9cd19c;
        }
        .box-yellow {
            background-color: #ffe6a8;
            border: 1px solid #ffd470;
        }
        .box-title {
            font-size: 1rem;
            font-weight: normal;
            text-align: center;
            width: 100%;
            padding: 5px;
            color: white;
        }
        .blue-title {
            background-color: #5bc0de;
        }
        .green-title {
            background-color: #5cb85c;
        }
        .yellow-title {
            background-color: #f0ad4e;
        }
        .date-picker {
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #fff;
        }
        .gauge {
            position: relative;
            width: 150px;
            height: 150px;
            margin: 0 auto;
        }
        .backlog-table {
            background-color: #ffe6a8;
            border-radius: 5px;
        }
        .version-box {
            background-color: #555;
            color: white;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.8rem;
        }
        .chart-container {
            height: 200px;
            background-color: #fff;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .service-type-chart {
            height: 200px;
            background-color: #fff;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .system-chart {
            height: 200px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
    </style>

        <div class="row mb-3">
            <div class="col-md-2">
                <div class="card border-2 border-primary border-start-0 border-end-0 border-top-0">
                    <div class="card-body ">
                    <h6 class="text-start">รอดำเนินการ</h6>
                    <h1 class="text-center">7</h1>
                    </div>
                </div>
                <div class="card border-2 border-primary border-start-0 border-end-0 border-top-0">
                    <div class="card-body ">
                    <h6 class="text-start">รอดำเนินการ</h6>
                    <h1 class="text-center">7</h1>
                    </div>
                </div>

            </div>
            
            <div class="col-md-2">
            <div class="card border-2 border-primary border-start-0 border-end-0 border-top-0">
                    <div class="card-body ">
                    <h6 class="text-start">กำลังดำเนินการ</h6>
                    <h1 class="text-center">7</h1>
                    </div>
                </div>

                <div class="card border-2 border-primary border-start-0 border-end-0 border-top-0">
                    <div class="card-body ">
                    <h6 class="text-start">เสร็จสิ้น</h6>
                    <h1 class="text-center">7</h1>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                
                <div class="dashboard-title">
                    <h3 class="m-0">Service Overview Dashboard</h3>
                    <small>V.02.2021 - ISSUE DATE MAY 25, 2021</small>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <div class="date-picker">
                    <strong>Jan 1, 2021 - Dec 31, 2021</strong>
                </div>
            </div>
           
            <div class="col-md-2">
                <div class="text-center">
                    <small class="d-block mb-1">CUSTOMER SATISFACTION</small>
                    <div class="gauge">
                        <canvas id="satisfactionGauge"></canvas>
                        <div style="position:absolute; top:60%; left:50%; transform:translate(-50%,-50%); font-size:1.5rem; font-weight:bold;">2.143</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-4">
                <div id="donutChart" style="height:230px; width:100%;"></div>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="section-title">System</div>
                        <div class="system-chart">
                            <div class="p-2">
                                <div class="d-flex justify-content-between">
                                    <div>ประเภทของระบบ (System)</div>
                                    <div>Record Count <i class="fas fa-sort"></i></div>
                                </div>
                                <div class="mt-3">
                                    <div class="mb-2">
                                        <div>แอร์ (AC)</div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 70%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div>อื่นๆ (Other)</div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 50%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div>ประปา (SAN)</div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 25%"></div>
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <div>ไฟฟ้า (Electrical)</div>
                                        <div class="progress">
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 25%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-center mt-2">
                                    <small>1 - 4 / 4</small>
                                    <button class="btn btn-sm btn-light"><i class="fas fa-chevron-left"></i></button>
                                    <button class="btn btn-sm btn-light"><i class="fas fa-chevron-right"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="section-title">Type of service</div>
                        <div class="service-type-chart" id="serviceTypeChart"></div>
                    </div>
                    <div class="col-md-4">
                        <div class="section-title">Backlog report</div>
                        <div class="backlog-table p-2">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">รหัสงานบริการ</th>
                                        <th scope="col">รายละเอียด</th>
                                        <th scope="col">ผู้รับผิดชอบ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>SR-004/15521</td>
                                        <td>ไม่เย็นเลย</td>
                                        <td>สาขา มาบตาพุด</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>SR-002/17521</td>
                                        <td>ไม่ติด</td>
                                        <td>สาขา สุรวงศ์</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>SR-002/16321</td>
                                        <td>ไม่ติด</td>
                                        <td>รังสิต อินเตอร์</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="text-center mt-2">
                                <small>1 - 3 / 3</small>
                                <button class="btn btn-sm btn-light"><i class="fas fa-chevron-left"></i></button>
                                <button class="btn btn-sm btn-light"><i class="fas fa-chevron-right"></i></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="section-title">Portfolio for Technician</div>
                <div class="chart-container p-2" id="technicianChart"></div>
            </div>
            <div class="col-md-6">
                <div class="section-title">Portfolio for Helpdesk</div>
                <div class="chart-container p-2" id="helpdeskChart"></div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    
    <?php
$js= <<< JS
            // Donut Chart
            var donutCtx = document.createElement('canvas');
            document.getElementById('donutChart').appendChild(donutCtx);
            
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: ['สำเร็จ (DONE)', 'ยังไม่สำเร็จ (Not Done)'],
                    datasets: [{
                        data: [57.1, 42.9],
                        backgroundColor: ['#5bc0de', '#ffe6a8'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
            
            // Customer Satisfaction Gauge
            var gaugeCtx = document.getElementById('satisfactionGauge').getContext('2d');
            new Chart(gaugeCtx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [2.143, 2.857],
                        backgroundColor: [
                            'rgba(255, 0, 0, 0.7)',
                            'rgba(0, 255, 0, 0.7)'
                        ],
                        borderWidth: 0,
                        circumference: 180,
                        rotation: 270
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            enabled: false
                        }
                    }
                }
            });
            
            // Service Type Chart
            var serviceTypeCtx = document.createElement('canvas');
            document.getElementById('serviceTypeChart').appendChild(serviceTypeCtx);
            
            new Chart(serviceTypeCtx, {
                type: 'bar',
                data: {
                    labels: ['งานซ่อม', 'แก้ไขระบบ', 'อื่นๆ'],
                    datasets: [{
                        label: 'จำนวน',
                        data: [3, 2, 2],
                        backgroundColor: '#4e95f4',
                        barPercentage: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            // Technician Portfolio Chart
            var technicianCtx = document.createElement('canvas');
            document.getElementById('technicianChart').appendChild(technicianCtx);
            
            new Chart(technicianCtx, {
                type: 'bar',
                data: {
                    labels: ['สาขา สุรวงศ์', 'สาขา โพธาราม', 'มาบตาพุด', 'รังสิต อินเตอร์', 'เมเจอร์ พระราม 2'],
                    datasets: [
                        {
                            label: 'สำเร็จ (DONE)',
                            data: [1, 2, 0, 0, 1],
                            backgroundColor: '#5cb85c',
                            barPercentage: 0.5
                        },
                        {
                            label: 'ยังไม่สำเร็จ (Not Done)',
                            data: [1, 0, 1, 1, 0],
                            backgroundColor: '#ffd470',
                            barPercentage: 0.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            stacked: false,
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
            
            // Helpdesk Portfolio Chart
            var helpdeskCtx = document.createElement('canvas');
            document.getElementById('helpdeskChart').appendChild(helpdeskCtx);
            
            new Chart(helpdeskCtx, {
                type: 'bar',
                data: {
                    labels: ['สาขา โพธาราม', 'บางนา กิโล', 'เซ็น แสนทรา', 'โรง สีลม'],
                    datasets: [
                        {
                            label: 'งานซ่อมแซม',
                            data: [3, 2, 1, 2],
                            backgroundColor: '#4e95f4',
                            barPercentage: 0.5
                        },
                        {
                            label: 'เรื่องร้องเรียน',
                            data: [0, 0, 1, 0],
                            backgroundColor: '#dc3545',
                            barPercentage: 0.5
                        },
                        {
                            label: 'สำรวจก่อนดำเนินการ',
                            data: [0, 0, 0, 0],
                            backgroundColor: '#ffc107',
                            barPercentage: 0.5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                boxWidth: 12
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            stacked: false,
                            grid: {
                                display: false
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    }
                }
            });
JS;
$this->registerJs($js,View::POS_END);
?>

<div class="card vh-100 d-flex justify-content-center align-items-center">
    <div class="card-body text-center">
        <h1>อยู่ระหว่างปรับปรุง</h1>
        <?php echo Html::img('@web/img/ma_service.jpg', ['width' => 500]) ?>
    </div>
</div>

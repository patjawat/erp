<?php
use yii\helpers\Url;
use yii\helpers\Html;
?>
<div class="row align-items-stretch">

    <!-- Begin total revenue chart -->
    <div class="col-md-8 col-lg-9">
        <div class="card">
            <div class="card-header border-0">
                <h5 class="card-title">ปริมาณการใช้เงินงบประมาณ</h5>
            </div>
            <div class="card-body" id="chartIndex2">

            </div>
        </div>
    </div> <!-- End total revenue chart -->

    <div class="col-md-4 col-lg-3">
        <div class="row">
            <div class="col-6">
                <a href="<?=Url::to(['/hr'])?>">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <?=Html::img('@web/images/hr.png', ['width' => 70])?>
                                <div>บุคลากร</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6">
                <a href="<?=Url::to(['/am'])?>">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <?=Html::img('@web/images/asset-allocation.png', ['width' => 50])?>
                                <div>ทรัพย์สิน</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-6">
                <a href="<?=Url::to(['/helpdesk'])?>">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column align-items-center justify-content-center">
                                <?=Html::img('@web/images/customer_service.png', ['width' => 100])?>
                                <div>งานซ่อมบำรุง</div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <?=Html::img('@web/images/stethoscope.png', ['width' => 50])?>
                            <div>ศูนย์เครื่องมือแพทย์</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card overflow-hidden">
            <div class="card-header border-0">
                <h5 class="card-title">มูลค่าทรัพย์สิน</h5>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item py-4">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-2 fs-14">มูลค่าครุภัณฑ์</p>
                                <h4 class="mb-0">1,625</h4>
                            </div>
                            <div class="avatar avatar-md bg-info me-0 align-self-center">
                                <i class="bx bx-layer fs-lg"></i>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item py-4">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-2 fs-14">มูลค่าสิ่งก่อสร้าง</p>
                                <h4 class="mb-0">$ 42,235</h4>
                            </div>
                            <div class="avatar avatar-md bg-primary me-0 align-self-center">
                                <i class="bx bx-bar-chart-alt fs-lg"></i>
                            </div>
                        </div>
                    </li>
                    <li class="list-group-item py-4">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <p class="text-muted mb-2 fs-14">ครุภัณฑ์</p>
                                <h4 class="mb-0">8,235</h4>
                            </div>
                            <div class="avatar avatar-md bg-success me-0 align-self-center">
                                <i class="bx bx-chart fs-lg"></i>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>



<?php
use yii\web\View;
$js = <<< JS

var options = {
          series: [{
          name: 'เงินงบประมาณ',
          data: [44, 55, 57, 56, 61, 58, 63, 60, 66,70,55,62]
        }, {
          name: 'เงินบำรุง',
          data: [76, 85, 101, 98, 87, 105, 91, 114, 94,85,66,79]
        }, {
          name: 'เงินบริจาค',
          data: [35, 41, 36, 26, 45, 48, 52, 53, 41,64,53,60]
        }],
          chart: {
          type: 'bar',
          height: 290
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            // barHeight: '30%',
            endingShape: 'rounded',
            borderRadius: 4,
            borderRadiusApplication: 'end',
          },
        },
        dataLabels: {
          enabled: false
        },
        stroke: {
          show: true,
          width: 2,
          colors: ['transparent']
        },
        xaxis: {
          categories: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ย.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'],
        },
        yaxis: {
          title: {
            text: '$ (thousands)'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return "$ " + val + " thousands"
            }
          }
        }
        };

        var chart = new ApexCharts(document.querySelector("#chartIndex2"), options);
        chart.render();


JS;
$this->registerJS($js, View::POS_END);
?>
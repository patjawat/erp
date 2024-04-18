<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use yii\helpers\Url;


$this->title = "ระบบคลัง";
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<body>

        <div class="row">
              <div class="col-8">

                
<div class="row">

<div class="col-lg-4 col-md-4 col-sm-12 col-sx-12">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <a href="<?=Url::to(['/hr/employees'])?>">
                        <span class="text-muted text-uppercase fs-6">เบิกวัสดุ</span>
                    </a>
                    <h2 class="text-muted text-uppercase fs-4">694 เรื่อง</h2>
                </div>
                <div class="text-center" style="position: relative;">
                    <div id="t-rev" style="min-height: 45px;">
                        <div id="apexchartsdlqwjkgl"
                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                            style="width: 90px; height: 45px;">
                            <i class="fa-solid fa fa-recycle fs-1"></i>
                            <div class="apexcharts-legend"></div>
                            
                        </div>
                    </div>
                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 91px; height: 70px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End-col -->

<div class="col-lg-4 col-md-12 col-sm-12 col-sx-12">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                <a href="#">
                        <span class="text-muted text-uppercase fs-6">ผ่านการตรวจสอบ</span>
                    </a>
                    <h2 class="text-muted text-uppercase fs-4">50 รายการ</h2>
                </div>
                
                <div class="text-center" style="position: relative;">
                    <div id="t-rev" style="min-height: 45px;">
                        <div id="apexchartsdlqwjkgl"
                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                            style="width: 90px; height: 45px;">
                            <i class="fa-solid fa-cash-register fs-1"></i>
                            <div class="apexcharts-legend"></div>
                            
                        </div>
                    </div>
                   
                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 91px; height: 70px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End-col -->

<!-- End-col -->
<div class="col-lg-4 col-md-12 col-sm-12 col-sx-12">
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <span class="text-muted text-uppercase fs-6">อนุมัติ</span>
                    <h2 class="text-muted text-uppercase fs-4">316 รายการ</h2>
                </div>
                <div class="text-center" style="position: relative;">
                    <div id="t-rev" style="min-height: 45px;">
                        <div id="apexchartsdlqwjkgl"
                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                            style="width: 90px; height: 45px;">
                            <i class="fa-solid fa-building fs-1"></i>
                            <div class="apexcharts-legend"></div>
                            
                        </div>
                    </div>
                  
                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 91px; height: 70px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End-col -->

</div>               
                          <div class="card">
                              <div class="card-body">
                              จำนวนมูลค่าการเบิก และจ่ายวัสดุ
                                <div id="chart" style="width:100%;height:550px;"></div>
                              </div>
                          </div>
                          <!-- <div class="card">
                              <div class="card-body">
                              มูลค่าประเภทงบการเงิน (ย้อนหลัง 10 ปี)
                              <div id="line-stack" style="width:100%;height:550px;"></div>
                              </div>
                          </div> -->
                </div>
             
                <div class="col-4">
                <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <span class="text-muted text-uppercase fs-6">จำนวนวัสดุน้อยกว่ากำหนด</span>
                    <h2 class="text-muted text-uppercase fs-4">85 รายการ</h2>
                </div>
                <div class="text-center" style="position: relative;">
                    <div id="t-rev" style="min-height: 45px;">
                        <div id="apexchartsdlqwjkgl"
                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                            style="width: 90px; height: 45px;">
                            <i class="fa-solid fa-building fs-1"></i>
                            <div class="apexcharts-legend"></div>
                            
                        </div>
                    </div>
                  
                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 91px; height: 70px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <div class="flex-grow-1">
                    <span class="text-muted text-uppercase fs-6">จำนวนวัสดุมากว่ากำหนด</span>
                    <h2 class="text-muted text-uppercase fs-4">54 รายการ</h2>
                </div>
                <div class="text-center" style="position: relative;">
                    <div id="t-rev" style="min-height: 45px;">
                        <div id="apexchartsdlqwjkgl"
                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                            style="width: 90px; height: 45px;">
                            <i class="fa-solid fa-building fs-1"></i>
                            <div class="apexcharts-legend"></div>
                            
                        </div>
                    </div>
                  
                    <div class="resize-triggers">
                        <div class="expand-trigger">
                            <div style="width: 91px; height: 70px;"></div>
                        </div>
                        <div class="contract-trigger"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
                        <div class="card">
                            <div class="card-body">
                                <div class="list-group border-0">
                                      <a href="<?=Url::to(['/warehouse/whcheck'])?>" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                                          <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i class="fa-solid fa-server avatar-title text-primary"></i> </div>
                                          <div class="d-flex gap-2 w-100 justify-content-between">
                                              <div>
                                                  <h6 class="mb-0 text-primary">ตรวจรับ</h6>
                                                  <p class="mb-0 opacity-75 fw-light">100 รายการ</p>
                                              </div>
                                          </div>
                                      </a>
                                      <a href="<?=Url::to(['/warehouse/whsup'])?>" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                                          <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i class="fa-solid fa-server avatar-title text-primary"></i> </div>
                                          <div class="d-flex gap-2 w-100 justify-content-between">
                                              <div>
                                                  <h6 class="mb-0 text-primary">คลังหลัก</h6>
                                                  <p class="mb-0 opacity-75 fw-light">6 คลัง</p>
                                              </div>
                                          </div>
                                      </a>
                                      <a href="<?=Url::to(['/warehouse/whdep'])?>" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                                          <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i class="fa-solid fa-server avatar-title text-primary"></i> </div>
                                          <div class="d-flex gap-2 w-100 justify-content-between">
                                              <div>
                                                  <h6 class="mb-0 text-primary">คลังหน่วยงาน</h6>
                                                  <p class="mb-0 opacity-75 fw-light">50 คลัง</p>
                                              </div>
                                          </div>
                                      </a>
                                      <a href="<?=Url::to(['/warehouse/whwithdrow'])?>" class="list-group-item list-group-item-action d-flex gap-3 py-3">
                                          <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i class="fa-solid fa-server avatar-title text-primary"></i> </div>
                                          <div class="d-flex gap-2 w-100 justify-content-between">
                                              <div>
                                                  <h6 class="mb-0 text-primary">เบิกวัสดุ</h6>
                                                  <p class="mb-0 opacity-75 fw-light">180 รายการ</p>
                                              </div>
                                          </div>
                                      </a>
                                   
                                  </div>
                                </div>
                            </div>
                        </div>
                  </div>
              </div>
           
</body>

<?php
use yii\web\View;
$js = <<< JS

var options = {
          series: [{
          name: 'มูลค่าการเบิกวัสดุ',
          data: [ 20000.00, 40000.90, 70000.00, 230000.20, 250000.60, 760000.70, 1350000.60, 1620000.20, 320000.60, 200000.00, 60000.40, 30000.30]
        }, {
          name: 'มูลค่าการจ่ายวัสดุ',
          data: [20000.60, 50000.90, 90000.00, 260000.40, 280000.70, 700000.70, 1750000.60, 1820000.20, 480000.70, 180000.80, 60000.00, 20000.30]
        }],
          chart: {
          type: 'bar',
          height: 350
        },
        plotOptions: {
          bar: {
            horizontal: false,
            columnWidth: '55%',
            endingShape: 'rounded'
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
          categories: ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.'],
        },
        yaxis: {
          title: {
            text: 'บาท'
          }
        },
        fill: {
          opacity: 1
        },
        tooltip: {
          y: {
            formatter: function (val) {
              return val + "บาท"
            }
          }
        }
        };

        var chart = new ApexCharts(document.querySelector("#chart"), options);
        chart.render();





var dom = document.getElementById('line-sections');
var myChart = echarts.init(dom, null, {
  renderer: 'canvas',
  useDirtyRect: false
});
var app = {};

var option;

option = {
  tooltip: {
    trigger: 'axis'
  },
  legend: {
    data: ['มูลค่าการเบิกวัสดุ', 'มูลค่าการจ่ายวัสดุ']
  },
  toolbox: {
    show: true,
    feature: {
      dataView: { show: true, readOnly: false },
      magicType: { show: true, type: ['line', 'bar'] },
      restore: { show: true },
      saveAsImage: { show: true }
    }
  },
  calculable: true,
  xAxis: [
    {
      type: 'category',
      // prettier-ignore
      data: ['ต.ค.', 'พ.ย.', 'ธ.ค.', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.']
    }
  ],
  yAxis: [
    {
      type: 'value'
    }
  ],
  series: [
    {
      name: 'มูลค่าการเบิกวัสดุ',
      type: 'bar',
      data: [
        2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2, 32.6, 20.0, 6.4, 3.3
      ],
      markPoint: {
        data: [
          { type: 'max', name: 'Max' },
          { type: 'min', name: 'Min' }
        ]
      },
      markLine: {
        data: [{ type: 'average', name: 'Avg' }]
      }
    },
    {
      name: 'มูลค่าการจ่ายวัสดุ',
      type: 'bar',
      data: [
        2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2, 48.7, 18.8, 6.0, 2.3
      ],
      markPoint: {
        data: [
          { name: 'Max', value: 182.2, xAxis: 7, yAxis: 183 },
          { name: 'Min', value: 2.3, xAxis: 11, yAxis: 3 }
        ]
      },
      markLine: {
        data: [{ type: 'average', name: 'Avg' }]
      }
    }
  ]
};

if (option && typeof option === 'object') {
  myChart.setOption(option);
}
window.addEventListener('resize', myChart.resize);


JS;
$this->registerJS($js,View::POS_END);


?>

<?php


$js = <<< JS

        var options3 = {
        chart: { height: 420, parentHeightOffset: 0, type: "donut" },
        labels: ['GenB', 'GenX', 'GenY', 'GenZ'],
      series: [20 , 20, 20, 20],
      stroke: { width: 0 },
      dataLabels: {
        enabled: !1,
        formatter: function (e, t) {
          return parseInt(e) + "%";
        },
      },
      legend: {
        show: !0,
        position: "bottom",
        offsetY: 10,
        markers: { width: 8, height: 8, offsetX: -3 },
        itemMargin: { horizontal: 15, vertical: 5 },
        fontSize: "13px",
        fontFamily: "prompt",
        fontWeight: 400,
      },
    //   tooltip: { theme: o },
      grid: { padding: { top: 15 } },
      plotOptions: {
        pie: {
          donut: {
            size: "75%",
            labels: {
              show: !0,
              value: {
                fontSize: "26px",
                fontFamily: "prompt",
                // color: t,
                fontWeight: 500,
                offsetY: -30,
                formatter: function (e) {
                  return parseInt(e) + "%";
                },
              },
              name: { offsetY: 20, fontFamily: "prompt" },
              total: {
                      show: !0,
                fontSize: "0.9rem",
                label: "ทั้งหมด",
                  showAlways: true,
                  show: true
                }
            },
          },
        },
      },
      responsive: [{ breakpoint: 420, options: { chart: { height: 360 } } }],
    };


    var chart3 = new ApexCharts(document.querySelector("#chart3"), options3);
        chart3.render();
JS;
$this->registerJS($js, View::POS_END);
?>


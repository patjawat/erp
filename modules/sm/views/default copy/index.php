<?php
/** @var yii\web\View $this */
use yii\helpers\Html;
use yii\helpers\Url;


$this->title = 'บริหารพัสดุ';
$this->params['breadcrumbs'][] = $this->title;
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-box-seam"></i> <?=$this->title;?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?=$this->render('../default/menu')?>
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
                                    <span class="text-muted text-uppercase fs-6">รายการขอซื้อ/ขอจ้าง</span>
                                    <h2 class="text-muted text-uppercase fs-4">5 รายการ</h2>
                                </div>
                                <div class="text-center" style="position: relative;">
                                    <div id="t-rev" style="min-height: 45px;">
                                        <div id="apexchartsdlqwjkgl"
                                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                            style="width: 90px; height: 45px;">
                                            <i class="fa-solid fa fa-list-ul fs-1"></i>
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
                                    <span class="text-muted text-uppercase fs-6">กำลังดำเนินการ</span>
                                    <h2 class="text-muted text-uppercase fs-4">4 รายการ</h2>
                                </div>
                                <div class="text-center" style="position: relative;">
                                    <div id="t-rev" style="min-height: 45px;">
                                        <div id="apexchartsdlqwjkgl"
                                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                            style="width: 90px; height: 45px;">
                                            <i class="fa-solid fa fa-undo fs-1"></i>
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
                                    <a href="#">
                                        <span class="text-muted text-uppercase fs-6">ดำเนินการเรียบร้อย</span>
                                    </a>
                                    <h2 class="text-muted text-uppercase fs-4">1 รายการ</h2>
                                </div>

                                <div class="text-center" style="position: relative;">
                                    <div id="t-rev" style="min-height: 45px;">
                                        <div id="apexchartsdlqwjkgl"
                                            class="apexcharts-canvas apexchartsdlqwjkgl apexcharts-theme-light"
                                            style="width: 90px; height: 45px;">
                                            <i class="fa-solid fa-check-square fs-1"></i>
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

                <div class="card">
                    <div class="card-body">
                        มูลค่าการจัดซื้อจัดจ้าง (ย้อนหลัง 10 ปี)
                        <div id="line-chart-container" style="width:100%;height:340px;"></div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="card rounded-4 border-0 h-100">
                            <div class="card-body">
                                <div id="chart01" style="width:100%;height:300px;"></div>
                                <center>
                                    <div class="card-title">ข้อมูลจัดซื้อจัดจ้าง แยกตามประเภทวัสดุ
                                        <br>(เปอร์เซ็นต์การจัดซื้อตามแผนวัสดุ)
                                    </div>
                                </center>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card rounded-4 border-0 h-100">
                            <div class="card-body">
                                <div id="chart02" style="width:100%;height:300px;"></div>
                                <center>
                                    <div class="card-title">ข้อมูลจัดซื้อจัดจ้าง แยกตามประเภทครุภัณฑ์
                                        <br>(เปอร์เซ็นต์การจัดซื้อตามแผนครุภัณฑ์)
                                    </div>
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">&nbsp;</div>

                <div class="row">
                    <div class="col-6">
                        <div class="card rounded-4 border-0 h-100">
                            <div class="card-body">
                                <div id="chart03" style="width:100%;height:300px;"></div>
                                <center>
                                    <div class="card-title">ข้อมูลจัดซื้อจัดจ้าง แยกตามประเภทงานจ้างเหมาทั่วไป'
                                        <br>(เปอร์เซ็นต์การจัดซื้อตามแผนงานจ้างเหมาทั่วไป)
                                    </div>
                                </center>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="card rounded-4 border-0 h-100">
                            <div class="card-body">
                                <div id="chart04" style="width:100%;height:300px;"></div>
                                <center>
                                    <div class="card-title">ข้อมูลจัดซื้อจัดจ้าง แยกตามประเภทงานก่อสร้าง
                                        <br>(เปอร์เซ็นต์การจัดซื้อตามแผนงานก่อสร้าง)
                                    </div>
                                </center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End-col -->
        </div>

        <div class="col-4">
            <div class="card rounded-4 border-0 h-30">
                <div class="card-body">
                    อัตราส่วนมูลค่าการจัดซื้อจัดจ้าง
                    <div id="pie-chart-container" style="width:100%;height:100%;"></div>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="list-group border-0">
                        <a href="<?=Url::to(['/sm/sup-request'])?>"
                            class="list-group-item list-group-item-action d-flex gap-3 py-3">
                            <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i
                                    class="fa-solid fa-server avatar-title text-primary"></i> </div>
                            <div class="d-flex gap-2 w-100 justify-content-between">
                                <div>
                                    <h6 class="mb-0 text-primary">ขอซื้อขอจ้าง</h6>
                                    <p class="mb-0 opacity-75 fw-light">0 รายการ</p>
                                </div>
                            </div>
                        </a>
                        <a href="<?=Url::to(['/sm/supregister'])?>"
                            class="list-group-item list-group-item-action d-flex gap-3 py-3">
                            <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i
                                    class="fa-solid fa-server avatar-title text-primary"></i> </div>
                            <div class="d-flex gap-2 w-100 justify-content-between">
                                <div>
                                    <h6 class="mb-0 text-primary">ทะเบียนคุม</h6>
                                    <p class="mb-0 opacity-75 fw-light">5 รายการ</p>
                                </div>
                            </div>
                        </a>
                        <a href="<?=Url::to(['/sm/sup-vendor'])?>"
                            class="list-group-item list-group-item-action d-flex gap-3 py-3">
                            <div class="rounded-2 flex-shrink-0 px-3 py-2 text-body-secondary bg-light"><i
                                    class="fa-solid fa-server avatar-title text-primary"></i> </div>
                            <div class="d-flex gap-2 w-100 justify-content-between">
                                <div>
                                    <h6 class="mb-0 text-primary">ผู้แทนจำหน่าย</h6>
                                    <p class="mb-0 opacity-75 fw-light">0 ร้าน</p>
                                </div>
                            </div>
                        </a>


                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php
                use yii\web\View;
                $js = <<< JS
                var options = {
                          series: [75],
                          chart: {
                          height: 350,
                          type: 'radialBar',
                          toolbar: {
                            show: true
                          }
                        },
                        plotOptions: {
                          radialBar: {
                            startAngle: -135,
                            endAngle: 225,
                            hollow: {
                              margin: 0,
                              size: '70%',
                              background: '#fff',
                              image: undefined,
                              imageOffsetX: 0,
                              imageOffsetY: 0,
                              position: 'front',
                              dropShadow: {
                                enabled: true,
                                top: 3,
                                left: 0,
                                blur: 4,
                                opacity: 0.24
                              }
                            },
                            track: {
                              background: '#fff',
                              strokeWidth: '67%',
                              margin: 0, // margin is in pixels
                              dropShadow: {
                                enabled: true,
                                top: -3,
                                left: 0,
                                blur: 4,
                                opacity: 0.35
                              }
                            },
                        
                            dataLabels: {
                              show: true,
                              name: {
                                offsetY: -10,
                                show: true,
                                color: '#888',
                                fontSize: '17px'
                              },
                              value: {
                                formatter: function(val) {
                                  return parseInt(val);
                                },
                                color: '#111',
                                fontSize: '36px',
                                show: true,
                              }
                            }
                          }
                        },
                        fill: {
                          type: 'gradient',
                          gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            shadeIntensity: 0.5,
                            gradientToColors: ['#ABE5A1'],
                            inverseColors: true,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 100]
                          }
                        },
                        stroke: {
                          lineCap: 'round'
                        },
                        labels: ['Percent'],
                        };

                        var chart = new ApexCharts(document.querySelector("#chart01"), options);
                        chart.render();


                var options02 = {
                          series: [80],
                          chart: {
                          height: 350,
                          type: 'radialBar',
                          toolbar: {
                            show: true
                          }
                        },
                        plotOptions: {
                          radialBar: {
                            startAngle: -135,
                            endAngle: 225,
                            hollow: {
                              margin: 0,
                              size: '70%',
                              background: '#fff',
                              image: undefined,
                              imageOffsetX: 0,
                              imageOffsetY: 0,
                              position: 'front',
                              dropShadow: {
                                enabled: true,
                                top: 3,
                                left: 0,
                                blur: 4,
                                opacity: 0.24
                              }
                            },
                            track: {
                              background: '#fff',
                              strokeWidth: '67%',
                              margin: 0, // margin is in pixels
                              dropShadow: {
                                enabled: true,
                                top: -3,
                                left: 0,
                                blur: 4,
                                opacity: 0.35
                              }
                            },
                        
                            dataLabels: {
                              show: true,
                              name: {
                                offsetY: -10,
                                show: true,
                                color: '#888',
                                fontSize: '17px'
                              },
                              value: {
                                formatter: function(val) {
                                  return parseInt(val);
                                },
                                color: '#111',
                                fontSize: '36px',
                                show: true,
                              }
                            }
                          }
                        },
                        fill: {
                          type: 'gradient',
                          gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            shadeIntensity: 0.5,
                            gradientToColors: ['#ABE5A1'],
                            inverseColors: true,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 100]
                          }
                        },
                        stroke: {
                          lineCap: 'round'
                        },
                        labels: ['Percent'],
                        };

                        var chart02 = new ApexCharts(document.querySelector("#chart02"), options02);
                        chart02.render();



                        var options03 = {
                          series: [62],
                          chart: {
                          height: 350,
                          type: 'radialBar',
                          toolbar: {
                            show: true
                          }
                        },
                        plotOptions: {
                          radialBar: {
                            startAngle: -135,
                            endAngle: 225,
                            hollow: {
                              margin: 0,
                              size: '70%',
                              background: '#fff',
                              image: undefined,
                              imageOffsetX: 0,
                              imageOffsetY: 0,
                              position: 'front',
                              dropShadow: {
                                enabled: true,
                                top: 3,
                                left: 0,
                                blur: 4,
                                opacity: 0.24
                              }
                            },
                            track: {
                              background: '#fff',
                              strokeWidth: '67%',
                              margin: 0, // margin is in pixels
                              dropShadow: {
                                enabled: true,
                                top: -3,
                                left: 0,
                                blur: 4,
                                opacity: 0.35
                              }
                            },
                        
                            dataLabels: {
                              show: true,
                              name: {
                                offsetY: -10,
                                show: true,
                                color: '#888',
                                fontSize: '17px'
                              },
                              value: {
                                formatter: function(val) {
                                  return parseInt(val);
                                },
                                color: '#111',
                                fontSize: '36px',
                                show: true,
                              }
                            }
                          }
                        },
                        fill: {
                          type: 'gradient',
                          gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            shadeIntensity: 0.5,
                            gradientToColors: ['#ABE5A1'],
                            inverseColors: true,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 100]
                          }
                        },
                        stroke: {
                          lineCap: 'round'
                        },
                        labels: ['Percent'],
                        };

                        var chart03 = new ApexCharts(document.querySelector("#chart03"), options03);
                        chart03.render();
                      



                        var options04 = {
                          series: [39],
                          chart: {
                          height: 350,
                          type: 'radialBar',
                          toolbar: {
                            show: true
                          }
                        },
                        plotOptions: {
                          radialBar: {
                            startAngle: -135,
                            endAngle: 225,
                            hollow: {
                              margin: 0,
                              size: '70%',
                              background: '#fff',
                              image: undefined,
                              imageOffsetX: 0,
                              imageOffsetY: 0,
                              position: 'front',
                              dropShadow: {
                                enabled: true,
                                top: 3,
                                left: 0,
                                blur: 4,
                                opacity: 0.24
                              }
                            },
                            track: {
                              background: '#fff',
                              strokeWidth: '67%',
                              margin: 0, // margin is in pixels
                              dropShadow: {
                                enabled: true,
                                top: -3,
                                left: 0,
                                blur: 4,
                                opacity: 0.35
                              }
                            },
                        
                            dataLabels: {
                              show: true,
                              name: {
                                offsetY: -10,
                                show: true,
                                color: '#888',
                                fontSize: '17px'
                              },
                              value: {
                                formatter: function(val) {
                                  return parseInt(val);
                                },
                                color: '#111',
                                fontSize: '36px',
                                show: true,
                              }
                            }
                          }
                        },
                        fill: {
                          type: 'gradient',
                          gradient: {
                            shade: 'dark',
                            type: 'horizontal',
                            shadeIntensity: 0.5,
                            gradientToColors: ['#ABE5A1'],
                            inverseColors: true,
                            opacityFrom: 1,
                            opacityTo: 1,
                            stops: [0, 100]
                          }
                        },
                        stroke: {
                          lineCap: 'round'
                        },
                        labels: ['Percent'],
                        };

                        var chart04 = new ApexCharts(document.querySelector("#chart04"), options04);
                        chart04.render();


                //============================================================

                var options = {
                  labels: ['มูลค่าการจัดซื้อวัสดุ', 'มูลค่าการจัดซื้อครุภัณฑ์', 'มูลค่าการงานจ้างเหมา', 'มูลค่าการงานก่อสร้าง'],
                  series: [1048, 580, 484, 300],
                  
                  chart: {
                  type: 'donut',
                  fontFamily: 'kanit,sans-serif',
                },
                responsive: [{
                  breakpoint: 480,
                  options: {
                    chart: {
                      width: 200
                    },
                    legend: {
                      position: 'bottom'
                    }
                  }
                }]
                };

                var chart = new ApexCharts(document.querySelector("#pie-chart-container"), options);
                chart.render();


                var options = {
                          series: [{
                          name: 'มูลคาการจัดซื้อจัดจ้างตามแผน',
                          data: [2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2]
                        }, {
                          name: 'มูลคาการจัดซื้อจัดจ้างทั้งหมด',
                          data: [2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2]
                        }],
                          chart: {
                          type: 'bar',
                          height: 350,
                          fontFamily: 'kanit,sans-serif',
              
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
                          categories: ['ปี 2563','ปี 2564','ปี 2565','ปี 2566','ปี 2567','ปี 2568','ปี 2569','ปี 2570'],
                        },
                        yaxis: {
                          title: {
                            text: 'มูลค่า (ล้านบาท)'
                          }
                        },
                        fill: {
                          opacity: 1
                        },
                        tooltip: {
                          y: {
                            formatter: function (val) {
                              return  val + " ล้านบาท"
                            }
                          }
                        }
                        };

                        var chart = new ApexCharts(document.querySelector("#line-chart-container"), options);
                        chart.render();


                var dom = document.getElementById('line');
                var myChart = echarts.init(dom, null, {
                  renderer: 'canvas',
                  useDirtyRect: false
                });
                var app = {};

                var option;

                option = {
                    title : {
                        text: 'มูลค่าจัดซื้อจัดจ้างในแต่ละปี',
                        // subtext: 'ปป'
                    },
                    tooltip : {
                        trigger: 'axis'
                    },
                    legend: {
                        data:['蒸发量']
                    },
                    chart: {
                        fontFamily: 'kanit,sans-serif',
                    },
                    toolbox: {
                        show : true,
                        feature : {
                            mark : {show: true},
                            dataView : {show: true, readOnly: false},
                            magicType : {show: true, type: ['line', 'bar']},
                            restore : {show: true},
                            saveAsImage : {show: true}
                        }
                    },
                    calculable : true,
                    xAxis : [
                        {
                            type : 'category',
                            data : ['ปี 2563','ปี 2564','ปี 2565','ปี 2566','ปี 2567','ปี 2568','ปี 2569','ปี 2570']
                        }
                    ],
                    yAxis : [
                        {
                            type : 'value'
                        }
                    ],
                    series : [
                        {
                            name:'มูลคาการจัดซื้อจัดจ้างตามแผน',
                            type:'bar',
                            data:[2.0, 4.9, 7.0, 23.2, 25.6, 76.7, 135.6, 162.2],
                            markPoint : {
                                data : [
                                    {type : 'max', name: '最大值'},
                                    {type : 'min', name: '最小值'}
                                ]
                            },
                            markLine : {
                                data : [
                                    {type : 'average', name: '平均值'}
                                ]
                            }
                        },
                      {
                            name:'มูลคาการจัดซื้อจัดจ้างทั้งหมด',
                            type:'bar',
                            data:[2.6, 5.9, 9.0, 26.4, 28.7, 70.7, 175.6, 182.2, 48.7, 18.8, 6.0, 2.3],
                            markPoint : {
                                data : [
                                    {name : '年最高', value : 182.2, xAxis: 7, yAxis: 183, symbolSize:18},
                                    {name : '年最低', value : 2.3, xAxis: 11, yAxis: 3}
                                ]
                            },
                            markLine : {
                                data : [
                                    {type : 'average', name : '平均值'}
                                ]
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
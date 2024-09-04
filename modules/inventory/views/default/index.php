<?php

/**
 * @var yii\web\View $this
 */

use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Php;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\helpers\Json;
use app\modules\inventory\models\Stock;
use app\models\Categorise;

$this->title = 'ระบบคลังวัสดุ';
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>


<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> มูลค่าเบิกจ่ายวัสดุทั้งหมด</h6>
                    <div class="dropdown float-end">
                        <a href="javascript:void(0)" class="rounded-pill dropdown-toggle me-0" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="fa-solid fa-ellipsis"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right">
                            <?= Html::a('<i class="fa-solid fa-circle-info text-primary me-2"></i> เพิ่มเติม', ['/sm/order'], ['class' => 'dropdown-item']) ?>
                        </div>
                    </div>
                </div>
                <!-- <div id="inventoryCharts"></div> -->
                <div id="showChart">
                    <div class="placeholder-glow">
                        <div class="d-flex justufy-content-row gap-5">
                            <?php for ($x = 1; $x <= 12; $x++): ?>
                            <div class="d-flex align-items-end gap-2">
                                <span class="placeholder" style="width:10px;height:200px"></span>
                                <span class="placeholder" style="width:10px;height:100px"></span>
                            </div>
                            <?php endfor ?>

                        </div>
                    </div>

                </div>
            </div>
        </div>




    </div>
    <div class="col-xl-6">
        <!-- #### -->
        <div class="row">
            <div class="col-6">
                <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                    <div class="card-body">
                        <div>
                            <div class="d-flex align-items-center">
                                <div class="avatar">
                                    <div class="avatar-title rounded bg-primary bg-opacity-25">
                                         <i class="fa-solid fa-chart-line font-size-24 mb-0 text-primary"></i>
                                       
                                    </div>
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 font-size-15">เบิกวัสดุ</h6>
                                </div>

                                <div class="flex-shrink-0">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="bx bx-dots-horizontal text-muted font-size-22"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" style="">
                                            <a class="dropdown-item" href="#">Yearly</a>
                                            <a class="dropdown-item" href="#">Monthly</a>
                                            <a class="dropdown-item" href="#">Weekly</a>
                                            <a class="dropdown-item" href="#">Today</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="mt-4 pt-1 mb-0 font-size-22">$34,123.20
                                    <span class="text-success fw-medium font-size-13 align-middle"><i
                                            class="mdi mdi-arrow-up"></i> 8.34% </span>
                                </h4>
                                <div class="d-flex mt-1 align-items-end overflow-hidden">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-0 text-truncate">มูลค่าการเบิกวัสดุทั้งหมด</p>
                                    </div>
                                    <div class="flex-shrink-0" style="position: relative;">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">

                <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                    <div class="card-body">
                        <div>
                            <div class="d-flex align-items-center">
                                <div class="avatar">
                                    <div class="avatar-title rounded bg-primary bg-opacity-25">
                                         <i class="fa-solid fa-chart-line font-size-24 mb-0 text-primary"></i>
                                    </div>
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 font-size-15">จ่ายวัสดุ</h6>
                                </div>

                                <div class="flex-shrink-0">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="bx bx-dots-horizontal text-muted font-size-22"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" style="">
                                            <a class="dropdown-item" href="#">Yearly</a>
                                            <a class="dropdown-item" href="#">Monthly</a>
                                            <a class="dropdown-item" href="#">Weekly</a>
                                            <a class="dropdown-item" href="#">Today</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="mt-4 pt-1 mb-0 font-size-22">$34,123.20
                                    <span class="text-success fw-medium font-size-13 align-middle"><i
                                            class="mdi mdi-arrow-up"></i> 8.34% </span>
                                </h4>
                                <div class="d-flex mt-1 align-items-end overflow-hidden">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-0 text-truncate">มูลค่าการจ่ายวัสดุทั้งหมด</p>
                                    </div>
                                    <div class="flex-shrink-0" style="position: relative;">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>




            </div>

            <div class="col-6">
                <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                    <div class="card-body">
                        <div>
                            <div class="d-flex align-items-center">
                                <div class="avatar">
                                    <div class="avatar-title rounded bg-primary bg-opacity-25">
                                         <i class="fa-solid fa-chart-line font-size-24 mb-0 text-primary"></i>
                                    </div>
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 font-size-15">วัสดุเหลือน้อย</h6>
                                </div>

                                <div class="flex-shrink-0">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="bx bx-dots-horizontal text-muted font-size-22"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" style="">
                                            <a class="dropdown-item" href="#">Yearly</a>
                                            <a class="dropdown-item" href="#">Monthly</a>
                                            <a class="dropdown-item" href="#">Weekly</a>
                                            <a class="dropdown-item" href="#">Today</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="mt-4 pt-1 mb-0 font-size-22">$34,123.20
                                    <span class="text-success fw-medium font-size-13 align-middle"><i
                                            class="mdi mdi-arrow-up"></i> 8.34% </span>
                                </h4>
                                <div class="d-flex mt-1 align-items-end overflow-hidden">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-0 text-truncate">มูลค่าการเบิกวัสดุทั้งหมด</p>
                                    </div>
                                    <div class="flex-shrink-0" style="position: relative;">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                    <div class="card-body">
                        <div>
                            <div class="d-flex align-items-center">
                                <div class="avatar">
                                    <div class="avatar-title rounded bg-primary bg-opacity-25">
                                         <i class="fa-solid fa-chart-line font-size-24 mb-0 text-primary"></i>
                                    </div>
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 font-size-15">วัสดุสูงกว่ากำหนด</h6>
                                </div>

                                <div class="flex-shrink-0">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle" href="#" data-bs-toggle="dropdown"
                                            aria-haspopup="true" aria-expanded="false">
                                            <i class="bx bx-dots-horizontal text-muted font-size-22"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" style="">
                                            <a class="dropdown-item" href="#">Yearly</a>
                                            <a class="dropdown-item" href="#">Monthly</a>
                                            <a class="dropdown-item" href="#">Weekly</a>
                                            <a class="dropdown-item" href="#">Today</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h4 class="mt-4 pt-1 mb-0 font-size-22">$34,123.20
                                    <span class="text-success fw-medium font-size-13 align-middle"><i
                                            class="mdi mdi-arrow-up"></i> 8.34% </span>
                                </h4>
                                <div class="d-flex mt-1 align-items-end overflow-hidden">
                                    <div class="flex-grow-1">
                                        <p class="text-muted mb-0 text-truncate">มูลค่าการเบิกวัสดุทั้งหมด</p>
                                    </div>
                                    <div class="flex-shrink-0" style="position: relative;">

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- #### -->
            <!-- <div class="col-6">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
          <div class="card-body">
            <h2 id="showTotalPrice">0</h2>
          </div>
          <div class="card-footer border-0">รวมมูลค่ารับเข้า</div>
        </div>

      </div>
      <div class="col-6">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
          <div class="card-body">
            <h2 id="showTotalOrder">0</h2>
          </div>
          <div class="card-footer border-0">จำนวนรับเข้า</div>
        </div>

      </div> -->
        </div>

    </div>
</div>

<div class="row">

    <div class="col-xl-8">
  <?=$this->render('list_warehouse')?>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body position-relative">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1 overflow-hidden">
                        <h5 class="card-title mb-4 text-truncate">ปริมาณวัสดุตามหมวดหมู่</h5>
                    </div>
                    <div class="flex-shrink-0 ms-2">
                        <!-- <div class="dropdown">
                            <a class="dropdown-toggle text-reset" href="#" data-bs-toggle="dropdown"
                                aria-haspopup="true" aria-expanded="false">
                                <span class="fw-semibold">Sort By:</span> <span class="text-muted">Weekly<i
                                        class="mdi mdi-chevron-down ms-1"></i></span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="#">Yearly</a>
                                <a class="dropdown-item" href="#">Monthly</a>
                                <a class="dropdown-item" href="#">Weekly</a>
                            </div>
                        </div> -->
                    </div>
                </div>
                <div id="saleing-categories"
                    data-colors="[&quot;#1f58c7&quot;, &quot;#4976cf&quot;,&quot;#6a92e1&quot;, &quot;#e6ecf9&quot;]">
                </div>
            </div>
        </div>


    </div>
</div>

<!-- end col-6 -->
</div>
<!-- 
<div class="row">
    <div class="col-12">
    <div id="showStoreInWarehouse">
            <div class="placeholder-glow">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="d-flex justify-content-between">
                                <h6 class="placeholder col-6"></h6>
                                <a class="btn btn-sm btn-light rounded-pill placeholder"
                                    href="/inventory/warehouse/list-order-request">แสดงท้ังหมด</a>
                            </div>
                            <table class="table table-primary">
                                <thead>
                                    <tr>
                                        <th scope="col">รายการ</th>
                                        <th>คลัง</th>
                                        <th>สถานะ</th>
                                        <th style="width:100px">ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($x = 1; $x <= 2; $x++): ?>
                                    <tr class="">
                                        <td>
                                            <div class="d-flex">
                                                <?= Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar avatar-sm bg-primary text-white placeholder']) ?>
                                                <div class="avatar-detail text-truncate d-flex flex-column">
                                                    <h6 class="mb-1 fs-13 placeholder"></h6>
                                                    <p class="text-muted mb-0 fs-13 placeholder col-6"></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="placeholder col-12"></span></td>
                                        <td><span class="placeholder col-12"></span></td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>

        </div>


        <div id="showOrderRequestInWarehouse" style="min-height: 463px;">
            <div class="placeholder-glow">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="d-flex justify-content-between">
                                <h6 class="placeholder col-6"></h6>
                            </div>
                            <table class="table table-primary">
                                <thead>
                                    <tr>
                                        <th scope="col">รายการ</th>
                                        <th>คลัง</th>
                                        <th>สถานะ</th>
                                        <th style="width:100px">ดำเนินการ</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for ($x = 1; $x <= 2; $x++): ?>
                                    <tr class="">
                                        <td>
                                            <div class="d-flex">
                                                <?= Html::img('@web/img/placeholder-img.jpg', ['class' => 'avatar avatar-sm bg-primary text-white placeholder']) ?>
                                                <div class="avatar-detail text-truncate d-flex flex-column">
                                                    <h6 class="mb-1 fs-13 placeholder"></h6>
                                                    <p class="text-muted mb-0 fs-13 placeholder col-6"></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td><span class="placeholder col-12"></span></td>
                                        <td><span class="placeholder col-12"></span></td>
                                        <td class="text-center">
                                            <a class="btn btn-light placeholder" href="#"></a>
                                        </td>
                                    </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                        <a class="btn btn-sm btn-light rounded-pill placeholder"
                            href="/inventory/warehouse/list-order-request">แสดงท้ังหมด</a>
                    </div>
                </div>
            </div>
        </div>


    </div> -->


    <?php

  use yii\web\View;
  // $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
  // $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

  $StoreInWarehouseUrl = Url::to(['/inventory/stock/warehouse']);
  $chartUrl = Url::to(['/inventory/stock/view-chart']);
  $OrderRequestInWarehouseUrl = Url::to(['/inventory/warehouse/list-order-request']);
  // $chartSummeryIn = Json::encode($chartSummary['in']);
  // $chartSummeryOut = Json::encode($chartSummary['out']);
  $js = <<< JS
  // getPendingOrder()
  // getlistOrderRequest()


  getStoreInWarehouse()
  getOrderRequestInWarehouse()
  getChart();

  //รายการใน Stock
  async function getOrderRequestInWarehouse(){
    await $.ajax({
      type: "get",
      url: "$StoreInWarehouseUrl",
      dataType: "json",
      success: function (res) {
        $('#showStoreInWarehouse').html(res.content)
      }
    });
  }

  // รายการขอเบิก
  async function getStoreInWarehouse(){
    await $.ajax({
      type: "get",
      url: "$OrderRequestInWarehouseUrl",
      dataType: "json",
      success: function (res) {
        $('#showOrderRequestInWarehouse').html(res.content)
        $('#OrderCount').html(res.count)
        $('#OrderConfirm').html(res.confirm)
        $('#showTotalOrder').html(res.totalOrder)
        $('#showTotalPrice').html(res.totalPrice)
      }
    });
  }

  async function getChart(){
    await $.ajax({
      type: "get",
      url: "$chartUrl",
      dataType: "json",
      success: function (res) {
        $('#showChart').html(res.content)
       
      }
    });
  }
 

  function getChartColorsArray(e) {
      if (null !== document.getElementById(e)) {
        var r = document.getElementById(e).getAttribute("data-colors");
        return (r = JSON.parse(r)).map(function(e) {
          var r = e.replace(" ", "");
          if (-1 == r.indexOf("--")) return r;
          var t = getComputedStyle(document.documentElement).getPropertyValue(r);
          return t || void 0
        })
      }
    }
    

  options = {
      chart: {
        height: 350,
        type: "donut"
      },
      series: [24, 18, 13, 15],
      labels: ["Fashion", "Beauty", "Clothing", "Others"],
      colors: barchartColors = getChartColorsArray("saleing-categories"),
      plotOptions: {
        pie: {
          startAngle: 25,
          donut: {
            size: "72%",
            labels: {
              show: !0,
              total: {
                show: !0,
                label: "Products",
                fontSize: "22px",
                fontFamily: "Montserrat,sans-serif",
                fontWeight: 600
              }
            }
          }
        }
      },
      legend: {
        show: !1,
        position: "bottom",
        horizontalAlign: "center",
        verticalAlign: "middle",
        floating: !1,
        fontSize: "14px",
        offsetX: 0
      },
      dataLabels: {
        style: {
          fontSize: "11px",
          fontFamily: "Montserrat,sans-serif",
          fontWeight: "bold",
          colors: void 0
        },
        background: {
          enabled: !0,
          foreColor: "#fff",
          padding: 4,
          borderRadius: 2,
          borderWidth: 1,
          borderColor: "#fff",
          opacity: 1
        }
      },
      responsive: [{
        breakpoint: 600,
        options: {
          chart: {
            height: 240
          },
          legend: {
            show: !1
          }
        }
      }]
    };
    (chart = new ApexCharts(document.querySelector("#saleing-categories"), options)).render();


 
  JS;
  $this->registerJS($js, View::POS_END);
  ?>
    <!-- <script>
    function getChartColorsArray(e) {
      if (null !== document.getElementById(e)) {
        var r = document.getElementById(e).getAttribute("data-colors");
        return (r = JSON.parse(r)).map(function(e) {
          var r = e.replace(" ", "");
          if (-1 == r.indexOf("--")) return r;
          var t = getComputedStyle(document.documentElement).getPropertyValue(r);
          return t || void 0
        })
      }
    }
    var barchartColors = getChartColorsArray("mini-1"),
      sparklineoptions1 = {
        series: [{
          data: [12, 14, 2, 47, 42, 15, 47, 75, 65, 19, 14]
        }],
        chart: {
          type: "area",
          width: 110,
          height: 35,
          sparkline: {
            enabled: !0
          }
        },
        fill: {
          type: "gradient",
          gradient: {
            shadeIntensity: 1,
            inverseColors: !1,
            opacityFrom: .45,
            opacityTo: .05,
            stops: [20, 100, 100, 100]
          }
        },
        stroke: {
          curve: "smooth",
          width: 2
        },
        colors: barchartColors,
        tooltip: {
          fixed: {
            enabled: !1
          },
          x: {
            show: !1
          },
          y: {
            title: {
              formatter: function(e) {
                return ""
              }
            }
          },
          marker: {
            show: !1
          }
        }
      },
      sparklinechart1 = new ApexCharts(document.querySelector("#mini-1"), sparklineoptions1);
    sparklinechart1.render();
    sparklineoptions1 = {
      series: [{
        data: [65, 14, 2, 47, 42, 15, 47, 75, 65, 19, 14]
      }],
      chart: {
        type: "area",
        width: 110,
        height: 35,
        sparkline: {
          enabled: !0
        }
      },
      fill: {
        type: "gradient",
        gradient: {
          shadeIntensity: 1,
          inverseColors: !1,
          opacityFrom: .45,
          opacityTo: .05,
          stops: [20, 100, 100, 100]
        }
      },
      stroke: {
        curve: "smooth",
        width: 2
      },
      colors: barchartColors = getChartColorsArray("mini-2"),
      tooltip: {
        fixed: {
          enabled: !1
        },
        x: {
          show: !1
        },
        y: {
          title: {
            formatter: function(e) {
              return ""
            }
          }
        },
        marker: {
          show: !1
        }
      }
    };
    (sparklinechart1 = new ApexCharts(document.querySelector("#mini-2"), sparklineoptions1)).render();
    sparklineoptions1 = {
      series: [{
        data: [12, 75, 2, 47, 42, 15, 47, 75, 65, 19, 14]
      }],
      chart: {
        type: "area",
        width: 110,
        height: 35,
        sparkline: {
          enabled: !0
        }
      },
      fill: {
        type: "gradient",
        gradient: {
          shadeIntensity: 1,
          inverseColors: !1,
          opacityFrom: .45,
          opacityTo: .05,
          stops: [20, 100, 100, 100]
        }
      },
      stroke: {
        curve: "smooth",
        width: 2
      },
      colors: barchartColors = getChartColorsArray("mini-3"),
      tooltip: {
        fixed: {
          enabled: !1
        },
        x: {
          show: !1
        },
        y: {
          title: {
            formatter: function(e) {
              return ""
            }
          }
        },
        marker: {
          show: !1
        }
      }
    };
    (sparklinechart1 = new ApexCharts(document.querySelector("#mini-3"), sparklineoptions1)).render();
    sparklineoptions1 = {
      series: [{
        data: [12, 14, 2, 47, 42, 15, 47, 75, 65, 19, 70]
      }],
      chart: {
        type: "area",
        width: 110,
        height: 35,
        sparkline: {
          enabled: !0
        }
      },
      fill: {
        type: "gradient",
        gradient: {
          shadeIntensity: 1,
          inverseColors: !1,
          opacityFrom: .45,
          opacityTo: .05,
          stops: [20, 100, 100, 100]
        }
      },
      stroke: {
        curve: "smooth",
        width: 2
      },
      colors: barchartColors = getChartColorsArray("mini-4"),
      tooltip: {
        fixed: {
          enabled: !1
        },
        x: {
          show: !1
        },
        y: {
          title: {
            formatter: function(e) {
              return ""
            }
          }
        },
        marker: {
          show: !1
        }
      }
    };
    (sparklinechart1 = new ApexCharts(document.querySelector("#mini-4"), sparklineoptions1)).render();
    var options = {
        series: [{
          data: [4, 6, 10, 17, 15, 19, 23, 27, 29, 25, 32, 35]
        }],
        chart: {
          toolbar: {
            show: !1
          },
          height: 323,
          type: "bar",
          events: {
            click: function(e, r, t) {}
          }
        },
        plotOptions: {
          bar: {
            columnWidth: "80%",
            distributed: !0,
            borderRadius: 8
          }
        },
        fill: {
          opacity: 1
        },
        stroke: {
          show: !1
        },
        dataLabels: {
          enabled: !1
        },
        legend: {
          show: !1
        },
        colors: barchartColors = getChartColorsArray("overview"),
        xaxis: {
          categories: ["Jan", "Feb", "Mar", "Apr", "May", "jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"]
        }
      },
      chart = new ApexCharts(document.querySelector("#overview"), options);
    chart.render();


    options = {
      chart: {
        height: 350,
        type: "donut"
      },
      series: [24, 18, 13, 15],
      labels: ["Fashion", "Beauty", "Clothing", "Others"],
      colors: barchartColors = getChartColorsArray("saleing-categories"),
      plotOptions: {
        pie: {
          startAngle: 25,
          donut: {
            size: "72%",
            labels: {
              show: !0,
              total: {
                show: !0,
                label: "Products",
                fontSize: "22px",
                fontFamily: "Montserrat,sans-serif",
                fontWeight: 600
              }
            }
          }
        }
      },
      legend: {
        show: !1,
        position: "bottom",
        horizontalAlign: "center",
        verticalAlign: "middle",
        floating: !1,
        fontSize: "14px",
        offsetX: 0
      },
      dataLabels: {
        style: {
          fontSize: "11px",
          fontFamily: "Montserrat,sans-serif",
          fontWeight: "bold",
          colors: void 0
        },
        background: {
          enabled: !0,
          foreColor: "#fff",
          padding: 4,
          borderRadius: 2,
          borderWidth: 1,
          borderColor: "#fff",
          opacity: 1
        }
      },
      responsive: [{
        breakpoint: 600,
        options: {
          chart: {
            height: 240
          },
          legend: {
            show: !1
          }
        }
      }]
    };
    (chart = new ApexCharts(document.querySelector("#saleing-categories"), options)).render();
    var worldemapmarkers = new jsVectorMap({
      map: "world_merc",
      selector: "#world-map-markers",
      zoomOnScroll: !1,
      zoomButtons: !1,
      selectedMarkers: [0, 2],
      markersSelectable: !0,
      regionStyle: {
        initial: {
          fill: "#cfd9ed"
        }
      },
      markers: [{
        name: "United States",
        coords: [31.9474, 35.2272]
      }, {
        name: "Italy",
        coords: [61.524, 105.3188]
      }, {
        name: "French",
        coords: [56.1304, -106.3468]
      }, {
        name: "Spain",
        coords: [71.7069, -42.6043]
      }],
      markerStyle: {
        initial: {
          fill: "#1f58c7"
        },
        selected: {
          fill: "#1f58c7"
        }
      },
      labels: {
        markers: {
          render: function(e) {
            return e.name
          }
        }
      }
    });
  </script> -->
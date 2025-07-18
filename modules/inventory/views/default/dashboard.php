<?php

/**
 * @var View $this
 */

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\db\Expression;
use app\modules\inventory\models\Stock;
use app\modules\inventory\models\StockEvent;

$this->title = 'Dashboard';
$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory/default/index']];
$this->params['breadcrumbs'][] = $this->title;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('../default/menu_dashbroad'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?=$this->render('menu_dashbroad',['active' => 'dashboard'])?>
<?php $this->endBlock(); ?>



<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6 class="card-title"><i class="fa-solid fa-chart-simple"></i> มูลค่ารับ-จ่ายวัสดุ</h6>
                    <div class="mb-3">
                        <?php echo $this->render('_search_year', ['model' => $searchModel]); ?></div>
                </div>
                <?php echo $this->render('chart_summary', ['model' => $searchModel]); ?>
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
                                        <i class="fa-solid fa-rocket mb-0 text-primary"></i>

                                    </div>
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-0 font-size-15">ยอดยกมา</h6>
                                </div>
                            </div>
                            <div>
                                <h3 class="mt-4 pt-1 mb-0 font-size-22">
                                    <?php echo number_format($searchModel->LastTotalStock(),2); ?>
                                    </h4>
                                    <div class="d-flex mt-1 align-items-end overflow-hidden">
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-0 text-truncate">รวมมูลค่าวัสดุคงเหลือ</p>
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
                                    <h6 class="mb-0 font-size-15">ยอดใช้ไป</h6>
                                </div>

                            </div>

                            <div>
                            <?php
                                    //echo $searchModel->OutSummary('SUB');
                                // echo $searchModel->OutSummary('BRANCH');
                                ?>
                                <h3 class="mt-4 pt-1 mb-0 font-size-22">
                                    
                      
                                <?php  echo  number_format(($searchModel->OutSummary('SUB')+$searchModel->OutSummary('BRANCH')),2); ?>
                                    </h4>
                                    <div class="d-flex mt-1 align-items-end overflow-hidden">
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-0 text-truncate">รวมมูลค่าที่ใช้ไป</p>
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
                                    <h6 class="mb-0 font-size-15">ยอดรับเข้า</h6>
                                </div>
                            </div>
                            <div>
                                
                                <h3 class="mt-4 pt-1 mb-0 font-size-22"> <?php echo number_format($searchModel->ReceiveMainSummary(),2); ?>
                                    </h4>
                                    <div class="d-flex mt-1 align-items-end overflow-hidden">
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-0 text-truncate">ยอดรับเข้า</p>
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
                                    <h6 class="mb-0 font-size-15">มูลค่าคงเหลือ</h6>
                                </div>

                            </div>

                            <div>
                                <h3 class="mt-4 pt-1 mb-0 font-size-22">
                                <?php echo number_format($searchModel->TotalPrice(),2); ?>
                                    </h4>
                                    <div class="d-flex mt-1 align-items-end overflow-hidden">
                                        <div class="flex-grow-1">
                                            <p class="text-muted mb-0 text-truncate">มูลค่าคงเหลือ</p>
                                        </div>
                                        <div class="flex-shrink-0" style="position: relative;">

                                        </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xl-8">
        <?php
        
        // try {
            echo $this->render('list_warehouse', [
                'searchModel' => $searchModel,
                'dataProvider' => $dataProvider,
                'searchModelWarehouse' => $searchModelWarehouse,
                'dataProviderWarehouse' => $dataProviderWarehouse,
            ]);
        // } catch (\Throwable $th) {
        //     //throw $th;
        // }
        ?>
        <div id="showWarehouse"></div>
    </div>
    <div class="col-xl-4">
        <div class="card">
            <div class="card-body position-relative">
                <div class="d-flex align-items-start">
                    <div class="flex-grow-1 overflow-hidden">
                        <h5 class="card-title mb-4 text-truncate">ปริมาณวัสดุตามหมวดหมู่</h5>
                    </div>
                    <div class="flex-shrink-0 ms-2">

                    </div>
                </div>
                <!-- <div id="saleing-categories" data-colors="[&quot;#1f58c7&quot;, &quot;#4976cf&quot;,&quot;#6a92e1&quot;, &quot;#e6ecf9&quot;]"> -->
                <div id="saleing-categories"
                    data-colors="[&quot;#FE9800&quot;, &quot;#1770B2&quot;,&quot;#1770B2&quot;, &quot;#0A67AD&quot;, &quot;#0F6AAF&quot;]">
                </div>
            </div>
        </div>


    </div>
</div>



<?php
$label = Json::encode($label);
$series = Json::encode($series);


// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

$StoreInWarehouseUrl = Url::to(['/inventory/stock/warehouse']);
$productSummeryUrl = Url::to(['/inventory/default/product-summary']);
$OrderRequestInWarehouseUrl = Url::to(['/inventory/warehouse/list-order-request']);
$js = <<< JS
  // getPendingOrder()
  // getlistOrderRequest()


  getStoreInWarehouse()
  getOrderRequestInWarehouse()
  productSummery();



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
    

    function productSummery()
    {
     
            const rawData = $series;
            const series = rawData.map(val => parseFloat(val.replace(/,/g, '')));
        options = {
            chart: {
                height: 350,
                type: "donut"
            },
            series: series,
            labels: $label,
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
                        fontFamily: "Prompt, sans-serif",
                        label: "วัสดุทั้งหมด",
                        fontSize: "22px",
                        fontWeight: 600,
                        formatter: function (w) {
                    return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(w.globals.seriesTotals.reduce((a, b) => a + b, 0));
                    }
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
    //   stroke: {
    //     width: 4,
    //     colors: ['#ffffff'],
    //      lineCap: 'round'
    // },
    
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
      tooltip: {
    y: {
      formatter: function (val) {
        return new Intl.NumberFormat('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(val);
      }
    },
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
  }

 
  JS;
$this->registerJS($js, View::POS_END);
?>
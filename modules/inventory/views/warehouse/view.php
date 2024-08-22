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

$this->title = $model->warehouse_name;
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
    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <h2 id="OrderCount">0</h2>
            </div>
            <div class="card-footer border-0">จำนวนการขอเบิกวัสดุ</div>
        </div>
    </div>
    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <h2 id="OrderConfirm">0</h2>
            </div>
            <div class="card-footer border-0">หัวหน้าเห็นชอบ</div>
        </div>
    </div>
    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <h2 id="showTotalPrice">0</h2>
            </div>
            <div class="card-footer border-0">มูลค่ารวม</div>
        </div>

    </div>
    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <h2 id="showTotalOrder">0</h2>
            </div>
            <div class="card-footer border-0">จำนวนรับเข้า</div>
        </div>

    </div>
</div>
<div class="row">
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6 class="card-title">ปริมาณเบิก/จ่าย</h6>
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
                <div id="inventoryCharts"></div>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div id="showOrderRequestInWarehouse"></div>


    </div>

    <!-- end col-6 -->
</div>

<div class="row">
    <div class="col-12">
        <div id="showStoreInWarehouse"></div>
    </div>
</div>

<?php
use yii\web\View;
// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

$StoreInWarehouseUrl = Url::to(['/inventory/stock/warehouse']);
$OrderRequestInWarehouseUrl = Url::to(['/inventory/warehouse/list-order-request']);
$chartSummeryIn = Json::encode($chartSummary['in']);
$chartSummeryOut = Json::encode($chartSummary['out']);
$js = <<< JS
  // getPendingOrder()
  // getlistOrderRequest()


  getStoreInWarehouse()
  getOrderRequestInWarehouse()

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


  var options = {
      plotOptions: {
            bar: { 
              horizontal: false,
               columnWidth: "50%", 
               endingShape: "rounded",
               startingShape: 'rounded',
               borderRadius: 10 
              },
          },
          series: [
            { name: "เข้า", data: $chartSummeryIn },
            { name: "ออก", data: $chartSummeryOut },
          ],
          colors: ["#0966ad", "#EA5455"],
          chart: {
            type: "bar",
            height: 450,
            stacked: true,
            zoom: { enabled: true },
          },
          responsive: [
            {
              breakpoint: 280,
              options: { legend: { position: "top", offsetY: 0 } },
            },
          ],

          xaxis: {
            categories: [
              "ต.ค.",
              "พ.ย.",
              "ธ.ค.",
              "ม.ค.",
              "ก.พ.",
              "มี.ค.",
              "เม.ย.",
              "พ.ย.",
              "มิ.ย.",
              "ก.ค.",
              "ส.ค.",
              "ก.ย.",
            ],
          },
          legend: { position: "bottom"},
          fill: { opacity: 1 },
        };
        var chart = new ApexCharts(
          document.querySelector("#inventoryCharts"),
          options
        );
        chart.render();    
  JS;
$this->registerJS($js, View::POS_END);
?>
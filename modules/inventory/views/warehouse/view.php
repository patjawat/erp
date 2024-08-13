<?php

/**
 * @var yii\web\View $this
 */

use PHPUnit\TextUI\XmlConfiguration\CodeCoverage\Report\Php;
use yii\helpers\Html;
use yii\helpers\Url;
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
        <div class="d-flex justify-conent-betwee gap-3">
            <div class="card w-100">
                <div class="card-body">
                    <h2 id="OrderCount">0</h2>
                    <p class="card-text">จำนวนการขอเบิกวัสดุ</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">50 เรื่อง</h4>
                <p class="card-text">หัวหน้าเห็นชอบ</p>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">50 เรื่อง</h4>
                <p class="card-text">หัวหน้าเห็นชอบ</p>
            </div>
        </div>
    </div>
    <div class="col-3">
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">50 เรื่อง</h4>
                <p class="card-text">หัวหน้าเห็นชอบ</p>
            </div>
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
                        <div class="dropdown-menu dropdown-menu-right" style="">
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

$StoreInWarehouseUrl = Url::to(['/inventory/store/list-in-warehouse']);
$OrderRequestInWarehouseUrl = Url::to(['/inventory/warehouse/list-order-request']);
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
            { name: "เบิก", data: [50, 45, 60, 70, 50, 45, 60, 70,30,40,23,19] },
            { name: "จ่าย", data: [-21, -54, -45, -35, -21, -54, -45, -35,-87,-40,-23,-34] },
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
              "ม.ค.",
              "ก.พ.",
              "มี.ค.",
              "เม.ย.",
              "พ.ย.",
              "มิ.ย.",
              "ก.ค.",
              "ส.ค.",
              "ก.ย.",
              "ต.ค.",
              "พ.ย.",
              "ธ.ค.",
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
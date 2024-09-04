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
                    <h4 class="card-title">100 เรื่อง</h4>
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
<div class="card">
  <div class="card-body">
<h6><i class="bi bi-ui-checks"></i> วัสดุในคลัง <span class="badge rounded-pill text-bg-primary">1 </span> รายการ</h6>
<div id="showStoreInWarehouse"></div>
  </div>
</div>

    </div>
    
    <!-- end col-6 -->
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <h6>วัสดุรอรับเข้า</h6>
            <div class="card-body">
                <?php
          $warehouse = Yii::$app->session->get('warehouse');
          $models = Stock::find()
          ->select(['p.id','stock.asset_item', 'sum(stock.qty) as sum_qty'])
          ->join('INNER JOIN', 'categorise p', 'p.id = stock.asset_item')
          ->where(['stock.to_warehouse_id' => $warehouse['warehouse_id']])
          ->groupBy('p.id')
          ->all();
          
          ?>
                <div class="table-responsive">
                    <table class="table table-primary">
                        <thead>
                            <tr>
                                <th scope="col">รายการ</th>
                                <th scope="col">คงเหลือ</th>
                                <th scope="col">ดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($models as $model):?>
                            <tr class="">
                                <td scope="row"><?=$model->getProductItem()->Avatar()?></td>
                                <td><?=$model['sum_qty']?></td>
                                <td>
                                    <?=Html::a('<i class="bi bi-clock-history"></i>',['/inventory/stock-movement/product','id' => $model->id])?>
                                </td>
                            </tr>
                            <?php endforeach;?>
                        </tbody>
                    </table>
                </div>



            </div>
            <!-- End Card body -->
        </div>
        <!-- End Card -->


        <?= $this->render(
          'list_request',
          ['model' => $model]
        ) ?>
    </div>
</div>

<?php
use yii\web\View;
// $showReceivePendingOrderUrl = Url::to(['/inventory/receive/list-pending-order']);
// $listOrderRequestUrl = Url::to(['/inventory/stock/list-order-request']);

$StoreInWarehouseUrl = Url::to(['/inventory/store/list-in-warehouse']);
$js = <<< JS
  // getPendingOrder()
  // getlistOrderRequest()


  getStoreInWarehouse()
  async function getStoreInWarehouse(){
    await $.ajax({
      type: "get",
      url: "$StoreInWarehouseUrl",
      dataType: "json",
      success: function (res) {
        $('#showStoreInWarehouse').html(res.content)
      }
    });
  }


  // //รายการวัสดุรอรับเข้าคลัง
  // async function getPendingOrder(){
  //   await $.ajax({
  //     type: "get",
  //     url: "$showReceivePendingOrderUrl",
  //     dataType: "json",
  //     success: function (res) {
  //       $('#showReceivePendingOrder').html(res.content)
  //     }
  //   });
  // }

  // // รายการขอเบิกวัสดุ
  // async function getlistOrderRequest(){
  //   await $.ajax({
  //     type: "get",
  //     url: "$listOrderRequestUrl",
  //     dataType: "json",
  //     success: function (res) {
  //       $('#showlistOrderRequest').html(res.content)
  //     }
  //   });
  // }
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
            height: 350,
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
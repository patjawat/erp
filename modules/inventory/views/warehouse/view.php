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
        <a href="<?=Url::to(['/inventory/stock/in-stock'])?>">
            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2 id="totalStock">0</h2>
                </div>
                <div class="card-footer border-0">จำนวนวัสดุในสต๊อค</div>
            </div>
        </a>
    </div>
    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <h2 id="showTotalPrice">0</h2>
            </div>
            <div class="card-footer border-0">รวมมูลค่ารับเข้า</div>
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
                <!-- <div id="inventoryCharts"></div> -->
                <div id="showChart">

                    <div class="placeholder-glow">
                        <div class="d-flex justufy-content-row gap-5">
                            <?php for ($x = 1; $x <= 12; $x++):?>
                            <div class="d-flex align-items-end gap-2">
                                <span class="placeholder" style="width:10px;height:200px"></span>
                                <span class="placeholder" style="width:10px;height:100px"></span>
                            </div>
                            <?php endfor?>

                        </div>
                    </div>

                </div>
            </div>
        </div>




        

        

    </div>
    <div class="col-6">
        


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
                                    <?php for ($x = 1; $x <= 2; $x++):?>
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
                                    <?php endfor;?>
                                </tbody>
                            </table>
                        </div>
                        <a class="btn btn-sm btn-light rounded-pill placeholder"
                            href="/inventory/warehouse/list-order-request">แสดงท้ังหมด</a>
                    </div>
                </div>
            </div>
        </div>

        

        


    </div>

</div>

<!-- end col-6 -->
</div>

<div class="row">
    <div class="col-12">



    </div>

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
  getChart();

  // รายการขอเบิก
  async function getStoreInWarehouse(){
    await $.ajax({
      type: "get",
      url: "$OrderRequestInWarehouseUrl",
      dataType: "json",
      success: function (res) {
        $('#showOrderRequestInWarehouse').html(res.content)
        $('#totalStock').html(res.totalstock)
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
 

 
  JS;
$this->registerJS($js, View::POS_END);
?>
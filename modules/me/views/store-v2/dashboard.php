<?php

/**
 * @var yii\web\View $this
 */

use yii\web\View;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\widgets\Pjax;
use app\models\Categorise;
use app\modules\inventory\models\Stock;

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse->warehouse_name;
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
Dashboard
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>

<?php  Pjax::begin(['id' => 'inventory-container']); ?>
<div class="row">
    <div class="col-3">
        <a href="<?=Url::to(['/inventory/stock/in-stock'])?>">
            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2><?php  echo number_format($searchModel->LastTotalStock(),2); ?> </h2>
                </div>
                <div class="card-footer border-0">ยอดยกมา</div>
            </div>
        </a>
    </div>

    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
            <?php if($warehouse['warehouse_type'] == 'MAIN'):?>
            <h2><?php echo number_format($searchModel->ReceiveMainSummary(),2); ?></h2>
            <?php endif?>

            <?php if($warehouse['warehouse_type'] == 'SUB'):?>
                <h2><?php echo number_format($searchModel->ReceiveSubSummary(),2); ?></h2>
            <?php endif?>

            </div>
            <div class="card-footer border-0">มูลค่ารับเข้า</div>
        </div>
    </div>

    <div class="col-3">
            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2><?=number_format($searchModel->OutSummary(),2)?></h2>

                </div>
                <div class="card-footer border-0">มูลค่าใช้ไป</div>
            </div>
    </div>
    <div class="col-3">

            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2> <?php 
                    // echo number_format(($searchModel->LastTotalStock()+$searchModel->ReceiveSubSummary()) - $searchModel->OutSummary(),2)
                    echo number_format($searchModel->SumSubStock(),2);
                     ?></h2>
                </div>
                <div class="card-footer border-0">มูลค่าคงเหลือ</div>
            </div>

    </div>
</div>



        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6 class="card-title">ปริมาณเบิก/จ่าย</h6>
                    <div class="mb-3">
                    <?php echo $this->render('_search_year', ['model' => $searchModel]); ?></div>
                </div>
                <?= $this->render('view_chart',['model' => $searchModel])?>
            </div>
        </div>

<?php

$OrderRequestInWarehouseUrl = Url::to(['/inventory/order-request']);
$js = <<< JS
  getStoreInWarehouse()
  // รายการขอเบิก
  async function getStoreInWarehouse(){
    await $.ajax({
      type: "get",
      url: "$OrderRequestInWarehouseUrl",
      dataType: "json",
      success: function (res) {
        $('#showOrderRequestInWarehouse').html(res.content)
        // $('#totalStock').html(res.totalstock)
        // $('#OrderConfirm').html(res.confirm)
        // $('#showTotalOrder').html(res.totalOrder)
      }
    });
  }
 
  JS;
$this->registerJS($js, View::POS_END);
?>

<?php Pjax::end(); ?>
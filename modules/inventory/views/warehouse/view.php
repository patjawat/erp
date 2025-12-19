<?php

/**
 * @var yii\web\View $this
 */

use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\components\AppHelper;
use app\modules\inventory\models\StockEvent;

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];

$this->params['breadcrumbs'][] = ['label' => 'ระบบคลัง', 'url' => ['/inventory']];
?>

<?php $this->beginBlock('page-title'); ?>
<div class="d-flex align-items-center gap-2 mb-2  text-primary-gradient">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="7" height="9" x="3" y="3" rx="1"></rect>
            <rect width="7" height="5" x="14" y="3" rx="1"></rect>
            <rect width="7" height="9" x="14" y="12" rx="1"></rect>
            <rect width="7" height="5" x="3" y="16" rx="1"></rect>
        </svg>
        ภาพรวม<?=$this->title?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<?php echo $this->render('@app/modules/inventory/menu',['active' => 'dashboard']) ?>
<?php $this->endBlock(); ?>


<?php

 $range = AppHelper::BudgetYearRange($searchModel->thai_year);
        $dateStart =  $range['start']; // 2025-10-01
        $dateEnd =  $range['end'];   // 2026-09-30


        $params = [
            ':date_start' => $dateStart,
            ':date_end' => $dateEnd,
        ];
        $conditions = [
            "e.name = 'order'",
            "i.asset_item IS NOT NULL",
        ];
    $warehouseId = $warehouse->id;
    if (!empty($warehouseId)) {
        $conditions[] = "e.warehouse_id = :warehouse_id";
        $params[':warehouse_id'] = $warehouseId;
    }

        // ----- Auto GROUP / ORDER -----
        $groupBy = '';
        list($sql, $params) = StockEvent::buildStockOrderSql(
            $conditions,
            $params,
            $groupBy ?? null,
            $orderBy ?? null
        );

        $querys = Yii::$app->db->createCommand($sql, $params)->queryOne();

?>



<?php  Pjax::begin(['id' => 'inventory-container']); ?>
<div class="row">
    <div class="col-3">
        <a href="<?=Url::to(['/inventory/stock/in-stock'])?>">
            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2><?php  echo number_format($querys['begin_price'] ?? 0,2); ?> </h2>
                </div>
                <div class="card-footer border-0">ยอดยกมา</div>
            </div>
        </a>
    </div>

    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <h2><?php echo number_format($querys['price_in'] ?? 0,2); ?></h2>
            <?php if($warehouse['warehouse_type'] == 'MAIN'):?>
            <?php endif?>

            <?php if($warehouse['warehouse_type'] == 'SUB'):?>
            <?php endif?>

            </div>
            <div class="card-footer border-0">มูลค่ารับเข้า</div>
        </div>
    </div>

    <div class="col-3">
            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2><?=number_format($querys['price_out'] ?? 0,2)?></h2>
                </div>
                <div class="card-footer border-0">มูลค่าใช้ไป</div>
            </div>
    </div>
    <div class="col-3">

            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2> <?php echo number_format($querys['end_price'] ?? 0,2);?></h2>
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

<?php if($warehouse['warehouse_type'] == 'MAIN'):?>
<?php echo $this->render('_order_request',[  'searchModel' => $searchModel,'dataProvider' => $dataProvider,])?>
<?php // $this->render('_order_withdraw',[  'searchModel' => $searchModel,'dataProvider' => $dataProvider,])?>
<?php endif?>

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
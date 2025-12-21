<?php

/**
 * @var yii\web\View $this
 */

use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Pjax;

$warehouse = Yii::$app->session->get('sub-warehouse');
$this->title = 'คลัง'.$warehouse->warehouse_name.'/Dashboard';

$this->params['breadcrumbs'][] = ['label' => 'คลังหน่วยงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Dashboard'
?>
<?php $this->beginBlock('page-title'); ?>
<div class="d-flex flex-column align-items-center align-items-lg-start gap-2 mb-2 text-primary-gradient text-center text-lg-start">
    <h4 class="fw-medium text-body d-flex align-items-center gap-2 mb-0">
      <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-clipboard-check-icon lucide-clipboard-check"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/></svg>
      <?= $this->title ?>
    </h4>
</div>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
<div class="d-flex gap-2">
    <?php echo $this->render('@app/modules/me/stock_menu',['active' => 'store']) ?>
    <?= $this->render('@app/components/ui/btnReturn')?>
</div>
<?php $this->endBlock(); ?>



<?php  Pjax::begin(['id' => 'inventory-container']); ?>
<div class="row">
    <div class="col-3">
        <a href="<?=Url::to(['/inventory/stock/in-stock'])?>">
            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2><?php  // echo number_format($searchModel->LastTotalStock(),2); ?> </h2>
                </div>
                <div class="card-footer border-0">ยอดยกมา</div>
            </div>
        </a>
    </div>

    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
            <?php if($warehouse['warehouse_type'] == 'MAIN'):?>
            <h2><?php // echo number_format($searchModel->ReceiveMainSummary(),2); ?></h2>
            <?php endif?>

            <?php if($warehouse['warehouse_type'] == 'SUB'):?>
                <h2><?php // echo number_format($searchModel->ReceiveSubSummary(),2); ?></h2>
            <?php endif?>

            </div>
            <div class="card-footer border-0">มูลค่ารับเข้า</div>
        </div>
    </div>

    <div class="col-3">
            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2><?php //number_format($searchModel->OutSummary(),2)?></h2>

                </div>
                <div class="card-footer border-0">มูลค่าใช้ไป</div>
            </div>
    </div>
    <div class="col-3">

            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2> <?php 
                    // echo number_format(($searchModel->LastTotalStock()+$searchModel->ReceiveSubSummary()) - $searchModel->OutSummary(),2)
                    //echo number_format($searchModel->SumSubStock(),2);
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
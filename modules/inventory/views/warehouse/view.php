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
use yii\widgets\Pjax;

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $model->warehouse_name .' | มูลค่าคลัง '.$model->SumPice().' บาท';
?>


<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>

<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php  Pjax::begin(['id' => 'inventory-container']); ?>
<div class="row">
    <!-- <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <h2><?=$model->SumPice()?></h2>
            </div>
            <div class="card-footer border-0">รวมมูลค่าคลัง</div>
        </div>
    </div> -->
    <div class="col-6">
        <a href="<?=Url::to(['/inventory/stock/in-stock'])?>">
            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <!-- <h2 id="totalStock">0</h2> -->
                     
                    <h2><?=$model->SumPice()?></h2>
                </div>
                <div class="card-footer border-0">สต๊อกวัสดุ</div>
            </div>
        </a>
    </div>

    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
            <h2><?=$model->SumPiceIn()?></h2>
            </div>
            <div class="card-footer border-0">มูลค่ารับเข้า</div>
        </div>
    </div>

    <div class="col-3">
        <a href="<?=Url::to(['/inventory/stock-out'])?>">

            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                <h2><?=$model->SumPiceOut()?></h2>
                </div>
                <div class="card-footer border-0">มูลค่าจ่ายวัสดุ</div>
            </div>
        </a>

    </div>
</div>


<div class="row">
    <div class="col-12">
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
                <?=$this->render('view_chart',['model' => $model])?>
            </div>
        </div>
      


        <?php if($warehouse['warehouse_type'] == 'MAIN'):?>
        <div id="showOrderRequestInWarehouse" style="min-height: 463px;">
            
        </div>

        <?php endif?>
    </div>

</div>



<?php

use yii\web\View;
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
  
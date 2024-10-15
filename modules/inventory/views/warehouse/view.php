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

<?php Pjax::begin(['id' => 'inventory-container']); ?>
<div class="row">
    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <h2><?=$model->SumPice()?></h2>
                <!-- <h2 id="showTotalPrice">0</h2> -->
            </div>
            <div class="card-footer border-0">รวมมูลค่าคลัง</div>
        </div>

    </div>
    <div class="col-3">
        <a href="<?=Url::to(['/inventory/stock/in-stock'])?>">
            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2 id="totalStock">0</h2>
                </div>
                <div class="card-footer border-0">วัสดุในสต๊อค</div>
            </div>
        </a>
    </div>

    <div class="col-3">
        <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
            <div class="card-body">
                <h2 id="OrderConfirm">0</h2>
            </div>
            <div class="card-footer border-0">มูลค่ารับเข้า</div>
        </div>
    </div>

    <div class="col-3">
        <a href="<?=Url::to(['/inventory/stock-out'])?>">

            <div class="card border border-primary border-4 border-top-0 border-end-0 border-start-0">
                <div class="card-body">
                    <h2 id="showTotalOrder">0</h2>
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

        <?php endif?>
    </div>

</div>

<?php Pjax::end(); ?>
<?= $warehouse['warehouse_type'] == 'SUB' ? $this->render('list_store',[
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider
                    ]) : null?>
    <?php

use yii\web\View;

$StoreInWarehouseUrl = Url::to(['/inventory/stock/warehouse']);
$OrderRequestInWarehouseUrl = Url::to(['/inventory/warehouse/list-order-request']);
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
        $('#totalStock').html(res.totalstock)
        $('#OrderConfirm').html(res.confirm)
        $('#showTotalOrder').html(res.totalOrder)
      }
    });
  }




    $("body").on("click", ".add-cart", function (e) {
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (res) {
            if(res.status == 'error'){
                    Swal.fire({
                    icon: "warning",
                    title: "เกินจำนวน",
                    showConfirmButton: false,
                    timer: 1500,
                });
                }
                // success()
                // $.pjax.reload({ container:'#inventory-container', history:false,replace: false,timeout: false});
        }
    });
});


JS;
$this->registerJS($js, View::POS_END);
?>

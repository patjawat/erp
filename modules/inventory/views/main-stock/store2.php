<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('../default/menu'); ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'inventory-container']); ?>
<?php

$cart = Yii::$app->cartMain;
$products = $cart->getItems();

?>

<style>
.card:hover {
    /* transition: 0.3s; */
    /* border-color: var(--bs-primary); */
    /* border: 2px solid var(--bs-primary) !important; */
    /* box-shadow: 0px 0px 2px 2px rgb(13, 110, 253); */
}
</style>
<div class="card">
    <div class="card-body d-flex justify-content-between align-items-center">
        <h6><i class="bi bi-ui-checks"></i> จำนวนวัสดุในคลัง <span
                class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount()); ?>
            </span> รายการ</h6>

        <?php echo $this->render('_search', ['model' => $searchModel]); ?>
        <div>
            <?php echo Html::a('<button type="button" class="btn btn-primary rounded-pill">
                    <i class="fa-solid fa-cart-plus"></i> เลือกเบิก <span class="badge text-bg-danger" id="totalCount">'.$cart->getCount().'</span> รายการ
                    </button>', ['/inventory/main-stock/show-cart'], ['class' => 'brn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-xl']]); ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-8">
        <!-- <div class="overflow-scroll">...</div> -->
        <div class="d-flex flex-wrap overflow-scroll" style="height:500px">
            <?php foreach ($dataProvider->getModels() as $model): ?>
            <div class="p-2 col-3">
                <div class="card position-relative rounded">
                    <p class="position-absolute top-0 end-0 p-2">
                        <i class="fa-solid fa-circle-info fs-4"></i>
                        <?php // echo $model->warehouse->warehouse_name; ?>
                    </p>
                    <?php echo Html::img($model->product->ShowImg(), ['class' => 'card-top']); ?>
                    <div class="card-body w-100">
                        <div class="d-flex justify-content-start align-items-center">
                            <?php if($model->SumQty() >= 1):?>
                            <span class="badge text-bg-primary  mt--45"><?php echo $model->SumQty(); ?>
                                <?php echo $model->product->unit_name; ?></span>
                            <!-- <span class="btn btn-sm btn-primary fs-13 mt--45 rounded-pill"><?php echo $model->SumQty(); ?> <?php echo $model->product->unit_name; ?> </span> -->
                            <?php else:?>
                            <span class="btn btn-sm btn-secondary fs-13 mt--45 rounded-pill"> หมด</span>
                            <?php endif;?>
                            <?php
                        // try {
                        //     // echo Html::a('<i class="fa-solid fa-cart-plus"></i> '.$model->getLotQty()['lot_number'].' <span class="badge text-bg-danger">'.$model->getLotQty()['qty'].'</span> เลือก', ['/inventory/main-stock/add-to-cart', 'id' => $model->getLotQty()['id']], ['class' => 'add-cart btn btn-sm btn-primary rounded-pill mt--45 zoom-in']);
                        // } catch (Throwable $th) {
                        //     // throw $th;
                        // }
        ?>
                        </div>
                        <p class="text-truncate mb-0"><?php echo $model->product->title; ?></p>

                        <div class="d-flex justify-content-between">
                            <span class="fw-semibold text-danger"> <i
                                    class="fa-solid fa-dollar-sign"></i><?php echo number_format($model->unit_price,2); ?></code>


                                <?php
                        try {
                            echo Html::a('<i class="fa-solid fa-cart-plus"></i> '.$model->getLotQty()['lot_number'].' <span class="badge text-bg-danger">'.$model->getLotQty()['qty'].'</span> เลือก', ['/inventory/main-stock/add-to-cart', 'id' => $model->getLotQty()['id']], ['class' => 'add-cart btn btn-sm btn-primary rounded-pill mt--45 zoom-in']);
                        } catch (Throwable $th) {
                            // throw $th;
                        }
        ?>
                                <!-- <span class="badge rounded-pill badge-soft-primary text-primary fs-13"> <?php echo $model->warehouse->warehouse_name; ?> </span> -->
                        </div>
                    </div>
                </div>

            </div>
            <?php endforeach?>
        </div>

        <div class="d-flex justify-content-center mt-3">
            <div class="text-muted">
                <?php echo LinkPager::widget([
            'pagination' => $dataProvider->pagination,
            'firstPageLabel' => 'หน้าแรก',
            'lastPageLabel' => 'หน้าสุดท้าย',
            'options' => [
                'listOptions' => 'pagination pagination-sm',
                'class' => 'pagination-sm',
            ],
        ]); ?>
            </div>
        </div>

    </div>
    <div class="col-4">
        <div class="card" style="height:500px;">
            <div class="card-body p-0 pt-4">
                <h5 class="text-center"><i class="fa-solid fa-basket-shopping"></i> ตะกร้าสินค้า</h5>
                <div class="table-responsive">
                    <table class="table table-primary">
                        <thead>
                            <tr>
                                <th scope="col">รายการ</th>
                                <th class="text-center">จำนวน</th>
                                <th scope="col" class="text-end">ราคา</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sumQty = 0; $getQuantity=0; foreach($products as $item):?>
                            <?php
                              $sumQty += (float)$item->SumQty();
                              $getQuantity += (float)$item->getQuantity();
                            ?>
                            <tr class="">
                                <td class="d-inline-block text-truncate" style="max-width:226px;">
                                    <?php
                                        try {
                                            echo $item->product->Avatar();
                                        } catch (Throwable $th) {
                                        }
                            ?>
                                </td>
                                <td class="text-center"><?=$item->getQuantity()?></td>
                                <td class="text-end"><span
                                        class="fw-semibold"></i><?=number_format($item->unit_price,2)?></span></td>
                            </tr>
                            <?php endforeach;?>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

    </div>
</div>





<?php Pjax::end(); ?>
<!-- End CardBody -->

<?php
$js = <<< JS


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
            }else{
                $('#totalCount').text(res.totalCount)
            }
                // success()
                // $.pjax.reload({ container:'#inventory-container', history:false,replace: false,timeout: false});
        }
    });
});



$("body").on("keypress", ".update-qty", function (e) {
    var keycode = e.keyCode ? e.keyCode : e.which;
    if (keycode == 13) {
        let qty = $(this).val()
        let id = $(this).attr('id')
        console.log(qty);
        
        $.ajax({
            type: "get",
            url: "/inventory/main-stock/update-cart",
            data: {
                'id':id,
                'quantity':qty 
            },
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
                ViewMainCar();
                $.pjax.reload({ container:'#inventory-container', history:false,replace: false,timeout: false});
            }
        });
    }
});


        $("body").on("click", ".update-cart", function (e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            data: {},
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
                ViewMainCar();
                $.pjax.reload({ container:'#inventory-container', history:false,replace: false,timeout: false});
            }
        });
    });

    $("body").on("click", ".delete-item-cart", function (e) {
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (response) {
            ViewMainCar();
            success()
            $.pjax.reload({ container:'#inventory-container', history:false,replace: false,timeout: false});
        }
    });
});

$("body").on("click", ".checkout", async function (e) {
    e.preventDefault();

  await Swal.fire({
    title: "ยืนยัน?",
    text: "บันทึกรายการนี้!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#3085d6",
    cancelButtonColor: "#d33",
    confirmButtonText: "ใช่, ยืนยัน!",
    cancelButtonText: "ยกเลิก",
  }).then(async (result) => {
    if (result.value == true) {
      await $.ajax({
        type: "post",
        url: $(this).attr('href'),
        dataType: "json",
        success: async function (response) {
            console.log(response);
            
          if (response.status == "success") {
            // await  $.pjax.reload({container:response.container, history:false,url:response.url});
            success("บันสำเร็จ!.");
          }
        },
      });
    }
  });

  });


JS;
$this->registerJS($js, View::POS_END);

?>
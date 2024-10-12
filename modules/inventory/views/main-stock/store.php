<?php

use yii\bootstrap5\LinkPager;
use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use yii\widgets\Pjax;

?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php Pjax::begin(['id' => 'inventory-container']); ?>
<?php
$cart = \Yii::$app->cartMain;
$products = $cart->getItems();

$warehouseSelect = Yii::$app->session->get('selectMainWarehouse');
?>

<div class="card">
<div class="card-body d-flex justify-content-between align-items-center">
        <h6><i class="bi bi-ui-checks"></i> จำนวนวัสดุในคลัง <span
                class="badge rounded-pill text-bg-primary"><?=number_format($dataProvider->getTotalCount())?> </span> รายการ</h6>

            <?=$this->render('_search', ['model' => $searchModel])?>
            <div>
                <?=Html::a('<button type="button" class="btn btn-primary rounded-pill">
                    <i class="fa-solid fa-cart-plus"></i> เลือกเบิก <span class="badge text-bg-danger">'.$cart->getCount().'</span> รายการ
                    </button>',['/inventory/main-stock/show-cart'],['class' => 'brn btn-primary rounded-pill shadow open-modal','data' => ['size' => 'modal-lg']])?>
                    </div>
    </div>
</div>

<div class="d-flex flex-wrap">
    <?php foreach($dataProvider->getModels() as $model):?>
    <div class="p-2 col-2">
        <div class="card">
            <?=Html::img($model->product->ShowImg(),['class' => 'card-top'])?>
            <div class="card-body w-100">
                <div class="text-end">
                <?php if($model->qty > 0):?>
                    <?=Html::a('<i class="fa-solid fa-cart-plus"></i> เลือก',['/inventory/main-stock/add-to-cart','id' => $model->id],['class' => 'add-cart btn btn-sm btn-warning rounded-pill mt--45 zoom-in'])?>
                    <!-- <?=Html::a('<i class="fa-solid fa-cart-plus"></i> เลือก',['/'],['class' => 'btn btn-sm btn-warning rounded-pill mt--45 zoom-in'])?> -->
                <?php else:?>
                    <button type="button" class="btn btn-sm btn-secondary shadow rounded-pill mt--45"><i class="fa-solid fa-triangle-exclamation text-danger"></i> หมด</button>
                    <?php endif;?>
                </div>
                <p class="text-truncate"><?=$model->product->title?></p>
                <div class="d-flex justify-content-between">
                    <div class="text-danger">
                       <span class="fw-semibold">
                           <?=$model->qty?> 
                       </span> 
                        <?=$model->product->unit_name?>
                    </div>
                    <!-- <span class="badge rounded-pill badge-soft-primary text-primary fs-13"> <?=$model->warehouse->warehouse_name?> </span> -->
                    <p class="badge rounded-pill text-bg-primary"><?=$model->warehouse->warehouse_name?></p>
                </div>
            </div>
        </div>

    </div>
    <?php endforeach?>
</div>

<div class="d-flex justify-content-center">
    <div class="text-muted">
        <?= LinkPager::widget([
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

<?php  Pjax::end(); ?>
<!-- End CardBody -->

<?php
$showCartUrl = Url::to(['/inventory/stock-out/show-cart']);
$deleteItemUrl = Url::to(['/inventory/store/delete']);
$updateItemUrl = Url::to(['/inventory/store/update']);
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
                }
                // success()
                $.pjax.reload({ container:'#inventory-container', history:false,replace: false,timeout: false});
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
            url: "/inventory/store/update-qty",
            data: {
                'id':id,
                'qty':qty 
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



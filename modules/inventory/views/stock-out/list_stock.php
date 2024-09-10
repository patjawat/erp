<?php

use app\modules\inventory\models\Store;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\widgets\Pjax;
/** @var yii\web\View $this */
/** @var app\modules\inventory\models\StoreSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$warehouse = Yii::$app->session->get('warehouse');
$this->title = $warehouse['warehouse_name'];
$this->params['breadcrumbs'][] = $this->title;

?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<?php yii\widgets\Pjax::begin(['id' => 'inventory']); ?>
<?php
// $selectWarehouse = Yii::$app->session->get('select-warehouse');
$cart = \Yii::$app->cart;
$products = $cart->getItems();
// echo "<pre>";
// print_r($cart);
// echo "</pre>";
?>

<div class="row">
<div class="col-8">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h6><i class="bi bi-ui-checks"></i> จำนวนวัสดุในคลัง <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
                <?=$this->render('_search', ['model' => $searchModel])?>
            </div>
            <div class="table-responsive">
                <table class="table table-primary">
                    <thead>
                        <tr>
                            <th scope="col">ชื่อรายการ</th>
                            <th class="text-center">ล๊อตปัจุบัน</th>
                            <th class="text-center">คงเหลือ</th>
                            <th class="text-center" style="width:90px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataProvider->getModels() as $item):?>
                        <tr class="">
                            <td scope="row"><?=$item->product->Avatar()?>
                        </td>
                            <td class="text-center"><?=$item->qty?></td>
                            <td class="text-center"><?=$item->total?></td>
                            <td class="text-center">
                                <?=Html::a('<i class="fa-solid fa-cart-plus"></i> เพิ่ม',['/inventory/stock-out/add-to-cart','id' => $item->id],['class' => 'add-cart btn btn-sm btn-primary shadow rounded-pill'])?>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            </div>

        </div>
        
    </div>
  

        
</div>
<div class="col-4">
<div id="showCart"></div>
</div>
</div>




<?php
$storeProductUrl = Url::to(['/inventory/store/product']);
$showCartUrl = Url::to(['/inventory/stock-out/show-cart']);
$deleteItemUrl = Url::to(['/inventory/store/delete']);
$updateItemUrl = Url::to(['/inventory/store/update']);
$js = <<< JS


getViewCar()

async function getViewCar()
    {
    await $.ajax({
        type: "get",
        url: "$showCartUrl",
        dataType: "json",
        success: function (res) {
            $('#showCart').html(res.content)
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
                getViewCar()
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
            url: "/inventory/stock-out/update-qty",
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
                getViewCar()
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
                getViewCar()
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
            getViewCar()
            // $.pjax.reload({container:response.container, history:false});
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


<?php yii\widgets\Pjax::end(); ?>


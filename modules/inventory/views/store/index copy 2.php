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
// print_r($selectWarehouse);
// echo "</pre>";
?>
 <?=$this->render('_search', ['model' => $searchModel])?>
<div class="row gx-4 gx-lg-5 row-cols-3 row-cols-md-3 row-cols-xl-5 justify-content-center">
<?php foreach($dataProvider->getModels() as $item):?>
                    <div class="col mb-5">
                        <div class="card h-100">
                            <!-- Sale badge-->
                            <div class="badge bg-dark text-white position-absolute" style="top: 0.5rem; right: 0.5rem">Sale</div>
                            <!-- Product image-->
                            <img class="card-img-top" src="https://dummyimage.com/450x300/dee2e6/6c757d.jpg" alt="...">

                            <!-- Product details-->
                            <div class="card-body pt-4">
                                <div class="text-center">
                                    <!-- Product name-->
                                    <h6 class="fw-light"><?=$item->product->title?></h6>
                                    <!-- Product price-->
                                    <?=$item->lot_number?>
                                </div>
                            </div>
                            <!-- Product actions-->
                            <div class="card-footer p-4 pt-0 border-top-0 bg-transparent">
                                <div class="text-center"><a class="btn btn-outline-dark mt-auto" href="#">Add to cart</a></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach;?>
                    
                </div>



<div class="row">
<div class="col-8">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">
                <h6><i class="bi bi-ui-checks"></i> จำนวนวัสดุในคลัง <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
                <?=$this->render('_search', ['model' => $searchModel])?>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th scope="col">ชื่อรายการ</th>
                            <th class="text-start">ล๊อตปัจุบัน</th>
                            <th class="text-center">คงเหลือ</th>
                            <th class="text-center" style="width:90px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataProvider->getModels() as $item):?>
                        <tr class="">
                            <td scope="row"><?=$item->product->Avatar()?>
                        </td>
                            <td class="text-start"> <?=$item->lot_number?></td>
                            <td class="text-center"><?=$item->qty?></td>
                            <!-- <td class="text-center"><?=$item->total?></td> -->
                            <td class="text-center">
                                <?=Html::a('<i class="fa-solid fa-cart-plus"></i> เพิ่ม',['/inventory/store/add-to-cart','id' => $item->id],['class' => 'add-cart btn btn-sm btn-primary shadow rounded-pill'])?>
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
    <div id="viewCartShow"></div>
<?php // $this->render('view_cart')?>
</div>
</div>

<?php yii\widgets\Pjax::end(); ?>


<?php
$storeProductUrl = Url::to(['/inventory/store/product']);
$viewCartUrl = Url::to(['/inventory/store/view-cart']);
$deleteItemUrl = Url::to(['/inventory/store/delete']);
$updateItemUrl = Url::to(['/inventory/store/update']);
$js = <<< JS


getViewCar()
async function getViewCar()
    {
    await $.ajax({
        type: "get",
        url: "$viewCartUrl",
        dataType: "json",
        success: function (res) {
            $('#viewCartShow').html(res.content)
            if(res.countItem == 0){
                $('#btnSaveCart').prop('disabled', true);
            //     $('#viewCart').hide()
            //     // $('#viewCart').dropdown('toggle');
            //     $('#countItemCart').hide()
            }else{
                $('#viewStoreCart').show()
                $('#countItemCart').html(res.countItem)
            }
            console.log(res.countItem);
        }
    });
    }


    $("body").on("click", ".add-cart", function (e) {
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (response) {
            getViewCar()
            success('เพิ่มลงในตะกร้าแล้ว')
        //   $.pjax.reload({container:response.container, history:false});
        }
    });
});


// $(document).ready(function(){
//   $('#addtocart').on('click',function(){
    
//     var button = $(this);
//     var cart = $('#cart');
//     var cartTotal = cart.attr('data-totalitems');
//     var newCartTotal = parseInt(cartTotal) + 1;
    
//     button.addClass('sendtocart');
//     setTimeout(function(){
//       button.removeClass('sendtocart');
//       cart.addClass('shake').attr('data-totalitems', newCartTotal);
//       setTimeout(function(){
//         cart.removeClass('shake');
//       },500)
//     },1000)
//   })
// })


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


$("body").on("keypress", ".update-qty", function (e) {
    var keycode = e.keyCode ? e.keyCode : e.which;
    if (keycode == 13) {
        let qty = $(this).val()
        let id = $(this).attr('id')
        console.log(qty);
        
        $.ajax({
            type: "get",
            url: "/inventory/store/update-cart",
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
                // success();
                getViewCar()
            }
        });
    }
});

JS;
$this->registerJS($js, View::POS_END);

?>





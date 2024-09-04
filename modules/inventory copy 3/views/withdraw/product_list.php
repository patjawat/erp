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

$this->title = 'Stores';
$this->params['breadcrumbs'][] = $this->title;

?>

<?php
// $selectWarehouse = Yii::$app->session->get('select-warehouse');
$cart = \Yii::$app->cart;
$products = $cart->getItems();
$selectWarehouse = Yii::$app->session->get('warehouse');

?>

<?php Pjax::begin(['id' => 'inventory', 'enablePushState' => true, 'timeout' => 5000]);?>

    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between">

                <h6><i class="bi bi-ui-checks"></i> จำนวน <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?> </span> รายการ</h6>
                <?=$this->render('_search', ['model' => $searchModel])?>
            </div>
            <div class="table-responsive">
                <table class="table table-primary">
                    <thead>
                        <tr>
                            <th scope="col">ชื่อรายการ</th>
                            <th scope="col">คลัง</th>
                            <th scope="col">จำนวนสต๊อก</th>
                            <th class="text-center" style="width:90px">ดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($dataProvider->getModels() as $item):?>
                        <tr class="">
                            <td scope="row"><?php 
                            echo $item->product->Avatar()?></td>
                            <td><?=$item->warehouse_id?></td>
                            <td><?=$item->stock_qty?></td>
                            <td class="text-center">
                                <?=Html::a('<i class="fa-solid fa-cart-plus"></i> เพิ่ม',['/inventory/withdraw/add-item','id' => $item->id],['class' => 'add-cart btn btn-sm btn-primary shadow rounded-pill'])?>
                            </td>
                        </tr>
                        <?php endforeach;?>
                    </tbody>
                </table>
            </div>

        </div>
        
    </div>



<?php
$storeProductUrl = Url::to(['/inventory/store/product']);
$viewCartUrl = Url::to(['/inventory/store/view-cart']);
$deleteItemUrl = Url::to(['/inventory/store/delete']);
$updateItemUrl = Url::to(['/inventory/store/update']);
$js = <<< JS


$(".modal-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
    $(".modal-dialog").addClass("modal-lg"); 
$(document).ready(function(){
  $('#addtocart').on('click',function(){
    
    var button = $(this);
    var cart = $('#cart');
    var cartTotal = cart.attr('data-totalitems');
    var newCartTotal = parseInt(cartTotal) + 1;
    
    button.addClass('sendtocart');
    setTimeout(function(){
      button.removeClass('sendtocart');
      cart.addClass('shake').attr('data-totalitems', newCartTotal);
      setTimeout(function(){
        cart.removeClass('shake');
      },500)
    },1000)
  })
})


        $("body").on("click", ".update-cart", function (e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            data: {},
            dataType: "json",
            success: function (res) {
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
JS;
$this->registerJS($js, View::POS_END);

?>


<?php Pjax::end(); ?>




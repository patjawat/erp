<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;

?>


<style>


</style>

<div class="row">
    <div class="col-12">
        <div id="storeProductShow">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-center">กำลังโหลด...</h6>
                </div>
            </div>
        </div>



        <div id="cart" class="cart" data-totalitems="0">
  <i class="fas fa-shopping-cart"></i>
</div>

    </div>
</div>

<?php
$storeProductUrl = Url::to(['/inventory/store/product']);
$viewCartUrl = Url::to(['/inventory/store/view-cart']);
$deleteItemUrl = Url::to(['/inventory/store/delete']);
$updateItemUrl = Url::to(['/inventory/store/update']);
$js = <<< JS

    getStoreProduct()
    async function getStoreProduct()
    {
    await $.ajax({
        type: "get",
        url: "$storeProductUrl",
        dataType: "json",
        success: function (res) {
            $('#storeProductShow').html(res.content)
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


    //     $("body").on("click", ".update-cart", function (e) {
    //     e.preventDefault();
    //     $.ajax({
    //         type: "get",
    //         url: $(this).attr('href'),
    //         data: {},
    //         dataType: "json",
    //         success: function (res) {
    //             getViewCar()
    //         }
    //     });
        
    // });
    // $("body").on("click", ".delete-item-cart", function (e) {
    // e.preventDefault();
    // $.ajax({
    //     type: "get",
    //     url: $(this).attr('href'),
    //     dataType: "json",
    //     success: function (response) {
    //         getViewCar()
    //         // $.pjax.reload({container:response.container, history:false});
    //     }
    // });
// });
JS;
$this->registerJS($js, View::POS_END);

?>
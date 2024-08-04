
<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\web\View;

$cart = \Yii::$app->cart;
$products = $cart->getItems();


?>

<div class="d-none d-lg-inline-flex ms-2 dropdown">


    <button id="viewCart" 
    data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false" class="btn header-item notify-icon" id="viewCart" <?= ($cart->getCount() == 0) ? 'style="display:none"' : null?>>
    <div data-bs-trigger="hover" data-bs-toggle="popover" data-bs-placement="right"
                data-bs-custom-class="custom-popover" data-bs-title="รายการขอเบิก"
                data-bs-content="รายการที่ขอเบิกวัสดุ จจากคลังหลัก"> 
    <i class="fa-solid fa-cart-shopping"></i>
    </div>
    
        <span class="badge bg-danger badge-pill notify-icon-badge bg-danger rounded-pill text-white" id="countItemCart" ></span>
    </button>
    <span class="dropdown-toggle" data-bs-toggle="dropdown" data-bs-auto-close="false" aria-expanded="false">

    </span>

    <div class="dropdown-menu-lg dropdown-menu-right dropdown-menu" style="width: 600px;">
        <div id="viewCartShow"></div>
        <div class="d-flex justify-content-center">
            <?=Html::a('เบิกวัสดุ',['/inventory/store/form-checkout'],['class' => 'btn btn-primary shadow rounded-pill'])?>

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
    getViewCar()
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

    async function getViewCar()
    {
    await $.ajax({
        type: "get",
        url: "$viewCartUrl",
        dataType: "json",
        success: function (res) {
            $('#viewCartShow').html(res.content)
            if(res.countItem == 0){
                $('#viewCart').hide()
                $('#countItemCart').hide()
            }else{
                $('#viewCart').show()
                $('#countItemCart').show()
                $('#countItemCart').html(res.countItem)
            }
            console.log(res.countItem);
        }
    });
    }


        $("body").on("click", ".update-cart", function (e) {
        e.preventDefault();
        $.ajax({
            type: "get",
            url: $(this).attr('href'),
            data: {},
            dataType: "json",
            success: function (res) {
                // $.pjax.reload({container:'#viewCart', history:false});
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
            // $.pjax.reload({container:'#viewCart', history:false});
        }
    });
});
JS;
$this->registerJS($js, View::POS_END);
?>




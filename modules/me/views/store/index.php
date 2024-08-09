<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\View;
/** @var yii\web\View $this */
$this->title = "เบิกวัสดุ/อุปกรณ์";
?>

<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cart-plus"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

        <div class="card">
            <div class="card-body">
                <?= Html::a('<i class="fa-solid fa-cart-plus"></i> เบิกวัสดุ ', ['/helpdesk/default/repair-select','title' => '<i class="fa-regular fa-circle-check"></i> เลือกประเภทการซ่อม'], ['class' => 'btn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-md']]) ?>
            </div>
        </div>
<div class="row">
<div class="col-8">


<div class="card">
    <div class="card-body">
        <h6><i class="bi bi-ui-checks"></i> ทะเบียนเบิกวัสดุ 0 รายการ</h6>
        <div class="table-responsive">


</div>
</div>
</div>

</div>
<div class="col-4">
<div id="storeMeProductShow"></div>
</div>
</div>


<?php
$storeMeProductUrl = Url::to(['/me/store/product']);
$viewCartUrl = Url::to(['/me/store/view-cart']);
$deleteItemUrl = Url::to(['/me/store/delete']);
$updateItemUrl = Url::to(['/me/store/update']);
$js = <<< JS

     getStoreMeProduct()
    async function getStoreMeProduct()
    {
    await $.ajax({
        type: "get",
        url: "$storeMeProductUrl",
        dataType: "json",
        success: function (res) {
            $('#storeMeProductShow').html(res.content)
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


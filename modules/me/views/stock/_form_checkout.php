<?php

use yii\helpers\Html;
use yii\web\View;
use yii\helpers\Url;
use app\models\Categorise;
use app\modules\inventory\models\warehouse;
use kartik\datecontrol\DateControl;
use kartik\select2\Select2;
use kartik\widgets\ActiveForm;
use yii\helpers\ArrayHelper;
$warehouse = Yii::$app->session->get('warehouse');
?>
<?php yii\widgets\Pjax::begin(['id' => 'me','enablePushState' => false,'timeout' => 88888]); ?>
<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <?=$model->getMe('ผู้ขอเบิก')['avatar']?>

            

        </div>
        <div id="viewOrder">
            <h6 class="text-center">กำลังโหลด...</h6>
        </div>
        <?php echo Html::a('บันทึกเบิก',['/inventory/receive/save-to-stock','id' => $model->id],['class' => 'btn btn-sm btn-primary rounded-pill shadow btn-confirm','data' => [
                        'title' => 'ยืนยัน',
                        'text' => 'ยืนยันการเบิก'
                        ]])?>
    </div>
</div>




<?php
$storeProductUrl = Url::to(['/inventory/store/product']);
$getOrderUrl = Url::to(['/inventory/store/view-cart']);
$deleteItemUrl = Url::to(['/inventory/store/delete']);
$updateItemUrl = Url::to(['/inventory/store/update']);
$js = <<< JS

    getStoreProduct()
    getOrder()
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

    async function getOrder()
    {
    await $.ajax({
        type: "get",
        url: "$getOrderUrl",
        dataType: "json",
        success: function (res) {
            $('#viewOrder').html(res.content)
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
        //   $.pjax.reload({container:response.container, history:false});
        }
    });
});


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


\$('.btn-confirm').click(function (e) { 
        e.preventDefault();
        var title = \$(this).data('title');
        var text = \$(this).data('text');
        var imageUrl = \$(this).data('img');
        var warehouse_id = \$(this).data('warehouse_id');
        Swal.fire({
            imageUrl: imageUrl,
            imageWidth: 400,
            imageHeight: 200,
            title: title,
            text: text,
            icon: "info",

            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            cancelButtonText: "<i class='bi bi-x-circle'></i> ยกเลิก",
            confirmButtonText: "<i class='bi bi-check-circle'></i> ยืนยัน"
            }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                \$.ajax({
                    type:"get",
                    url: $(this).attr('href'),
                    // data: {id:warehouse_id},
                    dataType: "json",
                    success: async function (response) {
                       
                    }
                });
            }
            });
        
    });


JS;
$this->registerJS($js, View::POS_END);

?>
<?php yii\widgets\Pjax::end()?>
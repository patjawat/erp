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
<?php yii\widgets\Pjax::begin(['id' => 'me','enablePushState' => false,'timeout' => 88888]); ?>

<div class="row">
    <div class="col-8">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <i class="bi bi-ui-checks"></i> ทะเบียนวัสดุ <span class="badge rounded-pill text-bg-primary"><?=$dataProvider->getTotalCount()?></span> รายการ
                    </div>
                    <div>
                <?=$this->render('_search', ['model' => $searchModel])?>

            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th>ชื่อรายการ</th>
                        <th style="width:350px">คงเหลือ</th>
                        <!-- <th style="width:100px">MaxStock</th>
                        <th style="width:100px">MinStock</th> -->
                        <th class="text-center" style="width:120px">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($dataProvider->getModels() as $item):?>
                    <tr class="">
                        <td>
                            <?=$item->product->Avatar()?>
                            
                        </td>
                    <td>
                    <div class="d-flex justify-content-between">
                                <span class="text-muted mb-0 fs-13">
                                    คงเหลือ <span class="text-primary">10</span>
                                </span>
                                <span class="text-muted mb-0 fs-13">80%</span>
                            </div>

                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-" role="progressbar" aria-label="Progress"
                                    aria-valuenow="83" aria-valuemin="0" aria-valuemax="100" style="width: 83%;">
                                </div>
                            </div>
                    </td>
                        <td class="text-center"><?=Html::a('<i class="fa-solid fa-cart-plus"></i> เพิ่ม',['/me/store/add-to-cart','id' => $item->asset_item],['class' => 'add-cart btn btn-sm btn-primary shadow rounded-pill'])?></td>
                        <?php endforeach;?>
                </tbody>
            </table>
        </div>
    </div>
</div>
    </div>
    <div class="col-4">
        <div id="viewCartMe"></div>
        

    
</div>
</div>


<?php
$warehouse = Yii::$app->session->get('warehouse');
print_r($warehouse);
?>
<?php

$viewCartMeUrl = Url::to(['/me/store/view-cart']);
$storeProductUrl = Url::to(['/me/store/product']);
// $getOrderUrl = Url::to(['/me/store/view-cart']);
$deleteItemUrl = Url::to(['/me/store/delete']);
$updateItemUrl = Url::to(['/me/store/update']);
$js = <<< JS

getViewCartMe()
// getOrder()
async function getViewCartMe()
    {
    await $.ajax({
        type: "get",
        url: "$viewCartMeUrl",
        dataType: "json",
        success: function (res) {
            $('#viewCartMe').html(res.content)
            if(res.countItem == 0){
                $('#viewCart').hide()
                // $('#viewCart').dropdown('toggle');
                $('#countItemCart2').hide()
            }else{
                $('#countItemCart2').show()
                $('#countItemCart2').html(res.countItem)
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
        success: async function (response) {
            await success('เพิ่มลงในตะกร้าแล้ว')
            await getViewCartMe()
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
                getViewCartMe()
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
            getViewCartMe()
            // $.pjax.reload({container:response.container, history:false});
        }
    });
});


    $("body").on("click", ".btn-confirm", function (e) {
// \$('.btn-confirm').click(function (e) { 
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
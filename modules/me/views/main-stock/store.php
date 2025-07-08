<?php

use yii\web\View;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\bootstrap5\LinkPager;
use app\modules\inventory\models\Warehouse;

$warehouse = Yii::$app->session->get('sub-warehouse');
$this->title = 'คลัง'.$warehouse->warehouse_name.'/เบิกวัสดุคลังหลัก';

$this->params['breadcrumbs'][] = ['label' => 'คลังหน่วยงาน', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => 'dashboard', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'เบิกวัสดุคลังหลัก'
?>

<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-shop fs-4 text-primaryr"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/menu') ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('navbar_menu'); ?>
<?php echo $this->render('@app/modules/me/menu',['active' => 'store']) ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('action'); ?>
 <?php echo Html::a('ทะเบียนการเบิก',['/me/stock-event/reuqest-order'],['class' => 'btn btn-primary shadow'])?>
<?php $this->endBlock(); ?>


<?php

$cart = Yii::$app->cartMain;
$products = $cart->getItems();

?>

<div class="card">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div class="d-flex flex-column">
            <h6><i class="bi bi-ui-checks"></i> จำนวนวัสดุในคลัง <span class="badge rounded-pill text-bg-primary"><?php echo number_format($dataProvider->getTotalCount()); ?></span> รายการ</h6>
            <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    </div>
        <div>

        <?php
            try {
                echo Html::a('<i class="fa-solid fa-cart-shopping"></i> บันทึกเบิกxx', ['/me/main-stock/create','name' => 'order','type' => 'OUT','title' => 'เบิก'.$warehouseSelect['warehouse_name']], ['class' => 'btn btn-primary rounded-pill shadow position-relative open-modal','data' => ['size' => 'modal-ld']]);
                //code...
            } catch (\Throwable $th) {
                //throw $th;
            }
            ?>
            <div>
<?php if($cart->getCount() == 0):?>
    <button type="button" class="btn btn-primary rounded-pill disabled">
                    <i class="fa-solid fa-cart-plus"></i> ตะกร้า <span class="badge text-bg-danger" id="totalCount"> 0 </span> รายการ
                    </button>
<?php else:?>
                <?php echo Html::a('<button type="button" class="btn btn-primary rounded-pill">
                    <i class="fa-solid fa-cart-plus"></i> ตะกร้า <span class="badge text-bg-danger" id="totalCount">'.$cart->getCount().'</span> รายการ
                    </button>',['/me/main-stock/create','title' => 'เบิกวัสดุคลังกลัก'], ['class' => 'brn btn-primary rounded-pill shadow open-modal', 'data' => ['size' => 'modal-xl']]); ?>
                    <?php endif;?>
                   
                    </div>
        </div>
    </div>
</div>

<div class="d-flex flex-wrap">
    <?php foreach ($dataProvider->getModels() as $model) { ?>
    <div class="p-2 col-2">
        <div class="card position-relative">
        <p class="position-absolute top-0 end-0 p-2">
                        <i class="fa-solid fa-circle-info fs-4"></i>
                    </p>
            <?php echo Html::img($model->product->ShowImg(), ['class' => 'card-top object-fit-cover rounded-top','style' => 'max-height: 155px;']); ?>
            <div class="card-body w-100">
            <div class="d-flex justify-content-start align-items-center">
                            <?php if($model->SumQty() >= 1):?>
                            <span class="badge text-bg-primary  mt--45"><?php echo $model->SumQty(); ?>
                                <?php echo $model->product->unit_name; ?></span>
                            <?php else:?>
                            <span class="btn btn-sm btn-secondary fs-13 mt--45 rounded-pill"> หมด</span>
                            <?php endif;?>
                        </div>
                        <p class="text-truncate mb-0"><?php echo $model->product->title; ?></p>
                        
                        <div class="d-flex justify-content-between">
                            <div class="fw-semibold text-danger">
                            <?php echo number_format($model->unit_price,2); ?>
                            </div>
                                <?php
                                                try {
                                                    echo Html::a('<i class="fa-solid fa-circle-plus"></i> เลือก', ['/me/main-stock/add-to-cart', 'id' => $model->id], ['class' => 'add-cart btn btn-sm btn-primary rounded-pill']);
                                                } catch (Throwable $th) {
                                                    // throw $th;
                                                }
                                ?>
                        </div>
            </div>
        </div>

    </div>
    <?php }?>
</div>

<div class="d-flex justify-content-center">
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
                $('#stocksearch-warehouse_id').val(res.mainWarehouse.id);
                $('#stocksearch-asset_type').val(res.asset_type);
                // $('#stocksearch-warehouse_id').prop('disabled', true).trigger('change');
                $('#totalCount').text(res.totalCount)
                
                if(res.totalCount >= 1){
                }
                if(res.totalCount == 1){
                    window.location.reload()    
                }
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
            url: "/me/main-stock/update-cart",
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
                $('#totalCount').text(res.totalCount)
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
                ViewMainCar();
                $('#totalCount').text(res.totalCount)
                if(res.totalCount == 0){
                    window.location.reload()
                }
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
        success: function (res) {
            ViewMainCar();
            success()
            if(res.totalCount == 0){
                window.location.reload()
            }
            $('#totalCount').text(res.totalCount)
            // $.pjax.reload({ container:'#inventory-container', history:false,replace: false,timeout: false});
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


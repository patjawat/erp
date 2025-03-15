<?php
use yii\helpers\Html;
$warehouse = Yii::$app->session->get('sub-warehouse');

$this->title = 'คลัง'.$warehouse->warehouse_name;

$cart = Yii::$app->cartSub;
$products = $cart->getItems();
?>
<?php $this->beginBlock('page-title'); ?>
<i class="bi bi-shop fs-1"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>


<div class="row">
<div class="col-8">
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-4 d-flex justify-content-start align-items-center">
                <h6><i class="bi bi-ui-checks"></i> วัสดุในคลัง <span class="badge rounded-pill text-bg-primary"><?= $dataProvider->getTotalCount(); ?> </span> รายการ</h6>
            </div>
            <div class="col-4"><?= $this->render('_search_stock', ['model' => $searchModel]); ?></div>
            <div class="col-4 d-flex justify-content-end align-items-center">
                <?=Html::a('
                    <i class="fa-solid fa-cart-plus"></i> ตะกร้า <span class="badge text-bg-danger" id="totalCount">'.$cart->getCount().'</span>
                   ',['/me/sub-stock/show-cart'],['class' => 'btn btn-primary open-modal rounded-pill','data' => ['size' => 'modal-xl ']])?>
                    </div>

        </div>
    </div>
</div>

<div id="cart-container">
    <?php foreach ($dataProvider->getModels() as $item): ?>
    <div class="card mb-2">
        <div class="card-body py-2">
            <div class="row align-items-center">
                <div class="col-md-1 p-1">
                    <?php echo Html::img($item->product->ShowImg(), ['class' => 'img-fluid object-fit-cover rounded-1']) ?>
                </div>
                <div class="col-md-7">
                    <h5 class="mb-1"> <?php echo $item->SumQty() == 0 ? '<span class="badge text-bg-danger me-1"><i class="fa-solid fa-triangle-exclamation"></i> หมด</span>' : ''?><?= $item->product->title ?></h5>
                    <p class="text-muted mb-0 fw-semibold"> <?= isset($item->product->productType->title) ? $item->product->productType->title : 'ไม่พบข้อมูล' ?></p>
                    <p class="text-muted mb-0 fw-semibold"><?php echo $item->lot_number ?> | คงเหลือ <?=$item->SumQty()?>  <?php echo $item->product->unit_name; ?></p>
                </div>
                <div class="col-md-3">
                    <?= isset($item->warehouse) ? $item->warehouse->warehouse_name  : 'ไม่พบข้อมูล' ?>
                </div>

                <div class="col-md-1 d-flex justify-content-center">
                <?php if($item->SumQty() > 0):?>
                    <?=Html::a('<i class="bi bi-cart-plus fs-3"></i>',['/me/sub-stock/add-to-cart','id' => $item->id],['class' => 'add-sub-cart'])?>
                    <?php else:?>
                        <i class="bi bi-cart-plus fs-3 text-secondary"></i>
                    <?php endif;?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    
    <div class="d-flex justify-content-center">
        <?= yii\bootstrap5\LinkPager::widget([
                    'pagination' => $dataProvider->pagination,
                    'firstPageLabel' => 'หน้าแรก',
                    'lastPageLabel' => 'หน้าสุดท้าย',
                    'options' => [
                        'class' => 'pagination pagination-sm',
                    ],
                ]); ?>
    
    </div>
</div>

    
</div>
<div class="col-4">

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6>ประวัติจ่ายวัสดุ</h6>
            <?php echo Html::a('ทะเบียนทั้งหมด',['/me/store-v2/order-out'],['class' => 'btn btn-light rounded-pill'])?>
        </div>
            <p class="card-text">กำลังอยู่ระหว่างพัฒนา</p>
    </div>
</div>

</div>
</div>

<?php
$js = <<< JS


$("body").on("click", ".add-sub-cart", function (e) {
    e.preventDefault();
    console.log('click');
    
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
                if(res.status =='success')
                {
                    $('#totalCount').text(res.totalCount);
                }
                success()
        }
    });
});


JS;
$this->registerJs($js,yii\web\View::POS_END);
?>


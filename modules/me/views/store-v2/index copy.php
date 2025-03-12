<?php
use yii\helpers\Html;

$title = '';
?>
<?php
$cart = Yii::$app->cartSub;
$products = $cart->getItems();
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-clipboard-user fs-1"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center">
            <h6><i class="bi bi-ui-checks"></i> วัสดุในสต๊อก <span class="badge rounded-pill text-bg-primary">
                    <?= $dataProvider->getTotalCount(); ?> </span> รายการ</h6>
            <?= $this->render('_search_stock', ['model' => $searchModel]); ?>

            <?=Html::a('<button type="button" class="btn btn-primary">
                    <i class="fa-solid fa-cart-plus"></i> ตะกร้า <span class="badge text-bg-danger" id="totalCount">'.$cart->getCount().'</span>
                    </button>',['/me/sub-stock/show-cart'],['class' => 'brn btn-primary shadow open-modal','data' => ['size' => 'modal-xl']])?>

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
                    <h6 class="mb-1"> <?= $item->product->title ?></h6>
                    <p class="text-muted mb-0 fw-bold"><?php echo $item->lot_number ?> | คงเหลือ  <?php echo $item->product->unit_name; ?></p>
                </div>
                <div class="col-md-3">
                    <?= isset($item->product->productType->title) ? $item->product->productType->title : 'ไม่พบข้อมูล' ?>
                </div>

                <div class="col-md-1 d-flex justify-content-center">
                    <?php //  Html::a('<i class="bi bi-cart-plus fs-1"></i>', ['/inventory/stock/view-stock-card', 'id' => $item->id], ['class' => 'add-sub-cart']) ?>
                    <?=Html::a('<i class="fa-solid fa-cart-plus"></i> เบิก',['/me/sub-stock/add-to-cart','id' => $item->id],['class' => 'add-sub-cart btn btn-sm btn-primary shadow rounded-pill'])?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
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


<?php
use yii\helpers\Html;

$title = '';
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-clipboard-user fs-1"></i> <?php echo $this->title; ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('page-action'); ?>
<?php  echo $this->render('@app/modules/me/views/store-v2/menu') ?>
<?php $this->endBlock(); ?>


<div id="cart-container">
<?php foreach($dataProvider->getModels() as $item):?>
            <div class="card mb-2">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                    <div class="col-md-1 p-1">
                            <?php echo Html::img($item->product->ShowImg(),['class' => 'img-fluid object-fit-cover rounded-1'])?>
                        </div>
                   
                            <div class="col-md-9">
                                <h6 class="mb-1">  <?=$item->product->title?></h6>
                                <p class="text-muted mb-0 fw-bold"><?php echo $item->lot_number?></p>
                            </div>
                            <div class="col-md-9">
                            <?=isset($item->product->productType->title) ? $item->product->productType->title : 'ไม่พบข้อมูล' ?>
                                </div>
                       
                        <div class="col-md-1 d-flex justify-content-center">
                        <?=Html::a('<i class="bi bi-cart-plus fs-1"></i>',['/inventory/stock/view-stock-card','id' => $item->id],['class' => ''])?>
                                                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach;?>
        </div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            <h6><i class="bi bi-ui-checks"></i> วัสดุในสต๊อก <span class="badge rounded-pill text-bg-primary">
                    <?=$dataProvider->getTotalCount();?> </span> รายการ</h6>
                    <?=$this->render('_search_stock', ['model' => $searchModel]); ?>
           <?php echo Html::a('<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white"> 0 </span><i class="bi bi-cart-check fs-1"></i>',['/'],['class' => 'position-relative me-3'])?>
        </div>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th scope="col" class="fw-semibold">ชื่อรายการ</th>
                    <th class="text-start fw-semibold">ประเภท</th>
                    <th scope="col" class="text-center fw-semibold">คงเหลือ</th>
                    <th scope="col" class="text-center fw-semibold">หน่วย</th>
                    <th scope="col" class="text-center fw-semibold">ดำเนินการ</th>
                </tr>
            </thead>
            <tbody class="align-middle table-group-divider">
                <?php foreach($dataProvider->getModels() as $item):?>
                <tr>
                    <th scope="row">
                    <div class="d-flex">
                            <?php echo Html::img($item->product->ShowImg(),['class' => 'avatar object-fit-cover'])?>
                                <div class="avatar-detail">
                                    <h6 class="mb-1 fs-15 fw-semibold" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <?=$item->product->title?>
                                    </h6>
                                   ล๊อต <?php echo $item->lot_number?> <span class="badge text-bg-primary"><?=$item->product->data_json['unit']?></span>
                                </div>
                            </div>    
                    
                    </th>
                    <td class="text-start">
                        <?=isset($item->product->productType->title) ? $item->product->productType->title : 'ไม่พบข้อมูล' ?>
                    </td>
                    <td class="text-center"><?=$item->SumQty()?></td>
                    <td class="text-center">
                    <?=$item->product->data_json['unit']?>
                    </td>
                    <?php if(isset($warehouse) && $warehouse['warehouse_type'] !== 'MAIN'):?>
                    <td class="text-end">
                    <?php if($item->SumQty() > 0):?>
                    <?=Html::a('<i class="fa-solid fa-cart-plus"></i> เบิก',['/inventory/sub-stock/add-to-cart','id' => $item->id],['class' => 'add-sub-cart btn btn-sm btn-primary shadow rounded-pill'])?>
                    <?php // Html::a('<i class="fa-solid fa-circle-plus"></i> เลือก2',['/inventory/sub-stock/select-lot','id' => $item->id],['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal','data' => ['size' => 'modal-lg']])?>
                   <?php else:?>
                    <button type="button" class="btn btn-sm btn-primary shadow rounded-pill" disabled><i class="fa-solid fa-circle-plus"></i> เลือก</button>
                </td>
                    <?php endif?>
                    <?php else:?>
                        <td class="text-center">
                        <?=Html::a('<i class="bi bi-cart-plus fs-3"></i>',['/inventory/stock/view-stock-card','id' => $item->id],['class' => ''])?>
                    </td>
                    <?php endif?>
                </tr>
                <?php endforeach;?>

            </tbody>
        </table>

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
 
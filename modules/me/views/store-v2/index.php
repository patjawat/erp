<!-- ตัวอย่าง -->
<!-- https://spruko.com/demo/tailwind/xintra/dist/html/index13.html -->
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

        <div id="cart-container">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6><i class="bi bi-ui-checks"></i> ทะเบียนวัสดุ <span
                                class="badge rounded-pill text-bg-primary">
                                <?=$dataProvider->getTotalCount();?> </span> รายการ</h6>

                        <?=$this->render('_search', ['model' => $searchModel]); ?>
                        <?=Html::a('
                    <i class="fa-solid fa-cart-plus"></i> ตะกร้า <span class="badge text-bg-danger" id="totalCount">'.$cart->getCount().'</span>
                   ',['/me/sub-stock/show-cart'],['class' => 'btn btn-primary open-modal rounded-pill','data' => ['size' => 'modal-xl ']])?>


                    </div>
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th class="text-center fw-semibold" style="width:30px">ลำดับ</th>
                                <th scope="col" class="fw-semibold">ชื่อรายการ</th>
                                <th class="text-start fw-semibold">ประเภท</th>
                                <th scope="col" class="text-center fw-semibold">คงเหลือ</th>
                                <th scope="col" class="text-center fw-semibold">หน่วย</th>
                                <th scope="col" class="text-end fw-semibold">มูลค่า</th>
                                <th scope="col" class="text-end fw-semibold">ดำเนินการ</th>
                            </tr>
                        </thead>
                        <tbody class="align-middle table-group-divider">
                            <?php foreach($dataProvider->getModels() as $key => $item):?>
                            <tr class="align-middle">
                                <td class="text-center fw-semibold">
                                    <?php echo (($dataProvider->pagination->offset + 1)+$key)?></td>
                                <td>
                                    <?php // echo Html::a($item->product->Avatar(),['/inventory/stock/view-stock-card','id' => $item->id])?>
                                    <div class="d-flex">
                                        <?php echo Html::img($item->product->showImg(),['class' => 'avatar'])?>
                                        <div class="avatar-detail">
                                            <h6 class="mb-1 fs-15"><?php echo $item->product->title?></h6>
                                            <span
                                                class="text-primary fw-semibold"><?php echo $item->product->code?></span>
                                            |
                                            <?php echo Html::a('<span class="badge rounded-pill badge-soft-primary text-primary fs-13 "><i class="fa-solid fa-clock"></i> Stock card</span>',['/me/stock-event/view-stock-card','id' => $item->id],['class' => 'open-modal','data' => ['size' => 'modal-xl']])?>


                                        </div>
                                    </div>
                                </td>
                                <td class="text-start">
                                    <?=isset($item->product->productType->title) ? $item->product->productType->title : 'ไม่พบข้อมูล' ?>
                                </td>
                                <td class="text-center"><?=$item->SumQty()?></td>
                                <td class="text-center"><?=$item->product->data_json['unit']?></td>
                                <td class="text-end">
                                    <span class="fw-semibold"><?=$item->SumPriceByItem()?></span>
                                </td>
                                <?php if(isset($warehouse) && $warehouse['warehouse_type'] !== 'MAIN'):?>
                                <td class="text-end">
                                    <?php if($item->SumQty() > 0):?>
                                    <?=Html::a('<i class="fa-solid fa-cart-plus"></i> เบิก',['/inventory/sub-stock/add-to-cart','id' => $item->id],['class' => 'add-sub-cart btn btn-sm btn-primary shadow rounded-pill'])?>
                                    <?php // Html::a('<i class="fa-solid fa-circle-plus"></i> เลือก2',['/inventory/sub-stock/select-lot','id' => $item->id],['class' => 'btn btn-sm btn-primary shadow rounded-pill open-modal','data' => ['size' => 'modal-lg']])?>
                                    <?php else:?>
                                    <button type="button" class="btn btn-sm btn-secondary shadow rounded-pill"
                                        disabled><i class="fa-solid fa-circle-exclamation"></i> หมด</button>
                                </td>
                                <?php endif?>
                                <?php else:?>
                                <td class="text-end">
                                    <?=Html::a('<i class="fa-solid fa-eye"></i>',['/inventory/stock/view-stock-card','id' => $item->id],['class' => 'btn btn-primary'])?>
                                </td>
                                <?php endif?>
                            </tr>
                            <?php endforeach;?>
                            <tr>
                                <td class="text-end" colspan="4"> <span class="fw-semibold">รวมทั้งสิ้น</span></td>
                                <td class="text-end"> <span class="fw-semibold"><?=$searchModel->SumPrice()?></span>
                                </td>
                                <td class="text-end"></td>
                            </tr>
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

        </div>


    </div>
    <div class="col-4">

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <h6>ประวัติจ่ายวัสดุ</h6>
                    <?php echo Html::a('ทะเบียนทั้งหมด',['/me/stock-event/out'],['class' => 'btn btn-light rounded-pill'])?>
                </div>
                <div id="ShowStockOutHistory"></div>
            </div>
        </div>

    </div>
</div>

<?php
$js = <<< JS


loadStockOutHistory()

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

function loadStockOutHistory()
{
    $.ajax({
        type: "get",
        url: "/me/stock-event/out",
        dataType: "json",
        success: function (res) {
            $("#ShowStockOutHistory").html(res.content)
        }
    });
}


JS;
$this->registerJs($js,yii\web\View::POS_END);
?>
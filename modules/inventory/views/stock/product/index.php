<?php

use yii\widgets\Pjax;
use yii\bootstrap5\Html;
use app\components\StockHelper;
?>

<?php Pjax::begin(['id' => 'inventory-container', 'enablePushState' => false, 'timeout' => 88888888]); ?>
<div>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
</div>


<div
    class="table-responsive">
    <table class="table table-striped table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width:30px">ลำดับ</th>
                <th scope="col">รายการ</th>
                <th scope="col">ประเภทวัสดุ</th>
                <th scope="col" class="text-center">จำนวนคงเหลือ</th>
                <th scope="col" class="text-center">ล๊อตปัจจุบัน</th>
                <th scope="col">ดำเนินการ</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($dataProvider->getModels() as $key => $item): ?>
                <?php
                    $firstOut = StockHelper::firstOut($item->asset_item, $item->warehouse_id);
                    ?>
                    <?php if($item->sumStockItem() < 0):?>
                        <tr class="table-danger">
                    <?php else:?>
                        <tr>
                    <?php endif?>

                    <td class="text-center"><?php echo (($dataProvider->pagination->offset + 1) + $key) ?></td>
                    <td><?= $item->product?->Avatar(); ?></td>
                    <td><?= $item->product?->productType->title; ?></td>
                    <td class="text-center"><?= $item->sumStockItem() ?></td>
                    <td class="text-center">

                    <?=$firstOut['lot_number'] == "" ? '<i class="fa-solid fa-circle-exclamation text-danger"></i>' : $firstOut['lot_number'] ?></td>
                    <td>
                    <?php if($item->sumStockItem() > 0 && $firstOut['lot_number'] !==""):?>    
                    <?= Html::a('<i class="fa-solid fa-circle-plus"></i> เลือก', ['/inventory/stock-order/add-new-order-item','id' => $firstOut['id'],'order_id' => $searchModel->order_id], ['class' => 'btn btn-sm btn-primary add-new-item']) ?></td>
               <?php else:?>
                <button type="button" class="btn btn-sm btn-secondary" disabled><i class="fa-solid fa-circle-plus"></i> เลือก</button>
                    <?php endif;?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="iq-card-footer text-muted d-flex justify-content-center mt-4">
    <?= yii\bootstrap5\LinkPager::widget([
        'pagination' => $dataProvider->pagination,
        'firstPageLabel' => 'หน้าแรก',
        'lastPageLabel' => 'หน้าสุดท้าย',
        'options' => [
            'listOptions' => 'pagination pagination-sm',
            'class' => 'pagination-sm',
        ],
    ]); ?>
</div>

<?php Pjax::end(); ?>

 <?php
$js = <<< JS
$('body').on('click','.add-new-item',function(e){
    e.preventDefault();
    $.ajax({
        type: "get",
        url: $(this).attr('href'),
        dataType: "json",
        success: function (response) {
            success();
            showOrderItem();
            
        }
    });
})
JS;
$this->registerJS($js, yii\web\View::POS_END);
?>

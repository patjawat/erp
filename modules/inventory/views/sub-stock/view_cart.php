<?php
use yii\web\View;
?>

<?php
use yii\helpers\Html;
use yii\widgets\Pjax;

    $cart = \Yii::$app->cartSub;
    $products = $cart->getItems();
    $warehouseSelect = Yii::$app->session->get('select-warehouse');
?>
<?php $this->beginBlock('page-title'); ?>
<i class="fa-solid fa-cubes-stacked"></i> <?= $this->title; ?>
<?php $this->endBlock(); ?>

<?php $this->beginBlock('sub-title'); ?>
<?php $this->endBlock(); ?>
<?php $this->beginBlock('page-action'); ?>
<?= $this->render('../default/menu') ?>
<?php $this->endBlock(); ?>

<table class="table table-primary">
    <thead>
        <tr>
            <th scope="col">ชื่อรายการ</th>
            <th class="text-center">จำนวสต็อก</th>
            <th class="text-center">หน่วย</th>
            <th class="text-end">มูลค่า</th>
            <th class="text-center" style="width:190px">จำนวนเบิก</th>
            <th scope="col" class="text-center align-center" style="width:32px;">#</th>
        </tr>
    </thead>
    <tbody>
        
        <?php $sumQty = 0; $getQuantity=0;foreach($products as $item):?>
            <?php
           $sumQty += (float)$item->SumQty();
           $getQuantity += (float)$item->getQuantity();
                
                ?>
        <tr class="<?=($item->getQuantity() > $item->SumQty()) ? 'badge-soft-danger' : ''?>">
            <td scope="row"><?=$item->product->Avatar();?></td>
            <td class="text-center">
            <span class="badge rounded-pill badge-soft-primary text-primary fs-13 fw-semibold"> <?=$item->SumQty()?> </span>
            </td>
            <td class="text-center"><?=$item->product->unit_name?></td>
            <td class="text-end"><span class="fw-semibold"><?=number_format($item->unit_price,2)?></span></td>
            <td>
                <div class="d-flex d-flex flex-row">
                    <?=Html::a('<i class="fa-solid fa-chevron-left"></i>',['/inventory/sub-stock/update-cart','id' => $item->id,'quantity' => ($item->getQuantity()-1)],['class' => 'btn update-sub-cart'])?>
                    <input type="text" value="<?=$item->getQuantity()?>" class="form-control update-qty"
                        id="<?=$item->id?>" style="width:50px;font-weight: 600;" />
                    <?=Html::a('<i class="fa-solid fa-chevron-right"></i>',['/inventory/sub-stock/update-cart','id' => $item->id,'quantity' => ($item->getQuantity()+1)],['class' => 'btn update-sub-cart'])?>
                </div>
            </td>
            <td>
                <?=Html::a('<i class="fa-solid fa-trash"></i>',['/inventory/sub-stock/delete-item','id' => $item->id],['class' => 'delete-sub-item-cart btn btn-sm btn-danger shadow '])?>
            </td>
        </tr>
        <?php endforeach;?>
        <tr>
            <td colspan="3" class="text-center">รวมทั้งสิ้น</td>
            <td class="text-end"><span class="fw-semibold"><?=number_format($cart->getCost(),2);?></span></td>
            <td></td>
            <td></td>
        </tr>
    </tbody>
</table>


<div class="text-center">
    <?php if(($getQuantity > $sumQty)):?>
    <button type="button" class="btn btn-primary rounded-pill" disabled><i class="fa-solid fa-cart-shopping"></i> บันทึกเบิก</button>
    <?php else:?>
    <?php echo  Html::a('<i class="fa-solid fa-cart-shopping"></i> บันทึกเบิก', ['/inventory/sub-stock/check-out','name' => 'order','type' => 'OUT','title' => 'เบิกวัสดุ'], ['class' => 'btn btn-primary rounded-pill shadow position-relative open-modal checkout','data' => ['size' => 'modal-ld']]) ?>
    <?php endif?>
</div>

<?php
$js = <<< JS



JS;
$this->registerJS($js,View::POS_END);

?>
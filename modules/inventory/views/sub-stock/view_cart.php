<?php
use yii\widgets\Pjax;
?>

<?php
use yii\helpers\Html;
use yii\web\View;

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
                        <th scope="col" style="width:60px">จำนวน</th>
                        <th scope="col" class="text-center align-center" style="width:32px;">#</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $item):?>
                    <tr class="">
                        <td scope="row">

                            <?php

                            echo $item->product->Avatar();
                            ?></td>
                        <td>
                            <div class="d-flex d-flex flex-row">
                                <?=Html::a('<i class="fa-solid fa-chevron-left"></i>',['/inventory/sub-stock/update-cart','id' => $item->id,'quantity' => ($item->getQuantity()-1)],['class' => 'btn update-sub-cart'])?>
                                <input type="text" value="<?=$item->getQuantity()?>" class="form-control update-qty" id="<?=$item->id?>" style="width:50px;font-weight: 600;" />
                                <?=Html::a('<i class="fa-solid fa-chevron-right"></i>',['/inventory/sub-stock/update-cart','id' => $item->id,'quantity' => ($item->getQuantity()+1)],['class' => 'btn update-sub-cart'])?>
                            </div>
                        </td>
                        <td>
                            <?=Html::a('<i class="fa-solid fa-trash"></i>',['/inventory/sub-stock/delete-item','id' => $item->id],['class' => 'delete-sub-item-cart btn btn-sm btn-danger shadow '])?>
                        </td>
                    </tr>
                    <?php endforeach;?>
                </tbody>
            </table>

        <div class="d-grid gap-2">
<?php if($cart->getCount() == 0):?>
        <button type="button" class="btn btn-primary" disabled><i class="fa-solid fa-cart-shopping"></i> เบิก</button>
<?php else:?>
        <?php echo  Html::a('<i class="fa-solid fa-cart-shopping"></i> บันทึกเบิก', ['/inventory/sub-stock/check-out','name' => 'order','type' => 'OUT','title' => 'เบิกวัดุ'], ['class' => 'btn btn-primary rounded-pill shadow position-relative open-modal checkout','data' => ['size' => 'modal-ld']]) ?>
        <?php endif?>
    </div>

<?php
$js = <<< JS


JS;
$this->registerJS($js,View::POS_END);

?>